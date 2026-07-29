<?php
require 'config.php';
require 'includes/auth.php';
require_login();

$me = current_user();

// Get the default email account
$stmt = $pdo->prepare("SELECT * FROM email_acc WHERE user_id = ? ORDER BY id DESC LIMIT 1");
$stmt->execute([$me['user_id']]);
$email_account = $stmt->fetch();

if (!$email_account) {
    header('Location: compose.php?error=' . urlencode('Please configure your email account first. <a href="manage_email.php">Go to Email Settings</a>'));
    exit;
}

require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function fail($msg) {
    header('Location: compose.php?error=' . urlencode($msg));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: compose.php');
    exit;
}

$to      = trim($_POST['to'] ?? '');
$subject = trim($_POST['subject'] ?? '') ?: '(no subject)';
$body    = trim($_POST['body'] ?? '');

if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
    fail('Please enter a valid recipient email address.');
}
if ($body === '') {
    fail('Message body cannot be empty.');
}

// ---------- Handle attachments ----------
$movedFiles = [];

if (!empty($_FILES['attachments']['name'][0])) {
    $count = count($_FILES['attachments']['name']);
    if ($count > 5) {
        fail('You can attach a maximum of 5 files.');
    }
    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0775, true);
    }
    for ($i = 0; $i < $count; $i++) {
        if ($_FILES['attachments']['error'][$i] === UPLOAD_ERR_NO_FILE) continue;
        if ($_FILES['attachments']['error'][$i] !== UPLOAD_ERR_OK) {
            fail('There was a problem uploading one of your files.');
        }
        if ($_FILES['attachments']['size'][$i] > MAX_ATTACHMENT_SIZE) {
            fail('"' . $_FILES['attachments']['name'][$i] . '" is larger than 10 MB.');
        }

        $original = $_FILES['attachments']['name'][$i];
        $ext      = pathinfo($original, PATHINFO_EXTENSION);
        $stored   = bin2hex(random_bytes(16)) . ($ext ? '.' . $ext : '');
        $dest     = UPLOAD_DIR . $stored;

        if (!move_uploaded_file($_FILES['attachments']['tmp_name'][$i], $dest)) {
            fail('Could not save "' . $original . '".');
        }

        $movedFiles[] = [
            'path'     => $dest,
            'original' => $original,
            'size'     => $_FILES['attachments']['size'][$i],
            'stored'   => $stored,
        ];
    }
}

// ---------- Send via PHPMailer using stored credentials ----------
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = $email_account['smtp_host'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $email_account['email'];
    $mail->Password   = $email_account['app_password'];
    $mail->Port       = $email_account['smtp_port'];
    
    if ($email_account['encryption'] === 'tls') {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    } elseif ($email_account['encryption'] === 'ssl') {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    }
    
    $mail->Timeout = 30;

    // Sender
    $sender_name = SMTP_FROM_NAME . ' (' . ($me['name'] ?? 'User') . ')';
    $mail->setFrom($email_account['email'], $sender_name);
    
    // Reply-To
    $reply_to_email = $me['email'] ?? $email_account['email'];
    $reply_to_name = $me['name'] ?? 'User';
    
    if (filter_var($reply_to_email, FILTER_VALIDATE_EMAIL)) {
        $mail->addReplyTo($reply_to_email, $reply_to_name);
    }

    // Recipient
    $mail->addAddress($to);

    // Attachments
    foreach ($movedFiles as $f) {
        if (file_exists($f['path'])) {
            $mail->addAttachment($f['path'], $f['original']);
        }
    }

    $mail->Subject = $subject;
    $mail->Body    = $body;
    $mail->isHTML(false);

    $mail->send();
    
} catch (Exception $e) {
    // Clean up files
    foreach ($movedFiles as $f) {
        if (file_exists($f['path'])) {
            unlink($f['path']);
        }
    }
    error_log('Mailer Error: ' . $mail->ErrorInfo);
    fail('Message was not sent. Error: ' . $mail->ErrorInfo);
}

// ---------- Log to database ----------
$recipientLookup = $pdo->prepare('SELECT user_id FROM users WHERE email_id = ?');
$recipientLookup->execute([$to]);
$recipientRow = $recipientLookup->fetch();
$recipientId  = $recipientRow ? $recipientRow['user_id'] : null;

$insert = $pdo->prepare('
    INSERT INTO emails (sender_id, recipient_email, recipient_id, subject, body, is_read, created_at)
    VALUES (?, ?, ?, ?, ?, 0, NOW())
');
$insert->execute([$me['user_id'], $to, $recipientId, $subject, $body]);
$emailId = $pdo->lastInsertId();

if (!empty($movedFiles)) {
    $attStmt = $pdo->prepare('INSERT INTO attachments (email_id, original_name, stored_name, filesize) VALUES (?, ?, ?, ?)');
    foreach ($movedFiles as $f) {
        $attStmt->execute([$emailId, $f['original'], $f['stored'], $f['size']]);
    }
}

header('Location: sent.php?sent=1');
exit;