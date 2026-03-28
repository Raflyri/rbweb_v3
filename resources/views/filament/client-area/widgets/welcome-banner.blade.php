<x-filament-widgets::widget class="fi-wi-welcome-banner">
    {{-- Greeting banner with user name, role, and quick article stats --}}
    <div class="relative overflow-hidden rounded-2xl border border-amber-400/20
                bg-gradient-to-br from-amber-950/60 via-gray-900/80 to-gray-950/80
                p-6 backdrop-blur-sm">

        {{-- Decorative glow --}}
        <div class="pointer-events-none absolute -right-10 -top-10 h-48 w-48
                    rounded-full bg-amber-500/10 blur-3xl"></div>

        <div class="relative flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            {{-- Greeting --}}
            <div>
                <p class="text-xs font-medium uppercase tracking-widest text-amber-400/70">
                    Welcome back
                </p>
                <h2 class="mt-1 text-xl font-bold text-white">
                    {{ $userName }}
                </h2>
                <span class="mt-2 inline-flex items-center gap-1.5 rounded-full border border-amber-400/20
                             bg-amber-500/10 px-3 py-0.5 text-xs font-medium text-amber-300">
                    <x-heroicon-m-star class="h-3 w-3" />
                    {{ Str::of($userRole)->replace('_', ' ')->title() }}
                </span>
            </div>

            {{-- Quick Stats --}}
            <div class="flex gap-6">
                <div class="text-center">
                    <p class="text-2xl font-bold text-white">{{ $totalArticles }}</p>
                    <p class="text-xs text-white/50">Total</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-amber-400">{{ $pendingArticles }}</p>
                    <p class="text-xs text-white/50">Pending</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-emerald-400">{{ $publishedArticles }}</p>
                    <p class="text-xs text-white/50">Published</p>
                </div>
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
