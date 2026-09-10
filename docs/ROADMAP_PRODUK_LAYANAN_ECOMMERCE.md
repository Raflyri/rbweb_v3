# Roadmap Produk & Layanan + E-commerce Kecil — rbeverything.com

Dokumen ini kelanjutan dari diskusi analisis kelayakan fitur Produk/Layanan + e-commerce kecil
di rbeverything.com. Isinya dua bagian: **roadmap bertahap** dan **prompt siap tempel** untuk
Claude Code (folder `rbeverything-main`), mengikuti gaya yang sama dengan
`docs/PROMPT_PERBAIKAN_BLOG_RBEVERYTHING.md` yang sudah ada di repo ini.

**Prinsip kerja:** satu fase = satu (atau beberapa) sesi Claude Code di branch `sandbox`,
dites di `web-sbox.rbeverything.com`, baru merge ke `main` kalau sudah oke. Jangan loncat
fase — Fase 2 butuh Fase 1 selesai, Fase 4 butuh Fase 3 selesai, dst. Fase 5 (Midtrans) sengaja
dibuat **tidak aktif secara default** — ini "wadah", bukan fitur yang langsung jalan.

---

## Ringkasan Roadmap & Effort

| Fase | Yang dikerjakan | Estimasi effort | Status Midtrans |
|---|---|---|---|
| 0 | Keputusan bisnis (bukan coding) | 15–30 menit ngobrol sama diri sendiri/istri | — |
| 1 | Model & admin CRUD Produk/Layanan (barang + jasa) | 1–2 hari | — |
| 2 | Halaman publik "Produk & Layanan" | 1 hari | — |
| 3 | Alur pemesanan (Order) + admin kelola pesanan | 3–5 hari | — |
| 4 | Pembayaran manual transfer (aktif sekarang) | 2–3 hari | — |
| 5 | Wadah integrasi Midtrans (disiapkan, non-aktif) | 1–2 hari | Siap dipasang begitu akun clear |

Total realistis: **±2–3 minggu** kerja tersela-sela (bukan full-time), untuk versi e-commerce
kecil yang layak pakai — bukan marketplace besar.

Catatan soal editor layout ala Gutenberg (poin 3 dari diskusi awal) ada di bagian paling
bawah dokumen ini, sebagai catatan — **tidak dikerjakan dulu** sesuai arahan kamu.

---

## Fase 0 — Keputusan Bisnis Dulu (Bukan Coding)

Sebelum prompt Fase 1 dijalankan, putuskan dulu 4 hal ini (supaya Claude Code tidak menebak-nebak
dan hasilnya sesuai kebutuhan asli RBeverything):

1. **Untuk produk "barang"** — apakah checkout perlu alamat pengiriman? (Kalau ya, ongkos kirim
   dihitung manual dulu oleh kamu/istri via WhatsApp/telepon setelah order masuk, bukan otomatis
   — ini yang paling masuk akal untuk skala kecil sekarang.)
2. **Untuk produk "jasa"** — apakah butuh field tambahan seperti "tanggal/jadwal yang diinginkan"
   di form pesan, atau cukup catatan bebas dulu?
3. **Rekening bank/e-wallet** untuk metode transfer manual (Fase 4) — nama bank, nomor rekening,
   atas nama siapa. Ini akan tampil ke calon pembeli di halaman checkout.
4. **Siapa yang boleh kelola harga & konfirmasi pembayaran** di admin — cukup kamu & istri
   (role `admin`/`super_admin`), atau ada peran lain? (Rekomendasi saya: **jangan** buka akses
   ini ke role client-area yang sama dengan penulis artikel — ingat temuan bug self-publish
   kemarin. Produk/harga/pembayaran sebaiknya admin-only dari awal.)

Simpan jawabannya di kepala/catatan sendiri — nanti tinggal disebutkan pas menjalankan prompt
Fase 3 dan Fase 4 kalau ada penyesuaian dari default yang saya tulis.

---

