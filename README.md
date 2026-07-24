# AmikomEventHub — Multi-Tenant SaaS Ticketing Platform

AmikomEventHub adalah platform ticketing marketplace berbasis Laravel yang telah ditransformasi menjadi **SaaS Multi-Tenant**, mendukung **Google SSO Login**, **Sistem Rating & Review**, serta penanganan khusus **Reserved Ticket (Race Condition)** dan **Bypass Event Gratis (Free Events)**.

---

## 1. Fitur-Fitur Utama yang Diimplementasikan

### 1.1 Soal 1 — Fitur Wajib (60%)
- **SSO Login Instant via Google (`laravel/socialite`)**
  - Registrasi & login 1-klik dengan Google OAuth.
  - Linking akun otomatis berdasarkan email jika user sudah terdaftar secara manual (tanpa mengabaikan/mengubah role eksisting).
  - Preservasi `intended_url` untuk langsung mengarahkan user kembali ke halaman checkout setelah SSO.
  - Mode Dev Fallback untuk testing lokal tanpa kredensial Google aktif.
- **Sistem Rating & Review Bintang (1-5)**
  - Review & rating hanya dapat diberikan oleh buyer yang memiliki transaksi sah (`paid`/`success`/`settlement`/`free_claimed`).
  - Strict validation: review hanya diizinkan untuk event yang tanggal pelaksanaannya sudah lewat (`event.date < now()`).
  - Constraint unik 1 user hanya 1 ulasan per event (`unique(user_id, event_id)`).
  - Tampilan rating rata-rata (avg rating), jumlah review, dan daftar ulasan terpaginasi di profil publik organizer (`/organizer/{slug}`).
- **Arsitektur Multi-Tenant (Multi-Organisasi)**
  - Skema database terisolasi berbasis `organization_id` pada tabel `users`, `events`, `transactions`, dan `reviews`.
  - Flow pendaftaran organizer mandiri (`/organizer/register`) berstatus `pending`.
  - Halaman Superadmin untuk verifikasi (Approve, Reject, Suspend) organizer.
  - Laravel **Global Scope (`TenantScope`)** & **Authorization Policy (`EventPolicy`)** untuk menjamin isolasi data mutlak antar-tenant.

### 1.2 Soal 2 — Fitur Pilihan (40%)
> **Pilihan Fitur:**
> 1. **5.1 Reserved Ticket (Race Condition Protection)**
> 2. **5.2 Bypass Transaksi Acara Gratis (Free Events)**
>
> **Alasan Pemilihan:** Kedua fitur ini menyempurnakan alur checkout transaksi dari sisi integritas stok dan performa. *Reserved Ticket* menjamin tidak terjadinya *overselling* akibat race condition saat pembeli berebutan tiket, sedangkan *Bypass Event Gratis* mengeliminasi request sia-sia ke Midtrans untuk transaksi senilai Rp 0.

- **5.1 Reserved Ticket (Pessimistic Locking & Expiration Scheduler)**
  - Stok tiket ditahan (`reserved_count`) saat user klik tombol Checkout menggunakan DB Transaction & `lockForUpdate()`.
  - Menghitung stok tersedia secara presisi: `quota - reserved_count - sold_count`.
  - Menolak transaksi jika stok habis tanpa pernah membiarkan kuota menjadi minus.
  - Command otomatis `php artisan app:release-expired-reservations` (diskedulkan per menit) untuk merilis kembali stok jika transaksi tidak dibayar dalam batas waktu (15 menit).
- **5.2 Bypass Transaksi untuk Event Gratis (Price = 0)**
  - Memeriksa jika `price == 0` atau `is_free == true`.
  - Melewati pemanggilan API Midtrans Snap sepenuhnya.
  - Mengubah status transaksi langsung menjadi `free_claimed`, menambah `sold_count`, dan mengarahkan pembeli langsung ke halaman E-Ticket.

---

## 2. Setup & Konfigurasi Lingkungan (`.env`)

### Langkah Installasi:
1. Clone / buka repository pada environment lokal Anda.
2. Jalankan perintah installasi dependency:
   ```bash
   composer install
   ```
