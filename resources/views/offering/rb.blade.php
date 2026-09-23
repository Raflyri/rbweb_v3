@extends('layouts.public')

@section('meta_title', 'Validasi Dokumen Penawaran Elektronik')
@section('meta_description', 'Dokumen penawaran ini telah divalidasi dan disetujui secara elektronik oleh Adistia Bianca Rizki - Principal Consultant RBEverything. Terima Kasih')
@section('meta_robots', 'noindex, nofollow')

@section('content')
<section class="rb-section" style="min-height: 55vh; display: flex; align-items: center; justify-content: center; padding: 4rem 1.5rem 6rem;">
    <div style="max-width: 44rem; width: 100%; margin: 0 auto; text-align: center; background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 1.25rem; padding: 3rem 2rem; box-shadow: 0 8px 32px rgba(0, 0, 0, 0.37); backdrop-filter: blur(12px);">
        
        <div style="display: inline-flex; align-items: center; justify-content: center; width: 4.25rem; height: 4.25rem; border-radius: 50%; background: rgba(56, 189, 248, 0.1); border: 1px solid rgba(56, 189, 248, 0.25); color: #38BDF8; margin-bottom: 1.5rem;">
            <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                <path d="m9 12 2 2 4-4"/>
            </svg>
        </div>

        <p style="font-size: clamp(1.1rem, 2.5vw, 1.35rem); line-height: 1.8; color: #F1F5F9; font-weight: 500; margin: 0;">
            Dokumen penawaran ini telah divalidasi dan disetujui secara elektronik oleh <strong style="color: #38BDF8; font-weight: 700;">Adistia Bianca Rizki</strong> - Principal Consultant RBEverything. Terima Kasih
        </p>

    </div>
</section>
@endsection
