@extends('layouts.public')

@section('meta_title', 'Manajemen Arcade Games')

@section('content')
<div class="rb-section" style="padding-top:2rem;padding-bottom:5rem;max-width:68rem;margin:0 auto;">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;margin-bottom:2rem;flex-wrap:wrap;">
        <div>
            <span class="rb-section-label">RB Dashboard</span>
            <h1 class="rb-section-title" style="margin:0;">Koleksi Arcade Games</h1>
        </div>
        <a href="{{ route('arcade-games.create') }}" class="rb-btn-primary" style="text-decoration:none;">
            + Tambah Game
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
                    <th style="padding:1rem 1.25rem;">Judul</th>
                    <th style="padding:1rem 1.25rem;">Slug</th>
                    <th style="padding:1rem 1.25rem;">Game URL</th>
                    <th style="padding:1rem 1.25rem;">Status</th>
                    <th style="padding:1rem 1.25rem;text-align:right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($games as $game)
                    <tr style="border-bottom:1px solid rgba(255,255,255,0.05);">
                        <td style="padding:1rem 1.25rem;font-weight:700;color:#F1F5F9;">{{ $game->title }}</td>
                        <td style="padding:1rem 1.25rem;color:var(--color-muted);font-family:'JetBrains Mono',monospace;font-size:0.8rem;">{{ $game->slug }}</td>
                        <td style="padding:1rem 1.25rem;color:var(--color-muted);font-size:0.85rem;">
                            <a href="{{ $game->game_url }}" target="_blank" rel="noopener" style="color:#38BDF8;text-decoration:none;">{{ \Illuminate\Support\Str::limit($game->game_url, 40) }}</a>
                        </td>
                        <td style="padding:1rem 1.25rem;">
                            <span style="padding:0.2rem 0.65rem;border-radius:999px;font-size:0.75rem;font-weight:700;background:{{ $game->status === 'Tayangkan' ? 'rgba(16,185,129,0.12)' : 'rgba(148,163,184,0.12)' }};color:{{ $game->status === 'Tayangkan' ? '#34D399' : '#94A3B8' }};">
                                {{ $game->status }}
                            </span>
                        </td>
                        <td style="padding:1rem 1.25rem;text-align:right;display:flex;justify-content:flex-end;gap:0.75rem;">
                            <a href="{{ route('arcade-games.edit', $game) }}" style="color:#38BDF8;text-decoration:none;font-weight:600;">Edit</a>
                            <form action="{{ route('arcade-games.destroy', $game) }}" method="POST" onsubmit="return confirm('Hapus game ini?');" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="background:none;border:none;color:#F87171;font-weight:600;cursor:pointer;">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="padding:2.5rem;text-align:center;color:var(--color-muted);">Belum ada game arcade yang terdaftar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($games->hasPages())
        <div style="margin-top:1.5rem;">
            {{ $games->links() }}
        </div>
    @endif
</div>
@endsection
