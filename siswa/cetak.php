<?php
require_once __DIR__ . '/../config/db.php';
check_login('siswa');

$user_id = $_SESSION['user_id'];

// Query complete registration data along with active school year
$query = "SELECT p.*, t.tahun as tahun_ajaran 
          FROM pendaftaran p 
          LEFT JOIN tahun_ajaran t ON p.tahun_ajaran_id = t.id 
          WHERE p.user_id = $user_id LIMIT 1";
          
$result = mysqli_query($conn, $query);
$siswa = mysqli_fetch_assoc($result);

if (!$siswa) {
    die("Data pendaftaran tidak ditemukan!");
}

$no_pendaftaran = $siswa['no_pendaftaran'];
// Generate dynamic QR Code URL encoding registration number
$qr_code_url = "https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=" . urlencode($no_pendaftaran);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Bukti Pendaftaran - <?php echo $no_pendaftaran; ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }
        .print-card {
            background-color: #fff;
            border: 2px solid #0F7B3F;
            border-radius: 12px;
            max-width: 800px;
            margin: 30px auto;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            position: relative;
            overflow: hidden;
        }
        .print-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 8px;
            background-color: #0F7B3F;
        }
        .header-logo {
            font-size: 2.5rem;
            color: #0F7B3F;
        }
        .info-table th {
            width: 30%;
            font-weight: 600;
            color: #555;
            background-color: #f8f9fa;
        }
        .info-table td {
            color: #111;
        }
        /* Printable Styles override */
        @media print {
            body {
                background-color: #fff !important;
            }
            .no-print {
                display: none !important;
            }
            .print-card {
                border: none !important;
                box-shadow: none !important;
                margin: 0 !important;
                max-width: 100% !important;
            }
            .info-table th {
                background-color: #f8f9fa !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>

    <!-- Print Action Header (Floating/Sticky at top, hidden on print) -->
    <div class="container-fluid bg-dark py-3 no-print">
        <div class="max-width: 800px; margin: 0 auto; display: flex; justify-content-between align-items-center">
            <a href="dashboard.php" class="btn btn-outline-light"><i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard</a>
            <button onclick="window.print()" class="btn btn-success fw-bold px-4"><i class="bi bi-printer-fill me-1"></i> Cetak / Download PDF</button>
        </div>
    </div>

    <!-- Printable Card Container -->
    <div class="print-card p-5">
        <!-- Logo & Header -->
        <div class="row align-items-center mb-4 border-bottom pb-4">
            <div class="col-2 text-center">
                <i class="bi bi-mortarboard-fill header-logo"></i>
            </div>
            <div class="col-8">
                <h4 class="fw-bold mb-1 text-uppercase text-primary-muh">SMP Muhammadiyah 1 Pringsewu</h4>
                <p class="mb-0 text-muted small">Alamat: Jl. KH. Ahmad Dahlan No. 1, Pringsewu, Lampung | Telp: +62 812-3456-7890</p>
                <p class="mb-0 text-muted small">Website: www.smpmuh1pringsewu.sch.id | Email: smpmuh1pringsewu@gmail.com</p>
            </div>
            <div class="col-2 text-end">
                <span class="badge bg-success py-2 px-3">BUKTI SPMB</span>
            </div>
        </div>

        <div class="text-center my-4">
            <h5 class="fw-bold text-uppercase mb-1">KARTU BUKTI PENDAFTARAN</h5>
            <span class="text-muted">Tahun Pelajaran <?php echo htmlspecialchars($siswa['tahun_ajaran']); ?></span>
        </div>

        <!-- Student credentials table -->
        <div class="row g-4 mb-4">
            <div class="col-md-9">
                <table class="table table-bordered align-middle info-table">
                    <tr>
                        <th>No. Pendaftaran</th>
                        <td class="fw-bold text-primary-muh fs-5"><?php echo htmlspecialchars($siswa['no_pendaftaran']); ?></td>
                    </tr>
                    <tr>
                        <th>NISN</th>
                        <td><?php echo htmlspecialchars($siswa['nisn']); ?></td>
                    </tr>
                    <tr>
                        <th>NIK</th>
                        <td><?php echo htmlspecialchars($siswa['nik']); ?></td>
                    </tr>
                    <tr>
                        <th>Nama Lengkap</th>
                        <td class="fw-bold"><?php echo htmlspecialchars($siswa['nama_lengkap']); ?></td>
                    </tr>
                    <tr>
                        <th>Tempat, Tgl Lahir</th>
                        <td><?php echo htmlspecialchars($siswa['tempat_lahir']) . ', ' . date('d F Y', strtotime($siswa['tanggal_lahir'])); ?></td>
                    </tr>
                    <tr>
                        <th>Jenis Kelamin</th>
                        <td><?php echo ($siswa['jenis_kelamin'] === 'L') ? 'Laki-laki (L)' : 'Perempuan (P)'; ?></td>
                    </tr>
                    <tr>
                        <th>Agama</th>
                        <td><?php echo htmlspecialchars($siswa['agama']); ?></td>
                    </tr>
                    <tr>
                        <th>Asal Sekolah</th>
                        <td><?php echo htmlspecialchars($siswa['asal_sekolah']); ?></td>
                    </tr>
                    <tr>
                        <th>No. HP / WA</th>
                        <td><?php echo htmlspecialchars($siswa['no_hp']); ?></td>
                    </tr>
                    <tr>
                        <th>Orang Tua (Ayah / Ibu)</th>
                        <td><?php echo htmlspecialchars($siswa['nama_ayah']) . ' / ' . htmlspecialchars($siswa['nama_ibu']); ?></td>
                    </tr>
                    <tr>
                        <th>Pekerjaan Ortu</th>
                        <td><?php echo htmlspecialchars($siswa['pekerjaan_ortu']); ?></td>
                    </tr>
                    <tr>
                        <th>Alamat Lengkap</th>
                        <td><?php echo nl2br(htmlspecialchars($siswa['alamat'])); ?></td>
                    </tr>
                </table>
            </div>
            
            <!-- QR Code Section & Photo placeholder -->
            <div class="col-md-3 text-center">
                <div class="border p-3 rounded bg-light mb-4">
                    <span class="small text-muted d-block mb-2">SCAN QR CODE</span>
                    <img src="<?php echo $qr_code_url; ?>" alt="QR Code" class="img-fluid border rounded shadow-sm">
                </div>
                <div class="border p-2 rounded bg-light d-flex flex-column align-items-center justify-content-center" style="height: 180px;">
                    <span class="text-muted small">PAS FOTO<br>3 x 4</span>
                </div>
            </div>
        </div>

        <!-- Notes and Signatures -->
        <div class="row mt-5">
            <div class="col-7">
                <div class="alert alert-secondary py-2 px-3 small">
                    <strong class="d-block mb-1">Catatan Calon Siswa:</strong>
                    <ol class="mb-0 ps-3">
                        <li>Kartu bukti ini wajib dibawa saat penyerahan berkas fisik dan ujian seleksi.</li>
                        <li>Verifikasi berkas fisik dilayani di ruang sekretariat panitia SPMB.</li>
                        <li>Keputusan panitia bersifat mutlak dan tidak dapat diganggu gugat.</li>
                    </ol>
                </div>
            </div>
            <div class="col-5 text-center">
                <p class="mb-5 small">Pringsewu, <?php echo date('d F Y', strtotime($siswa['tanggal_daftar'])); ?></p>
                <div class="mx-auto border-bottom border-dark" style="width: 200px; height: 40px;"></div>
                <p class="mt-2 small mb-0"><strong>Panitia SPMB Online</strong></p>
                <p class="text-muted" style="font-size: 0.75rem;">SMP Muhammadiyah 1 Pringsewu</p>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
