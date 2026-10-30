<?php

namespace App\Services\Sales;

use App\Models\Product;
use App\Models\SalesControl;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SalesControlService
{
    private static ?bool $tableExists = null;

    public function available(): bool
    {
        if (self::$tableExists === null) {
            self::$tableExists = Schema::hasTable('sales_controls');
        }

        return self::$tableExists;
    }
public function centralStock(Product $product): ?int
    {
        try {
            $inventory = DB::connection('inv');

            $warehouseId = $inventory
                ->table('warehouses')
                ->where('type', 'central')
                ->orderBy('id')
                ->value('id');

            if (!$warehouseId) {
                return null;
            }

            return (int) $inventory
                ->table('warehouse_stocks')
                ->where('warehouse_id', $warehouseId)
                ->where('product_id', $product->id)
                ->sum('quantity');
        } catch (Throwable $e) {
            report($e);

            return null;
        }
    }

    public function committedQuantity(SalesControl $control): int
    {
        $query = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('order_items.product_id', $control->product_id)
            ->where('orders.status', '!=', 'canceled');

        if ($control->counter_started_at) {
            $query->where('orders.created_at', '>=', $control->counter_started_at);
        }

        return max(0, (int) $query->sum('order_items.quantity'));
    }

    public function remaining(SalesControl $control): ?int
    {
        if (!$control->enabled) {
            return null;
        }

        $limits = [];

        if ($control->sale_limit !== null) {
            $limits[] = (int) $control->sale_limit;
        }

        $discountPlacementActive =
            $control->show_in_discount_box
            && (
                !$control->discount_enabled
                || $control->discountIsActive()
            );

        if (
            $discountPlacementActive
            && $control->discount_box_limit !== null
        ) {
            $limits[] = (int) $control->discount_box_limit;
        }

        if (
            $control->show_in_new_arrivals
            && $control->new_arrivals_limit !== null
        ) {
            $limits[] = (int) $control->new_arrivals_limit;
        }

        if (!$limits) {
            return null;
        }

        return max(
            min($limits)
            - $this->committedQuantity($control),
            0
        );
    }
    public function discountBoxRemaining(SalesControl $control): ?int
    {
        if (
            !$control->show_in_discount_box
            || $control->discount_box_limit === null
        ) {
            return null;
        }

        return max(
            (int) $control->discount_box_limit
            - $this->committedQuantity($control),
            0
        );
    }

    public function newArrivalsRemaining(SalesControl $control): ?int
    {
        if (
            !$control->show_in_new_arrivals
            || $control->new_arrivals_limit === null
        ) {
            return null;
        }

        return max(
            (int) $control->new_arrivals_limit
            - $this->committedQuantity($control),
            0
        );
    }

    public function validateQuantity(
        int $productId,
        int $requestedQuantity
    ): array {
        // ARIYA_SALES_CONTROL_TABLE_GUARD_V1
        if (!$this->available()) {
            return [
                'status' => true,
                'message' => 'ok',
                'remaining' => null,
            ];
        }
        $requestedQuantity = max($requestedQuantity, 0);

        $control = SalesControl::query()
            ->where('product_id', $productId)
            ->where('enabled', true)
            ->whereNotNull('sale_limit')
            ->first();

        if (!$control) {
            return [
                'status' => true,
                'message' => 'ok',
                'remaining' => null,
            ];
        }

        $remaining = $this->remaining($control);

        if ($remaining === null || $requestedQuantity <= $remaining) {
            return [
                'status' => true,
                'message' => 'ok',
                'remaining' => $remaining,
            ];
        }

        return [
            'status' => false,
            'message' => $this->limitMessage($control, $remaining),
            'remaining' => $remaining,
        ];
    }

    public function validateQuantities(array $productQuantities): array
    {
        foreach ($productQuantities as $productId => $quantity) {
            $status = $this->validateQuantity(
                (int) $productId,
                (int) $quantity
            );

            if (!$status['status']) {
                return $status;
            }
        }

        return [
            'status' => true,
            'message' => 'ok',
        ];
    }

    /**
     * Final checkout guard.
     *
     * SalesControl rows are locked until the Order and its OrderItems are
     * created. This prevents two concurrent checkout requests from consuming
     * the same remaining allocation.
     */
    public function withLockedLimits(
        array $productQuantities,
        Closure $callback
    ): array {
        if (!$this->available()) {
            return [
                'status' => true,
                'message' => 'ok',
                'value' => $callback(),
            ];
        }
        $productQuantities = collect($productQuantities)
            ->mapWithKeys(function ($quantity, $productId) {
                return [(int) $productId => max((int) $quantity, 0)];
            })
            ->filter(fn ($quantity) => $quantity > 0)
            ->all();

        if (!$productQuantities) {
            return [
                'status' => true,
                'message' => 'ok',
                'value' => $callback(),
            ];
        }

        return DB::transaction(function () use (
            $productQuantities,
            $callback
        ) {
            $controls = SalesControl::query()
                ->whereIn('product_id', array_keys($productQuantities))
                ->where('enabled', true)
                ->whereNotNull('sale_limit')
                ->lockForUpdate()
                ->get()
                ->keyBy('product_id');

            foreach ($productQuantities as $productId => $quantity) {
                /** @var SalesControl|null $control */
                $control = $controls->get($productId);

                if (!$control) {
                    continue;
                }

                $remaining = $this->remaining($control);

                if ($remaining !== null && $quantity > $remaining) {
                    return [
                        'status' => false,
                        'message' => $this->limitMessage(
                            $control,
                            $remaining
                        ),
                        'remaining' => $remaining,
                        'value' => null,
                    ];
                }
            }

            return [
                'status' => true,
                'message' => 'ok',
                'value' => $callback(),
            ];
        }, 3);
    }

    public function status(SalesControl $control): string
    {
        if (!$control->enabled) {
            return 'غیرفعال';
        }

        if (
            $control->sale_limit !== null
            && $this->remaining($control) <= 0
        ) {
            return 'سهم فروش تمام شده';
        }

        if ($control->discount_enabled) {
            if (
                $control->discount_starts_at
                && $control->discount_starts_at->isFuture()
            ) {
                return 'در انتظار شروع تخفیف';
            }

            if (
                $control->discount_ends_at
                && $control->discount_ends_at->isPast()
            ) {
                return 'تخفیف تمام شده';
            }

            if ($control->discountIsActive()) {
                return 'فعال با تخفیف';
            }
        }

        return 'فعال';
    }

    private function limitMessage(
        SalesControl $control,
        int $remaining
    ): string {
        $control->loadMissing('product');

        $title = $control->product?->title
            ?: ('محصول #' . $control->product_id);

        if ($remaining <= 0) {
            return 'سهم فروش محصول «' . $title . '» به پایان رسیده است.';
        }

        return 'از سهم فروش محصول «'
            . $title
            . '» فقط '
            . number_format($remaining)
            . ' عدد باقی مانده است.';
    }
}