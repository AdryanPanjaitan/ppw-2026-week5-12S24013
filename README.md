# SIPUS-Del Perpustakaan Kampus

## Identitas dan konteks

**Pembangunan Sistem Informasi Perpustakaan Kampus Del (SIPUS-Del): Implementasi Arsitektur MVC Lengkap, Relasi Eloquent Multi-Entitas, Validasi Form Server-Side, dan Audit Kueri Kinerja pada Laravel 13.x**

- Penulis: Adryan Julianto Panjaitan
- NIM: 12S24013
- Mata kuliah: Pemrograman & Pengujian Web (12S3101), Institut Teknologi Del
- Nama proyek: `sipus-del`
- Nama repositori: `ppw-2026-week5-12S24013`

## Fitur dan teknologi

SIPUS-Del menyediakan CRUD buku/kategori, relasi Eloquent, pagination 10 data, eager loading, flash message, konfirmasi hapus, validasi server-side, factory/seeder, feature test, dan audit SQL.

Stack: Laravel 13.x, PHP 8.3, SQLite, Blade, Bootstrap 5.3.3 CDN, Vite, dan Tailwind Vite plugin. Tema Del Royal Purple: `#4C1D95`, hover `#3B0764`.

## Struktur MVC

```text
app/Models/{Buku,Kategori}.php
app/Http/Controllers/{BukuController,KategoriController}.php
resources/views/components/layout.blade.php
resources/views/buku/{index,create,edit,show,_form}.blade.php
resources/views/kategori/{index,create,edit,show,_form}.blade.php
database/{migrations,factories,seeders}
routes/web.php
```

```mermaid
erDiagram
    KATEGORIS ||--o{ BUKUS : memiliki
    KATEGORIS { bigint id PK string kode_kategori UK string nama_kategori } 
    BUKUS { bigint id PK string isbn UK string judul string penulis string penerbit smallint tahun_terbit bigint kategori_id FK unsigned_integer stok text sinopsis } }
```

`kategori_id` memakai `cascadeOnDelete` untuk konsistensi foreign key. Controller menolak penghapusan kategori yang masih memiliki buku agar data tidak terhapus tidak sengaja. Index judul dan penulis mempercepat lookup katalog; unique index ISBN/kode menjaga integritas dan lookup.

## Instalasi

```bash
git clone <URL-REPOSITORI> sipus-del
cd sipus-del
composer install
cp .env.example .env
php artisan key:generate
```

Windows PowerShell: `New-Item database/database.sqlite -ItemType File` bila SQLite belum ada.

```bash
php artisan migrate:fresh --seed
npm install
npm run build
php artisan serve
```

Buka `http://localhost:8000`. Jangan commit `.env`, `vendor/`, `node_modules/`, atau `public/build/`.

## Rute dan request lifecycle

`php artisan route:list` menampilkan redirect root dan 14 rute resource (7 buku + 7 kategori). Implicit binding digunakan pada `Buku $buku` dan `Kategori $kategori`.

Request masuk ke `public/index.php`, aplikasi dibuat oleh `bootstrap/app.php`, service provider didaftarkan, middleware web dijalankan, router memanggil controller, controller memakai model dan view Blade, lalu response dikirim ke browser.

## Validasi dan keamanan

`$request->validate()` memeriksa ISBN unik berformat wajar, judul minimal 5 karakter, tahun 1900 sampai tahun berjalan, kategori yang ada, serta stok minimal 0. Update memakai `Rule::unique()->ignore($model->id)`. Semua form memakai CSRF dan PUT/DELETE spoofing, `old()` dan `@error` digunakan per field. Blade `{{ }}` mencegah XSS, `$fillable` mencegah mass assignment, dan query Eloquent terparameterisasi mencegah SQL injection.

## Audit SQL

`AppServiceProvider` memasang `DB::listen` dengan format `[SQL AUDIT] (x.xx ms) ...` ke `storage/logs/laravel.log`. Bukti dan langkah reproduksi ada di [docs/sql-audit.log](docs/sql-audit.log).

Tanpa eager loading, 10 baris menghasilkan 1 count + 1 buku + 10 kategori = 12 query (N+1). `Buku::with('kategori')->paginate(10)` menghasilkan 1 count + 1 buku + 1 kategori = 3 query; jumlahnya konstan terhadap jumlah baris. Index kategori memakai `withCount('bukus')`.

## Verifikasi

```bash
php artisan migrate:fresh --seed
php artisan route:list
php artisan test
php artisan view:cache
```

Seeder menghasilkan 5 kategori bermakna dan 20 buku realistis. Feature test mencakup eager loading/pagination, create-update-delete, ISBN duplikat, judul pendek, stok negatif, dan perlindungan penghapusan kategori berisi buku.

## Git dan pengumpulan

Conventional Commits minimum:

```text
feat(migration): create kategoris and bukus tables schema
feat(model): define relationships and fillable attributes
feat(controller): implement restful actions and server validation
feat(views): construct blade component layout and crud templates
perf(eager-loading): optimize sql queries to prevent n+1 problem
```

Verifikasi file rahasia: `git ls-files | grep -E "^(\.env$|vendor/)"` harus kosong.

```bash
git remote add origin https://github.com/<akun>/ppw-2026-week5-12S24013.git
git push -u origin main
```

## Checklist rubrik

- ✅ Arsitektur MVC & Routing (20%): `routes/web.php`, controller resource, binding.
- ✅ Model Relasional & Eloquent (25%): migration/model, `with`, `withCount`, return type relasi.
- ✅ Validasi & Keamanan (20%): controller, partial form, CSRF, escaping, fillable, spoofing.
- ✅ Blade & UI (20%): layout component, CRUD views, Bootstrap, pagination, empty state.
- ✅ Seeders/Logging/Git/Laporan (15%): factory/seeder, `AppServiceProvider`, [docs/sql-audit.log](docs/sql-audit.log), tests, README, `.gitignore`.
