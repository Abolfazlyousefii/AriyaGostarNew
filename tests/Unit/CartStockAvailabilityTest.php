<?php
namespace Tests\Unit;

use App\Services\Inventory\CartStockAvailability;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Facade;
use PHPUnit\Framework\TestCase;

class CartStockAvailabilityTest extends TestCase
{
    private Manager $database;

    protected function setUp(): void
    {
        parent::setUp();
        $this->database = new Manager();
        $this->database->addConnection(['driver' => 'sqlite', 'database' => ':memory:'], 'inv');
        $container = $this->database->getContainer();
        $container->instance('db', $this->database->getDatabaseManager());
        $container->instance('log', new \Psr\Log\NullLogger());
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($container);
        $db = $this->database->getConnection('inv');
        $schema = $db->getSchemaBuilder();
        $schema->create('warehouses', function (Blueprint $t) { $t->integer('id'); $t->string('type'); $t->string('name'); });
        $schema->create('products', function (Blueprint $t) { $t->integer('id'); $t->integer('category_id')->nullable(); $t->boolean('is_sellable'); $t->boolean('is_bundle'); $t->string('bundle_stock_mode'); });
        $schema->create('categories', function (Blueprint $t) { $t->integer('id'); $t->integer('parent_id')->nullable(); });
        $schema->create('inventory_count_freezes', function (Blueprint $t) { $t->integer('id'); $t->boolean('enabled'); $t->boolean('all_products'); $t->text('product_ids')->nullable(); $t->text('category_ids')->nullable(); });
        $db->table('inventory_count_freezes')->insert(['id' => 1, 'enabled' => 0, 'all_products' => 0, 'product_ids' => '[]', 'category_ids' => '[]']);
        $db->table('categories')->insert([['id' => 100, 'parent_id' => null], ['id' => 101, 'parent_id' => 100], ['id' => 102, 'parent_id' => 101], ['id' => 200, 'parent_id' => null]]);
        $schema->create('product_variants', function (Blueprint $t) { $t->integer('id'); $t->integer('product_id'); $t->boolean('is_active'); $t->boolean('sales_enabled'); });
        $schema->create('warehouse_stocks', function (Blueprint $t) { $t->integer('warehouse_id'); $t->integer('product_variant_id'); $t->integer('quantity'); });
        $schema->create('product_bundle_components', function (Blueprint $t) { $t->integer('bundle_variant_id'); $t->integer('component_variant_id'); $t->integer('quantity'); });
        $schema->create('bundle_component_allocations', function (Blueprint $t) { $t->integer('bundle_variant_id'); $t->integer('component_variant_id'); $t->integer('remaining'); });
        $db->table('warehouses')->insert([['id' => 1, 'type' => 'central', 'name' => 'انبار مرکزی'], ['id' => 2, 'type' => 'scrap', 'name' => 'ضایعات']]);
        $db->table('products')->insert([['id' => 10, 'is_sellable' => 1, 'is_bundle' => 0, 'bundle_stock_mode' => 'physical'], ['id' => 20, 'is_sellable' => 1, 'is_bundle' => 1, 'bundle_stock_mode' => 'automatic']]);
        $db->table('product_variants')->insert([['id' => 1, 'product_id' => 10, 'is_active' => 1, 'sales_enabled' => 1], ['id' => 2, 'product_id' => 20, 'is_active' => 1, 'sales_enabled' => 1]]);
        $db->table('products')->where('id', 10)->update(['category_id' => 102]);
        $db->table('products')->where('id', 20)->update(['category_id' => 200]);
        $db->table('warehouse_stocks')->insert([['warehouse_id' => 1, 'product_variant_id' => 1, 'quantity' => 5], ['warehouse_id' => 2, 'product_variant_id' => 1, 'quantity' => 100]]);
        $db->table('product_bundle_components')->insert(['bundle_variant_id' => 2, 'component_variant_id' => 1, 'quantity' => 2]);
        $db->table('bundle_component_allocations')->insert(['bundle_variant_id' => 2, 'component_variant_id' => 1, 'remaining' => 8]);
    }

    protected function tearDown(): void
    {
        $this->database->getConnection('inv')->disconnect();
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication(null);
        parent::tearDown();
    }

    private function line(int $id = 1, int $quantity = 3): array
    {
        return ['price_id' => $id, 'product_id' => $id === 1 ? 10 : 20, 'title' => 'کالا', 'variant' => 'مدل', 'quantity' => $quantity, 'physical' => true, 'minimum' => null, 'maximum' => null];
    }

    public function test_only_central_stock_is_used_and_duplicate_cart_lines_are_aggregated(): void
    {
        $result = (new CartStockAvailability())->check([$this->line(), $this->line()]);
        $this->assertFalse($result['status']);
        $this->assertSame(5, $result['lines'][1]['available']);
    }

