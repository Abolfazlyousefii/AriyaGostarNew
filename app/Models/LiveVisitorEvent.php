<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiveVisitorEvent extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'payload' => 'array',
        'occurred_at' => 'datetime',
    ];

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(LiveVisitor::class, 'live_visitor_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
