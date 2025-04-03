<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>@yield('title', 'Register - Personal Theme')</title>

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

<body class="d-flex flex-column h-100 bg-light">
    <main class="flex-shrink-0">
        <div class="content">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <div class="card shadow-sm" style="margin-top: 20%;">
                            <div class="card-body p-4">
                                <div class="mb-4 text-center">
                                    <h3>{{ __('messages.register') }}</h3>
                                </div>
                                <form action="{{ route('register') }}" method="post">
                                    @csrf

                                    <div class="form-group">
                                        <label for="email">{{ __('messages.email') }}</label>
                                        <input type="email" name="email"
                                            class="form-control @error('email') is-invalid @enderror" id="email"
                                            value="{{ old('email') }}" required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="password">{{ __('messages.password') }}</label>
                                        <input type="password" name="password"
                                            class="form-control @error('password') is-invalid @enderror" id="password"
                                            required>
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group mb-4">
                                        <label for="password-confirm">{{ __('messages.confirm_password') }}</label>
                                        <input type="password" name="password_confirmation" class="form-control"
                                            id="password-confirm" required>
                                    </div>

                                    <button type="submit"
                                        class="btn btn-block btn-primary">{{ __('messages.register') }}</button>
                                </form>

                                <div class="text-center mt-4">
                                    <p class="mb-0">
                                        {{ __('messages.already_have_account') }}
                                        <a href="{{ route('login') }}"
                                            class="text-decoration-none">{{ __('messages.sign_in') }}</a>
                                    </p>
                                </div>
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
