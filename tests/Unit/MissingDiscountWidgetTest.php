<?php

namespace Tests\Unit;

use App\Models\Widget;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MissingDiscountWidgetTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('discounts', function (Blueprint $table): void {
            $table->id();
            $table->softDeletes();
        });
    }

    public function test_discount_widget_with_a_deleted_discount_falls_back_without_crashing(): void
    {
        $widget = new Widget(['key' => 'products-discount-block']);
        $widget->setRelation('options', collect());

        $variables = get_widget($widget);

        $this->assertTrue($variables['products']->isEmpty());
        $this->assertNull($variables['timer']);
    }
}
