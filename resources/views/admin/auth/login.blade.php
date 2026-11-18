<x-admin.auth-layout title="Sign in">
    <h1 class="text-xl font-semibold text-ink">Sign in</h1>
    <p class="mt-1.5 text-sm text-ink/50">Use your administrator account.</p>

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

    <form method="POST" action="{{ route('admin.login.store') }}" class="mt-6 space-y-4">
        @csrf

        <div>
            <label for="email" class="admin-label">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}"
                   required autofocus autocomplete="username"
                   class="admin-input" placeholder="you@company.co.uk">
        </div>

        <div>
            <div class="flex items-center justify-between">
                <label for="password" class="admin-label">Password</label>
                <a href="{{ route('admin.password.request') }}" class="mb-1.5 text-xs text-accent-soft hover:text-ink">
                    Forgot password?
                </a>
            </div>
            <input id="password" name="password" type="password" required autocomplete="current-password"
                   class="admin-input" placeholder="••••••••••">
        </div>

        <label class="flex items-center gap-2.5 text-sm text-ink/60">
            <input type="checkbox" name="remember" value="1"
                   class="size-4 rounded border-line bg-near-black accent-[#3b82f6]">
            Keep me signed in
        </label>

        <button type="submit"
                class="w-full rounded-lg bg-accent px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-accent-deep">
            Sign in
        </button>
    </form>

    @if (app()->isLocal())
        <div class="mt-6 rounded-lg border border-line bg-near-black/40 px-4 py-3 text-xs leading-relaxed text-ink/45">
            <span class="font-semibold text-ink/70">Demo accounts</span><br>
            admin@mediaitsoft.co.uk · editor@mediaitsoft.co.uk · recruiter@mediaitsoft.co.uk<br>
            Password: <code class="text-accent-soft">Password!234</code>
        </div>
    @endif
</x-admin.auth-layout>
