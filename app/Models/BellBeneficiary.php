<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BellBeneficiary extends Model
{
    protected $table = 'bell_beneficiaries';

    protected $fillable = [
        'user_id',
        'account_number',
        'account_name',
        'bank_code',
        'bank_name',
        'is_favorite',
        'transfer_count',
        'last_used_at',
    ];

    protected $casts = [
        'is_favorite' => 'boolean',
        'transfer_count' => 'integer',
        'last_used_at' => 'datetime',
    ];

    /**
     * Get the user that owns the beneficiary
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

