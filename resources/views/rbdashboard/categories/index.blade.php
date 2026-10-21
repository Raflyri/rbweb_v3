@extends('layouts.public')

@section('meta_title', 'Manajemen Kategori Produk')

@section('content')
<div class="rb-section" style="padding-top:2rem;padding-bottom:5rem;max-width:64rem;margin:0 auto;">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;margin-bottom:2rem;flex-wrap:wrap;">
        <div>
            <span class="rb-section-label">RB Dashboard</span>
            <h1 class="rb-section-title" style="margin:0;">Kategori Produk & Layanan</h1>
        </div>
        <a href="{{ route('categories.create') }}" class="rb-btn-primary" style="text-decoration:none;">
            + Tambah Kategori
        </a>
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
                    <th style="padding:1rem 1.25rem;">Nama</th>
                    <th style="padding:1rem 1.25rem;">Slug</th>
                    <th style="padding:1rem 1.25rem;">Tipe</th>
                    <th style="padding:1rem 1.25rem;">Jumlah Produk</th>
                    <th style="padding:1rem 1.25rem;text-align:right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                    <tr style="border-bottom:1px solid rgba(255,255,255,0.05);">
                        <td style="padding:1rem 1.25rem;font-weight:700;color:#F1F5F9;">{{ $category->name }}</td>
                        <td style="padding:1rem 1.25rem;color:var(--color-muted);font-family:'JetBrains Mono',monospace;font-size:0.8rem;">{{ $category->slug }}</td>
                        <td style="padding:1rem 1.25rem;">
                            <span style="padding:0.2rem 0.65rem;border-radius:999px;font-size:0.75rem;font-weight:700;background:{{ $category->type === 'Barang' ? 'rgba(251,191,36,0.12)' : 'rgba(56,189,248,0.12)' }};color:{{ $category->type === 'Barang' ? '#FBBF24' : '#38BDF8' }};">
                                {{ $category->type }}
                            </span>
                        </td>
                        <td style="padding:1rem 1.25rem;color:var(--color-muted);">{{ $category->products_count ?? 0 }}</td>
                        <td style="padding:1rem 1.25rem;text-align:right;display:flex;justify-content:flex-end;gap:0.75rem;">
                            <a href="{{ route('categories.edit', $category) }}" style="color:#38BDF8;text-decoration:none;font-weight:600;">Edit</a>
                            <form action="{{ route('categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Hapus kategori ini?');" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="background:none;border:none;color:#F87171;font-weight:600;cursor:pointer;">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="padding:2.5rem;text-align:center;color:var(--color-muted);">Belum ada kategori yang dibuat.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($categories->hasPages())
        <div style="margin-top:1.5rem;">
            {{ $categories->links() }}
        </div>
    @endif
</div>
@endsection
