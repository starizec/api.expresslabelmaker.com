<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;

use App\Models\User;
use App\Models\Domain;
use App\Models\Licence;

class LicenceController extends Controller
{
    public function activate(Request $request)
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

        $licences = Licence::where('user_id', $user->id)
            ->where('domain_id', $domain->id)
            ->get();

        if ($validatedData['licence'] === 'trial' && count($licences) === 0) {
            $licence = Licence::create([
                'user_id' => $user->id,
                'domain_id' => $domain->id,
                'licence_uid' => Uuid::uuid4()->toString(),
                'valid_from' => Carbon::today()->toDateString(),
                'valid_until' => null,
                'licence_type_id' => 1,
                'usage_limit' => config('usage.trial')
            ]);
        } else {
            if (Licence::where('user_id', $user->id)->where('domain_id', $domain->id)->where('licence_uid', $validatedData['licence'])->exists()) {
                $licence = Licence::where('user_id', $user->id)
                    ->where('domain_id', $domain->id)
                    ->where('licence_uid', $validatedData['licence'])
                    ->first();
            } else {
                return response()->json([
                    'message' => 'Problem kod autorizacije'
                ], 401);
            }
        }

        return response()->json([
            'licence' => $licence->licence_uid,
            'domain' => $domain->name,
            'user' => $user->email,
            'valid_until' => $licence->valid_until,
            'usage' => $licence->usage,
        ], 201);
    }

    public function check(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email',
            'domain' => 'required',
            'licence' => 'required'
        ]);

        return $request->get('test');

        if (!User::where('email', $validatedData['email'])->exists() ||
            !Domain::where('name', $validatedData['domain'])->exists() ||
            !Licence::where('licence_uid', $validatedData['licence'])->exists()) {
            return response()->json([
                'message' => 'Problem kod autorizacije'
            ], 401);
        }

        $user = User::where('email', $validatedData['email'])->first();

        $domain = Domain::where('name', $validatedData['domain'])->first();

        $licence = Licence::where('user_id', $user->id)
            ->where('domain_id', $domain->id)
            ->where('licence_uid', $validatedData['licence'])
            ->first();

        return response()->json([
            'licence' => $licence->licence_uid,
            'domain' => $domain->name,
            'user' => $user->email,
            'valid_until' => $licence->valid_until,
            'usage' => $licence->usage,
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
