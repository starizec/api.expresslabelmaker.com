<?php

namespace App\Http\Controllers\Api\V1\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Services\Logger\ApiErrorLogger;
use App\Models\Courier;
use App\Models\DeliveryLocationHeader;
use App\Models\DeliveryLocation;
use App\Services\UserService;
use App\Services\Logger\ApiUsageLogger;

class HpController extends Controller
{
    protected $courier;
    protected $token;
    protected $user;

    protected $additionalServices = [
        1 => "Uručiti osobno",
        3 => "Uručenje subotom",
        4 => "S povratnicom",
        9 => "Plaćanje pouzećem (Otkupnina)",
        30 => "Slanje obavijesti primatelju",
        32 => "Slanje poruke e-pošte primatelju",
        47 => "Konsolidirana pošiljka",
    ];

    protected $barcodeTypes = [
        0 => "Predefiniran range dobiven od HP-a",
        1 => "Barcode dobiven u odgovoru iz sustava pri kreiranju pošiljke",
    ];

    protected $services = [
        26 => "Paket 24 D+1",
        29 => "Paket 24 D+2",
        32 => "Paket 24 D+3",
        38 => "Paket 24 D+4",
        39 => "EasyReturn D+3 (1st option)",
        40 => "EasyReturn D+3 (2nd option)",
        46 => "Pallet shipment D+5",
    ];

    protected $payedBy = [
        1 => "Pošiljatelj",
        2 => "Primatelj",
    ];

    protected $deliveryTypes = [
        "ADR" => 1,
        "PU" => 2,
        "PAK" => 3,
    ];

    protected $parcelSize = [
        "X" => 1,
        "S" => 2,
        "M" => 3,
        "L" => 4,
    ];

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

        $this->user = $jsonData->user;
        $parcel = $jsonData->parcel;
        $errors = [];

