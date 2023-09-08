<?php

namespace App\Helpers;

final class MultiParcelResponse
{
    public $order_number;
    public $parcel_number;
    public $label;

    public function __construct($order_number, $parcel_number, $label) {
        $this->order_number = $order_number;
        $this->parcel_number = $parcel_number;
        $this->label = $label;

    }
}
