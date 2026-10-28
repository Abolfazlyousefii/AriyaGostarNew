<?php

namespace Tests\Unit;

use App\Http\Resources\Datatable\Product\Product as ProductResource;
use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\Facade;
use PHPUnit\Framework\TestCase;

class AdminProductResourceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $app = new Container();
        Container::setInstance($app);
        Facade::setFacadeApplication($app);
        $router = new Router(new Dispatcher($app), $app);
        $app->instance('router', $router);
        foreach (['edit', 'destroy', 'create'] as $action) {
            $router->get('/admin/products/'.$action.'/{product?}', fn () => null)->name('admin.products.'.$action);
        }
        $router->getRoutes()->refreshNameLookups();
        $app->instance('url', new UrlGenerator($router->getRoutes(), Request::create('https://example.test')));
    }

    protected function tearDown(): void
    {
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication(null);
        Container::setInstance(null);
        parent::tearDown();
    }

    private function product(): object
    {
        // A database-free projection fixture: pricing rules are owned by the model.
        $product = new class {
            public $id = 12;
            public $slug = 'sample';
            public $image = null;
            public $title = 'نمونه';
            public $product_code = 'ARY-P-00000012';
            public $created_at = '2026-10-28 10:00:00';
            public $admin_updated_at = '2026-10-28 10:00:00';
            public $related_products_count = 3;
            public $category;
            public $categories;
            public $numericPrice = 125000.0;
            public function getLowestPrice($numeric = false) { return $numeric ? $this->numericPrice : '125,000 تومان'; }
            public function addableToCart() { return true; }
            public function isPublished() { return true; }
            public function prices() { return collect([['stock' => 8]]); }
            public function relatedProducts() { return collect(array_fill(0, $this->related_products_count, null)); }
        };
        $product->category = (object) ['title' => 'کابل'];
        $product->categories = collect([(object) ['title' => 'کابل'], (object) ['title' => 'شارژر']]);
        return $product;
    }

    public function test_table_receives_numeric_price_categories_and_related_count(): void
    {
        $row = (new ProductResource($this->product()))->toArray(Request::create('/'));
        $this->assertSame(125000.0, $row['price'] ?? null);
        $this->assertSame(['کابل', 'شارژر'], $row['categories'] ?? null);
        $this->assertSame('کابل', $row['category'] ?? null);
        $this->assertSame(3, $row['related_count'] ?? null);
        $this->assertSame('ARY-P-00000012', $row['product_code'] ?? null);
    }

    public function test_product_without_stock_price_or_categories_has_explicit_empty_values(): void
    {
        $product = $this->product();
        $product->numericPrice = null;
        $product->category = null;
        $product->categories = collect();
        $product->related_products_count = 0;
        $row = (new ProductResource($product))->toArray(Request::create('/'));
        $this->assertArrayHasKey('price', $row);
        $this->assertNull($row['price']);
        $this->assertSame([], $row['categories']);
        $this->assertSame(0, $row['related_count']);
    }

    public function test_uncategorized_product_does_not_count_the_entire_catalog_as_similar(): void
    {
        $product = $this->product();
        $product->category = null;
        $product->related_products_count = 1330;
        $row = (new ProductResource($product))->toArray(Request::create('/'));
        $this->assertSame(0, $row['related_count']);
    }
}
