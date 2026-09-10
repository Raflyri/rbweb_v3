# Prompt Perbaikan Blog/Artikel/News & Temuan Lain — rbeverything.com

Dokumen ini berisi kumpulan **prompt siap pakai** untuk kamu tempel satu-satu ke Claude Code (atau asisten koding lain) yang bekerja di folder `rbeverything-main`. Setiap prompt ditulis berdiri sendiri — jadi kamu bisa jalankan satu prompt per sesi, gak harus berurutan dalam satu sesi yang sama. Kalau pakai Claude Code, skill `rbeverything` di repo ini akan otomatis kepakai karena kata kunci proyeknya ada di tiap prompt.

**Urutan yang disarankan:** PROMPT 1 dulu (paling mendesak, langsung berdampak ke artikel yang mau kamu publish), baru PROMPT 2–8 sesuai prioritas kamu. Kerjakan semua di branch `sandbox` dulu, cek hasilnya di `web-sbox.rbeverything.com`, baru merge ke `main` kalau sudah oke — jangan langsung ke `main` supaya CI/CD dan test jadi jaring pengaman beneran.

---

## PROMPT 1 — Bersihkan & Siapkan Fitur Blog Supaya Tampil Profesional (PRIORITAS TERTINGGI)

```
Saya kerja di proyek Laravel 12 + Filament rbeverything.com (folder rbeverything-main).
Fitur blog publik di /blog dan /blog/{slug} saat ini tampil rusak/kosong: satu-satunya
artikel yang ada di database production judulnya cuma string URL
("https://rbeverything.com/blog/article"), isinya 0 kata, tanpa excerpt, tanpa thumbnail —
kelihatan seperti data uji coba yang kelupaan dihapus. Saya mau publikasikan artikel-artikel
asli yang sudah saya tulis, dan saya mau tampilannya benar-benar profesional untuk semua
kondisi (0 artikel, 1 artikel, banyak artikel).

Tolong kerjakan:

1. Buat Artisan command baru `php artisan articles:cleanup-test-data` yang mencari dan
   menghapus (atau memindah ke status Draft, biar aman — beri saya pilihan keduanya lewat
   konfirmasi interaktif) artikel dengan ciri-ciri data sampah: judul mengandung "http://"
   atau "https://", ATAU jumlah kata konten di bawah 10, ATAU published_at kosong padahal
   status Published. Jangan hapus artikel yang terlihat valid.

2. Audit app/Filament/Resources/Articles/Schemas/ArticleForm.php dan
   app/Filament/ClientArea/Resources/ClientArticleResource.php: tambahkan validasi supaya
   status TIDAK BISA diset ke "Published" kalau:
   - Konten (untuk minimal locale utama, id/en) kurang dari 50 kata, ATAU
   - Thumbnail belum diisi, ATAU
   - Excerpt/meta_description kosong (auto-generate dari content kalau kosong, jangan
     block, tapi kasih warning di form).
   Tampilkan pesan validasi yang jelas di Filament (pakai ->rules() atau ->afterStateUpdated
   dengan Notification, sesuaikan dengan pattern yang sudah ada di form ini).

3. Cek resources/views/blog/index.blade.php dan pastikan ada "empty state" yang didesain
   rapi (bukan cuma teks polos) untuk kondisi 0 artikel published — konsisten dengan gaya
   visual dark/red-accent yang sudah dipakai di blog/show.blade.php. Sertakan CTA halus
   mengarah ke halaman utama.

4. Cek resources/views/blog/show.blade.php — pastikan tidak ada elemen yang crash atau
   tampil aneh kalau content pendek, tidak ada thumbnail, atau tags kosong (beberapa
   sudah di-handle, tolong verifikasi dan lengkapi yang belum, termasuk kasus wordCount=0
   yang sekarang menampilkan "0 min read" secara janggal — beri minimum tampilan "1 min read"
   dan sembunyikan word count kalau 0).

5. Tulis Pest feature test baru di tests/Feature yang mengetes:
   - Artikel dengan status Draft/Pending Review TIDAK muncul di /blog.
   - Artikel valid dengan status Published MUNCUL di /blog dan /blog/{slug}.
   - Halaman /blog tampil benar (200 OK, tidak error) saat database artikel kosong.
   - Command articles:cleanup-test-data bekerja sesuai kriteria di atas (gunakan factory).

6. Jalankan `php artisan test` dan pastikan semua test lulus, termasuk test lama yang
   sudah ada di tests/Feature terkait artikel.

Jangan ubah struktur database (migration) di prompt ini kecuali benar-benar diperlukan.
Laporkan di akhir: file apa saja yang diubah, dan langkah manual yang perlu saya lakukan
di server production (misalnya menjalankan command baru lewat endpoint emergency-command).
```

