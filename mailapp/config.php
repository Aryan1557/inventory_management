<?php
/**
 * config.php
 */

session_start();

// ---------- DATABASE ----------
define('DB_HOST', 'localhost');
define('DB_NAME', 'inventory_management');
define('DB_USER', 'root');
define('DB_PASS', '');

// ---------- SMTP ----------
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASS', 'your-app-password');
define('SMTP_FROM_NAME', 'MailApp');

// ---------- MISC ----------
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('MAX_ATTACHMENT_SIZE', 10 * 1024 * 1024);

// ---------- AUTOLOAD PHPMailer (from vendor folder) ----------
require __DIR__ . '/vendor/autoload.php';

// Use PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// ---------- DB CONNECTION ----------
try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}   