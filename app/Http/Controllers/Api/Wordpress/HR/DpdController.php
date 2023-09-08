<?php

namespace App\Http\Controllers\Api\Wordpress\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Helpers\MultiParcelResponse;
use App\Helpers\MultiParcelError;

use function PHPUnit\Framework\isNull;

class DpdController extends Controller
{
    public function printLabel(Request $request)
    {
        $requestBody = $request->getContent();
        $jsonData = json_decode($requestBody);

        if (!isset($jsonData->user, $jsonData->parcel)) {
            return response()->json([
                "errors" => [
                    [
                        "error_id" => 123456,
                        "error_details" => "Missing object properties."
                    ]
                ],
            ], 400);
        }

        $user = $jsonData->user;
        $parcel = $jsonData->parcel;

        $parcelResponse = Http::post(config('urls.hr.dpd') .
            '/parcel/parcel_import?' .
            "username=$user->username&password=$user->password&" .
            http_build_query($parcel));

        $parcelResponseJson = json_decode($parcelResponse->body());

        if ($parcelResponse->successful()) {
            if ($parcelResponseJson->status === 'err') {
                return response()->json([
                    "errors" => [
                        [
                            "error_id" => 123456,
                            "error_details" => $parcelResponseJson->status . ' - ' . $parcelResponseJson->errlog
                        ]
                    ],
                ], 400);
            }
        } else {
            return response()->json([
                "errors" => [
                    [
                        "error_id" => 123456,
                        "error_details" => $parcelResponse->status() . " - DPD Server error"
                    ]
                ]
            ], $parcelResponse->status());
        }

        $pl_numbers = implode(",", $parcelResponseJson->pl_number);

        $parcelLabelResponse = Http::accept('*/*')->withHeaders([
            "xhrFields" => [
                'responseType' => 'blob'
            ],
            "content-type" => "application/x-www-form-urlencoded"
        ])->post(config('urls.hr.dpd') .
            '/parcel/parcel_print?' .
            "username=$user->username&password=$user->password&" .
            "parcels=$pl_numbers");

        $parcelLabelResponseJson = json_decode($parcelLabelResponse->body());

        if ($parcelLabelResponse->successful()) {
            if (isset($parcelLabelResponseJson->status)) {
                return response()->json([
                    "errors" => [
                        [
                            "error_id" => 123456,
                            "error_details" => $parcelLabelResponseJson->status . ' - ' . $parcelLabelResponseJson->errlog
                        ]
                    ],
                ], 400);
            }
        } else {
            return response()->json([
                "errors" => [
                    [
                        "error_id" => 123456,
                        "error_details" => $parcelLabelResponse->status() . ' - DPD Server error'
                    ]
                ]
            ], $parcelLabelResponse->status());
        }

        return response()->json([
            "data" => [
                "parcels" => $pl_numbers,
                "labels" => base64_encode($parcelLabelResponse->body()) // Encode as base64 for JSON
            ]
        ], 201);
    }

    public function printLabels(Request $request)
    {
        $requestBody = $request->getContent();
        $jsonData = json_decode($requestBody);

        if (!isset($jsonData->user, $jsonData->parcels)) {
            return response()->json([
                "errors" => [
                    [
                        "error_id" => 123456,
                        "error_details" => "Missing object properties."
                    ]
                ],
            ], 400);
        }

        $user = $jsonData->user;
        $parcels = $jsonData->parcels;

        $data = [];
        $errors = [];
        $all_pl_numbers = [];

        foreach ($parcels as $parcel) {
            $parcelResponse = Http::post(config('urls.hr.dpd') .
                '/parcel/parcel_import?' .
                "username=$user->username&password=$user->password&" .
                http_build_query($parcel->parcel));

            $parcelResponseJson = json_decode($parcelResponse->body());

            if ($parcelResponse->successful()) {
                if ($parcelResponseJson->status === 'err') {
                    $errors[] = new MultiParcelError($parcel->order_number, 1, $parcelResponseJson->status . ' ' . $parcelResponseJson->errlog);
                }
            } else {
                $errors[] = new MultiParcelError($parcel->order_number, 1, $parcelResponse->status() . 'DPD Server error');
            }

            $all_pl_numbers[] = $parcelResponseJson->pl_number;
            $pl_numbers = implode(",", $parcelResponseJson->pl_number);

            $parcelLabelResponse = Http::accept('*/*')->withHeaders([
                "xhrFields" => [
                    'responseType' => 'blob'
                ],
                "content-type" => "application/x-www-form-urlencoded"
            ])->post(config('urls.hr.dpd') .
                '/parcel/parcel_print?' .
                "username=$user->username&password=$user->password&" .
                "parcels=$pl_numbers");

            $parcelLabelResponseJson = json_decode($parcelLabelResponse->body());

            if ($parcelLabelResponse->successful()) {
                if (isset($parcelLabelResponseJson->status)) {
                    $errors[] = new MultiParcelError($parcel->order_number, 1, $parcelLabelResponseJson->status . ' ' . $parcelLabelResponseJson->errlog);
                }
            } else {
                $errors[] = new MultiParcelError($parcel->order_number, 1, $parcelLabelResponse->status() . 'DPD Server error');
            }

            $data[] = new MultiParcelResponse($parcel->order_number, $pl_numbers, base64_encode($parcelLabelResponse->body()));
        }

        $all_pl_numbers = implode(',', array_merge(...$all_pl_numbers));

        $allParcelLabelResponse = Http::accept('*/*')->withHeaders([
            "xhrFields" => [
                'responseType' => 'blob'
            ],
            "content-type" => "application/x-www-form-urlencoded"
        ])->post(config('urls.hr.dpd') .
            '/parcel/parcel_print?' .
            "username=$user->username&password=$user->password&" .
            "parcels=$all_pl_numbers");

        $allParcelLabelResponseJson = json_decode($allParcelLabelResponse->body());

        if ($allParcelLabelResponse->successful()) {
            if (isset($allParcelLabelResponseJson->status)) {
                return response()->json([
                    "errors" => [
                        [
                            "error_id" => 123456,
                            "error_details" => $allParcelLabelResponseJson->status . ' ' . $allParcelLabelResponseJson->errlog
                        ]
                    ],
                ], 400);
            }
        } else {
            return response()->json([
                "errors" => [
                    [
                        "error_id" => 123456,
                        "error_details" => $allParcelLabelResponse->status() . " - DPD Server error"
                    ]
                ]
            ], $allParcelLabelResponse->status());
        }

        return response()->json([
            "data" => $data,
            "errors" => $errors,
            "parcel" => base64_encode($allParcelLabelResponse->body())
        ], 201);
    }

