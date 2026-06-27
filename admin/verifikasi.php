<?php
require_once 'includes/header.php';

$error = '';
$success = '';

// ==========================================
// 1. UPDATE DOCUMENT VERIFICATION (POST)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'verify_doc') {
    $doc_id = (int) $_POST['doc_id'];
    $status = sanitize($_POST['status_verifikasi']);
    $catatan = sanitize($_POST['catatan']);
    $siswa_id = (int) $_POST['siswa_id']; // To redirect back

    if (empty($status)) {
        $error = 'Pilih status verifikasi!';
    } else {
        $update_query = "UPDATE dokumen SET status_verifikasi = ?, catatan = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, "ssi", $status, $catatan, $doc_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = 'Status dokumen berhasil diperbarui!';
        } else {
            $error = 'Gagal memperbarui status berkas: ' . mysqli_error($conn);
        }
    }
    
    // Redirect to detail view of the same student
    header("Location: verifikasi.php?id=" . $siswa_id . "&msg=" . urlencode($success ?: $error));
    exit();
}

// Check if verification details are requested for a specific student
$detail_id = isset($_GET['id']) ? (int) $_GET['id'] : null;

// ==========================================
// 2. DETAILED DOCUMENT CHECKER VIEW (GET)
// ==========================================
if ($detail_id):
    // Fetch student info
    $siswa_query = mysqli_query($conn, "SELECT * FROM pendaftaran WHERE id = $detail_id LIMIT 1");
    $siswa_data = mysqli_fetch_assoc($siswa_query);
    if (!$siswa_data) {
        die("Data pendaftar tidak ditemukan!");
    }

    // Fetch documents
    $docs_query = mysqli_query($conn, "SELECT * FROM dokumen WHERE pendaftaran_id = $detail_id");
    $docs = [];
    while ($row = mysqli_fetch_assoc($docs_query)) {
        $docs[$row['jenis_dokumen']] = $row;
    }

    $doc_types = [
        'foto' => ['label' => 'Pas Foto Calon Siswa', 'required' => true],
        'kk' => ['label' => 'Kartu Keluarga (KK)', 'required' => true],
        'akta' => ['label' => 'Akta Kelahiran', 'required' => true],
        'rapor' => ['label' => 'Buku Rapor Kelas V & VI', 'required' => true],
        'ijazah_skl' => ['label' => 'Ijazah / SKL', 'required' => true],
        'kip' => ['label' => 'Kartu Indonesia Pintar (KIP)', 'required' => false],
        'piagam' => ['label' => 'Piagam Prestasi', 'required' => false]
    ];
?>

<!-- Sidebar -->
<?php require_once 'includes/sidebar.php'; ?>

<!-- Main Content Area -->
<div class="main-content d-flex flex-column">
    <!-- Topbar -->
    <header class="top-navbar justify-content-between">
        <div class="d-flex align-items-center">
            <a href="verifikasi.php" class="text-dark me-3" aria-label="Back"><i class="bi bi-arrow-left fs-4"></i></a>
            <h4 class="fw-bold mb-0 text-primary-muh">Periksa Dokumen: <?php echo htmlspecialchars($siswa_data['nama_lengkap']); ?></h4>
        </div>
    </header>

    <!-- Page Content -->
    <main class="container-fluid p-4">
        <!-- Student Info Header -->
        <div class="card p-3 border-0 bg-white shadow-sm mb-4">
            <div class="row text-center text-md-start">
                <div class="col-md-4 mb-2 mb-md-0">
                    <span class="text-muted small">No. Pendaftaran</span>
                    <h6 class="fw-bold text-primary-muh mb-0"><?php echo $siswa_data['no_pendaftaran']; ?></h6>
                </div>
                <div class="col-md-4 mb-2 mb-md-0">
                    <span class="text-muted small">NISN / Asal Sekolah</span>
                    <h6 class="fw-bold text-dark mb-0"><?php echo $siswa_data['nisn']; ?> / <?php echo htmlspecialchars($siswa_data['asal_sekolah']); ?></h6>
                </div>
                <div class="col-md-4">
                    <span class="text-muted small">Kontak HP</span>
                    <h6 class="fw-bold text-dark mb-0"><?php echo htmlspecialchars($siswa_data['no_hp']); ?></h6>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Left Side: Documents Checklist Table -->
            <div class="col-lg-7">
                <div class="card p-4 border-0 bg-white shadow-sm h-100">
                    <h5 class="fw-bold mb-4 border-bottom pb-2">Checklist Berkas Fisik & Online</h5>
                    
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Nama Dokumen</th>
                                    <th>Status</th>
                                    <th>Aksi Periksa</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($doc_types as $key => $type): 
                                    $uploaded = isset($docs[$key]);
                                    $doc_info = $docs[$key] ?? null;
                                ?>
                                    <tr class="border-bottom">
                                        <td>
                                            <span class="fw-bold d-block text-dark">
                                                <?php echo $type['label']; ?>
                                                <?php if ($type['required']): ?><span class="text-danger">*</span><?php endif; ?>
                                            </span>
                                            <?php if ($uploaded): ?>
                                                <span class="text-muted small" style="font-size: 0.75rem;">Diunggah: <?php echo date('d M Y', strtotime($doc_info['uploaded_at'])); ?></span>
                                            <?php else: ?>
                                                <span class="text-danger small" style="font-size: 0.75rem;">Belum Diupload</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($uploaded): 
                                                $status = $doc_info['status_verifikasi'];
                                                if ($status === 'Lengkap'): ?>
                                                    <span class="badge bg-success">Lengkap</span>
                                                <?php elseif ($status === 'Kurang Lengkap'): ?>
                                                    <span class="badge bg-danger">Kurang Lengkap</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning text-dark">Belum Dicek</span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Kosong</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($uploaded): ?>
                                                <button class="btn btn-sm btn-primary-muh py-1 px-3 btn-verify" 
                                                        data-docid="<?php echo $doc_info['id']; ?>" 
                                                        data-label="<?php echo $type['label']; ?>" 
                                                        data-path="../<?php echo $doc_info['file_path']; ?>" 
                                                        data-status="<?php echo $doc_info['status_verifikasi']; ?>" 
                                                        data-catatan="<?php echo htmlspecialchars($doc_info['catatan'] ?? ''); ?>">
                                                    <i class="bi bi-shield-fill-check"></i> Periksa
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-outline-secondary py-1 px-3" disabled>Terkunci</button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right Side: Document Live Preview Card -->
            <div class="col-lg-5">
                <div class="card p-4 border-0 bg-white shadow-sm h-100 text-center">
                    <h5 class="fw-bold mb-4 border-bottom pb-2 text-start">Viewer File Pendukung</h5>
                    
                    <div id="previewContainer" class="d-flex flex-column align-items-center justify-content-center border rounded-3 p-3 bg-light" style="min-height: 400px;">
                        <i class="bi bi-file-earmark-arrow-up display-1 text-muted mb-3"></i>
                        <h6 class="fw-bold text-muted mb-1">Preview Berkas Calon Siswa</h6>
                        <p class="text-muted small px-4 mb-0">Klik tombol "Periksa" pada tabel disamping untuk melihat preview dan mengesahkan berkas pendaftaran.</p>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Dynamic Modal for checking and setting verification status -->
