<?php
require_once 'includes/header.php';

$error = '';
$success = '';

// ==========================================
// 1. PROCESS CRUD OPERATIONS (POST)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = sanitize($_POST['action']);

    if ($action === 'add') {
        $judul = sanitize($_POST['judul']);
        $isi = sanitize($_POST['isi']);
        $tanggal = sanitize($_POST['tanggal']);

        if (empty($judul) || empty($isi) || empty($tanggal)) {
            $error = 'Semua field wajib diisi!';
        } else {
            $query = "INSERT INTO pengumuman (judul, isi, tanggal) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "sss", $judul, $isi, $tanggal);
            
            if (mysqli_stmt_execute($stmt)) {
                $success = 'Pengumuman baru berhasil ditambahkan!';
            } else {
                $error = 'Gagal menyimpan pengumuman: ' . mysqli_error($conn);
            }
        }
    } 
    
    elseif ($action === 'edit') {
        $id = (int) $_POST['id'];
        $judul = sanitize($_POST['judul']);
        $isi = sanitize($_POST['isi']);
        $tanggal = sanitize($_POST['tanggal']);

        if (empty($judul) || empty($isi) || empty($tanggal)) {
            $error = 'Semua field wajib diisi!';
        } else {
            $query = "UPDATE pengumuman SET judul = ?, isi = ?, tanggal = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "sssi", $judul, $isi, $tanggal, $id);
            
            if (mysqli_stmt_execute($stmt)) {
                $success = 'Pengumuman berhasil diperbarui!';
            } else {
                $error = 'Gagal memperbarui pengumuman: ' . mysqli_error($conn);
            }
        }
    } 
    
    elseif ($action === 'delete') {
        $id = (int) $_POST['id'];
        
        $query = "DELETE FROM pengumuman WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = 'Pengumuman berhasil dihapus!';
        } else {
            $error = 'Gagal menghapus pengumuman: ' . mysqli_error($conn);
        }
    }
}

// Fetch all announcements from DB
$announcements = mysqli_query($conn, "SELECT * FROM pengumuman ORDER BY tanggal DESC, id DESC");
?>

<!-- Sidebar -->
<?php require_once 'includes/sidebar.php'; ?>

<!-- Main Content Area -->
<div class="main-content d-flex flex-column">
    <!-- Topbar -->
    <header class="top-navbar justify-content-between">
        <div class="d-flex align-items-center">
            <a href="#" class="text-dark me-3" id="sidebarToggle" aria-label="Toggle Sidebar"><i class="bi bi-justify fs-4"></i></a>
            <h4 class="fw-bold mb-0 text-primary-muh">Kelola Pengumuman SPMB</h4>
        </div>
        <button class="btn btn-primary-muh btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="bi bi-plus-circle me-1"></i> Tambah Pengumuman
        </button>
    </header>

    <!-- Page Content -->
    <main class="container-fluid p-4">
        <div class="card border-0 p-4 bg-white shadow-sm">
            <h5 class="fw-bold mb-4 border-bottom pb-2">Daftar Pengumuman Sekolah</h5>

            <div class="table-responsive">
                <table id="pengumumanTable" class="table table-striped table-hover align-middle" style="width:100%">
                    <thead>
                        <tr>
                            <th style="width: 15%;">Tanggal</th>
                            <th style="width: 25%;">Judul Pengumuman</th>
                            <th style="width: 45%;">Isi Pengumuman</th>
                            <th style="width: 15%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($announcements)): ?>
                            <tr>
                                <td><?php echo date('d M Y', strtotime($row['tanggal'])); ?></td>
                                <td class="fw-bold text-primary-muh"><?php echo htmlspecialchars($row['judul']); ?></td>
                                <td class="text-muted small">
                                    <?php echo nl2br(htmlspecialchars(substr($row['isi'], 0, 150))) . (strlen($row['isi']) > 150 ? '...' : ''); ?>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button class="btn btn-sm btn-outline-primary px-2 btn-edit" 
                                                data-id="<?php echo $row['id']; ?>" 
                                                data-judul="<?php echo htmlspecialchars($row['judul']); ?>" 
                                                data-isi="<?php echo htmlspecialchars($row['isi']); ?>" 
                                                data-tanggal="<?php echo $row['tanggal']; ?>" 
                                                data-bs-toggle="modal" data-bs-target="#editModal" title="Edit">
                                            <i class="bi bi-pencil-fill"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger px-2 btn-delete" 
                                                data-id="<?php echo $row['id']; ?>" 
                                                data-judul="<?php echo htmlspecialchars($row['judul']); ?>" title="Hapus">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </div>
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
2. MODALS LAYOUT
========================================== -->

<!-- ADD MODAL -->
<div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="" method="POST" class="needs-validation" novalidate>
            <input type="hidden" name="action" value="add">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="fw-bold">Tambah Pengumuman Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Tanggal Rilis</label>
                        <input type="date" name="tanggal" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                        <div class="invalid-feedback">Tentukan tanggal pengumuman.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Judul Pengumuman</label>
                        <input type="text" name="judul" class="form-control" placeholder="Contoh: Informasi Pengembalian Berkas Fisik" required>
                        <div class="invalid-feedback">Wajib memasukkan judul pengumuman.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Isi Pengumuman</label>
                        <textarea name="isi" class="form-control" rows="6" placeholder="Ketikkan teks pengumuman lengkap di sini..." required></textarea>
                        <div class="invalid-feedback">Tuliskan konten isi pengumuman.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary-muh">Simpan Pengumuman</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- EDIT MODAL -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="" method="POST" class="needs-validation" novalidate>
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="fw-bold">Edit Pengumuman</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Tanggal Rilis</label>
                        <input type="date" name="tanggal" id="edit_tanggal" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Judul Pengumuman</label>
                        <input type="text" name="judul" id="edit_judul" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Isi Pengumuman</label>
                        <textarea name="isi" id="edit_isi" class="form-control" rows="6" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary-muh">Simpan Perubahan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- HIDDEN DELETE FORM -->
<form action="" method="POST" id="deleteForm" class="d-none">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="delete_id">
</form>

<?php require_once 'includes/footer.php'; ?>

<!-- DataTables bindings script -->
<script>
$(document).ready(function() {
    $('#pengumumanTable').DataTable({
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data per halaman",
            zeroRecords: "Tidak ada pengumuman tersedia",
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

    // Edit button click logic
    $('.btn-edit').on('click', function() {
        $('#edit_id').val($(this).data('id'));
        $('#edit_judul').val($(this).data('judul'));
        $('#edit_isi').val($(this).data('isi'));
        $('#edit_tanggal').val($(this).data('tanggal'));
    });

    // Delete button click logic
    $('.btn-delete').on('click', function() {
        const id = $(this).data('id');
        const judul = $(this).data('judul');
        
        Swal.fire({
            title: 'Hapus Pengumuman?',
            text: "Pengumuman '" + judul + "' akan dihapus secara permanen dari sistem!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#delete_id').val(id);
                $('#deleteForm').submit();
            }
        });
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
