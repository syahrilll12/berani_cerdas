const pool = require('../config/database');
const { normalizePengajuan, kelayakan } = require('../utils/helpers');

const terimaPengajuan = async (req, res) => {
    try {
        let input = req.body;
        
        let rows = [];
        if (Array.isArray(input)) {
            rows = input;
        } else if (input.data_received && Array.isArray(input.data_received)) {
            rows = input.data_received;
        } else {
            rows = [input];
        }

        const saved = [];
        const connection = await pool.getConnection();

        try {
            await connection.beginTransaction();

            const query = `
                INSERT INTO tb_pengajuan (
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
                    updated_at=VALUES(updated_at)
            `;

            for (const row of rows) {
                const mhs = normalizePengajuan(row);

                if (mhs.nim === '' || mhs.nama_lengkap === '') {
                    continue;
                }

                const [status, catatan, badgeClass] = kelayakan(mhs);
                mhs.hasil_kelayakan = status;
                mhs.catatan = catatan;
                mhs.badge_class = badgeClass;

                const ipkValue = !isNaN(parseFloat(mhs.ipk)) ? parseFloat(mhs.ipk) : 0.00;

                await connection.execute(query, [
                    mhs.nim, mhs.nama_lengkap, mhs.semester, ipkValue, mhs.status_spp, mhs.status_mahasiswa,
                    mhs.status_verifikasi, mhs.jenis_mahasiswa, mhs.status_beasiswa_siga, mhs.catatan_admin,
                    status, catatan, badgeClass, mhs.created_at, mhs.updated_at
                ]);

                saved.push(mhs);
            }

            await connection.commit();

            const [totalRes] = await pool.query("SELECT COUNT(*) as total FROM tb_pengajuan");
            const totalData = totalRes[0].total;

            res.json({
                status: saved.length ? 'success' : 'error',
                message: saved.length ? 'Pengajuan beasiswa berhasil diterima Berani Cerdas.' : 'Tidak ada data valid yang diterima.',
                data_received: saved,
                total_data: totalData
            });

        } catch (error) {
            await connection.rollback();
            throw error;
        } finally {
            connection.release();
        }

    } catch (error) {
        console.error('Error in terimaPengajuan:', error);
        res.status(500).json({ status: 'error', message: 'Internal Server Error' });
    }
};

const getStatusBeasiswa = async (req, res) => {
    try {
        const nim = req.params.nim || req.query.nim;

        if (!nim) {
            return res.status(400).json({ status: 'error', message: 'NIM tidak diberikan' });
        }

        const [rows] = await pool.query("SELECT status_verifikasi FROM tb_pengajuan WHERE nim = ?", [nim]);

        if (rows.length > 0) {
            let status_beasiswa = 'Menunggu Verifikasi';
            if (rows[0].status_verifikasi === 'Terverifikasi') {
                status_beasiswa = 'Beasiswa Prestasi';
            } else if (rows[0].status_verifikasi === 'Ditolak') {
                status_beasiswa = 'Ditolak';
            }

            res.json({
                status: 'success',
                nim: nim,
                status_beasiswa: status_beasiswa
            });
        } else {
            res.json({
                status: 'success',
                nim: nim,
                status_beasiswa: 'Belum Mengajukan'
            });
        }

    } catch (error) {
        console.error('Error in getStatusBeasiswa:', error);
        res.status(500).json({ status: 'error', message: 'Internal Server Error' });
    }
};

const verifikasiStatusBeasiswa = async (req, res) => {
    try {
        const nim = req.params.nim;
        let message = 'Data mahasiswa tidak ditemukan.';

        if (!nim) {
            return res.status(400).json({ status: 'error', message: 'NIM tidak diberikan' });
        }

        const [rows] = await pool.query("SELECT * FROM tb_pengajuan WHERE nim = ?", [nim]);

        if (rows.length > 0) {
            const m = rows[0];
            const badgeClass = m.badge_class;
            const catatan = m.catatan;

            if (badgeClass === 'bad') {
                m.status_verifikasi = 'Ditolak';
                m.jenis_mahasiswa = 'Belum menerima';
                m.status_beasiswa_siga = 'Tidak Aktif';
                m.catatan_admin = 'Tidak dapat diterima karena IPK rendah.';
                message = 'Mahasiswa ditolak karena IPK rendah.';
            } else if (badgeClass === 'wait') {
                m.status_verifikasi = 'Ditunda';
                m.jenis_mahasiswa = 'Belum menerima';
                m.status_beasiswa_siga = 'Belum Aktif';
                m.catatan_admin = catatan;
                message = 'Mahasiswa belum bisa diaktifkan: ' + catatan;
            } else {
                m.status_verifikasi = 'Terverifikasi';
                m.jenis_mahasiswa = 'Penerima Beasiswa';
                m.status_beasiswa_siga = 'Aktif';
                m.catatan_admin = 'Mahasiswa berhasil diverifikasi dan aktif sebagai penerima beasiswa.';
                message = 'Mahasiswa berhasil diverifikasi sebagai penerima beasiswa aktif.';
            }

            const updatedAt = new Date().toISOString().slice(0, 19).replace('T', ' ');

            await pool.query(`
                UPDATE tb_pengajuan SET 
                    status_verifikasi=?, jenis_mahasiswa=?, status_beasiswa_siga=?, 
                    catatan_admin=?, updated_at=? WHERE nim=?
            `, [
                m.status_verifikasi, m.jenis_mahasiswa, m.status_beasiswa_siga,
                m.catatan_admin, updatedAt, nim
            ]);

            res.json({
                status: 'success',
                message: message,
                data: {
                    nim: nim,
                    status_verifikasi: m.status_verifikasi,
                    jenis_mahasiswa: m.jenis_mahasiswa,
                    status_beasiswa_siga: m.status_beasiswa_siga
                }
            });

        } else {
            res.status(404).json({ status: 'error', message: message });
        }

    } catch (error) {
        console.error('Error in verifikasiStatusBeasiswa:', error);
        res.status(500).json({ status: 'error', message: 'Internal Server Error' });
    }
};

module.exports = {
    terimaPengajuan,
    getStatusBeasiswa,
    verifikasiStatusBeasiswa
};
