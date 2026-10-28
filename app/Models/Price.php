<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

class Price extends Model {
    use SoftDeletes;

    protected $guarded = [ 'id' ];

    protected $casts = [
        'stock_sync_enabled' => 'boolean',
        'stock_synced_at'    => 'datetime',
    ];

    protected static function booted() {
        static::created(function ( Price $price ) {
            if ( empty($price->variant_code) ) {
                $price->forceFill([
                    'variant_code' => self::generateInventoryVariantCode($price->id),
                ])
                    ->saveQuietly();
            }
        });
    }

    public static function generateInventoryVariantCode( int $id ): string {
        return config('warehouse-inventory.variant_prefix', 'VAR-') . str_pad((string) $id, 8, '0', STR_PAD_LEFT);
    }

    /**
     * Backward-compatible alias for older calls.
     */
    public static function generateInventoryStockCode( int $id ): string {
        return self::generateInventoryVariantCode($id);
    }

    public function product() {
        return $this->belongsTo(Product::class);
    }

    public function file() {
        return $this->morphOne(File::class, 'fileable');
    }

    public function image() {
        return $this->belongsTo(Gallery::class, 'image_id', 'id');
    }

    public function orderItems() {
        return $this->hasMany(OrderItem::class);
    }

    public function get_attributes() {
        return $this->belongsToMany(Attribute::class);
    }

    public function getAttributesName() {
        if ( $this->product->isDownload() ) {
            return $this->file->title;
        }

        $title      = '';
        $attributes = $this->get_attributes()
            ->join('attribute_groups', 'attributes.attribute_group_id', '=', 'attribute_groups.id')
            ->orderBy('attribute_groups.ordering')
            ->select('attributes.*')
            ->get();

        foreach ( $attributes as $attribute ) {
            $title .= ' ' . $attribute->group->name . ' : ' . $attribute->name . ( $attributes->last() == $attribute ? '' : '،' );
        }

        return $title;
    }

    public function getAttributesValue() {
        if ( $this->product->isDownload() ) {
            return $this->file->title;
        }

        $title      = '';
        $attributes = $this->get_attributes()
            ->join('attribute_groups', 'attributes.attribute_group_id', '=', 'attribute_groups.id')
            ->orderBy('attribute_groups.ordering')
            ->select('attributes.*')
            ->get();

        foreach ( $attributes as $attribute ) {
            $title .= $attribute->name . ( $attributes->last() == $attribute ? '' : '،' );
        }

        return $title;
    }

    public function getDiscountExpireAtAttribute( $value ) {
        return $value ? Carbon::parse($value) : null;
    }

    public function price() {
        return $this->toRoundPrice($this->price);
    }

    public function tomanPrice() {
        if ( $this->product->currency ) {
            return $this->price * $this->product->currency->amount;
        }

        return $this->price;
    }

    public function changes() {
        return $this->hasMany(PriceChange::class, 'price_id');
    }

    public function createFile( $title, $file, $status ) {
        $filename = date("Y-m-d") . '/' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->storeAs('product-files', $filename, 'downloads');

        $this->file()
            ->create([
                'title'  => $title,
                'file'   => $filename,
                'disk'   => 'downloads',
                'size'   => $file->getSize(),
                'status' => $status,
            ]);
    }

    public function updateFile( $title, $file, $status ) {
        if ( $file ) {
            $filename = date("Y-m-d") . '/' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('product-files', $filename, 'downloads');
            $size = $file->getSize();
        }
        else {
            $filename = $this->file->file;
            $size     = $this->file->size;
        }

        $this->file()
            ->update([
                'title'  => $title,
                'file'   => $filename,
                'disk'   => 'downloads',
                'size'   => $size,
                'status' => $status,
            ]);
    }

    public function hasStock( $quantity, $with_attributes = false ) {
        $variant = (string) $this->variant_name;
        if ($with_attributes && $variant === '' && !$this->product->isDownload()) $variant = $this->getAttributesValue();
        return \App\Services\Inventory\CartStockRules::evaluate([
            'title' => (string) $this->product->title, 'variant' => $variant,
            'physical' => !$this->product->isDownload(), 'quantity' => (int) $quantity,
            'minimum' => $this->cart_min, 'maximum' => $this->cart_max,
        ], (int) $this->stock);
    }

    public function isDownloadable() {
        if ( $this->file && $this->file->status == 'inactive' ) {
            return false;
        }

        if ( $this->price == 0 ) {
            return true;
        }

        if ( auth()->check() ) {
            return auth()
                       ->user()
                       ->hasBought($this)
                   || auth()
                       ->user()
                       ->can('products.update');
        }

        return false;
    }

