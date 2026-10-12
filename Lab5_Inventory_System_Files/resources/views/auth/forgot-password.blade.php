@extends('layouts.guest')

@section('title', 'Forgot Password')

@section('content')
<h2 class="fw-bold mb-3">Forgot password?</h2>
<p class="text-muted">Enter your email address and we will send a password reset link.</p>

<form method="POST" action="{{ route('password.email') }}">
    @csrf
    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
               value="{{ old('email') }}" required autofocus>
        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <button class="btn btn-primary w-100">Email Password Reset Link</button>
</form>
@endsection
