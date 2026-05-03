const express = require('express');
const cors = require('cors');
require('dotenv').config();

const apiRoutes = require('./routes/api');

const app = express();
const port = process.env.PORT || 3000;

// Middleware global
app.use(cors());
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
