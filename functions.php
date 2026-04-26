<?php


function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}



function unwrap_pengajuan($row) {
    if (isset($row[0]) && is_array($row[0])) {
        $row = $row[0];
    }

    if (isset($row['data_received']) && is_array($row['data_received'])) {
        $row = $row['data_received'];
    }

    return $row;
}

function normalize_pengajuan($input) {
    $input = unwrap_pengajuan($input);

    return [
        'nim' => trim((string)($input['nim'] ?? '')),
        'nama_lengkap' => trim((string)($input['nama_lengkap'] ?? $input['nama'] ?? '')),
        'semester' => trim((string)($input['semester'] ?? '-')),
        'ipk' => trim((string)($input['ipk'] ?? '')),
        'status_spp' => trim((string)($input['status_spp'] ?? $input['status_pembayaran_siga'] ?? 'Belum Lunas')),
        'status_mahasiswa' => trim((string)($input['status_mahasiswa'] ?? 'Unknown')),
        'status_verifikasi' => trim((string)($input['status_verifikasi'] ?? 'Belum Diverifikasi')),
        'jenis_mahasiswa' => trim((string)($input['jenis_mahasiswa'] ?? 'Belum menerima')),
        'status_beasiswa_siga' => trim((string)($input['status_beasiswa_siga'] ?? 'Belum Aktif')),
        'catatan_admin' => trim((string)($input['catatan_admin'] ?? '')),
        'created_at' => $input['created_at'] ?? date('Y-m-d H:i:s'),
        'updated_at' => $input['updated_at'] ?? date('Y-m-d H:i:s'),
    ];
}

function normalize_all_pengajuan($rows) {
    $normalized = [];
    foreach ($rows as $row) {
        $mhs = normalize_pengajuan($row);
        if ($mhs['nim'] !== '' && $mhs['nama_lengkap'] !== '') {
            $normalized[] = $mhs;
        }
    }
    return $normalized;
}

function kelayakan($mhs) {
    $ipk = is_numeric($mhs['ipk'] ?? null) ? (float)$mhs['ipk'] : 0;
    $spp = strtolower(trim((string)($mhs['status_spp'] ?? '')));
    $statusMahasiswa = strtolower(trim((string)($mhs['status_mahasiswa'] ?? '')));

    if ($ipk < 3.00) {
        return ['Ditolak', 'Tidak dapat diterima karena IPK anda rendah.', 'bad'];
    }

    if ($spp !== 'lunas') {
        return ['Ditunda', 'Status pembayaran SIGA belum lunas.', 'wait'];
    }

    if (!in_array($statusMahasiswa, ['active', 'aktif'], true)) {
        return ['Ditunda', 'Status mahasiswa belum aktif.', 'wait'];
    }

    return ['Layak', 'Layak menerima beasiswa.', 'ok'];
}


