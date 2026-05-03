const express = require('express');
const router = express.Router();
const pengajuanController = require('../controllers/pengajuanController');
const authenticateToken = require('../middleware/auth');
const apiLimiter = require('../middleware/rateLimiter');
const jwt = require('jsonwebtoken');

// Endpoint untuk mendapatkan token JWT (opsional, jika SIGA-8 tidak memiliki secret yang sama)
router.post('/login', apiLimiter, (req, res) => {
    // Pada skenario nyata, validasi username/password.
    // Di sini kita berikan token untuk simulasi integrasi SIGA-8.
    const { client_id, client_secret } = req.body;
    
    // Contoh sederhana: hanya secret tertentu yang boleh
    if (client_id === 'siga8' && client_secret === 'secret123') {
        const payload = { system: 'siga-8' };
        const token = jwt.sign(payload, process.env.JWT_SECRET || 'super_secret_jwt_key_siga8_berani_cerdas', { expiresIn: '1h' });
        res.json({ token: token });
    } else {
        res.status(401).json({ status: 'error', message: 'Kredensial tidak valid' });
    }
});

// Middleware diterapkan pada route di bawah ini
router.use(apiLimiter);
router.use(authenticateToken);

// Routes
router.post('/terima-pengajuan', pengajuanController.terimaPengajuan);
router.get('/get-status/:nim', pengajuanController.getStatusBeasiswa);
router.put('/verifikasi-status/:nim', pengajuanController.verifikasiStatusBeasiswa);

module.exports = router;
