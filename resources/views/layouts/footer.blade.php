<footer class="bg-white py-4 mt-auto">
    <div class="container px-5">
        <div class="row align-items-center justify-content-between flex-column flex-sm-row">
            <div class="col-auto"><div class="small m-0">Copyright &copy; {{ config('app.name') }} {{ date('Y') }}</div></div>
            <div class="col-auto">
                <a class="small" href="{{ url('/privacy') }}">Privacy</a>
                <span class="mx-1">&middot;</span>
                <a class="small" href="{{ url('/terms') }}">Terms</a>
                <span class="mx-1">&middot;</span>
                <a class="small" href="{{ url('/contact') }}">Contact</a>
            </div>
        </div>
    </div>
</footer> 