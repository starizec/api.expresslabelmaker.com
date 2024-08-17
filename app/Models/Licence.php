<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Domain;

class Licence extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'domain_id',
        'valid_from',
        'valid_until',
        'usage',
        'usage_limit',
        'licence_uid',
        'licence_type_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function domain()
    {
        return $this->belongsTo(Domain::class);
    }
}
