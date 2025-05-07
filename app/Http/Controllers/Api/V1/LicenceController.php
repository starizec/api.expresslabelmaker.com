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

use App\Notifications\LicenceRenewalNotification;
use App\Notifications\LicenceBoughtNotification;

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
                        [
                            'error_message' => 'Domain already registered. Enter your licence code.',
                            'error_code' => '600'
                        ]
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
                        [
                            'error_message' => 'Invalid trial licence key.',
                            'error_code' => '801'
                        ]
                    ]
            ], 403);
        }

        $isNewUser = false;

        if (User::where('email', $data->email)->exists()) {
            $user = User::where('email', $data->email)->first();
        } else {
            // Generate a random password for the new user
            $randomPassword = Str::random(16);

            $user = User::create([
                'email' => $data->email,
                'password' => Hash::make($randomPassword),
            ]);

            $isNewUser = true;

            // Send password setup notification
            $user->sendPasswordSetupNotification();
        }

        $domain = Domain::firstOrCreate([
            'name' => $data->domain,
            'user_id' => $user->id
        ]);

        $domain->sendNewDomainNotification($domain->name);

        $licence = Licence::create([
            'user_id' => $user->id,
            'domain_id' => $domain->id,
            'licence_uid' => Uuid::uuid4()->toString(),
            'valid_from' => Carbon::today()->toDateString(),
            'valid_until' => Carbon::today()->addYear()->toDateString(),
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
            ApiErrorLogger::apiError(
                $data->domain . ' - Invalid licence key.',
                $request,
                'Invalid licence key.',
                __CLASS__ . '@' . __FUNCTION__ . '::' . __LINE__
            );

            return response()->json([
                "errors" => [
                    [
                        'error_message' => 'Invalid licence key.',
                        'error_code' => '801'
                    ]
                ],
            ], 403);
        }

        $user = User::where('email', $data->email)->first();
        $domain = Domain::where('name', DomainService::parseDomain($data->domain))->first();

        if (!Licence::where('user_id', $user->id)->where('domain_id', $domain->id)->where('licence_uid', $data->licence)->exists()) {
            ApiErrorLogger::apiError(
                $user->email . ' - User does not exist.',
                $request,
                'User does not exist.',
                __CLASS__ . '@' . __FUNCTION__ . '::' . __LINE__
            );

            return response()->json([
                "errors" => [
                    [
                        'error_message' => 'User does not exist.',
                        'error_code' => '802'
                    ]
                ],
            ], 403);
        }

        $licence = Licence::where('user_id', $user->id)
            ->where('domain_id', $domain->id)
            ->where('licence_uid', $data->licence)
            ->whereDate('valid_until', '>=', now())
            ->latest()
            ->first();

        if ($licence->user_id != $user->id || $licence->domain_id != $domain->id) {
            ApiErrorLogger::apiError(
                $user->email . ' - User does not exist.',
                $request,
                'User does not exist.',
                __CLASS__ . '@' . __FUNCTION__ . '::' . __LINE__
            );

            return response()->json([
                "errors" => [
                    [
                        'error_message' => 'User does not exist.',
                        'error_code' => '802'
                    ]
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

    public function buy($licence_uid)
    {
        if (!Licence::where('licence_uid', $licence_uid)->exists()) {
            ApiErrorLogger::apiError(
                $licence_uid . ' - Invalid licence key.',
                $licence_uid,
                'Invalid licence key.',
                __CLASS__ . '@' . __FUNCTION__ . '::' . __LINE__
            );

            return response()->json([
                "errors" => [
                    [
                        'error_message' => 'Invalid licence key.',
                        'error_code' => '801'
                    ]
                ],
            ], 403);
        }

        $licence = Licence::where('licence_uid', $licence_uid)
            ->with(['domain', 'user'])
            ->latest()
            ->first();

        if ($licence->licence_type_id != config('licence-types.trial')) {
            ApiErrorLogger::apiError(
                $licence->domain->name . ' - ' . $licence->user->email . ' - Full licence already exists.',
                $licence_uid,
                'Full licence already exists.',
                __CLASS__ . '@' . __FUNCTION__ . '::' . __LINE__
            );

            return response()->json([
                "errors" => [
                    [
                        'error_message' => 'Full licence already exists.',
                        'error_code' => '803'
                    ]
                ],
            ], 403);
        }

        $update_licence = Licence::where('user_id', $licence->user->id)
            ->where('domain_id', $licence->domain->id)
            ->where('licence_uid', $licence_uid)
            ->latest()
            ->first();

        $update_licence->update([
            'licence_type_id' => config('licence-types.full'),
            'usage_limit' => config('usage.full')
        ]);

        $update_licence->user->notify(new LicenceBoughtNotification($update_licence));

        return response()->json([
            'licence' => $update_licence->licence_uid,
            'domain' => $update_licence->domain->name,
            'user' => $update_licence->user->email,
            'valid_from' => $update_licence->valid_from,
            'valid_until' => $update_licence->valid_until,
            'usage' => $update_licence->usage,
            'usage_limit' => $update_licence->usage_limit,
        ], 201);
    }

    public function renew($licence_uid)
    {
        if (!Licence::where('licence_uid', $licence_uid)->exists()) {
            ApiErrorLogger::apiError(
                $licence_uid . ' - Invalid licence key.',
                $licence_uid,
                'Invalid licence key.',
                __CLASS__ . '@' . __FUNCTION__ . '::' . __LINE__
            );

            return response()->json([
                "errors" => [
                    [
                        'error_message' => 'Invalid licence key.',
                        'error_code' => '801'
                    ]
                ],
            ], 403);
        }

        $licence = Licence::where('licence_uid', $licence_uid)
            ->with(['domain', 'user'])
            ->latest()
            ->first();

        $new_licence = Licence::create([
            'user_id' => $licence->user->id,
            'domain_id' => $licence->domain->id,
            'licence_uid' => $licence->licence_uid,
            'valid_from' => Carbon::parse($licence->valid_until)->addDay()->toDateString(),
            'valid_until' => Carbon::parse($licence->valid_until)->addYear()->addDay()->toDateString(),
            'usage_limit' => config('usage.full'),
            'licence_type_id' => config('licence-types.full')
        ]);

        $new_licence->user->notify(new LicenceRenewalNotification($new_licence));

        return response()->json([
            'licence' => $new_licence->licence_uid,
            'domain' => $new_licence->domain->name,
            'user' => $new_licence->user->email,
        ], 201);
    }
}