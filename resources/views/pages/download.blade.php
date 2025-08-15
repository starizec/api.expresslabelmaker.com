@extends('layouts.app')

@section('content')
    <header class="text-white py-5" style="background: linear-gradient(to right, #045cb8, #047adb)">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 text-center">
                    <a href="{{ Storage::disk('public')->url($plugin_downloads->plugin_download_link) }}"
                        class="btn btn-light btn-lg me-3" download>
                        Preuzmi plugin
                    </a>

                    <div class="mt-2 text-white">Version: {{ $plugin_downloads->version }}</div>

                </div>

                <div class="col-md-6 d-none d-md-block">
                    <h1 class="display-4" style="font-size: 28px; font-weight: 600;">Preuzmi Woocommerce plugin</h1>
                    <p class="lead mb-4">Iskoristite sve prednosti za bržu i efikasniju obradu narudžbi u WooCommerce
                        trgovini.
                    </p>
                    <div class="mb-4">
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="bi bi-check2 text-white me-2"></i> Pojedinačni ispis adresnica</li>
                            <li class="mb-2"><i class="bi bi-check2 text-white me-2"></i> Višestruki ispis adresnica</li>
                            <li class="mb-2"><i class="bi bi-check2 text-white me-2"></i> Integracija s paketomatima</li>
                            <li class="mb-2"><i class="bi bi-check2 text-white me-2"></i> Praćenje statusa dostave</li>
                            <li class="mb-2"><i class="bi bi-check2 text-white me-2"></i> Može se integrirati više kurira
                                s jednom licencom</li>
                            <li class="mb-2"><i class="bi bi-check2 text-white me-2"></i> Izrada naloga za prikup</li>
                            <li class="mb-2"><i class="bi bi-check2 text-white me-2"></i> Spremanje PDF adresnica u
                                narudžbi</li>
                            <li class="mb-2"><i class="bi bi-check2 text-white me-2"></i> Brisanje svih spremljenih
                                PDF-ova</li>
                            <li class="mb-2"><i class="bi bi-check2 text-white me-2"></i> Intuitivan prikaz grešaka</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Content from posts -->
    @if (isset($post) && $post->translations->isNotEmpty())
        <div class="container mb-5">
            <div class="row justify-content-center">
                <div class="col-md-12">
                    <div class="card mt-4">
                        <div class="card-body">
                            @php
                                $translation = $post->translations->first();
                            @endphp
                            <h4 class="card-title">{{ $translation->title }}</h4>
                            <div class="card-text">
                                {!! $translation->content !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
