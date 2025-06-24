<?php

namespace App\Http\Controllers\DeliveryLocations\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

use App\Models\Courier;
use App\Models\DeliveryLocationHeader;
use App\Models\DeliveryLocation;

class GlsController extends Controller
{
    protected $courier;

    public function __construct()
    {
        $this->courier = Courier::where('name', 'GLS')
            ->whereHas('country', function ($query) {
                $query->where('short', 'HR');
            })
            ->first();
    }

    public function getDeliveryLocations()
    {
        $header = DeliveryLocationHeader::create([
            'courier_id' => $this->courier->id,
            'location_count' => 0,
            'geojson_file_name' => 'U_IZRADI'
        ]);

        $response = Http::withoutVerifying()->get('https://map.gls-croatia.com/data/deliveryPoints/hr.json');
        $responseJson = json_decode($response->body(), true);

        foreach ($responseJson['items'] as $item) {
            DeliveryLocation::create([
                'header_id' => $header->id,
                'location_id' => $item['id'],
                'place' => $item['contact']['city'],
                'postal_code' => $item['contact']['postalCode'],
                'street' => $item['contact']['address'],
                'house_number' => null,
                'lon' => $item['location'][1],
                'lat' => $item['location'][0],
                'name' => $item['name'],
                'type' => $item['type'],
                'description' => null,
                'phone' => null,
                'active' => true,
            ]);
        }

        $header->update([
            'location_count' => DeliveryLocation::where('header_id', $header->id)->count()
        ]);
    }
}
