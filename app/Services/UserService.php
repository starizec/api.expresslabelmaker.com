<?php

namespace App\Services;

use App\Models\Licence;
use App\Models\User;
use App\Models\Domain;
use App\Classes\UserClass;

use GuzzleHttp\Client;

class UserService
{
    static public function addUsage($user)
    {
        $licence = Licence::where('licence_uid', $user->licence)->latest()->first();

        if ($licence) {
            $licence->increment('usage');
            $licence->save();

            return response()->json(['data' => $licence], 201);
        } else {
            return response()->json(['error' => 'User not found.'], 404);
        }
    }

    static public function resetUSage(UserClass $user)
    {
        $licence = Licence::where('licence_uid', $user->licence)->first();

        if ($licence) {
            $licence->usage = 0;
            $licence->save();

            return response()->json(['data' => $licence], 201);
        } else {
            return response()->json(['error' => 'User not found.'], 404);
        }
    }

    static public function checkUserLicence($user, $pl_no)
    {

        //Postoji li licenca
        if (!Licence::where('licence_uid', $user->licence)->exists()) {
            return [
                "status" => 403,
                "message" => "Licence does not exist."
            ];
        }

        $licence = Licence::where('licence_uid', $user->licence)->first();

        //Postoji li user iz licence
        $user_l = User::find($licence->user_id);
        if ($user->email != $user_l->email) {
            return [
                "status" => 403,
                "message" => "Wrong licence email."
            ];
        }
        
        //Postoji li domena iz licence
        $domain_l = Domain::find($licence->domain_id);
        if ($user->domain != $domain_l->name) {
            return [
                "status" => 403,
                "message" => "Wrong licence domain."
            ];
        }

        
        if ($licence->licence_type_id === config('licence-types.admin')) { //Ako je admin može sve
            return [
                "status" => 204,
                "message" => "Licence OK"
            ];
        } elseif ($licence->licence_type_id === config('licence-types.trial')) { //Trial verzija
            return [
                "licence-types.trial",
                "domain_l" => $domain_l,
                "licence" => $licence,
                "user_l" => $user_l
            ];
            if (($licence->usage + $pl_no) > $licence->usage_limit) { // Ako je presao limit
                return [
                    "status" => 403,
                    "message" => "Monthly usage reached. " . $licence->usage_limit - $licence->usage . " remain while trying " . $pl_no
                ];
            } else {
                return [
                    "status" => 204,
                    "message" => "Licence OK."
                ];
            }
        } elseif ($licence->licence_type_id === config('licence-types.full')) { //Full verzija
            if (($licence->usage + $pl_no) > $licence->usage_limit) { // Ako je presao limit
                return [
                    "status" => 403,
                    "message" => "Monthly usage reached. " . $licence->usage_limit - $licence->usage . " remain while trying " . $pl_no
                ];
            } else {
                $today = time();
                if ($today > strtotime($licence->valid_until)) { // Ako je licenca istekla
                    return [
                        "status" => 403,
                        "message" => "Licence expired."
                    ];
                } else {
                    return [
                        "status" => 204,
                        "message" => "Licence OK."
                    ];
                }
            }
        }
    }
}
