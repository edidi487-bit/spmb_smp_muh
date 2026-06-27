<?php
require_once 'includes/header.php';

// 1. Fetch Stats Counters
$query_total = mysqli_query($conn, "SELECT COUNT(*) as total FROM pendaftaran");
$total_pendaftar = mysqli_fetch_assoc($query_total)['total'] ?? 0;

$query_docs = mysqli_query($conn, "SELECT COUNT(DISTINCT pendaftaran_id) as total FROM dokumen");
$total_berkas = mysqli_fetch_assoc($query_docs)['total'] ?? 0;

$query_lulus = mysqli_query($conn, "SELECT COUNT(*) as total FROM hasil_seleksi WHERE status_seleksi = 'LULUS'");
$total_lulus = mysqli_fetch_assoc($query_lulus)['total'] ?? 0;

$query_tidak = mysqli_query($conn, "SELECT COUNT(*) as total FROM hasil_seleksi WHERE status_seleksi = 'TIDAK LULUS'");
$total_tidak = mysqli_fetch_assoc($query_tidak)['total'] ?? 0;

$query_ta = mysqli_query($conn, "SELECT tahun FROM tahun_ajaran WHERE status = 'aktif' LIMIT 1");
$ta_row = mysqli_fetch_assoc($query_ta);
$active_ta = $ta_row['tahun'] ?? 'Belum Aktif';

// 2. Fetch Registration Trend Data (for Chart.js)
$chart_dates = [];
$chart_counts = [];
$query_chart = mysqli_query($conn, "SELECT DATE(tanggal_daftar) as tgl, COUNT(*) as jml FROM pendaftaran GROUP BY DATE(tanggal_daftar) ORDER BY DATE(tanggal_daftar) ASC LIMIT 15");
while ($row = mysqli_fetch_assoc($query_chart)) {
    $chart_dates[] = date('d M', strtotime($row['tgl']));
    $chart_counts[] = (int) $row['jml'];
}

// Fallback if no data exists
if (empty($chart_dates)) {
    $chart_dates = [date('d M')];
    $chart_counts = [0];
}
?>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Sidebar Layout -->
<?php require_once 'includes/sidebar.php'; ?>

