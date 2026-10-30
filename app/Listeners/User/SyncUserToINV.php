<?php

namespace App\Listeners\User;

use App\Models\Inventory\Customer;

class SyncUserToINV
{
    public function handle($event): void
    {
        $user = $event->user;

        // Only real, approved storefront customers belong in INV.
        if (
            $user->level !== 'user' ||
            !$user->verified_at ||
            !$user->is_active
        ) {
            return;
        }

        $mobile = trim((string) ($user->mobile ?? $user->username));

        if ($mobile === '') {
            return;
        }

        Customer::updateOrCreate(
            [
                'mobile' => $mobile,
            ],
            [
                'site_customer_id' => $user->id,

                'first_name' => $user->first_name ?? '',
                'last_name'  => $user->last_name ?? '',

                'name' => trim(
                    ($user->first_name ?? '') . ' ' .
                    ($user->last_name ?? '')
                ) ?: ($user->name ?? 'مشتری ناشناس'),

                'password' => $user->password,
                'is_active' => true,

                'synced_at' => now(),
                'sync_source' => 'site_registration',
            ]
        );
    }
}