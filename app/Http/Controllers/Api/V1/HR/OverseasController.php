<?php

namespace App\Http\Controllers\Api\V1\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

use App\Classes\MultiParcelResponse;
use App\Classes\MultiParcelError;

use App\Services\ErrorService;
use App\Services\UserService;
use App\Services\HR\OverseasLogger;

use App\Models\DeliveryLocation;
use App\Models\Courier;
use App\Models\DeliveryLocationHeader;

class OverseasController extends Controller
{
    protected $courier;

    public function __construct()
    {
        $this->courier = Courier::where('name', 'OVERSEAS')
            ->whereHas('country', function ($query) {
                $query->where('short', 'HR');
            })
            ->firstOrFail();
    }


    public function createLabel(Request $request)
    {
        $requestBody = $request->getContent();
        $jsonData = json_decode($requestBody);

        $user = $jsonData->user;
        $parcel = $jsonData->parcel;

        $parcelResponse = Http::withoutVerifying()->post(
            config('urls.hr.overseas') . "/createshipment?apikey=$user->apiKey",
            $this->prepareParcelPayload($parcel)
        );

        $parcelResponseJson = json_decode($parcelResponse->body());

        if ($parcelResponse->successful() && $parcelResponseJson->status > 0) {
            OverseasLogger::apiError(
                $user->email,
                $user->domain,
                $parcelResponseJson->status,
                $parcelResponseJson->error,
                $request,
                "App\Http\Controllers\Api\V1\HR\OverseasController@createLabel::" . __LINE__,
                json_encode($parcel)
            );

            return response()->json([
                "errors" => [
                    'code' => $parcelResponseJson->status,
                    'message' => $parcelResponseJson->error
                ],
            ], 400);
        } elseif (!$parcelResponse->successful()) {
            OverseasLogger::apiError(
                $user->email,
                $user->domain,
                $parcelResponse->status(),
                $parcelResponse->status() . " - Overseas Server error",
                $request,
                "App\Http\Controllers\Api\V1\HR\OverseasController@createLabel::" . __LINE__,
                json_encode($parcel)
            );

            return response()->json([
                "errors" => [
                    'code' => $parcelResponse->status(),
                    'message' => $parcelResponse->status() . " - Overseas Server error"
                ]
            ], $parcelResponse->status());
        }

        $pl_numbers = $parcelResponseJson->shipmentid;

        $parcelLabelResponse = Http::withoutVerifying()->accept('*/*')->withHeaders([
            "xhrFields" => [
                'responseType' => 'blob'
            ],
            "content-type" => "application/x-www-form-urlencoded"
        ])->post(
                config('urls.hr.overseas') .
                '/commitshipments?' .
                "apikey=$user->apiKey",
                [$pl_numbers]
            );

        $parcelLabelResponseJson = json_decode($parcelLabelResponse->body());

        if ($parcelLabelResponse->successful()) {
            if ($parcelLabelResponseJson->status > 0) {
                return response()->json([
                    "errors" => [
                        ErrorService::write(
                            $user->email,
                            400,
                            $parcelLabelResponseJson->status . ' - ' . json_encode($parcelResponseJson->error),
                            $request,
                            "App\Http\Controllers\Api\V1\HR\OverseasController@createLabel::" . __LINE__,
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
                        $parcelLabelResponse->status(),
                        $parcelLabelResponse->status() . ' - Overseas Server error',
                        $request,
                        "App\Http\Controllers\Api\V1\HR\OverseasController@createLabel::" . __LINE__,
                        json_encode($parcel)
                    )
                ]
            ], $parcelLabelResponse->status());
        }

        UserService::addUsage($user);

        return response()->json([
            "data" => [
                "parcels" => $pl_numbers,
                "label" => $parcelLabelResponse["labelsbase64"]
            ]
        ], 201);
    }

    public function createLabels(Request $request)
    {
        $requestBody = $request->getContent();
        $jsonData = json_decode($requestBody);

        $user = $jsonData->user;
        $parcels = $jsonData->parcels;

        $data = [];
        $errors = [];
        $all_pl_numbers = [];

        foreach ($parcels as $parcel) {
            $parcelResponse = Http::withoutVerifying()->post(
                config('urls.hr.overseas') . "/createshipment?apikey=$user->apiKey",
                $this->prepareParcelPayload($parcel)
            );

            $parcelResponseJson = json_decode($parcelResponse->body());

            if ($parcelResponse->successful()) {
                if ($parcelResponseJson->status > 0) {
                    $error = ErrorService::write(
                        $user->email,
                        $parcelResponseJson->status,
                        substr($parcelResponseJson->status . ' - ' . json_encode($parcelResponseJson->error), 0, 250),
                        $request,
                        "App\Http\Controllers\Api\V1\HR\OverseasController@createLabels::" . __LINE__,
                        json_encode($parcel)
                    );

                    $errors[] = new MultiParcelError($parcel->order_number, $error['error_id'], $error['error_details']);
                }
            } else {
                $error = ErrorService::write(
                    $user->email,
                    $parcelResponse->status(),
                    $parcelResponse->status() . 'Overseas Server error',
                    $request,
                    "App\Http\Controllers\Api\V1\HR\OverseasController@createLabels::" . __LINE__,
                    json_encode($parcel)
                );

                $errors[] = new MultiParcelError($parcel->order_number, $error['error_id'], $error['error_details']);
            }

            $all_pl_numbers[] = $parcelResponseJson->shipmentid;
            $pl_numbers = $parcelResponseJson->shipmentid;

            $parcelLabelResponse = Http::withoutVerifying()->accept('*/*')->withHeaders([
                "xhrFields" => [
                    'responseType' => 'blob'
                ],
                "content-type" => "application/x-www-form-urlencoded"
            ])->post(
                    config('urls.hr.overseas') .
                    '/commitshipments?' .
                    "apikey=$user->apiKey",
                    [$pl_numbers]
                );

            $parcelLabelResponseJson = json_decode($parcelLabelResponse->body());

            if ($parcelLabelResponse->successful()) {
                if ($parcelLabelResponseJson->status > 0) {
                    $error = ErrorService::write(
                        $user->email,
                        $parcelLabelResponseJson->status,
                        substr($parcelResponseJson->status . ' - ' . json_encode($parcelResponseJson->error), 0, 250),
                        $request,
                        "App\Http\Controllers\Api\V1\HR\OverseasController@createLabels::" . __LINE__,
                        json_encode($parcel)
                    );

                    $errors[] = new MultiParcelError($parcel->order_number, $error['error_id'], $error['error_details']);
                }
            } else {
                $error = ErrorService::write(
                    $user->email,
                    $parcelLabelResponse->status(),
                    $parcelLabelResponse->status() . 'Overseas Server error',
                    $request,
                    "App\Http\Controllers\Api\V1\HR\OverseasController@createLabels::" . __LINE__,
                    json_encode($parcel)
                );

                $errors[] = new MultiParcelError($parcel->order_number, $error['error_id'], $error['error_details']);
            }

            UserService::addUsage($user);
            $data[] = new MultiParcelResponse($parcel->order_number, $pl_numbers, $parcelLabelResponse["labelsbase64"]);
        }

        $allParcelLabelResponse = Http::withoutVerifying()->post(
            config('urls.hr.overseas') .
            '/reprintlabels?' .
            "apikey=$user->apiKey",
            $all_pl_numbers
        );

        $allParcelLabelResponseJson = json_decode($allParcelLabelResponse->body());

        if ($allParcelLabelResponse->successful()) {
            if ($allParcelLabelResponseJson->status > 0) {
                return response()->json([
                    "errors" => [
                        $error = ErrorService::write(
                            $user->email,
                            $allParcelLabelResponseJson->status,
                            substr($allParcelLabelResponseJson->status . ' ' . json_encode($allParcelLabelResponseJson->error), 0, 250),
                            $request,
                            "App\Http\Controllers\Api\V1\HR\OverseasController@createLabels::" . __LINE__,
                            json_encode($parcel)
                        )
                    ],
                ], 400);
            }
        } else {
            return response()->json([
                "errors" => [
                    $error = ErrorService::write(
                        $user->email,
                        $allParcelLabelResponse->status(),
                        $allParcelLabelResponse->status() . " - DPD Server error",
                        $request,
                        "App\Http\Controllers\Api\V1\HR\OverseasController@createLabels::" . __LINE__,
                        json_encode($parcel)
                    )
                ]
            ], $allParcelLabelResponse->status());
        }

        return response()->json([
            "data" => [
                "label" => $allParcelLabelResponse["labelsbase64"],
                "parcels" => $data
            ],
            "errors" => $errors
        ], 201);
    }

    public function getDeliveryLocations()
    {
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

    protected function prepareParcelPayload($parcel)
    {
        return [
            "Cosignee" => [
                "Name" => $parcel->name1,
                "CountryCode" => strtoupper($this->courier->country->short),
                "Zipcode" => $parcel->pcode,
                "City" => $parcel->city,
                "StreetAndNumber" => $parcel->rPropNum,
                "NotifyGSM" => $parcel->phone,
                "NotifyEmail" => $parcel->email,
            ],
            "UnitAmount" => $parcel->num_of_parcel,
            "Ref1" => $parcel->order_number,
            "NumberOfCollies" => $parcel->num_of_parcel,
            "CODValue" => !empty($parcel->cod_amount) ? $parcel->cod_amount : null,
            "CODCurrency" => !empty($parcel->cod_amount) ? 0 : null,
            "DeliveryRemark" => $parcel->sender_remark ?? null,
            "Remark" => $parcel->sender_remark ?? null,
            "CosigneeNotifyType" => 3,
        ];
    }
}
