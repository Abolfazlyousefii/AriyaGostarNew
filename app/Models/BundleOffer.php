<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BundleOffer extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function variants()
    {
        return $this->hasMany(BundleOfferVariant::class);
    }

    public function scopeLive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) { $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()); })
            ->where('ends_at', '>', now());
    }

    public function isLive(): bool
    {
        return $this->is_active && (!$this->starts_at || !$this->starts_at->isFuture())
            && $this->ends_at && $this->ends_at->isFuture();
    }
}
