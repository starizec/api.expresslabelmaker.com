<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;

use App\Services\DomainService;

use App\Models\User;
use App\Models\Domain;
use App\Models\Licence;
use App\Services\ErrorService;
use App\Services\UserService;

class LicenceController extends Controller
{
    /**
     * @OA\Post(
     *     path="/v1/licence/start-trial",
     *     description="Mora se poslati trial za licenca property ili vraća error.",
     *     summary="Kreira probno razdoblje s neograničenim vremenom i XX brojem izrada naljepnica.",
     *     tags={"Licence"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="email", type="string", example="user@example.com"),
     *                 @OA\Property(property="domain", type="string", example="example.com"),
     *                 @OA\Property(property="licence", type="string", example="trial")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Trial started successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="licence", type="string", example="unique-licence-uid"),
     *             @OA\Property(property="domain", type="string", example="example.com"),
     *             @OA\Property(property="email", type="string", example="user@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Invalid request or licence",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="error_id", type="integer", example=1),
     *                     @OA\Property(property="error_details", type="string", example="Error message")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function startTrial(Request $request)
    {
        $requestBody = $request->getContent();
        $jsonData = json_decode($requestBody);

        $data = $jsonData->user;

        //Ako domena postoji mora se upisati licenca.
        if (Domain::where('name', DomainService::parseDomain($data->domain))->exists()) {
            return response()->json([
                "errors" => [
                    ErrorService::write($data->email, 403, "Domain already registered. Enter your licence code.", $request, "App\Http\Controllers\Api\V1\LicenceController@startTrial" . __LINE__, ''),
                ],
            ], 403);
        }

        //Plugin mora poslati "trial" da se aktivira trial licence
        if ($data->licence != 'trial') {
            return response()->json([
                "errors" => [
                    ErrorService::write($data->email, 403, "Invalid trial licence key.", $request, "App\Http\Controllers\Api\V1\LicenceController@startTrial" . __LINE__, ''),
                ],
            ], 403);
        }

        if (User::where('email', $data->email)->exists()) {
            $user = User::where('email', $data->email)->first();
        } else {
            $user = User::firstOrCreate([
                'wp_user_id' => Uuid::uuid4(),
                'email' => $data->email,
            ]);
        }

        $domain = Domain::firstOrCreate([
            'name' => $data->domain,
            'user_id' => $user->id
        ]);

        $licence = Licence::create([
            'user_id' => $user->id,
            'domain_id' => $domain->id,
            'licence_uid' => Uuid::uuid4()->toString(),
            'valid_from' => Carbon::today()->toDateString(),
            'valid_until' => null,
            'licence_type_id' => config('licence-types.trial'),
            'usage_limit' => config('usage.trial')
        ]);

        return response()->json([
            'licence' => $licence->licence_uid,
            'domain' => $domain->name,
            'email' => $user->email
        ], 201);
    }

    /**
     * Check the validity of a user's licence.
     *
     * @OA\Post(
     *     path="/v1/licence/check",
     *     summary="Provjerava i vraća podatke licence",
     *     tags={"Licence"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="User data for licence check",
     *         @OA\JsonContent(
     *             required={"user"},
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="email", type="string"),
     *                 @OA\Property(property="domain", type="string"),
     *                 @OA\Property(property="licence", type="string"),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Licence and user information",
     *         @OA\JsonContent(
     *             @OA\Property(property="licence", type="string"),
     *             @OA\Property(property="domain", type="string"),
     *             @OA\Property(property="user", type="string"),
     *             @OA\Property(property="valid_from", type="string"),
     *             @OA\Property(property="valid_until", type="string"),
     *             @OA\Property(property="usage", type="integer"),
     *             @OA\Property(property="usage_limit", type="integer"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="User or licence does not exist",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="error_id", type="integer"),
     *                     @OA\Property(property="error_details", type="string"),
     *                 ),
     *             ),
     *         ),
     *     ),
     * )
     *
     * @param Request $request The HTTP request containing user data.
     * @return \Illuminate\Http\JsonResponse Response containing licence and user information.
     */
    public function check(Request $request)
    {
        $requestBody = $request->getContent();
        $jsonData = json_decode($requestBody);

        $data = $jsonData->user;

        if (
            !User::where('email', $data->email)->exists() ||
            !Domain::where('name', DomainService::parseDomain($data->domain))->exists() ||
            !Licence::where('licence_uid', $data->licence)->exists()
        ) {
            return response()->json([
                "errors" => [
                    [
                        ErrorService::write($data->email, 403, "User does not exist.", $request, "App\Http\Controllers\Api\V1\LicenceController@check" . __LINE__, '')
                    ]
                ],
            ], 403);
        }

        $user = User::where('email', $data->email)->first();
        $domain = Domain::where('name', DomainService::parseDomain($data->domain))->first();

        if (!Licence::where('user_id', $user->id)->where('domain_id', $domain->id)->where('licence_uid', $data->licence)->exists()) {
            return response()->json([
                "errors" => [
                    ErrorService::write($data->email, 403, "User does not exist.", $request, "App\Http\Controllers\Api\V1\LicenceController@check" . __LINE__, '')
                ],
            ], 403);
        }

        $licence = Licence::where('user_id', $user->id)
            ->where('domain_id', $domain->id)
            ->where('licence_uid', $data->licence)
            ->latest()
            ->first();

        if ($licence->user_id != $user->id || $licence->domain_id != $domain->id) {
            return response()->json([
                "errors" => [
                    ErrorService::write($data->email, 403, "User does not exist.", $request, "App\Http\Controllers\Api\V1\LicenceController@check" . __LINE__, '')
                ],
            ], 403);
        }

        return response()->json([
            'licence' => $licence->licence_uid,
            'domain' => $domain->name,
            'user' => $user->email,
            'valid_from' => $licence->valid_from,
            'valid_until' => $licence->valid_until,
            'usage' => $licence->usage,
            'usage_limit' => $licence->usage_limit,
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/v1/licence/buy",
     *     summary="Buy a licence",
     *     description="Kupnja licence. Korisnik mora imati nekakvu licencu. 'Licence' vrijednost mora biti 'Full'",
     *     operationId="buyLicence",
     *     tags={"Licence"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="JSON payload with user data",
     *         @OA\JsonContent(
     *             required={"user"},
     *             @OA\Property(property="user", type="object", description="User data", 
     *                 @OA\Property(property="licence", type="string", description="User's licence type ('full')"),
     *                 @OA\Property(property="email", type="string", description="User's email"),
     *                 @OA\Property(property="domain", type="string", description="User's domain")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Licence successfully purchased",
     *         @OA\JsonContent(
     *             @OA\Property(property="licence", type="string", description="Purchased licence UID"),
     *             @OA\Property(property="domain", type="string", description="User's domain"),
     *             @OA\Property(property="user", type="string", description="User's email"),
     *             @OA\Property(property="valid_from", type="string", description="Licence validity start date"),
     *             @OA\Property(property="valid_until", type="string", description="Licence validity end date"),
     *             @OA\Property(property="usage", type="integer", description="Current licence usage"),
     *             @OA\Property(property="usage_limit", type="integer", description="Licence usage limit")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Invalid request or user",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="array", description="List of errors",
     *                 @OA\Items(
     *                     @OA\Property(property="message", type="string", description="Error message"),
     *                     @OA\Property(property="code", type="integer", description="Error code"),
     *                     @OA\Property(property="details", type="string", description="Error details")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function buy(Request $request)
    {
        $requestBody = $request->getContent();
        $jsonData = json_decode($requestBody);

        $data = $jsonData->user;

        if ($data->licence != 'full') {
            return response()->json([
                "errors" => [
                    ErrorService::write($data->email, 403, "Invalid trial licence key.", $request, "App\Http\Controllers\Api\V1\LicenceController@buy" . __LINE__, ''),
                ],
            ], 403);
        }

        if (
            !User::where('email', $data->email)->exists() ||
            !Domain::where('name', DomainService::parseDomain($data->domain))->exists()
        ) {
            return response()->json([
                "errors" => [
                    [
                        ErrorService::write($data->email, 403, "User does not exist.", $request, "App\Http\Controllers\Api\V1\LicenceController@buy" . __LINE__, '')
                    ]
                ],
            ], 403);
        }

        $user = User::where('email', $data->email)->first();
        $domain = Domain::where('name', DomainService::parseDomain($data->domain))->first();

        if (!Licence::where('user_id', $user->id)->where('domain_id', $domain->id)->exists()) {
            return response()->json([
                "errors" => [
                    ErrorService::write($data->email, 403, "User does not exist.", $request, "App\Http\Controllers\Api\V1\LicenceController@buy" . __LINE__, '')
                ],
            ], 403);
        }

        $licence = Licence::where('user_id', $user->id)
            ->where('domain_id', $domain->id)
            ->first();

        //Full licenca
        if (!is_null($licence->valid_until) && !is_null($licence->valid_until) && $licence->licence_type_id === config('licence-types.full')) {
            $licence_start = $licence->valid_until;
            $licence_end = Carbon::parse($licence_start)->addYear()->toDateString();

            //Trial licenca
        } else if (is_null($licence->valid_until) && $licence->licence_type_id === config('licence-types.trial')) {
            $licence_start = Carbon::today()->toDateString();
            $licence_end = Carbon::today()->addYear()->toDateString();
        }

        $new_licence = Licence::create([
            'user_id' => $user->id,
            'domain_id' => $domain->id,
            'licence_uid' => $licence->licence_uid,
            'valid_from' => $licence_start,
            'valid_until' => $licence_end,
            'licence_type_id' => config('licence-types.full'),
            'usage_limit' => config('usage.full')
        ]);

        return response()->json([
            'licence' => $new_licence->licence_uid,
            'domain' => $domain->name,
            'user' => $user->email,
            'valid_from' => $new_licence->valid_from,
            'valid_until' => $new_licence->valid_until,
            'usage' => $new_licence->usage,
            'usage_limit' => $new_licence->usage_limit,
        ], 201);
    }
}
