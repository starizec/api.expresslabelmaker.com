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
            ],
            "errors" => []
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
            ],
            "errors" => []
        ], 201);
    }

    protected function prepareParcelPayload($parcel)
    {
        return [
            "name1" => $parcel->name1,
            "street" => $parcel->street,
            "rPropNum" => $parcel->rPropNum,
            "city" => $parcel->city,
            "country" => strtoupper($this->courier->country->short),
            "pcode" => $parcel->pcode,
            "email" => $parcel->email ?? null,
            "phone" => $parcel->phone ?? null,
            "contact" => $parcel->contact ?? null,
            "sender_remark" => $parcel->sender_remark ?? null,
            "weight" => !empty($parcel->weight) ? (float) $parcel->weight : null,
            "num_of_parcel" => (int) $parcel->num_of_parcel,
            "order_number" => $parcel->order_number ?? null,
            "parcel_type" => $parcel->parcel_type,
            "cod_amount" => !empty($parcel->cod_amount) ? (float) $parcel->cod_amount : null,
            "cod_purpose" => $parcel->cod_purpose ?? null,
            "pudo_id" => $parcel->pudo_id ?? null,
        ];
    }


    protected function validateParcel($parcel)
    {
        $rules = [
            'name1' => 'required|string|max:35',
            'name2' => 'nullable|string|max:35',
            'contact' => 'nullable|string|max:35',
            'street' => 'required|string|max:35',
            'rPropNum' => 'required|string|max:8',
            'city' => 'required|string|max:35',
            'pcode' => 'required|string|max:9|regex:/^[0-9]+$/',
            'email' => 'nullable|email|max:50',
            'phone' => 'nullable|string|max:30',

            'sender_remark' => 'nullable|string|max:50',
            'weight' => 'nullable|numeric|min:0.01',
            'num_of_parcel' => 'required|integer|min:1',
            'order_number' => 'nullable|string|max:20',
            'order_number2' => 'nullable|string|max:20',
            'parcel_type' => 'required|string|max:20',
            'parcel_cod_type' => 'nullable|in:avg,all,firstonly',
            'cod_amount' => 'nullable|numeric|min:0',
            'cod_purpose' => 'nullable|string|max:14',
            'predict' => 'nullable|in:1',
            'return_of_document' => 'nullable|in:1',
            'is_id_check' => 'nullable|in:1',
            'id_check_receiver' => 'nullable|string|max:35',
            'id_check_num' => 'nullable|string|max:5',
            'sender_name' => 'nullable|string|max:30',
            'sender_city' => 'nullable|string|max:30',
            'sender_pcode' => 'nullable|string|max:9|regex:/^[0-9]+$/',
            'sender_street' => 'nullable|string|max:30',
            'sender_phone' => 'nullable|string|max:20',
            'sender_email' => 'nullable|email|max:100',
            'pudo_id' => 'nullable|string|max:7',
            'dimension' => 'nullable|regex:/^[0-9]{1,3}[0-9]{1,3}[0-9]{1,3}$/',
            'return_name' => 'nullable|string|max:35',
            'return_name2' => 'nullable|string|max:35',
            'return_street' => 'nullable|string|max:35',
            'return_PropNum' => 'nullable|string|max:8',
            'return_city' => 'nullable|string|max:35',
            'return_pcode' => 'nullable|string|max:9|regex:/^[0-9]+$/',
            'return_phone' => 'nullable|string|max:20',
        ];

        $messages = [
            'name1.required' => 'Ime primatelja je obavezno',
            'street.required' => 'Ulica primatelja je obavezna',
            'rPropNum.required' => 'Kućni broj je obavezan',
            'city.required' => 'Grad je obavezan',
            'pcode.required' => 'Poštanski broj je obavezan',
            'pcode.regex' => 'Poštanski broj smije sadržavati samo brojeve',
            'email.email' => 'Email nije u ispravnom formatu',
            'phone.max' => 'Telefon ne smije biti duži od 30 znakova',

            'weight.numeric' => 'Težina mora biti broj',
            'weight.min' => 'Težina mora biti veća od 0',

            'num_of_parcel.required' => 'Broj paketa je obavezan',
            'num_of_parcel.integer' => 'Broj paketa mora biti cijeli broj',
            'num_of_parcel.min' => 'Broj paketa mora biti najmanje 1',

            'parcel_type.required' => 'Vrsta paketa je obavezna',
            'parcel_cod_type.in' => 'Nepoznata vrijednost za način raspodjele COD-a',
            'cod_amount.numeric' => 'Iznos pouzeća mora biti broj',
            'cod_amount.min' => 'Iznos pouzeća mora biti veći ili jednak 0',
            'cod_purpose.max' => 'Referenca pouzeća ne smije biti duža od 14 znakova',
            'predict.in' => 'Vrijednost za Predict mora biti 1',
            'return_of_document.in' => 'Vrijednost za povrat dokumentacije mora biti 1',
            'is_id_check.in' => 'Vrijednost za ID provjeru mora biti 1',
            'dimension.regex' => 'Dimenzije moraju biti unijete kao niz bez razdjelnika (npr. 100110120)',
        ];

        $validator = Validator::make((array) $parcel, $rules, $messages);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return true;
    }


}
