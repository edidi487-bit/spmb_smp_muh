<?php
// Get current filename to set active class
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar d-flex flex-column justify-content-between py-4">
    <div>
        <!-- Sidebar Brand -->
        <div class="px-4 mb-4 text-center">
            <i class="bi bi-mortarboard-fill text-primary-muh display-5"></i>
            <h5 class="fw-bold mt-2 mb-0 text-primary-muh small text-uppercase">SPMB Calon Siswa</h5>
            <span class="text-muted small" style="font-size: 0.7rem;">SMP Muhammadiyah 1 Pringsewu</span>
        </div>

        <hr class="mx-3 my-3">

        <!-- Sidebar Navigation Menu -->
        <nav class="nav flex-column">
            <a class="nav-link-side <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>" href="dashboard.php">
                <i class="bi bi-grid-fill"></i> Dashboard
            </a>
            
            <a class="nav-link-side <?php echo ($current_page == 'pendaftaran.php') ? 'active' : ''; ?>" href="pendaftaran.php">
                <i class="bi bi-file-person-fill"></i> Data Pendaftaran
            </a>
            
            <a class="nav-link-side <?php echo ($current_page == 'upload.php') ? 'active' : ''; ?>" href="upload.php">
                <i class="bi bi-cloud-arrow-up-fill"></i> Upload Dokumen
            </a>
            
            <a class="nav-link-side <?php echo ($current_page == 'cetak.php') ? 'active' : ''; ?>" href="cetak.php">
                <i class="bi bi-printer-fill"></i> Cetak Kartu Bukti
            </a>
        </nav>
    </div>

    <!-- Sidebar Bottom controls -->
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
        
        <a class="nav-link-side text-danger" href="#" onclick="confirmLogout(event)">
            <i class="bi bi-box-arrow-left"></i> Logout
        </a>
    </div>
</aside>

<!-- Script for logout confirm -->
<script>
function confirmLogout(e) {
    e.preventDefault();
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Sesi masuk Anda akan diakhiri.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#0F7B3F',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Keluar!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '../logout.php';
        }
    });
}
</script>
