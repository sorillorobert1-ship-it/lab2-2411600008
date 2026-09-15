<section>
    <h2 class="h5 fw-bold">Update Password</h2>
    <p class="text-muted">Use a long, random password to keep your account secure.</p>

    <form method="post" action="{{ route('password.update') }}">
        @csrf
        @method('put')

        <div class="mb-3">
            <label class="form-label" for="current_password">Current Password</label>
            <input id="current_password" name="current_password" type="password" class="form-control">
            @error('current_password', 'updatePassword')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="password">New Password</label>
            <input id="password" name="password" type="password" class="form-control">
            @error('password', 'updatePassword')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="password_confirmation">Confirm Password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" class="form-control">
        </div>

        <button class="btn btn-primary">Save</button>
        @if (session('status') === 'password-updated')
            <span class="text-success ms-2">Saved.</span>
        @endif
    </form>
</section>
