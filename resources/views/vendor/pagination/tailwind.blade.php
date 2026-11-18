@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="flex flex-wrap items-center justify-between gap-4">
        <p class="text-xs text-ink/45">
            Showing
            <span class="font-medium text-ink/70">{{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}</span>
            of
            <span class="font-medium text-ink/70">{{ $paginator->total() }}</span>
        </p>

        <ul class="flex flex-wrap items-center gap-1.5">
            <li>
                @if ($paginator->onFirstPage())
                    <span class="inline-flex size-9 cursor-not-allowed items-center justify-center rounded-lg border border-white/5 text-ink/20" aria-disabled="true">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                        <span class="sr-only">Previous</span>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                       class="inline-flex size-9 items-center justify-center rounded-lg border border-white/10 bg-white/5 text-ink/60 transition hover:border-white/25 hover:text-ink">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                        <span class="sr-only">Previous</span>
                    </a>
                @endif
            </li>

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li>
                        <span class="inline-flex size-9 items-center justify-center text-xs text-ink/30">{{ $element }}</span>
                    </li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        <li>
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page"
                                      class="inline-flex size-9 items-center justify-center rounded-lg border border-accent/50 bg-accent/15 text-xs font-semibold text-ink">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $url }}" aria-label="Go to page {{ $page }}"
                                   class="inline-flex size-9 items-center justify-center rounded-lg border border-white/10 bg-white/5 text-xs text-ink/55 transition hover:border-white/25 hover:text-ink">
                                    {{ $page }}
                                </a>
                            @endif
                        </li>
                    @endforeach
                @endif
            @endforeach

            <li>
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                       class="inline-flex size-9 items-center justify-center rounded-lg border border-white/10 bg-white/5 text-ink/60 transition hover:border-white/25 hover:text-ink">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
                        <span class="sr-only">Next</span>
                    </a>
                @else
                    <span class="inline-flex size-9 cursor-not-allowed items-center justify-center rounded-lg border border-white/5 text-ink/20" aria-disabled="true">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
                        <span class="sr-only">Next</span>
                    </span>
                @endif
            </li>
        </ul>
    </nav>
@endif
