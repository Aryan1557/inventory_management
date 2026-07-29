<?php
require 'config.php';
require 'includes/auth.php';
require_login();
require 'includes/email_auth.php';

$me = current_user();
$active = 'compose';
$error = $_GET['error'] ?? '';

// Check if user has email account configured
$has_email_account = get_default_email_account($pdo, $me['user_id']);
if (!$has_email_account) {
    $error = '⚠️ Please configure your email account first. <a href="email_accounts.php" style="color: var(--blue); font-weight: bold;">Go to Email Data</a>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Compose · MailApp</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="shell">
    <?php include 'includes/sidebar.php'; ?>
    <div class="main">
        <div class="topbar"><h2>Compose</h2></div>
        <div class="content">
            <?php if ($error): ?><div class="error-box"><?= htmlspecialchars($error) ?></div><?php endif; ?>
            <div class="compose-card">
                <div class="cc-head">New message</div>
                <div class="cc-body">
                    <form method="post" action="send.php" enctype="multipart/form-data">
                        <div class="field">
                            <label>From</label>
                            <input type="text" value="<?= htmlspecialchars($me['name'] . ' <' . $me['email'] . '>') ?>" disabled>
                        </div>
                        <div class="field">
                            <label>To</label>
                            <input type="email" name="to" placeholder="recipient@example.com" required>
                        </div>
                        <div class="field">
                            <label>Subject</label>
                            <input type="text" name="subject" placeholder="Subject" required>
                        </div>
                        <div class="field">
                            <label>Message</label>
                            <textarea name="body" placeholder="Write your message…" required></textarea>
                        </div>
                        <div class="field">
                            <label>Attachments (optional, up to 5 files, 10&nbsp;MB each)</label>
                            <div class="file-drop">
                                <div>Choose one or more files to attach</div>
                                <input type="file" name="attachments[]" multiple>
                            </div>
                        </div>
                        <div class="actions-row">
                            <button type="submit" class="btn primary">Send</button>
                            <a href="inbox.php" class="btn" style="background:#EEF0F3;color:#333;">Discard</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
