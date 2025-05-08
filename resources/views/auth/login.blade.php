<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>@yield('title', 'Personal - Start Bootstrap Theme')</title>

    <!-- Favicon-->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/favicon.ico') }}" />
    <!-- Custom Google font-->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@100;200;300;400;500;600;700;800;900&amp;display=swap"
        rel="stylesheet" />
    <!-- Bootstrap icons-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet" />
    <!-- Core theme CSS-->
    <link href="{{ asset('css/bootstrap/bootstrap.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/styles.css') }}" rel="stylesheet" />
    @stack('styles')
</head>

<body class="d-flex flex-column h-100" style="background: linear-gradient(to right, #045cb8, #047adb)">
    <main class="flex-shrink-0">
        <div class="content">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-12 text-center mt-5">
                        <img src="{{ asset('assets/logo-white.png') }}" alt="{{ config('app.name') }}" class="img-fluid" style="max-height: 80px;">
                    </div>
                    <div class="col-md-6">
                        <div class="card shadow-sm" style="margin-top: 20%;">
                            <div class="card-body p-4">
                                <div class="mb-4 text-center">
                                    <h3>{{ __('messages.sign_in') }}</h3>
                                </div>
                                <form action="{{ route('login', ['lang' => app()->getLocale()]) }}" method="post">
                                    @csrf
                                    <div class="form-group first">
                                        <label for="username">{{ __('messages.email') }}</label>
                                        <input type="email" name="email"
                                            class="form-control @error('email') is-invalid @enderror" id="username"
                                            value="{{ old('email') }}" required autofocus>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-group last mb-4">
                                        <label for="password">{{ __('messages.password') }}</label>
                                        <input type="password" name="password"
                                            class="form-control @error('password') is-invalid @enderror" id="password"
                                            required>
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="d-flex mb-5 align-items-center">
                                        <label class="control control--checkbox mb-0">
                                            <span class="caption">{{ __('messages.remember_me') }}</span>
                                            <input type="checkbox" name="remember" id="remember"
                                                {{ old('remember') ? 'checked' : '' }} />
                                            <div class="control__indicator"></div>
                                        </label>
                                        <span class="ml-auto">
                                            <a href="{{ route('password.request', ['lang' => app()->getLocale()]) }}"
                                                class="forgot-pass">{{ __('messages.forgot_password') }}</a>
                                        </span>
                                    </div>

                                    <button type="submit"
                                        class="btn btn-block btn-primary">{{ __('messages.login') }}</button>
                                </form>

                                <div class="text-center mt-4">
                                    <p class="mb-0">
                                        {{ __('messages.dont_have_account') }}
                                        <a href="{{ route('register', ['lang' => app()->getLocale()]) }}"
                                            class="text-decoration-none">{{ __('messages.register_here') }}</a>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>


    <!-- Bootstrap core JS-->
    <script src="{{ asset('js/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <!-- Core theme JS-->
    <script src="{{ asset('js/scripts.js') }}"></script>
    @stack('scripts')
</body>

</html>
