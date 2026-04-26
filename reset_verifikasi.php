<?php
require_once __DIR__ . '/koneksi.php';

// Reset semua mahasiswa kembali ke status 'Belum Diverifikasi'
$query = "UPDATE tb_pengajuan SET 
    status_verifikasi = 'Belum Diverifikasi', 
    jenis_mahasiswa = 'Belum menerima', 
    status_beasiswa_siga = 'Belum Aktif', 
    catatan_admin = '',
    updated_at = NOW()
    WHERE status_verifikasi IN ('Terverifikasi', 'Ditolak', 'Ditunda')";

if ($conn->query($query)) {
    $message = 'Semua status mahasiswa berhasil di-reset kembali ke antrean uji coba.';
} else {
    $message = 'Gagal melakukan reset: ' . $conn->error;
}

header('Location: index.php?msg=' . urlencode($message));
exit;
