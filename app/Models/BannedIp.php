<?php
// app/Models/BannedIp.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BannedIp extends Model
{
    protected $fillable = [
        'ip_address',
        'attempt_count',
        'reason',
        'banned_at',
    ];

    protected $casts = [
        'banned_at' => 'datetime',
    ];
}