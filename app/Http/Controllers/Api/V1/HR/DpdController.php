<?php

namespace App\Http\Controllers\Api\V1\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

use App\Services\UserService;
use App\Services\Logger\ApiErrorLogger;
use App\Services\Logger\ApiUsageLogger;
use Illuminate\Support\Facades\Log;
use App\Services\AdressService;
use App\Models\DeliveryLocationHeader;
use App\Models\DeliveryLocation;
use App\Models\Courier;

class DpdController extends Controller
{
    protected $courier;

    public function __construct()
    {
        $this->courier = Courier::where('name', 'DPD')
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

        Log::info('DPD Request: ' . json_encode($parcel));

        try {
            $this->validateParcel($parcel);
        } catch (ValidationException $e) {
            $error_message = implode(' | ', collect($e->errors())->flatten()->all());

            ApiErrorLogger::apiError(
                $this->courier->country->short . ' - ' . $this->courier->name . ' - ' . $user->domain . ' - ' . $error_message,
                $request,
                $error_message,
                __CLASS__ . '@' . __FUNCTION__ . '::' . __LINE__
            );

            return response()->json([
                'errors' =>
                    [
                        [
                            'order_number' => $parcel->order_number ?? 'unknown',
                            'error_code' => '700',
                            'error_message' => $error_message
                        ]
                    ]
            ], 422);
        }

        $parcelResponse = Http::withoutVerifying()->post(config('urls.hr.dpd') .
            '/parcel/parcel_import?' .
            "username=$user->username&password=$user->password&" .
            http_build_query($this->prepareParcelPayload($parcel)));

        $parcelResponseJson = json_decode($parcelResponse->body());

        if (($parcelResponse->successful() && $parcelResponseJson->status === 'err') || !$parcelResponse->successful()) {
            $error_message = $parcelResponse->successful()
                ? $parcelResponseJson->errlog
                : $parcelResponse->status() . " - DPD Server error";

            ApiErrorLogger::apiError(
                $this->courier->country->short . ' - ' . $this->courier->name . ' - ' . $user->domain . ' - ' . $error_message . ' - Client',
                $request,
                $error_message,
                __CLASS__ . '@' . __FUNCTION__ . '::' . __LINE__
            );

            ApiErrorLogger::apiError(
                $this->courier->country->short . ' - ' . $this->courier->name . ' - ' . $user->domain . ' - ' . $error_message . ' - Server',
                $this->prepareParcelPayload($parcel),
                $error_message,
                __CLASS__ . '@' . __FUNCTION__ . '::' . __LINE__
            );

            return response()->json([
                "errors" => [
                    [
                        'order_number' => $parcel->order_number ?? 'unknown',
                        'error_code' => '600',
                        'error_message' => 'DPD poruka: ' . $error_message
                    ]
                ]
            ], $parcelResponse->status());
        }

        $pl_numbers = implode(",", $parcelResponseJson->pl_number);

        $parcelLabelResponse = Http::withoutVerifying()->accept('*/*')->withHeaders([
            "xhrFields" => [
                'responseType' => 'blob'
            ],
            "content-type" => "application/x-www-form-urlencoded"
        ])->post(config('urls.hr.dpd') .
                '/parcel/parcel_print?' .
                "username=$user->username&password=$user->password&" .
                "parcels=$pl_numbers");

        $parcelLabelResponseJson = json_decode($parcelLabelResponse->body());

        if (($parcelLabelResponse->successful() && $parcelResponseJson->status === 'err') || !$parcelLabelResponse->successful()) {
            $error_message = $parcelLabelResponse->successful()
                ? $parcelLabelResponseJson->errlog
                : $parcelLabelResponse->status() . " - DPD Server error";

            ApiErrorLogger::apiError(
                $this->courier->country->short . ' - ' . $this->courier->name . ' - ' . $user->domain . ' - ' . $error_message,
                $request,
                $error_message,
                __CLASS__ . '@' . __FUNCTION__ . '::' . __LINE__
            );

            return response()->json([
                "errors" => [
                    [
                        'order_number' => $parcel->order_number ?? 'unknown',
                        'error_code' => '600',
                        'error_message' => 'DPD poruka: ' . $error_message
                    ]
                ]
            ], $parcelResponse->status());
        }

        UserService::addUsage($user);

        ApiUsageLogger::apiUsage($this->courier->country->short . ' - ' . $this->courier->name . ' - ' . $user->domain, $request);

        return response()->json([
            "data" => [
                "parcels" => $pl_numbers,
                "label" => base64_encode($parcelLabelResponse->body()) // Encode as base64 for JSON
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
        $allParcelLabelResponseJson = null;

        foreach ($parcels as $parcel) {
            try {
                $this->validateParcel($parcel->parcel);
            } catch (ValidationException $e) {
                $error_message = implode(' | ', collect($e->errors())->flatten()->all());

                ApiErrorLogger::apiError(
                    $this->courier->country->short . ' - ' . $this->courier->name . ' - ' . $user->domain . ' - ' . $error_message,
                    $request,
                    $error_message,
                    __CLASS__ . '@' . __FUNCTION__ . '::' . __LINE__
                );

                $errors[] = [
                    'order_number' => $parcel->order_number ?? 'unknown',
                    'error_code' => '700',
                    'error_message' => $error_message
                ];

                continue;
            }

            $parcelResponse = Http::withoutVerifying()->post(config('urls.hr.dpd') .
                '/parcel/parcel_import?' .
                "username=$user->username&password=$user->password&" .
                http_build_query($this->prepareParcelPayload($parcel->parcel)));

            $parcelResponseJson = json_decode($parcelResponse->body());

            if (($parcelResponse->successful() && $parcelResponseJson->status === 'err') || !$parcelResponse->successful()) {
                $error_message = $parcelResponse->successful()
                    ? $parcelResponseJson->errlog
                    : $parcelResponse->status() . " - DPD Server error";

                ApiErrorLogger::apiError(
                    $this->courier->country->short . ' - ' . $this->courier->name . ' - ' . $user->domain . ' - ' . $error_message . ' - Client',
                    $request,
                    $error_message,
                    __CLASS__ . '@' . __FUNCTION__ . '::' . __LINE__
                );

                ApiErrorLogger::apiError(
                    $this->courier->country->short . ' - ' . $this->courier->name . ' - ' . $user->domain . ' - ' . $error_message . ' - Server',
                    $this->prepareParcelPayload($parcel->parcel),
                    $error_message,
                    __CLASS__ . '@' . __FUNCTION__ . '::' . __LINE__
                );

                $errors[] = [
                    'order_number' => $parcel->order_number ?? 'unknown',
                    'error_code' => '600',
                    'error_message' => 'DPD poruka: ' . $error_message
                ];

                continue;
            }

            $all_pl_numbers[] = $parcelResponseJson->pl_number;
            $pl_numbers = implode(",", $parcelResponseJson->pl_number);

            $parcelLabelResponse = Http::withoutVerifying()->accept('*/*')->withHeaders([
                "xhrFields" => [
                    'responseType' => 'blob'
                ],
                "content-type" => "application/x-www-form-urlencoded"
            ])->post(config('urls.hr.dpd') .
                    '/parcel/parcel_print?' .
                    "username=$user->username&password=$user->password&" .
                    "parcels=$pl_numbers");

            $parcelLabelResponseJson = json_decode($parcelLabelResponse->body());

            if (($parcelLabelResponse->successful() && $parcelResponseJson->status === 'err') || !$parcelLabelResponse->successful()) {
                $error_message = $parcelLabelResponse->successful()
                    ? $parcelLabelResponseJson->errlog
                    : $parcelLabelResponse->status() . " - DPD Server error";

                ApiErrorLogger::apiError(
                    $this->courier->country->short . ' - ' . $this->courier->name . ' - ' . $user->domain . ' - ' . $error_message,
                    $request,
                    $error_message,
                    __CLASS__ . '@' . __FUNCTION__ . '::' . __LINE__
                );

                $errors[] = [
                    'order_number' => $parcel->order_number ?? 'unknown',
                    'error_code' => '600',
                    'error_message' => 'DPD poruka: ' . $error_message
                ];

                continue;
            }

            UserService::addUsage($user);

            $data[] = [
                'order_number' => $parcel->order_number ?? 'unknown',
                'parcel_number' => $pl_numbers,
                'label' => base64_encode($parcelLabelResponse->body())
            ];
        }

        if (count($all_pl_numbers) > 0) {
            $all_pl_numbers = implode(',', array_merge(...$all_pl_numbers));

            $allParcelLabelResponse = Http::withoutVerifying()->accept('*/*')->withHeaders([
                "xhrFields" => [
                    'responseType' => 'blob'
                ],
                "content-type" => "application/x-www-form-urlencoded"
            ])->post(config('urls.hr.dpd') .
                    '/parcel/parcel_print?' .
                    "username=$user->username&password=$user->password&" .
                    "parcels=$all_pl_numbers");

            $allParcelLabelResponseJson = json_decode($allParcelLabelResponse->body());

            if (($allParcelLabelResponse->successful() && isset($allParcelLabelResponseJson->status)) || !$allParcelLabelResponse->successful()) {
                $error_message = $allParcelLabelResponse->successful()
                    ? $allParcelLabelResponseJson->errlog
                    : $allParcelLabelResponse->status() . " - DPD Server error";

                ApiErrorLogger::apiError(
                    $this->courier->country->short . ' - ' . $this->courier->name . ' - ' . $user->domain . ' - ' . $error_message,
                    $request,
                    $error_message,
                    __CLASS__ . '@' . __FUNCTION__ . '::' . __LINE__
                );

                return response()->json([
                    "errors" => [
                        'order_number' => $parcel->order_number ?? 'unknown',
                        'error_message' => 'DPD poruka: ' . $error_message
                    ]
                ], $parcelResponse->status());
            }

            $allParcelLabelResponseJson = base64_encode($allParcelLabelResponse->body());
        }

        ApiUsageLogger::apiUsage(
            $this->courier->country->short . ' - ' . $this->courier->name . ' - ' . $user->domain,
            $request
        );

        return response()->json([
            "data" => [
                "label" => $allParcelLabelResponseJson,
                "parcels" => $data
            ],
            "errors" => $errors
        ], 201);
    }

    public function collectionRequest(Request $request)
    {
        $requestBody = $request->getContent();
        $jsonData = json_decode($requestBody);

        $user = $jsonData->user;
        $parcel = $jsonData->parcel;

        $parcelResponse = Http::withoutVerifying()->accept('*/*')->withHeaders([
            "content-type" => "application/x-www-form-urlencoded"
        ])->post(config('urls.hr.dpd') .
                '/collection_request/cr_import?' .
                "username=$user->username&password=$user->password&" .
                http_build_query($parcel));

        $parcelResponseJson = json_decode($parcelResponse->body());

        if (($parcelResponse->successful() && $parcelResponseJson->status === 'err') || !$parcelResponse->successful()) {
            $error_message = $parcelResponse->successful()
                ? $parcelResponseJson->errlog
                : $parcelResponse->status() . " - DPD Server error";

            $error_message = $parcelResponseJson->reference === null ? 'Missing parcel data.' : $error_message;

            ApiErrorLogger::apiError(
                $this->courier->country->short . ' - ' . $this->courier->name . ' - ' . $user->domain . ' - ' . $error_message,
                $request,
                $error_message,
                __CLASS__ . '@' . __FUNCTION__ . '::' . __LINE__
            );



            return response()->json([
                "errors" => [
                    [
                        'order_number' => $parcel->order_number ?? 'unknown',
                        'error_code' => '600',
                        'error_message' => 'DPD poruka: ' . $error_message
                    ]
                ]
            ], $parcelResponse->status());
        }

        UserService::addUsage($user);

        return response()->json([
            "data" => [
                "reference" => substr($parcelResponseJson->reference, 1, -1),
                "code" => $parcelResponseJson->code
            ]
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
            ]
        ], 201);
    }

    protected function prepareParcelPayload($parcel)
    {
        $recipient_adress = AdressService::splitAddress($parcel->recipient_adress);

        $additionalServicesIds = explode(',', $parcel->additional_services);

        switch ($parcel->delivery_service) {
            case "B2C":
                if ($parcel->cod_amount > 0) {
                    $delivery_service = "D-COD-B2C";
                } else if (isset($parcel->location_id)) {
                    $delivery_service = "D-B2C-PSD";
                } else {
                    $delivery_service = "D-B2C";
                }
                break;
            case "B2B":
                if ($parcel->cod_amount > 0) {
                    $delivery_service = "D-COD";
                } else {
                    $delivery_service = "D";
                }
                break;
            case "TYRE":
                $delivery_service = "D-TYRE";
                break;
            case "TYRE-B2C":
                $delivery_service = "D-TYRE-B2C";
                break;
            case "PAL":
                $delivery_service = "PAL";
                break;
            case "SWAP":
                $delivery_service = "D-SWAP";
                break;
        }

        $location = DeliveryLocation::where('id', $parcel->location_id)->latest()->first();

        $payload = [
            "name1" => $parcel->recipient_name,
            "street" => $recipient_adress['street'],
            "rPropNum" => $recipient_adress['house_number'],
            "city" => $parcel->recipient_city,
            "country" => strtoupper($parcel->recipient_country),
            "pcode" => $parcel->recipient_postal_code,
            "email" => $parcel->recipient_email ?? null,
            "phone" => $parcel->recipient_phone ?? null,
            "contact" => $parcel->recipient_name ?? null,
            "sender_remark" => $parcel->parcel_remark ?? null,
            "weight" => !empty($parcel->parcel_weight) ? (float) $parcel->parcel_weight : null,
            "num_of_parcel" => (int) $parcel->parcel_count,
            "order_number" => $parcel->order_number ?? null,
            "parcel_type" => $delivery_service,
            "cod_amount" => !empty($parcel->cod_amount) ? (float) $parcel->cod_amount : null,
            "cod_purpose" => !empty($parcel->cod_amount) ? ($parcel->order_number ?? 'COD') : null,
            "pudo_id" => $parcel->location_id ? $location->location_id : null,
            "predict" => 0,
        ];

        foreach ($additionalServicesIds as $additionalService) {
            if ($additionalService == "INS") {
                $payload['parcel_insurance'] = !empty($parcel->parcel_value) ? (float) $parcel->parcel_value : null;
            }

            if ($additionalService == "NOTIFY") {
                $payload['predict'] = 1;
            }
        }

        Log::info('DPD Payload: ' . json_encode($payload));

        return $payload;
    }

    protected function validateParcel($parcel)
    {
        $rules = [
            // Primatelj
            'recipient_id' => 'nullable|string|max:100',
            'recipient_name' => 'required|string|max:35',
            'recipient_phone' => 'nullable|string|max:30',
            'recipient_email' => 'nullable|email|max:50',
            'recipient_adress' => 'required|string|max:70',
            'recipient_city' => 'required|string|max:35',
            'recipient_postal_code' => 'required|string|max:10',
            'recipient_country' => 'required|string|size:2',

            // Paket
            'order_number' => 'nullable|string|max:20',
            'parcel_value' => 'nullable|numeric|min:0',
            'parcel_weight' => 'nullable|numeric|min:0.01',
            'parcel_remark' => 'nullable|string|max:50',
            'cod_amount' => 'nullable|numeric|min:0',
            'cod_currency' => 'nullable|string|size:3',
            'delivery_type' => 'nullable|string|in:Adresa,Paketomat',
            'location_type' => 'nullable|string|in:Paketomat,Benza,Poštanski ured',
            'location_id' => 'required_if:delivery_type,Paketomat|nullable|string|max:7',
            'parcel_count' => 'required|integer|min:1',

            // Pošiljatelj
            'sender_id' => 'nullable|string|max:100',
            'sender_name' => 'nullable|string|max:30',
            'sender_phone' => 'nullable|string|max:20',
            'sender_email' => 'nullable|email|max:100',
            'sender_adress' => 'nullable|string|max:60',
            'sender_city' => 'nullable|string|max:30',
            'sender_postal_code' => 'nullable|string|max:5',
            'sender_country' => 'nullable|string|size:2',

            // Kurir/usluge
            'delivery_service' => 'required|string|max:20',
            'delivery_additional_services' => 'nullable|string|max:255',
            'parcel_size' => 'nullable|string|max:20',
            'printer_type' => 'nullable|string|max:20',
            'print_position' => 'nullable|integer|min:1|max:6',

            // Dimenzije
            'parcel_x' => 'nullable|integer|min:1|max:999',
            'parcel_y' => 'nullable|integer|min:1|max:999',
            'parcel_z' => 'nullable|integer|min:1|max:999',

            // Reference
            'parcel_ref_1' => 'nullable|string|max:35',
            'parcel_ref_2' => 'nullable|string|max:35',
            'parcel_ref_3' => 'nullable|string|max:35',

            // Verifikacija/licenca
            'username' => 'nullable|string|max:100',
            'password' => 'nullable|string|max:100',
            'client_number' => 'nullable|string|max:50',
            'api_key' => 'nullable|string|max:200',
            'domain' => 'nullable|string|max:255',
            'licence' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:100',
        ];

        $messages = [
            'recipient_name.required' => 'Ime primatelja je obavezno.',
            'recipient_adress.required' => 'Adresa primatelja je obavezna.',
            'recipient_city.required' => 'Grad primatelja je obavezan.',
            'recipient_country.required' => 'Država primatelja je obavezna.',
            'recipient_postal_code.required' => 'Poštanski broj je obavezan.',
            'parcel_count.required' => 'Broj paketa je obavezan.',
            'parcel_count.min' => 'Broj paketa mora biti najmanje 1.',
            'delivery_type.required' => 'Način dostave je obavezan.',
            'location_id.required_if' => 'Za dostavu u paketomat potrebno je poslati location_id.',
            'delivery_service.required' => 'DPD usluga (npr. B2C/B2B) je obavezna.',
            'parcel_weight.min' => 'Težina mora biti veća od 0.',
            'parcel_remark.max' => 'Napomena može imati najviše 50 znakova.',
        ];

        $validator = Validator::make((array) $parcel, $rules, $messages);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return true;
    }

    public function getParcelStatus(Request $request)
    {
        $requestBody = $request->getContent();
        $jsonData = json_decode($requestBody);

        $this->user = $jsonData->user;
        $parcels = $jsonData->parcels;

        $status_response = [];

        foreach ($parcels as $parcel) {
            $statusResponse = Http::withoutVerifying()
                ->post(
                    config('urls.hr.dpd') . "/parcel/parcel_status",
                    [
                        "secret" => "FcJyN7vU7WKPtUh7m1bx",
                        "parcel_number" => $parcel->parcel_number,
                    ]
                );

            $statusResponseJson = json_decode($statusResponse->body());

            $status_response[] = [
                "order_number" => $parcel->order_number,
                "parcel_number" => $parcel->parcel_number,
                "status_message" => $statusResponseJson->parcel_status,
                "status_code" => "",
                "status_date" => now()->format('Y-m-d\TH:i:s'),
                "color" => "#fff"
            ];
        }

        return response()->json([
            "data" => [
                "statuses" => $status_response
            ]
        ], 201);
    }
}
