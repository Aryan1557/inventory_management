<?php
require 'config.php';
require 'includes/auth.php';
require_login(); // This will redirect to login if not logged in
$me = current_user();
$active = 'received';

$stmt = $pdo->prepare("
    SELECT e.*, u.name AS sender_name,
           (SELECT COUNT(*) FROM attachments a WHERE a.email_id = e.id) AS attach_count
    FROM emails e
    JOIN users u ON u.user_id = e.sender_id
    WHERE e.recipient_id = ?
    ORDER BY e.created_at DESC
");
$stmt->execute([$me['user_id']]);
$emails = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Received · MailApp</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="shell">
    <?php include 'includes/sidebar.php'; ?>
    <div class="main">
        <div class="topbar">
            <h2>Received</h2>
            <span class="mono"><?= count($emails) ?> total</span>
        </div>
        <div class="content">
            <?php if (empty($emails)): ?>
                <div class="empty-state">
                    <div class="stamp-big"></div>
                    <p>No mail has arrived at your address yet.</p>
                </div>
            <?php else: ?>
                <div class="email-list">
                    <?php foreach ($emails as $e): ?>
                        <a class="email-row <?= $e['is_read'] ? '' : 'unread' ?>" href="view_email.php?id=<?= $e['id'] ?>&folder=received">
                            <?php if (!$e['is_read']): ?><span class="unread-dot"></span><?php else: ?><span style="width:7px"></span><?php endif; ?>
                            <span class="who"><?= htmlspecialchars($e['sender_name']) ?></span>
                            <span class="subject-line">
                                <?= htmlspecialchars($e['subject']) ?>
                                <span class="preview">— <?= htmlspecialchars(mb_strimwidth(strip_tags($e['body']), 0, 80, '…')) ?></span>
                            </span>
                            <span class="meta">
                                <?php if ($e['attach_count'] > 0): ?><span class="clip">📎 <?= $e['attach_count'] ?></span><?php endif; ?>
                                <span class="date"><?= date('M j, g:ia', strtotime($e['created_at'])) ?></span>
                            </span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>