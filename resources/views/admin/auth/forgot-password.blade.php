<x-admin.auth-layout title="Forgot password">
    <h1 class="text-xl font-semibold text-ink">Reset your password</h1>
    <p class="mt-1.5 text-sm text-ink/50">
        Enter your email and we will send you a reset link.
    </p>

    @if (session('status'))
        <div class="mt-5 rounded-lg border border-emerald-400/25 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-100">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mt-5 rounded-lg border border-red-400/25 bg-red-500/10 px-4 py-3 text-sm text-red-100">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.password.email') }}" class="mt-6 space-y-4">
        @csrf
        <div>
            <label for="email" class="admin-label">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                   class="admin-input" placeholder="you@company.co.uk">
        </div>

        <button type="submit"
                class="w-full rounded-lg bg-accent px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-accent-deep">
            Send reset link
        </button>
    </form>

    <a href="{{ route('admin.login') }}" class="mt-5 inline-block text-xs text-ink/50 hover:text-ink">
        &larr; Back to sign in
    </a>
</x-admin.auth-layout>
