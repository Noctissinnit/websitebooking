<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Booking Meeting Room') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script src="/js/app.js"></script>
    <script src="/js/validate.js"></script>
    
    <script>
        $(document).ready(() => {
            @if (session()->has('error'))
                alert({
                    title: "Error",
                    text: "{{ session('error') }}",
                    icon: "error"
                });
            @endif

            @if (session()->has('success'))
                alert({
                    title: "Berhasil",
                    text: "{{ session('success') }}",
                    icon: "success"
                });
            @endif
        });
    </script>

    @yield('head')
</head>
<body>
    @include('layouts.loading')
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="/">
                    @if(View::hasSection('navbar-title'))
                        @yield('navbar-title')
                    @else
                        {{ config('app.name', 'Booking Meeting Room') }}
                    @endif
                </a>
                @auth
                    <a class="nav-link ms-1 mt-1" href="{{ route('admin.dashboard') }}">Dashboard</a>
                    <a class="nav-link ms-3 mt-1" href="{{ route('rooms.index') }}">Rooms</a>
                @endauth
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto"></ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        @guest
                            {{-- <!-- Link untuk Login sebagai Admin -->
                            <li class="nav-item bg-primary rounded mx-1">
                                <a class="nav-link text-light" href="{{ route('login') }}">{{ __('Login sebagai Admin') }}</a>
                            </li>
                            <!-- Link untuk Login sebagai User -->
                            <li class="nav-item bg-success rounded">
                                <a class="nav-link text-light" href="{{ route('login.google') }}">{{ __('Login sebagai User') }}</a>
                            </li> --}}
                        @else
                        <li class="nav-item bg-primary rounded">
                                <a id="nav-link" class="nav-link text-light" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    Halo, {{ Auth::user()->name }}
                                </a>

                            </li>
                            <li class="nav-item bg-danger rounded mx-1">
                        <a class="nav-link text-light" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                        </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            @yield('content')
        </main>
    </div>
</body>
</html>
