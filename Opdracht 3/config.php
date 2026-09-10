<?php
// Databaseconfiguratie (standaard XAMPP-instellingen)
define('DB_HOST', 'localhost');
define('DB_NAME', 'auto_reservering');
define('DB_USER', 'root');
define('DB_PASS', '');

// Uploads
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('UPLOAD_URL', 'uploads/');
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5 MB
define('ALLOWED_PHOTO_TYPES', ['jpg', 'jpeg', 'png', 'webp']);

// E-mail
define('MAIL_FROM', 'noreply@autoreservering.test');
define('MAIL_FROM_NAME', 'AutoReservering');

// Logbestand voor uitgaande e-mails (handig als er geen lokale mailserver is ingesteld)
define('MAIL_LOG_FILE', __DIR__ . '/logs/mail.log');