## Fase 1 — Model & Admin CRUD Produk/Layanan (Barang + Jasa)

```
Saya kerja di proyek Laravel 12 + Filament v5 rbeverything.com (folder rbeverything-main).
Saya mau menambah fitur katalog "Produk & Layanan" yang bisa dikelola lewat admin dashboard.
RBeverything (brand saya) menjual dua jenis item: barang (fisik) dan jasa (layanan) — beberapa
item perlu ditampilkan harganya, beberapa tidak (tampilkan "Hubungi Kami" kalau harga kosong).

Ikuti pola yang SUDAH ada di proyek ini untuk model Article (app/Models/Article.php,
app/Filament/Resources/Articles/) — translatable via spatie/laravel-translatable, folder
Filament Resource dipecah jadi Schemas/Tables/Pages, form field diekstrak ke class Support
terpisah (contoh: app/Filament/Support/ArticleFields.php), dan LogsActivity dari
spatie/laravel-activitylog untuk audit log. TAPI ada satu pola Article yang JANGAN ditiru:
Article menyimpan slug sebagai JSON per-locale yang sama untuk semua bahasa (lihat
ArticleFields::slugField() dan komentarnya) — ini pernah jadi sumber bug locale-key mismatch.
Untuk Product, pakai slug sebagai kolom string biasa (satu slug per produk, tidak perlu
per-locale), pakai trait HasSlug dari spatie/laravel-sluggable dengan cara yang standar.

Tolong kerjakan:

1. Migration `create_products_table`:
   - id, slug (string, unique)
   - name (json — translatable, locale: id + en saja untuk produk, tidak perlu ms/ja)
   - description (json — translatable, rich text)
   - short_description (json — translatable, text pendek untuk card di listing)
   - type (enum: 'barang', 'jasa') — WAJIB diisi
   - price (decimal 12,2, nullable) — kalau null berarti "Hubungi Kami"
   - currency (string, default 'IDR')
   - thumbnail (string, nullable — path file)
   - gallery (json, nullable — array path file untuk galeri tambahan, opsional untuk sekarang
     boleh disiapkan kolomnya saja tanpa UI upload multi-gambar kalau waktu terbatas)
   - is_active (boolean, default true) — kontrol tampil/tidak di halaman publik
   - is_featured (boolean, default false) — untuk highlight di homepage nanti
   - sort_order (unsigned smallint, default 0)
   - meta_title, meta_description (json, translatable, nullable — SEO, ikuti pola
     ArticleFields::metaTitleField/metaDescriptionField untuk batas karakter & helper text)
   - timestamps

2. Model `App\Models\Product`:
   - use HasFactory, HasSlug (spatie/laravel-sluggable), HasTranslations (spatie/laravel-translatable),
     LogsActivity
   - public $translatable = ['name', 'description', 'short_description', 'meta_title', 'meta_description'];
   - getSlugOptions() generate dari name.id (fallback name.en kalau id kosong)
   - scope `active()` → where is_active = true
   - scope `barang()` / `jasa()` → filter by type
   - accessor `hasPrice()` → return !is_null(price)
   - accessor `formattedPrice()` → format ke "Rp X.XXX.XXX" pakai number_format, return null
     kalau price null (biar view tinggal cek null untuk tampilkan "Hubungi Kami")
   - getActivitylogOptions() ikuti pola persis seperti di Article.php

3. Filament Resource `App\Filament\Resources\Products\ProductResource` dengan struktur folder
   Schemas/ProductForm.php, Tables/ProductsTable.php, Pages/ (List, Create, Edit) — CONTEK
   PERSIS struktur folder app/Filament/Resources/Articles/. Buat juga
   app/Filament/Support/ProductFields.php senada dengan ArticleFields.php:
   - Tab bahasa (ID dulu baru EN, urutan dibalik dari Article karena target pasar produk ini
     lebih ke lokal) untuk name, short_description, description (pakai RichEditor untuk
     description, TextInput untuk name, Textarea untuk short_description)
   - Select type (barang/jasa) — required, dengan hint text menjelaskan bedanya untuk tampilan
   - TextInput price — numeric, prefix "Rp", nullable, dengan helper text "Kosongkan untuk
     tampilkan tombol 'Hubungi Kami' alih-alih harga"
   - Toggle is_active, is_featured
   - FileUpload thumbnail (image, disk public, directory 'product-thumbnails', imageEditor,
     aspect ratio 1:1 dan 4:3 sebagai pilihan, maxSize 2048 — ikuti pola thumbnailField di
     ArticleFields)
   - Tabel: kolom thumbnail (kecil), name (locale aktif), type (badge warna beda barang vs
     jasa), price (format rupiah atau "Hubungi Kami"), is_active (icon toggle), is_featured,
     dengan filter by type dan is_active

4. Policy `App\Policies\ProductPolicy`: HANYA role admin/super_admin (cek pola pemakaian
   Spatie Permission di ProfilePolicy.php atau RolePolicy.php) yang boleh create/update/delete/
   publish. Daftarkan resource ini supaya TIDAK muncul sama sekali di panel ClientArea —
   ini murni admin panel utama, bukan sesuatu yang bisa diakses editor artikel biasa.
   Daftarkan juga permission barunya ke Filament Shield (php artisan shield:generate atau
   cara manual yang konsisten dengan permission Article/Post yang sudah ada).

5. Seeder/Factory: buat ProductFactory dan tambahkan beberapa contoh data dummy (2-3 barang,
   2-3 jasa, campur yang ada harga dan yang tidak) di DatabaseSeeder — TAPI beri komentar jelas
   bahwa ini data contoh untuk development, JANGAN pernah masuk ke seeder yang jalan otomatis
   di production (ingat temuan lama soal artikel data sampah yang kebawa ke production).

6. Tulis Pest test: model Product bisa dibuat, slug ter-generate otomatis dari name.id,
   scope active()/barang()/jasa() bekerja benar, formattedPrice() return null kalau price
   null dan return string "Rp ..." kalau ada, dan Policy menolak user non-admin.

7. Jalankan `php artisan test` pastikan semua lulus termasuk test lama.

Laporkan file yang dibuat/diubah, dan konfirmasi permission baru apa saja yang perlu saya
assign ke role admin/super_admin lewat Filament Shield UI.
```

