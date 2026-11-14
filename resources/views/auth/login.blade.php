<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | Robert Hotel Inventory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/hotel.css') }}" rel="stylesheet">
</head>
<body class="auth-body d-flex align-items-center">
<div class="container" style="max-width: 460px">
    <div class="text-center mb-4">
        <img src="{{ asset('image/logo.png') }}" alt="Robert Hotel" style="height: 70px">
        <h1 class="auth-title h3 mt-2">Robert Hotel Inventory</h1>
        <p class="auth-subtitle">Replenishment &amp; Supplier Management</p>
    </div>
    <div class="card auth-card shadow">
        <div class="card-body p-4">
            @if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
            <form method="POST" action="{{ route('login.attempt') }}" id="login-form">
                @csrf
                <label class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control mb-3" value="{{ old('email') }}" required autofocus>
                <label class="form-label">Password</label>
                <input type="password" name="password" id="password" class="form-control mb-3" required>
                <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="remember" id="remember"><label class="form-check-label" for="remember">Remember me</label></div>
                <button class="btn btn-primary w-100">Sign in</button>
            </form>
            @if ($demoUsers->isNotEmpty())
                <hr>
                <p class="small text-muted mb-2">Demo accounts (password: <code>password</code>) &mdash; click to fill:</p>
                <div class="d-flex flex-wrap gap-2">
                    @foreach ($demoUsers as $user)
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="document.getElementById('email').value='{{ $user->email }}';document.getElementById('password').value='password';">{{ ucfirst($user->role) }}</button>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
</body>
</html>
