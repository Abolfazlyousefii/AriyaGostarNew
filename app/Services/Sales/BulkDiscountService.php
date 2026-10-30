<?php

namespace App\Services\Sales;

use App\Models\Price;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Read-only effective pricing overlay. Never changes prices.* fields.
 * Existing SalesControl campaigns take precedence in SalesCampaignService.
 * Dates are evaluated per call, rather than relying on a cron job.
 */
class BulkDiscountService
{
    private static array $items = [];
    private static ?bool $tablesExist = null;

    public function available(): bool
    {
        if (self::$tablesExist === null) {
            self::$tablesExist = Schema::hasTable('bulk_discount_campaigns')
                && Schema::hasTable('bulk_discount_items');
        }
        return self::$tablesExist;
    }

    private function assignment(int $productId): ?object
    {
        if ($productId <= 0 || !$this->available()) {
            return null;
        }
        if (!array_key_exists($productId, self::$items)) {
            self::$items[$productId] = DB::table('bulk_discount_items as bi')
                ->join('bulk_discount_campaigns as bc', 'bc.id', '=', 'bi.campaign_id')
                ->where('bi.product_id', $productId)
                ->select([
                    'bi.timer_override',
                    'bi.starts_at as item_starts_at',
                    'bi.ends_at as item_ends_at',
                    'bc.starts_at as campaign_starts_at',
                    'bc.ends_at as campaign_ends_at',
                    'bc.discount_type',
                    'bc.discount_value',
                    'bc.round_step',
                    'bc.round_mode',
                    'bc.enabled',
                ])->first();
        }
        return self::$items[$productId];
    }

    private function active(?object $row): bool
    {
        if (!$row || !(bool) $row->enabled) {
            return false;
        }
        $override = (bool) $row->timer_override;
        $starts = $override ? $row->item_starts_at : $row->campaign_starts_at;
        $ends = $override ? $row->item_ends_at : $row->campaign_ends_at;
        $now = now();
        return (!$starts || $now->greaterThanOrEqualTo($starts))
            && (!$ends || $now->lessThan($ends));
    }

    public function activeForProduct(int $productId): bool
    {
        return $this->active($this->assignment($productId));
    }


    /** Correlated subquery for existing Product discount/without-discount scopes. */
    public static function activeProductExists($query): void
    {
        $startExpr = 'CASE WHEN abdi.timer_override = 1 THEN abdi.starts_at ELSE abdc.starts_at END';
        $endExpr = 'CASE WHEN abdi.timer_override = 1 THEN abdi.ends_at ELSE abdc.ends_at END';
        $query->selectRaw('1')
            ->from('bulk_discount_items as abdi')
            ->join('bulk_discount_campaigns as abdc', 'abdc.id', '=', 'abdi.campaign_id')
            ->whereColumn('abdi.product_id', 'products.id')
            ->where('abdc.enabled', true)
            ->whereRaw("({$startExpr} IS NULL OR {$startExpr} <= ?)", [now()])
            ->whereRaw("({$endExpr} IS NULL OR {$endExpr} > ?)", [now()])
            ->whereExists(function ($prices) {
                $prices->selectRaw('1')->from('prices as ap')
                    ->whereColumn('ap.product_id', 'products.id')
                    ->whereNull('ap.deleted_at')->where('ap.stock', '>', 0)
                    ->where(function ($q) {
                        $q->where('abdc.discount_type', 'percent')
                            ->orWhere(function ($fixed) {
                                $fixed->where('abdc.discount_type', 'fixed_price')
                                    ->whereRaw('ap.regular_price > abdc.discount_value * 10');
                            });
                    });
            });
    }

    /**
     * Same pricing function used by checkout and the admin preview.
     * All returned amounts are TOMAN, respecting existing per-product rounding.
     */
    public static function calculatePrice(
        Price $price, string $type, int $value,
        int $roundStep = 0, string $roundMode = 'nearest'
    ): ?float {
        $regular = max(0.0, (float) $price->regularPrice());
        if ($regular <= 0) { return null; }

        // A cheaper active per-variant manual discount must remain untouched.
        $manual = $regular;
        // ARIYA_DIRECT_DISCOUNT_EFFECTIVE_V1: direct fixed discounts must
        // survive even when legacy percent is zero. Do not call salePrice()
        // here; that would recurse into this very bulk-price calculation.
        $directFixed = $price->activeDirectFixedPrice();
        if ($directFixed !== null) {
            $manual = min($regular, $directFixed);
        } elseif ((int) $price->discount > 0
            && (!$price->discount_expire_at || $price->discount_expire_at->gt(now()))) {
            $manual = min($regular, max(0.0, (float) $price->toRoundPrice($price->discount_price)));
        }

        if ($type === 'percent' && $value >= 1 && $value <= 99) {
            $raw = max(0, (float) $price->regular_price) * (100 - $value) / 100;
            $candidate = max(1.0, (float) $price->toRoundPrice($raw));
        } elseif ($type === 'fixed_price' && $value > 0) {
            $candidate = (float) $value;
        } else {
            return null;
        }

        if ($roundStep > 0 && in_array($roundStep, [100,500,1000,5000,10000], true)) {
            $units = $candidate / $roundStep;
            $candidate = match ($roundMode) {
                'up' => ceil($units) * $roundStep,
                'down' => floor($units) * $roundStep,
                default => floor($units + 0.5) * $roundStep,
            };
            // Never make a nonzero product free after rounding down.
            $candidate = max(1.0, $candidate);
        }
        return min($regular, $manual, $candidate);
    }

    /** Return final sale price in TOMAN; null means no bulk override. */
    public function price(Price $price): ?float
    {
        $row = $this->assignment((int) $price->product_id);
        if (!$this->active($row)) { return null; }
        return self::calculatePrice(
            $price, (string) $row->discount_type, (int) $row->discount_value,
            (int) ($row->round_step ?? 0), (string) ($row->round_mode ?? 'nearest')
        );
    }
}
