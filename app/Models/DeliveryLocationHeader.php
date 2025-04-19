<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryLocationHeader extends Model
{
    use HasFactory;

    protected $fillable = [
        'courier_id',
        'location_count'
    ];

    public function courier()
    {
        return $this->belongsTo(Courier::class);
    }

    public function deliveryLocations()
    {
        return $this->hasMany(DeliveryLocation::class, 'header_id');
    }
} 