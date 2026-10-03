<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaOutbox extends Model
{
    protected $table = 'wa_outbox';

    protected $fillable = ['phone', 'message', 'group_key', 'status', 'error', 'sent_at'];

    protected $casts = [
        'sent_at' => 'datetime',
    ];
}
