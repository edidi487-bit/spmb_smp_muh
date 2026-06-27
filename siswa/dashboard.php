<?php
require_once 'includes/header.php';

$siswa_id = $siswa['id'];

// 1. Calculate Biodata Status
$is_biodata_complete = !empty($siswa['nik']) && !empty($siswa['tempat_lahir']) && 
                        !empty($siswa['alamat']) && !empty($siswa['asal_sekolah']) && 
                        !empty($siswa['no_hp']) && !empty($siswa['nama_ayah']) && 
                        !empty($siswa['nama_ibu']);

// 2. Calculate Document Status
$req_docs = ['foto', 'kk', 'akta', 'rapor', 'ijazah_skl'];
$docs_uploaded = [];
$docs_status = [];
$docs_comments = [];

$docs_query = mysqli_query($conn, "SELECT jenis_dokumen, status_verifikasi, catatan FROM dokumen WHERE pendaftaran_id = $siswa_id");
while ($doc = mysqli_fetch_assoc($docs_query)) {
    $docs_uploaded[] = $doc['jenis_dokumen'];
    $docs_status[$doc['jenis_dokumen']] = $doc['status_verifikasi'];
    $docs_comments[$doc['jenis_dokumen']] = $doc['catatan'];
}

$uploaded_count = 0;
foreach ($req_docs as $rd) {
    if (in_array($rd, $docs_uploaded)) {
        $uploaded_count++;
    }
}

// 3. Selection Status
$seleksi_query = mysqli_query($conn, "SELECT status_seleksi, keterangan FROM hasil_seleksi WHERE pendaftaran_id = $siswa_id LIMIT 1");
$seleksi = mysqli_fetch_assoc($seleksi_query);
$status_seleksi = $seleksi['status_seleksi'] ?? 'Belum Diseleksi';
$keterangan_seleksi = $seleksi['keterangan'] ?? '';

// 4. Fetch Announcements
$ann_query = mysqli_query($conn, "SELECT * FROM pengumuman ORDER BY tanggal DESC");
?>

<!-- Sidebar -->
<?php require_once 'includes/sidebar.php'; ?>