---

## Fase 2 — Halaman Publik "Produk & Layanan"

```
Saya kerja di proyek Laravel 12 + Filament v5 rbeverything.com (folder rbeverything-main).
Model Product (barang & jasa) dari fase sebelumnya sudah ada — sekarang saya mau halaman
publiknya, dengan gaya visual yang konsisten dengan halaman blog yang sudah ada
(resources/views/blog/index.blade.php dan blog/show.blade.php — dark theme dengan accent
warna, cek juga welcome.blade.php untuk gaya card produk homepage yang sudah ada lewat
LaunchpadLink supaya konsisten).

Tolong kerjakan:

1. Route baru di routes/web.php:
   - GET /produk-layanan → ProductController@index (listing, dengan filter tab "Semua" /
     "Barang" / "Jasa" via query string ?type=barang|jasa)
   - GET /produk-layanan/{slug} → ProductController@show (detail satu produk/jasa)
   Ikuti pola penamaan route ->name() yang konsisten dengan blog.index/blog.show yang sudah ada.

2. Controller `App\Http\Controllers\ProductController`:
   - index(): query Product::active()->orderBy('sort_order')->orderByDesc('is_featured'),
     filter by type kalau ada query param, paginate.
   - show(string $slug): findOrFail by slug, 404 kalau tidak ketemu atau is_active = false
     (kecuali user login sebagai admin, boleh preview produk non-aktif — cek pola serupa
     kalau ada di ArticleController untuk draft preview).

3. View resources/views/products/index.blade.php:
   - Grid card produk (thumbnail, nama, badge tipe barang/jasa, harga ATAU "Hubungi Kami"
     kalau price null, short_description, link ke detail)
   - Tab filter Semua/Barang/Jasa
   - Empty state yang didesain rapi (bukan teks polos) untuk kondisi belum ada produk aktif
     sama sekali — konsisten dengan empty state blog yang sudah dibuat di prompt sebelumnya

4. View resources/views/products/show.blade.php:
   - Thumbnail besar, nama, badge tipe, harga/Hubungi Kami, description lengkap (render HTML
     dari RichEditor dengan aman — CEK apakah content di-purify, ingat temuan lama soal
     Article yang TIDAK di-purify sebelum tampil; untuk Product, WAJIB pakai
     Purifier::clean() sebelum render dengan {!! !!}, jangan ulangi kesalahan yang sama)
   - Tombol CTA: kalau ada price → "Pesan Sekarang" (link ke /pesan/{slug}, akan dibuat di
     Fase 3 — untuk sekarang boleh disabled/placeholder dulu kalau route belum ada), kalau
     price null → "Hubungi Kami" (mailto: atau link WhatsApp, sesuaikan dengan kontak yang
     sudah dipakai di halaman lain proyek ini, cek resources/views untuk pola kontak yang ada)
   - Meta tag SEO (title, description, OG, canonical) — CONTEK PERSIS pola yang sudah bagus
     di resources/views/blog/show.blade.php, jangan bikin ulang dari nol

5. Tambahkan link "Produk & Layanan" di navigasi utama (cek layout/navigation partial yang
   dipakai blog & portfolio saat ini, biasanya di resources/views/layouts atau components).

6. Update sitemap generator (app/Console/Commands untuk app:generate-sitemap, cek command
   yang sudah ada) supaya menyertakan /produk-layanan dan setiap /produk-layanan/{slug} yang
   aktif — ingat temuan lama soal sitemap yang localhost/basi, jangan bikin masalah serupa
   untuk halaman baru ini.

7. Tulis Pest test: halaman index 200 OK dengan produk aktif tampil dan non-aktif tidak
   tampil, halaman show 404 untuk produk non-aktif/slug salah, filter type bekerja benar.

8. Jalankan `php artisan test`.

Laporkan file yang dibuat/diubah.
```

