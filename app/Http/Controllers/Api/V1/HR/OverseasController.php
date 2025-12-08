<?php

namespace App\Http\Controllers\Api\V1\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

use App\Classes\MultiParcelResponse;

use App\Services\UserService;
use App\Services\Logger\ApiErrorLogger;
use App\Services\Logger\ApiUsageLogger;
use Illuminate\Support\Facades\Log;

use App\Models\DeliveryLocation;
use App\Models\Courier;
use App\Models\DeliveryLocationHeader;


class OverseasController extends Controller
{
    protected $courier;
    protected $user;

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
        $this->user = $user;
        ApiErrorLogger::apiError(
            '$this->validateParcel($parcel): ',
            $request,
            $this->validateParcel($parcel),
            __CLASS__ . '@' . __FUNCTION__ . '::' . __LINE__
        );

        try {
            $this->validateParcel($parcel);
        } catch (ValidationException $e) {
            $error_message = implode(' | ', collect($e->errors())->flatten()->all());

            ApiErrorLogger::apiError(
                $this->courier->country->short . ' - ' . $this->courier->name . ' - ' . $user->domain . ' - ' . $error_message . ' - ' . $this->validateParcel($parcel),
                $request,
                $error_message,
                __CLASS__ . '@' . __FUNCTION__ . '::' . __LINE__
            );

            return response()->json([
                'errors' =>
                    [
                        [
                            'order_number' => $parcel->order_number ?? 'unknown',
                            'error_message' => $error_message,
                            'error_code' => '701'
                        ]
                    ]
            ], 422);
        }

        $parcelResponse = Http::withoutVerifying()->post(
            config('urls.hr.overseas') . "/createshipment?apikey=$user->apiKey",
            $this->prepareParcelPayload($parcel)
        );

        $parcelResponseJson = json_decode($parcelResponse->body());

        if (($parcelResponse->successful() && $parcelResponseJson->status > 0) || !$parcelResponse->successful()) {
            $error_message = $parcelResponse->successful()
                ? collect($parcelResponseJson->validations)->pluck('Message')->implode(' | ')
                : $parcelResponse->status() . " - Overseas Server error";

            ApiErrorLogger::apiError(
                $this->courier->country->short . ' - ' . $this->courier->name . ' - ' . $user->domain . ' - ' . $error_message,
                $request,
                $error_message,
                __CLASS__ . '@' . __FUNCTION__ . '::' . __LINE__
            );

            return response()->json([
                "errors" =>
                    [
                        [
                            'order_number' => $parcel->order_number ?? 'unknown',
                            'error_message' => 'Overseas poruka: ' . $error_message,
                            'error_code' => '601'
                        ]
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

        if (($parcelLabelResponse->successful() && $parcelLabelResponseJson->status > 0) || !$parcelLabelResponse->successful()) {
            $error_message = $parcelLabelResponse->successful()
                ? collect($parcelLabelResponseJson->validations)->pluck('Message')->implode(' | ')
                : $parcelLabelResponse->status() . " - Overseas Server error";

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
                        'error_message' => 'Overseas poruka: ' . $error_message,
                        'error_code' => '601'
                    ]
                ]
            ], $parcelResponse->status());
        }

        UserService::addUsage($user);

        ApiUsageLogger::apiUsage($this->courier->country->short . ' - ' . $this->courier->name . ' - ' . $user->domain, $request);

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
        $allParcelLabelResponse["labelsbase64"] = [];

        foreach ($parcels as $parcel) {
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

                $errors[] = [
                    'order_number' => $parcel->order_number ?? 'unknown',
                    'error_message' => $error_message,
                    'error_code' => '701'
                ];

                continue;
            }


            $parcelResponse = Http::withoutVerifying()->post(
                config('urls.hr.overseas') . "/createshipment?apikey=$user->apiKey",
                $this->prepareParcelPayload($parcel)
            );

            $parcelResponseJson = json_decode($parcelResponse->body());