**Kenapa ini prioritas #1:** ini akar masalah kenapa "blog gak bisa tampilkan apapun" — bukan karena sistemnya rusak total, tapi karena satu-satunya data yang ada memang rusak, dan belum ada pagar supaya kejadian serupa gak terulang begitu kamu mulai rutin publish artikel asli.

---

## PROMPT 2 — Tutup Celah "Self-Publish" di Client Area (PRIORITAS TINGGI, SECURITY)

```
Saya kerja di proyek Laravel 12 + Filament rbeverything.com (folder rbeverything-main).
Di app/Filament/ClientArea/Resources/ClientArticleResource.php, field
Select::make('status') punya opsi 'Draft', 'Pending Review', 'Scheduled', 'Published' —
dan user dengan role regular_user/premium bisa memilih 'Published' secara bebas, artinya
mereka bisa langsung menerbitkan artikel sendiri tanpa direview oleh admin/reviewer. Ini
melanggar alur kerja yang seharusnya: draft -> pending review -> (direview admin di
app/Filament/Resources/Articles/) -> published.

Tolong kerjakan:

1. Ubah Select status di ClientArticleResource.php supaya role non-admin (regular_user,
   premium) HANYA bisa memilih 'Draft' atau 'Pending Review' — sembunyikan/nonaktifkan
   opsi 'Scheduled' dan 'Published' untuk role tersebut. Gunakan pengecekan
   Auth::user()->hasAnyRole([...]) yang sudah dipakai di middleware
   app/Http/Middleware/EnsureClientRole.php sebagai referensi pola.

2. Tambahkan lapisan pertahanan kedua di level Model/Policy (jangan hanya andalkan UI
   Filament) — buat app/Policies/ArticlePolicy.php (belum ada saat ini, cek Policy lain
   seperti ProfilePolicy.php sebagai contoh pola) dengan method update() yang menolak
   perubahan status ke Published/Scheduled kalau user bukan admin/super_admin/reviewer.
   Daftarkan policy ini di AuthServiceProvider atau tempat policy lain didaftarkan.

3. Pastikan alur "Pending Review" tetap bisa di-approve oleh admin lewat
   app/Filament/Resources/Articles/ArticleResource.php — cek apakah sudah ada action
   approve/reject di sana; kalau belum, tambahkan tombol "Approve & Publish" dan
   "Reject" di ArticlesTable.php yang mengubah status + reviewer_id + reviewed_at.

4. Tulis Pest test baru: seorang user dengan role regular_user MENCOBA update artikel
   miliknya sendiri dengan status='Published' lewat request langsung (bukan lewat UI) —
   pastikan ini DITOLAK (baik oleh policy maupun tetap tersimpan sebagai Pending Review).
   Juga test bahwa admin/reviewer TETAP bisa publish seperti biasa.

5. Jalankan seluruh test suite Pest yang sudah ada (termasuk yang menyebut "workflow",
   "status transitions", "ownership scoping" di tests/Feature) untuk pastikan tidak ada
   yang regresi.

Laporkan file yang diubah/ditambah, dan jelaskan skenario yang sekarang diblokir vs yang
tetap diperbolehkan.
```

---

