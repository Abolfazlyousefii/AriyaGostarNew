<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BundleOfferVariant extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['required' => 'boolean'];

    public function offer()
    {
        return $this->belongsTo(BundleOffer::class, 'bundle_offer_id');
    }

    public function price()
    {
        return $this->belongsTo(Price::class);
    }
}
