@extends('layouts.public')

@section('meta_title', $page->meta_title ?: $page->title)
@section('meta_description', $page->meta_description ?: \Illuminate\Support\Str::limit(strip_tags($page->content), 160))
@section('meta_robots', $page->meta_robots ?: 'noindex, nofollow')

@section('content')
@if($page->template === 'verification')
    <section class="rb-section" style="min-height: 55vh; display: flex; align-items: center; justify-content: center; padding: 4rem 1.5rem 6rem;">
        <div style="max-width: 44rem; width: 100%; margin: 0 auto; text-align: center; background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 1.25rem; padding: 3rem 2rem; box-shadow: 0 8px 32px rgba(0, 0, 0, 0.37); backdrop-filter: blur(12px);">
            
            <div style="display: inline-flex; align-items: center; justify-content: center; width: 4.25rem; height: 4.25rem; border-radius: 50%; background: rgba(56, 189, 248, 0.1); border: 1px solid rgba(56, 189, 248, 0.25); color: #38BDF8; margin-bottom: 1.5rem;">
                <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    <path d="m9 12 2 2 4-4"/>
                </svg>
            </div>

            <div class="rb-custom-page-content" style="font-size: clamp(1.1rem, 2.5vw, 1.35rem); line-height: 1.8; color: #F1F5F9; font-weight: 500; margin: 0;">
                {!! $page->content !!}
            </div>

        </div>
    </section>
@else
    <section class="rb-section" style="min-height: 60vh; padding: 3rem 1.5rem 6rem;">
        <div style="max-width: 52rem; width: 100%; margin: 0 auto;">
            
            <h1 style="font-size: clamp(2rem, 4vw, 2.75rem); font-weight: 800; letter-spacing: -0.03em; color: #F8FAFC; margin-bottom: 2rem;">
                {{ $page->title }}
            </h1>

            <div class="prose prose-invert rb-custom-page-content" style="color: #CBD5E1; line-height: 1.8; font-size: 1.05rem;">
                {!! $page->content !!}
            </div>

        </div>
    </section>
@endif
@endsection
