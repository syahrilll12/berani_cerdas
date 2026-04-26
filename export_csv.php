<?php
require_once __DIR__ . '/koneksi.php';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=laporan_beasiswa_berani_cerdas.csv');

$output = fopen('php://output', 'w');

fputcsv($output, ['NIM', 'Nama Lengkap', 'Semester', 'IPK', 'Status SPP', 'Status Mahasiswa', 'Status Verifikasi', 'Jenis Mahasiswa', 'Status Beasiswa (SIGA)', 'Catatan Admin', 'Tanggal']);

$res = $conn->query("SELECT * FROM tb_pengajuan ORDER BY created_at DESC");

while ($row = $res->fetch_assoc()) {
    fputcsv($output, [
        $row['nim'],
        $row['nama_lengkap'],
        $row['semester'],
        $row['ipk'],
        $row['status_spp'],
        $row['status_mahasiswa'],
        $row['status_verifikasi'],
        $row['jenis_mahasiswa'],
        $row['status_beasiswa_siga'],
        $row['catatan_admin'],
        $row['created_at']
    ]);
}

fclose($output);
exit;
