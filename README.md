# 🛡️ Log Sentinel - Anomaly Detection System

<div align="center">

![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![Python](https://img.shields.io/badge/Python-3.10+-3776AB?style=for-the-badge&logo=python&logoColor=white)
![Scikit-Learn](https://img.shields.io/badge/Scikit--Learn-1.3+-F7931E?style=for-the-badge&logo=scikit-learn&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.x-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)

**Sistem Deteksi Anomali Log Server Berbasis Machine Learning**

*Proyek Ujian Akhir Semester - Mata Kuliah Kecerdasan Artifisial*

</div>

---

## 📋 Tim Penyusun

| No | Nama Lengkap | NPM |
|----|--------------|-----|
| 1 | Jeremy Christo Emmanuelle Panjaitan | 237006516084 |
| 2 | Muhammad Akbar Hadi Pratama | 237006516058 |
| 3 | Farrel Alfaridzi | 237006516028 |
| 4 | Chosmas Laurens Rumngewur | 217006516074 |

---

## 📖 Deskripsi Sistem

**Log Sentinel** adalah sistem deteksi anomali berbasis kecerdasan buatan yang dirancang untuk menganalisis log server secara real-time dan mengidentifikasi aktivitas mencurigakan yang berpotensi menjadi ancaman keamanan siber.

### Arsitektur Sistem

Sistem ini menggunakan arsitektur **microservice** yang terdiri dari dua komponen utama:

1. **Backend Laravel 11** - Menangani manajemen data, autentikasi pengguna, dan antarmuka dashboard monitoring.

2. **Python Flask ML Service** - Menyediakan layanan prediksi menggunakan algoritma **Isolation Forest** untuk mendeteksi pola anomali pada log server.

### Algoritma Isolation Forest

Isolation Forest adalah algoritma unsupervised learning yang efektif untuk deteksi anomali. Algoritma ini bekerja dengan prinsip bahwa:

- **Anomali** adalah data yang "berbeda" dan lebih mudah diisolasi
- **Data normal** membutuhkan lebih banyak partisi untuk diisolasi
- Menggunakan ensemble dari decision trees yang dibangun secara random

**Parameter yang dianalisis:**
- Response time (waktu respons server)
- Status code (kode HTTP)
- Request frequency (frekuensi permintaan)
- IP address patterns (pola alamat IP)

### Jenis Ancaman yang Terdeteksi

| Jenis Serangan | Indikator |
|----------------|-----------|
| DDoS Attack | Response time tinggi, status 503, traffic flood |
| Brute Force | Multiple failed login attempts, status 401 |
| SQL Injection | Suspicious query patterns, status 400 |
| Port Scanning | Sequential 404 errors, enumeration patterns |

---

## 🛠️ Tech Stack

### Backend & Frontend
- **Framework:** Laravel 11.x (PHP 8.2+)
- **Template:** Velzon Admin Dashboard
- **CSS Framework:** Bootstrap 5.x
- **Charts:** ApexCharts.js
- **Icons:** Remix Icon

### Machine Learning Service
- **Language:** Python 3.10+
- **Framework:** Flask 3.x
- **ML Library:** Scikit-Learn 1.3+
- **Data Processing:** NumPy, Pandas

### Database
- **Production:** MySQL 8.0+
- **Testing:** SQLite (in-memory)

---

## 🚀 Cara Instalasi & Menjalankan

### Prasyarat

Pastikan sistem Anda telah terinstal:
- PHP 8.2 atau lebih tinggi
- Composer 2.x
- Node.js 18.x & NPM
- Python 3.10 atau lebih tinggi
- MySQL 8.0 atau lebih tinggi
- Git

### Langkah 1: Clone Repository

```bash
git clone https://github.com/[username]/uas-kecerdasan-artifisial-kel3.git
cd uas-kecerdasan-artifisial-kel3
```

### Langkah 2: Setup Python ML Service

```bash
# Masuk ke direktori ml_service
cd ml_service

# Buat virtual environment
python -m venv venv

# Aktifkan virtual environment
# Windows:
venv\Scripts\activate
# Linux/Mac:
source venv/bin/activate

# Install dependencies
pip install -r requirements.txt

# Kembali ke root directory
cd ..
```

### Langkah 3: Setup Laravel Backend

```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install

# Build assets
npm run build

# Copy file environment
cp .env.example .env

# Generate application key
php artisan key:generate
```

### Langkah 4: Konfigurasi Database

Edit file `.env` dan sesuaikan konfigurasi database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=log_sentinel_db
DB_USERNAME=root
DB_PASSWORD=
```

Buat database di MySQL:

```sql
CREATE DATABASE log_sentinel_db;
```

### Langkah 5: Migrasi & Seeding Database

```bash
# Jalankan migrasi dan seeding
php artisan migrate --seed
```

Seeder akan membuat:
- 1 user admin untuk demo
- 20 dummy server logs (8 normal, 12 anomaly)

### Langkah 6: Menjalankan Aplikasi

**Terminal 1 - Jalankan Python ML Service:**

```bash
cd ml_service
venv\Scripts\activate  # Windows
# atau: source venv/bin/activate  # Linux/Mac

python app.py
```

ML Service akan berjalan di: `http://127.0.0.1:5000`

**Terminal 2 - Jalankan Laravel Server:**

```bash
php artisan serve
```

Laravel akan berjalan di: `http://127.0.0.1:8000`

### Langkah 7: Akses Dashboard

Buka browser dan akses: **http://127.0.0.1:8000**

---

## 🔐 Akun Demo

Gunakan kredensial berikut untuk login:

| Field | Value |
|-------|-------|
| **Email** | `admin@logsentinel.com` |
| **Password** | `password` |

---

## 📁 Struktur Direktori

```
uas-kecerdasan-artifisial-kel3/
├── app/
│   ├── Http/Controllers/
│   │   └── SentinelController.php    # Controller utama
│   └── Models/
│       └── ServerLog.php             # Model log server
├── database/
│   ├── factories/
│   │   └── ServerLogFactory.php      # Factory untuk testing
│   ├── migrations/
│   │   └── *_create_server_logs_table.php
│   └── seeders/
│       └── DatabaseSeeder.php        # Seeder data demo
├── ml_service/                       # Python ML Microservice
│   ├── app.py                        # Flask application
│   ├── venv/                         # Python virtual environment
│   └── requirements.txt              # Python dependencies
├── resources/views/
│   └── sentinel/
│       ├── dashboard.blade.php       # Halaman dashboard
│       ├── logs.blade.php            # Halaman daftar log
│       └── about.blade.php           # Halaman tentang tim
├── routes/
│   ├── web.php                       # Web routes
│   └── api.php                       # API routes
├── tests/
│   ├── Feature/
│   │   ├── ApiEndpointsTest.php      # Test API
│   │   └── DashboardTest.php         # Test Dashboard
│   └── Unit/
│       └── ServerLogModelTest.php    # Test Model
└── README.md
```

---

## 🧪 Menjalankan Unit Test

```bash
# Jalankan semua test
php artisan test

# Jalankan dengan detail output
php artisan test --verbose

# Jalankan test spesifik
php artisan test --filter=ServerLogModelTest
```

**Hasil Test:**
- ✅ 27 tests passed
- ✅ 116 assertions
- ✅ 0 failures

---

## 🔌 API Endpoints

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| POST | `/api/analyze` | Analisis log baru dengan ML |
| GET | `/api/recent-logs` | Ambil log terbaru |
| GET | `/api/stats` | Statistik dashboard |
| GET | `/api/chart-data` | Data untuk chart |
| POST | `/api/simulate-attack` | Simulasi serangan untuk demo |

### Contoh Request API Analyze

```bash
curl -X POST http://127.0.0.1:8000/api/analyze \
  -H "Content-Type: application/json" \
  -d '{
    "ip_address": "192.168.1.100",
    "method": "GET",
    "url": "/api/users",
    "status_code": 200,
    "user_agent": "Mozilla/5.0",
    "response_time": 150.5
  }'
```

---

## 📊 Fitur Utama

1. **Dashboard Real-time** - Monitoring statistik log server dengan visualisasi chart
2. **Deteksi Anomali ML** - Analisis otomatis menggunakan Isolation Forest
3. **Simulasi Serangan** - Fitur demo untuk menunjukkan kemampuan deteksi
4. **Filter & Search** - Pencarian dan filter log berdasarkan status
5. **Responsive Design** - Tampilan optimal di desktop dan mobile

---

## 📜 Lisensi

Proyek ini dibuat untuk keperluan akademis pada mata kuliah Kecerdasan Artifisial.

---

<div align="center">

**© 2026 - Log Sentinel Team**

*Universitas Nasional Jakarta*

</div>
