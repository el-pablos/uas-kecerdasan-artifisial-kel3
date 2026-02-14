<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Integration extends Model
{
    protected $fillable = [
        'name', 'type', 'command', 'schedule', 'status',
        'last_run_at', 'last_message', 'config',
    ];

    protected $casts = [
        'config' => 'array',
        'last_run_at' => 'datetime',
    ];
}
