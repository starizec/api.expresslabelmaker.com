<?php

namespace App\Services;

use App\Models\Licence;
use App\Models\User;
use App\Models\Domain;

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

    static public function checkUserLicence($user, $pl_no)
    {

        //Postoji li licenca
        if (!Licence::where('licence_uid', $user->licence)->exists()) {
            return [
                "status" => 403,
                "message" => "Licence does not exist.",
                "error_code" => "801"
            ];
        }

        $licence = Licence::where('licence_uid', $user->licence)->first();

        //Postoji li user iz licence
        $user_l = User::find($licence->user_id);
        if ($user->email != $user_l->email) {
            return [
                "status" => 403,
                "message" => "Wrong licence email.",
                "error_code" => "802"
            ];
        }
        
        //Postoji li domena iz licence
        $domain_l = Domain::find($licence->domain_id);
        if ($user->domain != $domain_l->name) {
            return [
                "status" => 403,
                "message" => "Wrong licence domain.",
                "error_code" => "805"
            ];
        }

        if ($licence->licence_type_id == config('licence-types.admin')) { //Ako je admin može sve
            return [
                "status" => 204,
                "message" => "Licence OK",
                "error_code" => null
            ];
        } elseif ($licence->licence_type_id == config('licence-types.trial')) { //Trial verzija
            if (($licence->usage + $pl_no) > $licence->usage_limit) { // Ako je presao limit
                return [
                    "status" => 403,
                    "message" => "Usage limit reached. " . $licence->usage_limit - $licence->usage . " remain while trying " . $pl_no,
                    "error_code" => "807"
                ];
            } else {
                return [
                    "status" => 204,
                    "message" => "Licence OK.",
                    "error_code" => null
                ];
            }
        } elseif ($licence->licence_type_id == config('licence-types.full')) { //Full verzija
            if (($licence->usage + $pl_no) > $licence->usage_limit) { // Ako je presao limit
                return [
                    "status" => 403,
                    "message" => "Usage limit reached. " . $licence->usage_limit - $licence->usage . " remain while trying " . $pl_no,
                    "error_code" => "807"
                ];
            } else {
                $today = time();
                if ($today > strtotime($licence->valid_until)) { // Ako je licenca istekla
                    return [
                        "status" => 403,
                        "message" => "Licence expired.",
                        "error_code" => "808"
                    ];
                } else {
                    return [
                        "status" => 204,
                        "message" => "Licence OK.",
                        "error_code" => null
                    ];
                }
            }
        }
    }
}
