<footer class="bg-dark text-white py-5 pt-5">
    <div class="container">
        <div class="row">
            <!-- Left Column - Logo -->
            <div class="col-md-4 text-center mb-3 mb-md-0">
                <img src="{{ asset('assets/logo-white.png') }}" alt="ExpressLabelMaker Logo" class="img-fluid"
                    style="max-height: 60px;">
            </div>

            <div class="col-md-2">
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="/{{ app()->getLocale() }}/#kontakt"
                            class="text-white text-decoration-none">{{ __('footer.kontakt') }}</a></li>
                    <li class="mb-2"><a href="/{{ app()->getLocale() }}/documentation/installation"
                            class="text-white text-decoration-none">{{ __('footer.dokumentacija') }}</a></li>
                    <li class="mb-2"><a href="/{{ app()->getLocale() }}/download"
                            class="text-white text-decoration-none">{{ __('footer.preuzmi_plugin') }}</a></li>
                </ul>
            </div>

            <div class="col-md-2">
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="/{{ app()->getLocale() }}/legal/terms-of-use"
                            class="text-white text-decoration-none">{{ __('footer.uvjeti_koristenja') }}</a></li>
                    <li class="mb-2"><a href="/{{ app()->getLocale() }}/legal/privacy-policy"
                            class="text-white text-decoration-none">{{ __('footer.politika_privatnosti') }}</a></li>
                    <li class="mb-2"><a href="/{{ app()->getLocale() }}/legal/payment-methods"
                            class="text-white text-decoration-none">{{ __('footer.nacini_placanja') }}</a></li>
                    <li class="mb-2"><a href="/{{ app()->getLocale() }}/legal/disclaimer-of-liability"
                            class="text-white text-decoration-none">{{ __('footer.izjava_o_ogranicenju_odgovornosti') }}</a>
                    </li>
                    <li class="mb-2"><a href="/{{ app()->getLocale() }}/legal/cookie-policy"
                            class="text-white text-decoration-none">{{ __('footer.pravila_o_kolacicima') }}</a></li>
                    <li class="mb-2"><a href="/{{ app()->getLocale() }}/legal/legal-notice"
                            class="text-white text-decoration-none">{{ __('footer.impressum') }}</a></li>
                </ul>
            </div>

            <div class="col-md-4 text-center mb-3 mb-md-0">
                <img src="{{ asset('assets/logos/stripe-logo.png') }}" alt="ExpressLabelMaker Logo" class="img-fluid"
                    style="max-height: 60px;">
            </div>
        </div>
    </div>
</footer>
