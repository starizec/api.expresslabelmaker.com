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
                    'error_message' => $error_message,
                    'error_code' => '701'
                ];

                continue;
            }


            $parcelResponse = Http::withoutVerifying()->post(
                config('urls.hr.overseas') . "/createshipment?apikey=$user->apiKey",
                $this->prepareParcelPayload($parcel->parcel)
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
        $payload = [
            "Cosignee" => [
                "Name" => $parcel->name1,
                "CountryCode" => strtoupper($this->courier->country->short),
                "Zipcode" => $parcel->pcode,
                "City" => $parcel->city,
                "StreetAndNumber" => $parcel->rPropNum,
                "NotifyGSM" => $parcel->phone ?? null,
                "NotifyEmail" => $parcel->email ?? null,
            ],
            "CosigneeNotifyType" => 3,
            "NumberOfCollies" => $parcel->num_of_parcel,
            "UnitAmount" => $parcel->num_of_parcel,
            "Ref1" => $parcel->order_number,
            "Ref3" => $parcel->sender_remark ?? null,
            "CODValue" => !empty($parcel->cod_amount) ? $parcel->cod_amount : null,
            "CODCurrency" => !empty($parcel->cod_amount) ? 0 : null,

            "DeliveryRemark" => $parcel->sender_remark ?? null,
            "Remark" => $parcel->sender_remark ?? null,
        ];

        if ($parcel->pudo_id) {
            $location = DeliveryLocation::where('location_id', $parcel->pudo_id)->first();

            $payload["DeliveryParcelShop"] = $location->id;
        }

        return $payload;
    }

    protected function prepareCollectionPayload($parcel)
    {
        return [
            "Sender" => [
                "Name" => $parcel->cname1,
                "CountryCode" => strtoupper($this->courier->country->short),
                "Zipcode" => $parcel->cpostal,
                "City" => $parcel->ccity,
                "StreetAndNumber" => $parcel->cstreet,
                "NotifyGSM" => $parcel->cphone,
                "NotifyEmail" => $parcel->cemail,
            ],
            "Cosignee" => [
                "Name" => $parcel->name1,
                "CountryCode" => strtoupper($this->courier->country->short),
                "Zipcode" => $parcel->pcode,
                "City" => $parcel->city,
                "StreetAndNumber" => $parcel->rPropNum,
                "NotifyGSM" => $parcel->phone,
                "NotifyEmail" => $parcel->email,
            ],
            "IsSenderNonCustomer" => true,
            "CosigneeNotifyType" => 0,
            "NumberOfCollies" => $parcel->num_of_parcel,
            "UnitAmount" => $parcel->num_of_parcel,
            "Ref1" => $parcel->order_number
        ];
    }

    protected function validateParcel($parcel)
    {
        $rules = [
            'name1' => 'required|string|max:255',
            'pcode' => 'required|string|max:5|regex:/^[0-9]+$/',
            'city' => 'required|string|max:255',
            'rPropNum' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'num_of_parcel' => 'required|integer|min:1',
            'order_number' => 'required|string|max:50',
            'sender_remark' => 'nullable|string|max:255',
            'cod_amount' => 'nullable|numeric|min:0',
        ];

        $messages = [
            'name1.required' => 'Ime i prezime je obavezno',
            'name1.string' => 'Ime i prezime mora biti tekst',
            'name1.max' => 'Ime i prezime ne smije biti duže od 255 znakova',

            'pcode.required' => 'Poštanski broj je obavezan',
            'pcode.string' => 'Poštanski broj mora biti tekst',
            'pcode.max' => 'Poštanski broj ne smije biti duži od 5 znakova',
            'pcode.regex' => 'Poštanski broj smije sadržavati samo brojeve',

            'city.required' => 'Grad je obavezan',
            'city.string' => 'Grad mora biti tekst',
            'city.max' => 'Grad ne smije biti duži od 255 znakova',

            'rPropNum.required' => 'Adresa je obavezna',
            'rPropNum.string' => 'Adresa mora biti tekst',
            'rPropNum.max' => 'Adresa ne smije biti duža od 255 znakova',

            'phone.string' => 'Telefon mora biti tekst',
            'phone.max' => 'Telefon ne smije biti duži od 20 znakova',

            'email.email' => 'Email mora biti u ispravnom formatu',
            'email.max' => 'Email ne smije biti duži od 255 znakova',

            'num_of_parcel.required' => 'Broj paketa je obavezan',
            'num_of_parcel.integer' => 'Broj paketa mora biti cijeli broj',
            'num_of_parcel.min' => 'Broj paketa mora biti veći od 0',

            'order_number.required' => 'Broj narudžbe je obavezan',
            'order_number.string' => 'Broj narudžbe mora biti tekst',
            'order_number.max' => 'Broj narudžbe ne smije biti duži od 50 znakova',

            'sender_remark.string' => 'Napomena mora biti tekst',
            'sender_remark.max' => 'Napomena ne smije biti duža od 255 znakova',

            'cod_amount.numeric' => 'Iznos COD-a mora biti broj',
            'cod_amount.min' => 'Iznos COD-a mora biti veći od 0',
        ];

        $validator = Validator::make((array) $parcel, $rules, $messages);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return true;
    }

    protected function validateCollection($parcel)
    {
        $rules = [
            'cname1' => 'required|string|max:255',
            'cpostal' => 'required|string|max:5|regex:/^[0-9]+$/',
            'ccity' => 'required|string|max:255',
            'cstreet' => 'required|string|max:255',
            'cphone' => 'nullable|string|max:20',
            'cemail' => 'nullable|email|max:255',
            'name1' => 'required|string|max:255',
            'pcode' => 'required|string|max:5|regex:/^[0-9]+$/',
            'city' => 'required|string|max:255',
            'rPropNum' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'num_of_parcel' => 'required|integer|min:1',
            'order_number' => 'required|string|max:50',
        ];

        $messages = [
            'cname1.required' => 'Ime i prezime pošiljatelja je obavezno',
            'cname1.string' => 'Ime i prezime pošiljatelja mora biti tekst',
            'cname1.max' => 'Ime i prezime pošiljatelja ne smije biti duže od 255 znakova',

            'cpostal.required' => 'Poštanski broj pošiljatelja je obavezan',
            'cpostal.string' => 'Poštanski broj pošiljatelja mora biti tekst',
            'cpostal.max' => 'Poštanski broj pošiljatelja ne smije biti duži od 5 znakova',
            'cpostal.regex' => 'Poštanski broj pošiljatelja smije sadržavati samo brojeve',

            'ccity.required' => 'Grad pošiljatelja je obavezan',
            'ccity.string' => 'Grad pošiljatelja mora biti tekst',
            'ccity.max' => 'Grad pošiljatelja ne smije biti duži od 255 znakova',

            'cstreet.required' => 'Adresa pošiljatelja je obavezna',
            'cstreet.string' => 'Adresa pošiljatelja mora biti tekst',
            'cstreet.max' => 'Adresa pošiljatelja ne smije biti duža od 255 znakova',

            'cphone.string' => 'Telefon pošiljatelja mora biti tekst',
            'cphone.max' => 'Telefon pošiljatelja ne smije biti duži od 20 znakova',

            'cemail.email' => 'Email pošiljatelja mora biti u ispravnom formatu',
            'cemail.max' => 'Email pošiljatelja ne smije biti duži od 255 znakova',

            'name1.required' => 'Ime i prezime primatelja je obavezno',
            'name1.string' => 'Ime i prezime primatelja mora biti tekst',
            'name1.max' => 'Ime i prezime primatelja ne smije biti duže od 255 znakova',

            'pcode.required' => 'Poštanski broj primatelja je obavezan',
            'pcode.string' => 'Poštanski broj primatelja mora biti tekst',
            'pcode.max' => 'Poštanski broj primatelja ne smije biti duži od 5 znakova',
            'pcode.regex' => 'Poštanski broj primatelja smije sadržavati samo brojeve',

            'city.required' => 'Grad primatelja je obavezan',
            'city.string' => 'Grad primatelja mora biti tekst',
            'city.max' => 'Grad primatelja ne smije biti duži od 255 znakova',

            'rPropNum.required' => 'Adresa primatelja je obavezna',
            'rPropNum.string' => 'Adresa primatelja mora biti tekst',
            'rPropNum.max' => 'Adresa primatelja ne smije biti duža od 255 znakova',

            'phone.string' => 'Telefon primatelja mora biti tekst',
            'phone.max' => 'Telefon primatelja ne smije biti duži od 20 znakova',

            'email.email' => 'Email primatelja mora biti u ispravnom formatu',
            'email.max' => 'Email primatelja ne smije biti duži od 255 znakova',

            'num_of_parcel.required' => 'Broj paketa je obavezan',
            'num_of_parcel.integer' => 'Broj paketa mora biti cijeli broj',
            'num_of_parcel.min' => 'Broj paketa mora biti veći od 0',

            'order_number.required' => 'Broj narudžbe je obavezan',
            'order_number.string' => 'Broj narudžbe mora biti tekst',
            'order_number.max' => 'Broj narudžbe ne smije biti duži od 50 znakova',
        ];

        $validator = Validator::make((array) $parcel, $rules, $messages);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return true;
    }

}
