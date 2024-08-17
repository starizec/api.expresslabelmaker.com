<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

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

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
