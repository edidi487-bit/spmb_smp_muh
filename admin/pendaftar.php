<?php
require_once 'includes/header.php';

$error = '';
$success = '';

// ==========================================
// 1. PROCESS CRUD ACTIONS (POST)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = sanitize($_POST['action']);

    if ($action === 'add') {
        $nisn = sanitize($_POST['nisn']);
        $nama_lengkap = sanitize($_POST['nama_lengkap']);
        $email = sanitize($_POST['email']);
        $password = $_POST['password'];
        $tahun_ajaran_id = (int) $_POST['tahun_ajaran_id'];

        if (empty($nisn) || empty($nama_lengkap) || empty($email) || empty($password) || empty($tahun_ajaran_id)) {
            $error = 'Semua kolom wajib diisi!';
        } elseif (!preg_match('/^[0-9]{10}$/', $nisn)) {
            $error = 'NISN harus terdiri dari 10 digit angka!';
        } else {
            // Check if NISN exists
            $check = mysqli_query($conn, "SELECT id FROM users WHERE username = '$nisn' LIMIT 1");
            if (mysqli_num_rows($check) > 0) {
                $error = 'NISN sudah terdaftar!';
            } else {
                mysqli_begin_transaction($conn);
                try {
                    $hashed_pwd = password_hash($password, PASSWORD_DEFAULT);
                    // Insert into users
                    mysqli_query($conn, "INSERT INTO users (username, password, role, nama_lengkap) VALUES ('$nisn', '$hashed_pwd', 'siswa', '$nama_lengkap')");
                    $user_id = mysqli_insert_id($conn);

                    // Insert pendaftaran
                    $no_pend = generateNoPendaftaran();
                    $insert_pend = "INSERT INTO pendaftaran (user_id, no_pendaftaran, nisn, NIK, nama_lengkap, tempat_lahir, tanggal_lahir, jenis_kelamin, agama, alamat, asal_sekolah, no_hp, email, nama_ayah, nama_ibu, pekerjaan_ortu, tahun_ajaran_id) 
                                    VALUES ($user_id, '$no_pend', '$nisn', '', '$nama_lengkap', '', '2012-01-01', 'L', 'Islam', '', '', '', '$email', '', '', '', $tahun_ajaran_id)";
                    mysqli_query($conn, $insert_pend);
                    $pend_id = mysqli_insert_id($conn);

                    // Insert hasil_seleksi
                    mysqli_query($conn, "INSERT INTO hasil_seleksi (pendaftaran_id, status_seleksi, keterangan) VALUES ($pend_id, 'Belum Diseleksi', 'Berkas Anda sedang diverifikasi.')");
                    
                    mysqli_commit($conn);
                    $success = 'Pendaftar baru berhasil ditambahkan!';
                } catch (Exception $e) {
                    mysqli_rollback($conn);
                    $error = 'Gagal menyimpan data: ' . $e->getMessage();
                }
            }
        }
    } 
    
    elseif ($action === 'edit') {
        $id = (int) $_POST['id'];
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
        $tahun_ajaran_id = (int) $_POST['tahun_ajaran_id'];

        if (empty($nama_lengkap) || empty($email) || empty($tahun_ajaran_id)) {
            $error = 'Nama Lengkap, Email, dan Tahun Ajaran wajib diisi!';
        } else {
            mysqli_begin_transaction($conn);
            try {
                // Fetch user_id
                $get_user = mysqli_query($conn, "SELECT user_id FROM pendaftaran WHERE id = $id LIMIT 1");
                $user_row = mysqli_fetch_assoc($get_user);
                $user_id = $user_row['user_id'];

                // Update users
                mysqli_query($conn, "UPDATE users SET nama_lengkap = '$nama_lengkap' WHERE id = $user_id");

                // Update pendaftaran
                $update_sql = "UPDATE pendaftaran SET 
                    nik = '$nik', nama_lengkap = '$nama_lengkap', tempat_lahir = '$tempat_lahir', tanggal_lahir = '$tanggal_lahir', 
                    jenis_kelamin = '$jenis_kelamin', agama = '$agama', alamat = '$alamat', asal_sekolah = '$asal_sekolah', 
                    no_hp = '$no_hp', email = '$email', nama_ayah = '$nama_ayah', nama_ibu = '$nama_ibu', 
                    pekerjaan_ortu = '$pekerjaan_ortu', tahun_ajaran_id = $tahun_ajaran_id 
                    WHERE id = $id";
                mysqli_query($conn, $update_sql);

                mysqli_commit($conn);
                $success = 'Data pendaftar berhasil diperbarui!';
            } catch (Exception $e) {
                mysqli_rollback($conn);
                $error = 'Gagal memperbarui data: ' . $e->getMessage();
            }
        }
    } 
    
    elseif ($action === 'delete') {
        $user_id = (int) $_POST['user_id'];
        
        // Deleting user will automatically cascade delete pendaftaran, dokumen, and hasil_seleksi
        if (mysqli_query($conn, "DELETE FROM users WHERE id = $user_id")) {
            $success = 'Pendaftar berhasil dihapus!';
        } else {
            $error = 'Gagal menghapus pendaftar: ' . mysqli_error($conn);
        }
    }
}

