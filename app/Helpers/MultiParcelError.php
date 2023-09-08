<?php

namespace App\Helpers;

final class MultiParcelError
{
    public $order_number;
    public $error_id;
    public $error_details;

    public function __construct($order_number, $error_id, $error_details) {
        $this->order_number = $order_number;
        $this->error_id = $error_id;
        $this->error_details = $error_details;

    }
}
