<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Courier;
use App\Models\DeliveryLocation;
use App\Models\DeliveryLocationHeader;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FetchOverseasDeliveryLocations implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $courier = Courier::where('name', 'OVERSEAS')
                ->whereHas('country', function ($query) {
                    $query->where('short', 'HR');
                })
                ->first();

            if (!$courier) {
                Log::error('Overseas courier not found for HR');
                return;
            }

            $header = DeliveryLocationHeader::create([
                'courier_id' => $courier->id,
                'location_count' => 0,
                'geojson_file_name' => 'U_IZRADI'
            ]);

            $response = Http::withoutVerifying()->post(
                config('urls.hr.overseas') . '/parcelshops?apikey=' . env('HR_OVERSEAS_API_KEY')
            );

            if (!$response->successful()) {
                Log::error('Failed to fetch Overseas delivery locations', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return;
            }

            $responseJson = json_decode($response->body(), true);

            if (!isset($responseJson['data']) || !is_array($responseJson['data'])) {
                Log::error('Invalid response format from Overseas API');
                return;
            }

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

            Log::info('Overseas delivery locations fetched successfully', [
                'header_id' => $header->id,
                'location_count' => $header->location_count
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching Overseas delivery locations', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
