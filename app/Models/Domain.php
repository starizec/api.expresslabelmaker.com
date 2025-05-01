<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Notifications\NewDomainNotification;
use Illuminate\Notifications\Notifiable;

class Domain extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sendNewDomainNotification($domain)
    {   
        $this->user->notify(new NewDomainNotification($domain));
    }
}
