<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class BellKyc extends Model
{
    protected $table = 'bell_kyc';

    protected $fillable = [
        'user_id',
        'bvn_encrypted',
        'kyc_status',
        'kyc_level',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    /**
     * Get the user that owns the KYC record
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Set BVN (encrypt before storing)
     */
    public function setBvnAttribute($value)
    {
        if ($value) {
            $this->attributes['bvn_encrypted'] = Crypt::encryptString($value);
        }
    }

    /**
     * Get BVN (decrypt when retrieving)
     */
    public function getBvnAttribute()
    {
        if (isset($this->attributes['bvn_encrypted'])) {
            try {
                return Crypt::decryptString($this->attributes['bvn_encrypted']);
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
    }
}