## PROMPT 3 — Samakan Kode Bahasa (Locale Keys) di Semua Form Artikel

```
Saya kerja di proyek Laravel 12 + Filament rbeverything.com (folder rbeverything-main).
Ada inkonsistensi kode bahasa (locale key) untuk field JSON translatable milik model
Article (title, content, excerpt, meta_title, meta_description, slug):

- app/Filament/Resources/Articles/Schemas/ArticleForm.php pakai key: en, id, ms, ja
- app/Filament/ClientArea/Resources/ClientArticleResource.php pakai key: id, my, en, jp
- app/Models/Article.php (di method slugExists dan resolveRouteBinding) pakai array
  campuran: ['id', 'my', 'en', 'jp', 'ms', 'ja'] — 6 key padahal semestinya cuma 4 bahasa.
- app/Http/Controllers/LocaleController.php mendukung locale: en, id, ja, ms, en-GB.

Standar yang benar dan harus dipakai KONSISTEN di seluruh kodebase adalah kode ISO 639-1:
en (English), id (Indonesia), ms (Melayu/Malay), ja (Japanese) — HAPUS penggunaan 'my'
dan 'jp' sepenuhnya, itu typo/salah kode.

Tolong kerjakan:

1. Cari SEMUA pemakaian locale key 'my' dan 'jp' di seluruh folder app/ dan resources/
   (grep -rn "'my'" dan grep -rn "'jp'"), termasuk di
   ClientArticleResource.php, Article.php (slugExists, resolveRouteBinding, boot()),
   dan file blade/view mana pun yang mereferensikan locale ini.

2. Ganti semua 'my' menjadi 'ms', dan semua 'jp' menjadi 'ja', supaya konsisten dengan
   ArticleForm.php dan LocaleController.php.

3. PENTING — migrasi data: buat migration/Artisan command satu-kali
   `php artisan articles:fix-locale-keys` yang membaca SEMUA artikel yang ada di database,
   dan untuk tiap kolom translatable (title, content, excerpt, meta_title,
   meta_description, slug): kalau ada key 'my' di JSON tapi key 'ms' kosong/tidak ada,
   pindahkan value dari 'my' ke 'ms' lalu hapus key 'my'. Lakukan hal sama untuk 'jp' -> 'ja'.
   Ini supaya data lama yang mungkin sudah tersimpan dengan key salah tidak hilang.

4. Setelah perbaikan kode, jalankan command tersebut di local/testing dulu dengan data
   dummy untuk memastikan migrasi data-nya benar sebelum saya jalankan di production lewat
   emergency-command endpoint.

5. Tulis Pest test yang membuat artikel lewat ClientArticleResource-style data (locale
   keys id/ms/en/ja) dan artikel lewat ArticleForm-style data, lalu pastikan KEDUANYA bisa
   diedit bolak-balik oleh admin maupun client tanpa kehilangan data translasi.

Laporkan semua file yang diubah dan tunjukkan hasil `grep -rn "'my'\|'jp'" app/ resources/`
setelah perbaikan untuk membuktikan sudah bersih (harus kosong).
```

---

## PROMPT 4 — Buat "Scheduled Publish" Benar-Benar Berfungsi

