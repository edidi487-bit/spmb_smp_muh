<?php
// Prevent direct access
if (count(get_included_files()) === 1) {
    http_response_code(403);
    exit("Direct access not allowed.");
}

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'spmb_muh');
define('BASE_URL', '/spmb_smp_muh/');

// Establish Connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die("Database Connection failed: " . mysqli_connect_error());
}

// Set charset to utf8mb4
mysqli_set_charset($conn, "utf8mb4");

// Start Session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Global sanitization helper
function sanitize($data) {
    global $conn;
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return mysqli_real_escape_string($conn, htmlspecialchars(strip_tags(trim($data))));
}

// Helper to generate Auto Increment Registration Number: SPMB-YYYY-XXXX
function generateNoPendaftaran() {
    global $conn;
    $year = date('Y');
    $query = "SELECT no_pendaftaran FROM pendaftaran WHERE no_pendaftaran LIKE 'SPMB-$year-%' ORDER BY id DESC LIMIT 1";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $last_no = $row['no_pendaftaran'];
        $num = (int) substr($last_no, 10);
        $num++;
    } else {
        $num = 1;
    }
    
    return "SPMB-$year-" . str_pad($num, 4, '0', STR_PAD_LEFT);
}

// Authentication Check Helper
function check_login($role = null) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . BASE_URL . "login.php");
        exit();
    }
    if ($role && $_SESSION['role'] !== $role) {
        if ($_SESSION['role'] === 'admin') {
            header("Location: " . BASE_URL . "admin/dashboard.php");
        } else {
            header("Location: " . BASE_URL . "siswa/dashboard.php");
        }
        exit();
    }
}
?>
