<?php

namespace App\Http\Controllers\Api\V1\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

use App\Classes\MultiParcelResponse;
use App\Classes\MultiParcelError;

use App\Services\ErrorService;
use App\Services\UserService;

class OverseasController extends Controller
{
    public function createLabel(Request $request)
    {
        $requestBody = $request->getContent();
        $jsonData = json_decode($requestBody);

        $user = $jsonData->user;
        $parcel = $jsonData->parcel;

        $parcelResponse = Http::post(
            config('urls.hr.overseas') .
                '/createshipment?' .
                "apikey=$user->apiKey",
            [
                "Cosignee" => [
                    "Name" => $parcel->name1,
                    "CountryCode" => "HR",
                    "Zipcode" => $parcel->pcode,
                    "City" => $parcel->city,
                    "StreetAndNumber" => $parcel->rPropNum,
                    "NotifyGSM" => $parcel->phone,
                    "NotifyEmail" => $parcel->email,
                ],
                "UnitAmount" => $parcel->num_of_parcel,
                "Ref1" => $parcel->order_number,
                "NumberOfCollies" => $parcel->num_of_parcel,
                "CODValue" => !empty($parcel->cod_amount) ? $parcel->cod_amount : null,
                "CODCurrency" => !empty($parcel->cod_amount) ? 0 : null,
                "ExWorksType" => !empty($parcel->cod_amount) ? 4 : null,
                "DeliveryRemark" => $parcel->sender_remark,
            ]
        );

        $parcelResponseJson = json_decode($parcelResponse->body());

        if ($parcelResponse->successful()) {
            if ($parcelResponseJson->status > 0) {
                return response()->json([
                    "errors" => [
                        ErrorService::write(
                            $user->email,
                            400,
                            $parcelResponseJson->status . ' - ' . json_encode($parcelResponseJson->error),
                            $request,
                            "App\Http\Controllers\Api\V1\HR\OverseasController@createLabel::" . __LINE__,
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
                        $parcelResponse->status() . " - Overseas Server error",
                        $request,
                        "App\Http\Controllers\Api\V1\HR\OverseasController@createLabel::" . __LINE__,
                        json_encode($parcel)
                    )
                ]
            ], $parcelResponse->status());
        }

        $pl_numbers = $parcelResponseJson->shipmentid;

        $parcelLabelResponse = Http::accept('*/*')->withHeaders([
            "xhrFields" => [
                'responseType' => 'blob'
            ],
            "content-type" => "application/x-www-form-urlencoded"
        ])->post(
            config('urls.hr.overseas') .
                '/commitshipments?' .
                "apikey=$user->apiKey",
            [$pl_numbers]
        );

        if ($parcelLabelResponse->successful()) {
            if (isset($parcelLabelResponseJson->status)) {
                return response()->json([
                    "errors" => [
                        ErrorService::write(
                            $user->email,
                            400,
                            $parcelLabelResponseJson->status . ' - ' . json_encode($parcelResponseJson->error),
                            $request,
                            "App\Http\Controllers\Api\V1\HR\OverseasController@createLabel::" . __LINE__,
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
                        $parcelLabelResponse->status() . ' - Overseas Server error',
                        $request,
                        "App\Http\Controllers\Api\V1\HR\OverseasController@createLabel::" . __LINE__,
                        json_encode($parcel)
                    )
                ]
            ], $parcelLabelResponse->status());
        }

        UserService::addUsage($user);

        return response()->json([
            "data" => [
                "parcels" => $pl_numbers,
                "label" => $parcelLabelResponse["labelsbase64"]
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
            $parcelResponse = Http::post(
                config('urls.hr.overseas') .
                    '/createshipment?' .
                    "apikey=$user->apiKey",
                [
                    "Cosignee" => [
                        "Name" => $parcel->parcel->name1,
                        "CountryCode" => "HR",
                        "Zipcode" => $parcel->parcel->pcode,
                        "City" => $parcel->parcel->city,
                        "StreetAndNumber" => $parcel->parcel->rPropNum,
                        "NotifyGSM" => $parcel->parcel->phone,
                        "NotifyEmail" => $parcel->parcel->email,
                    ],
                    "UnitAmount" => $parcel->parcel->num_of_parcel,
                    "Ref1" => $parcel->parcel->order_number,
                    "NumberOfCollies" => $parcel->parcel->num_of_parcel,
                    "CODValue" => !empty($parcel->parcel->cod_amount) ? $parcel->parcel->cod_amount : null,
                    "CODCurrency" => !empty($parcel->parcel->cod_amount) ? 0 : null,
                    "ExWorksType" => !empty($parcel->parcel->cod_amount) ? 4 : null,
                    "DeliveryRemark" => $parcel->parcel->sender_remark,
                ]
            );

            $parcelResponseJson = json_decode($parcelResponse->body());

            if ($parcelResponse->successful()) {
                if ($parcelResponseJson->status > 0) {
                    $error = ErrorService::write(
                        $user->email,
                        $parcelResponseJson->status,
                        substr($parcelResponseJson->status . ' - ' . json_encode($parcelResponseJson->error),0 ,250),
                        $request,
                        "App\Http\Controllers\Api\V1\HR\OverseasController@createLabels::" . __LINE__,
                        json_encode($parcel)
                    );

                    $errors[] = new MultiParcelError($parcel->order_number, $error['error_id'], $error['error_details']);
                }
            } else {
                $error = ErrorService::write(
                    $user->email,
                    $parcelResponse->status(),
                    $parcelResponse->status() . 'Overseas Server error',
                    $request,
                    "App\Http\Controllers\Api\V1\HR\OverseasController@createLabels::" . __LINE__,
                    json_encode($parcel)
                );

                $errors[] = new MultiParcelError($parcel->order_number, $error['error_id'], $error['error_details']);
            }

            $all_pl_numbers[] = $parcelResponseJson->shipmentid;
            $pl_numbers = $parcelResponseJson->shipmentid;

            $parcelLabelResponse = Http::post(
                config('urls.hr.overseas') .
                    '/commitshipments?' .
                    "apikey=$user->apiKey",
                [$pl_numbers]
            );

            $parcelLabelResponseJson = json_decode($parcelLabelResponse->body());

            if ($parcelLabelResponse->successful()) {
                if ($parcelLabelResponseJson->status > 0) {
                    $error = ErrorService::write(
                        $user->email,
                        $parcelLabelResponseJson->status,
                        substr($parcelResponseJson->status . ' - ' . json_encode($parcelResponseJson->error),0 ,250),
                        $request,
                        "App\Http\Controllers\Api\V1\HR\OverseasController@createLabels::" . __LINE__,
                        json_encode($parcel)
                    );

                    $errors[] = new MultiParcelError($parcel->order_number, $error['error_id'], $error['error_details']);
                }
            } else {
                $error = ErrorService::write(
                    $user->email,
                    $parcelLabelResponse->status(),
                    $parcelLabelResponse->status() . 'Overseas Server error',
                    $request,
                    "App\Http\Controllers\Api\V1\HR\OverseasController@createLabels::" . __LINE__,
                    json_encode($parcel)
                );

                $errors[] = new MultiParcelError($parcel->order_number, $error['error_id'], $error['error_details']);
            }

            UserService::addUsage($user);
            $data[] = new MultiParcelResponse($parcel->order_number, $pl_numbers, $parcelLabelResponse["labelsbase64"]);
        }

        return response()->json([
            "errors" => $errors
        ], 400);
        $allParcelLabelResponse = Http::post(
            config('urls.hr.overseas') .
                '/reprintlabels?' .
                "apikey=$user->apiKey",
            $all_pl_numbers
        );

        $allParcelLabelResponseJson = json_decode($allParcelLabelResponse->body());

        if ($allParcelLabelResponse->successful()) {
            if ($allParcelLabelResponseJson->status > 0) {
                return response()->json([
                    "errors" => [
                        $error = ErrorService::write(
                            $user->email,
                            $allParcelLabelResponseJson->status,
                            substr($allParcelLabelResponseJson->status . ' ' . json_encode($allParcelLabelResponseJson->error),0 ,250),
                            $request,
                            "App\Http\Controllers\Api\V1\HR\OverseasController@createLabels::" . __LINE__,
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
                        "App\Http\Controllers\Api\V1\HR\OverseasController@createLabels::" . __LINE__,
                        json_encode($parcel)
                    )
                ]
            ], $allParcelLabelResponse->status());
        }

        return response()->json([
            "data" => [
                "label" => $allParcelLabelResponse["labelsbase64"],
                "parcels" => $data
            ],
            "errors" => $errors
        ], 201);
    }
}
