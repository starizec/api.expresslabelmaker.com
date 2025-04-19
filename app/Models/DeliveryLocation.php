<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'header_id',
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

    public function header()
    {
        return $this->belongsTo(DeliveryLocationHeader::class);
    }

    protected $casts = [
        'lon' => 'decimal:8',
        'lat' => 'decimal:8',
        'active' => 'boolean'
    ];
}
