<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesControl extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'enabled' => 'boolean',
        'sale_limit' => 'integer',
        'counter_started_at' => 'datetime',

        'discount_enabled' => 'boolean',
        'discount_value' => 'integer',
        'discount_starts_at' => 'datetime',
        'discount_ends_at' => 'datetime',

        'show_in_discount_box' => 'boolean',
        'discount_box_limit' => 'integer',
        'discount_box_order' => 'integer',

        'show_in_new_arrivals' => 'boolean',
        'new_arrivals_limit' => 'integer',
        'new_arrivals_order' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeEnabled($query)
    {
        return $query->where('enabled', true);
    }

    public function discountIsActive(): bool
    {
        if (!$this->enabled || !$this->discount_enabled) {
            return false;
        }

        $now = now();

        if ($this->discount_starts_at && $this->discount_starts_at->isFuture()) {
            return false;
        }

        if ($this->discount_ends_at && $this->discount_ends_at->lte($now)) {
            return false;
        }

        return true;
    }
}