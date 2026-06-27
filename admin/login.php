<?php
require_once __DIR__ . '/../config/db.php';

// Redirect if already logged in as admin
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin') {
    header("Location: dashboard.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = 'Username dan Password wajib diisi!';
    } else {
        $query = "SELECT * FROM users WHERE username = ? LIMIT 1";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) === 1) {
            $user = mysqli_fetch_assoc($result);
            if ($user['role'] !== 'admin') {
                $error = 'Akses ditolak! Akun ini bukan administrator.';
            } elseif (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                
                header("Location: dashboard.php");
                exit();
            } else {
                $error = 'Password salah!';
            }
        } else {
            $error = 'Akun administrator tidak terdaftar!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Panitia - SPMB SMP Muhammadiyah 1 Pringsewu</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <!-- Custom Style CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #0F7B3F 0%, #083c1f 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        body.dark-mode {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.95) 0%, rgba(30, 41, 59, 0.95) 100%);
        }
        .login-card {
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
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
                <div class="text-center mb-4 text-white">
                    <i class="bi bi-shield-lock-fill display-4 mb-2"></i>
                    <h3 class="fw-bold mb-1">SPMB Administrator</h3>
                    <p class="opacity-75">SMP Muhammadiyah 1 Pringsewu</p>
                </div>
                
                <div class="card login-card p-4 p-md-5 bg-white">
                    <h4 class="fw-bold mb-4 text-center text-dark">Login Panitia</h4>
                    
                    <form action="" method="POST" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="username" class="form-label small fw-bold text-dark">Username</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                                <input type="text" class="form-control" id="username" name="username" placeholder="Username Admin" required>
                                <div class="invalid-feedback">Silakan masukkan username.</div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="password" class="form-label small fw-bold text-dark">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Password Admin" required>
                                <div class="invalid-feedback">Silakan masukkan password.</div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary-muh w-100 py-2 mb-3">
                            Login Panitia <i class="bi bi-box-arrow-in-right ms-1"></i>
                        </button>
                        
                        <div class="text-center mt-3">
                            <a href="../index.php" class="text-muted small text-decoration-none"><i class="bi bi-arrow-left"></i> Kembali ke Beranda Utama</a>
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
    <script src="../assets/js/main.js"></script>
    
    <?php if ($error !== ''): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Login Gagal',
            text: '<?php echo $error; ?>'
        });
    </script>
    <?php endif; ?>
</body>
</html>
