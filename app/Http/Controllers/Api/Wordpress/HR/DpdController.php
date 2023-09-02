<?php

namespace App\Http\Controllers\Api\Wordpress\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class DpdController extends Controller
{
    public function printLabel(Request $request)
    {
        $requestBody = $request->getContent();
        $jsonData = json_decode($requestBody);

        if (!isset($jsonData->user, $jsonData->parcel)) {
            return response()->json([
                "status" => 400,
                "info" => "Bad Request",
                "data" => "Missing 'user' and/or 'parcel' properties in JSON data"
            ], 400);
        }

        $user = $jsonData->user;
        $parcel = $jsonData->parcel;

        $parcel_response = Http::post(config('urls.hr.dpd') . '/parcel/parcel_import?' .
            "username=$user->username&" .
            "password=$user->password&" .
            "cod_amount=$parcel->cod_amount&" .
            "name1=$parcel->name1&" .
            "street=$parcel->street&" .
            "rPropNum=$parcel->rPropNum&" .
            "city=$parcel->city&" .
            "country=$parcel->country&" .
            "pcode=$parcel->pcode&" .
            "email=$parcel->email&" .
            "sender_remark=$parcel->sender_remark&" .
            "weight=$parcel->weight&" .
            "order_number=$parcel->order_number&" .
            "cod_purpose=$parcel->cod_purpose&" .
            "parcel_type=$parcel->parcel_type&" .
            "num_of_parcel=$parcel->num_of_parcel");
        $parcel_response_json = json_decode($parcel_response->body());

        $pl_numbers = implode(",", $parcel_response_json->pl_number);

        $parcel_label_response = Http::accept('*/*')->withHeaders([
            "xhrFields" => [
                'responseType' => 'blob'
            ],
            "content-type" => "application/x-www-form-urlencoded"
        ])->post(config('urls.hr.dpd') . '/parcel/parcel_print?' .
            "username=$user->username&" .
            "password=$user->password&" .
            "parcels=$pl_numbers");

        if ($parcel_label_response->successful()) {
            $response = [
                "status" => 200,
                "info" => "Success",
                "data" => [
                    "parcels" => $pl_numbers,
                    "labels" => base64_encode($parcel_label_response->body()) // Encode as base64 for JSON
                ]
            ];

            return response()->json($response, 200);
        } else {
            return response()->json([
                "status" => $parcel_label_response->status(),
                "info" => $parcel_label_response->reason(),
                "data" => []
            ], $parcel_label_response->status());
        }
    }

    public function printLabels()
    {
    }

    public function getPackageStatus()
    {
    }
}