            if (($parcelResponse->successful() && $parcelResponseJson->status > 0) || !$parcelResponse->successful()) {
                $error_message = $parcelResponse->successful()
                    ? collect($parcelResponseJson->validations)->pluck('Message')->implode(' | ')
                    : $parcelResponse->status() . " - Overseas Server error";

                ApiErrorLogger::apiError(
                    $this->courier->country->short . ' - ' . $this->courier->name . ' - ' . $user->domain . ' - ' . $error_message,
                    $request,
                    $parcelResponse,
                    __CLASS__ . '@' . __FUNCTION__ . '::' . __LINE__
                );

                $errors[] = [
                    'order_number' => $parcel->order_number ?? 'unknown',
                    'error_message' => 'Overseas poruka: ' . $error_message,
                    'error_code' => '601'
                ];

                continue;
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

            if (($parcelLabelResponse->successful() && $parcelLabelResponseJson->status > 0) || !$parcelLabelResponse->successful()) {
                $error_message = $parcelLabelResponse->successful()
                    ? collect($parcelLabelResponseJson->validations)->pluck('Message')->implode(' | ')
                    : $parcelLabelResponse->status() . " - Overseas Server error";

                ApiErrorLogger::apiError(
                    $this->courier->country->short . ' - ' . $this->courier->name . ' - ' . $user->domain . ' - ' . $error_message,
                    $request,
                    $parcelLabelResponse,
                    __CLASS__ . '@' . __FUNCTION__ . '::' . __LINE__
                );

                $errors[] = [
                    'order_number' => $parcel->order_number ?? 'unknown',
                    'error_message' => 'Overseas poruka: ' . $error_message,
                    'error_code' => '601'
                ];

                continue;
            }

            UserService::addUsage($user);

            $data[] = new MultiParcelResponse($parcel->order_number, $pl_numbers, $parcelLabelResponse["labelsbase64"]);
        }

        if (count($all_pl_numbers) > 0) {
            $allParcelLabelResponse = Http::withoutVerifying()->post(
                config('urls.hr.overseas') .
                '/reprintlabels?' .
                "apikey=$user->apiKey",
                $all_pl_numbers
            );

            $allParcelLabelResponseJson = json_decode($allParcelLabelResponse->body());

            if (($allParcelLabelResponse->successful() && $allParcelLabelResponseJson->status > 0) || !$allParcelLabelResponse->successful()) {
                $error_message = $allParcelLabelResponse->successful()
                    ? collect($allParcelLabelResponseJson->validations)->pluck('Message')->implode(' | ')
                    : $allParcelLabelResponse->status() . " - Overseas Server error";

                ApiErrorLogger::apiError(
                    $this->courier->country->short . ' - ' . $this->courier->name . ' - ' . $user->domain . ' - ' . $error_message,
                    $request,
                    $allParcelLabelResponse,
                    __CLASS__ . '@' . __FUNCTION__ . '::' . __LINE__
                );

                return response()->json([
                    "errors" => [
                        'order_number' => "unknown",
                        'error_message' => "Overseas poruka: $error_message",
                        'error_code' => '601'
                    ]
                ], 400);
            }
        }

        ApiUsageLogger::apiUsage(
            $this->courier->country->short . ' - ' . $this->courier->name . ' - ' . $user->domain,
            $request
        );

