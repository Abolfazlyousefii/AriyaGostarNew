<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LiveVisitor extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'metadata' => 'array',
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'cart_total' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(LiveVisitorEvent::class);
    }

    public function scopeOnline($query, int $minutes = 5)
    {
        return $query->where('last_activity_at', '>=', now()->subMinutes($minutes));
    }
}
