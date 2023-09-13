<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Classes\MultiParcelError;
use App\Classes\MultiStatusResponse;

/**
 * @OA\Post(
 *     path="/v1/parcel-statuses",
 *     summary="Dohvaća statuse parcele po kuriru i državi.",
 *     description="Dohvaća statuse parcele po kuriru i državi.",
 *     tags={"Parcel Status"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *              @OA\Property(
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
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(
 *                         property="parcel",
 *                         type="object",
 *                         @OA\Property(
 *                             property="country",
 *                             type="string",
 *                             description="Parcel country"
 *                         ),
 *                         @OA\Property(
 *                             property="courier",
 *                             type="string",
 *                             description="Parcel courier"
 *                         ),
 *                         @OA\Property(
 *                             property="parcel_number",
 *                             type="string",
 *                             description="Parcel number"
 *                         ),
 *                     ),
 *                     @OA\Property(
 *                         property="order_number",
 *                         type="string",
 *                         description="Order number"
 *                     ),
 *                 ),
 *             ),
 *         ),
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(
 *                         property="order_number",
 *                         type="string",
 *                         description="Order number"
 *                     ),
 *                     @OA\Property(
 *                         property="parcel_number",
 *                         type="string",
 *                         description="Parcel number"
 *                     ),
 *                     @OA\Property(
 *                         property="parcel_status",
 *                         type="string",
 *                         description="Parcel status"
 *                     ),
 *                 ),
 *             ),
 *             @OA\Property(
 *                 property="errors",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(
 *                         property="order_number",
 *                         type="string",
 *                         description="Order number"
 *                     ),
 *                     @OA\Property(
 *                         property="error_code",
 *                         type="integer",
 *                         description="Error code"
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
 *     @OA\Response(
 *         response=400,
 *         description="Invalid request format",
 *         @OA\JsonContent(
 *             type="object",
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

class StatusController extends Controller
{
    public function get(Request $request)
    {
        $requestBody = $request->getContent();
        $jsonData = json_decode($requestBody);

        $user = $jsonData->user;
        $parcels = $jsonData->parcels;

        $data = [];
        $errors = [];

        foreach ($parcels as $parcel) {
            $country_short = ucwords(strtolower($parcel->parcel->country));
            $courier_short = ucwords(strtolower($parcel->parcel->courier));
            $pl_no = $parcel->parcel->parcel_number;
            $order_no = $parcel->order_number;

            $className = 'App\\Services\\' . $country_short . '\\' . $courier_short . 'Service';
            $courier = new $className;

            $response = $courier->getParcelStatus($pl_no);

            if (isset($response['errors'])) {
                $errors[] = new MultiParcelError($order_no, 1, $response['errors'][0]['error_details']);
            } else {
                $data[] = new MultiStatusResponse($order_no, $pl_no, $response['parcel_status']);
            }
        }

        return response()->json([
            "data" => $data,
            "errors" => $errors
        ], 201);
    }
}
