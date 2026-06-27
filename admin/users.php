<?php
require_once 'includes/header.php';

$error = '';
$success = '';

// ==========================================
// 1. PROCESS POST ACTIONS (CRUD & RESET)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = sanitize($_POST['action']);

    if ($action === 'add_admin') {
        $username = sanitize($_POST['username']);
        $nama_lengkap = sanitize($_POST['nama_lengkap']);
        $password = $_POST['password'];

        if (empty($username) || empty($nama_lengkap) || empty($password)) {
            $error = 'Semua kolom wajib diisi!';
        } else {
            // Check if username already exists
            $check = mysqli_query($conn, "SELECT id FROM users WHERE username = '$username' LIMIT 1");
            if (mysqli_num_rows($check) > 0) {
                $error = 'Username sudah digunakan!';
            } else {
                $hashed_pwd = password_hash($password, PASSWORD_DEFAULT);
                $insert = mysqli_query($conn, "INSERT INTO users (username, password, role, nama_lengkap) VALUES ('$username', '$hashed_pwd', 'admin', '$nama_lengkap')");
                if ($insert) {
                    $success = 'Akun Admin baru berhasil didaftarkan!';
                } else {
                    $error = 'Gagal mendaftarkan admin: ' . mysqli_error($conn);
                }
            }
        }
    } 
    
    elseif ($action === 'reset_pwd') {
        $user_id = (int) $_POST['user_id'];
        $new_pwd = $_POST['new_password'];

        if (empty($new_pwd)) {
            $error = 'Sandi baru tidak boleh kosong!';
        } elseif (strlen($new_pwd) < 6) {
            $error = 'Sandi baru minimal terdiri dari 6 karakter!';
        } else {
            $hashed_pwd = password_hash($new_pwd, PASSWORD_DEFAULT);
            $update = mysqli_query($conn, "UPDATE users SET password = '$hashed_pwd' WHERE id = $user_id");
            if ($update) {
                $success = 'Password berhasil direset!';
            } else {
                $error = 'Gagal mereset password: ' . mysqli_error($conn);
            }
        }
    } 
    
    elseif ($action === 'delete') {
        $user_id = (int) $_POST['user_id'];
        $self_id = $_SESSION['user_id'];

        if ($user_id === $self_id) {
            $error = 'Anda tidak dapat menghapus akun Anda sendiri!';
        } else {
            if (mysqli_query($conn, "DELETE FROM users WHERE id = $user_id")) {
                $success = 'Akun pengguna berhasil dihapus!';
            } else {
                $error = 'Gagal menghapus pengguna: ' . mysqli_error($conn);
            }
        }
    }
}

// Fetch all users
$users = mysqli_query($conn, "SELECT * FROM users ORDER BY role ASC, username ASC");
?>

<!-- Sidebar -->
<?php require_once 'includes/sidebar.php'; ?>

