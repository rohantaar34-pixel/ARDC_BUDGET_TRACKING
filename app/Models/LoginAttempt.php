<?php
// app/Models/LoginAttempt.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginAttempt extends Model
{
    protected $fillable = [
        'ip_address',
        'email',
        'attempts',
        'last_attempt_at',
        'lockout_until',
    ];

    protected $casts = [
        'last_attempt_at' => 'datetime',
        'lockout_until' => 'datetime',
    ];
}
