<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RecoveryRoutesTest extends TestCase
{
    public function test_recovered_storefront_and_admin_routes_are_registered(): void
    {
        foreach ([
            'front.quick-wholesale.index',
            'front.quick-wholesale.store',
            'front.bundles.show',
            'front.bundles.variants',
            'front.bundles.suggestion',
            'admin.bulk-discounts.index',
            'admin.products.direct-discount.update',
            'admin.products.direct-discount.apply-all',
            'admin.sales-control.index',
            'admin.orders.manual-payment.confirm',
        ] as $routeName) {
            $this->assertTrue(Route::has($routeName), "Missing recovered route: {$routeName}");
        }
    }

    public function test_recovered_runtime_dependencies_are_autoloadable(): void
    {
        foreach ([
            \App\Services\Sales\SalesCampaignService::class,
            \App\Services\Sales\SalesControlService::class,
            \App\Services\Sales\BulkDiscountService::class,
            \App\Services\QuickWholesaleVariantCatalog::class,
            \App\Services\QuickWholesaleCartPlan::class,
            \App\Http\Controllers\Back\BulkDiscountController::class,
            \Themes\DefaultTheme\src\Controllers\QuickWholesaleController::class,
            \Themes\DefaultTheme\src\Controllers\BundleShowcaseController::class,
        ] as $className) {
            $this->assertTrue(class_exists($className), "Missing recovered class: {$className}");
        }
    }
}
