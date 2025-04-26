<?php

namespace App\Services;

use App\Models\Domain;

class AdressService
{
    public function splitAddress($address)
    {
        $address = trim($address);

        if (preg_match('/^(.*?)[\s,]*([0-9]+)\s*([a-zA-Z]?)$/', $address, $matches)) {
            return [
                'street' => trim($matches[1]),
                'house_number' => $matches[2],
                'house_number_suffix' => $matches[3] !== '' ? $matches[3] : null,
            ];
        }

        return [
            'street' => $address,
            'house_number' => null,
            'house_number_suffix' => null,
        ];
    }
}