<?php

namespace App\Http\Controllers\Api\V1\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Classes\MultiParcelResponse;
use App\Classes\MultiParcelError;

class DpdController extends Controller
{
    /**
     * @OA\Post(
     *     path="/v1/hr/dpd/create/label",
     *     summary="Generira DPD naljepnicu za jednu narudžbu",
     *     description="Generira DPD naljepnicu za jednu narudžbu. Može vratiti 2 naljepnice ako je narudžba od 2 paketa. Parcel property prihvaća sve elemente iz DPD dokumentacije: [https://easyship.hr/documentation/hr/DPD_EasyShip_Web_Services.pdf#page=3](https://easyship.hr/documentation/hr/DPD_EasyShip_Web_Services.pdf#page=3)",
     *     tags={"HrDpd"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="user",
     *                 type="object",
     *                 description="User information",
     *                 @OA\Property(
     *                     property="username",
     *                     type="string",
     *                     description="Client’s Easyship username"
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     type="string",
     *                     description="Client’s Easyship password"
     *                 ),
     *                 @OA\Property(
     *                     property="domain",
     *                     type="string",
     *                     description="Client’s domain"
     *                 ),
     *                 @OA\Property(
     *                     property="licence",
     *                     type="string",
     *                     description="Client’s ELM.com licence"
     *                 ),
     *                 @OA\Property(
     *                     property="email",
     *                     type="string",
     *                     description="Client’s ELM.com E-mail address"
     *                 ),
     *                 @OA\Property(
     *                     property="platform",
     *                     type="string",
     *                     description="WebStore platform"
     *                 ),
     *             ),
     *             @OA\Property(
     *                 property="parcel",
     *                 type="object",
     *                 description="Parcel information",
     *             
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Parcel label created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="parcels",
     *                     type="string",
     *                     description="Comma-separated parcel numbers"
     *                 ),
     *                 @OA\Property(
     *                     property="label",
     *                     type="string",
     *                     format="base64",
     *                     description="Base64-encoded parcel label"
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request - Invalid input or DPD Server error",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="errors",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(
     *                         property="error_id",
     *                         type="integer",
     *                         description="Error ID"
     *                     ),
     *                     @OA\Property(
     *                         property="error_details",
     *                         type="string",
     *                         description="Error details"
     *                     ),
     *                 ),
     *             ),
     *         ),
     *     ),
     * )
     */
    public function createLabel(Request $request)
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
                "label" => base64_encode($parcelLabelResponse->body()) // Encode as base64 for JSON
            ]
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/v1/hr/dpd/create/labels",
     *     summary="Generira DPD naljepnice za više narudžbi",
     *     description="Generira DPD naljepnice za više narudžbi. Parcel property prihvaća sve elemente iz DPD dokumentacije: [https://easyship.hr/documentation/hr/DPD_EasyShip_Web_Services.pdf#page=3](https://easyship.hr/documentation/hr/DPD_EasyShip_Web_Services.pdf#page=3) ",
     *     tags={"HrDpd"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="user",
     *                 type="object",
     *                 description="User information",
     *                 @OA\Property(
     *                     property="username",
     *                     type="string",
     *                     description="Client’s Easyship username (Max 20 characters)"
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     type="string",
     *                     description="Client’s Easyship password (Max 20 characters)"
     *                 ),
     *                 @OA\Property(
     *                     property="domain",
     *                     type="string",
     *                     description="Client’s domain (Max 20 characters)"
     *                 ),
     *                 @OA\Property(
     *                     property="licence",
     *                     type="string",
     *                     description="Client’s licence (Max 20 characters)"
     *                 ),
     *                 @OA\Property(
     *                     property="email",
     *                     type="string",
     *                     description="Client’s E-mail address (Max 50 characters)"
     *                 ),
     *                 @OA\Property(
     *                     property="platform",
     *                     type="string",
     *                     description="Client’s platform (Max 20 characters)"
     *                 ),
     *             ),
     *             @OA\Property(
     *                 property="parcels",
     *                 type="array",
     *                 description="Array of parcels",
     *                 @OA\Items(
     *                     type="object",
     *             @OA\Property(
     *                 property="order_number",
     *                 type="string",
     *                 description="Parcel information",
     *                      ),
     *                     @OA\Property(
     *                         property="parcel",
     *                         type="object",
     *                         description="Parcel information",
     *                     
     *                     ),
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Parcel labels created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="label",
     *                     type="string",
     *                     format="base64",
     *                     description="Base64-encoded parcel labels"
     *                 ),
     *                 @OA\Property(
     *                     property="parcels",
     *                     type="array",
     *                     description="Array of parcel responses",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(
     *                             property="order_number",
     *                             type="string",
     *                             description="Customer’s parcel reference (Max 20 characters)"
     *                         ),
     *                         @OA\Property(
     *                             property="parcel_number",
     *                             type="string",
     *                             description="Comma-separated parcel numbers"
     *                         ),
     *                         @OA\Property(
     *                             property="label",
     *                             type="string",
     *                             format="base64",
     *                             description="Base64-encoded parcel label"
     *                         ),
     *                     ),
     *                 ),
     *             ),
     *            @OA\Property(
     *                 property="errors",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(
     *                         property="error_id",
     *                         type="integer",
     *                         description="Error ID"
     *                     ),
     *                     @OA\Property(
     *                         property="error_details",
     *                         type="string",
     *                         description="Error details"
     *                     ),
     *                 ),
     *             ),
     *         ),
     *     ),
     *     ),
     * )
     */
    public function createLabels(Request $request)
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
            "data" => [
                "label" => base64_encode($allParcelLabelResponse->body()),
                "parcels" => $data
            ],
            "errors" => $errors
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/v1/hr/dpd/create/collection-request",
     *     summary="Creates a DPD collection request",
     *     description="Creates a DPD collection request for a parcel. Parcel property prihvaća sve elemente iz DPD dokumentacije: [https://easyship.hr/documentation/hr/DPD_EasyShip_Web_Services.pdf#page=15](https://easyship.hr/documentation/hr/DPD_EasyShip_Web_Services.pdf#page=15)",
     *     tags={"HrDpd"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="user",
     *                 type="object",
     *                 description="User information",
     *                 @OA\Property(
     *                     property="username",
     *                     type="string",
     *                     description="Client’s Easyship username (Max 20 characters)"
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     type="string",
     *                     description="Client’s Easyship password (Max 20 characters)"
     *                 ),
     *                 @OA\Property(
     *                     property="domain",
     *                     type="string",
     *                     description="Client’s domain (Max 20 characters)"
     *                 ),
     *                 @OA\Property(
     *                     property="licence",
     *                     type="string",
     *                     description="Client’s licence (Max 20 characters)"
     *                 ),
     *                 @OA\Property(
     *                     property="email",
     *                     type="string",
     *                     description="Client’s E-mail address (Max 50 characters)"
     *                 ),
     *                 @OA\Property(
     *                     property="platform",
     *                     type="string",
     *                     description="Client’s platform (Max 20 characters)"
     *                 ),
     *             ),
     *             @OA\Property(
     *                 property="parcel",
     *                 type="object",
     *                 description="Parcel information",
     *                 @OA\Property(
     *                     property="...",
     *                     type="string",
     *                     description="Additional properties within the 'parcel' object"
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Success response",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="reference",
     *                     type="string",
     *                     description="Reference number for the collection request"
     *                 ),
     *                 @OA\Property(
     *                     property="code",
     *                     type="string",
     *                     description="Code for the collection request"
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request or error response",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="errors",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(
     *                         property="error_id",
     *                         type="integer",
     *                         description="Error identifier"
     *                     ),
     *                     @OA\Property(
     *                         property="error_details",
     *                         type="string",
     *                         description="Error details"
     *                     ),
     *                 ),
     *             ),
     *         ),
     *     ),
     * )
     */

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
}
