<?php
require_once 'config/db.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: siswa/dashboard.php");
    }
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password']; // Do not sanitize passwords to keep original characters
    
    if (empty($username) || empty($password)) {
        $error = 'Username/NISN dan Password harus diisi!';
    } else {
        $query = "SELECT * FROM users WHERE username = ? LIMIT 1";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) === 1) {
            $user = mysqli_fetch_assoc($result);
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                
                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header("Location: admin/dashboard.php");
                } else {
                    header("Location: siswa/dashboard.php");
                }
                exit();
            } else {
                $error = 'Password yang Anda masukkan salah!';
            }
        } else {
            $error = 'Username atau NISN tidak terdaftar!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SPMB SMP Muhammadiyah 1 Pringsewu</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <!-- Custom Style CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
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
        .login-card {
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

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="text-center mb-4">
                    <a href="index.php" class="text-decoration-none">
                        <i class="bi bi-mortarboard-fill text-primary-muh display-4 d-block mb-2"></i>
                    </a>
                    <h3 class="fw-bold mb-1 text-primary-muh">Portal SPMB Online</h3>
                    <p class="text-muted">SMP Muhammadiyah 1 Pringsewu</p>
                </div>
                
                <div class="card login-card p-4 p-md-5">
                    <h4 class="fw-bold mb-4 text-center">Masuk ke Akun</h4>
                    
                    <form action="" method="POST" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="username" class="form-label small fw-bold">Username / NISN</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                                <input type="text" class="form-control" id="username" name="username" placeholder="Masukkan Username atau NISN" required>
                                <div class="invalid-feedback">Silakan masukkan username atau NISN Anda.</div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-2">
                                <label for="password" class="form-label small fw-bold mb-0">Password</label>
                            </div>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan Password" required>
                                <div class="invalid-feedback">Silakan masukkan password Anda.</div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary-muh w-100 py-2 mb-3">
                            Masuk <i class="bi bi-box-arrow-in-right ms-1"></i>
                        </button>
                        
                        <div class="text-center mt-3">
                            <span class="text-muted small">Belum punya akun?</span>
                            <a href="register.php" class="text-primary-muh small fw-bold text-decoration-none ms-1">Daftar Akun Baru</a>
                        </div>
                        <div class="text-center mt-2">
                            <a href="index.php" class="text-muted small text-decoration-none"><i class="bi bi-arrow-left"></i> Kembali ke Beranda</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Custom Main JS -->
    <script src="assets/js/main.js"></script>
    
    <?php if ($error !== ''): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Gagal Masuk',
            text: '<?php echo $error; ?>'
        });
    </script>
    <?php endif; ?>
</body>
</html>
