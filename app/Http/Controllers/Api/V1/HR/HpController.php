<?php

namespace App\Http\Controllers\Api\V1\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

use App\Classes\MultiParcelResponse;
use App\Classes\MultiParcelError;

use App\Services\ErrorService;
use App\Services\UserService;

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

    public function createLabel(Request $request)
    {
        $requestBody = $request->getContent();
        $jsonData = json_decode($requestBody);

        $user = $jsonData->user;
        $parcel = $jsonData->parcel;

        // TODO: Replace with actual HP API endpoint and authentication
        $hpApiUrl = config('urls.hr.hp'); 
        // Placeholder: Example API call structure
        $parcelResponse = Http::withHeaders([
            // Add necessary HP API headers, e.g., Authorization
            // 'Authorization' => 'Bearer YOUR_HP_API_TOKEN',
            'Content-Type' => 'application/json',
        ])->post($hpApiUrl . '/create-label', [
            'username' => $user->username, 
            'password' => $user->password, 
            'parcel_data' => $parcel, // Structure according to HP API
        ]);

        $parcelResponseJson = json_decode($parcelResponse->body());

        if ($parcelResponse->successful()) {
            // TODO: Adapt error checking based on HP API response structure
            if (isset($parcelResponseJson->status) && $parcelResponseJson->status === 'err') {
                return response()->json([
                    "errors" => [
                        ErrorService::write(
                            $user->email,
                            400,
                            $parcelResponseJson->status . ' - ' . ($parcelResponseJson->errlog ?? 'HP API Error'),
                            $request,
                            "App\Http\Controllers\Api\V1\HR\HpController@createLabel::" . __LINE__,
                            json_encode($parcel)
                        )
                    ],
                ], 400);
            }
        } else {
            return response()->json([
                "errors" => [
                    ErrorService::write(
                        $user->email,
                        $parcelResponse->status(),
                        $parcelResponse->status() . " - HP Server error",
                        $request,
                        "App\Http\Controllers\Api\V1\HR\HpController@createLabel::" . __LINE__,
                        json_encode($parcel)
                    )
                ]
            ], $parcelResponse->status());
        }

        // TODO: Extract parcel numbers and label data based on HP API response
        $parcel_numbers = $parcelResponseJson->parcel_number ?? 'N/A'; // Placeholder
        $label_data = $parcelResponseJson->label_data ?? ''; // Placeholder

        // TODO: Potentially fetch label separately if needed (like DPD)
        // If HP API requires a separate call to fetch the label, implement it here.
        
        UserService::addUsage($user);

        return response()->json([
            "data" => [
                "parcels" => $parcel_numbers,
                "label" => base64_encode($label_data) // Encode as base64 for JSON
            ]
        ], 201);
    }

    
    public function createLabels(Request $request)
    {
        $requestBody = $request->getContent();
        $jsonData = json_decode($requestBody);

        $user = $jsonData->user;
        $parcelsData = $jsonData->parcels;

        $responses = [];
        $errors = [];
        $all_parcel_identifiers = [];

        // TODO: Replace with actual HP API endpoint and authentication
        $hpApiUrl = config('urls.hr.hp'); 
        
        foreach ($parcelsData as $parcelItem) {
            // Placeholder: Example API call structure for each parcel
            $parcelResponse = Http::withHeaders([
                // Add necessary HP API headers
                'Content-Type' => 'application/json',
            ])->post($hpApiUrl . '/create-label', [
                'username' => $user->username, 
                'password' => $user->password, 
                'parcel_data' => $parcelItem->parcel, // Structure according to HP API
                'order_ref' => $parcelItem->order_number
            ]);

            $parcelResponseJson = json_decode($parcelResponse->body());

            if ($parcelResponse->successful()) {
                // TODO: Adapt error checking based on HP API response structure
                if (isset($parcelResponseJson->status) && $parcelResponseJson->status === 'err') {
                    $error = ErrorService::write(
                        $user->email,
                        $parcelResponseJson->status,
                        $parcelResponseJson->status . ' ' . ($parcelResponseJson->errlog ?? 'HP API Error'),
                        $request,
                        "App\Http\Controllers\Api\V1\HR\HpController@createLabels::" . __LINE__,
                        json_encode($parcelItem)
                    );
                    $errors[] = new MultiParcelError($parcelItem->order_number, $error['error_id'], $error['error_details']);
                    continue; // Skip to next parcel on error
                }
                
                // TODO: Extract successful parcel identifier (e.g., parcel number)
                $parcel_number = $parcelResponseJson->parcel_number ?? 'N/A';
                $all_parcel_identifiers[] = $parcel_number;

                // Add successful response (adjust structure as needed)
                $responses[] = new MultiParcelResponse($parcelItem->order_number, $parcel_number, null); // No individual label for now
                UserService::addUsage($user);

            } else {
                $error = ErrorService::write(
                    $user->email,
                    $parcelResponse->status(),
                    $parcelResponse->status() . ' - HP Server error',
                    $request,
                    "App\Http\Controllers\Api\V1\HR\HpController@createLabels::" . __LINE__,
                    json_encode($parcelItem)
                );
                $errors[] = new MultiParcelError($parcelItem->order_number, $error['error_id'], $error['error_details']);
            }
        }

        // TODO: Implement logic to fetch a combined label PDF if HP API supports it
        $combinedLabelData = '';
        if (!empty($all_parcel_identifiers)) {
            // Example: Make API call to get combined label
            $combinedLabelResponse = Http::withHeaders([
                // Add necessary HP API headers
                'Content-Type' => 'application/json',
            ])->post($hpApiUrl . '/print-labels', [
                'username' => $user->username, 
                'password' => $user->password, 
                'parcel_numbers' => $all_parcel_identifiers, // Send identifiers of successfully created parcels
            ]);
            
            if ($combinedLabelResponse->successful()) {
                // TODO: Extract combined label data (assuming PDF content)
                 $combinedLabelData = $combinedLabelResponse->body();
            } else {
                 // Handle error fetching combined label - log it, maybe add to errors array
                 ErrorService::write(
                    $user->email,
                    $combinedLabelResponse->status(),
                    'Failed to fetch combined HP label', 
                    $request, 
                    "App\Http\Controllers\Api\V1\HR\HpController@createLabels::" . __LINE__,
                    json_encode($all_parcel_identifiers)
                 );
            }
        }
        
        return response()->json([
            "data" => [
                "label" => base64_encode($combinedLabelData), // Combined label
                "parcels" => $responses // Individual parcel statuses
            ],
            "errors" => $errors
        ], 201);
    }

    
    public function collectionRequest(Request $request)
    {
        $requestBody = $request->getContent();
        $jsonData = json_decode($requestBody);

        $user = $jsonData->user;
        $pickupData = $jsonData->pickup_data;

        // TODO: Replace with actual HP API endpoint and authentication
        $hpApiUrl = config('urls.hr.hp');

        // Placeholder: Example API call structure
        $collectionResponse = Http::withHeaders([
             // Add necessary HP API headers
            'Content-Type' => 'application/json',
        ])->post($hpApiUrl . '/collection-request', [
            'username' => $user->username,
            'password' => $user->password,
            'pickup_details' => $pickupData, // Structure according to HP API
        ]);

        $collectionResponseJson = json_decode($collectionResponse->body());

        if ($collectionResponse->successful()) {
            // TODO: Adapt error checking based on HP API response structure
            if (isset($collectionResponseJson->status) && $collectionResponseJson->status === 'Error') {
                return response()->json([
                    "errors" => [
                        ErrorService::write(
                            $user->email,
                            400,
                            $collectionResponseJson->status . ' ' . ($collectionResponseJson->errlog ?? 'HP API Error'),
                            $request,
                            "App\Http\Controllers\Api\V1\HR\HpController@collectionRequest::" . __LINE__,
                            json_encode($pickupData)
                        )
                    ],
                ], 400);
            }
            
            // TODO: Validate required response fields from HP API
             if (!isset($collectionResponseJson->reference) || !isset($collectionResponseJson->pickup_id)) {
                return response()->json([
                    "errors" => [
                        ErrorService::write(
                            $user->email,
                            400,
                            'Missing required data in HP API response.',
                            $request,
                            "App\Http\Controllers\Api\V1\HR\HpController@collectionRequest::" . __LINE__,
                            json_encode($pickupData)
                        )
                    ],
                ], 400);
            }

        } else {
            return response()->json([
                "errors" => [
                    ErrorService::write(
                        $user->email,
                        $collectionResponse->status(),
                        $collectionResponse->status() . " - HP Server error",
                        $request,
                        "App\Http\Controllers\Api\V1\HR\HpController@collectionRequest::" . __LINE__,
                        json_encode($pickupData)
                    )
                ]
            ], $collectionResponse->status());
        }

        // No usage added for collection requests? Or maybe it should?
        // UserService::addUsage($user); 
        
        return response()->json([
            "data" => [
                "reference" => $collectionResponseJson->reference ?? 'N/A',
                "pickup_id" => $collectionResponseJson->pickup_id ?? 'N/A'
            ]
        ], 201);
    }

    public function getDeliveryLocations(){
        $header = DeliveryLocationHeader::where('courier_id', $this->courier->id)->latest()->first();
        $deliveryLocations = DeliveryLocation::where('header_id', $header->id)->get();

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

        return response()->json([
            "data" => [
                "geojson" => $geojson
            ],
            "errors" => []
        ], 201);
    }
}
