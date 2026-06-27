<?php
require_once __DIR__ . '/../config/db.php';
check_login('siswa');

// Fetch user pendaftaran details
$user_id = $_SESSION['user_id'];
$pendaftaran_query = mysqli_query($conn, "SELECT id, no_pendaftaran FROM pendaftaran WHERE user_id = $user_id LIMIT 1");
$siswa = mysqli_fetch_assoc($pendaftaran_query);
$pendaftaran_id = $siswa['id'];
$no_pendaftaran_clean = str_replace('-', '_', $siswa['no_pendaftaran']);

// ==========================================
// 1. HANDLE FILE UPLOAD POST REQUEST (AJAX)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document_file'])) {
    header('Content-Type: application/json');
    
    $jenis_dokumen = sanitize($_POST['jenis_dokumen'] ?? '');
    $allowed_types = ['foto', 'kk', 'akta', 'rapor', 'ijazah_skl', 'kip', 'piagam'];
    
    if (!in_array($jenis_dokumen, $allowed_types)) {
        echo json_encode(['status' => 'error', 'message' => 'Jenis dokumen tidak valid!']);
        exit();
    }

    $file = $_FILES['document_file'];
    
    // Server-Side Validation: File Errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan sistem saat mengupload file (Error Code: ' . $file['error'] . ')']);
        exit();
    }

    // Server-Side Validation: Max Size 2MB
    $max_size = 2 * 1024 * 1024;
    if ($file['size'] > $max_size) {
        echo json_encode(['status' => 'error', 'message' => 'Ukuran file maksimal adalah 2 MB!']);
        exit();
    }

    // Server-Side Validation: Allowed Extensions
    $filename = $file['name'];
    $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $allowed_exts = ['jpg', 'jpeg', 'png', 'pdf'];
    if (!in_array($file_ext, $allowed_exts)) {
        echo json_encode(['status' => 'error', 'message' => 'Hanya file JPG, JPEG, PNG, dan PDF yang diperbolehkan!']);
        exit();
    }

    // Server-Side Validation: Mime Type Check
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    $allowed_mimes = ['image/jpeg', 'image/png', 'application/pdf'];
    if (!in_array($mime_type, $allowed_mimes)) {
        echo json_encode(['status' => 'error', 'message' => 'Tipe berkas tidak valid! Pastikan berkas berupa gambar atau PDF asli.']);
        exit();
    }

    // Setup Target Directory
    $upload_dir = __DIR__ . '/../uploads/' . $jenis_dokumen . '/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // Secure Unique Filename
    $new_filename = $no_pendaftaran_clean . '_' . $jenis_dokumen . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $file_ext;
    $target_file = $upload_dir . $new_filename;
    
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        // Save database path relative to root
        $db_path = 'uploads/' . $jenis_dokumen . '/' . $new_filename;

        // Check if document already exists
        $check_doc = mysqli_query($conn, "SELECT id, file_path FROM dokumen WHERE pendaftaran_id = $pendaftaran_id AND jenis_dokumen = '$jenis_dokumen' LIMIT 1");
        
        if (mysqli_num_rows($check_doc) > 0) {
            $existing = mysqli_fetch_assoc($check_doc);
            $existing_id = $existing['id'];
            $old_path = __DIR__ . '/../' . $existing['file_path'];
            
            // Delete old file from storage if exists
            if (file_exists($old_path)) {
                @unlink($old_path);
            }

            // Update database and reset verification status
            $update_sql = "UPDATE dokumen SET file_path = ?, status_verifikasi = 'Belum Dicek', catatan = NULL, uploaded_at = CURRENT_TIMESTAMP WHERE id = ?";
            $stmt = mysqli_prepare($conn, $update_sql);
            mysqli_stmt_bind_param($stmt, "si", $db_path, $existing_id);
            mysqli_stmt_execute($stmt);
        } else {
            // Insert new document
            $insert_sql = "INSERT INTO dokumen (pendaftaran_id, jenis_dokumen, file_path, status_verifikasi) VALUES (?, ?, ?, 'Belum Dicek')";
            $stmt = mysqli_prepare($conn, $insert_sql);
            mysqli_stmt_bind_param($stmt, "iss", $pendaftaran_id, $jenis_dokumen, $db_path);
            mysqli_stmt_execute($stmt);
        }

        echo json_encode(['status' => 'success', 'message' => 'Berkas ' . strtoupper($jenis_dokumen) . ' berhasil diupload!']);
        exit();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal memindahkan file ke direktori tujuan.']);
        exit();
    }
}

