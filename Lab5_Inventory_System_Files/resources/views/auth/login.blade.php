@extends('layouts.guest')

@section('title', 'Login')

<img src="{{ asset('images/logo.png') }}" alt="Robert Hotel Logo" class="mx-auto mb-4 w-48">


@section('content')
<h2 class="fw-bold mb-4">Sign in</h2>

<form method="POST" action="{{ route('login') }}">
    @csrf

    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input id="email" type="email" name="email"
               class="form-control @error('email') is-invalid @enderror"
               value="{{ old('email') }}" required autofocus autocomplete="username">
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input id="password" type="password" name="password"
               class="form-control @error('password') is-invalid @enderror"
               required autocomplete="current-password">
        @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-check mb-4">
        <input class="form-check-input" type="checkbox" name="remember" id="remember_me">
        <label class="form-check-label" for="remember_me">Remember me</label>
    </div>

    <button class="btn btn-primary w-100 py-2">Log in</button>

    @if(Route::has('register'))
        <div class="text-center mt-3">
            <a href="{{ route('register') }}">Create an account</a>
        </div>
    @endif
</form>
@endsection
