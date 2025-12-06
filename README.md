# GDD Organizer - Game Design Document Hub

GDD Organizer adalah platform terpadu untuk pengembang game indie yang ingin mengelola seluruh aspek desain game mereka dalam satu tempat.

## Fitur Utama

- **Asset Gallery** - Upload, organisasi, dan pratinjau aset game
- **Character Stat Builder** - Definisikan dan seimbangkan atribut karakter dengan slider intuitif
- **Interactive Storyline** - Buat narasi bercabang dengan editor berbasis node
- **Export as PDF** - Hasilkan dokumen Game Design Document yang dapat dicetak dan dibagikan

## Teknologi yang Digunakan

- **Frontend**: HTML, CSS, JavaScript dengan Bootstrap 5
- **Backend**: PHP Native dengan MySQL Database
- **UI/UX**: Desain gelap dengan estetika industri untuk mengurangi ketegangan mata

## Cara Menjalankan Aplikasi

### Persyaratan Sistem

1. **XAMPP** (atau server web lain dengan PHP dan MySQL)
   - Download dari: https://www.apachefriends.org/

### Instalasi

1. **Install XAMPP**
2. **Letakkan folder project** ke dalam direktori `htdocs`:
   - Untuk Windows: `C:\xampp\htdocs\`
3. **Mulai Apache dan MySQL** melalui XAMPP Control Panel
4. **Akses aplikasi** melalui browser: `http://localhost/nama-folder-project`

### Konfigurasi Database

1. **Buka phpMyAdmin** melalui XAMPP Control Panel (http://localhost/phpmyadmin)
2. **Buat database baru** dengan nama `gdd_organizer`
3. **Atau jalankan file database secara manual** melalui terminal jika Anda memiliki akses ke MySQL

### Struktur File

```
FP/ (root project)
├── index.html              # Halaman utama
├── nav.html                # Template navigasi
├── css/
│   └── style.css          # Styling utama (dark theme & industrial aesthetic)
├── js/
│   └── main.js            # Fungsi JavaScript utama
├── pages/
│   ├── login.html         # Halaman login
│   ├── signup.html        # Halaman pendaftaran
│   ├── edit-project.html  # Halaman edit proyek (dengan sidebar navigasi)
│   ├── membership.html    # Halaman membership
│   └── profile.html       # Halaman profil
├── assets/
│   ├── images/            # Gambar placeholder
│   └── uploads/           # Tempat upload file (akan dibuat secara otomatis)
└── php/
    ├── database.php       # Konfigurasi dan skema database
    ├── auth.php           # Fungsi autentikasi
    ├── upload.php         # Fungsi upload file
    └── api.php            # API endpoints
```

## Cara Menggunakan Aplikasi

1. **Pendaftaran & Login**
   - Akses halaman signup untuk membuat akun
   - Gunakan halaman login untuk masuk ke dashboard

2. **Membuat Proyek Baru**
   - Setelah login, klik "Create New Project" dari dashboard
   - Isi detail proyek Anda

3. **Menggunakan Fitur-fitur**
   - **Asset Gallery**: Upload dan kelola aset game Anda
   - **Character Builder**: Gunakan slider untuk menyesuaikan statistik karakter
   - **Storyline Editor**: Buat narasi bercabang untuk game Anda
   - **Export PDF**: Hasilkan dokumen GDD yang dapat dibagikan

## Sistem Navigasi

Aplikasi ini menggunakan sistem navigasi dua tingkat:
- **Navbar atas**: Untuk berpindah antar halaman utama
- **Sidebar (di halaman edit project)**: Untuk navigasi antar komponen dalam proyek

Sistem navigasi otomatis mendeteksi halaman mana yang sedang aktif dan menandainya dengan benar.

## Panduan Pengembangan

Kode diorganisir dengan cara yang memudahkan pengembangan lebih lanjut:
- Semua file CSS disatukan dalam satu file untuk kemudahan perawatan
- JavaScript modular dengan fungsi-fungsi terpisah
- Struktur PHP dengan file-file terpisah untuk fungsionalitas berbeda

## Catatan Penting

- Dalam lingkungan produksi, pastikan untuk menghubungkan sistem pembayaran QRIS BCA dengan API yang sebenarnya
- File upload aman dengan validasi tipe file dan ukuran
- Semua input pengguna disaring untuk mencegah serangan injeksi

## Lisensi

Proyek ini dibuat untuk tujuan pendidikan.