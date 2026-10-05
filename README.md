# Event Attendance

Aplikasi absensi event berbasis Laravel. Admin mengelola event, peserta, dan kehadiran. Peserta bisa check-in sendiri lewat tautan publik (QR, NIK, atau nama).

README ini untuk **setup di laptop baru**, dari clone sampai aplikasi bisa dibuka di browser.

---

## Yang akan kamu dapatkan setelah setup

| Halaman | URL | Keterangan |
| --- | --- | --- |
| Login admin | http://localhost:8000/login | Masuk ke dashboard |
| Daftar event | http://localhost:8000/ | Redirect ke daftar event setelah login |
| Check-in publik | `/e/{id}/{slug}` | Tanpa login, untuk peserta |

Akun admin default **hanya untuk environment `local`**:

- Email: `admin@example.com`
- Password: `password`

Kalau `APP_ENV` bukan `local`, isi `ADMIN_EMAIL` dan `ADMIN_PASSWORD` di `.env` sebelum `php artisan db:seed`.

---

## 1. Prasyarat (install dulu sebelum clone)

Pastikan versi di bawah terpenuhi. Cek di terminal:

```bash
php -v          # PHP 8.3 atau lebih baru (disarankan 8.4)
composer -V     # Composer 2.x
node -v         # Node.js 20 atau lebih baru (disarankan 22/24)
npm -v
psql --version  # PostgreSQL 14 atau lebih baru
git --version
```

Aplikasi memakai **PostgreSQL**, bukan SQLite, untuk development.

Ekstensi PHP yang wajib:

- `pdo_pgsql` / `pgsql` — koneksi database
- `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`, `curl`, `zip`
- `gd` — generate QR code (PNG)

Cek ekstensi:

```bash
php -m | grep -Ei 'pdo_pgsql|pgsql|gd|mbstring|xml|curl|zip'
```

Kalau `pdo_pgsql` atau `gd` tidak muncul, install dulu (lihat bagian OS di bawah).

### 1.1 Linux (Ubuntu / Debian)

```bash
sudo apt update
sudo apt install -y git unzip curl
sudo apt install -y php php-cli php-mbstring php-xml php-curl php-zip php-pgsql php-gd php-bcmath
sudo apt install -y postgresql postgresql-contrib
```

Composer:

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

