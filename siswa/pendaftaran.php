<?php
require_once 'includes/header.php';

$siswa_id = $siswa['id'];
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input
    $nik = sanitize($_POST['nik']);
    $nama_lengkap = sanitize($_POST['nama_lengkap']);
    $tempat_lahir = sanitize($_POST['tempat_lahir']);
    $tanggal_lahir = sanitize($_POST['tanggal_lahir']);
    $jenis_kelamin = sanitize($_POST['jenis_kelamin']);
    $agama = sanitize($_POST['agama']);
    $alamat = sanitize($_POST['alamat']);
    $asal_sekolah = sanitize($_POST['asal_sekolah']);
    $no_hp = sanitize($_POST['no_hp']);
    $email = sanitize($_POST['email']);
    $nama_ayah = sanitize($_POST['nama_ayah']);
    $nama_ibu = sanitize($_POST['nama_ibu']);
    $pekerjaan_ortu = sanitize($_POST['pekerjaan_ortu']);

    // PHP Server-Side Validations
    if (empty($nik) || empty($nama_lengkap) || empty($tempat_lahir) || empty($tanggal_lahir) || 
        empty($jenis_kelamin) || empty($agama) || empty($alamat) || empty($asal_sekolah) || 
        empty($no_hp) || empty($email) || empty($nama_ayah) || empty($nama_ibu) || empty($pekerjaan_ortu)) {
        $error = 'Semua field wajib diisi!';
    } elseif (!preg_match('/^[0-9]{16}$/', $nik)) {
        $error = 'NIK harus berupa 16 digit angka!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid!';
    } elseif (!preg_match('/^[0-9]{10,15}$/', $no_hp)) {
        $error = 'Nomor HP harus berupa angka dengan panjang 10-15 digit!';
    } else {
        // Update query
        $update_query = "UPDATE pendaftaran SET 
            nik = ?, 
            nama_lengkap = ?, 
            tempat_lahir = ?, 
            tanggal_lahir = ?, 
            jenis_kelamin = ?, 
            agama = ?, 
            alamat = ?, 
            asal_sekolah = ?, 
            no_hp = ?, 
            email = ?, 
            nama_ayah = ?, 
            nama_ibu = ?, 
            pekerjaan_ortu = ? 
            WHERE id = ?";
            
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, "sssssssssssssi", 
            $nik, $nama_lengkap, $tempat_lahir, $tanggal_lahir, $jenis_kelamin, 
            $agama, $alamat, $asal_sekolah, $no_hp, $email, 
            $nama_ayah, $nama_ibu, $pekerjaan_ortu, $siswa_id
        );

        if (mysqli_stmt_execute($stmt)) {
            // Also sync the users display name
            mysqli_query($conn, "UPDATE users SET nama_lengkap = '$nama_lengkap' WHERE id = " . $siswa['user_id']);
            $_SESSION['nama_lengkap'] = $nama_lengkap;
            
            $success = 'Biodata pendaftaran berhasil disimpan!';
            // Refresh local row variables
            $pendaftaran_query = mysqli_query($conn, "SELECT * FROM pendaftaran WHERE user_id = " . $_SESSION['user_id'] . " LIMIT 1");
            $siswa = mysqli_fetch_assoc($pendaftaran_query);
        } else {
            $error = 'Gagal menyimpan data: ' . mysqli_error($conn);
        }
    }
}
?>

<!-- Sidebar -->
<?php require_once 'includes/sidebar.php'; ?>

