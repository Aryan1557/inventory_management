<?php
require 'config.php';
require 'includes/auth.php';
require_login(); // This will redirect to login if not logged in
$me = current_user();
$active = 'sent';

$stmt = $pdo->prepare("
    SELECT e.*,
           (SELECT COUNT(*) FROM attachments a WHERE a.email_id = e.id) AS attach_count
    FROM emails e
    WHERE e.sender_id = ?
    ORDER BY e.created_at DESC
");
$stmt->execute([$me['user_id']]);
$emails = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sent · MailApp</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="shell">
    <?php include 'includes/sidebar.php'; ?>
    <div class="main">
        <div class="topbar">
            <h2>Sent</h2>
            <span class="mono"><?= count($emails) ?> total</span>
        </div>
        <div class="content">
            <?php if (empty($emails)): ?>
                <div class="empty-state">
                    <div class="stamp-big"></div>
                    <p>You haven't sent anything yet. <a href="compose.php">Write your first email</a>.</p>
                </div>
            <?php else: ?>
                <div class="email-list">
                    <?php foreach ($emails as $e): ?>
                        <a class="email-row" href="view_email.php?id=<?= $e['id'] ?>&folder=sent">
                            <span style="width:7px"></span>
                            <span class="who">To: <?= htmlspecialchars($e['recipient_email']) ?></span>
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