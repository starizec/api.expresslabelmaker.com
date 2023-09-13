<?php

namespace App\Classes;

final class UserClass
{
    public $email;
    public $domain;
    public $licence;

    public function __construct($email, $domain, $licence)
    {
        $this->email = $email;
        $this->domain = $domain;
        $this->licence = $licence;
    }
}
