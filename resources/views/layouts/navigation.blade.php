<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container px-5">
        <a class="navbar-brand" href="{{ url('/') }}">
            <img src="{{ asset('assets/logo.png') }}" alt="Company Logo" height="40" class="d-inline-block">
        </a>

        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown"
            aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNavDropdown">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item active">
                    <a class="nav-link" href="{{ url('/') }}">Početna <span class="sr-only">(current)</span></a>
                </li>
                @guest
                    <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('register') }}">Register</a></li>
                @else
                    <li class="nav-item"><a class="nav-link" href="{{ url('/profile') }}">My Profile</a></li>
                    @if (Auth::user()->is_admin)
                        <li class="nav-item"><a class="nav-link" href="{{ url('/admin') }}">Admin Panel</a></li>
                    @endif
                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="nav-link">Logout</button>
                        </form>
                    </li>
                @endguest
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="languageDropdown" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        {{ strtoupper(app()->getLocale()) }}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="languageDropdown">
                        <li><a class="dropdown-item" href="{{ route('language.switch', 'hr') }}">Hrvatski</a></li>
                        <li><a class="dropdown-item" href="{{ route('language.switch', 'en') }}">English</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
