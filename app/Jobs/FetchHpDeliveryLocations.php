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

class FetchHpDeliveryLocations implements ShouldQueue
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
            $courier = Courier::where('name', 'HP')
                ->whereHas('country', function ($query) {
                    $query->where('short', 'HR');
                })
                ->first();

            if (!$courier) {
                Log::error('HP courier not found for HR');
                return;
            }

            $header = DeliveryLocationHeader::create([
                'courier_id' => $courier->id,
                'location_count' => 0,
                'geojson_file_name' => 'U_IZRADI'
            ]);

            $response = Http::withoutVerifying()->post('https://facility-api.posta.hr/api/facility/getfacilities', [
                'facilityType' => 'ALL',
                'nextWeek' => 0,
                'searchText' => ''
            ]);

            if (!$response->successful()) {
                Log::error('Failed to fetch HP delivery locations', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return;
            }

            $responseJson = json_decode($response->body(), true);

            if (!isset($responseJson['paketomatInfoList']) || !is_array($responseJson['paketomatInfoList'])) {
                Log::error('Invalid response format from HP API');
                return;
            }

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

            Log::info('HP delivery locations fetched successfully', [
                'header_id' => $header->id,
                'location_count' => $header->location_count
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching HP delivery locations', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