---

## Fase 3 — Alur Pemesanan (Order) + Admin Kelola Pesanan

```
Saya kerja di proyek Laravel 12 + Filament v5 rbeverything.com (folder rbeverything-main).
Model Product dan halaman publiknya sudah ada. Sekarang saya mau alur pemesanan sederhana —
BUKAN cart multi-item dulu (skala saya masih kecil), tapi form pesan per-produk: calon
pembeli klik "Pesan Sekarang" di halaman produk, isi form (nama, email, no. HP/WhatsApp,
[alamat pengiriman kalau type=barang], catatan tambahan, jumlah/qty), submit → jadi Order
baru dengan status "Baru", lalu diarahkan ke halaman pembayaran (akan diisi Fase 4 — untuk
prompt ini, buat saja placeholder halaman "menunggu pembayaran" yang menampilkan ringkasan
order + total harga, TANPA payment gateway sungguhan dulu).

Tolong kerjakan:

1. Migration `create_orders_table`:
   - id, order_number (string, unique, format contoh: RB-20260906-0001, generate otomatis)
   - product_id (foreign key ke products, nullable — nullOnDelete supaya histori order tidak
     ikut hilang kalau produk dihapus, tapi simpan snapshot di bawah)
   - product_name_snapshot (string) — nama produk saat dipesan, biar histori tidak berubah
     kalau nama produk diedit belakangan
   - product_type_snapshot (string)
   - price_snapshot (decimal 12,2, nullable)
   - qty (unsigned integer, default 1)
   - subtotal (decimal 12,2) — price_snapshot * qty, dihitung saat order dibuat
   - customer_name, customer_email, customer_phone (string)
   - shipping_address (text, nullable — hanya diisi kalau product_type_snapshot = 'barang')
   - notes (text, nullable)
   - status (enum: 'baru', 'diproses', 'selesai', 'dibatalkan', default 'baru')
   - payment_method (string, nullable — diisi di Fase 4, siapkan kolomnya saja sekarang)
   - payment_status (enum: 'menunggu', 'lunas', 'gagal', 'dibatalkan', default 'menunggu')
   - paid_at (timestamp, nullable)
   - timestamps

2. Model `App\Models\Order` dengan LogsActivity (ikuti pola Article.php), relasi
   belongsTo(Product::class), scope-scope status yang berguna (pending(), paid(), dst),
   dan static method generateOrderNumber() untuk format RB-YYYYMMDD-NNNN (increment harian).

3. Controller `App\Http\Controllers\OrderController`:
   - create(Product $product): tampilkan form pesan (resources/views/orders/create.blade.php),
     validasi produk harus is_active dan hasPrice() (untuk V1 ini, produk tanpa harga TIDAK
     bisa dipesan online — arahkan ke "Hubungi Kami" saja, sesuai keputusan Fase 0)
   - store(Request $request, Product $product): validasi input, hitung subtotal, buat Order,
     redirect ke route order.pending dengan order_number
   - pending(string $orderNumber): tampilkan ringkasan order + status "Menunggu Pembayaran"
     (resources/views/orders/pending.blade.php) — ini yang di Fase 4 akan diisi tombol/instruksi
     pembayaran sungguhan, untuk sekarang cukup tampilkan ringkasan + teks
     "Metode pembayaran akan segera tersedia, hubungi kami di [kontak] untuk instruksi
     pembayaran manual sementara ini."

4. Route baru: GET /produk-layanan/{product:slug}/pesan (order.create),
   POST /produk-layanan/{product:slug}/pesan (order.store),
   GET /pesanan/{orderNumber} (order.pending) — sesuaikan penamaan kalau ada konvensi lebih
   pas di proyek ini.

5. Filament Resource `App\Filament\Resources\Orders\OrderResource` (admin-only, sama seperti
   ProductResource — TIDAK muncul di ClientArea): tabel daftar order (nomor, nama pembeli,
   produk, qty, subtotal, status, payment_status, tanggal), filter by status, form edit untuk
   ubah status ('baru' → 'diproses' → 'selesai', atau 'dibatalkan'), tombol action cepat di
   tabel untuk ubah status tanpa buka form detail. Detail view menampilkan semua data order
   termasuk shipping_address kalau ada.

6. Notifikasi email sederhana: saat Order dibuat, kirim email ke alamat admin (pakai
   MAIL config yang sudah ada di proyek, cek app/Notifications atau app/Mail kalau sudah
   ada pola serupa) berisi ringkasan order baru. Boleh pakai Laravel Notification standar.

7. Tulis Pest test: submit form order dengan data valid → Order tersimpan dengan status baru
   dan payment_status menunggu, order_number ter-generate unik, shipping_address WAJIB diisi
   kalau product type barang (validasi gagal kalau kosong), TIDAK wajib kalau jasa. Test juga
   produk dengan price null tidak bisa diakses lewat route order.create (redirect/403).

8. Jalankan `php artisan test`.

Laporkan file yang dibuat/diubah, dan tunjukkan contoh order_number yang dihasilkan.
```

