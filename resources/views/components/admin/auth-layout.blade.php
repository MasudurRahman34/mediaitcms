@props(['title' => 'Admin'])

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title }} | {{ config('app.name') }}</title>
    @vite(['resources/css/admin.css'])
</head>
<body>
    <div class="flex min-h-dvh items-center justify-center bg-brand-gradient px-5 py-12">
        <div class="w-full max-w-md">
            <div class="mb-8 text-center">
                <div class="inline-flex items-center gap-2.5">
                    <svg class="size-10 text-ink" viewBox="0 0 56 56" fill="none" aria-hidden="true">
                        <path d="M7 50V21.5a10.5 10.5 0 0 1 21 0V50M28 50V21.5a10.5 10.5 0 0 1 21 0V50"
                              stroke="currentColor" stroke-width="9" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span class="flex flex-col text-left leading-none">
                        <span class="text-2xl font-extrabold tracking-tight text-ink">MEDIA</span>
                        <span class="mt-1 text-[0.6rem] font-semibold tracking-[0.4em] text-accent-soft">IT SOFT</span>
                    </span>
                </div>
                <p class="mt-4 text-sm text-ink/50">Content management</p>
            </div>

            <div class="rounded-2xl border border-white/10 bg-panel/95 p-7 shadow-2xl shadow-black/50 backdrop-blur">
                {{ $slot }}
            </div>

            <p class="mt-6 text-center text-xs text-ink/35">
                &copy; {{ now()->year }} {{ config('app.name') }}
            </p>
        </div>
    </div>
</body>
</html>
