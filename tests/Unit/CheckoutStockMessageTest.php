<?php

namespace Tests\Unit;

use App\Support\CheckoutStockMessage;
use PHPUnit\Framework\TestCase;

class CheckoutStockMessageTest extends TestCase
{
    public function test_it_extracts_and_deduplicates_inventory_variant_ids(): void
    {
        $this->assertSame([3851, 20024], CheckoutStockMessage::variantIds([
            'Insufficient ordinary stock for variant 3851.',
            'Insufficient protected bundle allocation for variant 20024.',
            'Insufficient ordinary stock for variant 3851.',
            'An unrelated validation message.',
        ]));
    }

    public function test_it_builds_an_actionable_message_for_each_product(): void
    {
        $this->assertSame([
            'موجودی کالای «هندزفری MICRO CA-19» کافی نیست. لطفاً تعداد آن را کاهش دهید یا از سبد خرید حذف کنید.',
            'موجودی کالای «ورکیم JOYROOM» کافی نیست. لطفاً تعداد آن را کاهش دهید یا از سبد خرید حذف کنید.',
        ], CheckoutStockMessage::customerMessages([
            'هندزفری MICRO CA-19',
            'ورکیم JOYROOM',
            'هندزفری MICRO CA-19',
        ]));
    }
}
