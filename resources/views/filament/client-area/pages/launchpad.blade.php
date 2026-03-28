<x-filament-panels::page>
    {{-- ── Header ──────────────────────────────────────────────────────── --}}
    <div class="mb-8">
        <h2 class="text-2xl font-bold tracking-tight text-white">
            🚀 My Launchpad
        </h2>
        <p class="mt-1 text-sm text-white/50">
            Your personal directory of RBeverything tools and platforms.
        </p>
    </div>

    @if($links->isEmpty())
        {{-- ── Empty State ─────────────────────────────────────────────── --}}
        <div class="flex flex-col items-center justify-center gap-4 rounded-2xl border border-white/10 bg-white/5 p-16 text-center backdrop-blur-sm">
            <x-heroicon-o-squares-2x2 class="h-16 w-16 text-white/20" />
            <div>
                <p class="text-lg font-semibold text-white/70">No tools available yet</p>
                <p class="mt-1 text-sm text-white/40">Check back soon — new tools will appear here.</p>
            </div>
        </div>
    @else
        {{-- ── Launchpad Grid ───────────────────────────────────────────── --}}
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
            @foreach($links as $link)
                @if($link['has_access'])
                    {{-- ── Accessible Card ──────────────────────────────── --}}
                    <a
                        href="{{ $link['url'] }}"
                        target="{{ $link['is_external'] ? '_blank' : '_self' }}"
                        rel="{{ $link['is_external'] ? 'noopener noreferrer' : '' }}"
                        class="group relative flex flex-col items-center gap-3 rounded-2xl border border-white/10
                               bg-white/5 p-6 text-center backdrop-blur-sm transition-all duration-300
                               hover:scale-105 hover:border-amber-400/40 hover:bg-white/10 hover:shadow-2xl hover:shadow-amber-900/20"
                    >
                        {{-- Icon --}}
                        <div class="flex h-14 w-14 items-center justify-center rounded-xl
                                    bg-gradient-to-br from-amber-500/20 to-amber-700/20
                                    ring-1 ring-amber-400/20 transition-all duration-300
                                    group-hover:from-amber-500/30 group-hover:to-amber-700/30 group-hover:ring-amber-400/40">
                            <x-dynamic-component
                                :component="'heroicon-o-' . $link['icon']"
                                class="h-7 w-7 text-amber-400 transition-transform duration-300 group-hover:scale-110"
                            />
                        </div>

                        {{-- Text --}}
                        <div>
                            <p class="text-sm font-semibold text-white transition-colors group-hover:text-amber-300">
                                {{ $link['title'] }}
                            </p>
                            @if($link['description'])
                                <p class="mt-1 text-xs leading-relaxed text-white/50">
                                    {{ Str::limit($link['description'], 60) }}
                                </p>
                            @endif
                        </div>

                        {{-- External link badge --}}
                        @if($link['is_external'])
                            <x-heroicon-o-arrow-top-right-on-square
                                class="absolute right-3 top-3 h-3.5 w-3.5 text-white/20 transition-colors group-hover:text-amber-400/60"
                            />
                        @endif
                    </a>
                @else
                    {{-- ── Locked Card (insufficient permission) ───────── --}}
                    <div
                        x-data="{ tooltip: false }"
                        @mouseenter="tooltip = true"
                        @mouseleave="tooltip = false"
                        class="group relative flex flex-col items-center gap-3 rounded-2xl border border-white/5
                               bg-white/[0.02] p-6 text-center opacity-50 cursor-not-allowed"
                    >
                        {{-- Lock overlay --}}
                        <div class="flex h-14 w-14 items-center justify-center rounded-xl
                                    bg-white/5 ring-1 ring-white/10">
                            <x-heroicon-o-lock-closed class="h-7 w-7 text-white/30" />
                        </div>

                        <div>
                            <p class="text-sm font-semibold text-white/40">{{ $link['title'] }}</p>
                            <p class="mt-1 text-xs text-white/25">Premium feature</p>
                        </div>

                        {{-- Tooltip --}}
                        <div
                            x-show="tooltip"
                            x-transition
                            class="absolute -top-10 left-1/2 z-10 -translate-x-1/2 whitespace-nowrap
                                   rounded-lg bg-gray-900 px-3 py-1.5 text-xs text-white shadow-lg ring-1 ring-white/10"
                        >
                            Upgrade your plan to unlock this
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    @endif
</x-filament-panels::page>
