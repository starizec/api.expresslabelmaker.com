<?php

namespace App\Http\Controllers\Api\V1\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

use App\Classes\MultiParcelResponse;
use App\Classes\MultiParcelError;

use App\Services\ErrorService;
use App\Services\UserService;

class DpdController extends Controller
{
    public function createLabel(Request $request)
    {
        $requestBody = $request->getContent();
        $jsonData = json_decode($requestBody);

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
                        ErrorService::write(
                            $user->email,
                            400,
                            $parcelResponseJson->status . ' - ' . $parcelResponseJson->errlog,
                            $request,
                            "App\Http\Controllers\Api\V1\HR\DpdController@createLabel::" . __LINE__,
                            json_encode($parcel)
                        )
                    ],
                ], 400);
            }
        } else {
            return response()->json([
                "errors" => [
                    ErrorService::write(
                        $user->email,
                        $parcelResponse->status(),
                        $parcelResponse->status() . " - DPD Server error",
                        $request,
                        "App\Http\Controllers\Api\V1\HR\DpdController@createLabel::" . __LINE__,
                        json_encode($parcel)
                    )
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
                        ErrorService::write(
                            $user->email,
                            400,
                            $parcelLabelResponseJson->status . ' - ' . $parcelLabelResponseJson->errlog,
                            $request,
                            "App\Http\Controllers\Api\V1\HR\DpdController@createLabel::" . __LINE__,
                            json_encode($parcel)
                        )
                    ],
                ], 400);
            }
        } else {
            return response()->json([
                "errors" => [
                    ErrorService::write(
                        $user->email,
                        $parcelLabelResponse->status(),
                        $parcelLabelResponse->status() . ' - DPD Server error',
                        $request,
                        "App\Http\Controllers\Api\V1\HR\DpdController@createLabel::" . __LINE__,
                        json_encode($parcel)
                    )
                ]
            ], $parcelLabelResponse->status());
        }

        UserService::addUsage($user);

        return response()->json([
            "data" => [
                "parcels" => $pl_numbers,
                "label" => base64_encode($parcelLabelResponse->body()) // Encode as base64 for JSON
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

        foreach ($parcels as $parcel) {
            $parcelResponse = Http::post(config('urls.hr.dpd') .
                '/parcel/parcel_import?' .
                "username=$user->username&password=$user->password&" .
                http_build_query($parcel->parcel));

            $parcelResponseJson = json_decode($parcelResponse->body());

            if ($parcelResponse->successful()) {
                if ($parcelResponseJson->status === 'err') {
                    $error = ErrorService::write(
                        $user->email,
                        $parcelResponseJson->status,
                        $parcelResponseJson->status . ' ' . $parcelResponseJson->errlog,
                        $request,
                        "App\Http\Controllers\Api\V1\HR\DpdController@createLabels::" . __LINE__,
                        json_encode($parcel)
                    );

                    $errors[] = new MultiParcelError($parcel->order_number, $error['error_id'], $error['error_details']);
                }
            } else {
                $error = ErrorService::write(
                    $user->email,
                    $parcelResponse->status(),
                    $parcelResponse->status() . 'DPD Server error',
                    $request,
                    "App\Http\Controllers\Api\V1\HR\DpdController@createLabels::" . __LINE__,
                    json_encode($parcel)
                );

                $errors[] = new MultiParcelError($parcel->order_number, $error['error_id'], $error['error_details']);
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
                    $error = ErrorService::write(
                        $user->email,
                        $parcelLabelResponseJson->status,
                        $parcelLabelResponseJson->status . ' ' . $parcelLabelResponseJson->errlog,
                        $request,
                        "App\Http\Controllers\Api\V1\HR\DpdController@createLabels::" . __LINE__,
                        json_encode($parcel)
                    );

                    $errors[] = new MultiParcelError($parcel->order_number, $error['error_id'], $error['error_details']);
                }
            } else {
                $error = ErrorService::write(
                    $user->email,
                    $parcelLabelResponse->status(),
                    $parcelLabelResponse->status() . 'DPD Server error',
                    $request,
                    "App\Http\Controllers\Api\V1\HR\DpdController@createLabels::" . __LINE__,
                    json_encode($parcel)
                );

                $errors[] = new MultiParcelError($parcel->order_number, $error['error_id'], $error['error_details']);
            }

            UserService::addUsage($user);
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
                        $error = ErrorService::write(
                            $user->email,
                            $allParcelLabelResponseJson->status,
                            $allParcelLabelResponseJson->status . ' ' . $allParcelLabelResponseJson->errlog,
                            $request,
                            "App\Http\Controllers\Api\V1\HR\DpdController@createLabels::" . __LINE__,
                            json_encode($parcel)
                        )
                    ],
                ], 400);
            }
        } else {
            return response()->json([
                "errors" => [
                    $error = ErrorService::write(
                        $user->email,
                        $allParcelLabelResponse->status(),
                        $allParcelLabelResponse->status() . " - DPD Server error",
                        $request,
                        "App\Http\Controllers\Api\V1\HR\DpdController@createLabels::" . __LINE__,
                        json_encode($parcel)
                    )
                ]
            ], $allParcelLabelResponse->status());
        }

        return response()->json([
            "data" => [
                "label" => base64_encode($allParcelLabelResponse->body()),
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
                        ErrorService::write(
                            $user->email,
                            400,
                            $parcelResponseJson->status . ' ' . $parcelResponseJson->errlog,
                            $request,
                            "App\Http\Controllers\Api\V1\HR\DpdController@collectionRequest::" . __LINE__,
                            json_encode($parcel)
                        )
                    ],
                ], 400);
            }

            if ($parcelResponseJson->reference === null) {
                return response()->json([
                    "errors" => [
                        ErrorService::write(
                            $user->email,
                            400,
                            'Missing parcel data.',
                            $request,
                            "App\Http\Controllers\Api\V1\HR\DpdController@collectionRequest::" . __LINE__,
                            json_encode($parcel)
                        )
                    ],
                ], 400);
            }
        } else {
            return response()->json([
                "errors" => [
                    ErrorService::write(
                        $user->email,
                        $parcelResponse->status(),
                        $parcelResponse->status() . " - DPD Server error",
                        $request,
                        "App\Http\Controllers\Api\V1\HR\DpdController@collectionRequest::" . __LINE__,
                        json_encode($parcel)
                    )
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
}
