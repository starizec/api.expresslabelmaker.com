<?php

namespace App\Classes;

final class MultiStatusResponse
{
    public $order_number;
    public $parcel_number;
    public $parcel_status;

    public function __construct($order_number, $parcel_number, $parcel_status) {
        $this->order_number = $order_number;
        $this->parcel_number = $parcel_number;
        $this->parcel_status = $parcel_status;
    }
}
