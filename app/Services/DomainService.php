<?php

namespace App\Services;

use App\Models\Domain;
use App\Models\Licence;

class DomainService
{
    static public function isDomain($domain) {
        return true;
    } 

    static public function parseDomain($domain) {
        return $domain;
    }

    static public function getLicence($domain) {
        $domain = Domain::where('name', self::parseDomain($domain))->latest()->first();
        
        if (!$domain) {
            return null;
        }

        $licence = Licence::where('domain_id', $domain->id)->latest()->first();
        
        return $licence->licence_uid;
    }
}