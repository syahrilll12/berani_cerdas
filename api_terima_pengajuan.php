<?php
require_once __DIR__ . '/functions.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode([
        'status' => 'ready',
        'message' => 'API Berani Cerdas siap menerima pengajuan beasiswa dari SIGA.',
        'endpoint' => 'POST http://localhost/berani-cerdas/api_terima_pengajuan.php',
        'format_didukung' => [
            'object langsung',
            'object dengan data_received',
            'array berisi object dengan data_received'
        ]
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

$raw = file_get_contents('php://input');
$input = json_decode($raw, true);

if (!is_array($input)) {
    $input = $_POST;
}

// Jika menerima array berisi beberapa pengajuan, simpan semuanya.
$rows = [];
if (isset($input[0]) && is_array($input[0])) {
    $rows = $input;
} else {
    $rows = [$input];
}

require_once __DIR__ . '/koneksi.php';

$saved = [];
$total_data = 0;

$stmt = $conn->prepare("INSERT INTO tb_pengajuan (
    nim, nama_lengkap, semester, ipk, status_spp, status_mahasiswa, 
    status_verifikasi, jenis_mahasiswa, status_beasiswa_siga, catatan_admin,
    hasil_kelayakan, catatan, badge_class, created_at, updated_at
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
ON DUPLICATE KEY UPDATE 
    nama_lengkap=VALUES(nama_lengkap), semester=VALUES(semester), 
    ipk=VALUES(ipk), status_spp=VALUES(status_spp), status_mahasiswa=VALUES(status_mahasiswa),
    status_verifikasi=VALUES(status_verifikasi), jenis_mahasiswa=VALUES(jenis_mahasiswa),
    status_beasiswa_siga=VALUES(status_beasiswa_siga), catatan_admin=VALUES(catatan_admin),
    hasil_kelayakan=VALUES(hasil_kelayakan), catatan=VALUES(catatan), badge_class=VALUES(badge_class),
    updated_at=VALUES(updated_at)");

foreach ($rows as $row) {
    $mhs = normalize_pengajuan($row);

    if ($mhs['nim'] === '' || $mhs['nama_lengkap'] === '') {
        continue;
    }

    [$status, $catatan, $class] = kelayakan($mhs);
    $mhs['hasil_kelayakan'] = $status;
    $mhs['catatan'] = $catatan;
    $mhs['badge_class'] = $class;

    $nim = $mhs['nim'];
    $nama = $mhs['nama_lengkap'];
    $semester = $mhs['semester'];
    $ipk = is_numeric($mhs['ipk']) ? $mhs['ipk'] : 0.00;
    $status_spp = $mhs['status_spp'];
    $status_mhs = $mhs['status_mahasiswa'];
    $status_ver = $mhs['status_verifikasi'];
    $jenis_mhs = $mhs['jenis_mahasiswa'];
    $siga_stat = $mhs['status_beasiswa_siga'];
    $catatan_adm = $mhs['catatan_admin'];
    $created_at = $mhs['created_at'];
    $updated_at = $mhs['updated_at'];
    
    $stmt->bind_param("sssssssssssssss", 
        $nim, $nama, $semester, $ipk, $status_spp, $status_mhs, 
        $status_ver, $jenis_mhs, $siga_stat, $catatan_adm,
        $status, $catatan, $class, $created_at, $updated_at
    );
    
    if($stmt->execute()){
        $saved[] = $mhs;
    }
}

$res = $conn->query("SELECT COUNT(*) as total FROM tb_pengajuan");
if ($res) {
    $row = $res->fetch_assoc();
    $total_data = $row['total'];
}

echo json_encode([
    'status' => count($saved) ? 'success' : 'error',
    'message' => count($saved) ? 'Pengajuan beasiswa berhasil diterima Berani Cerdas.' : 'Tidak ada data valid yang diterima.',
    'data_received' => $saved,
    'total_data' => (int)$total_data
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
