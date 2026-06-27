<?php
require_once __DIR__ . '/../../config/db.php';
check_login('siswa');

// Fetch user pendaftaran details
$user_id = $_SESSION['user_id'];
$pendaftaran_query = mysqli_query($conn, "SELECT * FROM pendaftaran WHERE user_id = $user_id LIMIT 1");
$siswa = mysqli_fetch_assoc($pendaftaran_query);

if (!$siswa) {
    // Session exists but registration record is missing (should not happen due to transaction)
    session_destroy();
    header("Location: " . BASE_URL . "login.php");
    exit();
}

$no_pendaftaran = $siswa['no_pendaftaran'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Siswa Dashboard - SPMB SMP Muhammadiyah 1 Pringsewu</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <!-- Custom Style CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .sidebar {
            height: 100vh;
            position: sticky;
            top: 0;
        }
    </style>
</head>
<body>

    <!-- Loading Spinner Overlay -->
    <div id="loadingOverlay" class="loading-overlay">
        <div class="spinner-border text-primary-muh" style="width: 3rem; height: 3rem;" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <!-- Main Layout Container -->
    <div class="dashboard-container">
