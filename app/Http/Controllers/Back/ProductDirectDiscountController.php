<?php
namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Models\Price;
use App\Models\Product;
use App\Services\Sales\DirectDiscountPricing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Morilog\Jalali\Jalalian;

final class ProductDirectDiscountController extends Controller
{
    /** Standalone per-variant discount update; never writes base price, stock or ERP IDs. */
    public function update(Request $request, Product $product)
    {
        if (! $product->isPhysical()) abort(422, 'Only physical product variants are supported.');

        $data = $request->validate([
            'price_id' => ['required','integer','min:1'],
            'mode' => ['required','in:percent,fixed_amount,fixed_final'],
            'value' => ['required','integer','min:0','max:999999999'],
            'discount_expire_at' => ['nullable','string','max:24'],
        ]);
        $mode = (string) $data['mode'];
        $value = (int) $data['value'];
        $expiresAt = $this->expiryFromJalali($data['discount_expire_at'] ?? null, $value);

        DB::transaction(function () use ($product,$data,$mode,$value,$expiresAt) {
            $variant = Price::query()->where('product_id',$product->id)
                ->whereKey((int) $data['price_id'])->lockForUpdate()->firstOrFail();
            $raw = (int) $variant->regular_price;
            $regularToman = (int) round($variant->regularPrice());
            if ($raw <= 0 || $regularToman <= 0) {
                throw ValidationException::withMessages(['value'=>'قیمت پایه معتبر نیست؛ ابتدا قیمت انبار را بررسی کنید.']);
            }
            try {
                $finalToman = DirectDiscountPricing::finalToman($regularToman,$mode,$value);
            } catch (\InvalidArgumentException $e) {
                throw ValidationException::withMessages(['value'=>'مقدار تخفیف باید معتبر و کمتر از قیمت اصلی باشد.']);
            }
            if ($mode === 'percent') {
                $variant->discount = $value;
                $variant->discount_price = DirectDiscountPricing::discountedRaw($raw,$value);
                $variant->direct_discount_mode = null;
                $variant->direct_discount_value = null;
            } else {
                $variant->discount = DirectDiscountPricing::effectivePercent($regularToman,$finalToman);
                // Keep the exact manager-entered fixed amount/final in the dedicated columns.
                $variant->direct_discount_mode = $finalToman < $regularToman ? $mode : null;
                $variant->direct_discount_value = $finalToman < $regularToman ? $value : null;
                $variant->discount_price = $finalToman * 10;
            }
            $variant->discount_expire_at = $finalToman < $regularToman ? $expiresAt : null;
            $variant->save();
        },3);

        return redirect()->route('admin.products.edit',['product'=>$product->id])
            ->with('ariya_direct_discount_saved','تخفیف اختصاصی این تنوع با موفقیت ذخیره شد.');
    }

    private function expiryFromJalali(?string $value,int $discountValue): ?string
    {
        if ($discountValue === 0) return null;
        $value=trim((string) $value);
        if ($value === '') return null;
        $value=strtr($value,[
          '۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9',
          '٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9',
        ]);
        if (!preg_match('/^[0-9]{4}\/[0-9]{2}\/[0-9]{2} [0-9]{2}:[0-9]{2}$/D',$value)) {
            throw ValidationException::withMessages(['discount_expire_at'=>'تاریخ پایان باید مانند ۱۴۰۵/۰۸/۳۰ ۲۳:۵۹ باشد.']);
        }
        try {
            $date=Jalalian::fromFormat('Y/m/d H:i',$value)->toCarbon();
            $check=strtr(Jalalian::fromCarbon($date)->format('Y/m/d H:i'),[
              '۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9',
              '٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9',
            ]);
            if ($check!==$value || $date->lessThanOrEqualTo(now())) throw new \InvalidArgumentException('Invalid expiration');
            return $date->format('Y-m-d H:i:s');
        } catch (\Throwable $e) {
            throw ValidationException::withMessages(['discount_expire_at'=>'تاریخ پایان نامعتبر است یا در گذشته قرار دارد.']);
        }
    }
}