```
Saya kerja di proyek Laravel 12 + Filament rbeverything.com (folder rbeverything-main).
Status 'Scheduled' untuk model Article sudah ada di database (lihat migration
2026_04_03_172118_add_scheduled_to_articles_status_enum.php) dan sudah bisa dipilih di
form ClientArticleResource.php, TAPI tidak pernah otomatis berubah jadi 'Published' saat
published_at tercapai — karena:
1. app/Models/Article.php method scopePublished() cuma cek status='Published', tidak
   pernah mengecek published_at.
2. Tidak ada Artisan command/job yang menjalankan transisi Scheduled -> Published.
3. app/Filament/Resources/Articles/Schemas/ArticleForm.php (form ADMIN) malah tidak
   punya opsi status 'Scheduled' sama sekali — cuma Draft/Pending Review/Published.

Karena hosting shared cPanel ini tidak punya akses SSH/cron leluasa (lihat
routes/console.php untuk Schedule::command yang sudah ada, dan
app/Http/Controllers/System/EmergencyCommandController.php untuk pola command-tanpa-SSH
yang sudah dipakai proyek ini), solusinya jangan bergantung penuh ke Laravel Scheduler.

Tolong kerjakan:

1. Tambahkan opsi 'Scheduled' ke Select status di
   app/Filament/Resources/Articles/Schemas/ArticleForm.php supaya admin juga bisa
   menjadwalkan publish, konsisten dengan ClientArticleResource.php.

2. Buat Artisan command baru `app:publish-scheduled-articles` yang mencari semua Article
   dengan status='Scheduled' dan published_at <= now(), lalu ubah status jadi 'Published'.
   Tempatkan di app/Console/Commands/, ikuti pola PurgeOldAuthLogs.php yang sudah ada.

3. Daftarkan command ini di routes/console.php lewat Schedule::command(...)->everyMinute()
   SEBAGAI cadangan (kalau cron server ternyata berjalan).

4. TAMBAHKAN JUGA jalur tanpa-cron: masukkan 'app:publish-scheduled-articles' ke
   $allowedCommands di app/Http/Controllers/System/EmergencyCommandController.php, supaya
   saya bisa trigger manual via HTTP POST kapan saja tanpa perlu SSH/cron — dan supaya bisa
   dipanggil dari cron eksternal cPanel (Cron Jobs di panel hosting bisa memanggil URL
   pakai curl/wget tanpa perlu SSH, ini fitur bawaan cPanel).

5. Update scopePublished() di Article.php TIDAK perlu diubah (biarkan hanya cek status,
   karena command di atas yang menjaga status selalu akurat) — tapi tambahkan scope baru
   scopeVisiblePublic() kalau ada tempat lain yang butuh gabungan Published dan Scheduled
   yang sudah lewat waktunya, untuk jaga-jaga race condition.

6. Tulis Pest test: artikel Scheduled dengan published_at di masa lalu -> setelah command
   dijalankan, statusnya jadi Published dan muncul di /blog. Artikel Scheduled dengan
   published_at di masa depan -> tetap Scheduled, tidak muncul di /blog.

Laporkan cara saya harus setting Cron Jobs di cPanel Rumahweb untuk memanggil endpoint ini
tiap beberapa menit (kasih contoh URL dan curl command-nya, tanpa expose token asli).
```

---

## PROMPT 5 — Aktifkan Full-Text Search yang Sudah Dibangun Tapi Menganggur

```
Saya kerja di proyek Laravel 12 + Filament rbeverything.com (folder rbeverything-main).
Migration database/migrations/2026_04_06_063530_optimize_articles_table_performance.php
sudah membuat generated columns (title_en, title_id, content_en, content_id) dan FULLTEXT
index (articles_fulltext_index) di tabel articles. TAPI fitur search di
app/Http/Controllers/ArticleController.php method index() masih pakai query lama:

    $q->where('title->' . $locale, 'like', '%' . $search . '%')
      ->orWhere('content->' . $locale, 'like', '%' . $search . '%');

Ini tidak memanfaatkan FULLTEXT index sama sekali (LIKE dengan wildcard di depan tidak
bisa pakai index apapun).

Tolong kerjakan:

1. Ubah query search di ArticleController@index untuk pakai MySQL FULLTEXT search lewat
   whereFullText() Eloquent (Laravel 10+ punya method ini bawaan) terhadap kolom
   title_en/content_en atau title_id/content_id sesuai locale aktif — kalau locale aktif
   'ms' atau 'ja' (yang TIDAK punya generated column), fallback ke LIKE seperti sekarang
   supaya tidak error.

2. Tambahkan migration baru untuk membuat generated columns + tambahkan title_ms,
   content_ms, title_ja, content_ja juga (supaya keempat bahasa yang didukung situs ini
   semua kebagian FULLTEXT search, bukan cuma en/id), dan perluas FULLTEXT index-nya.

3. Test manual: cari kata yang cuma ada di salah satu artikel, pastikan hasilnya relevan
   dan urutan relevansi masuk akal (FULLTEXT MySQL punya scoring bawaan — pertimbangkan
   pakai orderByRaw(MATCH...AGAINST...) untuk urutan berdasarkan relevansi, bukan cuma
   latest()).

4. Tulis Pest test baru untuk search: cari kata yang ada di title, cari kata yang ada di
   content, cari kata yang tidak ada sama sekali (harus return kosong, bukan error).

5. Jalankan `php artisan test` pastikan semua lulus.

Laporkan query final yang dipakai dan benchmark sederhana (jumlah query/waktu) sebelum
vs sesudah kalau memungkinkan.
```

