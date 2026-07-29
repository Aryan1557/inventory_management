<?php
require 'config.php';  // PHPMailer is loaded here
require 'includes/auth.php';
require_login();

$me = current_user();

// Get the default email account
$stmt = $pdo->prepare("SELECT * FROM email_acc WHERE user_id = ? ORDER BY id DESC LIMIT 1");
$stmt->execute([$me['user_id']]);
$email_account = $stmt->fetch();

if (!$email_account) {
    header('Location: compose.php?error=' . urlencode('Please configure your email account first. <a href="email_account.php">Go to Email Settings</a>'));
    exit;
}

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Accounts · MailApp</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .email-account-card {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: var(--radius);
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        .email-account-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .account-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .account-email {
            font-size: 18px;
            font-weight: 600;
            color: var(--navy);
        }
        .account-email .badge {
            font-size: 12px;
            background: var(--blue);
            color: white;
            padding: 2px 10px;
            border-radius: 12px;
            margin-left: 10px;
        }
        .account-details {
            color: var(--muted);
            font-size: 13px;
        }
        .account-details span {
            margin-right: 15px;
        }
        .account-actions {
            margin-top: 10px;
        }
        .account-actions button {
            margin-right: 8px;
        }
        .btn-danger {
            background: var(--red);
            color: white;
        }
        .btn-danger:hover {
            background: #c0392b;
        }
        .btn-success {
            background: #27ae60;
            color: white;
        }
        .btn-success:hover {
            background: #229954;
        }
        .btn-info {
            background: var(--blue);
            color: white;
        }
        .btn-info:hover {
            background: #2F5C8A;
        }
        .btn-sm {
            padding: 6px 14px;
            font-size: 12px;
        }
        .form-section {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: var(--radius);
            padding: 25px;
            margin-bottom: 30px;
        }
        .form-section h3 {
            margin-bottom: 20px;
            color: var(--navy);
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
        .empty-state {
            text-align: center;
            padding: 40px;
            color: var(--muted);
        }
        .empty-state .empty-icon {
            font-size: 48px;
            display: block;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
<div class="shell">
    <?php include 'includes/sidebar.php'; ?>
    <div class="main">
        <div class="topbar">
            <h2>📧 Email Accounts</h2>
            <span class="mono"><?= count($accounts) ?> account(s)</span>
        </div>
        <div class="content">
            <?php if ($message): ?>
                <div class="<?= $message_type === 'success' ? 'success-box' : 'error-box' ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>
            
            <!-- Add Account Form -->
            <div class="form-section">
                <h3>➕ Add Email Account</h3>
                <form method="POST">
                    <div class="form-row">
                        <div class="field">
                            <label>Email Address *</label>
                            <input type="email" name="email" placeholder="your-email@gmail.com" required>
                        </div>
                        <div class="field">
                            <label>App Password *</label>
                            <input type="password" name="app_password" placeholder="Enter app password" required>
                        </div>
                        <div class="field">
                            <label>SMTP Host</label>
                            <input type="text" name="smtp_host" value="smtp.gmail.com">
                        </div>
                        <div class="field">
                            <label>SMTP Port</label>
                            <input type="number" name="smtp_port" value="587">
                        </div>
                        <div class="field">
                            <label>Encryption</label>
                            <select name="encryption">
                                <option value="tls">TLS</option>
                                <option value="ssl">SSL</option>
                                <option value="none">None</option>
                            </select>
                        </div>
                    </div>
                    <div style="margin-top: 15px;">
                        <button type="submit" name="save_account" class="btn primary">💾 Save Account</button>
                    </div>
                </form>
            </div>
            
            <!-- Account List -->
            <h3>📋 Your Email Accounts</h3>
            <?php if (empty($accounts)): ?>
                <div class="empty-state">
                    <span class="empty-icon">📭</span>
                    <p>No email accounts configured yet.</p>
                    <p style="font-size: 13px; margin-top: 8px;">Add your Gmail account to send emails from MailApp.</p>
                </div>
            <?php else: ?>
                <?php foreach ($accounts as $account): ?>
                    <div class="email-account-card">
                        <div class="account-header">
                            <div>
                                <span class="account-email">
                                    <?= htmlspecialchars($account['email']) ?>
                                    <?php if ($account === $accounts[0]): ?>
                                        <span class="badge">Default</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <span style="color: var(--muted); font-size: 13px;">
                                Added: <?= date('M j, Y', strtotime($account['created_at'])) ?>
                            </span>
                        </div>
                        <div class="account-details">
                            <span>🔒 SMTP: <?= htmlspecialchars($account['smtp_host']) ?></span>
                            <span>🔢 Port: <?= htmlspecialchars($account['smtp_port']) ?></span>
                            <span>🔐 Encryption: <?= strtoupper(htmlspecialchars($account['encryption'])) ?></span>
                        </div>
                        <div class="account-actions">
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="account_id" value="<?= $account['id'] ?>">
                                <button type="submit" name="set_default" class="btn btn-sm btn-info">⭐ Set Default</button>
                            </form>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this account?');">
                                <input type="hidden" name="account_id" value="<?= $account['id'] ?>">
                                <button type="submit" name="delete_account" class="btn btn-sm btn-danger">🗑️ Delete</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>