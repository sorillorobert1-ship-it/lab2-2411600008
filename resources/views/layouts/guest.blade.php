<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Login') — Robert Hotel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/hotel.css') }}" rel="stylesheet">
</head>
<body class="auth-body">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-5">
                <div class="text-center mb-4">
                    <img src="{{ asset('image/logo.png') }}" alt="Robert Hotel Logo" class="mx-auto mb-3" style="max-width: 200px;">
                    <h1 class="fw-bold auth-title">Robert Hotel</h1>
                    <p class="auth-subtitle mb-0">Reservation & Room Inventory</p>
                </div>

                <div class="card shadow-sm auth-card">
                    <div class="card-body p-4 p-md-5">
                        @if(session('status'))
                            <div class="alert alert-success">{{ session('status') }}</div>
                        @endif

                        @isset($slot)
                            {{ $slot }}
                        @else
                            @yield('content')
                        @endisset
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