<div class="modal fade" id="verifyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="" method="POST">
            <input type="hidden" name="action" value="verify_doc">
            <input type="hidden" name="doc_id" id="verify_doc_id">
            <input type="hidden" name="siswa_id" value="<?php echo $detail_id; ?>">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="fw-bold" id="verify_title">Periksa Berkas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Pengesahan Status</label>
                        <select name="status_verifikasi" id="verify_status" class="form-select" required>
                            <option value="Belum Dicek">Belum Dicek</option>
                            <option value="Lengkap">Lengkap / Diterima</option>
                            <option value="Kurang Lengkap">Kurang Lengkap / Ditolak</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Catatan / Alasan Penolakan</label>
                        <textarea name="catatan" id="verify_catatan" class="form-control" rows="3" placeholder="Contoh: Lampiran foto kurang jelas / buram. Silakan upload ulang."></textarea>
                        <div class="form-text small text-muted">Hanya perlu diisi jika berkas berstatus "Kurang Lengkap".</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-sm btn-primary-muh">Simpan Status</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    // Dynamic viewer preview logic
    const previewContainer = document.getElementById("previewContainer");
    const verifyButtons = document.querySelectorAll(".btn-verify");
    const verifyModal = new bootstrap.Modal(document.getElementById("verifyModal"));

    verifyButtons.forEach(btn => {
        btn.addEventListener("click", function () {
            const docId = this.dataset.docid;
            const label = this.dataset.label;
            const path = this.dataset.path;
            const status = this.dataset.status;
            const catatan = this.dataset.catatan;

            // Update verification modal inputs
            document.getElementById("verify_doc_id").value = docId;
            document.getElementById("verify_title").innerText = "Pengesahan: " + label;
            document.getElementById("verify_status").value = status;
            document.getElementById("verify_catatan").value = catatan;

            // Load file preview dynamically on the right-hand panel
            const fileExt = path.split('.').pop().toLowerCase();
            previewContainer.innerHTML = ""; // Clear loader

            if (fileExt === 'pdf') {
                previewContainer.innerHTML = `<h6 class="fw-bold mb-3">${label} (PDF)</h6>
                    <iframe class="w-100 border rounded" style="height: 450px;" src="${path}"></iframe>
                    <div class="mt-3 w-100 d-flex justify-content-between">
                        <a href="${path}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-box-arrow-up-right"></i> Buka Tab Baru</a>
                        <button class="btn btn-sm btn-success" onclick="openVerifyModal()"><i class="bi bi-check2-square"></i> Verifikasi Berkas</button>
                    </div>`;
            } else if (['jpg', 'jpeg', 'png'].includes(fileExt)) {
                previewContainer.innerHTML = `<h6 class="fw-bold mb-3">${label} (Gambar)</h6>
                    <img class="img-fluid border rounded shadow-sm" style="max-height: 400px; object-fit: contain;" src="${path}" alt="${label}">
                    <div class="mt-3 w-100 d-flex justify-content-between">
                        <a href="${path}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-box-arrow-up-right"></i> Buka Gambar Asli</a>
                        <button class="btn btn-sm btn-success" onclick="openVerifyModal()"><i class="bi bi-check2-square"></i> Verifikasi Berkas</button>
                    </div>`;
            } else {
                previewContainer.innerHTML = `<i class="bi bi-file-earmark-text display-1 text-muted mb-3"></i>
                    <h6 class="fw-bold">${label}</h6>
                    <p class="text-danger small">Format tidak dapat dipratinjau langsung.</p>
                    <a href="${path}" target="_blank" class="btn btn-sm btn-primary-muh mt-2"><i class="bi bi-download"></i> Unduh Berkas</a>`;
            }
        });
    });

    window.openVerifyModal = function () {
        verifyModal.show();
    };
});
</script>

