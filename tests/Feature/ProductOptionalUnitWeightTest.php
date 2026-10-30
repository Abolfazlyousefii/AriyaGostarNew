<?php

namespace Tests\Feature;

use App\Http\Requests\Back\Product\StoreProductRequest;
use App\Http\Requests\Back\Product\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use ReflectionMethod;
use Tests\TestCase;

class ProductOptionalUnitWeightTest extends TestCase
{
    private const CONNECTION = 'optional_product_fields_testing';

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.connections.' . self::CONNECTION, [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);

        Schema::connection(self::CONNECTION)->create('products', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug');
            $table->string('type');
            $table->string('unit')->nullable();
            $table->integer('weight')->nullable();
            $table->boolean('inventory_to_site_synced')->default(false);
            $table->timestamps();
        });
    }

    public function test_store_validation_accepts_and_normalizes_empty_unit_and_weight(): void
    {
        $request = StoreProductRequest::create('/', 'POST', [
            'type' => 'physical',
            'unit' => '',
            'weight' => '',
        ]);
        $this->prepare($request);

        $rules = $request->rules();

        $this->assertStringContainsString('nullable', $rules['unit']);
        $this->assertStringContainsString('nullable', $rules['weight']);
        $this->assertNull($request->input('unit'));
        $this->assertNull($request->input('weight'));
    }

    public function test_update_validation_accepts_and_normalizes_empty_unit_and_weight(): void
    {
        $request = UpdateProductRequest::create('/', 'PUT', [
            'type' => 'physical',
            'unit' => '',
            'weight' => '',
        ]);
        $request->setRouteResolver(function () {
            return new class {
                public function parameter($key)
                {
                    return $key === 'product' ? (object) ['id' => 1] : null;
                }
            };
        });
        $this->prepare($request);

        $rules = $request->rules();

        $this->assertStringContainsString('nullable', $rules['unit']);
        $this->assertStringContainsString('nullable', $rules['weight']);
        $this->assertNull($request->input('unit'));
        $this->assertNull($request->input('weight'));
    }

    public function test_product_can_be_created_and_updated_with_null_unit_and_weight(): void
    {
        $product = Product::withoutEvents(function () {
            return Product::on(self::CONNECTION)->create([
                'title' => 'Optional fields product',
                'slug' => 'optional-fields-product',
                'type' => 'physical',
                'unit' => null,
                'weight' => null,
            ]);
        });

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'unit' => null,
            'weight' => null,
        ], self::CONNECTION);

        $product->update(['unit' => 'عدد', 'weight' => 250]);
        $product->update(['unit' => null, 'weight' => null]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'unit' => null,
            'weight' => null,
        ], self::CONNECTION);
    }

    public function test_inventory_api_product_remains_editable_without_unit_and_weight(): void
    {
        $product = Product::withoutEvents(function () {
            return Product::on(self::CONNECTION)->create([
                'title' => 'Warehouse product',
                'slug' => 'warehouse-product',
                'type' => 'physical',
                'inventory_to_site_synced' => true,
                'unit' => null,
                'weight' => null,
            ]);
        });

        $product->update(['title' => 'Edited warehouse product', 'unit' => null, 'weight' => null]);

        $this->assertSame('Edited warehouse product', $product->fresh()->title);
        $this->assertNull($product->fresh()->unit);
        $this->assertNull($product->fresh()->weight);
    }

    private function prepare($request): void
    {
        $method = new ReflectionMethod($request, 'prepareForValidation');
        $method->setAccessible(true);
        $method->invoke($request);
    }
}
