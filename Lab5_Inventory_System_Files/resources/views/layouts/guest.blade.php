<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Login') -hotel Inventory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/inventory.css') }}" rel="stylesheet">
</head>
<body class="min-vh-100 d-flex align-items-center" style="background:#f5f7fa;">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-5">
                <div class="text-center mb-4">
                    <h1 class="fw-bold" style="color:var(--inventory-primary);">hotel Inventory</h1>
                    <p class="text-muted">Inventory Management System</p>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body p-4 p-md-5">
                        @if(session('status'))
                            <div class="alert alert-success">{{ session('status') }}</div>
                        @endif

                        @yield('content')
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