---

## PROMPT 6 — Perkuat Keamanan: Sanitasi Konten & Emergency Endpoint

```
Saya kerja di proyek Laravel 12 + Filament rbeverything.com (folder rbeverything-main).
Ada dua isu keamanan yang perlu dibereskan:

ISU A — Konten artikel tidak pernah disaring (purified) sebelum disimpan/ditampilkan.
Package mews/purifier sudah ada di composer.json tapi tidak dipanggil di manapun untuk
model Article (beda dengan Post yang punya app/Observers/PostObserver.php). Konten
RichEditor ditampilkan mentah di resources/views/blog/show.blade.php lewat
{!! $displayContent !!} tanpa sanitasi server-side.

ISU B — app/Http/Controllers/System/EmergencyCommandController.php punya risiko
self-lockout: dia baca token pakai env('EMERGENCY_ROUTE_TOKEN') langsung, bukan lewat
config(). Kalau command 'config:cache' (yang ada di daftar allowedCommands) pernah
dijalankan lewat endpoint ini, Laravel berhenti membaca file .env untuk request
berikutnya, sehingga env() akan selalu null dan endpoint ini terkunci permanen untuk
semua orang -- padahal ini satu-satunya jalur artisan tanpa SSH di hosting ini. Selain
itu, perbandingan token pakai !== biasa (bukan constant-time), dan tidak ada rate
limiting sama sekali di endpoint ini.

Tolong kerjakan:

ISU A:
1. Tambahkan config/purifier.php (publish default config Mews Purifier kalau belum ada).
2. Buat app/Observers/ArticleObserver.php dengan method saving() yang menjalankan
   Purifier::clean() pada setiap field translatable bertipe HTML (content, untuk semua
   locale yang terisi) sebelum disimpan ke database. Daftarkan observer ini di
   AppServiceProvider (cek bagaimana PostObserver didaftarkan sebagai referensi pola).
3. Pastikan hasil purify tetap mempertahankan format dasar (bold, italic, link, list,
   heading, image, table, blockquote, code) yang dipakai RichEditor Filament — konfigurasi
   allowed HTML tags Purifier supaya tidak merusak konten yang sudah ada.
4. Tulis Pest test: simpan artikel dengan content mengandung
   '<script>alert(1)</script>' -> pastikan setelah disimpan, tag script tersebut hilang.

ISU B:
1. Pindahkan pembacaan EMERGENCY_ROUTE_TOKEN dari env() langsung di controller ke
   config/app.php (tambahkan key baru, misalnya 'emergency_token' => env('EMERGENCY_ROUTE_TOKEN')),
   lalu di controller baca lewat config('app.emergency_token'). Ini supaya value-nya
   tetap terbaca dengan benar meskipun config:cache pernah dijalankan.
2. Ganti perbandingan token dari $token !== $validToken menjadi
   !hash_equals((string) $validToken, (string) $token) untuk constant-time comparison.
3. Tambahkan rate limiting di route emergency-command (routes/web.php) menggunakan
   throttle middleware Laravel, misal ->middleware('throttle:5,1') (maksimal 5 request
   per menit per IP).
4. Tambahkan logging khusus setiap kali endpoint ini dipanggil dengan token SALAH
   (bukan cuma yang sukses seperti sekarang), supaya saya bisa pantau percobaan yang
   mencurigakan lewat storage/logs.
5. Jalankan php artisan config:clear lalu test manual endpoint ini masih berfungsi
   dengan token yang benar, dan tetap 401 dengan token salah.

Laporkan semua file yang diubah, dan ingatkan saya untuk generate ulang
EMERGENCY_ROUTE_TOKEN di .env production kalau perlu setelah perubahan ini di-deploy.
```

