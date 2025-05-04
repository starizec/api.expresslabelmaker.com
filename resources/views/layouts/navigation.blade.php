<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container px-5">
        <a class="navbar-brand" href="{{ url('/') }}">
            <img src="{{ asset('assets/logo.png') }}" alt="Company Logo" height="40" class="d-inline-block">
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown"
            aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNavDropdown">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item active">
                    <a class="nav-link" href="{{ url('/') }}">{{ __('messages.home') }} <span class="sr-only">(current)</span></a>
                </li>
                @guest
                    <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">{{ __('messages.login') }}</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('register') }}">{{ __('messages.register') }}</a></li>
                @else
                    <li class="nav-item"><a class="nav-link" href="{{ url('/profile') }}">{{ __('messages.my_profile') }}</a></li>
                    @if (Auth::user()->is_admin)
                        <li class="nav-item"><a class="nav-link" href="{{ url('/admin') }}">{{ __('messages.admin_panel') }}</a></li>
                    @endif
                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="nav-link border-0 bg-transparent">
                                {{ __('messages.logout') }}
                            </button>
                        </form>
                    </li>
                @endguest
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="languageDropdown" role="button"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        {{ strtoupper(app()->getLocale()) }}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="languageDropdown">
                        <li><a class="dropdown-item" href="{{ route('language.switch', 'hr') }}">Hrvatski</a></li>
                        <li><a class="dropdown-item" href="{{ route('language.switch', 'en') }}">English</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
