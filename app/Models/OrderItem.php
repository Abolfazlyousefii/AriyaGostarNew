<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'notes' => 'array',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function get_price()
    {
        return $this->belongsTo(Price::class, 'price_id', 'id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function realPrice()
    {
        return $this->real_price;
    }

    /**
     * قیمت واحد قبل از تخفیف.
     */
    public function originalUnitPrice(): int
    {
        $realPrice = (int) $this->real_price;
        $price = (int) $this->price;

        return max($realPrice > 0 ? $realPrice : $price, 0);
    }

    /**
     * قیمت واحد نهایی بعد از تخفیف.
     *
     * برای سازگاری با سفارش‌های قدیمی:
     * - اگر price قبلاً کمتر از real_price ذخیره شده باشد، همان price معتبر است.
     * - اگر price و real_price برابر باشند ولی discount وجود داشته باشد، تخفیف از روی فیلدها محاسبه می‌شود.
     */
    public function finalUnitPrice(): int
    {
        $base = $this->originalUnitPrice();
        $storedPrice = max((int) $this->price, 0);
        $discount = max((float) $this->discount, 0);
        $discountType = strtolower(trim((string) ($this->discount_type ?? 'percent')));

        if ($discount <= 0) {
            return $storedPrice > 0 ? $storedPrice : $base;
        }

        // سفارش‌های قدیمی معمولاً price را از قبل بعد از تخفیف ذخیره کرده‌اند.
        if ($storedPrice > 0 && $storedPrice < $base) {
            return $storedPrice;
        }

        if ($discountType === 'percent') {
            $percent = min($discount, 100);

            return max((int) round($base * ((100 - $percent) / 100)), 0);
        }

        // fixed / amount و سایر حالت‌های مبلغ ثابت
        return max((int) round($base - $discount), 0);
    }

    public function originalLineAmount(): int
    {
        return $this->originalUnitPrice() * max((int) $this->quantity, 0);
    }

    public function finalLineAmount(): int
    {
        return $this->finalUnitPrice() * max((int) $this->quantity, 0);
    }

    public function discountAmount(): int
    {
        return max($this->originalLineAmount() - $this->finalLineAmount(), 0);
    }

    public function discountLabel(): string
    {
        $discount = max((float) $this->discount, 0);

        if ($discount <= 0) {
            return '۰';
        }

        if (strtolower(trim((string) ($this->discount_type ?? 'percent'))) === 'percent') {
            $value = rtrim(rtrim(number_format($discount, 2, '.', ''), '0'), '.');

            return $value . '٪';
        }

        return number_format((int) round($discount)) . ' تومان';
    }

    /**
     * Snapshot وضعیت انبار/ارسال که در ستون notes ذخیره می‌شود.
     *
     * ساختار پیشنهادی:
     * notes.fulfillment.*
     */
    public function fulfillmentNotes(): array
    {
        $notes = $this->normalizeNotesValue($this->notes);

        if ($notes === []) {
            $notes = $this->normalizeNotesValue($this->getRawOriginal('notes'));
        }

        if (isset($notes['fulfillment'])) {
            $fulfillment = $this->normalizeNotesValue($notes['fulfillment']);

            if ($fulfillment !== []) {
                return $fulfillment;
            }
        }

        if (isset($notes['inventory'])) {
            $inventory = $this->normalizeNotesValue($notes['inventory']);

            if ($inventory !== []) {
                return $inventory;
            }
        }

        return $notes;
    }

    private function normalizeNotesValue($value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (!is_string($value)) {
            return [];
        }

        $value = trim($value);

        if ($value === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        if (is_string($decoded)) {
            $decoded = json_decode($decoded, true);
        }

        return is_array($decoded) ? $decoded : [];
    }

    public function hasFulfillmentNote(): bool
    {
        $notes = $this->fulfillmentNotes();

        return collect([
            'status',
            'message',
            'customer_message',
            'reason',
            'shippable',
            'can_ship',
            'is_unavailable',
            'removed',
            'deleted',
            'paid_quantity',
            'approved_quantity',
            'unsent_quantity',
            'returned_quantity',
            'returned_amount',
            'refund_amount',
            'refunded_amount',
        ])->contains(fn ($key) => array_key_exists($key, $notes));
    }

    public function fulfillmentStatus(): ?string
    {
        $status = strtolower(trim((string) ($this->fulfillmentNotes()['status'] ?? '')));

        return $status !== '' ? $status : null;
    }

    public function fulfillmentStatusLabel(): ?string
    {
        $status = $this->fulfillmentStatus();

        $labels = [
            'removed'       => 'حذف‌شده از ارسال',
            'deleted'       => 'حذف‌شده از ارسال',
            'unavailable'   => 'ناموجود و غیرقابل ارسال',
            'out_of_stock'  => 'ناموجود و غیرقابل ارسال',
            'not_shippable' => 'غیرقابل ارسال',
            'not_shipped'   => 'ارسال‌نشده',
            'rejected'      => 'تأیید نشده',
            'cancelled'     => 'لغو شده',
            'canceled'      => 'لغو شده',
            'returned'      => 'مرجوع‌شده',
            'adjusted'      => 'اصلاح‌شده توسط انبار',
            'approved'      => 'تأیید شده',
        ];

        return $status ? ($labels[$status] ?? 'وضعیت ویژه') : null;
    }

    public function fulfillmentMessage(): ?string
    {
        $notes = $this->fulfillmentNotes();
        $message = $notes['customer_message'] ?? $notes['message'] ?? $notes['reason'] ?? null;
        $message = is_scalar($message) ? trim((string) $message) : '';

        return $message !== '' ? $message : null;
    }

    public function isUnavailableForShipping(): bool
    {
        if (($this->hasNoteKey('shippable') && !$this->noteBoolean('shippable', true))
            || ($this->hasNoteKey('can_ship') && !$this->noteBoolean('can_ship', true))) {
            return true;
        }

        if ($this->noteBoolean('is_unavailable') || $this->noteBoolean('removed') || $this->noteBoolean('deleted')) {
            return true;
        }

        return in_array($this->fulfillmentStatus(), [
            'removed',
            'deleted',
            'unavailable',
            'out_of_stock',
            'not_shippable',
            'not_shipped',
            'rejected',
            'cancelled',
            'canceled',
            'returned',
        ], true);
    }

    public function isPartiallyAdjusted(): bool
    {
        return $this->unsentQuantity() > 0 && $this->approvedQuantity() > 0;
    }

    public function paidQuantity(): int
    {
        return max(0, min(
            $this->noteInteger('paid_quantity', (int) $this->quantity),
            (int) $this->quantity
        ));
    }

    public function approvedQuantity(): int
    {
        if ($this->hasNoteKey('approved_quantity')) {
            return max(0, min($this->noteInteger('approved_quantity'), $this->paidQuantity()));
        }

        return $this->isUnavailableForShipping() ? 0 : $this->paidQuantity();
    }

    public function unsentQuantity(): int
    {
        if ($this->hasNoteKey('unsent_quantity')) {
            return max(0, min($this->noteInteger('unsent_quantity'), $this->paidQuantity()));
        }

        return max($this->paidQuantity() - $this->approvedQuantity(), 0);
    }

    public function returnedQuantity(): int
    {
        return max(0, min($this->noteInteger('returned_quantity', 0), $this->paidQuantity()));
    }

    /**
     * مبالغ وضعیت‌ها از قیمت واقعی بعد از تخفیف محاسبه می‌شوند.
     * بنابراین برای paid/approved/unsent نیازی به وارد کردن مبلغ در notes نیست.
     */
    public function paidAmount(): int
    {
        return $this->finalUnitPrice() * $this->paidQuantity();
    }

    public function approvedAmount(): int
    {
        return $this->finalUnitPrice() * $this->approvedQuantity();
    }

    public function unsentAmount(): int
    {
        return $this->finalUnitPrice() * $this->unsentQuantity();
    }

    public function returnedAmount(): int
    {
        foreach (['returned_amount', 'refunded_amount', 'refund_amount'] as $key) {
            if ($this->hasNoteKey($key)) {
                return max(0, $this->noteInteger($key));
            }
        }

        return $this->finalUnitPrice() * $this->returnedQuantity();
    }

    private function hasNoteKey(string $key): bool
    {
        return array_key_exists($key, $this->fulfillmentNotes());
    }

    private function noteBoolean(string $key, bool $default = false): bool
    {
        if (!$this->hasNoteKey($key)) {
            return $default;
        }

        $value = $this->fulfillmentNotes()[$key];

        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        if (is_string($value)) {
            return in_array(strtolower(trim($value)), ['1', 'true', 'yes', 'on'], true);
        }

        return $default;
    }

    private function noteInteger(string $key, int $default = 0): int
    {
        $value = $this->fulfillmentNotes()[$key] ?? $default;

        if (is_string($value)) {
            $value = str_replace([',', ' '], '', $value);
        }

        return is_numeric($value) ? (int) $value : $default;
    }
}
