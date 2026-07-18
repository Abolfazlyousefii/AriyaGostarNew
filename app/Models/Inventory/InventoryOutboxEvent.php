<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;

class InventoryOutboxEvent extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['payload' => 'array', 'next_attempt_at' => 'datetime', 'sent_at' => 'datetime'];
}
