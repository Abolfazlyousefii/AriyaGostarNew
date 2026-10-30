<?php

namespace App\Services\Sales;

use App\Models\Price;
use App\Models\Product;
use App\Models\SalesControl;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class SalesCampaignService
{
    private static ?bool $tableExists = null;

    private static array $activeDiscountCache = [];

    public function available(): bool
    {
        if (self::$tableExists === null) {
            self::$tableExists = Schema::hasTable('sales_controls');
        }

        return self::$tableExists;
    }

    public function activeDiscountForProduct(
        int $productId
    ): ?SalesControl {
        if (!$this->available() || $productId <= 0) {
            return null;
        }

        if (
            array_key_exists(
                $productId,
                self::$activeDiscountCache
            )
        ) {
            return self::$activeDiscountCache[$productId];
        }

        $control = SalesControl::query()
            ->where('product_id', $productId)
            ->where('enabled', true)
            ->where('discount_enabled', true)
            ->first();

        if (!$control || !$control->discountIsActive()) {
            $control = null;
        }

        self::$activeDiscountCache[$productId] = $control;

        return $control;
    }

    /**
     * Effective campaign price in the same unit returned by
     * Price::regularPrice() / Price::salePrice(): Toman.
     */
    public function campaignPrice(Price $price): ?float
    {
        $control = $this->activeDiscountForProduct(
            (int) $price->product_id
        );

        if (!$control) {
            // ARIYA_BULK_DISCOUNT_V1: prefer existing per-product SalesControl.
            return app(\App\Services\Sales\BulkDiscountService::class)->price($price);
        }

        /*
         * prices.regular_price is stored in the raw DB unit.
         * Price::toRoundPrice() converts it to the displayed unit.
         *
         * The admin fixed discount input is explicitly Toman,
         * therefore amount discounts are converted back to the raw
         * DB unit before Price::toRoundPrice().
         */
        $regularRaw = max((int) $price->regular_price, 0);
        $value = max((int) $control->discount_value, 0);

        if ($control->discount_type === 'percent') {
            $value = min($value, 100);

            $discountedRaw =
                $regularRaw * ((100 - $value) / 100);
        } elseif ($control->discount_type === 'amount') {
            $discountedRaw = max(
                $regularRaw - ($value * 10),
                0
            );
        } else {
            return null;
        }

        return $price->toRoundPrice($discountedRaw);
    }

    public function campaignPercent(Price $price): ?int
    {
        $campaignPrice = $this->campaignPrice($price);

        if ($campaignPrice === null) {
            return null;
        }

        $regularPrice = (float) $price->regularPrice();

        if ($regularPrice <= 0) {
            return 0;
        }

        return max(
            0,
            min(
                100,
                (int) round(
                    (
                        ($regularPrice - $campaignPrice)
                        / $regularPrice
                    ) * 100
                )
            )
        );
    }

    /**
     * null:
     *   no manual curation has been configured; preserve old widget logic.
     *
     * empty Collection:
     *   manual curation exists, but no configured product is currently
     *   eligible. Do NOT fall back to automatic widget products.
     */
    public function showcaseProducts(
        string $placement
    ): ?Collection {
        if (!$this->available()) {
            return null;
        }

        $flag = match ($placement) {
            'discount' => 'show_in_discount_box',
            'new_arrivals' => 'show_in_new_arrivals',
            default => null,
        };

        if (!$flag) {
            return null;
        }

        $orderColumn = $placement === 'discount'
            ? 'discount_box_order'
            : 'new_arrivals_order';

        $configured = SalesControl::query()
            ->where($flag, true)
            ->orderBy($orderColumn)
            ->orderBy('id')
            ->get();

        if ($configured->isEmpty()) {
            return null;
        }

        $eligibleIds = $configured
            ->filter(function (
                SalesControl $control
            ) use ($placement) {
                if (!$control->enabled) {
                    return false;
                }

                if (
                    $placement === 'discount'
                    && $control->discount_enabled
                    && !$control->discountIsActive()
                ) {
                    return false;
                }

                $remaining = app(
                    SalesControlService::class
                )->remaining($control);

                return $remaining === null
                    || $remaining > 0;
            })
            ->pluck('product_id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        if (!$eligibleIds) {
            return collect();
        }

        $products = Product::detectLang()
            ->published()
            ->with([
                'lowestPrice',
                'category:id,title,slug,type',
            ])
            ->whereIn('products.id', $eligibleIds)
            ->where(function ($query) {
                $query
                    ->where('products.type', 'download')
                    ->orWhereHas(
                        'prices',
                        fn ($prices) => $prices
                            ->where('stock', '>', 0)
                    );
            })
            ->get()
            ->keyBy('id');

        return collect($eligibleIds)
            ->map(fn ($id) => $products->get($id))
            ->filter()
            ->values();
    }

    public function discountShowcaseTimer(): ?int
    {
        if (!$this->available()) {
            return null;
        }

        $endsAt = SalesControl::query()
            ->where('enabled', true)
            ->where('show_in_discount_box', true)
            ->where('discount_enabled', true)
            ->where(function ($query) {
                $query
                    ->whereNull('discount_starts_at')
                    ->orWhere(
                        'discount_starts_at',
                        '<=',
                        now()
                    );
            })
            ->whereNotNull('discount_ends_at')
            ->where('discount_ends_at', '>', now())
            ->min('discount_ends_at');

        if (!$endsAt) {
            return null;
        }

        return \Carbon\Carbon::parse($endsAt)
            ->getTimestamp() * 1000;
    }
}
