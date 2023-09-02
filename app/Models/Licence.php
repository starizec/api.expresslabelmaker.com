<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
