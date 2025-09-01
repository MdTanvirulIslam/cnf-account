<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/src/assets/img/favicon.ico') }}"/>
    <link href="{{ asset("assets/layouts/vertical-light-menu/css/light/loader.css") }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset("assets/layouts/vertical-light-menu/css/dark/loader.css") }}" rel="stylesheet" type="text/css" />
    <script src="{{ asset("assets/layouts/vertical-light-menu/loader.js") }}"></script>
    <!-- BEGIN GLOBAL MANDATORY STYLES -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:400,600,700" rel="stylesheet">
    <link href="{{ asset("assets/src/bootstrap/css/bootstrap.min.css") }}" rel="stylesheet" type="text/css" />

    <link href="{{ asset("assets/layouts/vertical-light-menu/css/light/plugins.css") }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset("assets/src/assets/css/light/authentication/auth-boxed.css") }}" rel="stylesheet" type="text/css" />

    {{--<link href="{{ "assets/layouts/vertical-light-menu/css/dark/plugins.css"}}" rel="stylesheet" type="text/css" />
    <link href="{{ "assets/src/assets/css/dark/authentication/auth-boxed.css"}}" rel="stylesheet" type="text/css" />--}}
    <!-- END GLOBAL MANDATORY STYLES -->
    <!-- Scripts -->
    {{--@vite(['resources/css/app.css', 'resources/js/app.js'])--}}
</head>
<body class="form">

<!-- BEGIN LOADER -->
<div id="load_screen">
    <div class="loader">
        <div class="loader-content">
            <div class="spinner-grow align-self-center"></div>
        </div>
    </div>
</div>
<!--  END LOADER -->

<div class="auth-container d-flex">

    <div class="container mx-auto align-self-center">

        <div class="row">

            <div class="col-xxl-4 col-xl-5 col-lg-5 col-md-8 col-12 d-flex flex-column align-self-center mx-auto">
                <div class="card mt-3 mb-3">
                    <div class="card-body">
                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-12 mb-3">

                                    <h2>Sign In</h2>
                                    <p>Enter your email and password to login</p>

                                </div>
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input
                                            type="email"
                                            class="form-control @error('email') is-invalid @enderror"
                                            id="email"
                                            name="email"
                                            value="{{ old('email') }}"
                                            required
                                            autofocus
                                            autocomplete="username"
                                        >
                                        <x-input-error :messages="$errors->get('email')" class="invalid-feedback d-block" />
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="mb-4">
                                        <label for="password" class="form-label">Password</label>
                                        <input
                                            type="password"
                                            class="form-control @error('password') is-invalid @enderror"
                                            id="password"
                                            name="password"
                                            required
                                            autocomplete="current-password"
                                        >
                                        <x-input-error :messages="$errors->get('password')" class="invalid-feedback d-block" />
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="mb-3">
                                        <div class="form-check form-check-primary form-check-inline">

                                            <label for="remember_me" class="form-check-label">
                                                <input id="remember_me" type="checkbox" class="form-check-input me-3" name="remember">
                                                <span class="form-check-label">{{ __('Remember me') }}</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="mb-4">
                                        <button class="btn btn-secondary w-100">LOG IN</button>
                                    </div>
                                </div>


                                <div class="col-12">
                                    <div class="text-center">
                                        @if (\Illuminate\Support\Facades\Route::has('password.request'))
                                            <a class="text-warning" href="{{ route('password.request') }}">
                                                {{ __('Forgot your password?') }}
                                            </a>
                                        @endif
                                    </div>

                                </div>

                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>

<!-- BEGIN GLOBAL MANDATORY SCRIPTS -->
<script src="{{ asset("assets/src/bootstrap/js/bootstrap.bundle.min.js") }}"></script>
<!-- END GLOBAL MANDATORY SCRIPTS -->


</body>
</html>
