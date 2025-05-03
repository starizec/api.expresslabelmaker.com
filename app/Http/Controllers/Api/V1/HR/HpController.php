<?php

namespace App\Http\Controllers\Api\V1\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Services\Logger\ApiErrorLogger;
use App\Services\AdressService;
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
        24 => "Paletizirana pošiljka",
        26 => "Paket 24 D+1",
        29 => "Paket 24 D+2",
        32 => "Paket 24 D+3",
        38 => "Paket 24 D+4",
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
                    'parcels' => [$this->prepareParcelPayload($parcel)]
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
                $pl_numbers[]["barcode"] = $package->barcode;
            }
        }

        $parcelLabelResponse = Http::withoutVerifying()
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
                'Accept' => 'application/json'
            ])
            ->get(
                config('urls.hr.hp') . "/shipment/get_shipping_labels",
                [
                    'client_reference_number' => $parcel->order_number,
                    'barcodes' => $pl_numbers,
                    'A4' => true
                ]
            );

        $parcelLabelResponseJson = json_decode($parcelLabelResponse->body());

        if (!$parcelLabelResponse->successful() || $parcelLabelResponseJson->ErrorCode != null) {
            $error_message = $parcelLabelResponse->successful()
                ? $parcelLabelResponseJson->ErrorCode . " - " . $parcelLabelResponseJson->ErrorMessage
                : $parcelLabelResponse->status() . " - Overseas Server error";


            ApiErrorLogger::apiError(
                $this->courier->country->short . ' - ' . $this->courier->name . ' - ' . $this->user->domain . ' - ' . $error_message,
                $request,
                $error_message,
                __CLASS__ . '@' . __FUNCTION__ . '::' . __LINE__
            );

            return response()->json([
                "errors" => [
                    [
                        'order_number' => $parcel->order_number ?? 'unknown',
                        'error_message' => 'HP poruka: ' . $error_message,
                        'error_code' => '603'
                    ]
                ]
            ], $parcelLabelResponse->status());
        }

        UserService::addUsage($this->user);

        ApiUsageLogger::apiUsage($this->courier->country->short . ' - ' . $this->courier->name . ' - ' . $this->user->domain, $request);

        return response()->json([
            "data" => [
                "parcels" => $pl_numbers,
                "label" => $parcelLabelResponseJson->PackageLabel
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
        $additionalServices = [["additional_service_id" => 30]];

        if ($parcel->cod_amount) {
            $additionalServices[] = ["additional_service_id" => 9];
        }

        $adressService = new AdressService();
        $senderAdress = $adressService->splitAddress($parcel->sender_adress);
        $recipientAdress = $adressService->splitAddress($parcel->recipient_adress);

        return [
            "client_reference_number" => (string) $parcel->order_number,
            "service" => (string) 26,
            "payed_by" => 1,
            "delivery_type" => (int) $this->deliveryTypes[$parcel->delivery_type],
            "payment_value" => (float) $parcel->cod_amount ?? null,
            "value" => (float) $parcel->cod_amount ?? null,
            "parcel_size" => (string) $parcel->cod_amount ? "M" : null,

            "reference_field_B" => (string) $parcel->order_number,
            "reference_field_C" => (string) $parcel->parcel_ref_1 ?? null,
            "reference_field_D" => (string) $parcel->parcel_ref_2 ?? null,

            "sender" => [
                "sender_name" => (string) $parcel->sender_name,
                "sender_phone" => (string) $parcel->sender_phone,
                "sender_email" => (string) $parcel->sender_email ?? null,
                "sender_street" => (string) $senderAdress['street'],
                "sender_hnum" => (string) $senderAdress['house_number'],
                "sender_hnum_suffix" => (string) $senderAdress['house_number_suffix'],
                "sender_zip" => (string) $parcel->sender_postal_code,
                "sender_city" => (string) $parcel->sender_city,
                "sender_pickup_center" => null,
            ],

            "recipient" => [
                "recipient_name" => (string) $parcel->recipient_name,
                "recipient_phone" => (string) $parcel->recipient_phone,
                "recipient_email" => (string) $parcel->recipient_email ?? null,
                "recipient_street" => (string) $recipientAdress['street'],
                "recipient_hnum" => (string) $recipientAdress['house_number'],
                "recipient_hnum_suffix" => (string) $recipientAdress['house_number_suffix'],
                "recipient_zip" => (string) $parcel->recipient_postal_code,
                "recipient_city" => (string) $parcel->recipient_city,
                "recipient_pickup_center" => (string) $parcel->location_id ?? null,
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
        ];
    }

    protected function validateParcel($parcel)
    {
        $rules = [
            'order_number' => 'required|string|max:255',
            'delivery_type' => 'required|string|in:ADR,PU,PAK',
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
