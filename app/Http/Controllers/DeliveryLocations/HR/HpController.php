<?php

namespace App\Http\Controllers\DeliveryLocations\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

use App\Models\Courier;
use App\Models\DeliveryLocationHeader;
use App\Models\DeliveryLocation;

class HpController extends Controller
{
    protected $courier;

    public function __construct()
    {
        $this->courier = Courier::where('name', 'HP')
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

        $response = Http::withoutVerifying()->post('https://facility-api.posta.hr/api/facility/getfacilities', [
            'facilityType' => 'ALL',
            'nextWeek' => 0,
            'searchText' => ''
        ]);
        $responseJson = json_decode($response->body(), true);

        foreach ($responseJson['paketomatInfoList'] as $item) {
            DeliveryLocation::create([
                'header_id' => $header->id,
                'location_id' => $item['code'],
                'place' => $item['city'],
                'postal_code' => $item['zip'],
                'street' => $item['address'],
                'house_number' => null,
                'lon' => $item['getLng'],
                'lat' => $item['geoLat'],
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
