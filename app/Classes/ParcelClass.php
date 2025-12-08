<?php

namespace App\Classes;

class ParcelClass
{
    // Primatelj
    public string  $recipient_id;
    public string  $recipient_name;
    public string  $recipient_phone;
    public string  $recipient_email;
    public string  $recipient_adress;
    public string  $recipient_city;
    public string  $recipient_postal_code;
    public string  $recipient_country;

    // Paket
    public string  $order_number;
    public string  $parcel_value;
    public string  $parcel_weight;
    public ?string $parcel_remark = null;
    public ?string $cod_amount = null;
    public ?string $cod_currency = null;
    public string  $delivery_type;
    public ?string $location_type = null;
    public ?string $location_id = null;
    public int     $parcel_count = 1;
    public ?string $parcel_size = null;

    // Pošiljatelj
    public string  $sender_id;
    public string  $sender_name;
    public string  $sender_phone;
    public string  $sender_email;
    public string  $sender_adress;
    public string  $sender_city;
    public string  $sender_postal_code;
    public string  $sender_country;

    // Kurir
    public string  $delivery_sevice; // (ako je tipfeler, preimenuj u delivery_service u cijelom kodu)
    public ?string $delivery_additional_services = null; // <- ovo koristi u metodi
    public ?string $printer_type = null;
    public ?int    $print_position = null;

    // Opcionalne dimenzije
    public ?string $parcel_x = null;
    public ?string $parcel_y = null;
    public ?string $parcel_z = null;
    public ?string $parcel_ref_1 = null;
    public ?string $parcel_ref_2 = null;
    public ?string $parcel_ref_3 = null;

    // Verifikacija
    public ?string $username = null;
    public ?string $password = null;
    public ?string $client_number = null;
    public ?string $api_key = null;
    public string  $domain;
    public string  $licence;
    public string  $email;

    /** (neobavezno) helper za parse dodatnih usluga */
    public function additionalServicesArray(): array
    {
        if (!$this->delivery_additional_services) return [];
        return array_filter(array_map('trim', explode(',', $this->delivery_additional_services)));
    }
}
