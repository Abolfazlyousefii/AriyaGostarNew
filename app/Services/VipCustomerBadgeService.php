<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;

final class VipCustomerBadgeService
{
    /** Null for ordinary customers, a safe display option for an active VIP. */
    public static function forUser(int $userId): ?array
    {
        $membership = DB::table('vip_customer_memberships as vip')
            ->leftJoin('vip_customer_badges as badge', 'badge.user_id', '=', 'vip.user_id')
            ->where('vip.user_id', $userId)
            ->where('vip.active', 1)
            ->first(['badge.icon_type', 'badge.emoji']);

        if (!$membership) {
            return null;
        }

        $emoji = trim((string) ($membership->emoji ?? ''));
        $type = $membership->icon_type === 'emoji' && $emoji !== ''
            ? 'emoji' : 'premium_star';

        return ['type' => $type, 'emoji' => $type === 'emoji' ? $emoji : null];
    }
}
