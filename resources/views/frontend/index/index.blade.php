@extends('layouts.app')

@section('title', 'ExpressLabelMaker - Automatizirana izrada adresnica za WooCommerce trgovine')

@section('content')
    <header class="text-white py-5" style="background: linear-gradient(to right, #045cb8, #047adb)">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="display-4">ExpressLabelMaker</h1>
                    <p class="lead mb-4">Automatizirana izrada adresnica za WooCommerce trgovine</p>
                    <div class="mb-4">
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="bi bi-check2 text-white me-2"></i>Plug & play rješenje – spremno za
                                korištenje odmah</li>
                            <li class="mb-2"><i class="bi bi-check2 text-white me-2"></i>Sigurna i stabilna integracija
                            </li>
                            <li class="mb-2"><i class="bi bi-check2 text-white me-2"></i>Povezivanje s kuririma u par
                                klikova</li>
                            <li class="mb-2"><i class="bi bi-check2 text-white me-2"></i>Tehnička podrška i redovita
                                ažuriranja</li>
                            <li class="mb-2"><i class="bi bi-check2 text-white me-2"></i>Više vremena za prodaju, manje za
                                administraciju</li>
                        </ul>
                    </div>
                    <a href="#download" class="btn btn-light btn-lg me-3">Preuzmi plugin</a>
                </div>
                <div class="col-md-6 text-center">
                    <img src="{{ asset('assets/vectors/undraw_delivery-address_409g_1.svg') }}"
                        alt="ExpressLabelMaker Preview" class="img-fluid">
                </div>
            </div>
        </div>
    </header>

    <section class="py-5">
        <div class="container">
            <h2 class="mb-4 fw-bold">Mogućnosti Express Label Makera</h2>
            <p class="mb-5 text-muted">Iskoristite sve prednosti za bržu i efikasniju obradu narudžbi u WooCommerce
                trgovini.</p>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="text-center px-3">
                        <div class="mb-3">
                            <img src="{{ asset('assets/vectors/undraw_create_8val.svg') }}" alt="ExpressLabelMaker Preview"
                                class="img-fluid card-icon" style="height: 120px; width: auto; object-fit: contain;">
                        </div>
                        <h5 class="fw-bold">Izrada pojedinačne adresnice</h5>
                        <p class="text-muted">Brza izrada adresnice direktno iz detalja pojedine narudžbe.</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="text-center px-3">
                        <div class="mb-3">
                            <img src="{{ asset('assets/vectors/undraw_correct-answer_vjt7.svg') }}"
                                alt="ExpressLabelMaker Preview" class="img-fluid card-icon"
                                style="height: 120px; width: auto; object-fit: contain;">
                        </div>
                        <h5 class="fw-bold">Izrada više adresnica</h5>
                        <p class="text-muted">Odaberite više narudžbi i generirajte sve adresnice odjednom (multiselect).
                        </p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="text-center px-3">
                        <div class="mb-3">
                            <img src="{{ asset('assets/vectors/undraw_location-tracking_q3yd.svg') }}"
                                alt="ExpressLabelMaker Preview" class="img-fluid card-icon"
                                style="height: 120px; width: auto; object-fit: contain;">
                        </div>
                        <h5 class="fw-bold">Prikaz statusa paketa</h5>
                        <p class="text-muted">Pregled statusa dostave izravno na stranici svake narudžbe.</p>
                    </div>
                </div>

                <div class="col-md-4 mt-4">
                    <div class="text-center px-3">
                        <div class="mb-3">
                            <img src="{{ asset('assets/vectors/undraw_drone-delivery_ri74.svg') }}"
                                alt="ExpressLabelMaker Preview" class="img-fluid card-icon"
                                style="height: 120px; width: auto; object-fit: contain;">
                        </div>
                        <h5 class="fw-bold"> Dostava na paketomate</h5>
                        <p class="text-muted">Kreiranje adresnica za dostavu na paketomate.</p>
                    </div>
                </div>

                <div class="col-md-4  mt-4">
                    <div class="text-center px-3">
                        <div class="mb-3">
                            <img src="{{ asset('assets/vectors/undraw_web-shopping_m3o2.svg') }}"
                                alt="ExpressLabelMaker Preview" class="img-fluid card-icon"
                                style="height: 120px; width: auto; object-fit: contain;">
                        </div>
                        <h5 class="fw-bold">Izrada adresnice za prikup</h5>
                        <p class="text-muted">Kreirajte naljepnicu za povratnu pošiljku ili prikup proizvoda.</p>
                    </div>
                </div>

                <div class="col-md-4  mt-4">
                    <div class="text-center px-3">
                        <div class="mb-3">
                            <img src="{{ asset('assets/vectors/undraw_delivery-truck_mjui.svg') }}"
                                alt="ExpressLabelMaker Preview" class="img-fluid card-icon"
                                style="height: 120px; width: auto; object-fit: contain;">
                        </div>
                        <h5 class="fw-bold">Više kurirskih službi na jednom računu</h5>
                        <p class="text-muted">Dodajte sve kurire i izbjegnite plaćanje za svakog pojedinačno.</p>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <section class="py-5 bg-light">
        <div class="container text-center">
            <h2 class="fw-bold mb-4">Podržane kurirske službe</h2>
            <p class="mb-5 text-muted">Express Label Maker trenutno podržava sljedeće kurirske službe za generiranje
                adresnica i praćenje paketa:</p>

            <div class="row justify-content-center g-4">
                <div class="col-6 col-md-3">
                    <img src="{{ asset('assets/logos/dpd-logo.png') }}" alt="DPD logo" class="img-fluid mb-2"
                        style="max-height: 60px;">
                    <p class="fw-semibold">DPD</p>
                </div>
                <div class="col-6 col-md-3">
                    <img src="{{ asset('assets/logos/overseas-logo.png') }}" alt="Overseas Express logo"
                        class="img-fluid mb-2" style="max-height: 60px;">
                    <p class="fw-semibold">Overseas Express</p>
                </div>
                <div class="col-6 col-md-3">
                    <img src="{{ asset('assets/logos/GLS-logo.svg') }}" alt="GLS logo" class="img-fluid mb-2"
                        style="max-height: 60px;">
                    <p class="fw-semibold">GLS</p>
                </div>
                <div class="col-6 col-md-3">
                    <img src="{{ asset('assets/logos/posta-logo.png') }}" alt="Hrvatska pošta logo"
                        class="img-fluid mb-2" style="max-height: 60px;">
                    <p class="fw-semibold">Hrvatska pošta</p>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 bg-primary text-white text-center" style="background: linear-gradient(to right, #045cb8, #047adb)" id="download">
        <div class="container">
            <h2 class="mb-4">Besplatno isprobajte</h2>
            <p class="lead">Svi korisnici dobivaju <strong>10 besplatnih izrada adresnica</strong>.</p>
            <div class="d-grid gap-3 d-sm-flex justify-content-sm-center">
                <a href="/download" class="btn btn-light btn-lg px-4">Preuzmi plugin</a>
            </div>
        </div>
    </section>

    <footer class="py-4 bg-dark text-white">
        <div class="container text-center">
            <p class="mb-2">Imate pitanja? <a href="/kontakt"
                    class="text-white text-decoration-underline">Kontaktirajte nas</a></p>
            <p class="mb-0"><a href="/dokumentacija" class="text-white text-decoration-underline">Pogledajte
                    dokumentaciju</a></p>
        </div>
    </footer>
@endsection