<!-- Main Content Area -->
<div class="main-content d-flex flex-column">
    <!-- Topbar -->
    <header class="top-navbar justify-content-between">
        <div class="d-flex align-items-center">
            <a href="#" class="text-dark me-3" id="sidebarToggle" aria-label="Toggle Sidebar"><i class="bi bi-justify fs-4"></i></a>
            <h4 class="fw-bold mb-0 text-primary-muh d-none d-md-block">Dashboard Calon Siswa</h4>
        </div>
        <div class="d-flex align-items-center">
            <span class="me-2 text-muted d-none d-sm-inline">Selamat datang,</span>
            <span class="fw-bold text-dark"><?php echo htmlspecialchars($siswa['nama_lengkap']); ?></span>
        </div>
    </header>

    <!-- Page Content -->
    <main class="container-fluid p-4">
        
        <!-- Welcome Alert banner -->
        <div class="card gradient-green border-0 p-4 mb-4 rounded-4 shadow-sm text-white">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3 class="fw-bold mb-1">Halo, <?php echo htmlspecialchars($siswa['nama_lengkap']); ?>!</h3>
                    <p class="mb-0 opacity-90">Selamat datang di Portal Penerimaan Murid Baru SMP Muhammadiyah 1 Pringsewu. Nomor pendaftaran Anda adalah <strong class="text-warning"><?php echo $no_pendaftaran; ?></strong>.</p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <a href="cetak.php" class="btn btn-warning fw-bold"><i class="bi bi-printer-fill me-1"></i> Cetak Kartu Pendaftaran</a>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Left Side: Status Info Cards -->
            <div class="col-lg-8">
                <div class="row g-4">
                    <!-- Status Akun / Biodata -->
                    <div class="col-md-6">
                        <div class="card h-100 p-4 border-0 bg-white">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <span class="text-muted small d-block">Status Akun / Biodata</span>
                                    <h5 class="fw-bold mt-1">Kelengkapan Data</h5>
                                </div>
                                <span class="badge <?php echo $is_biodata_complete ? 'bg-success' : 'bg-danger'; ?> px-3 py-2 rounded-pill">
                                    <?php echo $is_biodata_complete ? 'Lengkap' : 'Belum Lengkap'; ?>
                                </span>
                            </div>
                            <p class="text-muted small mb-4">Pengisian data pendaftaran diri, asal sekolah, dan data orang tua wali secara benar.</p>
                            <a href="pendaftaran.php" class="btn <?php echo $is_biodata_complete ? 'btn-outline-success' : 'btn-primary-muh'; ?> w-100 mt-auto">
                                <i class="bi bi-pencil-square me-1"></i> <?php echo $is_biodata_complete ? 'Edit Data Diri' : 'Lengkapi Biodata'; ?>
                            </a>
                        </div>
                    </div>

                    <!-- Status Berkas / Dokumen -->
                    <div class="col-md-6">
                        <div class="card h-100 p-4 border-0 bg-white">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <span class="text-muted small d-block">Status Dokumen</span>
                                    <h5 class="fw-bold mt-1">Upload Berkas Wajib</h5>
                                </div>
                                <span class="badge <?php echo ($uploaded_count === 5) ? 'bg-success' : 'bg-warning text-dark'; ?> px-3 py-2 rounded-pill">
                                    <?php echo $uploaded_count; ?> dari 5 Berkas
                                </span>
                            </div>
                            <p class="text-muted small mb-4">Unggah dokumen Pas Foto, KK, Akta Lahir, Rapor, dan Ijazah/SKL berukuran maksimal 2MB.</p>
                            
                            <!-- Detailed document alert warnings -->
                            <?php 
                            $has_incomplete = false;
                            foreach ($docs_status as $key => $status) {
                                if ($status === 'Kurang Lengkap') {
                                    $has_incomplete = true;
                                    break;
                                }
                            }
                            if ($has_incomplete): ?>
                                <div class="alert alert-danger p-2 small mb-3">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i> Ada dokumen yang ditolak admin!
                                </div>
                            <?php endif; ?>

                            <a href="upload.php" class="btn <?php echo ($uploaded_count === 5 && !$has_incomplete) ? 'btn-outline-success' : 'btn-primary-muh'; ?> w-100 mt-auto">
                                <i class="bi bi-cloud-arrow-up-fill me-1"></i> Upload Dokumen
                            </a>
                        </div>
                    </div>

                    <!-- Status Seleksi -->
                    <div class="col-12">
                        <div class="card p-4 border-0 bg-white">
                            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-3">
                                <div class="mb-2 mb-sm-0">
                                    <span class="text-muted small d-block">Hasil Pengumuman Seleksi</span>
                                    <h5 class="fw-bold mb-0 mt-1">Status Kelulusan</h5>
                                </div>
                                <div>
                                    <?php if ($status_seleksi === 'LULUS'): ?>
                                        <span class="badge bg-success fs-6 px-4 py-2 rounded-pill">🟢 LULUS</span>
                                    <?php elseif ($status_seleksi === 'TIDAK LULUS'): ?>
                                        <span class="badge bg-danger fs-6 px-4 py-2 rounded-pill">🔴 TIDAK LULUS</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark fs-6 px-4 py-2 rounded-pill">🟡 SEDANG DIPROSES</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <hr class="my-3">

                            <div class="p-3 bg-light rounded-3">
                                <h6 class="fw-bold mb-2">Pemberitahuan:</h6>
                                <?php if ($status_seleksi === 'LULUS'): ?>
                                    <p class="text-success fw-bold mb-1 fs-5">Selamat Anda dinyatakan LULUS di SMP Muhammadiyah 1 Pringsewu.</p>
                                    <p class="text-muted mb-0 small"><?php echo htmlspecialchars($keterangan_seleksi); ?></p>
                                <?php elseif ($status_seleksi === 'TIDAK LULUS'): ?>
                                    <p class="text-danger fw-bold mb-1 fs-5">Terima kasih telah mengikuti seleksi.</p>
                                    <p class="text-muted mb-0 small">Jangan patah semangat. Kami mengapresiasi minat dan waktu yang Anda luangkan.</p>
                                <?php else: ?>
                                    <p class="text-warning fw-bold mb-1">Berkas Anda sedang diverifikasi oleh panitia.</p>
                                    <p class="text-muted mb-0 small">Silakan lengkapi biodata dan upload semua berkas wajib terlebih dahulu. Pengumuman kelulusan akan dirilis secara bertahap.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: School Board Announcements -->
            <div class="col-lg-4">
                <div class="card h-100 p-4 border-0 bg-white">
                    <h5 class="fw-bold mb-4"><i class="bi bi-bell-fill text-warning me-2"></i> Pengumuman Sekolah</h5>
                    
                    <div class="d-flex flex-column gap-3 overflow-auto" style="max-height: 400px;">
                        <?php if (mysqli_num_rows($ann_query) > 0): ?>
                            <?php while ($ann = mysqli_fetch_assoc($ann_query)): ?>
                                <div class="p-3 bg-light rounded-3 border-start border-4 border-success">
                                    <span class="small text-muted d-block"><?php echo date('d M Y', strtotime($ann['tanggal'])); ?></span>
                                    <h6 class="fw-bold mb-1 mt-1 text-primary-muh"><?php echo htmlspecialchars($ann['judul']); ?></h6>
                                    <p class="text-muted small mb-0 lh-sm"><?php echo nl2br(htmlspecialchars($ann['isi'])); ?></p>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="text-center text-muted py-4">Belum ada pengumuman untuk saat ini.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require_once 'includes/footer.php'; ?>
