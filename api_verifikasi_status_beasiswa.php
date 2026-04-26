<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/koneksi.php';

$nim = $_GET['nim'] ?? '';
$message = 'Data mahasiswa tidak ditemukan.';

if ($nim !== '') {
    $stmt = $conn->prepare("SELECT * FROM tb_pengajuan WHERE nim = ?");
    $stmt->bind_param("s", $nim);
    $stmt->execute();
    $res = $stmt->get_result();
    $m = $res->fetch_assoc();
    $stmt->close();

    if ($m) {
        $class = $m['badge_class'];
        $catatan = $m['catatan'];

        if ($class === 'bad') {
            $m['status_verifikasi'] = 'Ditolak';
            $m['jenis_mahasiswa'] = 'Belum menerima';
            $m['status_beasiswa_siga'] = 'Tidak Aktif';
            $m['catatan_admin'] = 'Tidak dapat diterima karena IPK rendah.';
            $message = 'Mahasiswa ditolak karena IPK rendah.';
        } elseif ($class === 'wait') {
            $m['status_verifikasi'] = 'Ditunda';
            $m['jenis_mahasiswa'] = 'Belum menerima';
            $m['status_beasiswa_siga'] = 'Belum Aktif';
            $m['catatan_admin'] = $catatan;
            $message = 'Mahasiswa belum bisa diaktifkan: ' . $catatan;
        } else {
            $m['status_verifikasi'] = 'Terverifikasi';
            $m['jenis_mahasiswa'] = 'Penerima Beasiswa';
            $m['status_beasiswa_siga'] = 'Aktif';
            $m['catatan_admin'] = 'Mahasiswa berhasil diverifikasi dan aktif sebagai penerima beasiswa.';
            $message = 'Mahasiswa berhasil diverifikasi sebagai penerima beasiswa aktif.';
        }

        $m['updated_at'] = date('Y-m-d H:i:s');

        $update = $conn->prepare("UPDATE tb_pengajuan SET 
            status_verifikasi=?, jenis_mahasiswa=?, status_beasiswa_siga=?, 
            catatan_admin=?, updated_at=? WHERE nim=?");
        $update->bind_param("ssssss", 
            $m['status_verifikasi'], $m['jenis_mahasiswa'], $m['status_beasiswa_siga'], 
            $m['catatan_admin'], $m['updated_at'], $nim
        );
        $update->execute();
        $update->close();
    }
}

header('Location: index.php?msg=' . urlencode($message));
exit;
