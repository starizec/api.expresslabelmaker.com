<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Courier extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'country_id'];

    public function country(): HasOne
    {
        return $this->hasOne(Country::class, 'id', 'country_id');
    }

}