    public function test_bundle_capacity_uses_protected_allocation_not_finished_sku_stock(): void
    {
        $result = (new CartStockAvailability())->check([$this->line(2, 3)]);
        $this->assertTrue($result['status']);
        $this->assertSame(4, $result['lines'][2]['available']);
        $this->assertFalse((new CartStockAvailability())->check([$this->line(2, 5)])['status']);
    }

    public function test_disabled_component_blocks_its_bundle(): void
    {
        $this->database->getConnection('inv')->table('product_variants')->where('id', 1)->update(['sales_enabled' => 0]);
        $result = (new CartStockAvailability())->check([$this->line(2, 1)]);
        $this->assertSame('not_sellable', $result['lines'][2]['code']);
    }

    public function test_wrong_product_mapping_cannot_use_another_variants_stock(): void
    {
        $line = $this->line(); $line['product_id'] = 999;
        $result = (new CartStockAvailability())->check([$line]);
        $this->assertSame('mapping_missing', $result['lines'][1]['code']);
    }

    public function test_preflight_issues_select_queries_only(): void
    {
        $db = $this->database->getConnection('inv');
        $db->enableQueryLog();
        $result = (new CartStockAvailability())->check([$this->line(), $this->line(2, 1)]);
        $this->assertTrue($result['status']);
        foreach ($db->getQueryLog() as $query) $this->assertMatchesRegularExpression('/^select\b/i', $query['query']);
    }

    private function freeze(array $values): void
    {
        $this->database->getConnection('inv')->table('inventory_count_freezes')->where('id', 1)->update($values + ['enabled' => 1]);
    }

    public function test_counting_parent_category_blocks_descendants_before_stock_or_quantity_errors(): void
    {
        $this->freeze(['category_ids' => '[100]']);
        $line = $this->line(1, 100); $line['maximum'] = 1;
        $result = (new CartStockAvailability())->check([$line]);
        $this->assertFalse($result['status']);
        $this->assertSame('stocktake', $result['lines'][1]['code']);
        $this->assertStringContainsString('انبارگردانی', $result['errors'][0]);
        $this->assertStringContainsString('کالا / مدل', $result['errors'][0]);
        $this->assertSame(5, $result['lines'][1]['available']);
    }

    public function test_frozen_snapshot_still_blocks_a_product_moved_to_an_open_category(): void
    {
        $this->freeze(['product_ids' => '["10"]']);
        $this->assertSame('stocktake', (new CartStockAvailability())->check([$this->line()])['lines'][1]['code']);
    }

    public function test_a_new_physical_bundle_cannot_bypass_its_frozen_component(): void
    {
        $this->freeze(['category_ids' => '[100]']);
        $this->database->getConnection('inv')->table('products')->where('id', 20)->update(['bundle_stock_mode' => 'physical']);
        $this->assertSame('stocktake', (new CartStockAvailability())->check([$this->line(2, 1)])['lines'][2]['code']);
    }

    public function test_automatic_bundle_is_blocked_when_a_component_is_counted(): void
    {
        $this->freeze(['product_ids' => '[10]']);
        $this->assertSame('stocktake', (new CartStockAvailability())->check([$this->line(2, 1)])['lines'][2]['code']);
    }

    public function test_other_categories_stay_open_and_reopening_is_seen_by_the_same_service(): void
    {
        $service = new CartStockAvailability();
        $this->freeze(['category_ids' => '[200]']);
        $this->assertTrue($service->check([$this->line()])['status']);
        $this->freeze(['all_products' => 1]);
        $this->assertSame('stocktake', $service->check([$this->line()])['lines'][1]['code']);
        $this->freeze(['enabled' => 0]);
        $this->assertTrue($service->check([$this->line()])['status']);
    }

    public function test_stocktake_lookup_failure_does_not_allow_purchase(): void
    {
        $this->database->getConnection('inv')->getSchemaBuilder()->drop('inventory_count_freezes');
        $this->assertFalse((new CartStockAvailability())->check([$this->line()])['status']);
        $this->assertTrue((new \App\Services\Inventory\InventoryCountAvailability())->forProduct(10, 'کالا')['blocked']);
    }

    public function test_product_page_uses_the_same_bundle_and_reopen_rules_without_writes(): void
    {
        $this->freeze(['category_ids' => '[100]']);
        $db = $this->database->getConnection('inv'); $db->enableQueryLog();
        $service = new \App\Services\Inventory\InventoryCountAvailability();
        $review = $service->forProduct(20, 'بسته');
        $this->assertTrue($review['blocked']);
        $this->assertStringContainsString('انبارگردانی', $review['message']);
        foreach ($db->getQueryLog() as $query) $this->assertMatchesRegularExpression('/^select\b/i', $query['query']);
        $this->freeze(['enabled' => 0]);
        $this->assertFalse($service->forProduct(20, 'بسته')['blocked']);
    }
}
