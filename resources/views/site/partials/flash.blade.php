@php
    $success = session('enquiry_success') ?? session('application_success') ?? session('status');
@endphp

@if ($success)
    <div class="fixed inset-x-4 bottom-6 z-[60] mx-auto max-w-md sm:left-auto sm:right-6 sm:mx-0">
        <div role="status"
             class="flex items-start gap-3 rounded-2xl border border-accent/30 bg-surface-800/95 p-4 text-sm text-ink shadow-2xl shadow-black/40 backdrop-blur-xl">
            <span class="mt-0.5 text-accent-soft"><x-site.icon name="check-circle" :size="20" /></span>
            <p class="flex-1">{{ $success }}</p>
        </div>
    </div>
@endif

@if ($errors->any())
    <div class="fixed inset-x-4 bottom-6 z-[60] mx-auto max-w-md sm:left-auto sm:right-6 sm:mx-0">
        <div role="alert"
             class="rounded-2xl border border-red-400/30 bg-red-500/10 p-4 text-sm text-red-100 shadow-2xl backdrop-blur-xl">
            <p class="font-semibold">Please check the form and try again.</p>
            <ul class="mt-2 list-disc space-y-1 pl-5 text-red-100/80">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
