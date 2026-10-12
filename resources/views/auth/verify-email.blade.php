@extends('layouts.guest')

@section('title', 'Verify Email')

@section('content')
<h2 class="fw-bold mb-3" style="color:#17365d;">Verify your email</h2>
<p class="text-muted">Thanks for signing up. Please verify your email address using the link we sent you.</p>

@if (session('status') == 'verification-link-sent')
    <div class="alert alert-success">A new verification link has been sent to your email address.</div>
@endif

<div class="d-flex justify-content-between align-items-center gap-2">
    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button class="btn btn-primary">Resend Verification Email</button>
    </form>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button class="btn btn-link">Log Out</button>
    </form>
</div>
@endsection
