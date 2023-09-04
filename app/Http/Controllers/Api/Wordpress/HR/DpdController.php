<?php

namespace App\Http\Controllers\Api\Wordpress\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
                        "status" => 400,
                        "id" => 123456,
                        "type_id" => "Dodati vrste errora",
                        "courier_id" => "DPD",
                        "title" =>  "Invalid Attribute",
                        "detail" => "Missing user property."
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
                            "status" => 1,
                            "id" => 123456,
                            "type_id" => "Dodati vrste errora",
                            "courier_id" => "DPD",
                            "title" =>  $parcelResponseJson->status,
                            "detail" => $parcelResponseJson->errlog
                        ]
                    ],
                ], 400);
            }
        } else {
            return response()->json([
                "errors" => [
                    [
                        "status" => $parcelResponse->status(),
                        "id" => 123456,
                        "type_id" => "Dodati vrste errora",
                        "courier_id" => "DPD",
                        "title" =>  "DPD error",
                        "detail" => "DPD server je vratio grešku."
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
                            "status" => 2,
                            "id" => 123456,
                            "type_id" => "Dodati vrste errora",
                            "courier_id" => "DPD",
                            "title" =>  $parcelLabelResponseJson->status,
                            "detail" => $parcelLabelResponseJson->errlog
                        ]
                    ],
                ], 400);
            }
        } else {
            return response()->json([
                "errors" => [
                    [
                        "status" => $parcelLabelResponse->status(),
                        "id" => 123456,
                        "type_id" => "Dodati vrste errora",
                        "courier_id" => "DPD",
                        "title" =>  "DPD error",
                        "detail" => "DPD server je vratio grešku."
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

    public function printLabels()
    {
    }

    public function getParcelStatus(Request $request)
    {
        $requestBody = $request->getContent();
        $jsonData = json_decode($requestBody);

        if (!isset($jsonData->user, $jsonData->parcel)) {
            return response()->json([
                "errors" => [
                    [
                        "status" => 400,
                        "id" => 123456,
                        "type_id" => "Dodati vrste errora",
                        "courier_id" => "DPD",
                        "title" =>  "Invalid Attribute",
                        "detail" => "Missing user property."
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
                        "status" => $parcelStatusResponse->status(),
                        "id" => 123456,
                        "type_id" => "Dodati vrste errora",
                        "courier_id" => "DPD",
                        "title" =>  "DPD error",
                        "detail" => "DPD server je vratio grešku."
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
