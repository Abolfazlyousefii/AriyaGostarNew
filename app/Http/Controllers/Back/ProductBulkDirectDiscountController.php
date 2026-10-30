<?php
namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Models\Price;
use App\Models\Product;
use App\Services\Sales\BulkDirectDiscountPlan;
use App\Services\Sales\DirectDiscountPricing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Morilog\Jalali\Jalalian;

final class ProductBulkDirectDiscountController extends Controller
{
    /** Atomic update of direct discounts on ALL currently active price rows of one physical product. */
    public function applyAll(Request $request, Product $product)
    {
        if (!$product->isPhysical()) abort(422, 'Only physical product variants are supported.');
        $data = $request->validate([
            'mode' => ['required','in:percent,fixed_amount,fixed_final'],
            'value' => ['required','integer','min:0','max:999999999'],
            'discount_expire_at' => ['nullable','string','max:24'],
        ]);
        $mode = (string) $data['mode'];
        $value = (int) $data['value'];
        $expiresAt = $this->expiryFromJalali($data['discount_expire_at'] ?? null, $value);
        $updatedCount = DB::transaction(function () use ($product, $mode, $value, $expiresAt): int {
            $variants = Price::query()
                ->where('product_id', $product->id)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            // Validate every row BEFORE writing any. An invalid fixed final/amount
            // rejects the whole request, never a partially discounted product.
            $regularById = [];
            foreach ($variants as $variant) {
                $regularById[(int) $variant->id] = (int) round($variant->regularPrice());
            }
            try {
                $plan = BulkDirectDiscountPlan::build($regularById, $mode, $value);
            } catch (\InvalidArgumentException $e) {
                throw ValidationException::withMessages([
                    'value' => 'تخفیف برای همه تنوع‌ها معتبر نیست. در حالت مبلغ ثابت یا قیمت نهایی، مقدار باید از قیمت پایه ارزان‌ترین تنوع کمتر باشد و تمام تنوع‌ها قیمت پایه مثبت داشته باشند.',
                ]);
            }
            foreach ($variants as $variant) {
                $regularToman = $regularById[(int) $variant->id];
                $finalToman = $plan[(int) $variant->id];
                if ($mode === 'percent') {
                    $variant->discount = $value;
                    $variant->discount_price = DirectDiscountPricing::discountedRaw(
                        (int) $variant->regular_price, $value
                    );
                    $variant->direct_discount_mode = null;
                    $variant->direct_discount_value = null;
                } else {
                    $variant->discount = DirectDiscountPricing::effectivePercent($regularToman, $finalToman);
                    $variant->discount_price = $finalToman * 10;
                    $variant->direct_discount_mode = $finalToman < $regularToman ? $mode : null;
                    $variant->direct_discount_value = $finalToman < $regularToman ? $value : null;
                }
                $variant->discount_expire_at = $finalToman < $regularToman ? $expiresAt : null;
                $variant->save();
            }
            return count($plan);
        }, 3);

        return redirect()->route('admin.products.edit', ['product' => $product->id])
            ->with('ariya_bulk_direct_discount_saved',
                'تخفیف اختصاصی با موفقیت برای '.number_format($updatedCount).' تنوع ذخیره شد.');
    }

    private function expiryFromJalali(?string $input, int $value): ?string
    {
        if ($value === 0) return null;
        $input = trim((string) $input);
        if ($input === '') return null;
        $input = strtr($input, [
            '۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9',
            '٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9',
        ]);
        if (!preg_match('/^[0-9]{4}\/[0-9]{2}\/[0-9]{2} [0-9]{2}:[0-9]{2}$/D', $input)) {
            throw ValidationException::withMessages(['discount_expire_at' => 'تاریخ پایان باید به صورت ۱۴۰۵/۰۸/۳۰ ۲۳:۵۹ باشد.']);
        }
        try {
            $date = Jalalian::fromFormat('Y/m/d H:i', $input)->toCarbon();
            $check = strtr(Jalalian::fromCarbon($date)->format('Y/m/d H:i'), [
                '۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9',
                '٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9',
            ]);
            if ($check !== $input || $date->lessThanOrEqualTo(now())) {
                throw new \InvalidArgumentException('Invalid or expired date');
            }
            return $date->format('Y-m-d H:i:s');
        } catch (\Throwable $e) {
            throw ValidationException::withMessages(['discount_expire_at' => 'تاریخ پایان نامعتبر است یا در گذشته قرار دارد.']);
        }
    }
}
