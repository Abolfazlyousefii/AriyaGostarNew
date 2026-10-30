<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class MembershipRequest extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $guarded = ['id'];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    // MEMBERSHIP_DOCUMENT_IMAGE_URL_V1
    public function documentImageUrl(): ?string
    {
        $path = ltrim((string) $this->document_image, '/');

        if ($path === '') {
            return null;
        }

        /*
         * New membership documents are stored on Arvan.
         */
        try {
            if (Storage::disk('arvan')->exists($path)) {
                return Storage::disk('arvan')->url($path);
            }
        } catch (\Throwable $e) {
            // Fall back to legacy local storage.
        }

        /*
         * Keep compatibility with older documents stored in public/uploads.
         */
        try {
            if (Storage::disk('uploads')->exists($path)) {
                return Storage::disk('uploads')->url($path);
            }
        } catch (\Throwable $e) {
            //
        }

        return null;
    }
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function approvedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_user_id');
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }
}
