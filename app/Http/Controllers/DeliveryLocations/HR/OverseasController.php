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

    }
}
