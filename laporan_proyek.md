# BOLOBOX - Game Design Document Hub

BOLOBOX adalah platform berbasis web yang dirancang untuk membantu pengembang game mengelola aset, desain karakter, dan alur cerita (storyline) dalam satu tempat yang terintegrasi. Proyek ini dibangun dengan fokus pada antarmuka modern dan arsitektur yang skalabel.

---

##  Laporan Proyek & Implementasi Teknis

### 1. Frontend & Backend Development

**Frontend (Antarmuka Pengguna):**
*   **Framework CSS:** Menggunakan **Bootstrap 5** untuk sistem grid responsif, komponen UI (Navbar, Cards, Modals), dan utilitas layout.
*   **Custom Styling:** File `style.css` mengimplementasikan desain *Dark Mode* dengan nuansa industrial. Fitur visual meliputi efek *glassmorphism*, transisi halus, dan background grid interaktif untuk editor cerita.
*   **Interaktivitas:** Menggunakan **Vanilla JavaScript** (ES6+) untuk:
    *   Manipulasi DOM dinamis (Rendering daftar project tanpa reload).
    *   Komunikasi asinkron via Fetch API.
    *   Logika editor visual (Drag-and-drop node cerita).

**Backend (Logika Server):**
*   **Bahasa:** PHP Native (Tanpa Framework).
*   **Arsitektur:** Menggunakan pola API-first. File PHP di folder `/php` berfungsi sebagai endpoint API yang menerima request JSON/formData dan mengembalikan respon JSON.
*   **Keamanan:**
    *   Sanitasi input untuk mencegah SQL Injection.
    *   Hashing password menggunakan `password_hash()` (Bcrypt).
    *   Validasi sesi server-side untuk melindungi akses data.

### 2. Database Implementation

Proyek ini menggunakan **MySQL** sebagai sistem manajemen basis data relasional. Struktur database dirancang untuk menjaga integritas data antar entitas game.

**Skema Database:**
*   **`users`**: Menyimpan kredensial pengguna (Username, Email, Hash Password).
*   **`projects`**: Tabel utama yang menyimpan meta-data proyek game. Berelasi *One-to-Many* dengan users.
*   **`assets`**: Menyimpan path file gambar yang diunggah. Berelasi dengan tabel `projects`.
*   **`characters`**: Menyimpan atribut RPG (HP, Attack, Speed).
*   **`story_nodes`**: Menyimpan data visual scripting untuk alur cerita. Menggunakan tipe data `JSON` untuk kolom `connections` guna menyimpan relasi graf yang kompleks antar node cerita.

### 3. Integrasi API

BOLOBOX mengimplementasikan beberapa jenis integrasi API untuk meningkatkan fungsionalitas:

*   **Google OAuth 2.0:** Memungkinkan pengguna untuk Login/Nendaftar menggunakan akun Google mereka.
    *   Endpoint: `https://accounts.google.com/o/oauth2/auth` & `https://oauth2.googleapis.com/token`.
    *   Flow: Authorization Code Flow (Server-side exchange).
*   **Internal RESTful API:** Backend PHP menyediakan endpoint REST untuk Frontend:
    *   `POST /php/auth.php`: Otentikasi user.
    *   `GET/POST /php/projects.php`: CRUD operasi untuk semua data proyek.
    *   `POST /php/upload.php`: Penanganan upload file multipart.

### 4. Pengujian (Testing)

Pengujian dilakukan untuk memastikan stabilitas aplikasi sebelum deployment:

*   **Unit Testing (Manual):**
    *   Verifikasi fungsi registrasi/login (Cek validasi email & password).
    *   Tes upload gambar (Validasi tipe file & batas ukuran 5MB).
*   **Integration Testing:**
    *   Memastikan alur Google Login berhasil membuat sesi user di database lokal.
    *   Memastikan data karakter yang diedit tersimpan dan dimuat kembali dengan benar saat halaman di-refresh.
*   **Deployment Testing (Railway):**
    *   Memastikan Environment Variables (`DB_HOST`, `GOOGLE_CLIENT_ID`) terbaca dengan benar.
    *   Verifikasi koneksi database di lingkungan produksi cloud.
    *   Pengecekan error handling (Menampilkan pesan JSON yang jelas saat koneksi DB gagal).

---
