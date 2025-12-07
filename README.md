# GDD Organizer - Game Design Document Hub

GDD Organizer adalah platform terpadu untuk pengembang game indie yang ingin mengelola seluruh aspek desain game mereka dalam satu tempat.

## Fitur Utama

- **Asset Gallery** - Upload dan kelola aset game dalam tampilan galeri horizontal yang modern.
- **Character Stat Builder** - Definisikan atribut karakter dengan slider visual intuitif dalam tampilan split-view.
- **Interactive Storyline Editor** - Editor narasi yang kuat dengan fitur:
    - **Visual Nodes**: Tambahkan dan edit node cerita.
    - **Connections**: Sambungkan node untuk membuat alur cerita bercabang (Branching Narrative).
    - **Zoom/Pan**: Navigasi kanvas cerita yang luas dengan fitur zoom in/out.
    - **Save Story**: Simpan progres cerita Anda langsung dari toolbar.
- **Edit Project Dashboard** - UI yang didesain ulang sepenuhnya dengan tema "Dark Dashboard" yang premium.
- **Export as PDF** - (Coming Soon) Hasilkan dokumen Game Design Document yang dapat dicetak.

## Teknologi yang Digunakan

- **Frontend**: HTML5, Modern CSS (Custom Dark Theme), JavaScript (Vanilla ES6+).
- **Backend**: PHP 8+ dengan PDO untuk keamanan database.
- **Database**: MySQL.
- **Library**: Bootstrap 5, Font Awesome Icons.

## Cara Menjalankan Aplikasi

### Persyaratan Sistem

1. **XAMPP** (atau web server stack lain dengan PHP & MySQL).
2. **Browser Modern** (Chrome, Firefox, Edge).

### Instalasi

1. **Install XAMPP** jika belum.
2. **Letakkan folder project** ke dalam direktori `htdocs`:
   - Contoh Windows: `C:\xampp\htdocs\FP-PWEB-main\`
3. **Mulai Apache dan MySQL** melalui XAMPP Control Panel.
4. **Setup Database Otomatis**:
   - Aplikasi ini dilengkapi dengan fitur setup otomatis. Cukup buka halaman utama aplikasi, dan jika database belum ada, aplikasi akan mencoba membuatnya (pastikan user root tanpa password, konfigurasi default XAMPP).
   - **Manual Setup (Jika otomatis gagal):**
     1. Buka `phpMyAdmin`.
     2. Buat database `gdd_organizer`.
     3. Import isi struktur tabel dari file `php/database.php` (atau biarkan script `php/setup.php` menjalankannya saat akses pertama).
     4. Tabel yang dibutuhkan: `users`, `projects`, `assets`, `characters`, `story_nodes`.

5. **Akses Aplikasi**:
   - Buka browser dan kunjungi: `http://localhost/FP-PWEB-main/` (sesuaikan dengan nama folder Anda).

### Struktur File Penting

```
FP/
├── index.html              # Halaman Landing & Dashboard Utama
├── pages/
│   ├── login.html          # Authentication
│   ├── edit-project.html   # INTI APLIKASI: Editor Proyek Lengkap (UI Baru)
│   └── profile.html        # Manajemen Profil User
├── css/
│   └── style.css           # Styling utama (Dark Theme, Custom Sliders, Nodes)
├── php/
│   ├── database.php        # Koneksi DB & Skema Tabel
│   ├── auth.php            # Login/Register/Session
│   └── projects.php        # API Proyek (CRUD, Uploads, Save Story)
└── assets/uploads/         # Direktori penyimpanan file user
```

## Panduan Penggunaan

1. **Buat Akun**: Register akun baru lalu Login.
2. **Dashboard**: Buat proyek baru dengan mengklik "Create New Project".
3. **Edit Project**: Masuk ke halaman editor proyek yang baru didesain ulang.
   - **Upload Asset**: Gunakan tombol di bagian "Asset Gallery".
   - **Edit Karakter**: Masukkan nama dan geser slider stat di "Character Stat Builder", lalu Simpan.
   - **Buat Cerita**:
     - Klik **(+)** untuk tambah node.
     - Klik **(Link)** untuk masuk mode koneksi -> Klik Node Awal -> Klik Node Tujuan.
     - Double-klik node untuk mengedit teks.
     - Klik **(Save)** di toolbar floppy disk untuk menyimpan cerita.

## Catatan Penting

- **Akses**: Pastikan mengakses via `http://localhost/...` dan BUKAN `file://...` agar fitur backend berjalan.
- **Email Save Error (Fixed)**: Logika penyimpanan cerita sekarang mendukung validasi yang benar tanpa error "email required".
- **Start Node (Fixed)**: Node awal default sekarang terhubung dengan benar ke sistem data.

## Lisensi

Proyek ini dibuat untuk tujuan pendidikan (Final Project PWEB).