<?php
require_once 'includes/header.php';

$error = '';
$success = '';

// ==========================================
// 1. UPDATE SELECTION DECISION (POST)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'set_seleksi') {
    $pendaftaran_id = (int) $_POST['pendaftaran_id'];
    $status_seleksi = sanitize($_POST['status_seleksi']);
    $keterangan = sanitize($_POST['keterangan']);

    if (empty($status_seleksi)) {
        $error = 'Pilih status kelulusan!';
    } else {
        // Set default congratulatory/informational messages based on status if left empty
        if (empty($keterangan)) {
            if ($status_seleksi === 'LULUS') {
                $keterangan = 'Selamat! Anda dinyatakan Lulus Seleksi Administrasi di SMP Muhammadiyah 1 Pringsewu. Harap segera melakukan daftar ulang di gedung sekolah pada tanggal 16-20 Juli dengan membawa berkas asli.';
            } elseif ($status_seleksi === 'TIDAK LULUS') {
                $keterangan = 'Terima kasih atas partisipasi Anda dalam mengikuti rangkaian seleksi penerimaan murid baru kami.';
            } else {
                $keterangan = 'Berkas Anda sedang diverifikasi oleh panitia seleksi.';
            }
        }

        // Run update query on hasil_seleksi
        $update_sql = "UPDATE hasil_seleksi SET status_seleksi = ?, keterangan = ? WHERE pendaftaran_id = ?";
        $stmt = mysqli_prepare($conn, $update_sql);
        mysqli_stmt_bind_param($stmt, "ssi", $status_seleksi, $keterangan, $pendaftaran_id);

        if (mysqli_stmt_execute($stmt)) {
            $success = 'Keputusan kelulusan pendaftar berhasil disimpan!';
        } else {
            $error = 'Gagal menyimpan hasil seleksi: ' . mysqli_error($conn);
        }
    }
}

// Fetch all registered students along with their selection status
$query = "SELECT p.*, h.status_seleksi, h.keterangan as catatan_seleksi, t.tahun as tahun_ajaran 
          FROM pendaftaran p 
          LEFT JOIN hasil_seleksi h ON p.id = h.pendaftaran_id 
          JOIN tahun_ajaran t ON p.tahun_ajaran_id = t.id
          ORDER BY p.id DESC";
$siswa_list = mysqli_query($conn, $query);
?>

<!-- Sidebar -->
<?php require_once 'includes/sidebar.php'; ?>

<!-- Main Content Area -->
<div class="main-content d-flex flex-column">
    <!-- Topbar -->
    <header class="top-navbar justify-content-between">
        <div class="d-flex align-items-center">
            <a href="#" class="text-dark me-3" id="sidebarToggle" aria-label="Toggle Sidebar"><i class="bi bi-justify fs-4"></i></a>
            <h4 class="fw-bold mb-0 text-primary-muh">Seleksi Calon Murid Baru</h4>
        </div>
    </header>

    <!-- Page Content -->
    <main class="container-fluid p-4">
        <div class="card border-0 p-4 bg-white shadow-sm">
            <h5 class="fw-bold mb-4 border-bottom pb-2">Hasil Keputusan Seleksi Calon Siswa</h5>

            <div class="table-responsive">
                <table id="seleksiTable" class="table table-striped table-hover align-middle" style="width:100%">
                    <thead>
                        <tr>
                            <th>No. Daftar</th>
                            <th>NISN</th>
                            <th>Nama Lengkap</th>
                            <th>Asal Sekolah</th>
                            <th>Tahun Ajaran</th>
                            <th>Status Seleksi</th>
                            <th>Catatan Kelulusan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($siswa_list)): 
                            $status = $row['status_seleksi'] ?? 'Belum Diseleksi';
                        ?>
                            <tr>
                                <td class="fw-bold"><?php echo $row['no_pendaftaran']; ?></td>
                                <td><?php echo $row['nisn']; ?></td>
                                <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                                <td><?php echo htmlspecialchars($row['asal_sekolah'] ?: '-'); ?></td>
                                <td><?php echo htmlspecialchars($row['tahun_ajaran']); ?></td>
                                <td>
                                    <?php if ($status === 'LULUS'): ?>
                                        <span class="badge bg-success">🟢 LULUS</span>
                                    <?php elseif ($status === 'TIDAK LULUS'): ?>
                                        <span class="badge bg-danger">🔴 TIDAK LULUS</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">🟡 BELUM DISELEKSI</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted small text-truncate" style="max-width: 200px;" title="<?php echo htmlspecialchars($row['catatan_seleksi']); ?>">
                                    <?php echo htmlspecialchars($row['catatan_seleksi'] ?: '-'); ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary-muh py-1 px-3 btn-decision" 
                                            data-id="<?php echo $row['id']; ?>" 
                                            data-nama="<?php echo htmlspecialchars($row['nama_lengkap']); ?>" 
                                            data-status="<?php echo $status; ?>" 
                                            data-keterangan="<?php echo htmlspecialchars($row['catatan_seleksi'] ?? ''); ?>"
                                            data-bs-toggle="modal" data-bs-target="#decisionModal">
                                        <i class="bi bi-shield-check"></i> Seleksi
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<!-- ==========================================
2. DECISION MODAL
========================================== -->
<div class="modal fade" id="decisionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="" method="POST">
            <input type="hidden" name="action" value="set_seleksi">
            <input type="hidden" name="pendaftaran_id" id="decision_pendaftaran_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="fw-bold">Tentukan Kelulusan Calon Siswa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nama Calon Siswa</label>
                        <input type="text" id="decision_nama" class="form-control bg-light" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Keputusan Kelulusan</label>
                        <select name="status_seleksi" id="decision_status" class="form-select" required>
                            <option value="Belum Diseleksi">Belum Diseleksi</option>
                            <option value="LULUS">🟢 LULUS</option>
                            <option value="TIDAK LULUS">🔴 TIDAK LULUS</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Keterangan / Pengumuman Lanjutan</label>
                        <textarea name="keterangan" id="decision_keterangan" class="form-control" rows="4" placeholder="Tuliskan jadwal daftar ulang atau alasan tidak lulus di sini..."></textarea>
                        <div class="form-text small text-muted">Akan ditampilkan langsung di dashboard siswa & portal kelulusan umum. Kosongkan untuk menggunakan pesan default sistem.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary-muh">Simpan Keputusan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

<!-- DataTables & modal bindings script -->
<script>
$(document).ready(function() {
    $('#seleksiTable').DataTable({
        language: {
            search: "Cari Calon Siswa:",
            lengthMenu: "Tampilkan _MENU_ data per halaman",
            zeroRecords: "Data tidak ditemukan",
            info: "Menampilkan halaman _PAGE_ dari _PAGES_",
            infoEmpty: "Tidak ada data tersedia",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Selanjutnya",
                previous: "Sebelumnya"
            }
        }
    });

    // Bind data elements to Decision Modal when clicked
    $('.btn-decision').on('click', function() {
        $('#decision_pendaftaran_id').val($(this).data('id'));
        $('#decision_nama').val($(this).data('nama'));
        $('#decision_status').val($(this).data('status'));
        $('#decision_keterangan').val($(this).data('keterangan'));
    });
});
</script>

<?php if ($error !== ''): ?>
<script>
    showToast('error', '<?php echo $error; ?>');
</script>
<?php endif; ?>

<?php if ($success !== ''): ?>
<script>
    showToast('success', '<?php echo $success; ?>');
</script>
<?php endif; ?>