// Fetch all academic years for selection dropdowns
$ta_options = mysqli_query($conn, "SELECT * FROM tahun_ajaran ORDER BY status ASC, tahun DESC");
$ta_arr = [];
while ($row = mysqli_fetch_assoc($ta_options)) {
    $ta_arr[] = $row;
}

// Fetch students list
$siswa_list = mysqli_query($conn, "SELECT p.*, u.id as user_id, u.username as login_username, t.tahun as tahun_ajaran, h.status_seleksi 
                                   FROM pendaftaran p 
                                   JOIN users u ON p.user_id = u.id 
                                   JOIN tahun_ajaran t ON p.tahun_ajaran_id = t.id 
                                   LEFT JOIN hasil_seleksi h ON p.id = h.pendaftaran_id 
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
            <h4 class="fw-bold mb-0 text-primary-muh">Kelola Calon Pendaftar</h4>
        </div>
        <button class="btn btn-primary-muh btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="bi bi-plus-circle me-1"></i> Tambah Pendaftar
        </button>
    </header>

    <!-- Page Content -->
    <main class="container-fluid p-4">
        <div class="card border-0 p-4 bg-white shadow-sm">
            
            <!-- Custom DataTables filter bar -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Filter Jenis Kelamin</label>
                    <select id="filterJK" class="form-select form-select-sm">
                        <option value="">Semua</option>
                        <option value="Laki-laki">Laki-laki</option>
                        <option value="Perempuan">Perempuan</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Filter Status Seleksi</label>
                    <select id="filterStatus" class="form-select form-select-sm">
                        <option value="">Semua</option>
                        <option value="LULUS">LULUS</option>
                        <option value="TIDAK LULUS">TIDAK LULUS</option>
                        <option value="Belum Diseleksi">Belum Diseleksi</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Filter Tahun Ajaran</label>
                    <select id="filterTA" class="form-select form-select-sm">
                        <option value="">Semua</option>
                        <?php foreach ($ta_arr as $ta): ?>
                            <option value="<?php echo htmlspecialchars($ta['tahun']); ?>"><?php echo htmlspecialchars($ta['tahun']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- DataTables Table -->
            <div class="table-responsive">
                <table id="pendaftarTable" class="table table-striped table-hover align-middle" style="width:100%">
                    <thead>
                        <tr>
                            <th>No. Daftar</th>
                            <th>NISN</th>
                            <th>Nama Lengkap</th>
                            <th>L/P</th>
                            <th>Asal Sekolah</th>
                            <th>Tahun Ajaran</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($s = mysqli_fetch_assoc($siswa_list)): ?>
                            <tr>
                                <td class="fw-bold"><?php echo $s['no_pendaftaran']; ?></td>
                                <td><?php echo $s['nisn']; ?></td>
                                <td><?php echo htmlspecialchars($s['nama_lengkap']); ?></td>
                                <td><?php echo ($s['jenis_kelamin'] === 'L') ? 'Laki-laki' : 'Perempuan'; ?></td>
                                <td><?php echo htmlspecialchars($s['asal_sekolah'] ?: '-'); ?></td>
                                <td><?php echo htmlspecialchars($s['tahun_ajaran']); ?></td>
                                <td>
                                    <?php if ($s['status_seleksi'] === 'LULUS'): ?>
                                        <span class="badge bg-success">LULUS</span>
                                    <?php elseif ($s['status_seleksi'] === 'TIDAK LULUS'): ?>
                                        <span class="badge bg-danger">TIDAK LULUS</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Belum Diseleksi</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button class="btn btn-sm btn-outline-primary px-2 btn-edit" data-id="<?php echo $s['id']; ?>" 
                                                data-nisn="<?php echo $s['nisn']; ?>" data-nik="<?php echo $s['nik']; ?>" 
                                                data-nama="<?php echo htmlspecialchars($s['nama_lengkap']); ?>" 
                                                data-tempat="<?php echo htmlspecialchars($s['tempat_lahir']); ?>" 
                                                data-tgl="<?php echo $s['tanggal_lahir']; ?>" data-jk="<?php echo $s['jenis_kelamin']; ?>" 
                                                data-agama="<?php echo $s['agama']; ?>" data-alamat="<?php echo htmlspecialchars($s['alamat']); ?>" 
                                                data-asal="<?php echo htmlspecialchars($s['asal_sekolah']); ?>" data-hp="<?php echo $s['no_hp']; ?>" 
                                                data-email="<?php echo $s['email']; ?>" data-ayah="<?php echo htmlspecialchars($s['nama_ayah']); ?>" 
                                                data-ibu="<?php echo htmlspecialchars($s['nama_ibu']); ?>" data-pekerjaan="<?php echo htmlspecialchars($s['pekerjaan_ortu']); ?>" 
                                                data-ta="<?php echo $s['tahun_ajaran_id']; ?>" data-bs-toggle="modal" data-bs-target="#editModal" title="Edit Data">
                                            <i class="bi bi-pencil-fill"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger px-2 btn-delete" data-uid="<?php echo $s['user_id']; ?>" data-nama="<?php echo htmlspecialchars($s['nama_lengkap']); ?>" title="Hapus Data">
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
                    <h5 class="fw-bold">Tambah Pendaftar Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">NISN (10 Digit)</label>
                        <input type="text" name="nisn" class="form-control" pattern="[0-9]{10}" maxlength="10" placeholder="10 Digit Angka" required>
                        <div class="invalid-feedback">Masukkan 10 digit NISN yang valid.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" class="form-control" placeholder="Nama Lengkap Calon Siswa" required>
                        <div class="invalid-feedback">Wajib isi nama lengkap.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="alamat@gmail.com" required>
                        <div class="invalid-feedback">Masukkan format email valid.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Password Akun</label>
                        <input type="password" name="password" class="form-control" minlength="6" placeholder="Min 6 Karakter" required>
                        <div class="invalid-feedback">Sandi minimal 6 karakter.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Tahun Ajaran</label>
                        <select name="tahun_ajaran_id" class="form-select" required>
                            <?php foreach ($ta_arr as $ta): ?>
                                <option value="<?php echo $ta['id']; ?>" <?php echo ($ta['status'] === 'aktif') ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($ta['tahun']); ?> (<?php echo $ta['status']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary-muh">Tambah</button>
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
                    <h5 class="fw-bold">Edit Biodata Pendaftar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">NISN (Tidak dapat diubah)</label>
                            <input type="text" id="edit_nisn" class="form-control bg-light" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">NIK (16 Digit)</label>
                            <input type="text" name="nik" id="edit_nik" class="form-control" pattern="[0-9]{16}" maxlength="16" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" id="edit_nama" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" id="edit_tempat" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" id="edit_tgl" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Jenis Kelamin</label>
                            <select name="jenis_kelamin" id="edit_jk" class="form-select" required>
                                <option value="L">Laki-laki (L)</option>
                                <option value="P">Perempuan (P)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Agama</label>
                            <select name="agama" id="edit_agama" class="form-select" required>
                                <option value="Islam">Islam</option>
                                <option value="Kristen">Kristen</option>
                                <option value="Katolik">Katolik</option>
                                <option value="Hindu">Hindu</option>
                                <option value="Buddha">Buddha</option>
                                <option value="Konghucu">Konghucu</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Alamat Lengkap</label>
                            <textarea name="alamat" id="edit_alamat" class="form-control" rows="2" required></textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label small fw-bold">Asal Sekolah (SD/MI)</label>
                            <input type="text" name="asal_sekolah" id="edit_asal" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">No HP</label>
                            <input type="tel" name="no_hp" id="edit_hp" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Email</label>
                            <input type="email" name="email" id="edit_email" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Nama Ayah</label>
                            <input type="text" name="nama_ayah" id="edit_ayah" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Nama Ibu</label>
                            <input type="text" name="nama_ibu" id="edit_ibu" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Pekerjaan Orang Tua</label>
                            <input type="text" name="pekerjaan_ortu" id="edit_pekerjaan" class="form-control" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label small fw-bold">Tahun Ajaran</label>
                            <select name="tahun_ajaran_id" id="edit_ta" class="form-select" required>
                                <?php foreach ($ta_arr as $ta): ?>
                                    <option value="<?php echo $ta['id']; ?>"><?php echo htmlspecialchars($ta['tahun']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
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
    <input type="hidden" name="user_id" id="delete_uid">
</form>

<?php require_once 'includes/footer.php'; ?>

<!-- DataTables setup and execution -->
<script>
$(document).ready(function() {
    // 1. Initialize DataTable with Excel & PDF Exports
    const table = $('#pendaftarTable').DataTable({
        dom: "<'row'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        buttons: [
            {
                extend: 'excelHtml5',
                text: '<i class="bi bi-file-earmark-excel-fill"></i> Export Excel',
                className: 'btn btn-success btn-sm me-2 rounded',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6]
                }
            },
            {
                extend: 'pdfHtml5',
                text: '<i class="bi bi-file-earmark-pdf-fill"></i> Export PDF',
                className: 'btn btn-danger btn-sm rounded',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6]
                },
                customize: function (doc) {
                    doc.content[1].table.widths = Array(doc.content[1].table.body[0].length + 1).join('*').split('');
                }
            }
        ],
        language: {
            search: "Cari Pendaftar:",
            lengthMenu: "Tampilkan _MENU_ data per halaman",
            zeroRecords: "Data tidak ditemukan",
            info: "Menampilkan halaman _PAGE_ dari _PAGES_",
            infoEmpty: "Tidak ada data tersedia",
            infoFiltered: "(disaring dari _MAX_ total data)",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Selanjutnya",
                previous: "Sebelumnya"
            }
        }
    });

    // 2. Custom Filter Implementations
    $('#filterJK').on('change', function() {
        table.column(3).search(this.value).draw();
    });

    $('#filterStatus').on('change', function() {
        table.column(6).search(this.value).draw();
    });

    $('#filterTA').on('change', function() {
        table.column(5).search(this.value).draw();
    });

    // 3. Edit Button Handler: bind data attributes to Edit Modal inputs
    $('.btn-edit').on('click', function() {
        $('#edit_id').val($(this).data('id'));
        $('#edit_nisn').val($(this).data('nisn'));
        $('#edit_nik').val($(this).data('nik'));
        $('#edit_nama').val($(this).data('nama'));
        $('#edit_tempat').val($(this).data('tempat'));
        $('#edit_tgl').val($(this).data('tgl'));
        $('#edit_jk').val($(this).data('jk'));
        $('#edit_agama').val($(this).data('agama'));
        $('#edit_alamat').val($(this).data('alamat'));
        $('#edit_asal').val($(this).data('asal'));
        $('#edit_hp').val($(this).data('hp'));
        $('#edit_email').val($(this).data('email'));
        $('#edit_ayah').val($(this).data('ayah'));
        $('#edit_ibu').val($(this).data('ibu'));
        $('#edit_pekerjaan').val($(this).data('pekerjaan'));
        $('#edit_ta').val($(this).data('ta'));
    });

    // 4. Delete Confirm Handler using SweetAlert
    $('.btn-delete').on('click', function() {
        const uid = $(this).data('uid');
        const nama = $(this).data('nama');
        
        Swal.fire({
            title: 'Hapus Calon Siswa?',
            text: "Menghapus akun " + nama + " juga akan menghapus data pendaftaran, seluruh berkas upload, dan status kelulusan secara permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus Akun!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#delete_uid').val(uid);
                $('#deleteForm').submit();
            }
        });
    });
});
</script>

<!-- SweetAlert notification toasts -->
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
