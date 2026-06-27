<?php
require_once 'config/db.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: siswa/dashboard.php");
    }
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nisn = sanitize($_POST['nisn']);
    $nama_lengkap = sanitize($_POST['nama_lengkap']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // 1. Validation Checks
    if (empty($nisn) || empty($nama_lengkap) || empty($password) || empty($confirm_password)) {
        $error = 'Semua field wajib diisi!';
    } elseif (!preg_match('/^[0-9]{10}$/', $nisn)) {
        $error = 'NISN harus berupa 10 digit angka!';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal terdiri dari 6 karakter!';
    } elseif ($password !== $confirm_password) {
        $error = 'Konfirmasi password tidak cocok!';
    } else {
        // Check if NISN is already registered in users
        $check_query = "SELECT id FROM users WHERE username = ? LIMIT 1";
        $stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($stmt, "s", $nisn);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            $error = 'NISN ini sudah terdaftar sebagai pengguna!';
        } else {
            // Check if active tahun ajaran exists
            $ta_query = mysqli_query($conn, "SELECT id FROM tahun_ajaran WHERE status = 'aktif' LIMIT 1");
            $ta_row = mysqli_fetch_assoc($ta_query);
            
            if (!$ta_row) {
                $error = 'Pendaftaran belum dibuka (Tahun Ajaran aktif belum dikonfigurasi). Silakan hubungi admin.';
            } else {
                $tahun_ajaran_id = $ta_row['id'];
                
                // Start transaction to insert users, pendaftaran, and hasil_seleksi
                mysqli_begin_transaction($conn);
                
                try {
                    // Hash password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $role = 'siswa';

                    // Insert user
                    $insert_user_query = "INSERT INTO users (username, password, role, nama_lengkap) VALUES (?, ?, ?, ?)";
                    $stmt_user = mysqli_prepare($conn, $insert_user_query);
                    mysqli_stmt_bind_param($stmt_user, "ssss", $nisn, $hashed_password, $role, $nama_lengkap);
                    mysqli_stmt_execute($stmt_user);
                    $user_id = mysqli_insert_id($conn);

                    // Generate registration number
                    $no_pendaftaran = generateNoPendaftaran();

                    // Insert skeleton registration details
                    // Set default empty strings or dummy values for not-null/foreign fields
                    $tanggal_lahir = '2012-01-01'; // Default placeholder, will be updated by student
                    $jenis_kelamin = 'L';
                    $agama = 'Islam';
                    $alamat = '';
                    $asal_sekolah = '';
                    $no_hp = '';
                    $email = '';
                    $nama_ayah = '';
                    $nama_ibu = '';
                    $pekerjaan_ortu = '';
                    $nik = '';
                    $tempat_lahir = '';

                    $insert_pend_query = "INSERT INTO pendaftaran 
                        (user_id, no_pendaftaran, nisn, NIK, nama_lengkap, tempat_lahir, tanggal_lahir, jenis_kelamin, agama, alamat, asal_sekolah, no_hp, email, nama_ayah, nama_ibu, pekerjaan_ortu, tahun_ajaran_id) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    
                    $stmt_pend = mysqli_prepare($conn, $insert_pend_query);
                    mysqli_stmt_bind_param($stmt_pend, "isssssssssssssssi", 
                        $user_id, $no_pendaftaran, $nisn, $nik, $nama_lengkap, $tempat_lahir, $tanggal_lahir, 
                        $jenis_kelamin, $agama, $alamat, $asal_sekolah, $no_hp, $email, 
                        $nama_ayah, $nama_ibu, $pekerjaan_ortu, $tahun_ajaran_id
                    );
                    mysqli_stmt_execute($stmt_pend);
                    $pendaftaran_id = mysqli_insert_id($conn);

                    // Insert skeleton selection status
                    $insert_seleksi_query = "INSERT INTO hasil_seleksi (pendaftaran_id, status_seleksi, keterangan) VALUES (?, 'Belum Diseleksi', 'Berkas Anda sedang diverifikasi oleh panitia.')";
                    $stmt_seleksi = mysqli_prepare($conn, $insert_seleksi_query);
                    mysqli_stmt_bind_param($stmt_seleksi, "i", $pendaftaran_id);
                    mysqli_stmt_execute($stmt_seleksi);

                    // Commit changes
                    mysqli_commit($conn);

                    // Auto login the registered student
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['username'] = $nisn;
                    $_SESSION['role'] = $role;
                    $_SESSION['nama_lengkap'] = $nama_lengkap;

                    $success = 'Registrasi berhasil! Mengalihkan ke dashboard...';
                } catch (Exception $e) {
                    mysqli_rollback($conn);
                    $error = 'Terjadi kesalahan sistem: ' . $e->getMessage();
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Akun - SPMB SMP Muhammadiyah 1 Pringsewu</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <!-- Custom Style CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, rgba(15, 123, 63, 0.1) 0%, rgba(15, 123, 63, 0.2) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        body.dark-mode {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.95) 0%, rgba(30, 41, 59, 0.95) 100%);
        }
        .register-card {
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            border: none;
        }
    </style>
</head>
<body>

    <!-- Theme Toggle Floating -->
    <div class="position-absolute top-0 end-0 p-3">
        <button class="dark-mode-toggle shadow-sm bg-white" id="darkModeToggle" aria-label="Dark Mode Toggle">
            <i class="bi bi-moon-stars-fill"></i>
        </button>
    </div>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="text-center mb-4">
                    <a href="index.php" class="text-decoration-none">
                        <i class="bi bi-mortarboard-fill text-primary-muh display-4 d-block mb-2"></i>
                    </a>
                    <h3 class="fw-bold mb-1 text-primary-muh">Daftar Akun Calon Siswa</h3>
                    <p class="text-muted">Lengkapi form pendaftaran akun di bawah ini</p>
                </div>
                
                <div class="card register-card p-4 p-md-5">
                    <form action="" method="POST" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="nisn" class="form-label small fw-bold">NISN (Nomor Induk Siswa Nasional)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                                <input type="text" class="form-control" id="nisn" name="nisn" placeholder="10 Digit NISN" pattern="[0-9]{10}" maxlength="10" required>
                                <div class="invalid-feedback">Masukkan 10 digit angka NISN yang valid.</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="nama_lengkap" class="form-label small fw-bold">Nama Lengkap Siswa</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                                <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" placeholder="Nama Lengkap Sesuai Rapor/Ijazah" required>
                                <div class="invalid-feedback">Silakan isi nama lengkap Anda.</div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label small fw-bold">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Minimal 6 Karakter" minlength="6" required>
                                <div class="invalid-feedback">Sandi harus memiliki minimal 6 karakter.</div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="confirm_password" class="form-label small fw-bold">Konfirmasi Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Ulangi Password" required>
                                <div class="invalid-feedback">Ulangi password pendaftaran Anda.</div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary-muh w-100 py-2 mb-3">
                            Registrasi Akun <i class="bi bi-person-plus-fill ms-1"></i>
                        </button>
                        
                        <div class="text-center mt-3">
                            <span class="text-muted small">Sudah punya akun?</span>
                            <a href="login.php" class="text-primary-muh small fw-bold text-decoration-none ms-1">Masuk Sekarang</a>
                        </div>
                        <div class="text-center mt-2">
                            <a href="index.php" class="text-muted small text-decoration-none"><i class="bi bi-arrow-left"></i> Kembali ke Beranda</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Custom Main JS -->
    <script src="assets/js/main.js"></script>
    
    <?php if ($error !== ''): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Registrasi Gagal',
            text: '<?php echo $error; ?>'
        });
    </script>
    <?php endif; ?>

    <?php if ($success !== ''): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil Registrasi',
            text: '<?php echo $success; ?>',
            showConfirmButton: false,
            timer: 2000
        }).then(() => {
            window.location.href = 'siswa/dashboard.php';
        });
    </script>
    <?php endif; ?>
</body>
</html>