---

## Fase 4 — Pembayaran Manual Transfer (Aktif Sekarang)

```
Saya kerja di proyek Laravel 12 + Filament v5 rbeverything.com (folder rbeverything-main).
Alur Order dari fase sebelumnya sudah ada tapi halaman "menunggu pembayaran" masih placeholder.
Karena akun Midtrans saya belum clear urusan bisnisnya, saya mau metode pembayaran yang BISA
JALAN SEKARANG: transfer bank manual dengan verifikasi oleh admin. Tapi desain kodenya harus
disiapkan supaya nanti gampang ditambah metode Midtrans tanpa bongkar ulang (lihat Fase 5).

Tolong kerjakan:

1. Buat interface `App\Contracts\PaymentGateway` dengan method minimal:
   - charge(Order $order): array — return data yang dibutuhkan view untuk menampilkan cara
     bayar (misal: ['type' => 'manual_transfer', 'instructions' => [...]] atau
     ['type' => 'redirect', 'url' => '...'] untuk gateway yang redirect)
   - name(): string — nama tampilan metode ini

2. Buat `App\Services\Payment\ManualTransferGateway implements PaymentGateway`:
   - charge() mengembalikan detail rekening bank/e-wallet RBeverything (SIMPAN detail rekening
     ini di config/services.php di bawah key 'manual_transfer' — bank_name, account_number,
     account_holder — BUKAN hardcode di kelas, dan BUKAN masuk ke .env kalau bukan rahasia,
     cukup config biasa karena ini info yang memang publik/ditampilkan ke pembeli).
     Isi placeholder value di config, saya akan isi datanya sendiri manual setelah ini.

3. Buat `App\Services\Payment\PaymentGatewayResolver` (atau pola factory/manager sederhana)
   yang mengembalikan instance gateway aktif berdasarkan config('services.payment.active_gateway')
   — default-nya 'manual_transfer'. Ini titik yang nanti di Fase 5 tinggal ditambah case
   'midtrans' tanpa ubah kode lain yang memanggilnya.

4. Update OrderController@pending: panggil resolver, tampilkan instruksi pembayaran dari
   charge() di view (nomor rekening, nominal, catatan "cantumkan {order_number} di berita
   transfer").

5. Tambahkan field upload bukti transfer: migration tambah kolom `payment_proof` (string,
   nullable) di tabel orders. Buat form kecil di halaman order.pending untuk customer upload
   bukti transfer (FileUpload biasa via form HTML + Controller method baru
   uploadProof(Order $order, Request $request) yang simpan file ke storage dan set
   payment_status jadi 'menunggu_verifikasi' — tambahkan value ini ke enum payment_status).

6. Di Filament OrderResource, tambahkan:
   - Kolom/preview bukti transfer yang sudah diupload
   - Action tombol "Konfirmasi Lunas" (khusus admin/super_admin) yang set payment_status
     jadi 'lunas', paid_at jadi now(), dan kirim notifikasi email ke customer (pakai
     customer_email dari order) bahwa pembayaran terkonfirmasi
   - Action tombol "Tolak Bukti Transfer" yang set payment_status balik ke 'menunggu' dengan
     catatan alasan (tambahkan kolom payment_note text nullable kalau belum ada)

7. Tulis Pest test: resolver mengembalikan ManualTransferGateway secara default, upload bukti
   transfer mengubah payment_status jadi menunggu_verifikasi, action "Konfirmasi Lunas" hanya
   bisa dijalankan oleh admin/super_admin (test policy/permission), dan mengubah payment_status
   jadi lunas + set paid_at.

8. Jalankan `php artisan test`.

Laporkan file yang dibuat/diubah, dan config apa saja di config/services.php yang perlu saya
isi manual (nomor rekening dsb).
```

