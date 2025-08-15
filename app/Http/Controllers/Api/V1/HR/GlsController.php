<?php

namespace App\Http\Controllers\Api\V1\HR;

use App\Http\Controllers\Controller;
use App\Services\AdressService;
use Illuminate\Http\Request;
use App\Models\Courier;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Services\Logger\ApiErrorLogger;
use Illuminate\Support\Facades\Http;
use App\Services\UserService;
use App\Services\Logger\ApiUsageLogger;
use DateInterval;
use App\Models\DeliveryLocationHeader;
use App\Models\DeliveryLocation;
use Illuminate\Support\Facades\Log;
class GlsController extends Controller
{
    protected $courier;
    protected $user;

    protected $successfulStatusCodes = [
        '5',
        '8',
        '54',
        '55',
        '58',
        '59'
    ];

    protected $unsuccessfulStatusCodes = [
        '11',
        '12',
        '13',
        '14',
        '15',
        '16',
        '17',
        '18',
        '19',
        '20',
        '23',
        '25',
        '28',
        '29',
        '30',
        '31',
        '33',
        '34',
        '35',
        '36',
        '38',
        '39',
        '40',
        '42',
        '43',
        '44',
        '60',
        '62',
        '66',
        '68',
        '69',
        '70',
        '71'
    ];


