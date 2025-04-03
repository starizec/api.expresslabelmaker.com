@extends('layouts.app')

@section('title', 'ExpressLabelMaker - Automatizirana izrada adresnica za WooCommerce trgovine')

@section('content')
  <header class="text-white py-5" style="background: linear-gradient(to right, #045cb8, #047adb)">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-md-6">
          <h1 class="display-4">ExpressLabelMaker</h1>
          <p class="lead mb-4">Automatizirana izrada adresnica za WooCommerce trgovine</p>
          
          <a href="#download" class="btn btn-primary btn-lg">Preuzmi plugin</a>
        </div>
        <div class="col-md-6 text-center">
          <img src="{{ asset('assets/images/hero.png') }}" alt="ExpressLabelMaker Preview" class="img-fluid">
        </div>
      </div>
    </div>
  </header>

  <section class="py-5 bg-light">
    <div class="container">
      <h2 class="text-center mb-4">Kako funkcionira?</h2>
      <ol class="list-group list-group-flush list-group-numbered mx-auto" style="max-width: 600px;">
        <li class="list-group-item">Preuzmite plugin s ove stranice</li>
        <li class="list-group-item">Registrirajte se i kreirajte svoju licencu</li>
        <li class="list-group-item">Unesite licencni ključ u postavke plugina</li>
        <li class="list-group-item">Počnite izrađivati adresnice direktno iz WooCommerce narudžbi</li>
      </ol>
    </div>
  </section>

  <section class="py-5">
    <div class="container">
      <h2 class="text-center mb-4">Ključne mogućnosti</h2>
      <div class="row justify-content-center">
        <div class="col-lg-8">
          <ul class="list-group">
            <li class="list-group-item"><i class="bi bi-check-circle-fill text-success me-2"></i>Automatska izrada adresnica za DPD, GLS i Overseas</li>
            <li class="list-group-item"><i class="bi bi-check-circle-fill text-success me-2"></i>Mogućnost dodavanja ostalih kurirskih službi</li>
            <li class="list-group-item"><i class="bi bi-check-circle-fill text-success me-2"></i>Izrada adresnica pojedinačno ili višestruko (multiselect)</li>
            <li class="list-group-item"><i class="bi bi-check-circle-fill text-success me-2"></i>Kreiranje adresnica za povrat proizvoda</li>
            <li class="list-group-item"><i class="bi bi-check-circle-fill text-success me-2"></i>Prikaz statusa paketa unutar narudžbi</li>
            <li class="list-group-item"><i class="bi bi-check-circle-fill text-success me-2"></i>Jednostavno i pregledno sučelje</li>
          </ul>
        </div>
      </div>
    </div>
  </section>

  <section class="py-5 bg-light">
    <div class="container text-center">
      <h2 class="mb-4">Besplatno isprobajte</h2>
      <p class="lead">Svi korisnici dobivaju <strong>10 besplatnih izrada adresnica</strong>. Nakon toga, plugin možete nastaviti koristiti uz <strong>povoljnu godišnju licencu</strong>.</p>
    </div>
  </section>

  <section class="py-5">
    <div class="container">
      <h2 class="text-center mb-4">Za koga je plugin?</h2>
      <div class="row justify-content-center">
        <div class="col-lg-8">
          <ul class="list-group">
            <li class="list-group-item">Web trgovine koje šalju robu putem kurirskih službi</li>
            <li class="list-group-item">Tvrtke koje žele automatizirati dostavu i smanjiti greške</li>
            <li class="list-group-item">WooCommerce korisnici kojima je važna brzina i preciznost</li>
          </ul>
        </div>
      </div>
    </div>
  </section>

  <section class="py-5 bg-light">
    <div class="container">
      <h2 class="text-center mb-4">Zašto odabrati nas?</h2>
      <div class="row justify-content-center">
        <div class="col-lg-8">
          <ul class="list-group">
            <li class="list-group-item"><i class="bi bi-star-fill text-warning me-2"></i>Plug & play rješenje – spremno za korištenje odmah</li>
            <li class="list-group-item"><i class="bi bi-shield-check text-primary me-2"></i>Sigurna i stabilna integracija</li>
            <li class="list-group-item"><i class="bi bi-lightning-fill text-warning me-2"></i>Povezivanje s kuririma u par klikova</li>
            <li class="list-group-item"><i class="bi bi-headset text-primary me-2"></i>Tehnička podrška i redovita ažuriranja</li>
            <li class="list-group-item"><i class="bi bi-clock text-success me-2"></i>Više vremena za prodaju, manje za administraciju</li>
          </ul>
        </div>
      </div>
    </div>
  </section>

  <section class="py-5 bg-primary text-white text-center" id="download">
    <div class="container">
      <h2 class="mb-4">Preuzmite plugin i započnite već danas</h2>
      <div class="d-grid gap-3 d-sm-flex justify-content-sm-center">
        <a href="/download" class="btn btn-light btn-lg px-4">Preuzmi plugin</a>
        <a href="/register" class="btn btn-outline-light btn-lg px-4">Registriraj se i kreiraj licencu</a>
      </div>
    </div>
  </section>

  <footer class="py-4 bg-dark text-white">
    <div class="container text-center">
      <p class="mb-2">Imate pitanja? <a href="/kontakt" class="text-white text-decoration-underline">Kontaktirajte nas</a></p>
      <p class="mb-0"><a href="/dokumentacija" class="text-white text-decoration-underline">Pogledajte dokumentaciju</a></p>
    </div>
  </footer>
@endsection