---

## Fase 5 — Wadah Integrasi Midtrans (Disiapkan, Tidak Aktif)

```
Saya kerja di proyek Laravel 12 + Filament v5 rbeverything.com (folder rbeverything-main).
Sistem pembayaran manual transfer dari fase sebelumnya sudah jalan, dengan
App\Contracts\PaymentGateway dan PaymentGatewayResolver yang sudah disiapkan supaya bisa
ditambah metode baru. Saya SUDAH PUNYA akun Midtrans, tapi proses verifikasi bisnisnya masih
berjalan — jadi saya mau kodenya DISIAPKAN SEKARANG (supaya nanti tinggal isi API key dan
ganti satu config, tanpa coding ulang), tapi JANGAN diaktifkan sebagai metode default, dan
JANGAN sampai kalau ada error di sisi Midtrans (misal API key belum valid) itu bikin fitur
lain di website ikut error.

Tolong kerjakan:

1. Install package resmi `midtrans/midtrans-php` via composer.

2. Tambahkan config di config/services.php:
   ```
   'midtrans' => [
       'server_key' => env('MIDTRANS_SERVER_KEY'),
       'client_key' => env('MIDTRANS_CLIENT_KEY'),
       'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
       'is_active' => env('MIDTRANS_IS_ACTIVE', false),  // <-- saklar utama, default MATI
   ],
   ```
   Tambahkan juga MIDTRANS_SERVER_KEY, MIDTRANS_CLIENT_KEY, MIDTRANS_IS_PRODUCTION=false,
   MIDTRANS_IS_ACTIVE=false ke .env.example dengan komentar singkat "isi setelah akun bisnis
   Midtrans clear, lalu set MIDTRANS_IS_ACTIVE=true".

3. Buat `App\Services\Payment\MidtransGateway implements PaymentGateway`:
   - charge(Order $order): pakai Midtrans Snap API untuk buat transaksi (transaction_details:
     order_id => order_number, gross_amount => subtotal; customer_details dari data order),
     kembalikan snap_token/redirect_url. BUNGKUS pemanggilan API Midtrans dalam try/catch —
     kalau gagal (API key salah, network error, dsb), lempar exception custom
     `PaymentGatewayException` yang di-catch di OrderController supaya user tetap dapat
     pesan error yang jelas ("Pembayaran online sedang tidak tersedia, silakan hubungi kami")
     alih-alih halaman 500 mentah.
   - name(): return 'Midtrans'

4. Update PaymentGatewayResolver: kalau config('services.midtrans.is_active') true DAN
   config('services.payment.active_gateway') === 'midtrans', kembalikan MidtransGateway,
   selain itu tetap fallback ke ManualTransferGateway. Jangan biarkan resolver memilih
   Midtrans kalau is_active masih false, APAPUN nilai active_gateway-nya — ini pagar supaya
   tidak ada yang tidak sengaja mengaktifkan sebelum kamu benar-benar siap.

5. Buat route webhook: POST /payment/midtrans/notification → 
   MidtransNotificationController@handle — route ini HARUS dikecualikan dari CSRF (lihat pola
   yang sudah ada di route emergency-command untuk withoutMiddleware VerifyCsrfToken) karena
   dipanggil server Midtrans, bukan browser. Di controller: verifikasi signature_key sesuai
   dokumentasi resmi Midtrans (order_id + status_code + gross_amount + ServerKey di-hash
   SHA512, dibandingkan dengan signature_key yang dikirim) SEBELUM memproses apapun — tolak
   dengan 403 kalau signature tidak cocok. Kalau valid, update Order terkait (cari by
   order_number) sesuai transaction_status dari Midtrans: 'settlement'/'capture' → lunas,
   'expire'/'cancel'/'deny' → gagal, 'pending' → tetap menunggu.

6. Tambahkan rate limiting/throttle di route webhook (contoh throttle:60,1, sesuaikan dengan
   dokumentasi Midtrans soal retry) mengikuti pola yang sudah ada di route emergency-command.

7. Buat 1 Artisan command `php artisan midtrans:test-connection` yang sekadar mengecek apakah
   server_key terisi dan bisa dipakai buat request sederhana ke Midtrans API (get status
   transaksi dummy) — supaya saya bisa cek dari terminal "apakah wadah ini sudah siap dipasang"
   begitu API key production saya terima, tanpa perlu bikin order sungguhan dulu.

8. Tulis Pest test: resolver TETAP mengembalikan ManualTransferGateway selama
   MIDTRANS_IS_ACTIVE=false meskipun active_gateway di-set 'midtrans' (test pagar keamanan ini
   secara eksplisit), webhook menolak request dengan signature salah (403), webhook memproses
   dan update status dengan benar untuk signature valid (mock response Midtrans, jangan panggil
   API asli di test).

9. Jalankan `php artisan test`.

10. Tulis dokumentasi singkat di docs/MIDTRANS_ACTIVATION.md: langkah-langkah yang saya perlu
    lakukan begitu akun Midtrans clear (isi 3 env var, set MIDTRANS_IS_ACTIVE=true, jalankan
    midtrans:test-connection, ubah active_gateway jadi 'midtrans' kalau mau jadi default, atau
    biarkan manual transfer tetap jadi opsi paralel).

Laporkan file yang dibuat/diubah. PENTING: konfirmasi ke saya bahwa TIDAK ADA cara bagi
MidtransGateway untuk aktif tanpa saya secara sengaja mengisi MIDTRANS_IS_ACTIVE=true di .env
production.
```

