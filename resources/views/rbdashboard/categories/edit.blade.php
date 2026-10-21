@extends('layouts.public')

@section('meta_title', 'Edit Kategori Produk')

@section('content')
<div class="rb-section" style="padding-top:2rem;padding-bottom:5rem;max-width:42rem;margin:0 auto;">
    <div style="margin-bottom:2rem;">
        <a href="{{ route('categories.index') }}" style="color:var(--color-muted);text-decoration:none;font-size:0.85rem;">&larr; Kembali ke Daftar Kategori</a>
        <h1 class="rb-section-title" style="margin-top:0.5rem;">Edit Kategori: {{ $category->name }}</h1>
    </div>

    <form action="{{ route('categories.update', $category) }}" method="POST" style="background:rgba(255,255,255,0.025);border:1px solid var(--color-border);border-radius:1rem;padding:2rem;display:flex;flex-direction:column;gap:1.25rem;">
        @csrf
        @method('PUT')

        <div>
            <label for="name" style="display:block;font-size:0.85rem;font-weight:600;color:#F1F5F9;margin-bottom:0.5rem;">Nama Kategori</label>
            <input type="text" id="name" name="name" value="{{ old('name', $category->name) }}" required
                   style="width:100%;padding:0.75rem 1rem;border-radius:0.5rem;background:rgba(0,0,0,0.35);border:1px solid var(--color-border);color:#F1F5F9;">
            @error('name') <p style="color:#F87171;font-size:0.8rem;margin-top:0.35rem;">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="slug" style="display:block;font-size:0.85rem;font-weight:600;color:#F1F5F9;margin-bottom:0.5rem;">Slug</label>
            <input type="text" id="slug" name="slug" value="{{ old('slug', $category->slug) }}"
                   style="width:100%;padding:0.75rem 1rem;border-radius:0.5rem;background:rgba(0,0,0,0.35);border:1px solid var(--color-border);color:#F1F5F9;">
            @error('slug') <p style="color:#F87171;font-size:0.8rem;margin-top:0.35rem;">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="type" style="display:block;font-size:0.85rem;font-weight:600;color:#F1F5F9;margin-bottom:0.5rem;">Tipe Kategori</label>
            <select id="type" name="type" required
                    style="width:100%;padding:0.75rem 1rem;border-radius:0.5rem;background:#0F172A;border:1px solid var(--color-border);color:#F1F5F9;">
                <option value="Barang" {{ old('type', $category->type) === 'Barang' ? 'selected' : '' }}>Barang</option>
                <option value="Jasa" {{ old('type', $category->type) === 'Jasa' ? 'selected' : '' }}>Jasa</option>
            </select>
            @error('type') <p style="color:#F87171;font-size:0.8rem;margin-top:0.35rem;">{{ $message }}</p> @enderror
        </div>

        <div style="display:flex;justify-content:flex-end;gap:0.75rem;margin-top:0.5rem;">
            <button type="submit" class="rb-btn-primary" style="cursor:pointer;border:none;">Perbarui Kategori</button>
        </div>
    </form>
</div>
@endsection
