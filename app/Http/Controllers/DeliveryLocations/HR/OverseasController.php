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
                'country' => 'HR',
                'courier' => 'OVERSEAS',
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

        DeliveryLocation::where('country', 'HR')
            ->where('courier', 'OVERSEAS')
            ->whereDate('created_at', '!=', now()->format('Y-m-d'))
            ->delete();
    }
}
