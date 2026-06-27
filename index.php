<?php
require_once 'config/db.php';

// Fetch statistics
$total_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM pendaftaran");
$total_row = mysqli_fetch_assoc($total_query);
$total_pendaftar = $total_row['total'] ?? 0;

// Fetch Active schedules
$jadwal_query = mysqli_query($conn, "SELECT * FROM jadwal ORDER BY mulai ASC");

// Fetch Announcements
$pengumuman_query = mysqli_query($conn, "SELECT * FROM pengumuman ORDER BY tanggal DESC LIMIT 3");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPMB - SMP Muhammadiyah 1 Pringsewu</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom Style CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <!-- Loading Spinner Overlay -->
    <div id="loadingOverlay" class="loading-overlay">
        <div class="spinner-border text-primary-muh" style="width: 3rem; height: 3rem;" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-custom fixed-top bg-transparent">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center text-white navbar-scrolled-text" href="#">
                <i class="bi bi-mortarboard-fill me-2 fs-3 text-warning"></i>
                <div>
                    <span class="fw-bold d-block lh-1 text-uppercase fs-6">SMP MUHAMMADIYAH 1</span>
                    <span class="small d-block text-uppercase" style="font-size: 0.75rem; letter-spacing: 1px;">Pringsewu</span>
                </div>
            </a>
            <button class="navbar-toggler text-white border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <i class="bi bi-list fs-1"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link text-white" href="#home">Home</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="#info">Informasi</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="#keunggulan">Keunggulan</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="#alur">Alur</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="#syarat">Persyaratan</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="#jadwal">Jadwal</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="#faq">FAQ</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="#kontak">Kontak</a></li>
                    <li class="nav-item px-2">
                        <button class="dark-mode-toggle text-white" id="darkModeToggle" aria-label="Dark Mode Toggle">
                            <i class="bi bi-moon-stars-fill"></i>
                        </button>
                    </li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item ms-2">
                            <a class="btn btn-warning fw-bold px-4 py-2" href="<?php echo ($_SESSION['role'] === 'admin') ? 'admin/dashboard.php' : 'siswa/dashboard.php'; ?>">
                                <i class="bi bi-speedometer2 me-1"></i> Dashboard
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item ms-2 mb-2 mb-lg-0">
                            <a class="btn btn-outline-light px-3 py-2" href="login.php">Masuk</a>
                        </li>
                        <li class="nav-item ms-lg-2">
                            <a class="btn btn-warning fw-bold px-4 py-2" href="register.php">Daftar Sekarang</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero-section" style="background-image: url('https://images.unsplash.com/photo-1541339907198-e08756dedf3f?auto=format&fit=crop&w=1920&q=80');">
        <div class="hero-overlay"></div>
        <div class="container hero-content text-center text-lg-start">
            <div class="row align-items-center">
                <div class="col-lg-7 animate-fade-in">
                    <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold mb-3 text-uppercase">SPMB Online <?php echo date('Y'); ?></span>
                    <h1 class="display-3 fw-bold mb-3 text-white">Selamat Datang di SMP Muhammadiyah 1 Pringsewu</h1>
                    <p class="lead mb-4 text-white-50">Sekolah Berkemajuan yang Unggul dalam Prestasi, Mandiri, dan Berakhlak Mulia Berlandaskan Nilai-Nilai Keislaman.</p>
                    <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center justify-content-lg-start">
                        <a href="register.php" class="btn btn-warning btn-lg px-4 py-3 fw-bold shadow-sm">
                            <i class="bi bi-pencil-square me-2"></i> Daftar Sekarang
                        </a>
                        <a href="#info" class="btn btn-outline-light btn-lg px-4 py-3">
                            <i class="bi bi-info-circle me-2"></i> Panduan SPMB
                        </a>
                        <a href="siswa/status.php" class="btn btn-info btn-lg px-4 py-3 fw-bold text-white shadow-sm">
                            <i class="bi bi-search me-2"></i> Cek Kelulusan
                        </a>
                    </div>
                </div>
                <div class="col-lg-5 d-none d-lg-block text-center animate-fade-in" style="animation-delay: 0.2s;">
                    <div class="p-4 glass-card shadow-lg text-center" style="max-width: 400px; margin: 0 auto;">
                        <i class="bi bi-mortarboard fs-1 text-warning mb-3 d-block"></i>
                        <h4 class="fw-bold mb-2 text-white">Gelombang Aktif</h4>
                        <p class="text-white-50 mb-3">Dapatkan kesempatan kuota beasiswa prestasi dan jalur khusus ikatan persyarikatan.</p>
                        <div class="p-3 bg-white bg-opacity-10 rounded-3 border border-white border-opacity-10 text-white">
                            <span class="fw-bold fs-5">Pendaftaran Dibuka</span>
                            <hr class="my-2 border-white opacity-20">
                            <span class="small d-block text-white-50">Lakukan pendaftaran berkas sekarang!</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Info & Announcements -->
    <section id="info" class="py-5">
        <div class="container py-4">
            <div class="row g-4 align-items-center">
                <div class="col-lg-4">
                    <span class="text-primary-muh fw-bold text-uppercase d-block mb-2">Informasi Terbaru</span>
                    <h2 class="fw-bold mb-3">Pengumuman & Update SPMB</h2>
                    <p class="text-muted">Ikuti terus berita terbaru seputar penerimaan murid baru di SMP Muhammadiyah 1 Pringsewu agar tidak ketinggalan jadwal verifikasi.</p>
                </div>
                <div class="col-lg-8">
                    <div class="row g-4">
                        <?php if (mysqli_num_rows($pengumuman_query) > 0): ?>
                            <?php while ($pengumuman = mysqli_fetch_assoc($pengumuman_query)): ?>
                                <div class="col-md-6">
                                    <div class="card h-100 card-hover p-4">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="bg-primary bg-opacity-10 text-primary-muh p-3 rounded-3 me-3">
                                                <i class="bi bi-bell-fill fs-4"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block"><?php echo date('d M Y', strtotime($pengumuman['tanggal'])); ?></small>
                                                <h5 class="fw-bold mb-0 text-truncate" style="max-width: 200px;"><?php echo $pengumuman['judul']; ?></h5>
                                            </div>
                                        </div>
                                        <p class="text-muted small mb-0"><?php echo nl2br(substr($pengumuman['isi'], 0, 150)) . '...'; ?></p>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="col-12">
                                <div class="alert alert-info text-center">Belum ada pengumuman terbaru saat ini.</div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Keunggulan Sekolah -->
    <section id="keunggulan" class="py-5 bg-white">
        <div class="container py-4">
            <div class="text-center mb-5">
                <span class="text-primary-muh fw-bold text-uppercase">Kenapa Memilih Kami</span>
                <h2 class="fw-bold">Keunggulan SMP Muhammadiyah 1 Pringsewu</h2>
                <div class="mx-auto bg-primary-muh" style="height: 3px; width: 60px; margin-top: 10px;"></div>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card card-hover text-center p-4 border-0 h-100">
                        <div class="mx-auto mb-3 bg-primary-muh bg-opacity-10 text-primary-muh rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="bi bi-book-half fs-2"></i>
                        </div>
                        <h5 class="fw-bold">Kurikulum Terintegrasi</h5>
                        <p class="text-muted small">Menggabungkan kurikulum nasional (Merdeka) dengan kurikulum Ismuba (Al-Islam, Kemuhammadiyahan, dan Bahasa Arab).</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-hover text-center p-4 border-0 h-100">
                        <div class="mx-auto mb-3 bg-primary-muh bg-opacity-10 text-primary-muh rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="bi bi-trophy-fill fs-2 text-warning"></i>
                        </div>
                        <h5 class="fw-bold">Unggul Dalam Prestasi</h5>
                        <p class="text-muted small">Berbagai pencapaian kejuaraan akademik maupun non-akademik di tingkat kabupaten, provinsi hingga nasional.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-hover text-center p-4 border-0 h-100">
                        <div class="mx-auto mb-3 bg-primary-muh bg-opacity-10 text-primary-muh rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="bi bi-people-fill fs-2"></i>
                        </div>
                        <h5 class="fw-bold">Fasilitas Lengkap & Nyaman</h5>
                        <p class="text-muted small">Dilengkapi laboratorium komputer, perpustakaan digital, masjid sekolah, sarana olahraga, dan hotspot area.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Alur Pendaftaran -->
    <section id="alur" class="py-5">
        <div class="container py-4">
            <div class="text-center mb-5">
                <span class="text-primary-muh fw-bold text-uppercase">Prosedur SPMB</span>
                <h2 class="fw-bold">Alur Pendaftaran Online</h2>
                <div class="mx-auto bg-primary-muh" style="height: 3px; width: 60px; margin-top: 10px;"></div>
            </div>
            <div class="row g-4 justify-content-center">
                <div class="col-lg-3 col-md-6 text-center">
                    <div class="p-4">
                        <div class="mx-auto mb-3 bg-primary-muh text-white rounded-circle d-flex align-items-center justify-content-center fs-3 fw-bold shadow" style="width: 60px; height: 60px;">1</div>
                        <h5 class="fw-bold mt-3">Registrasi Akun</h5>
                        <p class="text-muted small">Buat akun siswa menggunakan email aktif di halaman registrasi.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 text-center">
                    <div class="p-4">
                        <div class="mx-auto mb-3 bg-primary-muh text-white rounded-circle d-flex align-items-center justify-content-center fs-3 fw-bold shadow" style="width: 60px; height: 60px;">2</div>
                        <h5 class="fw-bold mt-3">Isi Formulir</h5>
                        <p class="text-muted small">Lengkapi formulir pendaftaran data diri dan orang tua secara valid.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 text-center">
                    <div class="p-4">
                        <div class="mx-auto mb-3 bg-primary-muh text-white rounded-circle d-flex align-items-center justify-content-center fs-3 fw-bold shadow" style="width: 60px; height: 60px;">3</div>
                        <h5 class="fw-bold mt-3">Upload Dokumen</h5>
                        <p class="text-muted small">Unggah scan Kartu Keluarga, Akta Lahir, Pas Foto, dan berkas lainnya.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 text-center">
                    <div class="p-4">
                        <div class="mx-auto mb-3 bg-primary-muh text-white rounded-circle d-flex align-items-center justify-content-center fs-3 fw-bold shadow" style="width: 60px; height: 60px;">4</div>
                        <h5 class="fw-bold mt-3">Pengumuman & Cetak</h5>
                        <p class="text-muted small">Cetak kartu bukti pendaftaran dan cek pengumuman seleksi di dashboard.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Persyaratan -->
    <section id="syarat" class="py-5 bg-white">
        <div class="container py-4">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <img src="https://images.unsplash.com/photo-1427504494785-3a9ca7044f45?auto=format&fit=crop&w=800&q=80" alt="Requirements" class="img-fluid rounded-4 shadow-md">
                </div>
                <div class="col-lg-6 mt-4 mt-lg-0">
                    <span class="text-primary-muh fw-bold text-uppercase d-block mb-2">Persyaratan Dokumen</span>
                    <h2 class="fw-bold mb-3">Persyaratan Calon Peserta Didik</h2>
                    <p class="text-muted">Siapkan dokumen pendukung berikut dalam format JPG/PNG/PDF sebelum mengisi formulir online:</p>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex align-items-center border-0 px-0 bg-transparent">
                            <i class="bi bi-check-circle-fill text-primary-muh me-3 fs-5"></i> Pas Foto terbaru berlatar belakang merah/biru (Maks 2MB)
                        </li>
                        <li class="list-group-item d-flex align-items-center border-0 px-0 bg-transparent">
                            <i class="bi bi-check-circle-fill text-primary-muh me-3 fs-5"></i> Scan Kartu Keluarga asli (Maks 2MB)
                        </li>
                        <li class="list-group-item d-flex align-items-center border-0 px-0 bg-transparent">
                            <i class="bi bi-check-circle-fill text-primary-muh me-3 fs-5"></i> Scan Akta Kelahiran asli (Maks 2MB)
                        </li>
                        <li class="list-group-item d-flex align-items-center border-0 px-0 bg-transparent">
                            <i class="bi bi-check-circle-fill text-primary-muh me-3 fs-5"></i> Scan Rapor kelas V & VI (Maks 2MB)
                        </li>
                        <li class="list-group-item d-flex align-items-center border-0 px-0 bg-transparent">
                            <i class="bi bi-check-circle-fill text-primary-muh me-3 fs-5"></i> Scan Ijazah asli / Surat Keterangan Lulus (Maks 2MB)
                        </li>
                        <li class="list-group-item d-flex align-items-center border-0 px-0 bg-transparent">
                            <i class="bi bi-check-circle-fill text-primary-muh me-3 fs-5"></i> Scan Kartu KIP (Opsional, jika memiliki)
                        </li>
                        <li class="list-group-item d-flex align-items-center border-0 px-0 bg-transparent">
                            <i class="bi bi-check-circle-fill text-primary-muh me-3 fs-5"></i> Scan Piagam Prestasi minimal tingkat Kabupaten (Opsional, jika memiliki)
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Jadwal & Stats -->
    <section id="jadwal" class="py-5">
        <div class="container py-4">
            <div class="row g-5">
                <div class="col-lg-8">
                    <span class="text-primary-muh fw-bold text-uppercase d-block mb-2">Agenda SPMB</span>
                    <h2 class="fw-bold mb-4">Jadwal Gelombang Pendaftaran</h2>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Gelombang</th>
                                    <th>Mulai</th>
                                    <th>Selesai</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($jadwal_query) > 0): ?>
                                    <?php while ($jadwal = mysqli_fetch_assoc($jadwal_query)): ?>
                                        <tr>
                                            <td class="fw-bold"><?php echo htmlspecialchars($jadwal['gelombang']); ?></td>
                                            <td><?php echo date('d M Y', strtotime($jadwal['mulai'])); ?></td>
                                            <td><?php echo date('d M Y', strtotime($jadwal['selesai'])); ?></td>
                                            <td class="text-muted small"><?php echo htmlspecialchars($jadwal['keterangan']); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">Jadwal belum dikonfigurasi oleh admin.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card bg-primary-muh border-0 p-4 text-white h-100 d-flex flex-column justify-content-between">
                        <div>
                            <i class="bi bi-people fs-1 text-warning mb-3 d-block"></i>
                            <h4 class="fw-bold">Statistik Pendaftar</h4>
                            <p class="text-white-50 small">Pendaftaran online terus berjalan. Pantau jumlah pendaftar saat ini secara realtime.</p>
                        </div>
                        <div class="mt-4">
                            <span class="display-3 fw-bold d-block text-warning"><?php echo number_format($total_pendaftar); ?></span>
                            <span class="text-uppercase small tracking-wide">Calon Siswa Terdaftar</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ -->
    <section id="faq" class="py-5 bg-white">
        <div class="container py-4">
            <div class="text-center mb-5">
                <span class="text-primary-muh fw-bold text-uppercase">Tanya Jawab</span>
                <h2 class="fw-bold">Pertanyaan yang Sering Diajukan (FAQ)</h2>
                <div class="mx-auto bg-primary-muh" style="height: 3px; width: 60px; margin-top: 10px;"></div>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingOne">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                    Apakah pendaftaran online di SMP Muhammadiyah 1 Pringsewu dikenakan biaya?
                                </button>
                            </h2>
                            <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted">
                                    Registrasi akun online sepenuhnya gratis. Untuk biaya pendaftaran administratif/formulir, silakan cek instruksi selanjutnya di dashboard siswa atau hubungi panitia.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingTwo">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                    Bagaimana jika berkas yang diupload statusnya "Kurang Lengkap"?
                                </button>
                            </h2>
                            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted">
                                    Anda dapat melihat catatan dari admin di halaman dashboard siswa. Silakan hapus file lama yang ditolak dan upload ulang file baru sesuai instruksi.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingThree">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                    Kapan pengumuman hasil seleksi dirilis?
                                </button>
                            </h2>
                            <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted">
                                    Hasil seleksi akan dirilis setelah proses verifikasi dokumen dan ujian selesai dilakukan. Pengumuman dapat dilihat melalui menu "Cek Status Seleksi" di halaman depan atau dashboard masing-masing.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Kontak -->
    <section id="kontak" class="py-5">
        <div class="container py-4">
            <div class="row g-4">
                <div class="col-lg-5">
                    <span class="text-primary-muh fw-bold text-uppercase d-block mb-2">Hubungi Kami</span>
                    <h2 class="fw-bold mb-3">Ada Pertanyaan?</h2>
                    <p class="text-muted mb-4">Tim panitia SPMB kami siap melayani Anda melalui saluran telepon, email, atau datang langsung ke sekretariat pendaftaran sekolah.</p>
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary-muh bg-opacity-10 text-primary-muh p-3 rounded-3 me-3">
                            <i class="bi bi-geo-alt-fill fs-4"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block">Alamat Sekolah</small>
                            <span class="fw-bold">Jl. KH. Ahmad Dahlan No. 1, Pringsewu, Lampung</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary-muh bg-opacity-10 text-primary-muh p-3 rounded-3 me-3">
                            <i class="bi bi-telephone-fill fs-4"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block">WhatsApp / Telp</small>
                            <span class="fw-bold">+62 812-3456-7890</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="bg-primary-muh bg-opacity-10 text-primary-muh p-3 rounded-3 me-3">
                            <i class="bi bi-envelope-fill fs-4"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block">Email</small>
                            <span class="fw-bold">smpmuh1pringsewu@gmail.com</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="card p-4 border-0 shadow-sm">
                        <h4 class="fw-bold mb-3">Kirim Pesan</h4>
                        <form class="needs-validation" novalidate>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Nama Lengkap</label>
                                    <input type="text" class="form-control" placeholder="Nama Anda" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Email / No. HP</label>
                                    <input type="text" class="form-control" placeholder="Kontak Anda" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold">Pesan Anda</label>
                                    <textarea class="form-control" rows="4" placeholder="Tuliskan pertanyaan Anda..." required></textarea>
                                </div>
                                <div class="col-12 text-end">
                                    <button type="submit" class="btn btn-primary-muh">Kirim Pesan <i class="bi bi-send ms-1"></i></button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 border-top border-secondary">
        <div class="container text-center">
            <div class="d-flex justify-content-center mb-3">
                <i class="bi bi-facebook mx-2 fs-4"></i>
                <i class="bi bi-instagram mx-2 fs-4"></i>
                <i class="bi bi-youtube mx-2 fs-4"></i>
                <i class="bi bi-twitter mx-2 fs-4"></i>
            </div>
            <p class="mb-0 small text-white-50">&copy; <?php echo date('Y'); ?> SMP Muhammadiyah 1 Pringsewu. All rights reserved.</p>
        </div>
    </footer>

    <!-- Bootstrap 5 Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Custom Main JS -->
    <script src="assets/js/main.js"></script>
</body>
</html>
