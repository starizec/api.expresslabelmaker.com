<?php

namespace App\Http\Controllers\DeliveryLocations\HR;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use App\Models\DeliveryLocation;

class OverseasController extends Controller
{
    public function getDeliveryLocations()
    {
        $response = Http::withoutVerifying()->post(
            'https://api.overseas.hr/parcelshops?apikey=30c0de4c609d44be94e3a1ca044d7dfd'
        );

        $responseJson = json_decode($response->body(), true);

        foreach ($responseJson['data'] as $item) {
            if ($item['IsActive'] == false) {
                continue;
            }

            $deliveryLocation = DeliveryLocation::create([
                'country' => 'HR',
                'courier' => 'OVERSEAS',
                'location_id' => $item['CenterID'],
                'place' => $item['Address']['Place'],
                'postal_code' => $item['Address']['ZipCode'],
                'street' => $item['Address']['Street'],
                'house_number' => $item['Address']['HouseNumber'] ?? null,
                'lon' => (is_numeric($item['GeoLong'] ?? null) && abs($item['GeoLong']) <= 180)
                    ? round($item['GeoLong'], 8)
                    : null,

                'lat' => (is_numeric($item['GeoLat'] ?? null) && abs($item['GeoLat']) <= 90)
                    ? round($item['GeoLat'], 8)
                    : null,

                'name' => $item['Address']['Name'],
                'type' => $item['Type'],
                'description' => null,
                'phone' => $item['Address']['TextPhone'] ?? null,
                'active' => $item['IsActive'],
            ]);
        }

        // Delete old entries
        DeliveryLocation::where('country', 'HR')
            ->where('courier', 'OVERSEAS')
            ->whereDate('created_at', '!=', now()->format('Y-m-d'))
            ->delete();
    }
}
