<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'country',
        'courier',
        'location_id',
        'place',
        'postal_code',
        'street',
        'house_number',
        'lon',
        'lat',
        'name',
        'type',
        'description',
        'phone',
        'active'
    ];

    protected $casts = [
        'lon' => 'decimal:8',
        'lat' => 'decimal:8',
        'active' => 'boolean'
    ];
}