// ==========================================
// 2. RENDER THE FILE UPLOAD GUI (GET)
// ==========================================
require_once 'includes/header.php';

// Fetch uploaded documents status
$docs_uploaded = [];
$docs_paths = [];
$docs_status = [];
$docs_notes = [];

$docs_query = mysqli_query($conn, "SELECT jenis_dokumen, file_path, status_verifikasi, catatan FROM dokumen WHERE pendaftaran_id = $pendaftaran_id");
while ($doc = mysqli_fetch_assoc($docs_query)) {
    $jenis = $doc['jenis_dokumen'];
    $docs_uploaded[] = $jenis;
    $docs_paths[$jenis] = $doc['file_path'];
    $docs_status[$jenis] = $doc['status_verifikasi'];
    $docs_notes[$jenis] = $doc['catatan'];
}

$document_list = [
    'foto' => ['label' => 'Pas Foto Calon Siswa', 'desc' => 'Foto terbaru 3x4 formal berlatar merah/biru. Format JPG/PNG.', 'required' => true],
    'kk' => ['label' => 'Kartu Keluarga (KK)', 'desc' => 'Scan/Foto Kartu Keluarga asli dengan nama siswa tercantum. Format JPG/PNG/PDF.', 'required' => true],
    'akta' => ['label' => 'Akta Kelahiran', 'desc' => 'Scan/Foto Akta Kelahiran asli calon siswa. Format JPG/PNG/PDF.', 'required' => true],
    'rapor' => ['label' => 'Buku Rapor Kelas V & VI', 'desc' => 'Scan/Foto halaman nilai Rapor SD/MI kelas 5 dan 6. Format JPG/PNG/PDF.', 'required' => true],
    'ijazah_skl' => ['label' => 'Ijazah / SKL (Surat Keterangan Lulus)', 'desc' => 'Scan/Foto Ijazah asli atau Surat Keterangan Lulus dari SD/MI. Format JPG/PNG/PDF.', 'required' => true],
    'kip' => ['label' => 'Kartu Indonesia Pintar (KIP)', 'desc' => 'Scan Kartu KIP asli untuk pengajuan beasiswa afirmasi (Opsional). Format JPG/PNG/PDF.', 'required' => false],
    'piagam' => ['label' => 'Piagam Prestasi', 'desc' => 'Scan Piagam/Sertifikat Kejuaraan minimal tingkat Kabupaten (Opsional). Format JPG/PNG/PDF.', 'required' => false]
];
?>

<!-- Sidebar -->
<?php require_once 'includes/sidebar.php'; ?>

