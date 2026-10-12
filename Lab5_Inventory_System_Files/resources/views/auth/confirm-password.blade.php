@extends('layouts.guest')

@section('title', 'Confirm Password')

@section('content')
<h2 class="fw-bold mb-3">Confirm password</h2>
<p class="text-muted">Please confirm your password before continuing.</p>

<form method="POST" action="{{ route('password.confirm') }}">
    @csrf
    <div class="mb-4">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required autocomplete="current-password">
        @error('password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
    </div>

    <button class="btn btn-primary w-100">Confirm</button>
</form>
@endsection
