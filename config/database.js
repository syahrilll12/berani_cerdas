const mysql = require('mysql2/promise');
require('dotenv').config();

const pool = mysql.createPool({
    host: process.env.DB_HOST || 'localhost',
    user: process.env.DB_USER || 'admin_db',
    password: process.env.DB_PASS ?? 'PasswordKuat123!',
    database: process.env.DB_NAME || 'berani_cerdas',
    waitForConnections: true,
    connectionLimit: 10,
    queueLimit: 0
});

module.exports = pool;
