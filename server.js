const express = require('express');
const cors = require('cors');
const fs = require('fs');
const path = require('path');
const morgan = require('morgan');
require('dotenv').config();

const apiRoutes = require('./routes/api');

const app = express();

// Buat folder logs jika belum ada
const logDirectory = path.join(__dirname, 'logs');
if (!fs.existsSync(logDirectory)) {
    fs.mkdirSync(logDirectory);
}

// Konfigurasi stream untuk logging ke file access.log
const accessLogStream = fs.createWriteStream(path.join(logDirectory, 'access.log'), { flags: 'a' });

app.set('trust proxy', 1);

const port = process.env.PORT || 3000;

// Middleware global
app.use(cors());
app.use(morgan('combined', { stream: accessLogStream })); // Logging output ke file logs/access.log
app.use(morgan('dev')); // Logging output berwarna ke console terminal
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Routing API
app.use('/api', apiRoutes);

// Menangani endpoint GET /api/terima-pengajuan yang digunakan sistem lama untuk mengecek readiness
app.get('/api/terima-pengajuan', (req, res) => {
    res.json({
        status: 'ready',
        message: 'API Berani Cerdas siap menerima pengajuan beasiswa dari SIGA.',
        endpoint: 'POST /api/terima-pengajuan',
        format_didukung: [
            'object langsung',
            'object dengan data_received',
            'array berisi object dengan data_received'
        ]
    });
});

// 404 Handler
app.use((req, res) => {
    res.status(404).json({ status: 'error', message: 'Endpoint tidak ditemukan.' });
});

// Jalankan server
app.listen(port, () => {
    console.log(`Server Berani Cerdas berjalan di http://localhost:${port}`);
    console.log(`Menunggu request dari sistem SIGA-8...`);
});
