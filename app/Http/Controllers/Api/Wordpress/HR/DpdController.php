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

        $parcel_response = Http::post(config('urls.hr.dpd') .
            '/parcel/parcel_import?' .
            "username=$user->username&password=$user->password&" .
            http_build_query($parcel));

        $parcel_response_json = json_decode($parcel_response->body());

        if ($parcel_response->successful()) {
            if ($parcel_response_json->status === 'err') {
                return response()->json([
                    "errors" => [
                        [
                            "status" => 1,
                            "id" => 123456,
                            "type_id" => "Dodati vrste errora",
                            "courier_id" => "DPD",
                            "title" =>  $parcel_response_json->status,
                            "detail" => $parcel_response_json->errlog
                        ]
                    ],
                ], 400);
            }
        } else {
            return response()->json([
                "errors" => [
                    [
                        "status" => $parcel_response->status(),
                        "id" => 123456,
                        "type_id" => "Dodati vrste errora",
                        "courier_id" => "DPD",
                        "title" =>  "DPD error",
                        "detail" => "DPD server je vratio grešku."
                    ]
                ]
            ], $parcel_response->status());
        }

        $pl_numbers = implode(",", $parcel_response_json->pl_number);

        $parcel_label_response = Http::accept('*/*')->withHeaders([
            "xhrFields" => [
                'responseType' => 'blob'
            ],
            "content-type" => "application/x-www-form-urlencoded"
        ])->post(config('urls.hr.dpd') .
            '/parcel/parcel_print?' .
            "username=$user->username&password=$user->password&" .
            "parcels=$pl_numbers");

        if ($parcel_label_response->successful()) {
            if ($parcel_response_json->status === 'err') {
                return response()->json([
                    "errors" => [
                        [
                            "status" => 2,
                            "id" => 123456,
                            "type_id" => "Dodati vrste errora",
                            "courier_id" => "DPD",
                            "title" =>  $parcel_response_json->status,
                            "detail" => $parcel_response_json->errlog
                        ]
                    ],
                ], 400);
            }
        } else {
            return response()->json([
                "errors" => [
                    [
                        "status" => $parcel_label_response->status(),
                        "id" => 123456,
                        "type_id" => "Dodati vrste errora",
                        "courier_id" => "DPD",
                        "title" =>  "DPD error",
                        "detail" => "DPD server je vratio grešku."
                    ]
                ]
            ], $parcel_label_response->status());
        }

        return response()->json([
            "data" => [
                "parcels" => $pl_numbers,
                "labels" => base64_encode($parcel_label_response->body()) // Encode as base64 for JSON
            ]
        ], 201);
    }

    public function printLabels()
    {
    }

    public function getPackageStatus()
    {
    }
}
