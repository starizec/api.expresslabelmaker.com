@extends('layouts.app')

@section('title', 'ExpressLabelMaker - Automatizirana izrada adresnica za WooCommerce trgovine')

@section('content')
    <header class="text-white py-5" style="background: linear-gradient(to right, #045cb8, #047adb)">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="display-4" style="font-size: 28px; font-weight: 600;">ExpressLabelMaker.com</h1>
                    <p class="lead mb-4">Automatizirana izrada adresnica za WooCommerce trgovine</p>
                    <div class="mb-4">
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="bi bi-check2 text-white me-2"></i> Plug & play rješenje – spremno
                                za
                                korištenje odmah</li>
                            <li class="mb-2"><i class="bi bi-check2 text-white me-2"></i> Sigurna i stabilna integracija
                            </li>
                            <li class="mb-2"><i class="bi bi-check2 text-white me-2"></i> Povezivanje s kuririma u par
                                klikova</li>
                            <li class="mb-2"><i class="bi bi-check2 text-white me-2"></i> Tehnička podrška i redovita
                                ažuriranja</li>
                            <li class="mb-2"><i class="bi bi-check2 text-white me-2"></i> Više vremena za prodaju, manje
                                za
                                administraciju</li>
                        </ul>
                    </div>
                    <a href="{{ url(app()->getLocale() . '/download') }}" class="btn btn-light btn-lg me-3">Preuzmi
                        plugin</a>
                </div>
                <div class="col-md-6 text-center d-none d-md-block">
                    <img src="{{ asset('assets/vectors/undraw_delivery-address_409g_1.svg') }}"
                        alt="ExpressLabelMaker Preview" class="img-fluid">
                </div>
            </div>
        </div>
    </header>

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
                    <img src="{{ asset('assets/logos/posta-logo.png') }}" alt="Hrvatska pošta logo" class="img-fluid mb-2"
                        style="max-height: 60px;">
                    <p class="fw-semibold">Hrvatska pošta</p>
                </div>
            </div>
        </div>
    </section>

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
        <div class="container">
            <h2 class="fw-bold text-center mb-4">Zašto odabrati ExpressLabelMaker?</h2>
            <p class="text-center text-muted mb-5">Pogledajte usporedbu ključnih značajki između nas i ostalih rješenja.
            </p>

            <div class="table-responsive rounded overflow-hidden">
                <table class="table table-bordered text-center align-middle mb-0">
                    <thead class="table-primary" style="background-color: #045cb8;">
                        <tr>
                            <th class="text-start" style="color: #fff;">Naziv značajke</th>
                            <th style="color: #fff;">ExpressLabelMaker</th>
                            <th style="color: #fff;">Ostali</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-start">Izrada pojedinačne adresnice</td>
                            <td>✅</td>
                            <td>✅</td>
                        </tr>
                        <tr>
                            <td class="text-start">Izrada više adresnica odjednom</td>
                            <td>✅</td>
                            <td>✅</td>
                        </tr>
                        <tr>
                            <td class="text-start">Dostava na paketomate</td>
                            <td>✅</td>
                            <td>✅</td>
                        </tr>
                        <tr>
                            <td class="text-start">Pregled statusa dostave</td>
                            <td>✅</td>
                            <td>❌</td>
                        </tr>
                        <tr>
                            <td class="text-start">Filtiranje narudžbi po statusu dostave</td>
                            <td>✅</td>
                            <td>❌</td>
                        </tr>
                        <tr>
                            <td class="text-start">Jedna licenca za sve kurirske službe</td>
                            <td>✅</td>
                            <td>❌</td>
                        </tr>
                        <tr>
                            <td class="text-start">Izrada prikupa iz narudžbe</td>
                            <td>✅</td>
                            <td>❌</td>
                        </tr>
                        <tr>
                            <td class="text-start">Intuitivni prikaz greški</td>
                            <td>✅</td>
                            <td>❌</td>
                        </tr>
                        <tr>
                            <td class="text-start">Upravljanje spremljenim adresnicama</td>
                            <td>✅</td>
                            <td>❌</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>


    <section class="py-5">
        <div class="container">
            <h2 class="fw-bold text-center mb-4">Koliko vremena možete uštedjeti?</h2>
            <p class="text-center text-muted mb-5">Ručno izrađivanje adresnica troši vaše dragocjeno vrijeme. Pomoću našeg
                plugina možete ga bolje iskoristiti.</p>

            <div class="row justify-content-center mb-4">
                <div class="col-md-8">
                    <div class="card p-4 shadow-sm border-0">
                        <label for="addressCount" class="form-label fw-semibold">Broj adresnica <strong
                                id="lablesCreated">50</strong></label>
                        <input type="range" class="form-range" min="1" max="1000" step="1"
                            id="addressCount" value="50" oninput="updateTimeSaved()">
                        <div class="d-flex justify-content-between mb-3">
                            <span>1</span>
                            <span>1000</span>
                        </div>

                        <p class="text-center fs-5">Ušteda s ExpressLabelMaker.com: <strong id="timeSaved">250 minuta (4
                                sata i 10 minuta)</strong></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        function updateTimeSaved() {
            const slider = document.getElementById('addressCount');
            const count = parseInt(slider.value);
            const totalMinutes = count * 5;
            const hours = Math.floor(totalMinutes / 60);
            const minutes = totalMinutes % 60;

            document.getElementById('lablesCreated').textContent = `${count}`;
            document.getElementById('timeSaved').textContent =
                `${totalMinutes} minuta (${hours} ${hours === 1 ? 'sat' : 'sata'} i ${minutes} ${minutes === 1 ? 'minuta' : 'minuta'})`;
        }

        document.addEventListener('DOMContentLoaded', updateTimeSaved);
    </script>

    <section class="py-5 bg-light" id="kontakt">
        <div class="container">
            <h2 class="fw-bold text-center mb-4">Kontaktirajte nas</h2>
            <p class="text-center text-muted mb-5">Imate pitanja? Javite nam se i odgovorit ćemo vam u najkraćem mogućem
                roku.</p>

            <div class="row justify-content-center">
                <div class="col-md-8">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4">
                            <form action="{{ route('contact.submit') }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email adresa</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                        id="email" name="email" required placeholder="Email">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="message" class="form-label">Poruka</label>
                                    <textarea class="form-control @error('message') is-invalid @enderror" id="message" name="contactMessage"
                                        rows="4" required placeholder="Upit..."></textarea>
                                    @error('message')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <div class="row">
                                        <div class="col-md-6 mb-3 mb-md-0">
                                            {!! NoCaptcha::renderJs() !!}
                                            {!! NoCaptcha::display() !!}
                                            @error('g-recaptcha-response')
                                                <div class="text-danger mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <button type="submit" class="btn btn-primary btn-lg w-100">Pošalji poruku</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 bg-primary text-white text-center"
        style="background: linear-gradient(to right, #045cb8, #047adb)" id="download">
        <div class="container">
            <h2 class="mb-4">Besplatno isprobajte</h2>
            <p class="lead">Svi korisnici dobivaju <strong>10 besplatnih izrada adresnica</strong>.</p>
            <div class="d-grid gap-3 d-sm-flex justify-content-sm-center">
                <a href="{{ url(app()->getLocale() . '/download') }}" class="btn btn-light btn-lg px-4">Preuzmi
                    plugin</a>
            </div>
        </div>
    </section>


@endsection
