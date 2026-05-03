const jwt = require('jsonwebtoken');
require('dotenv').config();

const authenticateToken = (req, res, next) => {
    const authHeader = req.headers['authorization'];
    const token = authHeader && authHeader.split(' ')[1]; // Format: Bearer TOKEN

    if (!token) {
        return res.status(401).json({ status: 'error', message: 'Token JWT tidak ditemukan. Harap gunakan header Authorization: Bearer <token>' });
    }

    jwt.verify(token, process.env.JWT_SECRET || 'super_secret_jwt_key_siga8_berani_cerdas', (err, user) => {
        if (err) {
            return res.status(403).json({ status: 'error', message: 'Token tidak valid atau sudah kadaluarsa.' });
        }
        req.user = user;
        next();
    });
};

module.exports = authenticateToken;
