<footer class="bg-dark text-white py-4">
    <div class="container">
        <div class="row">
            <!-- Left Column - Logo -->
            <div class="col-md-4 text-center mb-3 mb-md-0">
                <img src="{{ asset('assets/logo-white.png') }}" alt="ExpressLabelMaker Logo" class="img-fluid" style="max-height: 60px;">
            </div>

            <!-- Middle Column - Title -->
            <div class="col-md-4 text-center mb-3 mb-md-0">
                <h5 class="mb-2">ExpressLabelMaker.com</h5>
                <p></p>
            </div>

            <!-- Right Column - Links -->
            <div class="col-md-4">
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="/{{ app()->getLocale() }}/uvjeti-koristenja" class="text-white text-decoration-none">{{ __('footer.uvjeti_koristenja') }}</a></li>
                    <li class="mb-2"><a href="/{{ app()->getLocale() }}/politika-privatnosti" class="text-white text-decoration-none">{{ __('footer.politika_privatnosti') }}</a></li>
                    <li class="mb-2"><a href="/{{ app()->getLocale() }}/nacini-placanja" class="text-white text-decoration-none">{{ __('footer.nacini_placanja') }}</a></li>
                    <li class="mb-2"><a href="/{{ app()->getLocale() }}/izjava-o-ogranicenju-odgovornosti" class="text-white text-decoration-none">{{ __('footer.izjava_o_ogranicenju_odgovornosti') }}</a></li>
                    <li class="mb-2"><a href="/{{ app()->getLocale() }}/pravila-o-kolacicima" class="text-white text-decoration-none">{{ __('footer.pravila_o_kolacicima') }}</a></li>
                    <li class="mb-2"><a href="/{{ app()->getLocale() }}/impressum" class="text-white text-decoration-none">{{ __('footer.impressum') }}</a></li>
                </ul>
            </div>
        </div>
    </div>
</footer>
