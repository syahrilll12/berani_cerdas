const rateLimit = require('express-rate-limit');

const apiLimiter = rateLimit({
    windowMs: 15 * 60 * 1000, // 15 menit
    max: 100, // batasi setiap IP hingga 100 request per windowMs
    message: {
        status: 'error',
        message: 'Terlalu banyak request dari IP ini, coba lagi setelah 15 menit.'
    },
    standardHeaders: true, // Return rate limit info in the `RateLimit-*` headers
    legacyHeaders: false, // Disable the `X-RateLimit-*` headers
});

module.exports = apiLimiter;
