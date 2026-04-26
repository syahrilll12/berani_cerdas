<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once __DIR__ . '/koneksi.php';

$nim = $_GET['nim'] ?? '';

if (!$nim) {
    echo json_encode(['status' => 'error', 'message' => 'NIM tidak diberikan']);
    exit;
}

$stmt = $conn->prepare("SELECT status_verifikasi FROM tb_pengajuan WHERE nim = ?");
$stmt->bind_param("s", $nim);
$stmt->execute();
$res = $stmt->get_result();

if ($row = $res->fetch_assoc()) {
    $status_beasiswa = 'Menunggu Verifikasi';
    if ($row['status_verifikasi'] === 'Terverifikasi') {
        $status_beasiswa = 'Beasiswa Prestasi';
    } elseif ($row['status_verifikasi'] === 'Ditolak') {
        $status_beasiswa = 'Ditolak';
    }
    
    echo json_encode([
        'status' => 'success',
        'nim' => $nim,
        'status_beasiswa' => $status_beasiswa
    ]);
} else {
    echo json_encode([
        'status' => 'success',
        'nim' => $nim,
        'status_beasiswa' => 'Belum Mengajukan'
    ]);
}

$stmt->close();
$conn->close();
