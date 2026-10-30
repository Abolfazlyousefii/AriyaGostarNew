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
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($container);
        $db = $this->database->getConnection('inv');
        $schema = $db->getSchemaBuilder();
        $schema->create('warehouses', function (Blueprint $t) { $t->integer('id'); $t->string('type'); $t->string('name'); });
        $schema->create('products', function (Blueprint $t) { $t->integer('id'); $t->boolean('is_sellable'); $t->boolean('is_bundle'); $t->string('bundle_stock_mode'); });
        $schema->create('product_variants', function (Blueprint $t) { $t->integer('id'); $t->integer('product_id'); $t->boolean('is_active'); $t->boolean('sales_enabled'); });
        $schema->create('warehouse_stocks', function (Blueprint $t) { $t->integer('warehouse_id'); $t->integer('product_variant_id'); $t->integer('quantity'); });
        $schema->create('product_bundle_components', function (Blueprint $t) { $t->integer('bundle_variant_id'); $t->integer('component_variant_id'); $t->integer('quantity'); });
        $schema->create('bundle_component_allocations', function (Blueprint $t) { $t->integer('bundle_variant_id'); $t->integer('component_variant_id'); $t->integer('remaining'); });
        $db->table('warehouses')->insert([['id' => 1, 'type' => 'central', 'name' => 'انبار مرکزی'], ['id' => 2, 'type' => 'scrap', 'name' => 'ضایعات']]);
        $db->table('products')->insert([['id' => 10, 'is_sellable' => 1, 'is_bundle' => 0, 'bundle_stock_mode' => 'physical'], ['id' => 20, 'is_sellable' => 1, 'is_bundle' => 1, 'bundle_stock_mode' => 'automatic']]);
        $db->table('product_variants')->insert([['id' => 1, 'product_id' => 10, 'is_active' => 1, 'sales_enabled' => 1], ['id' => 2, 'product_id' => 20, 'is_active' => 1, 'sales_enabled' => 1]]);
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
}
