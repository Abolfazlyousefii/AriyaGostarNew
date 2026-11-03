<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class VipDiscountService
{
    public static function percentForUser(?int $userId): float
    {
        if (!$userId || !Schema::hasTable('vip_customer_memberships') || !Schema::hasTable('vip_discount_settings')) {
            return 0.0;
        }

        $active = DB::table('vip_customer_memberships')
            ->where('user_id', $userId)->where('active', 1)->exists();
        if (!$active) return 0.0;

        return min(100.0, max(0.0, (float) DB::table('vip_discount_settings')->where('id', 1)->value('percent')));
    }
}
