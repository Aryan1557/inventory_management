<?php
require 'config.php';
require 'includes/auth.php';
require_login();
$me = current_user();

$id     = (int)($_GET['id'] ?? 0);
$folder = $_GET['folder'] ?? 'inbox';
$active = in_array($folder, ['inbox', 'sent', 'received']) ? $folder : 'inbox';

$stmt = $pdo->prepare("
    SELECT e.*, su.name AS sender_name, su.email_id AS sender_email
    FROM emails e
    JOIN users su ON su.user_id = e.sender_id
    WHERE e.id = ? AND (e.sender_id = ? OR e.recipient_id = ?)
");
$stmt->execute([$id, $me['user_id'], $me['user_id']]);
$email = $stmt->fetch();

if (!$email) {
    header('Location: inbox.php');
    exit;
}

// Mark as read if the current user is the recipient viewing it
if ($email['recipient_id'] == $me['user_id'] && !$email['is_read']) {
    $pdo->prepare('UPDATE emails SET is_read = 1 WHERE id = ?')->execute([$id]);
    $email['is_read'] = 1;
}

$attStmt = $pdo->prepare('SELECT * FROM attachments WHERE email_id = ?');
$attStmt->execute([$id]);
$attachments = $attStmt->fetchAll();

$isIncoming = $email['recipient_id'] == $me['user_id'];
$backHref = ['inbox' => 'inbox.php', 'sent' => 'sent.php', 'received' => 'received.php'][$active];

function human_size($bytes) {
    if ($bytes < 1024) return $bytes . ' B';
    if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
    return round($bytes / 1048576, 1) . ' MB';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($email['subject']) ?> · MailApp</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="shell">
    <?php include 'includes/sidebar.php'; ?>
    <div class="main">
        <div class="topbar"><h2>Message</h2></div>
        <div class="content">
            <a class="back-link" href="<?= $backHref ?>">&larr; Back to <?= ucfirst($active) ?></a>
            <div class="email-detail">
                <h2><?= htmlspecialchars($email['subject']) ?></h2>
                <div class="meta-row">
                    <div class="from-to">
                        <?php if ($isIncoming): ?>
                            <div><b>From:</b> <?= htmlspecialchars($email['sender_name']) ?> &lt;<?= htmlspecialchars($email['sender_email']) ?>&gt;</div>
                            <div><b>To:</b> <?= htmlspecialchars($me['email']) ?></div>
                        <?php else: ?>
                            <div><b>From:</b> <?= htmlspecialchars($me['name']) ?> &lt;<?= htmlspecialchars($me['email']) ?>&gt;</div>
                            <div><b>To:</b> <?= htmlspecialchars($email['recipient_email']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="mono"><?= date('M j, Y \a\t g:ia', strtotime($email['created_at'])) ?></div>
                </div>
                <div class="body-text"><?= htmlspecialchars($email['body']) ?></div>

                <?php if (!empty($attachments)): ?>
                    <div class="attachment-list">
                        <?php foreach ($attachments as $a): ?>
                            <a class="attachment-chip" href="download_attachment.php?id=<?= $a['id'] ?>">
                                📎 <?= htmlspecialchars($a['original_name']) ?>
                                <span class="size">(<?= human_size($a['filesize']) ?>)</span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>