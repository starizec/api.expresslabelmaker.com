<?php

namespace App\Services\HR;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DpdService
{
    public function getParcelStatus($parcel_number)
    {
        $secret = config('misc.hr.dpd.status_secret');

        $parcelStatusResponse = Http::accept('*/*')->withHeaders([
            "content-type" => "application/x-www-form-urlencoded"
        ])->post(config('urls.hr.dpd') .
            "/parcel/parcel_status?secret=" .
            "$secret&parcel_number=$parcel_number");

        if (!$parcelStatusResponse->successful()) {
            return [
                "errors" => [
                    [
                        "error_id" => 123456,
                        "error_details" => $parcelStatusResponse->status() . " - DPD Server error"
                    ]
                ],
            ];
        }

        $parcelStatusResponseJson = json_decode($parcelStatusResponse->body());

        return [
            "parcel_number" => $parcel_number,
            "parcel_status" => $parcelStatusResponseJson->parcel_status
        ];
    }
}
