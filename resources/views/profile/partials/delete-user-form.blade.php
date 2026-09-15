<section>
    <h2 class="h5 fw-bold text-danger">Delete Account</h2>
    <p class="text-muted">Once your account is deleted, its data will be permanently removed.</p>

    <form method="post" action="{{ route('profile.destroy') }}" onsubmit="return confirm('Delete this account permanently?');">
        @csrf
        @method('delete')
        <div class="mb-3">
            <label class="form-label" for="delete_password">Password</label>
            <input id="delete_password" name="password" type="password" class="form-control" required>
            @error('password', 'userDeletion')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>
        <button class="btn btn-outline-danger">Delete Account</button>
    </form>
</section>