        try {
            $this->validateParcel($parcel);
        } catch (ValidationException $e) {
            $error_message = implode(' | ', collect($e->errors())->flatten()->all());

            ApiErrorLogger::apiError(
                $this->courier->country->short . ' - ' . $this->courier->name . ' - ' . $this->user->domain . ' - ' . $error_message,
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
                            'error_code' => '703'
                        ]
                    ]
            ], 422);
        }

        $this->token = $this->getToken()['accessToken'];

        $parcelResponse = Http::withoutVerifying()
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->token
            ])
            ->post(
                config('urls.hr.hp') . "/shipment/create_shipment_orders",
                [
                    "parcels" => [$this->prepareParcelPayload($parcel)],
                    "return_address_label" => true
                ]
            );

        $parcelResponseJson = json_decode($parcelResponse->body());

        foreach ($parcelResponseJson->ShipmentOrdersList as $shipmentOrder) {
            if ($shipmentOrder->ErrorCode != null) {
                $errors[] = $shipmentOrder->ErrorCode . " - " . $shipmentOrder->ErrorMessage;
            }
        }

        if (($parcelResponse->successful() && count($errors) > 0) || !$parcelResponse->successful()) {
            $error_message = $parcelResponse->successful()
                ? implode(' | ', collect($errors)->flatten()->all())
                : $parcelResponse->status() . " - HP Server error";

            ApiErrorLogger::apiError(
                $this->courier->country->short . ' - ' . $this->courier->name . ' - ' . $this->user->domain . ' - ' . $error_message,
                $request,
                $error_message,
                __CLASS__ . '@' . __FUNCTION__ . '::' . __LINE__
            );

            return response()->json([
                "errors" => [
                    'order_number' => $parcel->order_number ?? 'unknown',
                    'error_message' => 'HP poruka: ' . $error_message,
                    'error_code' => '603'
                ]
            ], $parcelResponse->status());
        }

        $pl_numbers = [];

        foreach ($parcelResponseJson->ShipmentOrdersList as $shipmentOrder) {
            foreach ($shipmentOrder->Packages as $package) {
                $pl_numbers[] = $package->barcode;
            }
        }

        $pl_numbers = implode(",", $pl_numbers);

        UserService::addUsage($this->user);

        ApiUsageLogger::apiUsage($this->courier->country->short . ' - ' . $this->courier->name . ' - ' . $this->user->domain, $request);

        return response()->json([
            "data" => [
                "order_number" => $parcel->order_number,
                "parcels" => $pl_numbers,
                "label" => $parcelResponseJson->ShipmentsLabel
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

    protected function getToken()
    {
        $response = Http::withoutVerifying()->post(
            config('urls.hr.hp-auth') . "/authentication/client_auth",
            [
                'username' => $this->user->username,
                'password' => $this->user->password
            ]
        );

        return $response->json();
    }

    protected function prepareParcelPayload($parcel)
    {
        if (!isset($parcel->location_id) || $parcel->location_id == "" || $parcel->location_id == null) {
            $deliveryTypeId = $this->deliveryTypes["ADR"];

        } elseif (isset($parcel->location_id) && $parcel->location_type == "PU") {
            $deliveryTypeId = $this->deliveryTypes["PU"];

        } elseif (isset($parcel->location_id) && $parcel->location_type == "PAK") {
            $deliveryTypeId = $this->deliveryTypes["PAK"];
        }

        $additionalServices = [];

        if (isset($parcel->cod_amount) && (float)$parcel->cod_amount > 0) {
            $additionalServices[] = ["additional_service_id" => 9];
        }

        $additionalServicesIds = explode(',', $parcel->additional_services);

        foreach ($additionalServicesIds as $additionalServiceId) {
            $additionalServices[] = ["additional_service_id" => (int)trim($additionalServiceId)];
        }

        return
            [
                "client_reference_number" => (string) $parcel->order_number,
                "service" => (string) $parcel->delivery_sevice,
                "payed_by" => 1,
                "delivery_type" => (int) $deliveryTypeId,
                "payment_value" => (float) isset($parcel->cod_amount) ? $parcel->cod_amount : null,
                "value" => (float) $parcel->parcel_value,
                "parcel_size" => (string) isset($parcel->parcel_size) ? $parcel->parcel_size : null,

                "reference_field_B" => (string) $parcel->parcel_remark,

                "sender" => [
                    "sender_name" => (string) $parcel->sender_name,
                    "sender_phone" => (string) $parcel->sender_phone,
                    "sender_email" => (string) isset($parcel->sender_email) ? $parcel->sender_email : null,
                    "sender_street" => (string) $parcel->sender_adress,
                    "sender_hnum" => (string) ".",
                    "sender_hnum_suffix" => (string) ".",
                    "sender_zip" => (string) $parcel->sender_postal_code,
                    "sender_city" => (string) $parcel->sender_city,
                    "sender_pickup_center" => null,
                ],

                "recipient" => [
                    "recipient_name" => (string) $parcel->recipient_name,
                    "recipient_phone" => (string) $parcel->recipient_phone,
                    "recipient_email" => (string) isset($parcel->recipient_email) ? $parcel->recipient_email : null,
                    "recipient_street" => (string) $parcel->recipient_adress,
                    "recipient_hnum" => (string) ".",
                    "recipient_hnum_suffix" => (string) ".",
                    "recipient_zip" => (string) $parcel->recipient_postal_code,
                    "recipient_city" => (string) $parcel->recipient_city,
                    "recipient_pickup_center" => (string) isset($parcel->location_id) ? $parcel->location_id : null,
                ],
                "additional_services" => $additionalServices,
                "packages" => [
                    [
                        "barcode" => "",
                        "barcode_type" => 1,
                        "barcode_client" => (string) $parcel->order_number,
                        "weight" => (float) $parcel->parcel_weight
                    ]
                ]
            ]
        ;
    }

    protected function validateParcel($parcel)
    {
        $rules = [
            'order_number' => 'required|string|max:255',
            'cod_amount' => 'nullable|numeric|min:0',
            'parcel_ref_1' => 'nullable|string|max:255',
            'parcel_ref_2' => 'nullable|string|max:255',

            'sender_name' => 'required|string|max:255',
            'sender_phone' => 'required|string|max:30',
            'sender_email' => 'nullable|email|max:255',
            'sender_adress' => 'required|string|max:255',
            'sender_postal_code' => 'required|string|max:5|regex:/^[0-9]+$/',
            'sender_city' => 'required|string|max:255',

            'recipient_name' => 'required|string|max:255',
            'recipient_phone' => 'required|string|max:30',
            'recipient_email' => 'nullable|email|max:255',
            'recipient_adress' => 'required|string|max:255',
            'recipient_postal_code' => 'required|string|max:5|regex:/^[0-9]+$/',
            'recipient_city' => 'required|string|max:255',

            'location_id' => 'nullable|string|max:50',
            'parcel_weight' => 'required|numeric|min:0.01',
        ];

        $messages = [
            'order_number.required' => 'Broj narudžbe je obavezan.',
            'delivery_type.required' => 'Tip dostave je obavezan.',
            'cod_amount.numeric' => 'Iznos pouzeća mora biti broj.',
            'sender_email.email' => 'Email pošiljatelja mora biti ispravan.',
            'recipient_email.email' => 'Email primatelja mora biti ispravan.',
            'parcel_weight.required' => 'Težina paketa je obavezna.',
            'parcel_weight.min' => 'Težina paketa mora biti veća od 0.',
        ];

        $data = (array) $parcel;

        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return true;
    }


}
