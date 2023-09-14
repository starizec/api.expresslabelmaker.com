<?php

namespace App\Services;

use App\Models\Domain;

class DomainService
{
    static public function isDomain($domain) {
        return true;
    } 

    static public function parseDomain($domain) {
        return $domain;
    }
}