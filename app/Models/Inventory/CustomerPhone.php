<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerPhone extends Model {
    protected $connection = 'inv';

    protected $fillable = [ 'customer_id', 'phone', 'is_primary' ];

    protected $casts = [ 'is_primary' => 'boolean' ];

    public function customer(): BelongsTo {
        return $this->belongsTo(Customer::class);
    }
}
