<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

use App\Services\DomainService;
use App\Services\Logger\ApiErrorLogger;
use App\Services\ErrorService;
use App\Services\UserService;


use App\Models\User;
use App\Models\Domain;
use App\Models\Licence;

class LicenceController extends Controller
{
    public function startTrial(Request $request)
    {
        $requestBody = $request->getContent();
        $jsonData = json_decode($requestBody);

        $data = $jsonData->user;

        //Ako domena postoji mora se upisati licenca.
        if (Domain::where('name', DomainService::parseDomain($data->domain))->exists()) {
            ApiErrorLogger::apiError(
                $data->domain . ' - Domain already registered. Enter your licence code.',
                $request,
                'Domain already registered. Enter your licence code.',
                __CLASS__ . '@' . __FUNCTION__ . '::' . __LINE__
            );

            return response()->json([
                'errors' =>
                    [
                        'error_message' => 'Domain already registered. Enter your licence code.'
                    ]
            ], 403);
        }

        //Plugin mora poslati "trial" da se aktivira trial licence
        if ($data->licence != 'trial') {
            ApiErrorLogger::apiError(
                $data->domain . ' - Invalid trial licence key.',
                $request,
                'Invalid trial licence key.',
                __CLASS__ . '@' . __FUNCTION__ . '::' . __LINE__
            );

            return response()->json([
                'errors' =>
                    [
                        'error_message' => 'Invalid trial licence key.'
                    ]
            ], 403);
        }

        if (User::where('email', $data->email)->exists()) {
            $user = User::where('email', $data->email)->first();
        } else {
            // Generate a random password for the new user
            $randomPassword = Str::random(16);
            
            $user = User::create([
                'email' => $data->email,
                'password' => Hash::make($randomPassword),
            ]);
            
            // Send password setup notification
            $user->sendPasswordSetupNotification();
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
