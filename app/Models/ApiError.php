<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiError extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'error_status',
        'error_message',
        'request',
        'stack_trace',
        'log'
    ];
}
