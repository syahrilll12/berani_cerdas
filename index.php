<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/koneksi.php';

$data = [];
$res = $conn->query("SELECT * FROM tb_pengajuan ORDER BY created_at DESC");
while($row = $res->fetch_assoc()){
    $data[] = $row;
}

$penerima = [];
$pengajuan = [];
$exception = [];

foreach ($data as $m) {
    if ($m['status_verifikasi'] === 'Terverifikasi') {
        $penerima[] = $m;
    } elseif ($m['badge_class'] === 'bad' || $m['status_verifikasi'] === 'Ditolak') {
        $exception[] = $m;
    } else {
        $pengajuan[] = $m;
    }
}

$lastSync = count($data) ? max(array_column($data, 'updated_at')) : '-';
$message = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berani Cerdas - Admin</title>
    <link rel="stylesheet" href="assets/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<header class="top-header">
    <div class="header-left">
        <div class="logo-area">
            <div class="logo-circle">✤</div>
            <span><b>BERANI</b> CERDAS</span>
        </div>
        <div class="dashboard-title" style="margin-left: 20px;">
            <h1>Dashboard</h1>
        </div>
    </div>
    <div class="header-right">
        <button class="moon-btn" type="button" onclick="document.body.classList.toggle('dark')">☾</button>
        <div class="profile">
            <div class="avatar">AD</div>
            <div class="profile-text"><strong>ADMIN BERANI CERDAS</strong><small>user</small></div>
        </div>
    </div>
</header>

<main class="page">
    <?php if ($message): ?>
        <div class="alert success-alert"><?= e($message) ?></div>
    <?php endif; ?>

    <section class="hero-card">
        <div>
            <p class="eyebrow">Admin Berani Cerdas</p>
            <h2>Kelola verifikasi beasiswa dari data SIGA.</h2>
            <p>Data pengajuan diterima melalui API Berani Cerdas, lalu admin dapat memverifikasi mahasiswa satu per satu agar status beasiswa menjadi aktif.</p>
            <div class="actions">
                <a class="btn primary" href="export_csv.php" target="_blank">Export ke CSV</a>
                <button class="btn print" onclick="window.print()">Cetak Laporan</button>
                <a class="btn ghost danger-action" href="reset_verifikasi.php" style="color: red; border-color: red;" onclick="return confirm('Anda yakin ingin mereset semua status mahasiswa kembali ke Menunggu Verifikasi?')">Reset Uji Coba</a>
            </div>
        </div>
        <div class="stats">
            <div><b><?= count($data) ?></b><span>Total Data</span></div>
            <div><b><?= count($penerima) ?></b><span>Penerima Aktif</span></div>
            <div><b><?= count($pengajuan) ?></b><span>Menunggu Verifikasi</span></div>
            <div><b><?= count($exception) ?></b><span>Exception</span></div>
        </div>
    </section>

    <section class="panel" style="max-width: 600px; margin: 0 auto;">
        <div class="panel-head">
            <div>
                <h3>Sebaran Status Pengajuan</h3>
            </div>
        </div>
        <div style="height: 250px; display: flex; justify-content: center;">
            <canvas id="statusChart"></canvas>
        </div>
    </section>

    <section class="panel">
        <div class="panel-head">
            <div>
                <h3>Mahasiswa Aktif Menerima Beasiswa</h3>
            </div>
            <span class="chip success">Sudah diverifikasi admin</span>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>NIM</th>
                        <th>Nama</th>
                        <th>Semester</th>
                        <th>IPK</th>
                        <th>SPP</th>
                        <th>Status Mahasiswa</th>
                        <th>Jenis Mahasiswa</th>
                        <th>Status Beasiswa</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($penerima as $m): ?>
                    <tr>
                        <td><?= e($m['nim']) ?></td>
                        <td><?= e($m['nama_lengkap']) ?></td>
                        <td><?= e($m['semester']) ?></td>
                        <td><span class="badge info"><?= e($m['ipk']) ?></span></td>
                        <td><span class="badge ok"><?= e($m['status_spp']) ?></span></td>
                        <td><?= e($m['status_mahasiswa']) ?></td>
                        <td><?= e($m['jenis_mahasiswa']) ?></td>
                        <td><span class="badge ok"><?= e($m['status_beasiswa_siga']) ?></span></td>
                        <td><?= e($m['catatan_admin'] ?: 'Mahasiswa aktif menerima beasiswa.') ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$penerima): ?>
                    <tr><td colspan="9" class="empty">Belum ada mahasiswa yang diverifikasi sebagai penerima aktif.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="panel">
        <div class="panel-head">
            <div>
                <h3>Pengajuan Beasiswa / Belum Diverifikasi</h3>
            </div>
            <span class="chip warn">Verifikasi satu per satu</span>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>NIM</th>
                        <th>Nama</th>
                        <th>Semester</th>
                        <th>IPK</th>
                        <th>SPP</th>
                        <th>Status Mahasiswa</th>
                        <th>Jenis Mahasiswa</th>
                        <th>Catatan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($pengajuan as $m): ?>
                    <tr>
                        <td><?= e($m['nim']) ?></td>
                        <td><?= e($m['nama_lengkap']) ?></td>
                        <td><?= e($m['semester']) ?></td>
                        <td><span class="badge info"><?= e($m['ipk']) ?></span></td>
                        <td><span class="badge <?= strtolower($m['status_spp']) === 'lunas' ? 'ok' : 'wait' ?>"><?= e($m['status_spp']) ?></span></td>
                        <td><?= e($m['status_mahasiswa']) ?></td>
                        <td>Belum menerima</td>
                        <td><?= e($m['catatan']) ?></td>
                        <td>
                            <a class="small-btn" href="api_verifikasi_status_beasiswa.php?nim=<?= urlencode($m['nim']) ?>">Verifikasi</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$pengajuan): ?>
                    <tr><td colspan="9" class="empty">Tidak ada pengajuan menunggu verifikasi.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="panel">
        <div class="panel-head">
            <div>
                <h3>Ditolak Karena IPK Rendah</h3>
            </div>
            <span class="chip danger">IPK &lt; 3.00</span>
        </div>
        <div class="cards-grid">
        <?php foreach ($exception as $m): ?>
            <div class="mini-card danger-card">
                <h3><?= e($m['nama_lengkap']) ?></h3>
                <p>NIM <?= e($m['nim']) ?> · IPK <?= e($m['ipk']) ?> · <?= e($m['status_spp']) ?></p>
                <b>Exception:</b> tidak dapat diterima karena IPK anda rendah.
                <?php if (($m['status_verifikasi'] ?? '') !== 'Ditolak'): ?>
                    <div class="card-action"><a class="small-btn danger-action" href="api_verifikasi_status_beasiswa.php?nim=<?= urlencode($m['nim']) ?>">Proses Penolakan</a></div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        <?php if (!$exception): ?>
            <div class="mini-card">Belum ada exception IPK.</div>
        <?php endif; ?>
        </div>
    </section>
</main>

<script>
    const chartData = {
        aktif: <?= count($penerima) ?>,
        menunggu: <?= count($pengajuan) ?>,
        ditolak: <?= count($exception) ?>
    };
</script>
<script src="assets/script.js"></script>
</body>
</html>
