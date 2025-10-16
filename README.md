# Project Soal Intervieww PandaBiru App

API untuk “Project Skill Test – Panda Biru”.
Stack: Laravel + Sanctum, MySQL/SQLite, Storage symlink untuk gambar.

Fitur Utama

Auth: POST /api/v1/login (Sanctum)
Master: GET /api/v1/stores, GET /api/v1/stores/{id}, GET /api/v1/products
Report: POST /api/v1/report/{context} (attendance|availability|promo)
Summary: GET /api/v1/reports/summary
image_url otomatis jadi URL absolut via accessor Model

Setup Cepat
cp .env.example .env
set DB_*, APP_URL=http://<IP_LAN>
composer install
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan serve

APP_URL wajib benar agar image_url terbentuk absolut di response.

Seeders

StoreSeeder (kode, nama, jam buka, image)

ProductSeeder (name, barcode, size, image)

Pastikan import: use Illuminate\Support\Facades\DB;

Endpoints Ringkas
POST   /api/v1/login
GET    /api/v1/stores
GET    /api/v1/stores/{id}
GET    /api/v1/products
POST   /api/v1/report/attendance
POST   /api/v1/report/availability
POST   /api/v1/report/promo
GET    /api/v1/reports/summary

Catatan

Autentikasi: header Authorization: Bearer <token>.
Gambar: letakkan di storage/app/public/... dan akses via storage:link.