    public function __construct()
    {
        $this->courier = Courier::where('name', 'GLS')
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
        $pl_numbers = [];
        $printPosition = 1;
        $printerType = "A4_4x1";

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
                            'error_code' => '702'
                        ]
                    ]
            ], 422);
        }

        $printPosition = $parcel->print_position;
        $printerType = $parcel->printer_type;
        Log::info(json_encode($this->prepareParcelPayload($parcel)));
        $parcelResponse = Http::withoutVerifying()
            ->post(
                config('urls.hr.gls') . "/ParcelService.svc/json/PrintLabels",
                [
                    "Username" => $this->user->username,
                    "Password" => $this->passwordHash($this->user->password),
                    "WebshopEngine" => "Woocommerce",
                    "PrintPosition" => $printPosition,
                    "ShowPrintDialog" => 0,
                    "TypeOfPrinter" => $printerType,
                    "ParcelList" => [$this->prepareParcelPayload($parcel)],
                ]
            );



        $parcelResponseJson = json_decode($parcelResponse->body());

        if (!$parcelResponse->successful() || ($parcelResponse->successful() && count($parcelResponseJson->PrintLabelsErrorList) > 0)) {
            foreach ($parcelResponseJson->PrintLabelsErrorList as $error) {
                $errors[] = $error->ErrorCode . " - " . $error->ErrorDescription;
            }

            $error_message = $parcelResponse->successful()
                ? implode(' | ', collect($errors)->flatten()->all())
                : $parcelResponse->status() . " - GLS Server error";

            ApiErrorLogger::apiError(
                $this->courier->country->short . ' - ' . $this->courier->name . ' - ' . $this->user->domain . ' - ' . $error_message,
                $request,
                $error_message,
                __CLASS__ . '@' . __FUNCTION__ . '::' . __LINE__
            );

            return response()->json([
                "errors" => [
                    'order_number' => $parcel->order_number ?? 'unknown',
                    'error_message' => 'GLS poruka: ' . $error_message,
                    'error_code' => '602'
                ]
            ], $parcelResponse->status());
        }

        foreach ($parcelResponseJson->PrintLabelsInfoList as $parcelInfo) {
            $pl_numbers[] = $parcelInfo->ParcelNumber;
        }

        $pl_numbers = implode(",", $pl_numbers);

        UserService::addUsage($this->user);

        ApiUsageLogger::apiUsage($this->courier->country->short . ' - ' . $this->courier->name . ' - ' . $this->user->domain, $request);

        return response()->json([
            "data" => [
                "order_number" => $parcel->order_number,
                "parcels" => $pl_numbers,
                "label" => $this->labelToBase64($parcelResponseJson->Labels)
            ]
        ], 201);
    }

    public function createLabels(Request $request)
    {
        $requestBody = $request->getContent();
        $jsonData = json_decode($requestBody);

        $this->user = $jsonData->user;
        $parcels = $jsonData->parcels;

        $data = [];
        $errors = [];
        $allparcels = [];
        $printerType = "A4_4x1";
        $printPosition = 1;

        foreach ($parcels as $parcel) {
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

                $errors[] = [
                    'order_number' => $parcel->order_number ?? 'unknown',
                    'error_message' => $error_message,
                    'error_code' => '702'
                ];

                continue;
            }

            $allparcels[] = $this->prepareParcelPayload($parcel);
            $printPosition = $parcel->print_position;
            $printerType = $parcel->printer_type;
        }

        $parcelResponse = Http::withoutVerifying()
            ->post(
                config('urls.hr.gls') . "/ParcelService.svc/json/PrintLabels",
                [
                    "Username" => $this->user->username,
                    "Password" => $this->passwordHash($this->user->password),
                    "WebshopEngine" => "Woocommerce",
                    "PrintPosition" => $printPosition,
                    "ShowPrintDialog" => 0,
                    "TypeOfPrinter" => $printerType,
                    "ParcelList" => $allparcels,
                ]
            );

        $parcelResponseJson = json_decode($parcelResponse->body());

        $combinedErrors = [];

        foreach ($parcelResponseJson->PrintLabelsErrorList as $errorInfo) {
            $errorOrderNumber = $errorInfo->ClientReferenceList[0];

            if (!isset($combinedErrors[$errorOrderNumber])) {
                $combinedErrors[$errorOrderNumber] = [
                    'order_number' => $errorOrderNumber,
                    'error_message' => 'GLS poruka: ' . $errorInfo->ErrorCode . ' - ' . $errorInfo->ErrorDescription,
                    'error_code' => '602'
                ];
            } else {
                $combinedErrors[$errorOrderNumber]['error_message'] .= '; ' . $errorInfo->ErrorDescription;
            }

            ApiErrorLogger::apiError(
                $this->courier->country->short . ' - ' . $this->courier->name . ' - ' . $this->user->domain . ' - ' . 'GLS poruka: ' . $errorInfo->ErrorCode . ' - ' . $errorInfo->ErrorDescription,
                $request,
                'GLS poruka: ' . $errorInfo->ErrorCode . ' - ' . $errorInfo->ErrorDescription,
                __CLASS__ . '@' . __FUNCTION__ . '::' . __LINE__
            );
        }

        $errors = array_values($combinedErrors);

        foreach ($parcelResponseJson->PrintLabelsInfoList as $parcelInfo) {
            $data[] = [
                "order_number" => $parcelInfo->ClientReference,
                "parcel_number" => $parcelInfo->ParcelNumber,
                "label" => $this->labelToBase64($parcelResponseJson->Labels)
            ];
            UserService::addUsage($this->user);
        }

        ApiUsageLogger::apiUsage($this->courier->country->short . ' - ' . $this->courier->name . ' - ' . $this->user->domain, $request);

        return response()->json([
            "data" => [
                "parcels" => $data,
                "label" => $this->labelToBase64($parcelResponseJson->Labels)
            ],
            "errors" => $errors
        ], 201);
    }

    public function collectionRequest(Request $request)
    {
        $requestBody = $request->getContent();
        $jsonData = json_decode($requestBody);

        $this->user = $jsonData->user;
        $parcel = $jsonData->parcel;
        $errors = [];
        $pl_numbers = [];
        $printPosition = 1;
        $printerType = "A4_4x1";

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
                            'error_code' => '702'
                        ]
                    ]
            ], 422);
        }

        $printPosition = $parcel->print_position;
        $printerType = $parcel->printer_type;

        $parcelResponse = Http::withoutVerifying()
            ->post(
                config('urls.hr.gls') . "/ParcelService.svc/json/PrintLabels",
                [
                    "Username" => $this->user->username,
                    "Password" => $this->passwordHash($this->user->password),
                    "WebshopEngine" => "Woocommerce",
                    "PrintPosition" => $printPosition,
                    "ShowPrintDialog" => 0,
                    "TypeOfPrinter" => $printerType,
                    "ParcelList" => [$this->prepareParcelPayload($parcel)],
                ]
            );



        $parcelResponseJson = json_decode($parcelResponse->body());

        if (!$parcelResponse->successful() || ($parcelResponse->successful() && count($parcelResponseJson->PrintLabelsErrorList) > 0)) {
            foreach ($parcelResponseJson->PrintLabelsErrorList as $error) {
                $errors[] = $error->ErrorCode . " - " . $error->ErrorDescription;
            }

            $error_message = $parcelResponse->successful()
                ? implode(' | ', collect($errors)->flatten()->all())
                : $parcelResponse->status() . " - GLS Server error";

            ApiErrorLogger::apiError(
                $this->courier->country->short . ' - ' . $this->courier->name . ' - ' . $this->user->domain . ' - ' . $error_message,
                $request,
                $error_message,
                __CLASS__ . '@' . __FUNCTION__ . '::' . __LINE__
            );

            return response()->json([
                "errors" => [
                    'order_number' => $parcel->order_number ?? 'unknown',
                    'error_message' => 'GLS poruka: ' . $error_message,
                    'error_code' => '602'
                ]
            ], $parcelResponse->status());
        }

        foreach ($parcelResponseJson->PrintLabelsInfoList as $parcelInfo) {
            $pl_numbers[] = $parcelInfo->ParcelNumber;
        }

        $pl_numbers = implode(",", $pl_numbers);

        UserService::addUsage($this->user);

        ApiUsageLogger::apiUsage($this->courier->country->short . ' - ' . $this->courier->name . ' - ' . $this->user->domain, $request);

        return response()->json([
            "data" => [
                "order_number" => $parcel->order_number,
                "parcels" => $pl_numbers,
                "label" => $this->labelToBase64($parcelResponseJson->Labels)
            ]
        ], 201);
    }

    public function getParcelStatus(Request $request)
    {
        $requestBody = $request->getContent();
        $jsonData = json_decode($requestBody);

        $this->user = $jsonData->user;
        $parcels = $jsonData->parcels;

        $track_numbers = [];
        $status_response = [];

        foreach ($parcels as $parcel) {
            $statusResponse = Http::withoutVerifying()
                ->post(
                    config('urls.hr.gls') . "/ParcelService.svc/json/GetParcelStatuses",
                    [
                        "Username" => $this->user->username,
                        "Password" => $this->passwordHash($this->user->password),
                        "ParcelNumber" => $parcel->parcel_number,
                        "ReturnPOD" => false,
                        "LanguageIsoCode" => "HR"
                    ]
                );


            $statusResponseJson = json_decode($statusResponse->body());

            if (count($statusResponseJson->ParcelStatusList) > 0) {
                $status = $this->getLatestParcelStatus($statusResponseJson->ParcelStatusList);

                $status_response[] = [
                    "order_number" => $parcel->order_number,
                    "parcel_number" => $parcel->parcel_number,
                    "status_message" => $status->StatusDescription,
                    "status_code" => $status->StatusCode,
                    "status_date" => $this->formatGlsDate($status->StatusDate),
                    "color" => "#fff"
                ];
            }

            if (count($statusResponseJson->GetParcelStatusErrors) > 0) {
                $status_response[] = [
                    "order_number" => $parcel->order_number,
                    "parcel_number" => $parcel->parcel_number,
                    "status_message" => "Greška u dohvatu statusa",
                    "status_code" => "00",
                    "status_date" => now()->format('Y-m-d\TH:i:s'),
                    "color" => config('colors.error')
                ];
            }
        }

        return response()->json([
            "data" => [
                "statuses" => $status_response
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
        $service_list = [];

        if ($parcel->cod_amount && $parcel->cod_amount > 0) {
            $service_list[] = [
                "Code" => "COD"
            ];
        }

        if (isset($parcel->location_id) && $parcel->location_id != "") {
            $service_list[] = [
                "Code" => "PSD",
                "PSDParameter" => [
                    "StringValue" => $parcel->location_id
                ]
            ];
        }

        $additionalServicesIds = explode(',', $parcel->additional_services);

        foreach ($additionalServicesIds as $additionalServiceId) {
            if ($additionalServiceId == "INS") {
                $service_list[] = [
                    "Code" => "INS",
                    "INSParameter" => [
                        "Value" => $parcel->parcel_value
                    ]
                ];
            }

            if ($additionalServiceId == "FDS") {
                $service_list[] = [
                    "Code" => "FDS",
                    "FDSParameter" => [
                        "Value" => $parcel->recipient_email
                    ]
                ];
            }

            if ($additionalServiceId == "FSS") {
                $service_list[] = [
                    "Code" => "FSS",
                    "FSSParameter" => [
                        "Value" => $parcel->recipient_phone
                    ]
                ];
            }
        }

        return [
            "ClientNumber" => $this->user->client_number,
            "ClientReference" => $parcel->order_number,
            "Count" => $parcel->parcel_count,
            "CODAmount" => $parcel->cod_amount ?? null,
            "CODReference" => $parcel->order_number ?? null,
            "CODCurrency" => $parcel->cod_currency ?? null,
            "PickupAddress" => [
                "Name" => $parcel->sender_name,
                "Street" => AdressService::splitAddress($parcel->sender_adress)['street'],
                "HouseNumber" => AdressService::splitAddress($parcel->sender_adress)['house_number'],
                "City" => $parcel->sender_city,
                "ZipCode" => $parcel->sender_postal_code,
                "CountryIsoCode" => $parcel->sender_country,
                "ContactName" => $parcel->sender_name,
                "ContactPhone" => $parcel->sender_phone,
                "ContactEmail" => $parcel->sender_email
            ],
            "DeliveryAddress" => [
                "Name" => $parcel->recipient_name,
                "Street" => AdressService::splitAddress($parcel->recipient_adress)['street'],
                "HouseNumber" => AdressService::splitAddress($parcel->recipient_adress)['house_number'],
                "City" => $parcel->recipient_city,
                "ZipCode" => $parcel->recipient_postal_code,
                "CountryIsoCode" => $parcel->recipient_country,
                "ContactName" => $parcel->recipient_name,
                "ContactPhone" => $parcel->recipient_phone,
                "ContactEmail" => $parcel->recipient_email
            ],
            "ServiceList" => $service_list
        ];
    }

    protected function validateParcel($parcel)
    {
        $rules = [
            'order_number' => 'required|string|max:255',
            'cod_amount' => 'nullable|numeric|min:0',
            'parcel_ref_1' => 'nullable|string|max:255',
            'parcel_ref_2' => 'nullable|string|max:255',
            'parcel_weight' => 'required|numeric|min:0.01',
            'print_position' => 'required|integer|min:1',
            'printer_type' => 'required|string|max:255',
            'additional_services' => 'nullable|string|max:255',
            'cod_currency' => 'nullable|string|max:3',
            'parcel_count' => 'required|integer|min:1',

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

    protected function passwordHash($password)
    {
        return array_values(unpack('C*', hash('sha512', $password, true)));
    }

    protected function labelToBase64($label)
    {
        return base64_encode(implode(array_map('chr', $label)));
    }

    protected function getLatestParcelStatus(array $parcelStatusList): ?object
    {
        if (empty($parcelStatusList)) {
            return null;
        }

        usort($parcelStatusList, function ($a, $b) {
            return strtotime($a->StatusDate) <=> strtotime($b->StatusDate);
        });

        return end($parcelStatusList);
    }

    protected function getColor($status_code)
    {
        if (in_array($status_code, $this->successfulStatusCodes)) {
            return config('colors.successful');
        } else if (in_array($status_code, $this->unsuccessfulStatusCodes)) {
            return config('colors.unsuccessful');
        } else {
            return config('colors.default');
        }
    }

    protected function formatGlsDate($date)
    {
        // Parse GLS date format: /Date(1749751680000+0200)/
        if (preg_match('/\/Date\((\d+)([+-]\d{4})\)\//', $date, $matches)) {
            $timestamp = (int) ($matches[1] / 1000); // Convert milliseconds to seconds
            $timezoneOffset = $matches[2];

            // Create DateTime object from timestamp
            $dateTime = new \DateTime();
            $dateTime->setTimestamp($timestamp);

            // Apply timezone offset (convert to hours)
            $offsetHours = (int) substr($timezoneOffset, 0, 3);
            $offsetMinutes = (int) substr($timezoneOffset, 3, 2);
            $offsetSign = substr($timezoneOffset, 0, 1);

            if ($offsetSign === '+') {
                $dateTime->add(new DateInterval("PT{$offsetHours}H{$offsetMinutes}M"));
            } else {
                $dateTime->sub(new DateInterval("PT{$offsetHours}H{$offsetMinutes}M"));
            }

            // Format to ISO 8601 format: YYYY-MM-DDTHH:mm:ss+HH:mm
            return $dateTime->format('Y-m-d\TH:i:s');
        }

        // If parsing fails, return original date
        return $date;
    }
}
