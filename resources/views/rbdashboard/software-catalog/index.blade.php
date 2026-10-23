@extends('layouts.public')

@section('meta_title', 'Manajemen Software Catalog')

@section('content')
<div class="rb-section" style="padding-top:2rem;padding-bottom:5rem;max-width:76rem;margin:0 auto;">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;margin-bottom:2rem;flex-wrap:wrap;">
        <div>
            <span class="rb-section-label">RB Dashboard</span>
            <h1 class="rb-section-title" style="margin:0;">Software Catalog Hub</h1>
            <p style="margin:0.5rem 0 0;font-size:0.875rem;color:var(--color-muted);">
                Kelola daftar aplikasi yang ditampilkan pada portal <code>software-catalog.rbeverything.com</code>.
            </p>
        </div>
        <div style="display:flex;align-items:center;gap:0.75rem;">
            <a href="https://software-catalog.rbeverything.com" target="_blank" rel="noopener" class="rb-btn-secondary" style="text-decoration:none;font-size:0.875rem;padding:0.6rem 1rem;display:inline-flex;align-items:center;gap:0.5rem;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                Kunjungi Portal
            </a>
            <a href="{{ route('software-catalog.create') }}" class="rb-btn-primary" style="text-decoration:none;">
                + Tambah Software
            </a>
        </div>
    </div>

    @if(session('success'))
        <div style="padding:1rem 1.25rem;margin-bottom:1.5rem;border-radius:0.75rem;background:rgba(16,185,129,0.12);border:1px solid rgba(16,185,129,0.35);color:#34D399;font-size:0.9rem;">
            {{ session('success') }}
        </div>
    @endif

    <div style="background:rgba(255,255,255,0.025);border:1px solid var(--color-border);border-radius:1rem;overflow:hidden;">
        <table style="width:100%;border-collapse:collapse;text-align:left;font-size:0.9rem;">
            <thead>
                <tr style="border-bottom:1px solid var(--color-border);background:rgba(255,255,255,0.03);color:var(--color-muted);">
                    <th style="padding:1rem 1.25rem;">Software</th>
                    <th style="padding:1rem 1.25rem;">Kategori & Versi</th>
                    <th style="padding:1rem 1.25rem;">Tgl Rilis</th>
                    <th style="padding:1rem 1.25rem;">Tech Stack</th>
                    <th style="padding:1rem 1.25rem;">Live Demo URL</th>
                    <th style="padding:1rem 1.25rem;">Status</th>
                    <th style="padding:1rem 1.25rem;text-align:right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                    <tr style="border-bottom:1px solid rgba(255,255,255,0.05);">
                        <td style="padding:1rem 1.25rem;">
                            <div style="font-weight:700;color:#F1F5F9;font-size:0.95rem;">
                                {{ $item->title }}
                                @if($item->is_featured)
                                    <span style="margin-left:0.35rem;padding:0.15rem 0.45rem;border-radius:0.35rem;background:rgba(220,38,38,0.2);border:1px solid rgba(220,38,38,0.4);color:#FCA5A5;font-size:0.7rem;font-weight:700;">Featured</span>
                                @endif
                            </div>
                            <div style="color:var(--color-muted);font-family:'JetBrains Mono',monospace;font-size:0.75rem;margin-top:0.2rem;">{{ $item->slug }}</div>
                        </td>
                        <td style="padding:1rem 1.25rem;">
                            <span style="padding:0.2rem 0.6rem;border-radius:0.4rem;font-size:0.75rem;font-weight:600;background:rgba(56,189,248,0.12);color:#38BDF8;border:1px solid rgba(56,189,248,0.25);">
                                {{ $item->softwareCategory?->name ?? $item->category ?? 'Umum' }}
                            </span>
                            <span style="margin-left:0.35rem;padding:0.2rem 0.5rem;border-radius:0.4rem;font-size:0.75rem;background:rgba(255,255,255,0.05);color:#94A3B8;">
                                v{{ $item->version }}
                            </span>
                        </td>
                        <td style="padding:1rem 1.25rem;color:#CBD5E1;font-size:0.825rem;font-family:'JetBrains Mono',monospace;">
                            {{ $item->release_date ? $item->release_date->format('d M Y') : '-' }}
                        </td>
                        <td style="padding:1rem 1.25rem;">
                            @if(!empty($item->tech_stack))
                                <div style="display:flex;flex-wrap:wrap;gap:0.35rem;max-width:16rem;">
                                    @foreach(array_slice($item->tech_stack, 0, 3) as $tech)
                                        <span style="font-size:0.72rem;padding:0.15rem 0.45rem;border-radius:0.25rem;background:rgba(255,255,255,0.04);color:#CBD5E1;border:1px solid rgba(255,255,255,0.08);">{{ $tech }}</span>
                                    @endforeach
                                    @if(count($item->tech_stack) > 3)
                                        <span style="font-size:0.72rem;color:var(--color-muted);">+{{ count($item->tech_stack) - 3 }}</span>
                                    @endif
                                </div>
                            @else
                                <span style="color:var(--color-muted);font-size:0.8rem;">-</span>
                            @endif
                        </td>
                        <td style="padding:1rem 1.25rem;color:var(--color-muted);font-size:0.85rem;">
                            @if($item->demo_url)
                                <a href="{{ $item->demo_url }}" target="_blank" rel="noopener" style="color:#38BDF8;text-decoration:none;display:inline-flex;align-items:center;gap:0.3rem;">
                                    {{ \Illuminate\Support\Str::limit($item->demo_url, 28) }}
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                                </a>
                            @else
                                <span style="color:var(--color-muted);font-size:0.8rem;">Belum ada URL</span>
                            @endif
                        </td>
                        <td style="padding:1rem 1.25rem;">
                            <span style="padding:0.25rem 0.65rem;border-radius:999px;font-size:0.75rem;font-weight:700;background:{{ $item->status === 'Tayangkan' ? 'rgba(16,185,129,0.12)' : 'rgba(148,163,184,0.12)' }};color:{{ $item->status === 'Tayangkan' ? '#34D399' : '#94A3B8' }};">
                                {{ $item->status }}
                            </span>
                        </td>
                        <td style="padding:1rem 1.25rem;text-align:right;">
                            <div style="display:flex;justify-content:flex-end;gap:0.75rem;">
                                <a href="{{ route('software-catalog.edit', $item) }}" style="color:#38BDF8;text-decoration:none;font-weight:600;">Edit</a>
                                <form action="{{ route('software-catalog.destroy', $item) }}" method="POST" onsubmit="return confirm('Hapus software ini dari katalog?');" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" style="background:none;border:none;color:#F87171;font-weight:600;cursor:pointer;">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="padding:3rem 1rem;text-align:center;color:var(--color-muted);">
                            Belum ada software yang terdaftar di katalog. Klik tombol <strong>+ Tambah Software</strong> untuk menambahkan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($items->hasPages())
        <div style="margin-top:1.5rem;">
            {{ $items->links() }}
        </div>
    @endif
</div>
@endsection