        return response()->json([
            "data" => [
                "label" => $allParcelLabelResponse["labelsbase64"],
                "parcels" => $data
            ]
        ], 201);
    }

    public function collectionRequest(Request $request)
    {
        $requestBody = $request->getContent();
        $jsonData = json_decode($requestBody);

        $user = $jsonData->user;
        $parcel = $jsonData->parcel;

        try {
            $this->validateCollection($parcel);
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
                            'error_message' => $error_message,
                            'error_code' => '701'
                        ]
                    ]
            ], 422);
        }

        $parcelResponse = Http::withoutVerifying()->post(
            config('urls.hr.overseas') . "/createshipment?apikey=$user->apiKey",
            $this->prepareParcelPayload($parcel)
        );

        $parcelResponseJson = json_decode($parcelResponse->body());

        if (($parcelResponse->successful() && $parcelResponseJson->status > 0) || !$parcelResponse->successful()) {
            $error_message = $parcelResponse->successful()
                ? collect($parcelResponseJson->validations)->pluck('Message')->implode(' | ')
                : $parcelResponse->status() . " - Overseas Server error";

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
                        'error_message' => 'Overseas poruka: ' . $error_message,
                        'error_code' => '601'
                    ]
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

        if (($parcelLabelResponse->successful() && $parcelLabelResponseJson->status > 0) || !$parcelLabelResponse->successful()) {
            $error_message = $parcelLabelResponse->successful()
                ? collect($parcelLabelResponseJson->validations)->pluck('Message')->implode(' | ')
                : $parcelLabelResponse->status() . " - Overseas Server error";

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
                        'error_message' => 'Overseas poruka: ' . $error_message,
                        'error_code' => '601'
                    ]
                ]
            ], $parcelResponse->status());
        }

        UserService::addUsage($user);

        ApiUsageLogger::apiUsage($this->courier->country->short . ' - ' . $this->courier->name . ' - ' . $user->domain, $request);

        return response()->json([
            "data" => [
                "parcels" => $pl_numbers,
                "label" => $parcelLabelResponse["labelsbase64"]
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
        $notify_type = 0;
        if (isset($parcel->additional_services)) {
            $additionalServicesIds = explode(',', $parcel->additional_services);
            foreach ($additionalServicesIds as $additionalServiceId) {
                if ($additionalServiceId == "SMS") {
                    $notify_type += 2;
                }

                if ($additionalServiceId == "EMAIL") {
                    $notify_type += 1;
                }
            }
        }

        $payload = [
            "Cosignee" => [
                "Name" => $parcel->recipient_name,
                "CountryCode" => strtoupper($this->courier->country->short),
                "Zipcode" => $parcel->recipient_postal_code,
                "City" => $parcel->recipient_city,
                "StreetAndNumber" => $parcel->recipient_adress,
                "NotifyGSM" => $parcel->recipient_phone ?? null,
                "NotifyEmail" => $parcel->recipient_email ?? null,
            ],
            "CosigneeNotifyType" => $notify_type,
            "NumberOfCollies" => $parcel->parcel_count,
            "UnitAmount" => $parcel->parcel_count,
            "Ref1" => $parcel->order_number,
            "Ref2" => $parcel->parcel_ref_1 ?? null,
            "CODValue" => !empty($parcel->cod_amount) ? $parcel->cod_amount : null,
            "CODCurrency" => !empty($parcel->cod_amount) ? 0 : null,
            "DeliveryRemark" => $parcel->parcel_remark ?? null,
            "Remark" => $parcel->parcel_remark ?? null,
        ];

        if ($parcel->location_id) {
            $location = DeliveryLocation::where('id', $parcel->location_id)->latest()->first();
            $payload["DeliveryParcelShop"] = $location->location_id;
            $payload["Cosignee"]["StreetAndNumber"] = $location->street;
            $payload["Cosignee"]["Zipcode"] = $location->postal_code;
            $payload["Cosignee"]["City"] = $location->place;
        }

        return $payload;
    }


    protected function prepareCollectionPayload($parcel)
    {
        $additionalServicesIds = explode(',', $parcel->additional_services);

        $notify_type = 0;

        foreach ($additionalServicesIds as $additionalServiceId) {
            if ($additionalServiceId == "SMS") {
                $notify_type += 2;
            }

            if ($additionalServiceId == "EMAIL") {
                $notify_type += 1;
            }
        }

        return [
            "Sender" => [
                "Name" => $parcel->sender_name,
                "CountryCode" => strtoupper($this->courier->country->short),
                "Zipcode" => $parcel->sender_postal_code,
                "City" => $parcel->sender_city,
                "StreetAndNumber" => $parcel->sender_adress,
                "NotifyGSM" => $parcel->sender_phone,
                "NotifyEmail" => $parcel->sender_email,
            ],
            "Cosignee" => [
                "Name" => $parcel->recipient_name,
                "CountryCode" => strtoupper($this->courier->country->short),
                "Zipcode" => $parcel->recipient_postal_code,
                "City" => $parcel->recipient_city,
                "StreetAndNumber" => $parcel->recipient_adress,
                "NotifyGSM" => $parcel->recipient_phone,
                "NotifyEmail" => $parcel->recipient_email,
            ],
            "IsSenderNonCustomer" => true,
            "CosigneeNotifyType" => $notify_type,
            "NumberOfCollies" => $parcel->parcel_count,
            "UnitAmount" => $parcel->parcel_count,
            "Ref1" => $parcel->order_number
        ];
    }

    protected function validateParcel($parcel)
    {
        // Convert object to array and handle null values properly
        $parcelArray = json_decode(json_encode($parcel), true);
        
        $rules = [
            'recipient_name' => 'required|string|max:255',
            'recipient_phone' => 'nullable|string|max:20',
            'recipient_email' => 'nullable|email|max:255',
            'recipient_adress' => 'required|string|max:255',
            'recipient_city' => 'required|string|max:255',
            'recipient_postal_code' => 'required|string|max:5|regex:/^[0-9]+$/',

            'order_number' => 'required|string|max:50',
            'parcel_remark' => 'nullable|string|max:255',
            'cod_amount' => 'nullable|numeric|min:0',
            'cod_currency' => 'nullable|string|size:3',
            'location_id' => 'nullable|string|max:50',
            'parcel_count' => 'required|integer|min:1',

            'additional_services' => 'nullable|string|max:255',

            'parcel_ref_1' => 'nullable|string|max:100',
        ];


        $messages = [
            'recipient_name.required' => 'Ime i prezime je obavezno',
            'recipient_email.email' => 'Email primatelja mora biti ispravan',
            'recipient_adress.required' => 'Adresa je obavezna',
            'recipient_city.required' => 'Grad je obavezan',
            'recipient_postal_code.required' => 'Poštanski broj je obavezan',
            'recipient_postal_code.regex' => 'Poštanski broj primatelja smije sadržavati samo brojeve',

            'parcel_count.required' => 'Broj paketa je obavezan',
            'parcel_count.integer' => 'Broj paketa mora biti cijeli broj',
            'parcel_count.min' => 'Broj paketa mora biti barem 1',

            'parcel_remark.string' => 'Napomena uz paket mora biti tekst',
            'cod_amount.numeric' => 'Iznos pouzeća mora biti broj',
            'cod_currency.size' => 'Valuta pouzeća mora sadržavati 3 slova (npr. HRK)',

            'location_id.string' => 'ID lokacije mora biti tekst',

            'additional_services.string' => 'Dodatne usluge moraju biti tekst',

            'parcel_ref_1.string' => 'Referenca 1 mora biti tekst',
        ];



        $validator = Validator::make($parcelArray, $rules, $messages);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return true;
    }

    protected function validateCollection($parcel)
    {
        $rules = [
            'recipient_name' => 'required|string|max:255',
            'recipient_phone' => 'nullable|string|max:20',
            'recipient_email' => 'nullable|email|max:255',
            'recipient_adress' => 'required|string|max:255',
            'recipient_city' => 'required|string|max:255',
            'recipient_postal_code' => 'required|string|max:5|regex:/^[0-9]+$/',

            'order_number' => 'required|string|max:50',
            'parcel_value' => 'nullable|numeric|min:0',
            'parcel_weight' => 'nullable|numeric|min:0',
            'parcel_remark' => 'nullable|string|max:255',
            'parcel_count' => 'required|integer|min:1',

            'sender_name' => 'required|string|max:255',
            'sender_phone' => 'required|string|max:20',
            'sender_email' => 'required|email|max:255',
            'sender_adress' => 'required|string|max:255',
            'sender_city' => 'required|string|max:255',
            'sender_postal_code' => 'required|string|max:5|regex:/^[0-9]+$/',

            'additional_services' => 'nullable|string|max:255',
        ];


        $messages = [
            'sender_name.required' => 'Ime pošiljatelja je obavezno',
            'sender_name.string' => 'Ime pošiljatelja mora biti tekst',
            'sender_name.max' => 'Ime pošiljatelja ne smije biti duže od 255 znakova',

            'sender_phone.required' => 'Telefon pošiljatelja je obavezan',
            'sender_phone.string' => 'Telefon pošiljatelja mora biti tekst',
            'sender_phone.max' => 'Telefon pošiljatelja ne smije biti duži od 20 znakova',

            'sender_email.required' => 'Email pošiljatelja je obavezan',
            'sender_email.email' => 'Email pošiljatelja mora biti u ispravnom formatu',
            'sender_email.max' => 'Email pošiljatelja ne smije biti duži od 255 znakova',

            'sender_adress.required' => 'Adresa pošiljatelja je obavezna',
            'sender_adress.string' => 'Adresa pošiljatelja mora biti tekst',
            'sender_adress.max' => 'Adresa pošiljatelja ne smije biti duža od 255 znakova',

            'sender_city.required' => 'Grad pošiljatelja je obavezan',
            'sender_city.string' => 'Grad pošiljatelja mora biti tekst',
            'sender_city.max' => 'Grad pošiljatelja ne smije biti duži od 255 znakova',

            'sender_postal_code.required' => 'Poštanski broj pošiljatelja je obavezan',
            'sender_postal_code.string' => 'Poštanski broj pošiljatelja mora biti tekst',
            'sender_postal_code.regex' => 'Poštanski broj pošiljatelja smije sadržavati samo brojeve',

            'recipient_name.required' => 'Ime primatelja je obavezno',
            'recipient_email.email' => 'Email primatelja mora biti ispravan',
            'recipient_adress.required' => 'Adresa primatelja je obavezna',
            'recipient_city.required' => 'Grad primatelja je obavezan',
            'recipient_postal_code.required' => 'Poštanski broj primatelja je obavezan',
            'recipient_postal_code.regex' => 'Poštanski broj primatelja smije sadržavati samo brojeve',

            'parcel_count.required' => 'Broj paketa je obavezan',
            'parcel_count.integer' => 'Broj paketa mora biti cijeli broj',
            'parcel_count.min' => 'Broj paketa mora biti barem 1',

            'parcel_value.numeric' => 'Vrijednost paketa mora biti broj',
            'parcel_weight.numeric' => 'Težina paketa mora biti broj',
            'parcel_remark.string' => 'Napomena uz paket mora biti tekst',

            'additional_services.string' => 'Dodatne usluge moraju biti tekst',
        ];



        // Convert object to array and handle null values properly
        $parcelArray = json_decode(json_encode($parcel), true);

        $validator = Validator::make($parcelArray, $rules, $messages);

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
            $apiKey = $this->user->apiKey ?? '';
            $statusResponse = Http::withoutVerifying()
                ->get(
                    config('urls.hr.overseas') . "/shipmentbyid?apikey=" . $apiKey . "&shipmentid=" . $parcel->parcel_number,
                );

            $statusResponseJson = json_decode($statusResponse->body());

            $latestStatus = $statusResponseJson->data->Events[count($statusResponseJson->data->Events) - 1];

            $status_response[] = [
                "order_number" => $parcel->order_number,
                "parcel_number" => $parcel->parcel_number,
                "status_message" => $latestStatus->StatusDescription,
                "status_code" => $latestStatus->StatusName,
                "status_date" => $latestStatus->TimeOfScan,
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
