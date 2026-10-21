@extends('layouts.public')

@section('meta_title', 'Tambah Game Arcade')

@section('content')
<div class="rb-section" style="padding-top:2rem;padding-bottom:5rem;max-width:44rem;margin:0 auto;">
    <div style="margin-bottom:2rem;">
        <a href="{{ route('arcade-games.index') }}" style="color:var(--color-muted);text-decoration:none;font-size:0.85rem;">&larr; Kembali ke Daftar Game</a>
        <h1 class="rb-section-title" style="margin-top:0.5rem;">Tambah Game Arcade</h1>
    </div>

    <form action="{{ route('arcade-games.store') }}" method="POST" enctype="multipart/form-data"
          style="background:rgba(255,255,255,0.025);border:1px solid var(--color-border);border-radius:1rem;padding:2rem;display:flex;flex-direction:column;gap:1.25rem;">
        @csrf

        <div>
            <label for="title" style="display:block;font-size:0.85rem;font-weight:600;color:#F1F5F9;margin-bottom:0.5rem;">Judul Game</label>
            <input type="text" id="title" name="title" value="{{ old('title') }}" required
                   style="width:100%;padding:0.75rem 1rem;border-radius:0.5rem;background:rgba(0,0,0,0.35);border:1px solid var(--color-border);color:#F1F5F9;">
            @error('title') <p style="color:#F87171;font-size:0.8rem;margin-top:0.35rem;">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="slug" style="display:block;font-size:0.85rem;font-weight:600;color:#F1F5F9;margin-bottom:0.5rem;">Slug (Opsional)</label>
            <input type="text" id="slug" name="slug" value="{{ old('slug') }}" placeholder="Otomatis dari judul jika dikosongkan"
                   style="width:100%;padding:0.75rem 1rem;border-radius:0.5rem;background:rgba(0,0,0,0.35);border:1px solid var(--color-border);color:#F1F5F9;">
            @error('slug') <p style="color:#F87171;font-size:0.8rem;margin-top:0.35rem;">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="game_url" style="display:block;font-size:0.85rem;font-weight:600;color:#F1F5F9;margin-bottom:0.5rem;">Game URL (Cloudflare / Web URL)</label>
            <input type="url" id="game_url" name="game_url" value="{{ old('game_url') }}" required placeholder="https://tetris-rb.pages.dev"
                   style="width:100%;padding:0.75rem 1rem;border-radius:0.5rem;background:rgba(0,0,0,0.35);border:1px solid var(--color-border);color:#F1F5F9;">
            @error('game_url') <p style="color:#F87171;font-size:0.8rem;margin-top:0.35rem;">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="description" style="display:block;font-size:0.85rem;font-weight:600;color:#F1F5F9;margin-bottom:0.5rem;">Deskripsi</label>
            <textarea id="description" name="description" rows="4" required
                      style="width:100%;padding:0.75rem 1rem;border-radius:0.5rem;background:rgba(0,0,0,0.35);border:1px solid var(--color-border);color:#F1F5F9;">{{ old('description') }}</textarea>
            @error('description') <p style="color:#F87171;font-size:0.8rem;margin-top:0.35rem;">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="thumbnail" style="display:block;font-size:0.85rem;font-weight:600;color:#F1F5F9;margin-bottom:0.5rem;">Thumbnail Gambar</label>
            <input type="file" id="thumbnail" name="thumbnail" accept="image/*"
                   style="width:100%;padding:0.6rem;border-radius:0.5rem;background:rgba(0,0,0,0.35);border:1px solid var(--color-border);color:#F1F5F9;">
            @error('thumbnail') <p style="color:#F87171;font-size:0.8rem;margin-top:0.35rem;">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="status" style="display:block;font-size:0.85rem;font-weight:600;color:#F1F5F9;margin-bottom:0.5rem;">Status Publikasi</label>
            <select id="status" name="status" required
                    style="width:100%;padding:0.75rem 1rem;border-radius:0.5rem;background:#0F172A;border:1px solid var(--color-border);color:#F1F5F9;">
                <option value="Tayangkan" {{ old('status') === 'Tayangkan' ? 'selected' : '' }}>Tayangkan</option>
                <option value="Draft" {{ old('status', 'Draft') === 'Draft' ? 'selected' : '' }}>Draft</option>
            </select>
            @error('status') <p style="color:#F87171;font-size:0.8rem;margin-top:0.35rem;">{{ $message }}</p> @enderror
        </div>

        <div style="display:flex;justify-content:flex-end;gap:0.75rem;margin-top:0.5rem;">
            <button type="submit" class="rb-btn-primary" style="cursor:pointer;border:none;">Simpan Game</button>
        </div>
    </form>
</div>
@endsection
