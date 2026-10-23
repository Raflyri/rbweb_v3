@extends('layouts.public')

@section('meta_title', 'Tambah Software ke Katalog')

@section('content')
<div class="rb-section" style="padding-top:2rem;padding-bottom:5rem;max-width:48rem;margin:0 auto;">
    <div style="margin-bottom:2rem;">
        <a href="{{ route('software-catalog.index') }}" style="color:var(--color-muted);text-decoration:none;font-size:0.85rem;">&larr; Kembali ke Daftar Software</a>
        <h1 class="rb-section-title" style="margin-top:0.5rem;">Tambah Software Baru</h1>
    </div>

    <form action="{{ route('software-catalog.store') }}" method="POST" enctype="multipart/form-data"
          style="background:rgba(255,255,255,0.025);border:1px solid var(--color-border);border-radius:1rem;padding:2rem;display:flex;flex-direction:column;gap:1.5rem;">
        @csrf

        <div>
            <label for="title" style="display:block;font-size:0.85rem;font-weight:600;color:#F1F5F9;margin-bottom:0.5rem;">Nama Software</label>
            <input type="text" id="title" name="title" value="{{ old('title') }}" required placeholder="Contoh: Sistem POS & ERP Toko Bangunan"
                   style="width:100%;padding:0.75rem 1rem;border-radius:0.5rem;background:rgba(0,0,0,0.35);border:1px solid var(--color-border);color:#F1F5F9;">
            @error('title') <p style="color:#F87171;font-size:0.8rem;margin-top:0.35rem;">{{ $message }}</p> @enderror
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
            <div>
                <label for="software_category_id" style="display:block;font-size:0.85rem;font-weight:600;color:#F1F5F9;margin-bottom:0.5rem;">Kategori Software</label>
                <select id="software_category_id" name="software_category_id" required
                        style="width:100%;padding:0.75rem 1rem;border-radius:0.5rem;background:rgba(0,0,0,0.35);border:1px solid var(--color-border);color:#F1F5F9;">
                    <option value="">-- Pilih Kategori --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('software_category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
                @error('software_category_id') <p style="color:#F87171;font-size:0.8rem;margin-top:0.35rem;">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="version" style="display:block;font-size:0.85rem;font-weight:600;color:#F1F5F9;margin-bottom:0.5rem;">Versi Rilis</label>
                <input type="text" id="version" name="version" value="{{ old('version', '1.0') }}" required placeholder="Contoh: 1.0, 2.0, atau 2.4-LTS"
                       style="width:100%;padding:0.75rem 1rem;border-radius:0.5rem;background:rgba(0,0,0,0.35);border:1px solid var(--color-border);color:#F1F5F9;">
                @error('version') <p style="color:#F87171;font-size:0.8rem;margin-top:0.35rem;">{{ $message }}</p> @enderror
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
            <div>
                <label for="release_date" style="display:block;font-size:0.85rem;font-weight:600;color:#F1F5F9;margin-bottom:0.5rem;">Tanggal Rilis Resmi</label>
                <input type="date" id="release_date" name="release_date" value="{{ old('release_date', date('Y-m-d')) }}"
                       style="width:100%;padding:0.75rem 1rem;border-radius:0.5rem;background:rgba(0,0,0,0.35);border:1px solid var(--color-border);color:#F1F5F9;">
                @error('release_date') <p style="color:#F87171;font-size:0.8rem;margin-top:0.35rem;">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="badge_text" style="display:block;font-size:0.85rem;font-weight:600;color:#F1F5F9;margin-bottom:0.5rem;">Teks Badge / Sorotan</label>
                <input type="text" id="badge_text" name="badge_text" value="{{ old('badge_text') }}" placeholder="Contoh: v2.0 Tested, Ready to Deploy"
                       style="width:100%;padding:0.75rem 1rem;border-radius:0.5rem;background:rgba(0,0,0,0.35);border:1px solid var(--color-border);color:#F1F5F9;">
                @error('badge_text') <p style="color:#F87171;font-size:0.8rem;margin-top:0.35rem;">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label for="short_description" style="display:block;font-size:0.85rem;font-weight:600;color:#F1F5F9;margin-bottom:0.5rem;">Deskripsi Singkat (Kartu Katalog)</label>
            <textarea id="short_description" name="short_description" rows="3" required placeholder="Ringkasan singkat 1-2 kalimat untuk kartu depan..."
                      style="width:100%;padding:0.75rem 1rem;border-radius:0.5rem;background:rgba(0,0,0,0.35);border:1px solid var(--color-border);color:#F1F5F9;">{{ old('short_description') }}</textarea>
            @error('short_description') <p style="color:#F87171;font-size:0.8rem;margin-top:0.35rem;">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="description" style="display:block;font-size:0.85rem;font-weight:600;color:#F1F5F9;margin-bottom:0.5rem;">Deskripsi Lengkap & Problem Solving (Opsional)</label>
            <textarea id="description" name="description" rows="5" placeholder="Penjelasan detail fitur, arsitektur, dan cara kerja software..."
                      style="width:100%;padding:0.75rem 1rem;border-radius:0.5rem;background:rgba(0,0,0,0.35);border:1px solid var(--color-border);color:#F1F5F9;">{{ old('description') }}</textarea>
            @error('description') <p style="color:#F87171;font-size:0.8rem;margin-top:0.35rem;">{{ $message }}</p> @enderror
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
            <div>
                <label for="tech_stack" style="display:block;font-size:0.85rem;font-weight:600;color:#F1F5F9;margin-bottom:0.5rem;">Tech Stack (Pisahkan dengan koma)</label>
                <input type="text" id="tech_stack" name="tech_stack" value="{{ old('tech_stack') }}" placeholder="Next.js, Prisma, Tailwind, TypeScript"
                       style="width:100%;padding:0.75rem 1rem;border-radius:0.5rem;background:rgba(0,0,0,0.35);border:1px solid var(--color-border);color:#F1F5F9;">
                @error('tech_stack') <p style="color:#F87171;font-size:0.8rem;margin-top:0.35rem;">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="demo_type" style="display:block;font-size:0.85rem;font-weight:600;color:#F1F5F9;margin-bottom:0.5rem;">Tipe Demo</label>
                <select id="demo_type" name="demo_type" required
                        style="width:100%;padding:0.75rem 1rem;border-radius:0.5rem;background:rgba(0,0,0,0.35);border:1px solid var(--color-border);color:#F1F5F9;">
                    <option value="direct" {{ old('demo_type') === 'direct' ? 'selected' : '' }}>Direct Link</option>
                    <option value="one-click" {{ old('demo_type') === 'one-click' ? 'selected' : '' }}>One-Click Demo</option>
                    <option value="credentials" {{ old('demo_type') === 'credentials' ? 'selected' : '' }}>Kredensial Manual</option>
                </select>
                @error('demo_type') <p style="color:#F87171;font-size:0.8rem;margin-top:0.35rem;">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label for="features" style="display:block;font-size:0.85rem;font-weight:600;color:#F1F5F9;margin-bottom:0.5rem;">Fitur-Fitur Utama (Satu per baris)</label>
            <textarea id="features" name="features" rows="4" placeholder="Manajemen kasir cepat&#10;Konversi multi-satuan fleksibel&#10;Pencatatan piutang pelanggan"
                      style="width:100%;padding:0.75rem 1rem;border-radius:0.5rem;background:rgba(0,0,0,0.35);border:1px solid var(--color-border);color:#F1F5F9;">{{ old('features') }}</textarea>
            @error('features') <p style="color:#F87171;font-size:0.8rem;margin-top:0.35rem;">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="changelog" style="display:block;font-size:0.85rem;font-weight:600;color:#F1F5F9;margin-bottom:0.5rem;">Catatan Rilis / Changelog (Opsional)</label>
            <textarea id="changelog" name="changelog" rows="3" placeholder="• Pembaruan v2.0: Dukungan KaTeX & pendeteksi tab siswa..."
                      style="width:100%;padding:0.75rem 1rem;border-radius:0.5rem;background:rgba(0,0,0,0.35);border:1px solid var(--color-border);color:#F1F5F9;">{{ old('changelog') }}</textarea>
            @error('changelog') <p style="color:#F87171;font-size:0.8rem;margin-top:0.35rem;">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="demo_url" style="display:block;font-size:0.85rem;font-weight:600;color:#F1F5F9;margin-bottom:0.5rem;">URL Live Demo</label>
            <input type="url" id="demo_url" name="demo_url" value="{{ old('demo_url') }}" placeholder="https://digitalization-exam.rbeverything.com/"
                   style="width:100%;padding:0.75rem 1rem;border-radius:0.5rem;background:rgba(0,0,0,0.35);border:1px solid var(--color-border);color:#F1F5F9;">
            @error('demo_url') <p style="color:#F87171;font-size:0.8rem;margin-top:0.35rem;">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="demo_credentials" style="display:block;font-size:0.85rem;font-weight:600;color:#F1F5F9;margin-bottom:0.5rem;">Catatan Kredensial Demo (Opsional)</label>
            <input type="text" id="demo_credentials" name="demo_credentials" value="{{ old('demo_credentials') }}" placeholder="Email: siswa@sekolah.com / Pass: password"
                   style="width:100%;padding:0.75rem 1rem;border-radius:0.5rem;background:rgba(0,0,0,0.35);border:1px solid var(--color-border);color:#F1F5F9;">
            @error('demo_credentials') <p style="color:#F87171;font-size:0.8rem;margin-top:0.35rem;">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="thumbnail" style="display:block;font-size:0.85rem;font-weight:600;color:#F1F5F9;margin-bottom:0.5rem;">Thumbnail Gambar</label>
            <input type="file" id="thumbnail" name="thumbnail" accept="image/*"
                   style="width:100%;padding:0.6rem;border-radius:0.5rem;background:rgba(0,0,0,0.35);border:1px solid var(--color-border);color:#F1F5F9;">
            @error('thumbnail') <p style="color:#F87171;font-size:0.8rem;margin-top:0.35rem;">{{ $message }}</p> @enderror
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
            <div>
                <label for="status" style="display:block;font-size:0.85rem;font-weight:600;color:#F1F5F9;margin-bottom:0.5rem;">Status Publikasi</label>
                <select id="status" name="status" required
                        style="width:100%;padding:0.75rem 1rem;border-radius:0.5rem;background:rgba(0,0,0,0.35);border:1px solid var(--color-border);color:#F1F5F9;">
                    <option value="Draft" {{ old('status') === 'Draft' ? 'selected' : '' }}>Draft</option>
                    <option value="Tayangkan" {{ old('status') === 'Tayangkan' ? 'selected' : '' }}>Tayangkan</option>
                </select>
            </div>
            <div>
                <label for="sort_order" style="display:block;font-size:0.85rem;font-weight:600;color:#F1F5F9;margin-bottom:0.5rem;">Urutan Tampil</label>
                <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}"
                       style="width:100%;padding:0.75rem 1rem;border-radius:0.5rem;background:rgba(0,0,0,0.35);border:1px solid var(--color-border);color:#F1F5F9;">
            </div>
        </div>

        <div style="display:flex;align-items:center;gap:0.75rem;padding:0.5rem 0;">
            <input type="checkbox" id="is_featured" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }} style="width:1.2rem;height:1.2rem;accent-color:var(--rb-red);">
            <label for="is_featured" style="font-size:0.875rem;color:#F1F5F9;cursor:pointer;">Tampilkan sebagai Software Unggulan (Featured Spotlight)</label>
        </div>

        <div style="display:flex;justify-content:flex-end;gap:1rem;margin-top:1rem;">
            <a href="{{ route('software-catalog.index') }}" class="rb-btn-secondary" style="text-decoration:none;">Batal</a>
            <button type="submit" class="rb-btn-primary">Simpan Software</button>
        </div>
    </form>
</div>
@endsection