<?php 
require_once 'includes/footer.php';
// Render redirect toast notifications if redirected with GET status messages
if (isset($_GET['msg'])) {
    $msg = urldecode($_GET['msg']);
    echo "<script>showToast('success', '" . $msg . "');</script>";
}
?>

<?php
// ==========================================
// 3. STUDENT LIST VIEW (DEFAULT VIEW)
// ==========================================
else: 
    // Fetch all students who uploaded at least one document
    $pendaftar_query = mysqli_query($conn, "SELECT p.id, p.no_pendaftaran, p.nisn, p.nama_lengkap, p.asal_sekolah,
                                            COUNT(d.id) as doc_count,
                                            SUM(CASE WHEN d.status_verifikasi = 'Lengkap' THEN 1 ELSE 0 END) as approved_count,
                                            SUM(CASE WHEN d.status_verifikasi = 'Kurang Lengkap' THEN 1 ELSE 0 END) as rejected_count,
                                            SUM(CASE WHEN d.status_verifikasi = 'Belum Dicek' THEN 1 ELSE 0 END) as pending_count
                                            FROM pendaftaran p
                                            JOIN dokumen d ON p.id = d.pendaftaran_id
                                            GROUP BY p.id
                                            ORDER BY p.id DESC");
?>

<!-- Sidebar -->
<?php require_once 'includes/sidebar.php'; ?>

<!-- Main Content Area -->
<div class="main-content d-flex flex-column">
    <!-- Topbar -->
    <header class="top-navbar justify-content-between">
        <div class="d-flex align-items-center">
            <a href="#" class="text-dark me-3" id="sidebarToggle" aria-label="Toggle Sidebar"><i class="bi bi-justify fs-4"></i></a>
            <h4 class="fw-bold mb-0 text-primary-muh">Verifikasi Berkas Calon Siswa</h4>
        </div>
    </header>

    <!-- Page Content -->
    <main class="container-fluid p-4">
        <div class="card border-0 p-4 bg-white shadow-sm">
            <h5 class="fw-bold mb-4 border-bottom pb-2">Calon Siswa Mengunggah Berkas</h5>
            
            <div class="table-responsive">
                <table id="verifikasiTable" class="table table-striped table-hover align-middle" style="width:100%">
                    <thead>
                        <tr>
                            <th>No. Daftar</th>
                            <th>Nama Lengkap</th>
                            <th>Asal Sekolah</th>
                            <th>Total Berkas</th>
                            <th>Status (Menunggu)</th>
                            <th>Status (Ditolak)</th>
                            <th>Status (Diterima)</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($pendaftar_query)): ?>
                            <tr>
                                <td class="fw-bold"><?php echo $row['no_pendaftaran']; ?></td>
                                <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                                <td><?php echo htmlspecialchars($row['asal_sekolah'] ?: '-'); ?></td>
                                <td class="fw-bold"><?php echo $row['doc_count']; ?> Berkas</td>
                                <td>
                                    <?php if ($row['pending_count'] > 0): ?>
                                        <span class="badge bg-warning text-dark"><?php echo $row['pending_count']; ?> Menunggu</span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-muted">0</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($row['rejected_count'] > 0): ?>
                                        <span class="badge bg-danger"><?php echo $row['rejected_count']; ?> Ditolak</span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-muted">0</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($row['approved_count'] > 0): ?>
                                        <span class="badge bg-success"><?php echo $row['approved_count']; ?> Diterima</span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-muted">0</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="verifikasi.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary-muh py-1 px-3">
                                        <i class="bi bi-file-earmark-check-fill me-1"></i> Periksa Berkas
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php require_once 'includes/footer.php'; ?>

<!-- DataTable Initialization -->
<script>
$(document).ready(function() {
    $('#verifikasiTable').DataTable({
        language: {
            search: "Cari Pendaftar:",
            lengthMenu: "Tampilkan _MENU_ data per halaman",
            zeroRecords: "Tidak ada siswa yang sudah mengupload dokumen",
            info: "Menampilkan halaman _PAGE_ dari _PAGES_",
            infoEmpty: "Tidak ada berkas masuk",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Selanjutnya",
                previous: "Sebelumnya"
            }
        }
    });
});
</script>
<?php endif; ?>
