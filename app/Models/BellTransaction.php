<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BellTransaction extends Model
{
    protected $table = 'bell_transactions';

    protected $fillable = [
        'user_id',
        'external_id',
        'type',
        'amount',
        'currency',
        'status',
        'reference',
        'idempotency_key',
        'response',
        'description',
        'source_account_number',
        'source_account_name',
        'source_bank_code',
        'source_bank_name',
        'destination_account_number',
        'destination_account_name',
        'destination_bank_code',
        'destination_bank_name',
        'charge',
        'net_amount',
        'session_id',
        'transaction_type_name',
        'completed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'charge' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'response' => 'array',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the user that owns the transaction
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}