---

## Catatan (Tidak Dikerjakan Dulu): Editor Layout ala Gutenberg

Ini catatan, bukan prompt — sesuai arahan kamu untuk tidak lanjut ke fitur ini dulu, tapi
dokumentasikan supaya keputusannya jelas kalau nanti mau diambil lagi.

**Kondisi sekarang:** field konten artikel (dan nanti deskripsi produk) pakai `RichEditor`
bawaan Filament (berbasis TipTap) — WYSIWYG untuk teks (bold, heading, list, tabel, gambar
inline), tapi bukan editor layout. Tidak ada kontrol untuk menyusun blok berdampingan, atur
lebar kolom, atau drag-and-drop posisi elemen seperti Gutenberg/Elementor/Webflow.

**Kenapa ditunda itu keputusan yang masuk akal:** fitur Produk & Layanan + e-commerce di
roadmap ini jauh lebih berdampak langsung ke bisnis RBeverything (bisa jualan) dibanding
kontrol layout yang detail. Editor layout visual sungguhan itu proyek tersendiri yang cukup
besar, dan risikonya nambah beban maintenance jangka panjang kalau dikerjakan setengah hati.

**Kalau nanti mau diambil lagi, dua opsi (sudah dibahas sebelumnya, dicatat ulang di sini
biar tidak hilang konteksnya):**

