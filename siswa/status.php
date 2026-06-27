<?php
require_once __DIR__ . '/../config/db.php';

$search_key = '';
$siswa = null;
$hasil = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_key'])) {
    $search_key = sanitize($_POST['search_key']);

    if (empty($search_key)) {
        $error = 'Nomor Pendaftaran atau NISN wajib diisi!';
    } else {
        // Query to search pendaftaran and selection results
        $query = "SELECT p.*, h.status_seleksi, h.keterangan 
                  FROM pendaftaran p 
                  LEFT JOIN hasil_seleksi h ON p.id = h.pendaftaran_id 
                  WHERE p.no_pendaftaran = ? OR p.nisn = ? LIMIT 1";
        
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ss", $search_key, $search_key);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) === 1) {
            $siswa = mysqli_fetch_assoc($result);
        } else {
            $error = 'Data Pendaftaran tidak ditemukan. Silakan periksa kembali Nomor Pendaftaran atau NISN Anda.';
        }
    }
} elseif (isset($_SESSION['user_id']) && $_SESSION['role'] === 'siswa') {
    // If logged in, pre-populate student's own status
    $user_id = $_SESSION['user_id'];
    $query = "SELECT p.*, h.status_seleksi, h.keterangan 
              FROM pendaftaran p 
              LEFT JOIN hasil_seleksi h ON p.id = h.pendaftaran_id 
              WHERE p.user_id = ? LIMIT 1";
              
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if (mysqli_num_rows($result) === 1) {
        $siswa = mysqli_fetch_assoc($result);
        $search_key = $siswa['no_pendaftaran'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Kelulusan - SPMB SMP Muhammadiyah 1 Pringsewu</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <!-- Custom Style CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
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
        .status-card {
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
            <div class="col-md-7 col-lg-6">
                <div class="text-center mb-4">
                    <a href="../index.php" class="text-decoration-none">
                        <i class="bi bi-mortarboard-fill text-primary-muh display-4 d-block mb-2"></i>
                    </a>
                    <h3 class="fw-bold mb-1 text-primary-muh">Cek Status Kelulusan</h3>
                    <p class="text-muted">SMP Muhammadiyah 1 Pringsewu</p>
                </div>
                
                <div class="card status-card p-4 p-md-5">
                    <!-- Search Form -->
                    <form action="" method="POST" class="needs-validation" novalidate>
                        <div class="mb-4">
                            <label for="search_key" class="form-label small fw-bold">Nomor Pendaftaran / NISN</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control form-control-lg" id="search_key" name="search_key" 
                                       value="<?php echo htmlspecialchars($search_key); ?>" 
                                       placeholder="Contoh: SPMB-2026-0001 atau NISN" required>
                                <button type="submit" class="btn btn-primary-muh px-4">Cari</button>
                            </div>
                            <div class="form-text small text-muted">Masukkan Nomor Pendaftaran atau NISN yang didapatkan saat registrasi.</div>
                        </div>
                    </form>

                    <!-- Search Result Display -->
                    <?php if ($siswa): 
                        $status = $siswa['status_seleksi'] ?? 'Belum Diseleksi';
                    ?>
                        <hr class="my-4">
                        <div class="text-center">
                            <h5 class="text-muted small text-uppercase">Hasil Penyelidikan</h5>
                            <h4 class="fw-bold mt-1 mb-3 text-dark"><?php echo htmlspecialchars($siswa['nama_lengkap']); ?></h4>
                            <p class="small text-muted mb-1">Nomor Pendaftaran: <strong><?php echo $siswa['no_pendaftaran']; ?></strong></p>
                            <p class="small text-muted mb-3">Asal Sekolah: <strong><?php echo htmlspecialchars($siswa['asal_sekolah']); ?></strong></p>

                            <!-- Status Badge -->
                            <div class="my-4">
                                <?php if ($status === 'LULUS'): ?>
                                    <span class="badge bg-success fs-5 px-4 py-2 rounded-pill"><i class="bi bi-check-circle-fill me-1"></i> LULUS</span>
                                    <div class="alert alert-success mt-4 mb-0 border-0">
                                        <h5 class="fw-bold mb-2">Selamat! 🎉</h5>
                                        <p class="mb-0 fs-6">Selamat Anda dinyatakan LULUS di SMP Muhammadiyah 1 Pringsewu.</p>
                                        <?php if (!empty($siswa['keterangan'])): ?>
                                            <hr>
                                            <p class="mb-0 small text-start"><strong>Catatan Sekolah:</strong> <?php echo htmlspecialchars($siswa['keterangan']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                <?php elseif ($status === 'TIDAK LULUS'): ?>
                                    <span class="badge bg-danger fs-5 px-4 py-2 rounded-pill"><i class="bi bi-x-circle-fill me-1"></i> TIDAK LULUS</span>
                                    <div class="alert alert-danger mt-4 mb-0 border-0">
                                        <h5 class="fw-bold mb-2">Terima Kasih</h5>
                                        <p class="mb-0 fs-6">Terima kasih telah mengikuti seleksi.</p>
                                        <p class="mb-0 mt-2 small text-muted">Tetap semangat dan semoga sukses di jenjang berikutnya.</p>
                                    </div>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark fs-5 px-4 py-2 rounded-pill"><i class="bi bi-clock-fill me-1"></i> BELUM DISELEKSI</span>
                                    <div class="alert alert-warning mt-4 mb-0 border-0 text-dark">
                                        <h5 class="fw-bold mb-2">Berkas Sedang Diverifikasi</h5>
                                        <p class="mb-0 fs-6">Berkas pendaftaran Anda saat ini sedang dalam proses pemeriksaan oleh panitia SPMB.</p>
                                        <p class="mb-0 mt-2 small text-muted">Pantau terus halaman ini atau login ke dashboard Anda untuk melihat perkembangan terbaru.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="text-center mt-3">
                        <a href="../index.php" class="text-muted small text-decoration-none"><i class="bi bi-arrow-left"></i> Kembali ke Beranda</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Custom Main JS -->
    <script src="../assets/js/main.js"></script>

    <?php if ($error !== ''): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Pencarian Gagal',
            text: '<?php echo $error; ?>'
        });
    </script>
    <?php endif; ?>
</body>
</html>