<!-- Main Content Area -->
<div class="main-content d-flex flex-column">
    <!-- Topbar -->
    <header class="top-navbar justify-content-between">
        <div class="d-flex align-items-center">
            <a href="#" class="text-dark me-3" id="sidebarToggle" aria-label="Toggle Sidebar"><i class="bi bi-justify fs-4"></i></a>
            <h4 class="fw-bold mb-0 text-primary-muh">Upload Dokumen Pendukung</h4>
        </div>
    </header>

    <!-- Page Content -->
    <main class="container-fluid p-4">
        <div class="row justify-content-center">
            <div class="col-xl-10">

                <!-- Alert Information -->
                <div class="alert alert-warning border-0 shadow-sm mb-4">
                    <div class="d-flex">
                        <i class="bi bi-exclamation-triangle-fill fs-4 me-3 text-warning"></i>
                        <div>
                            <h6 class="fw-bold mb-1 text-dark">Informasi Penting Sebelum Upload</h6>
                            <p class="mb-0 small text-muted">Pastikan resolusi dokumen dapat dibaca dengan jelas oleh panitia verifikasi. Format yang didukung adalah <strong>JPG, JPEG, PNG, dan PDF</strong> dengan ukuran file maksimal <strong>2 MB</strong> per berkas.</p>
                        </div>
                    </div>
                </div>

                <!-- Document Upload List -->
                <div class="card p-4 border-0 bg-white">
                    <h5 class="fw-bold text-primary-muh mb-4 border-bottom pb-2">
                        <i class="bi bi-folder-symlink-fill me-2"></i> Daftar Dokumen SPMB
                    </h5>

                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 30%;">Jenis Dokumen</th>
                                    <th style="width: 30%;">Status Verifikasi</th>
                                    <th style="width: 40%;">Aksi & Upload</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($document_list as $key => $doc): 
                                    $is_uploaded = in_array($key, $docs_uploaded);
                                    $status = $docs_status[$key] ?? 'Belum Diupload';
                                    $path = $docs_paths[$key] ?? '';
                                    $notes = $docs_notes[$key] ?? '';
                                ?>
                                    <tr class="border-bottom">
                                        <!-- Document Info -->
                                        <td>
                                            <span class="fw-bold d-block text-dark">
                                                <?php echo $doc['label']; ?>
                                                <?php if ($doc['required']): ?>
                                                    <span class="text-danger">*</span>
                                                <?php else: ?>
                                                    <span class="text-muted small fw-normal">(Opsional)</span>
                                                <?php endif; ?>
                                            </span>
                                            <span class="text-muted small d-block" style="font-size: 0.75rem;"><?php echo $doc['desc']; ?></span>
                                        </td>
                                        
                                        <!-- Verification Status Badge -->
                                        <td>
                                            <?php if (!$is_uploaded): ?>
                                                <span class="badge bg-secondary px-3 py-2 rounded-pill"><i class="bi bi-x-circle me-1"></i> Belum Diupload</span>
                                            <?php else: ?>
                                                <?php if ($status === 'Lengkap'): ?>
                                                    <span class="badge bg-success px-3 py-2 rounded-pill"><i class="bi bi-check-circle-fill me-1"></i> Lengkap / Diterima</span>
                                                <?php elseif ($status === 'Kurang Lengkap'): ?>
                                                    <span class="badge bg-danger px-3 py-2 rounded-pill"><i class="bi bi-exclamation-octagon-fill me-1"></i> Kurang Lengkap</span>
                                                    <?php if ($notes !== ''): ?>
                                                        <div class="mt-2 text-danger small bg-danger bg-opacity-10 p-2 rounded border border-danger border-opacity-20">
                                                            <strong>Alasan ditolak:</strong> <?php echo htmlspecialchars($notes); ?>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="badge bg-warning text-dark px-3 py-2 rounded-pill"><i class="bi bi-clock-fill me-1"></i> Belum Dicek</span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Actions & AJAX Upload Trigger -->
                                        <td>
                                            <div class="d-flex flex-column gap-2">
                                                <!-- If uploaded, show preview and rename info -->
                                                <?php if ($is_uploaded): ?>
                                                    <div class="d-flex gap-2 align-items-center mb-1">
                                                        <a href="../<?php echo $path; ?>" target="_blank" class="btn btn-sm btn-outline-primary py-1 px-3">
                                                            <i class="bi bi-eye-fill me-1"></i> Lihat Berkas
                                                        </a>
                                                        <span class="small text-muted"><i class="bi bi-check2-all text-success"></i> Terupload</span>
                                                    </div>
                                                <?php endif; ?>

                                                <!-- Upload File Input (only if verification is not complete, or if re-upload is needed) -->
                                                <?php if ($status !== 'Lengkap'): ?>
                                                    <div>
                                                        <input type="file" class="form-control form-control-sm" id="input_<?php echo $key; ?>" accept=".jpg,.jpeg,.png,.pdf" required>
                                                    </div>
                                                    
                                                    <!-- Dynamic upload progress bar wrapper -->
                                                    <div id="progress_<?php echo $key; ?>_container" class="d-none mt-2">
                                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                                            <span class="small text-muted">Mengupload berkas...</span>
                                                            <span class="small fw-bold text-primary-muh" id="progress_<?php echo $key; ?>_text">0%</span>
                                                        </div>
                                                        <div class="progress" style="height: 6px;">
                                                            <div id="progress_<?php echo $key; ?>_bar" class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                                        </div>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-success small"><i class="bi bi-lock-fill"></i> Terkunci (Sudah Terverifikasi)</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </main>
</div>

<!-- Upload AJAX scripts linking -->
<script src="../assets/js/upload.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Register document input uploads with progress listeners
    <?php foreach ($document_list as $key => $doc): ?>
        <?php if ($docs_status[$key] !== 'Lengkap'): ?>
            initDocumentUpload(
                'input_<?php echo $key; ?>', 
                'progress_<?php echo $key; ?>_container', 
                'progress_<?php echo $key; ?>_bar', 
                'progress_<?php echo $key; ?>_text', 
                '<?php echo $key; ?>'
            );
        <?php endif; ?>
    <?php endforeach; ?>
});
</script>

<?php require_once 'includes/footer.php'; ?>
