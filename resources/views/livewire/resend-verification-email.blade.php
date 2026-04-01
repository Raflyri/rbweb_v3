{{--
    Resend Verification Email — Livewire Component View
    Rendered inside the Client Area via the WelcomeBannerWidget or a Render Hook.
    Hidden completely when the user is already verified.
--}}

@php
    /** @var \App\Models\User $user */
    $user = auth()->user();
    $isVerified = $user?->hasVerifiedEmail() ?? true;
@endphp

@unless($isVerified)
<div x-data="{ show: true }" x-show="show" wire:poll.1000ms="tick"
     class="relative w-full rounded-xl border border-amber-400/40
            bg-linear-to-r from-amber-950/70 via-amber-900/40 to-amber-950/70
            px-4 py-3 backdrop-blur-sm shadow-md shadow-amber-900/10
            ring-1 ring-amber-500/20 mb-4">

    <!-- Dismiss button -->
    <button @click="show = false" type="button" class="absolute top-2 right-2 flex items-center justify-center rounded-full p-1 text-amber-400/50 hover:bg-amber-500/10 hover:text-amber-300 transition" aria-label="Dismiss">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>

    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between mr-6">

        {{-- Left: icon + message --}}
        <div class="flex items-start gap-3">
            {{-- Warning icon --}}
            <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center
                         rounded-full bg-amber-500/20 ring-1 ring-amber-400/30">
                <svg class="h-3.5 w-3.5 text-amber-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd"
                          d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673
                             1.167-.17 2.625-1.516 2.625H3.72c-1.347
                             0-2.189-1.458-1.515-2.625L8.485 2.495zM10
                             5a.75.75 0 01.75.75v3.5a.75.75 0
                             01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z"
                          clip-rule="evenodd"/>
                </svg>
            </span>

            <div>
                <p class="text-sm font-semibold text-amber-300 leading-tight">
                    Akun Anda belum diverifikasi
                </p>
                <p class="mt-0.5 text-xs text-amber-200/70">
                    Cek kotak masuk email Anda. Tautan berlaku <strong>5 menit</strong>.
                </p>

                {{-- Success message --}}
                @if($sent)
                    <p class="mt-1 flex items-center gap-1.5 text-xs font-medium text-emerald-400">
                        <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                  d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0
                                     01-1.127.075l-4.5-4.5a.75.75 0
                                     011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z"
                                  clip-rule="evenodd"/>
                        </svg>
                        Email berhasil dikirim.
                    </p>
                @endif

                {{-- Error message --}}
                @if($errorMessage)
                    <p class="mt-1 text-xs font-medium text-red-400">
                        ⚠ {{ $errorMessage }}
                    </p>
                @endif
            </div>
        </div>

        {{-- Right: resend button --}}
        <div class="shrink-0 pt-2 sm:pt-0">
            @if($cooldownSeconds > 0)
                <button disabled
                        class="inline-flex cursor-not-allowed items-center gap-1.5 rounded-lg
                               border border-amber-400/20 bg-amber-500/10 px-3 py-1.5 text-xs
                               font-medium text-amber-400/50 transition">
                    {{-- Spinner --}}
                    <svg class="h-3 w-3 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor"
                              d="M4 12a8 8 0 018-8v8H4z"/>
                    </svg>
                    Kirim ulang ({{ $cooldownSeconds }}d)
                </button>
            @else
                <button wire:click="resend"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-amber-400/30
                               bg-amber-500/20 px-3 py-1.5 text-xs font-semibold text-amber-300
                               shadow-sm transition hover:bg-amber-500/30 hover:text-amber-200
                               focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2
                               focus:ring-offset-transparent active:scale-95
                               disabled:cursor-not-allowed disabled:opacity-50">
                    {{-- Loading state --}}
                    <span wire:loading wire:target="resend">
                        <svg class="h-3 w-3 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor"
                                  d="M4 12a8 8 0 018-8v8H4z"/>
                        </svg>
                    </span>
                    {{-- Default state icon --}}
                    <span wire:loading.remove wire:target="resend">
                        <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M3 4a2 2 0 00-2 2v1.161l8.441 4.221a1.25 1.25 0
                                     001.118 0L19 7.162V6a2 2 0 00-2-2H3z"/>
                            <path d="M19 8.839l-7.77 3.885a2.75 2.75 0 01-2.46
                                     0L1 8.839V14a2 2 0 002 2h14a2 2 0 002-2V8.839z"/>
                        </svg>
                    </span>
                    Kirim Ulang
                </button>
            @endif
        </div>

    </div>
</div>
@endunless
