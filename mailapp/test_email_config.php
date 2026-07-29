<?php
require 'config.php';
require 'includes/auth.php';
require_login();
require 'includes/email_auth.php';

$me = current_user();
$account = get_default_email_account($pdo, $me['user_id']);

if (!$account) {
    die('No email account configured. Please add one in <a href="email_accounts.php">Email Data</a>');
}

echo "<h2>Testing Email Configuration</h2>";
echo "<p>Email: " . htmlspecialchars($account['email']) . "</p>";
echo "<p>SMTP Host: " . htmlspecialchars($account['smtp_host']) . "</p>";
echo "<p>SMTP Port: " . htmlspecialchars($account['smtp_port']) . "</p>";

// Test the connection
require __DIR__ . '/vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = $account['smtp_host'];
    $mail->SMTPAuth = true;
    $mail->Username = $account['email'];
    $mail->Password = $account['app_password'];
    $mail->Port = $account['smtp_port'];
    
    if ($account['encryption'] === 'tls') {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    } elseif ($account['encryption'] === 'ssl') {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    }
    
    $mail->setFrom($account['email'], 'Test');
    $mail->addAddress($account['email']);
    $mail->Subject = 'SMTP Connection Test';
    $mail->Body = 'This is a test email to verify your SMTP settings.';
    
    if ($mail->send()) {
        echo "<p style='color: green;'>✅ SMTP connection successful! Test email sent.</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ SMTP connection failed: " . $mail->ErrorInfo . "</p>";
}