<!-- Main Content Area -->
<div class="main-content d-flex flex-column">
    <!-- Topbar -->
    <header class="top-navbar justify-content-between">
        <div class="d-flex align-items-center">
            <a href="#" class="text-dark me-3" id="sidebarToggle" aria-label="Toggle Sidebar"><i class="bi bi-justify fs-4"></i></a>
            <h4 class="fw-bold mb-0 text-primary-muh">Data Diri Calon Siswa</h4>
        </div>
    </header>

    <!-- Page Content -->
    <main class="container-fluid p-4">
        <div class="row justify-content-center">
            <div class="col-xl-10">
                
                <!-- Instruction Alert -->
                <div class="alert alert-info border-0 shadow-sm mb-4">
                    <div class="d-flex">
                        <i class="bi bi-info-circle-fill fs-4 me-3 text-primary-muh"></i>
                        <div>
                            <h6 class="fw-bold mb-1">Panduan Pengisian Formulir</h6>
                            <p class="mb-0 small text-muted">Isilah formulir dengan lengkap dan benar menggunakan data yang tertera pada Kartu Keluarga (KK), Akta Kelahiran, dan Ijazah/Rapor Anda. Perubahan nama akan otomatis sinkron dengan nama profil akun.</p>
                        </div>
                    </div>
                </div>

                <form action="" method="POST" class="needs-validation" novalidate>
                    <!-- CARD 1: DATA PERSONAL SISWA -->
                    <div class="card p-4 border-0 mb-4 bg-white">
                        <h5 class="fw-bold text-primary-muh mb-4 border-bottom pb-2">
                            <i class="bi bi-person-fill me-2"></i> Data Diri Calon Siswa
                        </h5>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">NISN (Username Akun)</label>
                                <input type="text" class="form-control bg-light text-muted" value="<?php echo htmlspecialchars($siswa['nisn']); ?>" disabled>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="nik" class="form-label small fw-bold">NIK (Nomor Induk Kependudukan)</label>
                                <input type="text" class="form-control" id="nik" name="nik" value="<?php echo htmlspecialchars($siswa['nik']); ?>" placeholder="16 Digit NIK Sesuai KK" pattern="[0-9]{16}" maxlength="16" required>
                                <div class="invalid-feedback">Masukkan 16 digit angka NIK yang valid.</div>
                            </div>

                            <div class="col-md-12">
                                <label for="nama_lengkap" class="form-label small fw-bold">Nama Lengkap</label>
                                <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" value="<?php echo htmlspecialchars($siswa['nama_lengkap']); ?>" placeholder="Nama Lengkap" required>
                                <div class="invalid-feedback">Silakan masukkan nama lengkap sesuai dokumen legal.</div>
                            </div>

                            <div class="col-md-6">
                                <label for="tempat_lahir" class="form-label small fw-bold">Tempat Lahir</label>
                                <input type="text" class="form-control" id="tempat_lahir" name="tempat_lahir" value="<?php echo htmlspecialchars($siswa['tempat_lahir']); ?>" placeholder="Kota/Kabupaten Lahir" required>
                                <div class="invalid-feedback">Silakan masukkan kota kelahiran.</div>
                            </div>

                            <div class="col-md-6">
                                <label for="tanggal_lahir" class="form-label small fw-bold">Tanggal Lahir</label>
                                <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir" value="<?php echo htmlspecialchars($siswa['tanggal_lahir']); ?>" required>
                                <div class="invalid-feedback">Silakan masukkan tanggal lahir yang valid.</div>
                            </div>

                            <div class="col-md-6">
                                <label for="jenis_kelamin" class="form-label small fw-bold">Jenis Kelamin</label>
                                <select class="form-select" id="jenis_kelamin" name="jenis_kelamin" required>
                                    <option value="" disabled <?php echo empty($siswa['jenis_kelamin']) ? 'selected' : ''; ?>>Pilih Jenis Kelamin</option>
                                    <option value="L" <?php echo ($siswa['jenis_kelamin'] === 'L') ? 'selected' : ''; ?>>Laki-laki (L)</option>
                                    <option value="P" <?php echo ($siswa['jenis_kelamin'] === 'P') ? 'selected' : ''; ?>>Perempuan (P)</option>
                                </select>
                                <div class="invalid-feedback">Silakan pilih jenis kelamin.</div>
                            </div>

                            <div class="col-md-6">
                                <label for="agama" class="form-label small fw-bold">Agama</label>
                                <select class="form-select" id="agama" name="agama" required>
                                    <option value="" disabled <?php echo empty($siswa['agama']) ? 'selected' : ''; ?>>Pilih Agama</option>
                                    <option value="Islam" <?php echo ($siswa['agama'] === 'Islam') ? 'selected' : ''; ?>>Islam</option>
                                    <option value="Kristen" <?php echo ($siswa['agama'] === 'Kristen') ? 'selected' : ''; ?>>Kristen</option>
                                    <option value="Katolik" <?php echo ($siswa['agama'] === 'Katolik') ? 'selected' : ''; ?>>Katolik</option>
                                    <option value="Hindu" <?php echo ($siswa['agama'] === 'Hindu') ? 'selected' : ''; ?>>Hindu</option>
                                    <option value="Buddha" <?php echo ($siswa['agama'] === 'Buddha') ? 'selected' : ''; ?>>Buddha</option>
                                    <option value="Konghucu" <?php echo ($siswa['agama'] === 'Konghucu') ? 'selected' : ''; ?>>Konghucu</option>
                                    <option value="Lainnya" <?php echo ($siswa['agama'] === 'Lainnya') ? 'selected' : ''; ?>>Lainnya</option>
                                </select>
                                <div class="invalid-feedback">Silakan pilih agama Anda.</div>
                            </div>

                            <div class="col-md-12">
                                <label for="alamat" class="form-label small fw-bold">Alamat Lengkap Rumah</label>
                                <textarea class="form-control" id="alamat" name="alamat" rows="3" placeholder="Nama Jalan, RT/RW, Dusun, Desa, Kecamatan, Kabupaten Sesuai KK" required><?php echo htmlspecialchars($siswa['alamat']); ?></textarea>
                                <div class="invalid-feedback">Silakan isi alamat lengkap Anda.</div>
                            </div>

                            <div class="col-md-12">
                                <label for="asal_sekolah" class="form-label small fw-bold">Asal Sekolah (SD/MI)</label>
                                <input type="text" class="form-control" id="asal_sekolah" name="asal_sekolah" value="<?php echo htmlspecialchars($siswa['asal_sekolah']); ?>" placeholder="Contoh: SD Negeri 1 Pringsewu" required>
                                <div class="invalid-feedback">Silakan isi asal sekolah Anda.</div>
                            </div>

                            <div class="col-md-6">
                                <label for="no_hp" class="form-label small fw-bold">Nomor HP (WhatsApp Aktif)</label>
                                <input type="tel" class="form-control" id="no_hp" name="no_hp" value="<?php echo htmlspecialchars($siswa['no_hp']); ?>" placeholder="Contoh: 0812XXXXXXXX" pattern="[0-9]{10,15}" required>
                                <div class="invalid-feedback">Masukkan nomor handphone numerik yang valid (10-15 digit).</div>
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label small fw-bold">Alamat Email Aktif</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($siswa['email']); ?>" placeholder="Contoh: siswa@gmail.com" required>
                                <div class="invalid-feedback">Masukkan alamat email yang valid.</div>
                            </div>
                        </div>
                    </div>

                    <!-- CARD 2: DATA ORANG TUA -->
                    <div class="card p-4 border-0 mb-4 bg-white">
                        <h5 class="fw-bold text-primary-muh mb-4 border-bottom pb-2">
                            <i class="bi bi-people-fill me-2"></i> Data Orang Tua / Wali
                        </h5>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="nama_ayah" class="form-label small fw-bold">Nama Lengkap Ayah Kandung</label>
                                <input type="text" class="form-control" id="nama_ayah" name="nama_ayah" value="<?php echo htmlspecialchars($siswa['nama_ayah']); ?>" placeholder="Nama Ayah Kandung" required>
                                <div class="invalid-feedback">Masukkan nama lengkap ayah kandung.</div>
                            </div>

                            <div class="col-md-6">
                                <label for="nama_ibu" class="form-label small fw-bold">Nama Lengkap Ibu Kandung</label>
                                <input type="text" class="form-control" id="nama_ibu" name="nama_ibu" value="<?php echo htmlspecialchars($siswa['nama_ibu']); ?>" placeholder="Nama Ibu Kandung (Sesuai Akta/KK)" required>
                                <div class="invalid-feedback">Masukkan nama lengkap ibu kandung.</div>
                            </div>

                            <div class="col-md-12">
                                <label for="pekerjaan_ortu" class="form-label small fw-bold">Pekerjaan Orang Tua / Wali</label>
                                <input type="text" class="form-control" id="pekerjaan_ortu" name="pekerjaan_ortu" value="<?php echo htmlspecialchars($siswa['pekerjaan_ortu']); ?>" placeholder="Contoh: PNS / Karyawan Swasta / Wiraswasta / Petani" required>
                                <div class="invalid-feedback">Silakan isi pekerjaan orang tua atau wali Anda.</div>
                            </div>
                        </div>
                    </div>

                    <!-- SUBMIT BUTTONS -->
                    <div class="card p-3 border-0 bg-transparent text-end">
                        <a href="dashboard.php" class="btn btn-outline-secondary me-2">Kembali</a>
                        <button type="submit" class="btn btn-primary-muh px-4 py-2">
                            Simpan Formulir <i class="bi bi-save ms-1"></i>
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </main>
</div>

<?php require_once 'includes/footer.php'; ?>

<!-- Alerts triggers -->
<?php if ($error !== ''): ?>
<script>
    Swal.fire({
        icon: 'error',
        title: 'Gagal Menyimpan',
        text: '<?php echo $error; ?>'
    });
</script>
<?php endif; ?>

<?php if ($success !== ''): ?>
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil Disimpan',
        text: '<?php echo $success; ?>'
    });
</script>
<?php endif; ?>
