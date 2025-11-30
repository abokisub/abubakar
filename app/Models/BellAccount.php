<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BellAccount extends Model
{
    protected $table = 'bell_accounts';

    protected $fillable = [
        'user_id', // unsignedInteger to match user table
        'external_id',
        'account_number',
        'bank_code',
        'bank_name',
        'currency',
        'status',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Get the user that owns the account
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}

