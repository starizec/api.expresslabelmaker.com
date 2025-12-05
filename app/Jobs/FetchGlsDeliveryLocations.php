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

class FetchGlsDeliveryLocations implements ShouldQueue
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
            $courier = Courier::where('name', 'GLS')
                ->whereHas('country', function ($query) {
                    $query->where('short', 'HR');
                })
                ->first();

            if (!$courier) {
                Log::error('GLS courier not found for HR');
                return;
            }

            $header = DeliveryLocationHeader::create([
                'courier_id' => $courier->id,
                'location_count' => 0,
                'geojson_file_name' => 'U_IZRADI'
            ]);

            $response = Http::withoutVerifying()->get('https://map.gls-croatia.com/data/deliveryPoints/hr.json');

            if (!$response->successful()) {
                Log::error('Failed to fetch GLS delivery locations', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return;
            }

            $responseJson = json_decode($response->body(), true);

            if (!isset($responseJson['items']) || !is_array($responseJson['items'])) {
                Log::error('Invalid response format from GLS API');
                return;
            }

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

            Log::info('GLS delivery locations fetched successfully', [
                'header_id' => $header->id,
                'location_count' => $header->location_count
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching GLS delivery locations', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
