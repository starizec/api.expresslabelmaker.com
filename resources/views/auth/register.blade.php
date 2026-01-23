<!DOCTYPE html>
<html lang="en" class="h-100">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>@yield('title', __('messages.register') . ' - ' . config('app.name'))</title>

    <!-- Favicon-->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/favicon.ico') }}" />
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@100;200;300;400;500;600;700;800;900&display=swap"
        rel="stylesheet" />
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet" />
    <!-- Core theme CSS -->
    <link href="{{ asset('css/bootstrap/bootstrap.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/styles.css') }}" rel="stylesheet" />
    @stack('styles')
</head>

<body class="d-flex flex-column h-100 align-items-center justify-content-center" style="background: linear-gradient(to right, #045cb8, #047adb)">
    <main class="w-100">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 text-center mb-4">
                    <img src="{{ asset('assets/logo-white.png') }}" alt="{{ config('app.name') }}" class="img-fluid" style="max-height: 80px;">
                </div>
                <div class="col-md-6">
                    <div class="card shadow">
                        <div class="card-body p-4">
                            <div class="mb-4 text-center">
                                <h3>{{ __('messages.register') }}</h3>
                            </div>

                            @if(session('error'))
                                <div class="alert alert-danger">
                                    {{ session('error') }}
                                </div>
                            @endif

                            <form action="{{ route('register', ['lang' => app()->getLocale()]) }}" method="post">
                                @csrf

                                <div class="mb-3">
                                    <label for="email" class="form-label">{{ __('messages.email') }}</label>
                                    <input type="email" name="email"
                                        class="form-control @error('email') is-invalid @enderror" id="email"
                                        value="{{ old('email') }}" required autofocus>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">{{ __('messages.password') }}</label>
                                    <input type="password" name="password"
                                        class="form-control @error('password') is-invalid @enderror" id="password"
                                        required>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                    <label for="password_confirmation" class="form-label">{{ __('messages.confirm_password') }}</label>
                                    <input type="password" name="password_confirmation" class="form-control"
                                        id="password_confirmation" required>
                                </div>

                                <div class="mb-4">
                                    {!! NoCaptcha::renderJs() !!}
                                    {!! NoCaptcha::display() !!}
                                    @error('g-recaptcha-response')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="d-grid">
                                    <button type="submit"
                                        class="btn btn-primary">{{ __('messages.register') }}</button>
                                </div>
                            </form>

                            <div class="text-center mt-4">
                                <p class="mb-0">
                                    {{ __('messages.already_have_account') }}
                                    <a href="{{ route('login', ['lang' => app()->getLocale()]) }}"
                                        class="text-decoration-none">{{ __('messages.sign_in') }}</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Bootstrap JS -->
    <script src="{{ asset('js/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <!-- Core theme JS -->
    <script src="{{ asset('js/scripts.js') }}"></script>
    @stack('scripts')
</body>

</html>
