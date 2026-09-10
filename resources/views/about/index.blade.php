@extends('layouts.public')

{{-- ════════════════════════════════════════════════════════════
     SEO — About Us
════════════════════════════════════════════════════════════ --}}
@section('meta_title',       $content['hero_title'])
@section('meta_description', Str::limit(strip_tags($content['hero_subtitle']), 160))
@section('og_type',          'website')
@section('og_title',         $content['hero_title'] . ' — ' . $siteName)
@section('og_description',   Str::limit(strip_tags($content['hero_subtitle']), 160))
@section('canonical',        route('about'))
@section('nav_about_active', 'style="color:var(--color-text);"')

@section('content')

    {{-- ════════════════════════════════════════════════════════
         HERO SECTION
    ════════════════════════════════════════════════════════ --}}
    <section class="about-hero" aria-label="{{ $content['hero_title'] }}" style="padding-bottom:2.5rem;">
        <div class="rb-section" style="max-width:68rem;">

            {{-- Breadcrumb --}}
            <nav class="breadcrumb" aria-label="Breadcrumb" style="display:flex;align-items:center;gap:0.5rem;font-size:0.8rem;color:var(--color-muted);margin-bottom:2rem;">
                <a href="{{ route('home') }}" style="color:var(--color-muted);text-decoration:none;transition:color 0.2s;" onmouseover="this.style.color='#F1F5F9'" onmouseout="this.style.color='var(--color-muted)'">{{ __('nav.home') }}</a>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
                <span style="color:#F1F5F9;font-weight:600;">{{ __('nav.about') }}</span>
            </nav>

            {{-- Hero Header --}}
            <div style="text-align:center;max-width:54rem;margin:0 auto 4rem;">
                <span class="rb-section-label" style="margin-bottom:1rem;display:inline-block;">{{ $content['hero_badge'] }}</span>
                <h1 class="rb-section-title" style="font-size:clamp(2.2rem, 5vw, 3.4rem);font-weight:900;letter-spacing:-0.03em;line-height:1.15;margin-bottom:1.5rem;color:#F8FAFC;">
                    {{ $content['hero_title'] }}
                </h1>
                <p style="font-size:clamp(1rem, 2vw, 1.18rem);color:#94A3B8;line-height:1.8;max-width:44rem;margin:0 auto;">
                    {{ $content['hero_subtitle'] }}
                </p>
            </div>

            {{-- ════════════════════════════════════════════════════
                 STATS SHOWCASE
            ════════════════════════════════════════════════════ --}}
            @if(!empty($content['stats']))
            <div style="margin-bottom:5rem;">
                <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:1.5rem;border:1px solid rgba(255,255,255,0.08);border-radius:1.25rem;padding:2.5rem 2rem;background:rgba(255,255,255,0.02);backdrop-filter:blur(12px);box-shadow:0 8px 32px rgba(0,0,0,0.37);">
                    @foreach($content['stats'] as $idx => $stat)
                    <div style="text-align:center;padding:0.75rem 1rem;">
                        <div style="font-size:clamp(2.4rem, 4vw, 3rem);font-weight:900;letter-spacing:-0.04em;line-height:1;background:linear-gradient(135deg,#38BDF8,#818CF8);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;font-family:var(--font-mono, monospace);">
                            {{ $stat['value'] ?? '' }}
                        </div>
                        <div style="font-size:0.825rem;color:#94A3B8;margin-top:0.6rem;font-weight:500;letter-spacing:0.02em;">
                            {{ $stat['label_' . $locale] ?? $stat['label_id'] ?? $stat['label_en'] ?? '' }}
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- ════════════════════════════════════════════════════
                 STORY & PHILOSOPHY
            ════════════════════════════════════════════════════ --}}
            @if(!empty($content['story_title']) || !empty($content['story_content']))
            <div style="margin-bottom:5rem;">
                <div style="border:1px solid rgba(255,255,255,0.07);border-radius:1.5rem;padding:clamp(2rem, 5vw, 3.5rem);background:linear-gradient(180deg, rgba(255,255,255,0.03) 0%, rgba(255,255,255,0.01) 100%);box-shadow:0 12px 40px rgba(0,0,0,0.4);">
                    <div style="display:inline-flex;align-items:center;gap:0.5rem;margin-bottom:1.25rem;">
                        <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#38BDF8;box-shadow:0 0 10px #38BDF8;"></span>
                        <span style="font-size:0.75rem;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:#38BDF8;">Filosofi & Dedikasi</span>
                    </div>
                    <h2 style="font-size:clamp(1.6rem, 3.5vw, 2.2rem);font-weight:800;letter-spacing:-0.03em;color:#F1F5F9;margin-bottom:1.75rem;line-height:1.25;">
                        {{ $content['story_title'] }}
                    </h2>
                    <div style="font-size:1.05rem;color:#94A3B8;line-height:1.85;" class="about-story-prose">
                        {!! $content['story_content'] !!}
                    </div>
                </div>
            </div>
            @endif

            {{-- ════════════════════════════════════════════════════
                 VISION & MISSION
            ════════════════════════════════════════════════════ --}}
            @if(!empty($content['vision']) || !empty($missionList))
            <div style="margin-bottom:5rem;">
                <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(320px, 1fr));gap:2rem;">
                    
                    {{-- Vision Card --}}
                    @if(!empty($content['vision']))
                    <div style="border:1px solid rgba(56,189,248,0.15);border-radius:1.25rem;padding:2.5rem;background:radial-gradient(ellipse at top left, rgba(56,189,248,0.06), rgba(255,255,255,0.01));box-shadow:0 8px 30px rgba(0,0,0,0.3);position:relative;overflow:hidden;">
                        <div style="display:flex;align-items:center;gap:0.875rem;margin-bottom:1.25rem;">
                            <div style="width:42px;height:42px;border-radius:0.75rem;background:rgba(56,189,248,0.12);border:1px solid rgba(56,189,248,0.25);display:flex;align-items:center;justify-content:center;color:#38BDF8;">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </div>
                            <h3 style="font-size:1.35rem;font-weight:700;color:#F1F5F9;letter-spacing:-0.02em;">Visi Kami</h3>
                        </div>
                        <p style="font-size:1rem;color:#CBD5E1;line-height:1.75;">
                            {{ $content['vision'] }}
                        </p>
                    </div>
                    @endif

                    {{-- Mission Card --}}
                    @if(!empty($missionList))
                    <div style="border:1px solid rgba(129,140,248,0.15);border-radius:1.25rem;padding:2.5rem;background:radial-gradient(ellipse at top right, rgba(129,140,248,0.06), rgba(255,255,255,0.01));box-shadow:0 8px 30px rgba(0,0,0,0.3);position:relative;overflow:hidden;">
                        <div style="display:flex;align-items:center;gap:0.875rem;margin-bottom:1.25rem;">
                            <div style="width:42px;height:42px;border-radius:0.75rem;background:rgba(129,140,248,0.12);border:1px solid rgba(129,140,248,0.25);display:flex;align-items:center;justify-content:center;color:#818CF8;">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m12 15 2 2 4-4"/>
                                    <rect width="18" height="18" x="3" y="3" rx="2"/>
                                    <path d="M3 9h18"/>
                                </svg>
                            </div>
                            <h3 style="font-size:1.35rem;font-weight:700;color:#F1F5F9;letter-spacing:-0.02em;">Misi Kami</h3>
                        </div>
                        <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:0.875rem;">
                            @foreach($missionList as $mItem)
                            <li style="display:flex;align-items:flex-start;gap:0.75rem;font-size:0.95rem;color:#CBD5E1;line-height:1.6;">
                                <span style="display:inline-flex;align-items:center;justify-content:center;width:18px;height:18px;border-radius:50%;background:rgba(129,140,248,0.2);color:#818CF8;font-size:0.75rem;flex-shrink:0;margin-top:0.2rem;">✓</span>
                                <span>{{ preg_replace('/^\d+[\.\)]\s*/', '', $mItem) }}</span>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                </div>
            </div>
            @endif

            {{-- ════════════════════════════════════════════════════
                 CORE VALUES
            ════════════════════════════════════════════════════ --}}
            @if(!empty($content['core_values']))
            <div style="margin-bottom:5.5rem;">
                <div style="text-align:center;max-width:40rem;margin:0 auto 3rem;">
                    <span class="rb-section-label" style="margin-bottom:0.75rem;display:inline-block;">Prinsip Fundamental</span>
                    <h2 class="rb-section-title" style="font-size:clamp(1.8rem, 4vw, 2.5rem);font-weight:800;color:#F1F5F9;letter-spacing:-0.03em;">
                        Nilai-Nilai yang Memandu Kami
                    </h2>
                </div>

                <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));gap:1.5rem;">
                    @foreach($content['core_values'] as $idx => $val)
                    <div style="border:1px solid rgba(255,255,255,0.06);border-radius:1.25rem;padding:2rem;background:rgba(255,255,255,0.02);transition:all 0.3s ease;display:flex;flex-direction:column;gap:1rem;" onmouseover="this.style.borderColor='rgba(56,189,248,0.3)';this.style.transform='translateY(-4px)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.06)';this.style.transform='translateY(0)'">
                        <div style="display:flex;align-items:center;justify-content:space-between;">
                            <div style="width:40px;height:40px;border-radius:0.75rem;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);display:flex;align-items:center;justify-content:center;color:#38BDF8;">
                                @if(($val['icon'] ?? '') === 'cursor-arrow-rays')
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>
                                @elseif(($val['icon'] ?? '') === 'cube-transparent')
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.12 6.4-6.05-3.5a2 2 0 0 0-2.14 0L6.88 6.4a2 2 0 0 0-1 1.73v7a2 2 0 0 0 1 1.73l6.05 3.5a2 2 0 0 0 2.14 0l6.05-3.5a2 2 0 0 0 1-1.73v-7a2 2 0 0 0-1-1.73Z"/><path d="M12 22V12"/></svg>
                                @elseif(($val['icon'] ?? '') === 'users')
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                @elseif(($val['icon'] ?? '') === 'shield-check')
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg>
                                @else
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/></svg>
                                @endif
                            </div>
                            <span style="font-family:var(--font-mono, monospace);font-size:0.75rem;color:#475569;font-weight:700;">0{{ $idx + 1 }}</span>
                        </div>
                        <div>
                            <h4 style="font-size:1.15rem;font-weight:700;color:#F1F5F9;margin-bottom:0.5rem;letter-spacing:-0.01em;">
                                {{ $val['title_' . $locale] ?? $val['title_id'] ?? $val['title_en'] ?? '' }}
                            </h4>
                            <p style="font-size:0.9rem;color:#94A3B8;line-height:1.65;margin:0;">
                                {{ $val['desc_' . $locale] ?? $val['desc_id'] ?? $val['desc_en'] ?? '' }}
                            </p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- ════════════════════════════════════════════════════
                 CALL TO ACTION BANNER
            ════════════════════════════════════════════════════ --}}
            @if(!empty($content['cta_title']))
            <div style="margin-bottom:4rem;">
                <div style="border:1px solid rgba(56,189,248,0.2);border-radius:1.75rem;padding:clamp(2.5rem, 6vw, 4rem) 2rem;background:radial-gradient(ellipse at center, rgba(56,189,248,0.08) 0%, rgba(3,5,8,0.95) 75%);text-align:center;position:relative;overflow:hidden;box-shadow:0 12px 48px rgba(0,0,0,0.5);">
                    <div style="position:relative;z-index:2;max-width:44rem;margin:0 auto;">
                        <span class="rb-section-label" style="margin-bottom:1rem;display:inline-block;">Siap Berkolaborasi</span>
                        <h2 style="font-size:clamp(1.8rem, 4vw, 2.6rem);font-weight:800;color:#F8FAFC;letter-spacing:-0.03em;margin-bottom:1rem;line-height:1.2;">
                            {{ $content['cta_title'] }}
                        </h2>
                        @if(!empty($content['cta_subtitle']))
                        <p style="font-size:1.05rem;color:#94A3B8;line-height:1.75;margin-bottom:2.25rem;">
                            {{ $content['cta_subtitle'] }}
                        </p>
                        @endif
                        <div style="display:flex;align-items:center;justify-content:center;gap:1rem;flex-wrap:wrap;">
                            <a href="{{ $content['cta_button_url'] }}" class="rb-btn-primary" style="padding:0.875rem 2rem;font-size:1rem;text-decoration:none;display:inline-flex;align-items:center;gap:0.6rem;">
                                <span>{{ $content['cta_button_text'] }}</span>
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M5 12h14M12 5l7 7-7 7" />
                                </svg>
                            </a>
                            @if(!empty($generalSettings->whatsapp_number))
                            <a href="https://wa.me/{{ preg_replace('/\D/', '', $generalSettings->whatsapp_number) }}" target="_blank" rel="noopener" class="rb-btn-ghost" style="padding:0.875rem 1.75rem;font-size:1rem;text-decoration:none;display:inline-flex;align-items:center;gap:0.5rem;border-radius:9999px;border:1px solid rgba(255,255,255,0.12);background:rgba(255,255,255,0.03);color:#F1F5F9;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                                </svg>
                                <span>WhatsApp</span>
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </section>

@endsection

@section('styles')
<style>
    .about-story-prose p {
        margin-bottom: 1.25rem;
    }
    .about-story-prose p:last-child {
        margin-bottom: 0;
    }
    .about-story-prose strong {
        color: #F8FAFC;
        font-weight: 700;
    }
</style>
@endsection
