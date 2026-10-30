<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BulkDiscountCampaign extends Model
{
    protected $guarded = ['id'];
    protected $casts = [
        'enabled' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(BulkDiscountItem::class, 'campaign_id');
    }
}
