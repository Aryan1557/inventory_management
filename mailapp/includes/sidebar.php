<?php
/**
 * includes/sidebar.php
 * Expects $active to be one of: inbox, sent, received, compose, email_accounts
 */

$me = current_user();

if (!$me) {
    header('Location: login.php');
    exit;
}

// Get counts
$unreadCount = $pdo->prepare("SELECT COUNT(*) FROM emails WHERE recipient_id = ? AND is_read = 0");
$unreadCount->execute([$me['user_id']]);
$unreadCount = $unreadCount->fetchColumn() ?: 0;

$sentCount = $pdo->prepare("SELECT COUNT(*) FROM emails WHERE sender_id = ?");
$sentCount->execute([$me['user_id']]);
$sentCount = $sentCount->fetchColumn() ?: 0;

$receivedCount = $pdo->prepare("SELECT COUNT(*) FROM emails WHERE recipient_id = ?");
$receivedCount->execute([$me['user_id']]);
$receivedCount = $receivedCount->fetchColumn() ?: 0;

$emailAccountsCount = $pdo->prepare("SELECT COUNT(*) FROM email_acc WHERE user_id = ?");
$emailAccountsCount->execute([$me['user_id']]);
$emailAccountsCount = $emailAccountsCount->fetchColumn() ?: 0;
?>
<div class="sidebar">
    <div class="brand"><span class="stamp"></span>MailApp</div>
    <a href="compose.php" class="compose-btn">+ Compose</a>
    <div class="nav">
        <a href="inbox.php" class="<?= $active === 'inbox' ? 'active' : '' ?>">
            📥 Inbox <span class="count"><?= $unreadCount ?></span>
        </a>
        <a href="sent.php" class="<?= $active === 'sent' ? 'active' : '' ?>">
            📤 Sent <span class="count"><?= $sentCount ?></span>
        </a>
        <a href="received.php" class="<?= $active === 'received' ? 'active' : '' ?>">
            📬 Received <span class="count"><?= $receivedCount ?></span>
        </a>
        <a href="manage_email.php" class="<?= $active === 'email_accounts' ? 'active' : '' ?>">
            ⚙️ Email Settings <span class="count"><?= $emailAccountsCount ?></span>
        </a>
    </div>
    <div class="user-box">
        <div class="name"><?= htmlspecialchars($me['name'] ?? 'User') ?></div>
        <div class="email"><?= htmlspecialchars($me['email'] ?? '') ?></div>
        <a href="logout.php">🚪 Log out</a>
    </div>
</div>