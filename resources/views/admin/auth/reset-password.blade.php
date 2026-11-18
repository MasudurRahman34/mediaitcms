<x-admin.auth-layout title="Choose a new password">
    <h1 class="text-xl font-semibold text-ink">Choose a new password</h1>
    <p class="mt-1.5 text-sm text-ink/50">Use at least 10 characters with letters and numbers.</p>

    @if ($errors->any())
        <div class="mt-5 rounded-lg border border-red-400/25 bg-red-500/10 px-4 py-3 text-sm text-red-100">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.password.update') }}" class="mt-6 space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div>
            <label for="email" class="admin-label">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email', $email) }}" required
                   class="admin-input">
        </div>

        <div>
            <label for="password" class="admin-label">New password</label>
            <input id="password" name="password" type="password" required autocomplete="new-password"
                   class="admin-input">
        </div>

        <div>
            <label for="password_confirmation" class="admin-label">Confirm password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required
                   autocomplete="new-password" class="admin-input">
        </div>

        <button type="submit"
                class="w-full rounded-lg bg-accent px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-accent-deep">
            Reset password
        </button>
    </form>
</x-admin.auth-layout>
