@extends('layouts.app')

@section('title', 'Profile')

@section('content')
<h1 class="fw-bold mb-4">Profile</h1>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                @include('profile.partials.update-password-form')
            </div>
        </div>
        <div class="card shadow-sm">
            <div class="card-body">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</div>
@endsection
