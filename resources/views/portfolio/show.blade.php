<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- ── SEO & Page Title ──────────────────────────────────────── --}}
    <title>{{ $profile->user->name }} — Portfolio | RBeverything</title>
    <meta name="description"
        content="{{ $profile->bio ? Str::limit($profile->bio, 160) : ($profile->headline ?? $profile->user->name . '\'s professional portfolio on RBeverything.') }}">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- ── Open Graph (rich social sharing) ─────────────────────── --}}
    <meta property="og:type"        content="profile">
    <meta property="og:title"       content="{{ $profile->user->name }} — Portfolio">
    <meta property="og:description" content="{{ $profile->headline ?? 'Professional digital portfolio on RBeverything.' }}">
    <meta property="og:url"         content="{{ url()->current() }}">
    @if($profile->avatar_url)
    <meta property="og:image"       content="{{ $profile->avatar_url }}">
    @endif
    <meta name="twitter:card"       content="summary_large_image">
    <meta name="twitter:title"      content="{{ $profile->user->name }} — Portfolio">
    <meta name="twitter:description" content="{{ $profile->headline ?? 'Professional digital portfolio on RBeverything.' }}">
    @if($profile->avatar_url)
    <meta name="twitter:image"      content="{{ $profile->avatar_url }}">
    @endif

    {{-- ── Fonts ───────────────────────────────────────────────── --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,300;0,14..32,400;0,14..32,500;0,14..32,600;0,14..32,700;0,14..32,800;0,14..32,900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body style="background-color:#030508;color:#F1F5F9;font-family:'Inter',system-ui,sans-serif;-webkit-font-smoothing:antialiased;overflow-x:hidden;">

    {{-- ══════════════════════════════════════════════════════════ --}}
    {{-- BACKGROUND                                                --}}
    {{-- ══════════════════════════════════════════════════════════ --}}
    <div aria-hidden="true" style="position:fixed;inset:0;z-index:-1;pointer-events:none;">
        <div style="position:absolute;inset:0;background:radial-gradient(ellipse 80% 55% at 50% -5%,rgba(14,165,233,0.10) 0%,transparent 70%),radial-gradient(ellipse 60% 40% at 85% 110%,rgba(99,102,241,0.08) 0%,transparent 60%),#030508;"></div>
        <div style="position:absolute;inset:0;background-image:radial-gradient(circle,rgba(255,255,255,0.035) 1px,transparent 1px);background-size:32px 32px;mask-image:radial-gradient(ellipse 100% 100% at center,black 20%,transparent 80%);"></div>
    </div>

    {{-- ══════════════════════════════════════════════════════════ --}}
    {{-- STICKY NAVIGATION                                         --}}
    {{-- ══════════════════════════════════════════════════════════ --}}
    <nav class="glass-nav" id="pf-nav" role="navigation" aria-label="Portfolio navigation"
         style="position:sticky;top:0;z-index:9000;padding:0.875rem 0;">
        <div style="max-width:68rem;margin:0 auto;padding:0 1.5rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;">
            {{-- Back to home --}}
            <a href="/" aria-label="Back to RBeverything"
               style="font-size:0.85rem;font-weight:600;color:#64748B;text-decoration:none;display:flex;align-items:center;gap:0.35rem;transition:color 0.25s;"
               onmouseover="this.style.color='#38BDF8'" onmouseout="this.style.color='#64748B'">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
                RBeverything
            </a>

            {{-- Section anchors (desktop) --}}
            <div id="pf-nav-links" style="display:flex;align-items:center;gap:1.5rem;flex-wrap:wrap;">
                @if($profile->user->experiences->isNotEmpty())
                <a href="#experience" style="font-size:0.8rem;font-weight:500;color:#64748B;text-decoration:none;transition:color 0.25s;" onmouseover="this.style.color='#F1F5F9'" onmouseout="this.style.color='#64748B'">Experience</a>
                @endif
                @if($profile->user->education->isNotEmpty())
                <a href="#education" style="font-size:0.8rem;font-weight:500;color:#64748B;text-decoration:none;transition:color 0.25s;" onmouseover="this.style.color='#F1F5F9'" onmouseout="this.style.color='#64748B'">Education</a>
                @endif
                @if($profile->user->skills->isNotEmpty())
                <a href="#skills" style="font-size:0.8rem;font-weight:500;color:#64748B;text-decoration:none;transition:color 0.25s;" onmouseover="this.style.color='#F1F5F9'" onmouseout="this.style.color='#64748B'">Skills</a>
                @endif
                @if($profile->user->achievements->isNotEmpty())
                <a href="#achievements" style="font-size:0.8rem;font-weight:500;color:#64748B;text-decoration:none;transition:color 0.25s;" onmouseover="this.style.color='#F1F5F9'" onmouseout="this.style.color='#64748B'">Achievements</a>
                @endif
                @if($profile->user->posts->isNotEmpty())
                <a href="#publications" style="font-size:0.8rem;font-weight:500;color:#64748B;text-decoration:none;transition:color 0.25s;" onmouseover="this.style.color='#F1F5F9'" onmouseout="this.style.color='#64748B'">Publications</a>
                @endif
            </div>

            {{-- Slug pill --}}
            <span style="font-size:0.75rem;font-weight:600;color:#38BDF8;background:rgba(56,189,248,0.08);border:1px solid rgba(56,189,248,0.2);padding:0.3rem 0.85rem;border-radius:9999px;font-family:'JetBrains Mono',monospace;white-space:nowrap;">
                @{{ $profile->custom_url_slug }}
            </span>
        </div>
    </nav>

    <main style="max-width:68rem;margin:0 auto;padding:3.5rem 1.5rem 6rem;">

        {{-- ══════════════════════════════════════════════════════ --}}
        {{-- HERO                                                   --}}
        {{-- ══════════════════════════════════════════════════════ --}}
        <header class="pf-fade-up" style="text-align:center;margin-bottom:4rem;" aria-label="Profile header">

            {{-- Avatar --}}
            <div style="display:flex;justify-content:center;margin-bottom:1.75rem;">
                <div class="pf-avatar-ring">
                    @if($profile->avatar_url)
                        <img src="{{ $profile->avatar_url }}"
                             alt="{{ $profile->user->name }}"
                             class="pf-avatar-inner"
                             loading="eager">
                    @else
                        <div class="pf-avatar-initials" aria-label="{{ substr($profile->user->name, 0, 1) }}">
                            {{ strtoupper(substr($profile->user->name, 0, 1)) }}
                        </div>
                    @endif
                </div>
            </div>

            {{-- Name --}}
            <h1 style="font-size:clamp(2.2rem,6vw,4rem);font-weight:900;letter-spacing:-0.035em;line-height:1.1;margin-bottom:0.6rem;color:#F1F5F9;">
                {{ $profile->user->name }}
            </h1>

            {{-- Headline --}}
            @if($profile->headline)
            <p class="pf-fade-up-d1" style="font-size:1.1rem;font-weight:600;color:#38BDF8;margin-bottom:1rem;letter-spacing:-0.01em;">
                {{ $profile->headline }}
            </p>
            @endif

            {{-- Bio --}}
            @if($profile->bio)
            <p class="pf-fade-up-d2" style="font-size:1rem;color:#64748B;max-width:48rem;margin:0 auto;line-height:1.8;font-weight:300;">
                {{ $profile->bio }}
            </p>
            @endif

            {{-- Quick stat pills --}}
            <div class="pf-fade-up-d3" style="display:flex;flex-wrap:wrap;gap:0.625rem;justify-content:center;margin-top:1.75rem;">
                @if($profile->user->experiences->isNotEmpty())
                <span style="font-size:0.75rem;font-weight:600;color:#94A3B8;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);padding:0.3rem 0.875rem;border-radius:9999px;">
                    {{ $profile->user->experiences->count() }} {{ Str::plural('Experience', $profile->user->experiences->count()) }}
                </span>
                @endif
                @if($profile->user->education->isNotEmpty())
                <span style="font-size:0.75rem;font-weight:600;color:#94A3B8;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);padding:0.3rem 0.875rem;border-radius:9999px;">
                    {{ $profile->user->education->count() }} {{ Str::plural('Qualification', $profile->user->education->count()) }}
                </span>
                @endif
                @if($profile->user->skills->isNotEmpty())
                <span style="font-size:0.75rem;font-weight:600;color:#94A3B8;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);padding:0.3rem 0.875rem;border-radius:9999px;">
                    {{ $profile->user->skills->count() }} Skills
                </span>
                @endif
                @if($profile->user->achievements->isNotEmpty())
                <span style="font-size:0.75rem;font-weight:600;color:#94A3B8;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);padding:0.3rem 0.875rem;border-radius:9999px;">
                    {{ $profile->user->achievements->count() }} {{ Str::plural('Achievement', $profile->user->achievements->count()) }}
                </span>
                @endif
            </div>
        </header>

        {{-- ══════════════════════════════════════════════════════ --}}
        {{-- 2-COLUMN LAYOUT                                        --}}
        {{-- ══════════════════════════════════════════════════════ --}}
        <div style="display:grid;grid-template-columns:1fr;gap:2rem;"
             id="pf-content-grid">

            {{-- ── MAIN COLUMN ──────────────────────────────────── --}}
            <div style="display:flex;flex-direction:column;gap:2rem;">

                {{-- EXPERIENCE ───────────────────────────────────── --}}
                @if($profile->user->experiences->isNotEmpty())
                <section id="experience" class="glass-card pf-fade-up-d1" style="padding:2rem 2.25rem;border-radius:1.5rem;">
                    <h2 style="font-size:1.125rem;font-weight:800;letter-spacing:-0.02em;margin-bottom:1.75rem;display:flex;align-items:center;gap:0.75rem;">
                        <span class="pf-section-icon" style="background:rgba(56,189,248,0.1);color:#38BDF8;" aria-hidden="true">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/>
                            </svg>
                        </span>
                        Experience
                    </h2>
                    <div style="display:flex;flex-direction:column;gap:2rem;">
                        @foreach($profile->user->experiences as $exp)
                        <div class="pf-timeline-item">
                            <div style="display:flex;flex-wrap:wrap;align-items:baseline;justify-content:space-between;gap:0.5rem;margin-bottom:0.25rem;">
                                <h3 style="font-size:1rem;font-weight:700;color:#F1F5F9;letter-spacing:-0.01em;">{{ $exp->role }}</h3>
                                <span style="font-size:0.7rem;font-family:'JetBrains Mono',monospace;color:#475569;flex-shrink:0;">
                                    {{ $exp->start_date?->format('M Y') }} — {{ $exp->end_date ? $exp->end_date->format('M Y') : 'Present' }}
                                </span>
                            </div>
                            <p style="font-size:0.85rem;font-weight:600;color:#38BDF8;margin-bottom:0.6rem;">{{ $exp->company }}</p>
                            @if($exp->description)
                            <p style="font-size:0.875rem;color:#64748B;line-height:1.75;">{{ $exp->description }}</p>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </section>
                @endif

                {{-- EDUCATION ────────────────────────────────────── --}}
                @if($profile->user->education->isNotEmpty())
                <section id="education" class="glass-card pf-fade-up-d2" style="padding:2rem 2.25rem;border-radius:1.5rem;">
                    <h2 style="font-size:1.125rem;font-weight:800;letter-spacing:-0.02em;margin-bottom:1.75rem;display:flex;align-items:center;gap:0.75rem;">
                        <span class="pf-section-icon" style="background:rgba(167,139,250,0.1);color:#A78BFA;" aria-hidden="true">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>
                            </svg>
                        </span>
                        Education
                    </h2>
                    <div style="display:flex;flex-direction:column;gap:2rem;">
                        @foreach($profile->user->education as $edu)
                        <div class="pf-timeline-item" style="border-left-color:rgba(167,139,250,0.2);">
                            <div style="display:flex;flex-wrap:wrap;align-items:baseline;justify-content:space-between;gap:0.5rem;margin-bottom:0.25rem;">
                                <h3 style="font-size:1rem;font-weight:700;color:#F1F5F9;letter-spacing:-0.01em;">{{ $edu->degree }}</h3>
                                <span style="font-size:0.7rem;font-family:'JetBrains Mono',monospace;color:#475569;flex-shrink:0;">
                                    {{ $edu->start_date?->format('Y') }} — {{ $edu->end_date ? $edu->end_date->format('Y') : 'Present' }}
                                </span>
                            </div>
                            <p style="font-size:0.85rem;font-weight:600;color:#A78BFA;">{{ $edu->institution }}</p>
                        </div>
                        @endforeach
                    </div>
                </section>
                @endif

                {{-- PUBLICATIONS ─────────────────────────────────── --}}
                @if($profile->user->posts->isNotEmpty())
                <section id="publications" class="glass-card pf-fade-up-d3" style="padding:2rem 2.25rem;border-radius:1.5rem;">
                    <h2 style="font-size:1.125rem;font-weight:800;letter-spacing:-0.02em;margin-bottom:1.75rem;display:flex;align-items:center;gap:0.75rem;">
                        <span class="pf-section-icon" style="background:rgba(52,211,153,0.1);color:#34D399;" aria-hidden="true">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/>
                            </svg>
                        </span>
                        Publications
                    </h2>
                    <div style="display:flex;flex-direction:column;gap:0.875rem;">
                        @foreach($profile->user->posts as $post)
                        <article class="pf-pub-card">
                            <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;margin-bottom:0.5rem;flex-wrap:wrap;">
                                @if($post->type)
                                <span style="font-size:0.65rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:#34D399;background:rgba(52,211,153,0.08);border:1px solid rgba(52,211,153,0.18);padding:0.2rem 0.6rem;border-radius:9999px;">{{ $post->type }}</span>
                                @endif
                                @if($post->published_at)
                                <span style="font-size:0.7rem;color:#475569;font-family:'JetBrains Mono',monospace;">{{ $post->published_at->format('M j, Y') }}</span>
                                @endif
                            </div>
                            <h3 style="font-size:0.975rem;font-weight:700;color:#F1F5F9;letter-spacing:-0.01em;line-height:1.4;margin-bottom:0.5rem;">{{ $post->title }}</h3>
                            @if($post->content)
                            <p style="font-size:0.85rem;color:#64748B;line-height:1.7;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                                {{ strip_tags($post->content) }}
                            </p>
                            @endif
                        </article>
                        @endforeach
                    </div>
                </section>
                @endif

            </div>{{-- / main column --}}

            {{-- ── SIDEBAR ──────────────────────────────────────── --}}
            <div style="display:flex;flex-direction:column;gap:2rem;">

                {{-- SKILLS ──────────────────────────────────────── --}}
                @if($profile->user->skills->isNotEmpty())
                <section id="skills" class="glass-card pf-fade-up-d1" style="padding:1.75rem 2rem;border-radius:1.5rem;">
                    <h2 style="font-size:1.125rem;font-weight:800;letter-spacing:-0.02em;margin-bottom:1.25rem;display:flex;align-items:center;gap:0.75rem;">
                        <span class="pf-section-icon" style="background:rgba(251,113,133,0.1);color:#FB7185;" aria-hidden="true">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z"/>
                            </svg>
                        </span>
                        Skills
                    </h2>
                    <div style="display:flex;flex-wrap:wrap;gap:0.5rem;">
                        @foreach($profile->user->skills as $skill)
                        <span class="pf-skill-badge">{{ $skill->skill_name }}</span>
                        @endforeach
                    </div>
                </section>
                @endif

                {{-- ACHIEVEMENTS ─────────────────────────────────── --}}
                @if($profile->user->achievements->isNotEmpty())
                <section id="achievements" class="glass-card pf-fade-up-d2" style="padding:1.75rem 2rem;border-radius:1.5rem;">
                    <h2 style="font-size:1.125rem;font-weight:800;letter-spacing:-0.02em;margin-bottom:1.25rem;display:flex;align-items:center;gap:0.75rem;">
                        <span class="pf-section-icon" style="background:rgba(251,191,36,0.1);color:#FBBF24;" aria-hidden="true">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="8" r="6"/><path d="M15.477 12.89L17 22l-5-3-5 3 1.523-9.11"/>
                            </svg>
                        </span>
                        Achievements
                    </h2>
                    <div style="display:flex;flex-direction:column;gap:1.25rem;">
                        @foreach($profile->user->achievements as $achievement)
                        <div style="padding-bottom:1.25rem;border-bottom:1px solid rgba(255,255,255,0.05);" class="last:pb-0 last:border-0">
                            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:0.5rem;margin-bottom:0.3rem;">
                                <h3 style="font-size:0.9rem;font-weight:700;color:#F1F5F9;letter-spacing:-0.01em;line-height:1.35;">{{ $achievement->title }}</h3>
                                @if($achievement->date)
                                <span style="font-size:0.68rem;color:#475569;font-family:'JetBrains Mono',monospace;flex-shrink:0;margin-top:0.1rem;">{{ $achievement->date->format('M Y') }}</span>
                                @endif
                            </div>
                            @if($achievement->description)
                            <p style="font-size:0.8rem;color:#64748B;line-height:1.65;">{{ $achievement->description }}</p>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </section>
                @endif

                {{-- ABOUT ME (quick facts) ───────────────────────── --}}
                <section class="glass-card pf-fade-up-d3" style="padding:1.75rem 2rem;border-radius:1.5rem;">
                    <h2 style="font-size:1.125rem;font-weight:800;letter-spacing:-0.02em;margin-bottom:1.25rem;display:flex;align-items:center;gap:0.75rem;">
                        <span class="pf-section-icon" style="background:rgba(129,140,248,0.1);color:#818CF8;" aria-hidden="true">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>
                            </svg>
                        </span>
                        About
                    </h2>
                    <dl style="display:flex;flex-direction:column;gap:0.75rem;">
                        <div style="display:flex;justify-content:space-between;align-items:center;padding:0.5rem 0;border-bottom:1px solid rgba(255,255,255,0.04);">
                            <dt style="font-size:0.775rem;color:#475569;font-weight:500;">Profile</dt>
                            <dd style="font-size:0.775rem;color:#94A3B8;font-weight:600;font-family:'JetBrains Mono',monospace;">@{{ $profile->custom_url_slug }}</dd>
                        </div>
                        @if($profile->user->experiences->isNotEmpty())
                        <div style="display:flex;justify-content:space-between;align-items:center;padding:0.5rem 0;border-bottom:1px solid rgba(255,255,255,0.04);">
                            <dt style="font-size:0.775rem;color:#475569;font-weight:500;">Experience</dt>
                            <dd style="font-size:0.775rem;color:#94A3B8;font-weight:600;">{{ $profile->user->experiences->count() }} {{ Str::plural('role', $profile->user->experiences->count()) }}</dd>
                        </div>
                        @endif
                        @if($profile->user->education->isNotEmpty())
                        <div style="display:flex;justify-content:space-between;align-items:center;padding:0.5rem 0;border-bottom:1px solid rgba(255,255,255,0.04);">
                            <dt style="font-size:0.775rem;color:#475569;font-weight:500;">Education</dt>
                            <dd style="font-size:0.775rem;color:#94A3B8;font-weight:600;">{{ $profile->user->education->count() }} {{ Str::plural('degree', $profile->user->education->count()) }}</dd>
                        </div>
                        @endif
                        @if($profile->user->skills->isNotEmpty())
                        <div style="display:flex;justify-content:space-between;align-items:center;padding:0.5rem 0;border-bottom:1px solid rgba(255,255,255,0.04);">
                            <dt style="font-size:0.775rem;color:#475569;font-weight:500;">Skills</dt>
                            <dd style="font-size:0.775rem;color:#94A3B8;font-weight:600;">{{ $profile->user->skills->count() }} listed</dd>
                        </div>
                        @endif
                        @if($profile->user->achievements->isNotEmpty())
                        <div style="display:flex;justify-content:space-between;align-items:center;padding:0.5rem 0;">
                            <dt style="font-size:0.775rem;color:#475569;font-weight:500;">Achievements</dt>
                            <dd style="font-size:0.775rem;color:#94A3B8;font-weight:600;">{{ $profile->user->achievements->count() }} earned</dd>
                        </div>
                        @endif
                    </dl>
                </section>

            </div>{{-- / sidebar --}}

        </div>{{-- / 2-column grid --}}

    </main>

    {{-- ══════════════════════════════════════════════════════════ --}}
    {{-- FOOTER                                                    --}}
    {{-- ══════════════════════════════════════════════════════════ --}}
    <footer style="border-top:1px solid rgba(255,255,255,0.05);padding:2.5rem 1.5rem;text-align:center;">
        <div style="max-width:68rem;margin:0 auto;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:1.5rem;">
            <p style="font-size:0.8rem;color:#334155;">
                Portfolio powered by
                <a href="/" style="color:#38BDF8;text-decoration:none;font-weight:600;">RBeverything</a>
                — Build yours at
                <a href="/client-area/register" style="color:#818CF8;text-decoration:none;font-weight:600;">/client-area</a>
            </p>
            <p style="font-size:0.75rem;color:#1E293B;">
                © {{ date('Y') }} RBeverything. All rights reserved.
            </p>
        </div>
    </footer>

    {{-- ══════════════════════════════════════════════════════════ --}}
    {{-- FLOATING SHARE BUTTON                                     --}}
    {{-- ══════════════════════════════════════════════════════════ --}}
    <button class="pf-share-btn" id="pf-share-btn" aria-label="Copy portfolio link to clipboard" title="Copy link">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/>
            <polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/>
        </svg>
        <span id="pf-share-label">Share</span>
    </button>

    {{-- ══════════════════════════════════════════════════════════ --}}
    {{-- INLINE SCRIPTS                                            --}}
    {{-- ══════════════════════════════════════════════════════════ --}}
    <script>
    (function () {
        'use strict';

        /* ── Responsive 2-column grid ─────────────────────────── */
        function updateGrid() {
            var grid = document.getElementById('pf-content-grid');
            if (!grid) return;
            if (window.innerWidth >= 900) {
                grid.style.gridTemplateColumns = '2fr 1fr';
            } else {
                grid.style.gridTemplateColumns = '1fr';
            }
        }
        updateGrid();
        window.addEventListener('resize', updateGrid);

        /* ── Share / Copy link ────────────────────────────────── */
        var shareBtn   = document.getElementById('pf-share-btn');
        var shareLabel = document.getElementById('pf-share-label');

        if (shareBtn) {
            shareBtn.addEventListener('click', function () {
                var url = '{{ url()->current() }}';
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(url).then(function () {
                        shareLabel.textContent = 'Copied!';
                        shareBtn.classList.add('copied');
                        setTimeout(function () {
                            shareLabel.textContent = 'Share';
                            shareBtn.classList.remove('copied');
                        }, 2500);
                    });
                }
            });
        }

        /* ── Active nav highlight on scroll ──────────────────── */
        var sections = document.querySelectorAll('section[id]');
        var navLinks = document.querySelectorAll('#pf-nav-links a');

        function onScroll() {
            var scrollY = window.scrollY + 120;
            sections.forEach(function (sec) {
                if (scrollY >= sec.offsetTop && scrollY < sec.offsetTop + sec.offsetHeight) {
                    navLinks.forEach(function (link) {
                        link.style.color = link.getAttribute('href') === '#' + sec.id ? '#F1F5F9' : '#64748B';
                    });
                }
            });
        }
        window.addEventListener('scroll', onScroll, { passive: true });
    })();
    </script>

</body>
</html>