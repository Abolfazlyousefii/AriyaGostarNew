<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceAttachment extends Model {
    protected $connection = 'inv';

    protected $fillable = [
        'invoice_id',
        'type',
        'file_path',
        'title',
    ];

    public function invoice(): BelongsTo {
        return $this->belongsTo(Invoice::class);
    }
}
