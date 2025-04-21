<?php

namespace App\Http\Controllers\DeliveryLocations\HR;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

use App\Models\DeliveryLocationHeader;
use App\Models\DeliveryLocation;
use App\Models\Country;
use App\Models\Courier;

class OverseasController extends Controller
{
    protected $courier;

    public function __construct()
    {
        $this->courier = Courier::where('name', 'OVERSEAS')
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

        $response = Http::withoutVerifying()->post(
            config('urls.hr.overseas') . '/parcelshops?apikey=' . env('HR_OVERSEAS_API_KEY')
        );

        $responseJson = json_decode($response->body(), true);

        foreach ($responseJson['data'] as $item) {
            if (
                $item['IsActive'] == false ||
                $item['Delivery'] == false ||
                !isset($item['GeoLong']) ||
                !isset($item['GeoLat']) ||
                !$item['GeoLong'] ||
                !$item['GeoLat'] ||
                !is_numeric($item['GeoLong']) ||
                !is_numeric($item['GeoLat']) ||
                !str_contains((string) $item['GeoLong'], '.') ||
                !str_contains((string) $item['GeoLat'], '.')
            ) {
                continue;
            }

            DeliveryLocation::create([
                'header_id' => $header->id,
                'location_id' => $item['ShortName'],
                'place' => $item['Address']['Place'],
                'postal_code' => $item['Address']['ZipCode'],
                'street' => $item['Address']['Street'],
                'house_number' => $item['Address']['HouseNumber'] ?? null,
                'lon' => $item['GeoLong'],
                'lat' => $item['GeoLat'],
                'name' => $item['Address']['Name'],
                'type' => $item['Type'],
                'description' => null,
                'phone' => $item['Address']['TextPhone'] ?? null,
                'active' => $item['IsActive'],
            ]);
        }

        $header->update([
            'location_count' => DeliveryLocation::where('header_id', $header->id)->count()
        ]);

        $this->createDeliveryLocationsGeoJson($header->id);
    }

    public function createDeliveryLocationsGeoJson($header_id)
    {
        $deliveryLocations = DeliveryLocation::where('header_id', $header_id)
            ->get();

        $features = [];

        foreach ($deliveryLocations as $location) {
            $features[] = [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [(float) $location->lon, (float) $location->lat]
                ],
                'properties' => [
                    'id' => $location->id,
                    'location_id' => $location->location_id,
                    'name' => $location->name,
                    'place' => $location->place,
                    'postal_code' => $location->postal_code,
                    'street' => $location->street,
                    'house_number' => $location->house_number,
                    'type' => $location->type,
                    'active' => $location->active,
                ]
            ];
        }

        $geojson = [
            'type' => 'FeatureCollection',
            'features' => $features
        ];

        $filename = $header_id . '_' . now()->format('Y-m-d_H-i-s') . '.geojson';
        
        $path = storage_path('app/public/geojson/' . $filename);

        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents($path, json_encode($geojson, JSON_PRETTY_PRINT));

        DeliveryLocationHeader::where('id', $header_id)->update([
            'geojson_file_name' => $filename
        ]);
    }
}
