<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BellWebhook extends Model
{
    protected $table = 'bell_webhooks';

    protected $fillable = [
        'event',
        'payload',
        'headers',
        'received_at',
        'processed',
        'error',
    ];

    protected $casts = [
        'payload' => 'array',
        'headers' => 'array',
        'received_at' => 'datetime',
        'processed' => 'boolean',
    ];
}



