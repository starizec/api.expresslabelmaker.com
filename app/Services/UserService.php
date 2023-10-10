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

    static public function getWpUser($email)
    {
        $url = 'https://expresslabelmaker.com/wp-json/wc/v3/customers';

        $consumerKey = env('WP_CK', '');
        $consumerSecret = env('WP_CS', '');

        $client = new Client([
            'base_uri' => $url,
            'auth' => [$consumerKey, $consumerSecret],
        ]);

        $response = $client->get('', [
            'query' => ['email' => $email],
        ]);

        $data = json_decode($response->getBody(), true);

        if ($response->getStatusCode() == 200) {
            return [
                "status" => 200,
                "message" => "Success",
                'data' => $data
            ];
        } else {
            return [
                "status" => $response->getStatusCode(),
                "message" => 'Failed to get user data. Status code: ' . $response->getStatusCode(),
                "data" => []
            ];
        }
    }

    static public function createWpUser($email)
    {
        $url = 'https://expresslabelmaker.com/wp-json/wc/v3/customers';

        $consumerKey = env('WP_CK', '');
        $consumerSecret = env('WP_CS', '');

        $user_data = [
            'email' => $email
        ];

        $client = new Client([
            'base_uri' => $url,
            'auth' => [$consumerKey, $consumerSecret],
        ]);

        $response = $client->post('', [
            'json' => $user_data,
        ]);

        $data = json_decode($response->getBody(), true);

        if ($response->getStatusCode() == 201) {
            return [
                "status" => 201,
                "message" => "User created",
                'data' => $data
            ];
        } else {
            return [
                "status" => $response->getStatusCode(),
                "message" => 'Failed to create user. Status code: ' . $response->getStatusCode(),
                "data" => []
            ];
        }
    }
}