    public function collectionRequest(Request $request)
    {

        $requestBody = $request->getContent();
        $jsonData = json_decode($requestBody);

        if (!isset($jsonData->user, $jsonData->parcel)) {
            return response()->json([
                "errors" => [
                    [
                        "error_id" => 123456,
                        "error_details" => "Missing object properties."
                    ]
                ],
            ], 400);
        }

        $user = $jsonData->user;
        $parcel = $jsonData->parcel;

        $parcelResponse = Http::accept('*/*')->withHeaders([
            "content-type" => "application/x-www-form-urlencoded"
        ])->post(config('urls.hr.dpd') .
            '/collection_request/cr_import?' .
            "username=$user->username&password=$user->password&" .
            http_build_query($parcel));

        $parcelResponseJson = json_decode($parcelResponse->body());

        if ($parcelResponse->successful()) {
            if ($parcelResponseJson->status === 'Error') {
                return response()->json([
                    "errors" => [
                        [
                            "error_id" => 123456,
                            "error_details" => $parcelResponseJson->status . ' ' . $parcelResponseJson->errlog
                        ]
                    ],
                ], 400);
            }

            if ($parcelResponseJson->reference === null) {
                return response()->json([
                    "errors" => [
                        [
                            "error_id" => 123456,
                            "error_details" => 'Missing parcel data.'
                        ]
                    ],
                ], 400);
            }
        } else {
            return response()->json([
                "errors" => [
                    [
                        "error_id" => 123456,
                        "error_details" => $parcelResponse->status() . " - DPD Server error"
                    ]
                ]
            ], $parcelResponse->status());
        }

        return response()->json([
            "data" => [
                "reference" => substr($parcelResponseJson->reference, 1, -1),
                "code" => $parcelResponseJson->code
            ]
        ], 201);
    }

    public function getParcelStatus(Request $request)
    {
        $requestBody = $request->getContent();
        $jsonData = json_decode($requestBody);

        if (!isset($jsonData->user, $jsonData->parcel)) {
            return response()->json([
                "errors" => [
                    [
                        "error_id" => 123456,
                        "error_details" => "Missing object properties."
                    ]
                ],
            ], 400);
        }

        $parcel = $jsonData->parcel;
        $secret = config('misc.hr.dpd.status_secret');

        $parcelStatusResponse = Http::accept('*/*')->withHeaders([
            "content-type" => "application/x-www-form-urlencoded"
        ])->post(config('urls.hr.dpd') .
            "/parcel/parcel_status?secret=" .
            "$secret&" .
            http_build_query($parcel));

        if (!$parcelStatusResponse->successful()) {
            return response()->json([
                "errors" => [
                    [
                        "error_id" => 123456,
                        "error_details" => $parcelStatusResponse->status() . " - DPD Server error"
                    ]
                ],
            ], $parcelStatusResponse->status());
        }

        $parcelStatusResponseJson = json_decode($parcelStatusResponse->body());

        return response()->json([
            "data" => [
                "parcel_number" => $parcel->parcel_number,
                "parcel_status" => $parcelStatusResponseJson->parcel_status
            ]
        ], 201);
    }
}
