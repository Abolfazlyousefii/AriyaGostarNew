<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;

class InventoryInboundEvent extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['payload' => 'array', 'processed_at' => 'datetime'];
}