    public function downloadLink() {
        $time = Carbon::now()
            ->addHours(5)
            ->getTimestamp();

        $hash = Hash::make(config('app.key') . $time . $this->id);

        $link = Route::has('front.products.download') ? route('front.products.download', [ 'price' => $this ]) : '#';

        $link .= "?mac=$hash&time=$time";

        return $link;
    }

    // ARIYA_DIRECT_DISCOUNT_EFFECTIVE_V1
    // Standalone fixed discounts are in Toman; never depend on the legacy
    // prices.discount column (ERP imports can legitimately reset it to zero).
    // Return null for inactive/invalid direct discounts, so regular prices
    // and old percent discounts remain backward-compatible.
    public function activeDirectFixedPrice(): ?float {
        $mode = (string) ($this->direct_discount_mode ?? '');
        if (!in_array($mode, ['fixed_amount', 'fixed_final'], true)) {
            return null;
        }
        if ($this->discount_expire_at && ! $this->discount_expire_at->gt(now())) {
            return null;
        }

        $regular = (int) round($this->regularPrice());
        $value = (int) ($this->direct_discount_value ?? 0);
        if ($regular <= 0 || $value <= 0) {
            return null;
        }
        try {
            $final = \App\Services\Sales\DirectDiscountPricing::finalToman(
                $regular, $mode, $value
            );
        } catch (\InvalidArgumentException $e) {
            return null;
        }
        return $final > 0 && $final < $regular ? (float) $final : null;
    }

    private function legacyPercentDiscountActive(): bool {
        return (int) $this->discount > 0
            && (is_null($this->discount_expire_at) || $this->discount_expire_at->gt(now()));
    }

    // Existing SalesControl/Bulk campaign still participates in pricing.
    // In the presence of a fixed direct price, pick the lower valid price;
    // this also protects that direct price from an ERP repricing or an overlay.
    private function effectiveSalePrice(): float {
        $regular = (float) $this->regularPrice();
        $direct = $this->activeDirectFixedPrice();
        $campaign = app(\App\Services\Sales\SalesCampaignService::class)->campaignPrice($this);
        if ($campaign !== null) {
            return $direct === null ? (float) $campaign : min((float) $campaign, $direct);
        }
        if ($direct !== null) {
            return $direct;
        }
        return $this->legacyPercentDiscountActive()
            ? (float) $this->toRoundPrice($this->discount_price)
            : $regular;
    }

    public function hasDiscount() {
        return $this->effectiveSalePrice() < (float) $this->regularPrice();
    }

    public function discountPrice() {
        return $this->effectiveSalePrice();
    }

    public function regularPrice() {
        return $this->toRoundPrice($this->regular_price);
    }

    public function discount() {
        $direct = $this->activeDirectFixedPrice();
        $campaign = app(\App\Services\Sales\SalesCampaignService::class)->campaignPrice($this);
        if ($direct !== null || $campaign !== null) {
            return \App\Services\Sales\DirectDiscountPricing::effectivePercent(
                (int) round($this->regularPrice()),
                (int) round($this->effectiveSalePrice())
            );
        }
        return $this->legacyPercentDiscountActive() ? (int) $this->discount : 0;
    }

    public function salePrice() {
        return $this->effectiveSalePrice();
    }
    public function toRoundPrice( $price ) {
        $price           = $price / 10;
        $rounding_amount = $this->product->rounding_amount;

        if ( $rounding_amount == 'default' ) {
            $rounding_amount = option('default_rounding_amount', 'no');
        }

        $rounding_type = $this->product->rounding_type;

        if ( $rounding_type == 'default' ) {
            $rounding_type = option('default_rounding_type', 'close');
        }

        switch ( $rounding_amount ) {
            case "100":
            case "1000":
            case "10000":
            case "100000":
            {
                if ( $rounding_type == 'up' ) {
                    $price = ceil($price / $rounding_amount) * $rounding_amount;
                }
                else if ( $rounding_type == 'down' ) {
                    $price = floor($price / $rounding_amount) * $rounding_amount;
                }
                else {
                    $price = round($price / $rounding_amount) * $rounding_amount;
                }
                break;
            }
        }

        return (float) $price;
    }

    public function pendingToSend() {
        return $this->orderItems()
            ->whereHas('order', function ( $q ) {
                $q->notCompleted();
            })
            ->whereHas('product', function ( $q3 ) {
                $q3->physical();
            })
            ->sum('order_items.quantity');
    }

    public function scopeInStock( $query ) {
        return $query->where('stock', '>', 0);
    }

    public function inventoryApiCode(): string {
        if ( !empty($this->variant_code) ) {
            return (string) $this->variant_code;
        }

        return self::generateInventoryVariantCode((int) $this->id);
    }
}