<!-- Main Content Area -->
<div class="main-content d-flex flex-column">
    <!-- Topbar -->
    <header class="top-navbar justify-content-between">
        <div class="d-flex align-items-center">
            <a href="#" class="text-dark me-3" id="sidebarToggle" aria-label="Toggle Sidebar"><i class="bi bi-justify fs-4"></i></a>
            <h4 class="fw-bold mb-0 text-primary-muh">Kelola Akun Pengguna</h4>
        </div>
        <button class="btn btn-primary-muh btn-sm" data-bs-toggle="modal" data-bs-target="#addAdminModal">
            <i class="bi bi-person-plus-fill me-1"></i> Tambah Staff Admin
        </button>
    </header>

    <!-- Page Content -->
    <main class="container-fluid p-4">
        <div class="card border-0 p-4 bg-white shadow-sm">
            <h5 class="fw-bold mb-4 border-bottom pb-2">Daftar Akun Pengguna Sistem</h5>

            <div class="table-responsive">
                <table id="userTable" class="table table-striped table-hover align-middle" style="width:100%">
                    <thead>
                        <tr>
                            <th>Username / NISN</th>
                            <th>Nama Lengkap</th>
                            <th>Hak Akses</th>
                            <th>Tanggal Registrasi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($users)): ?>
                            <tr>
                                <td class="fw-bold"><?php echo htmlspecialchars($row['username']); ?></td>
                                <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                                <td>
                                    <?php if ($row['role'] === 'admin'): ?>
                                        <span class="badge bg-success">Staff Admin</span>
                                    <?php else: ?>
                                        <span class="badge bg-primary">Siswa</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d M Y, H:i', strtotime($row['created_at'])); ?></td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button class="btn btn-sm btn-outline-warning px-2 btn-reset" 
                                                data-id="<?php echo $row['id']; ?>" 
                                                data-username="<?php echo htmlspecialchars($row['username']); ?>" 
                                                data-bs-toggle="modal" data-bs-target="#resetModal" title="Reset Password">
                                            <i class="bi bi-key-fill"></i> Reset
                                        </button>
                                        <?php if ($row['id'] !== $_SESSION['user_id']): ?>
                                            <button class="btn btn-sm btn-outline-danger px-2 btn-delete" 
                                                    data-id="<?php echo $row['id']; ?>" 
                                                    data-username="<?php echo htmlspecialchars($row['username']); ?>" 
                                                    data-role="<?php echo $row['role']; ?>" title="Hapus">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        <?php endif; ?>
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

<!-- ADD ADMIN MODAL -->
<div class="modal fade" id="addAdminModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="" method="POST" class="needs-validation" novalidate>
            <input type="hidden" name="action" value="add_admin">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="fw-bold">Tambah Akun Admin Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Username</label>
                        <input type="text" name="username" class="form-control" placeholder="Username untuk login" required>
                        <div class="invalid-feedback">Wajib isi username.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nama Lengkap Staff</label>
                        <input type="text" name="nama_lengkap" class="form-control" placeholder="Nama lengkap staf" required>
                        <div class="invalid-feedback">Wajib isi nama lengkap.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Password Akun</label>
                        <input type="password" name="password" class="form-control" minlength="6" placeholder="Minimal 6 karakter" required>
                        <div class="invalid-feedback">Sandi minimal 6 karakter.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary-muh">Daftarkan Admin</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- RESET PASSWORD MODAL -->
<div class="modal fade" id="resetModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="" method="POST" class="needs-validation" novalidate>
            <input type="hidden" name="action" value="reset_pwd">
            <input type="hidden" name="user_id" id="reset_user_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="fw-bold">Reset Password Akun</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Username Akun</label>
                        <input type="text" id="reset_username" class="form-control bg-light" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Sandi Baru</label>
                        <input type="password" name="new_password" class="form-control" minlength="6" placeholder="Ketik sandi baru minimal 6 karakter" required>
                        <div class="invalid-feedback">Sandi minimal 6 karakter.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-warning">Reset Password</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- HIDDEN DELETE FORM -->
<form action="" method="POST" id="deleteForm" class="d-none">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="user_id" id="delete_user_id">
</form>

<?php require_once 'includes/footer.php'; ?>

<!-- DataTables and triggers script -->
<script>
$(document).ready(function() {
    $('#userTable').DataTable({
        language: {
            search: "Cari Pengguna:",
            lengthMenu: "Tampilkan _MENU_ data per halaman",
            zeroRecords: "Tidak ada user terdaftar",
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

    // Reset password button logic
    $('.btn-reset').on('click', function() {
        $('#reset_user_id').val($(this).data('id'));
        $('#reset_username').val($(this).data('username'));
    });

    // Delete user button logic
    $('.btn-delete').on('click', function() {
        const id = $(this).data('id');
        const username = $(this).data('username');
        const role = $(this).data('role');
        
        let confirmText = "Wipe out user login credentials?";
        if (role === 'siswa') {
            confirmText = "Menghapus akun siswa " + username + " akan menghapus data pendaftaran, seluruh berkas upload, dan status kelulusan secara permanen!";
        }

        Swal.fire({
            title: 'Hapus Akun?',
            text: confirmText,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus Akun!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#delete_user_id').val(id);
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
