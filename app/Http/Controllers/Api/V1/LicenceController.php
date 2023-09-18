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
                    ErrorService::write($data->email, 403, "Domain already registered. Enter your licence code.", $request, "App\Http\Controllers\Api\V1\LicenceController@startTrial", ''),
                ],
            ], 403);
        }

        //Plugin mora poslati "trial" da se aktivira trial licence
        if ($data->licence != 'trial') {
            return response()->json([
                "errors" => [
                    ErrorService::write($data->email, 403, "Invalid trial licence key.", $request, "App\Http\Controllers\Api\V1\LicenceController@startTrial", ''),
                ],
            ], 403);
        }

        $user = User::firstOrCreate([
            'email' => $data->email
        ]);

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
            'licence_type_id' => 1,
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
                        ErrorService::write($data->email, 403, "User does not exist.", $request, "App\Http\Controllers\Api\V1\LicenceController@check", '')
                    ]
                ],
            ], 403);
        }

        $user = User::where('email', $data->email)->first();
        $domain = Domain::where('name', DomainService::parseDomain($data->domain))->first();

        if (!Licence::where('user_id', $user->id)->where('domain_id', $domain->id)->where('licence_uid', $data->licence)->exists()) {
            return response()->json([
                "errors" => [
                    ErrorService::write($data->email, 403, "User does not exist.", $request, "App\Http\Controllers\Api\V1\LicenceController@check", '')
                ],
            ], 403);
        }

        $licence = Licence::where('user_id', $user->id)
            ->where('domain_id', $domain->id)
            ->where('licence_uid', $data->licence)
            ->first();

        if ($licence->user_id != $user->id || $licence->domain_id != $domain->id) {
            return response()->json([
                "errors" => [
                    ErrorService::write($data->email, 403, "User does not exist.", $request, "App\Http\Controllers\Api\V1\LicenceController@check", '')
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

    public function buy(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email',
            'domain' => 'required',
            'licence' => 'required'
        ]);

        $user = User::firstOrCreate([
            'email' => $validatedData['email']
        ]);

        $domain = Domain::firstOrCreate([
            'name' => $validatedData['domain'],
            'user_id' => $user->id
        ]);

        $licence = Licence::create([
            'user_id' => $user->id,
            'domain_id' => $domain->id,
            'licence_uid' => Uuid::uuid4()->toString(),
            'valid_from' => Carbon::today()->toDateString(),
            'valid_until' => null,
            'licence_type_id' => 1,
            'usage_limit' => config('usage.trial')
        ]);

        return response()->json([
            'licence' => $licence->licence_uid,
            'domain' => $domain->name,
            'user' => $user->email,
            'valid_until' => $licence->valid_until,
            'usage' => $licence->usage,
        ], 201);

        //slanje email-a
    }
}
