<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;

class InventoryInboundEvent extends Model
{
    protected $table = 'inventory_inbound_events';

    protected $fillable = [
        'event_id',
        'source',
        'event_type',
        'payload_hash',
        'payload',
        'status',
        'processed_at',
        'error',
    ];

    protected $casts = [
        'payload' => 'array',
        'processed_at' => 'datetime',
    ];
}