<!-- Main Content Area -->
<div class="main-content d-flex flex-column">
    <!-- Topbar -->
    <header class="top-navbar justify-content-between">
        <div class="d-flex align-items-center">
            <a href="#" class="text-dark me-3" id="sidebarToggle" aria-label="Toggle Sidebar"><i class="bi bi-justify fs-4"></i></a>
            <h4 class="fw-bold mb-0 text-primary-muh">Dashboard Administrator</h4>
        </div>
        <div class="d-flex align-items-center">
            <span class="me-2 text-muted">User:</span>
            <span class="fw-bold text-dark"><?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?></span>
            <span class="badge bg-success ms-2">Admin</span>
        </div>
    </header>

    <!-- Page Content -->
    <main class="container-fluid p-4">
        <!-- Stats Summary Counters Grid -->
        <div class="row g-3 mb-4">
            <!-- Total Pendaftar -->
            <div class="col-6 col-lg-3">
                <div class="card stat-card p-3 border-0 bg-white h-100 card-hover">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small d-block">Total Pendaftar</span>
                            <h3 class="fw-bold mb-0 mt-1"><?php echo $total_pendaftar; ?></h3>
                        </div>
                        <div class="bg-primary bg-opacity-10 text-primary-muh p-3 rounded-3">
                            <i class="bi bi-people-fill fs-3"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="pendaftar.php" class="small text-decoration-none text-primary-muh">Lihat Detail <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
            </div>

            <!-- Berkas Masuk -->
            <div class="col-6 col-lg-3">
                <div class="card stat-card p-3 border-0 bg-white h-100 card-hover">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small d-block">Berkas Masuk</span>
                            <h3 class="fw-bold mb-0 mt-1"><?php echo $total_berkas; ?></h3>
                        </div>
                        <div class="bg-info bg-opacity-10 text-info p-3 rounded-3">
                            <i class="bi bi-file-earmark-check-fill fs-3"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="verifikasi.php" class="small text-decoration-none text-info">Lihat Detail <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
            </div>

            <!-- Lulus -->
            <div class="col-6 col-lg-3">
                <div class="card stat-card p-3 border-0 bg-white h-100 card-hover">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small d-block">Lulus Seleksi</span>
                            <h3 class="fw-bold mb-0 mt-1 text-success"><?php echo $total_lulus; ?></h3>
                        </div>
                        <div class="bg-success bg-opacity-10 text-success p-3 rounded-3">
                            <i class="bi bi-check-circle-fill fs-3"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="seleksi.php" class="small text-decoration-none text-success">Lihat Detail <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
            </div>

            <!-- Tidak Lulus -->
            <div class="col-6 col-lg-3">
                <div class="card stat-card p-3 border-0 bg-white h-100 card-hover">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small d-block">Tidak Lulus</span>
                            <h3 class="fw-bold mb-0 mt-1 text-danger"><?php echo $total_tidak; ?></h3>
                        </div>
                        <div class="bg-danger bg-opacity-10 text-danger p-3 rounded-3">
                            <i class="bi bi-x-circle-fill fs-3"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="seleksi.php" class="small text-decoration-none text-danger">Lihat Detail <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Left Side: Chart and Trend -->
            <div class="col-lg-8">
                <div class="card border-0 p-4 bg-white h-100">
                    <h5 class="fw-bold mb-3"><i class="bi bi-graph-up text-primary-muh me-2"></i> Tren Pendaftaran Calon Siswa</h5>
                    <div class="flex-grow-1" style="position: relative; height: 300px;">
                        <canvas id="registrationChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Right Side: School Metadata / Active School Year -->
            <div class="col-lg-4">
                <div class="card border-0 p-4 bg-white h-100 d-flex flex-column justify-content-between">
                    <div>
                        <h5 class="fw-bold mb-3"><i class="bi bi-info-circle-fill text-warning me-2"></i> Status Sistem</h5>
                        <ul class="list-group list-group-flush mb-4">
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent">
                                <span class="text-muted">Tahun Ajaran Aktif</span>
                                <span class="badge bg-primary-muh fs-6"><?php echo htmlspecialchars($active_ta); ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent">
                                <span class="text-muted">Server Time</span>
                                <span class="small fw-bold text-dark"><?php echo date('d F Y, H:i'); ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent">
                                <span class="text-muted">Database Server</span>
                                <span class="badge bg-success">Online</span>
                            </li>
                        </ul>
                    </div>
                    
                    <div class="p-3 bg-light rounded-3 mt-auto">
                        <h6 class="fw-bold mb-2">Aksi Cepat Admin:</h6>
                        <div class="d-grid gap-2">
                            <a href="pengumuman.php" class="btn btn-sm btn-outline-success text-start"><i class="bi bi-plus-circle me-1"></i> Tambah Pengumuman Baru</a>
                            <a href="jadwal.php" class="btn btn-sm btn-outline-success text-start"><i class="bi bi-calendar-plus me-1"></i> Edit Jadwal Pendaftaran</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Script to render registration trends chart -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    const ctx = document.getElementById('registrationChart').getContext('2d');
    
    // Gradient fill for line chart
    const primaryGrad = ctx.createLinearGradient(0, 0, 0, 300);
    primaryGrad.addColorStop(0, 'rgba(15, 123, 63, 0.4)');
    primaryGrad.addColorStop(1, 'rgba(15, 123, 63, 0.01)');

    const chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($chart_dates); ?>,
            datasets: [{
                label: 'Jumlah Pendaftar',
                data: <?php echo json_encode($chart_counts); ?>,
                borderColor: '#0F7B3F',
                borderWidth: 3,
                backgroundColor: primaryGrad,
                fill: true,
                tension: 0.3,
                pointBackgroundColor: '#0F7B3F',
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    },
                    grid: {
                        color: 'rgba(0,0,0,0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
