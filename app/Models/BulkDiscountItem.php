<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BulkDiscountItem extends Model
{
    protected $guarded = ['id'];
    protected $casts = [
        'timer_override' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function campaign()
    {
        return $this->belongsTo(BulkDiscountCampaign::class, 'campaign_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