3. Duplikasi file `.env.example` ke `.env`:
   ```bash
   cp .env.example .env
   ```
4. Generate Application Key:
   ```bash
   php artisan key:generate
   ```
5. Sesuaikan variabel berikut pada file `.env`:
   ```env
   APP_URL=http://localhost:8000

   # Database (SQLite / MySQL)
   DB_CONNECTION=sqlite
   DB_DATABASE=database/database.sqlite

   # Google Socialite OAuth
   GOOGLE_CLIENT_ID=your-google-client-id
   GOOGLE_CLIENT_SECRET=your-google-client-secret
   GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"

   # Midtrans Gateway
   MIDTRANS_MERCHANT_ID="your-merchant-id"
   MIDTRANS_SERVER_KEY="your-server-key"
   MIDTRANS_CLIENT_KEY="your-client-key"
   MIDTRANS_IS_PRODUCTION=false
   ```
6. Jalankan migrasi dan seeder awal:
   ```bash
   php artisan migrate:fresh --seed
   ```

---

## 3. Akun Testing Default (dari DatabaseSeeder)

- **Superadmin**: `admin@amikom.ac.id` / `password`
- **Organizer (Approved - BEM Amikom)**: `bem@amikom.ac.id` / `password`
- **Organizer (Pending - HIMIKA Amikom)**: `himika@amikom.ac.id` / `password`
- **Buyer**: `buyer@amikom.ac.id` / `password`

---

## 4. Panduan Testing / Langkah Demo Fitur

### A. Testing Fitur 1: Google SSO Login
1. Buka rute `/login`.
2. Klik tombol **"Continue with Google"** (atau gunakan link mock dev `(Mode Dev: Klik untuk Mock Google Login Instant)` jika tidak menyetel Google API Key).
3. Sistem akan otomatis meregistrasikan/menghubungkan akun dan login sebagai role `buyer`.

### B. Testing Fitur 2: Rating & Review
1. Login sebagai `buyer@amikom.ac.id`.
2. Buka rute `/my-ticket`.
3. Pada pesanan event yang sudah lampau ("AI & FUTURE TECH SUMMIT 2025"), klik **"Beri Ulasan"**.
4. Isi rating (1-5 bintang) dan komentar, lalu kirim.
5. Buka profil publik organizer di `/organizer/bem-amikom` untuk melihat ulasan dan rating rata-rata yang diperbarui secara real-time.

### C. Testing Fitur 3: Multi-Tenant Architecture & Data Isolation
1. **Approval Flow**: Login sebagai `himika@amikom.ac.id` (status `pending`). Coba buat event baru — sistem akan memblokir pendaftaran event sampai disetujui Superadmin. Login sebagai `admin@amikom.ac.id` di `/admin/organizers` lalu klik **Approve**.
2. **Isolasi Tenant**: Login sebagai Organizer BEM Amikom (`bem@amikom.ac.id`). Buka `/admin/events`. Data event yang tampil hanyalah milik BEM. Jika mencoba mengakses URL edit event milik tenant lain secara langsung, sistem akan merespon dengan `403 Forbidden`.

### D. Testing Fitur 5.1: Reserved Ticket (Race Condition)
1. Pilih event berbayar pada halaman utama.
2. Lakukan checkout. Sistem akan menahan stok (`reserved_count +1`) selama 15 menit.
3. Jalankan command rilis reservasi expired manual untuk pengujian:
   ```bash
   php artisan app:release-expired-reservations
   ```

### E. Testing Fitur 5.2: Free Event Bypass
1. Pilih event **"Hackathon Gratis - Unleash Your Code"** (Harga Rp 0).
2. Klik **Beli Tiket / Klaim Tiket Gratis**.
3. Isi data pemesan lalu klik **Klaim Tiket Gratis**.
4. Pembelian langsung sukses (status `free_claimed`) tanpa membuka pop-up Midtrans, dan E-Ticket dengan QR code langsung diterbitkan.

---

## 5. Pengujian Otomatis (Feature & Unit Tests)

Seluruh fitur di atas telah diuji menggunakan suite pengujian otomatis Laravel PHPUnit/Pest:

```bash
php artisan test
```

Semua 9 test case dipastikan berstatus **PASSING**.