---

## PROMPT 7 — Perbaiki SEO: APP_URL, Sitemap Auto-Update, robots.txt

```
Saya kerja di proyek Laravel 12 + Filament rbeverything.com (folder rbeverything-main).
Saya sudah cek https://rbeverything.com/sitemap.xml dan isinya pakai URL "http://localhost"
(bukan domain asli), cuma berisi 2 entry statis (home + /blog), tidak ada artikel/profil
sama sekali, dan lastmod-nya sudah 5 bulan lalu. Ini karena:
1. APP_URL di .env production kemungkinan besar masih nilai default (perlu saya cek dan
   perbaiki manual di server, tapi kamu bantu saya siapkan checklist verifikasinya).
2. app/Console/Commands/GenerateSitemap.php dijadwalkan lewat Laravel Scheduler
   (routes/console.php), yang butuh cron schedule:run tiap menit — kemungkinan besar
   tidak ada di hosting shared ini.
3. 'app:generate-sitemap' tidak ada di allowedCommands
   app/Http/Controllers/System/EmergencyCommandController.php jadi tidak bisa di-trigger
   manual lewat HTTP.
4. public/robots.txt men-disallow "/admin/" padahal path panel admin sebenarnya adalah
   "/rbdashboard" (lihat app/Providers/Filament/AdminPanelProvider.php) — jadi path admin
   yang asli tidak terlindungi dari crawler.

Tolong kerjakan:

1. Tambahkan 'app:generate-sitemap' ke $allowedCommands di EmergencyCommandController.php
   supaya saya bisa trigger manual kapan saja lewat HTTP tanpa SSH.

2. SOLUSI UTAMA (lebih andal daripada cron di shared hosting): buat
   app/Observers/ArticleObserver.php (kalau belum dibuat dari prompt sebelumnya, tambahkan
   di observer yang sama) dan app/Observers/ProfileObserver.php dengan method saved() dan
   deleted() yang otomatis memanggil Artisan::call('app:generate-sitemap') setiap kali ada
   artikel/profil publik yang dibuat, diubah, atau dihapus. Supaya sitemap.xml selalu
   ter-update seketika tanpa bergantung sama sekali pada cron server. Pertimbangkan pakai
   queue (ShouldQueue) kalau proyek ini sudah pakai queue worker; kalau belum ada queue
   worker yang jalan di hosting ini, jalankan synchronous saja (dispatch langsung, jangan
   ->queue()) supaya tetap jalan tanpa perlu queue:work yang butuh proses long-running.

3. Perbaiki public/robots.txt: ganti "Disallow: /admin/" menjadi "Disallow: /rbdashboard/"
   dan pastikan "Disallow: /client-area/" tetap ada. Tambahkan juga "Disallow: /system/"
   untuk melindungi endpoint emergency-command dari crawler (meski sudah POST-only, tidak
   ada salahnya).

4. Buatkan saya artisan command `php artisan app:check-seo-config` yang mengecek dan
   menampilkan warning kalau: APP_URL masih mengandung "localhost", APP_DEBUG bernilai
   true, atau sitemap.xml belum pernah di-generate ulang dalam 7 hari terakhir. Ini supaya
   saya bisa jalankan command ini via emergency-command endpoint kapan saja untuk audit
   cepat tanpa perlu akses SSH ke server.

5. Setelah semua di atas jalan, generate ulang sitemap dan tunjukkan saya cara verifikasi
   isinya sudah benar (pakai domain asli, mengandung semua artikel published).

PENTING: Setelah kode ini di-deploy, saya sendiri yang perlu login ke cPanel dan
mengecek/mengubah nilai APP_URL di file .env production menjadi https://rbeverything.com
(tanpa trailing slash) — tolong ingatkan saya di laporan akhir kamu, karena ini file .env
tidak ikut ke git jadi tidak bisa kamu ubah lewat kode.
```