1. **Opsi ringan — Filament `Builder` field:** admin susun halaman dari blok-blok yang sudah
   didefinisikan sebelumnya (Hero, Dua-Kolom Teks+Gambar, Galeri, CTA, dst), bisa diurutkan
   naik-turun, tiap blok punya beberapa varian layout siap pakai. Bukan drag-and-drop bebas,
   tapi cukup fleksibel untuk kebanyakan kebutuhan halaman custom. Estimasi 3–5 hari kerja
   kalau/ketika mau dikerjakan.
2. **Opsi penuh — editor visual drag-and-drop sungguhan:** integrasi library JS pihak ketiga
   (semacam GrapesJS/Craft.js) sebagai custom field Filament. Ini baru benar-benar setara
   Gutenberg (posisi bebas, resize kolom visual). Estimasi 3–6 minggu, dan perlu dipikirkan
   matang soal maintenance jangka panjang (risiko bentrok tiap kali upgrade Filament/Tailwind).

**Rekomendasi kalau nanti dibutuhkan:** mulai dari Opsi 1 dulu, baru naik ke Opsi 2 kalau
ternyata kebutuhannya sudah jelas-jelas melebihi apa yang bisa dicakup blok-blok siap pakai.
Jangan mulai dari Opsi 2 langsung — itu investasi besar untuk kebutuhan yang belum tentu
sebesar itu.

---

*Dokumen ini dibuat sebagai kelanjutan analisis kelayakan fitur Produk/Layanan & e-commerce
untuk rbeverything.com. Urutan fase dirancang supaya setiap tahap tetap menghasilkan sesuatu
yang bisa langsung dipakai (bukan menunggu semua fase selesai baru ada manfaatnya) — Fase 1–2
sudah bikin katalog produk tampil, Fase 3–4 sudah bisa terima pesanan & pembayaran manual,
Fase 5 tinggal "colokkan" begitu Midtrans siap.*
