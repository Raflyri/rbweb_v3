# Mengaktifkan Midtrans

Kode Midtrans sudah terpasang penuh tapi **sengaja dimatikan**. Dokumen ini
langkah-langkah menyalakannya begitu akun bisnis Midtrans kamu clear.

Selama `MIDTRANS_IS_ACTIVE=false`:

- `PaymentGatewayResolver` tidak akan pernah mengembalikan `MidtransGateway`,
  **apa pun** isi `services.payment.active_gateway`;
- endpoint webhook `POST /payment/midtrans/notification` menjawab 404 untuk
  semua request, jadi tidak ada yang bisa menandai pesanan lunas dari luar;
- pembeli tetap memakai transfer bank manual seperti sekarang.

---

## Langkah aktivasi

### 1. Isi tiga nilai di `.env`

Ambil server key dan client key dari dashboard Midtrans
(Settings → Access Keys). Pastikan mengambil dari environment yang benar —
kunci sandbox tidak berlaku di production dan sebaliknya.

```env
MIDTRANS_SERVER_KEY=SB-Mid-server-xxxxxxxxxxxxxxxx
MIDTRANS_CLIENT_KEY=SB-Mid-client-xxxxxxxxxxxxxxxx
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_IS_ACTIVE=false
```

Biarkan `MIDTRANS_IS_ACTIVE=false` dulu — langkah 2 menguji kuncinya tanpa
menyalakan apa pun.

### 2. Uji koneksinya dari terminal

```bash
php artisan midtrans:test-connection
```

Perintah ini menanyakan status sebuah transaksi yang tidak mungkin ada, jadi
aman dijalankan kapan pun, termasuk di production. Yang dicari:

| Hasil | Artinya |
|---|---|
| ✅ Koneksi berhasil | Server key diterima. Lanjut. |
| ❌ Server key ditolak (401) | Kunci salah, atau kunci sandbox dipakai di production (atau sebaliknya). |
| ❌ Gagal menghubungi | Masalah jaringan/firewall dari server ke `api.midtrans.com`. |

Kalau hosting memblokir koneksi keluar, ini tempat masalah itu ketahuan —
sebelum ada pembeli sungguhan yang menunggu.

### 3. Daftarkan URL notifikasi di dashboard Midtrans

Settings → Configuration → **Payment Notification URL**:

```
https://rbeverything.com/payment/midtrans/notification
```

Untuk sandbox, pakai domain sandbox kamu (`https://web-sbox.rbeverything.com/...`).

Endpoint ini dikecualikan dari CSRF (dipanggil server Midtrans, bukan browser),
dibatasi 60 request per menit, dan memverifikasi signature SHA512 sebelum
membaca apa pun dari isi request.

### 4. Nyalakan

```env
MIDTRANS_IS_ACTIVE=true
```

Lalu bersihkan cache config di server:

```bash
php artisan config:clear
```

Setelah ini Midtrans **siap** tapi belum otomatis dipakai — lihat langkah 5.

### 5. Pilih apakah Midtrans jadi metode utama

Di [`config/services.php`](../config/services.php):

```php
'payment' => [
    'active_gateway' => 'midtrans',   // sebelumnya 'manual_transfer'
],
```

Dua pilihan yang masuk akal:

- **Ganti ke `'midtrans'`** — semua pesanan baru diarahkan ke Snap. Transfer
  manual berhenti ditawarkan (pesanan lama yang sudah menunggu bukti transfer
  tetap bisa diverifikasi seperti biasa dari panel admin).
- **Biarkan `'manual_transfer'`** — Midtrans tetap non-aktif sebagai metode
  pembeli meskipun `MIDTRANS_IS_ACTIVE=true`. Berguna kalau kamu mau menguji
  koneksi dan webhook dulu tanpa mengubah pengalaman pembeli.

### 6. Uji satu transaksi sungguhan di sandbox

Buat pesanan uji, bayar dengan
[kartu uji Midtrans](https://docs.midtrans.com/docs/testing-payment-on-sandbox),
lalu pastikan status pesanan berubah jadi **Lunas** di `/rbdashboard` dan
pembeli menerima email konfirmasi. Kalau statusnya tidak berubah, periksa
`storage/logs/laravel.log` — semua penolakan webhook ditulis di sana beserta
alasannya.

---

## Cara mematikannya lagi

Set `MIDTRANS_IS_ACTIVE=false` lalu `php artisan config:clear`. Sistem langsung
kembali ke transfer manual, dan webhook berhenti menjawab. Tidak perlu deploy
ulang dan tidak ada data yang hilang.

---

## Yang perlu diketahui soal perilakunya

**Status Midtrans dipetakan seperti ini:**

| `transaction_status` | Akibatnya di pesanan |
|---|---|
| `settlement` | Lunas, `paid_at` diisi, pembeli dapat email |
| `capture` + `fraud_status=accept` | Sama seperti settlement |
| `capture` + `fraud_status=challenge` | Menunggu Verifikasi — **tidak** dianggap lunas, perlu dilihat manusia |
| `capture` + `fraud_status=deny` | Gagal |
| `pending` | Tidak diubah |
| `expire`, `cancel`, `deny` | Gagal, alasannya dicatat |
| `refund`, `partial_refund` | Pembayaran ditandai dibatalkan |
| lainnya | Tidak diubah, hanya dicatat di log |

**Status pesanan (`baru`/`diproses`/`selesai`) tidak pernah diubah otomatis
oleh webhook.** Pembayaran gagal bukan berarti pesanan batal — itu keputusan
kamu, lewat tombol di panel admin.

**Notifikasi ganda aman.** Midtrans mengulang kirim sampai dapat balasan 200,
jadi satu settlement bisa datang berkali-kali. Pesanan yang sudah lunas
dilewati, jadi pembeli tidak menerima dua email untuk satu pembayaran, dan
`paid_at` tidak bergeser.

**Kalau Midtrans sedang bermasalah**, halaman pesanan menampilkan kalimat
"Pembayaran online sedang tidak tersedia, silakan hubungi kami" beserta nomor
pesanan — bukan halaman error 500. Detail teknisnya masuk ke log.

---

## Jaminan yang diuji otomatis

Berkas [`tests/Feature/PaymentMidtransTest.php`](../tests/Feature/PaymentMidtransTest.php)
mengunci hal-hal ini, jadi tidak bisa rusak diam-diam:

- resolver tetap mengembalikan transfer manual selama `MIDTRANS_IS_ACTIVE=false`,
  bahkan ketika `active_gateway` sudah di-set `'midtrans'`;
- server key kosong dihitung sebagai non-aktif — ini menutup celah paling
  berbahaya, karena signature adalah hash yang diakhiri server key, dan dengan
  key kosong siapa pun bisa menghitung signature yang "valid";
- webhook menolak signature yang salah (403) dan tidak menjawab sama sekali
  saat non-aktif (404);
- settlement berulang hanya menghasilkan satu email.
