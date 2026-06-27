<?php
// Get current filename to set active class
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar d-flex flex-column justify-content-between py-4">
    <div>
        <!-- Sidebar Brand -->
        <div class="px-4 mb-4 text-center">
            <i class="bi bi-mortarboard-fill text-primary-muh display-5"></i>
            <h5 class="fw-bold mt-2 mb-0 text-primary-muh small text-uppercase">SPMB Panitia</h5>
            <span class="text-muted small" style="font-size: 0.7rem;">SMP Muhammadiyah 1 Pringsewu</span>
        </div>

        <hr class="mx-3 my-3">

        <!-- Sidebar Navigation Menu -->
        <nav class="nav flex-column">
            <a class="nav-link-side <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>" href="dashboard.php">
                <i class="bi bi-grid-fill"></i> Dashboard
            </a>
            
            <a class="nav-link-side <?php echo ($current_page == 'pendaftar.php') ? 'active' : ''; ?>" href="pendaftar.php">
                <i class="bi bi-people-fill"></i> Kelola Pendaftar
            </a>

            <a class="nav-link-side <?php echo ($current_page == 'verifikasi.php') ? 'active' : ''; ?>" href="verifikasi.php">
                <i class="bi bi-file-earmark-check-fill"></i> Verifikasi Berkas
            </a>

            <a class="nav-link-side <?php echo ($current_page == 'seleksi.php') ? 'active' : ''; ?>" href="seleksi.php">
                <i class="bi bi-check-circle-fill"></i> Seleksi Pendaftar
            </a>

            <a class="nav-link-side <?php echo ($current_page == 'pengumuman.php') ? 'active' : ''; ?>" href="pengumuman.php">
                <i class="bi bi-bell-fill"></i> Pengumuman
            </a>

            <a class="nav-link-side <?php echo ($current_page == 'jadwal.php') ? 'active' : ''; ?>" href="jadwal.php">
                <i class="bi bi-calendar-event-fill"></i> Jadwal SPMB
            </a>

            <a class="nav-link-side <?php echo ($current_page == 'users.php') ? 'active' : ''; ?>" href="users.php">
                <i class="bi bi-person-gear"></i> Kelola User
            </a>
        </nav>
    </div>

    <!-- Sidebar Bottom Controls -->
    <div>
        <div class="px-3 mb-2">
            <div class="d-flex align-items-center justify-content-between p-2 bg-light rounded-3">
                <span class="small text-muted">Mode Gelap</span>
                <button class="dark-mode-toggle btn-sm shadow-none" id="darkModeToggle">
                    <i class="bi bi-moon-stars-fill"></i>
                </button>
            </div>
        </div>
        
        <hr class="mx-3 my-2">
        
        <a class="nav-link-side text-danger" href="#" onclick="confirmAdminLogout(event)">
            <i class="bi bi-box-arrow-left"></i> Logout
        </a>
    </div>
</aside>

<!-- Script for admin logout confirm -->
<script>
function confirmAdminLogout(e) {
    e.preventDefault();
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Sesi admin Anda akan diakhiri.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#0F7B3F',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Logout!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'logout.php';
        }
    });
}
</script>
