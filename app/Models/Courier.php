<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Courier extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'country_id', 'short'];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

}
