function unwrapPengajuan(row) {
    if (Array.isArray(row) && row.length > 0 && typeof row[0] === 'object') {
        row = row[0];
    }
    if (row && row.data_received && Array.isArray(row.data_received)) {
        row = row.data_received;
    }
    return row;
}

function normalizePengajuan(inputRaw) {
    const input = unwrapPengajuan(inputRaw);

    const now = new Date().toISOString().slice(0, 19).replace('T', ' ');

    return {
        nim: String(input.nim || '').trim(),
        nama_lengkap: String(input.nama_lengkap || input.nama || '').trim(),
        semester: String(input.semester || '-').trim(),
        ipk: String(input.ipk || '').trim(),
        status_spp: String(input.status_spp || input.status_pembayaran_siga || 'Belum Lunas').trim(),
        status_mahasiswa: String(input.status_mahasiswa || 'Unknown').trim(),
        status_verifikasi: String(input.status_verifikasi || 'Belum Diverifikasi').trim(),
        jenis_mahasiswa: String(input.jenis_mahasiswa || 'Belum menerima').trim(),
        status_beasiswa_siga: String(input.status_beasiswa_siga || 'Belum Aktif').trim(),
        catatan_admin: String(input.catatan_admin || '').trim(),
        created_at: input.created_at || now,
        updated_at: input.updated_at || now,
    };
}

function kelayakan(mhs) {
    const ipk = !isNaN(parseFloat(mhs.ipk)) ? parseFloat(mhs.ipk) : 0;
    const spp = String(mhs.status_spp || '').trim().toLowerCase();
    const statusMahasiswa = String(mhs.status_mahasiswa || '').trim().toLowerCase();

    if (ipk < 3.00) {
        return ['Ditolak', 'Tidak dapat diterima karena IPK anda rendah.', 'bad'];
    }

    if (spp !== 'lunas') {
        return ['Ditunda', 'Status pembayaran SIGA belum lunas.', 'wait'];
    }

    if (!['active', 'aktif'].includes(statusMahasiswa)) {
        return ['Ditunda', 'Status mahasiswa belum aktif.', 'wait'];
    }

    return ['Layak', 'Layak menerima beasiswa.', 'ok'];
}

module.exports = {
    unwrapPengajuan,
    normalizePengajuan,
    kelayakan
};