Node.js (contoh LTS 22 via NodeSource, atau pakai [nvm](https://github.com/nvm-sh/nvm)):

```bash
# nvm
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.40.3/install.sh | bash
# tutup dan buka terminal baru, lalu:
nvm install 22
nvm use 22
```

Jalankan ulang service PostgreSQL:

```bash
sudo systemctl enable --now postgresql
```

### 1.2 macOS

Install [Homebrew](https://brew.sh) dulu, lalu:

```bash
brew install php composer node postgresql@16 git
brew services start postgresql@16
```

Pastikan `php`, `psql`, dan `node` ada di `PATH` (Homebrew biasanya memberi instruksi setelah install).

### 1.3 Windows

Pilih salah satu alur yang nyaman:

1. **Laravel Herd** (paling sederhana): install [Herd](https://herd.laravel.com), lalu install PostgreSQL terpisah ([installer resmi](https://www.postgresql.org/download/windows/) atau [Postgres.app](https://postgresapp.com) tidak untuk Windows — di Windows pakai installer PostgreSQL).
2. **php.new + Node + PostgreSQL**:
   - PHP & Composer (PowerShell, jalankan sebagai Administrator kalau perlu):

     ```powershell
     Set-ExecutionPolicy Bypass -Scope Process -Force
     iex ((New-Object System.Net.WebClient).DownloadString('https://php.new/install/windows/8.5'))
     ```

     Tutup semua terminal, buka lagi, lalu cek `php -v` dan `composer -V`.
   - Node.js: [https://nodejs.org](https://nodejs.org) (LTS).
   - Git: [https://git-scm.com](https://git-scm.com).
   - PostgreSQL: installer dari [postgresql.org](https://www.postgresql.org/download/windows/). Ingat **password user `postgres`** yang kamu set saat install.

Aktifkan ekstensi `pgsql` dan `gd` di `php.ini` jika belum (`extension=pgsql`, `extension=pdo_pgsql`, `extension=gd`).

---

## 2. Clone repository

Ganti URL dengan remote Git kamu:

```bash
git clone <URL-REPO> event-attendance
cd event-attendance
```

Kalau proyek sudah ada di folder (copy USB, zip, dsb.) cukup `cd` ke folder itu. Yang penting isi folder ada `artisan`, `composer.json`, dan `.env.example`.

---

## 3. Siapkan database PostgreSQL

Buat database kosong bernama `event_attendance` (nama ini sesuai `.env.example`).

Masuk ke `psql` sebagai superuser:

**Linux:**

```bash
sudo -u postgres psql
```

**macOS / Windows** (kalau user OS sudah bisa `psql`):

```bash
psql -U postgres
```

Lalu di prompt `psql`:

```sql
CREATE DATABASE event_attendance;
\q
```

Kalau user `postgres` belum punya password di Linux (auth `peer`), set dulu:

```sql
ALTER USER postgres PASSWORD 'password_yang_kamu_mau';
```

Catat: **host**, **port** (default `5432`), **nama database**, **username**, **password**. Nilai ini nanti diisi di `.env`.

Tes koneksi dari luar `psql`:

```bash
psql -h 127.0.0.1 -U postgres -d event_attendance -c '\conninfo'
```

Kalau gagal, biasanya salah password, PostgreSQL belum jalan, atau `pg_hba.conf` menolak koneksi TCP. Pastikan service PostgreSQL running.

---

## 4. File environment

Di root proyek:

```bash
cp .env.example .env
```

Windows (PowerShell):

```powershell
Copy-Item .env.example .env
```

Buka `.env` dan isi bagian database. Contoh lokal:

```env
APP_NAME="Event Attendance"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

APP_LOCALE=id
APP_TIMEZONE=Asia/Jakarta

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=event_attendance
DB_USERNAME=postgres
DB_PASSWORD=password_postgres_kamu
DB_SSLMODE=prefer
```

Jangan commit file `.env`. `APP_KEY` masih kosong; akan diisi di langkah berikutnya.

Opsional, ubah akun admin yang akan dibuat seeder:

```env
ADMIN_NAME=Administrator
ADMIN_EMAIL=admin@example.com
ADMIN_PASSWORD=
```

Di `local`, kalau `ADMIN_PASSWORD` kosong seeder memakai password `password`. Di production, keduanya wajib diisi.

---

## 5. Install dependensi PHP & frontend

Masih di root proyek:

```bash
composer install
```

Kalau error `ext-pgsql` / `ext-gd` missing, kembali ke langkah 1 dan install ekstensi, lalu `composer install` lagi.

```bash
npm install
```

---

## 6. Kunci aplikasi, migrasi, dan data awal

```bash
php artisan key:generate
```

Perintah itu mengisi `APP_KEY` di `.env`.

Jalankan migrasi (membuat tabel users, events, participants, attendances, cache, session, jobs):

```bash
php artisan migrate
```

Kalau error koneksi (`SQLSTATE[08006]` / `could not connect`), cek `DB_*` di `.env` dan pastikan PostgreSQL running. Setelah mengubah `.env`:

```bash
php artisan config:clear
```

Isi akun admin + data demo (satu event + 20 peserta, hanya jika `APP_ENV=local` dan belum ada event):

```bash
php artisan db:seed
```

Permission folder storage & cache (Linux/macOS, kalau page error permission):

```bash
chmod -R ug+rwx storage bootstrap/cache
```

---

## 7. Jalankan aplikasi (development)

Buka **dua terminal** di folder proyek, atau pakai satu perintah gabungan.

### Cara A — satu perintah (disarankan)

```bash
composer run dev
```

Ini menjalankan `php artisan dev` (server Laravel + Vite). Biarkan terminal tetap terbuka.

### Cara B — dua terminal

Terminal 1 — backend:

```bash
php artisan serve
```

Terminal 2 — Vite (hot reload CSS/JS):

```bash
npm run dev
```

Buka browser: **http://localhost:8000/login**

Login dengan `admin@example.com` / `password` (atau nilai `ADMIN_*` yang kamu set).

Untuk check-in publik, buka event di dashboard lalu pakai tautan publik yang ditampilkan di halaman event.

Hentikan server dengan `Ctrl+C` di terminal yang masih running.

---

## 8. Perintah yang sering dipakai setelah hari pertama

| Perintah | Kegunaan |
| --- | --- |
| `composer run dev` | Jalankan server + Vite |
| `php artisan migrate` | Terapkan migrasi baru |
| `php artisan migrate:fresh --seed` | **Hapus semua data**, migrasi ulang, seed lagi |
| `php artisan db:seed` | Buat/update admin (dan demo event jika belum ada) |
| `php artisan test` | Jalankan tes (pakai SQLite in-memory, tidak perlu Postgres) |
| `npm run build` | Build aset produksi ke `public/build` |
| `php artisan config:clear` | Bersihkan cache config setelah ubah `.env` |

`migrate:fresh` menghancurkan data. Jangan dipakai di database yang berisi data nyata.

---

## 9. Alur cepat (kalau prasyarat sudah terpasang)

```bash
git clone <URL-REPO> event-attendance
cd event-attendance
cp .env.example .env
# edit .env: DB_PASSWORD (dan DB_* lain jika perlu)
composer install
npm install
php artisan key:generate
php artisan migrate
php artisan db:seed
composer run dev
```

Lalu buka http://localhost:8000/login.

Ada script `composer run setup` yang menggabungkan `composer install`, copy `.env`, `key:generate`, `migrate`, `npm install`, dan `npm run build`. Script itu **tidak** mengisi password database dan **tidak** menjalankan seeder. Tetap edit `.env` dulu sebelum migrate.

---

## 10. Troubleshooting

**`could not find driver` / `could not connect to server`**  
PHP belum punya `pdo_pgsql`, atau PostgreSQL tidak listening di `127.0.0.1:5432`. Cek `php -m` dan `sudo systemctl status postgresql` (Linux).

**`SQLSTATE[28P01] password authentication failed`**  
`DB_USERNAME` / `DB_PASSWORD` tidak cocok. Samakan dengan user PostgreSQL.

**`SQLSTATE[3D000] database "event_attendance" does not exist`**  
Database belum dibuat. Ulangi langkah 3.

**Halaman tanpa CSS / tampilan rusak**  
Vite tidak running. Jalankan `npm run dev` bersama `php artisan serve`, atau `composer run dev`. Alternatif sekali-jalan: `npm run build`.

**QR code tidak muncul / error image**  
Ekstensi `gd` belum aktif. Install `php-gd` lalu restart terminal.

**`Your APP_KEY is empty` / 500 setelah clone**  
Jalankan `php artisan key:generate`.

**Login gagal setelah seed**  
Pastikan `APP_ENV=local` atau `ADMIN_PASSWORD` di `.env` sesuai yang dipakai saat login. Seed lagi: `php artisan db:seed`.

**Port 8000 sudah dipakai**

```bash
php artisan serve --port=8001
```

Ubah juga `APP_URL` di `.env` agar sama.

---

## Stack

- Laravel 13, PHP ^8.3
- PostgreSQL
- Blade + Tailwind CSS 4 + Vite 8
- QR: `simplesoftwareio/simple-qrcode`

## Lisensi

Proyek ini memakai kerangka Laravel yang dilisensikan di bawah [MIT license](https://opensource.org/licenses/MIT).
