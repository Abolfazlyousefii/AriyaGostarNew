<?php

namespace App\Services\Inventory;

use App\Models\Inventory\Customer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Resolve the purchaser by Site user ID, without ever stealing another customer's account. */
class SiteCustomerResolver
{
    public function forUser(User $user): Customer
    {
        $siteUserId = (int) $user->id;
        $customer = Customer::query()->where('site_customer_id', $siteUserId)->first();
        if ($customer) {
            return $customer;
        }

        if ($user->level !== 'user' || !$user->is_active || !$user->verified_at) {
            throw ValidationException::withMessages([
                'customer' => 'حساب مشتری هنوز تأیید یا فعال نشده است.',
            ]);
        }

        // Use the login identity, never the recipient/mobile entered for this order.
        $mobile = trim((string) ($user->mobile ?: $user->username));
        if (!preg_match('/^09\d{9}$/', $mobile)) {
            throw ValidationException::withMessages([
                'customer' => 'شماره موبایل حساب کاربری برای اتصال به انبار معتبر نیست.',
            ]);
        }

        return DB::connection('inv')->transaction(function () use ($siteUserId, $mobile, $user): Customer {
            $customer = Customer::query()
                ->where('site_customer_id', $siteUserId)
                ->lockForUpdate()
                ->first();
            if ($customer) {
                return $customer;
            }

            $customer = Customer::query()->where('mobile', $mobile)->lockForUpdate()->first();
            if ($customer) {
                if ((int) ($customer->site_customer_id ?? 0) > 0
                    && (int) $customer->site_customer_id !== $siteUserId) {
                    throw ValidationException::withMessages([
                        'customer' => 'شماره موبایل به حساب دیگری در انبار متصل است؛ لطفاً با پشتیبانی تماس بگیرید.',
                    ]);
                }

                $customer->site_customer_id = $siteUserId;
                $customer->save();
                return $customer;
            }

            return Customer::create([
                'site_customer_id' => $siteUserId,
                'mobile' => $mobile,
                'name' => trim((string) ($user->first_name ?? '') . ' ' . (string) ($user->last_name ?? ''))
                    ?: ((string) ($user->name ?: 'مشتری سایت')),
                'address' => '',
            ]);
        }, 3);
    }
}