---

## PROMPT 8 — Housekeeping Repo & Sinkronisasi Branch (Prioritas Rendah, tapi Mudah)

```
Saya kerja di proyek Laravel 12 + Filament rbeverything.com (folder rbeverything-main).
Ada beberapa file debug/scratch yang ke-commit ke repo git (bukan cuma ada di lokal):
add_unique.php, cleanup.php, full_cleanup.php, check_table.php, dump.txt, dump2.txt,
error.txt, error2.txt, error_log.txt, login.html, test.php, test_compiled.php,
blog_curl.txt, migrate_output.txt, versions.json, sitemap-error.log, sitemap-error2.log.
Semua ini kelihatan seperti sisa debugging manual di masa lalu.

Juga, branch 'sandbox' (staging) tertinggal 11 commit dari branch 'main' (production) —
terakhir sinkron 2 April 2026, sedangkan main sudah sampai 8 April 2026. Jadi
web-sbox.rbeverything.com saat ini TIDAK mencerminkan kondisi main terkini.

Tolong kerjakan:

1. Review isi tiap file yang saya sebutkan di atas — pastikan tidak ada file yang masih
   dipakai/direferensikan oleh kode aplikasi (grep referensinya dulu sebelum hapus).
   Kalau memang tidak dipakai, hapus dari repo (git rm) dan commit terpisah dengan pesan
   yang jelas, misalnya "chore: remove stale debug/scratch files from repo root".

2. Tambahkan pattern file-file semacam ini ke .gitignore supaya tidak ke-commit lagi di
   masa depan: *.txt di root (kecuali README-related), test_compiled.php, dump*.txt,
   error*.txt/log, versions.json, login.html, dan skrip PHP one-off seperti
   *_cleanup.php/check_*.php kalau memang pola penamaannya konsisten begitu.

3. JANGAN merge main ke sandbox secara otomatis dulu — tampilkan dulu ke saya ringkasan
   11 commit yang ada di main tapi belum ada di sandbox (git log sandbox..main), supaya
   saya review dulu sebelum kamu jalankan git merge main ke branch sandbox dan push.

Laporkan daftar file yang dihapus, isi .gitignore yang ditambahkan, dan ringkasan 11
commit yang perlu saya approve sebelum sandbox disinkronkan.
```

---

## Catatan Sebelum Push ke Hosting

Setelah PROMPT 1–8 (atau minimal PROMPT 1, 2, 6, 7 yang paling penting) selesai dikerjakan dan lolos test di branch `sandbox`:

1. Cek dulu di `web-sbox.rbeverything.com` — buka `/blog`, coba search, coba buka satu artikel penuh, cek tampilan di HP (mobile) juga.
2. Baru merge `sandbox` ke `main` supaya GitHub Actions jalanin test sekali lagi lalu deploy otomatis ke production.
3. Setelah deploy production selesai, jalankan migration baru (kalau prompt di atas menambahkan migration) lewat endpoint `/system/emergency-command` dengan command `migrate`.
4. Cek manual nilai `APP_URL` dan `APP_DEBUG` di `.env` production lewat cPanel File Manager (lihat PROMPT 7) — ini satu-satunya langkah yang harus kamu lakukan manual, gak bisa lewat kode/CI.
5. Login ke `/rbdashboard`, mulai publish artikel-artikel asli kamu satu per satu, cek tiap satu tayang dengan benar di `/blog` sebelum lanjut ke artikel berikutnya.
