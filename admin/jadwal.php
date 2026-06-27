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
        $gelombang = sanitize($_POST['gelombang']);
        $mulai = sanitize($_POST['mulai']);
        $selesai = sanitize($_POST['selesai']);
        $keterangan = sanitize($_POST['keterangan']);

        if (empty($gelombang) || empty($mulai) || empty($selesai)) {
            $error = 'Nama Gelombang, Tanggal Mulai dan Selesai wajib diisi!';
        } elseif ($mulai > $selesai) {
            $error = 'Tanggal Mulai tidak boleh melewati Tanggal Selesai!';
        } else {
            $query = "INSERT INTO jadwal (gelombang, mulai, selesai, keterangan) VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "ssss", $gelombang, $mulai, $selesai, $keterangan);
            
            if (mysqli_stmt_execute($stmt)) {
                $success = 'Gelombang pendaftaran baru berhasil ditambahkan!';
            } else {
                $error = 'Gagal menyimpan gelombang: ' . mysqli_error($conn);
            }
        }
    } 
    
    elseif ($action === 'edit') {
        $id = (int) $_POST['id'];
        $gelombang = sanitize($_POST['gelombang']);
        $mulai = sanitize($_POST['mulai']);
        $selesai = sanitize($_POST['selesai']);
        $keterangan = sanitize($_POST['keterangan']);

        if (empty($gelombang) || empty($mulai) || empty($selesai)) {
            $error = 'Nama Gelombang, Tanggal Mulai dan Selesai wajib diisi!';
        } elseif ($mulai > $selesai) {
            $error = 'Tanggal Mulai tidak boleh melewati Tanggal Selesai!';
        } else {
            $query = "UPDATE jadwal SET gelombang = ?, mulai = ?, selesai = ?, keterangan = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "ssssi", $gelombang, $mulai, $selesai, $keterangan, $id);
            
            if (mysqli_stmt_execute($stmt)) {
                $success = 'Gelombang pendaftaran berhasil diperbarui!';
            } else {
                $error = 'Gagal memperbarui gelombang: ' . mysqli_error($conn);
            }
        }
    } 
    
    elseif ($action === 'delete') {
        $id = (int) $_POST['id'];
        
        $query = "DELETE FROM jadwal WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = 'Gelombang pendaftaran berhasil dihapus!';
        } else {
            $error = 'Gagal menghapus gelombang: ' . mysqli_error($conn);
        }
    }
}

// Fetch all schedules from DB
$schedules = mysqli_query($conn, "SELECT * FROM jadwal ORDER BY mulai ASC, id DESC");
?>

<!-- Sidebar -->
<?php require_once 'includes/sidebar.php'; ?>

<!-- Main Content Area -->
<div class="main-content d-flex flex-column">
    <!-- Topbar -->
    <header class="top-navbar justify-content-between">
        <div class="d-flex align-items-center">
            <a href="#" class="text-dark me-3" id="sidebarToggle" aria-label="Toggle Sidebar"><i class="bi bi-justify fs-4"></i></a>
            <h4 class="fw-bold mb-0 text-primary-muh">Kelola Jadwal SPMB</h4>
        </div>
        <button class="btn btn-primary-muh btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="bi bi-plus-circle me-1"></i> Tambah Gelombang
        </button>
    </header>

    <!-- Page Content -->
    <main class="container-fluid p-4">
        <div class="card border-0 p-4 bg-white shadow-sm">
            <h5 class="fw-bold mb-4 border-bottom pb-2">Daftar Gelombang Pendaftaran</h5>

            <div class="table-responsive">
                <table id="jadwalTable" class="table table-striped table-hover align-middle" style="width:100%">
                    <thead>
                        <tr>
                            <th>Gelombang</th>
                            <th>Mulai</th>
                            <th>Selesai</th>
                            <th>Keterangan / Deskripsi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($schedules)): ?>
                            <tr>
                                <td class="fw-bold text-primary-muh"><?php echo htmlspecialchars($row['gelombang']); ?></td>
                                <td><?php echo date('d M Y', strtotime($row['mulai'])); ?></td>
                                <td><?php echo date('d M Y', strtotime($row['selesai'])); ?></td>
                                <td class="text-muted small"><?php echo htmlspecialchars($row['keterangan'] ?: '-'); ?></td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button class="btn btn-sm btn-outline-primary px-2 btn-edit" 
                                                data-id="<?php echo $row['id']; ?>" 
                                                data-gelombang="<?php echo htmlspecialchars($row['gelombang']); ?>" 
                                                data-mulai="<?php echo $row['mulai']; ?>" 
                                                data-selesai="<?php echo $row['selesai']; ?>" 
                                                data-keterangan="<?php echo htmlspecialchars($row['keterangan'] ?? ''); ?>" 
                                                data-bs-toggle="modal" data-bs-target="#editModal" title="Edit">
                                            <i class="bi bi-pencil-fill"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger px-2 btn-delete" 
                                                data-id="<?php echo $row['id']; ?>" 
                                                data-gelombang="<?php echo htmlspecialchars($row['gelombang']); ?>" title="Hapus">
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
    <div class="modal-dialog">
        <form action="" method="POST" class="needs-validation" novalidate>
            <input type="hidden" name="action" value="add">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="fw-bold">Tambah Gelombang Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nama Gelombang</label>
                        <input type="text" name="gelombang" class="form-control" placeholder="Contoh: Gelombang 1 Jalur Prestasi" required>
                        <div class="invalid-feedback">Wajib isi nama gelombang.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Tanggal Mulai</label>
                        <input type="date" name="mulai" class="form-control" required>
                        <div class="invalid-feedback">Wajib tentukan tanggal mulai.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Tanggal Selesai</label>
                        <input type="date" name="selesai" class="form-control" required>
                        <div class="invalid-feedback">Wajib tentukan tanggal selesai.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Keterangan Tambahan</label>
                        <input type="text" name="keterangan" class="form-control" placeholder="Contoh: Kuota terbatas / Jalur khusus">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary-muh">Simpan Gelombang</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- EDIT MODAL -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="" method="POST" class="needs-validation" novalidate>
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="fw-bold">Edit Gelombang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nama Gelombang</label>
                        <input type="text" name="gelombang" id="edit_gelombang" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Tanggal Mulai</label>
                        <input type="date" name="mulai" id="edit_mulai" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Tanggal Selesai</label>
                        <input type="date" name="selesai" id="edit_selesai" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Keterangan Tambahan</label>
                        <input type="text" name="keterangan" id="edit_keterangan" class="form-control">
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
    $('#jadwalTable').DataTable({
        language: {
            search: "Cari Gelombang:",
            lengthMenu: "Tampilkan _MENU_ data per halaman",
            zeroRecords: "Tidak ada jadwal tersedia",
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
        $('#edit_gelombang').val($(this).data('gelombang'));
        $('#edit_mulai').val($(this).data('mulai'));
        $('#edit_selesai').val($(this).data('selesai'));
        $('#edit_keterangan').val($(this).data('keterangan'));
    });

    // Delete button click logic
    $('.btn-delete').on('click', function() {
        const id = $(this).data('id');
        const gelombang = $(this).data('gelombang');
        
        Swal.fire({
            title: 'Hapus Gelombang?',
            text: "Gelombang '" + gelombang + "' akan dihapus dari portal publik!",
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
