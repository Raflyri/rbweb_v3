@extends('layouts.public')

@section('meta_title', 'RBeverything Arcade — Play Web Games')
@section('meta_description', 'Mainkan berbagai koleksi game web interaktif langsung dari browser di portal RBeverything Arcade.')

@section('content')
<section class="rb-section" style="padding-top:2rem;padding-bottom:5rem;">
    <div style="margin-bottom:3rem;">
        <span class="rb-section-label">Arcade Portal</span>
        <h1 class="rb-section-title" style="margin-bottom:0.75rem;">RBeverything Arcade</h1>
        <p style="font-size:1.05rem;color:var(--color-muted);max-width:38rem;line-height:1.75;margin:0;">
            Koleksi game web interaktif yang siap dimainkan langsung dari browser tanpa instalasi.
        </p>
    </div>

    @if($games->count() > 0)
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(18rem,1fr));gap:1.75rem;">
            @foreach($games as $game)
                <article class="rb-card" style="display:flex;flex-direction:column;overflow:hidden;border-radius:1rem;background:rgba(255,255,255,0.025);border:1px solid var(--color-border);">
                    <a href="{{ route('arcade.play', $game->slug) }}" style="display:block;aspect-ratio:16/9;overflow:hidden;background:rgba(255,255,255,0.03);position:relative;">
                        @if($game->thumbnail)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($game->thumbnail) }}"
                                 alt="{{ $game->title }}"
                                 style="width:100%;height:100%;object-fit:cover;display:block;"
                                 loading="lazy">
                        @else
                            <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:var(--rb-red);">
                                <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="6" y1="12" x2="10" y2="12"/><line x1="8" y1="10" x2="8" y2="14"/>
                                    <line x1="15" y1="13" x2="15.01" y2="13"/><line x1="18" y1="11" x2="18.01" y2="11"/>
                                    <rect x="2" y="6" width="20" height="12" rx="2"/>
                                </svg>
                            </div>
                        @endif
                    </a>

                    <div style="padding:1.25rem;display:flex;flex-direction:column;gap:0.75rem;flex:1;">
                        <h2 style="font-size:1.15rem;font-weight:800;color:#F1F5F9;margin:0;">
                            <a href="{{ route('arcade.play', $game->slug) }}" style="color:inherit;text-decoration:none;">
                                {{ $game->title }}
                            </a>
                        </h2>

                        @if($game->description)
                            <p style="font-size:0.875rem;color:var(--color-muted);line-height:1.65;margin:0;flex:1;">
                                {{ \Illuminate\Support\Str::limit($game->description, 140) }}
                            </p>
                        @endif

                        <div style="margin-top:auto;padding-top:0.75rem;border-top:1px solid var(--color-border);display:flex;justify-content:flex-end;">
                            <a href="{{ route('arcade.play', $game->slug) }}" class="rb-btn-primary" style="text-decoration:none;">
                                Mainkan Sekarang
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <polygon points="5 3 19 12 5 21 5 3"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @else
        <div style="text-align:center;padding:4rem 1.5rem;border:1px dashed var(--color-border);border-radius:1.25rem;background:rgba(255,255,255,0.015);">
            <h3 style="font-size:1.25rem;font-weight:800;color:#F1F5F9;margin:0 0 0.5rem;">Belum ada game yang ditayangkan</h3>
            <p style="font-size:0.95rem;color:var(--color-muted);margin:0;">Silakan kembali lagi nanti untuk memainkan koleksi game terbaru kami.</p>
        </div>
    @endif
</section>
@endsection
