<?php
require 'config.php';
require 'includes/auth.php';
require_login();

$me = current_user();
$active = 'email_accounts';
$message = '';
$message_type = '';

// Get all email accounts for the current user
function getUserEmailAccounts($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT * FROM email_acc WHERE user_id = ? ORDER BY id DESC");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

// Save email account
function saveEmailAccount($pdo, $user_id, $email, $app_password, $smtp_host = 'smtp.gmail.com', $smtp_port = 587, $encryption = 'tls') {
    // Check if account already exists
    $check = $pdo->prepare("SELECT id FROM email_acc WHERE user_id = ? AND email = ?");
    $check->execute([$user_id, $email]);
    $existing = $check->fetch();
    
    if ($existing) {
        // Update existing account
        $stmt = $pdo->prepare("
            UPDATE email_acc 
            SET app_password = ?, smtp_host = ?, smtp_port = ?, encryption = ?, updated_at = NOW()
            WHERE user_id = ? AND email = ?
        ");
        return $stmt->execute([$app_password, $smtp_host, $smtp_port, $encryption, $user_id, $email]);
    } else {
        // Insert new account
        $stmt = $pdo->prepare("
            INSERT INTO email_acc (user_id, email, app_password, smtp_host, smtp_port, encryption, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        return $stmt->execute([$user_id, $email, $app_password, $smtp_host, $smtp_port, $encryption]);
    }
}

// Delete email account
function deleteEmailAccount($pdo, $user_id, $account_id) {
    $stmt = $pdo->prepare("DELETE FROM email_acc WHERE id = ? AND user_id = ?");
    return $stmt->execute([$account_id, $user_id]);
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_account'])) {
        $email = trim($_POST['email'] ?? '');
        $app_password = trim($_POST['app_password'] ?? '');
        $smtp_host = trim($_POST['smtp_host'] ?? 'smtp.gmail.com');
        $smtp_port = intval($_POST['smtp_port'] ?? 587);
        $encryption = trim($_POST['encryption'] ?? 'tls');
        
        if (empty($email) || empty($app_password)) {
            $message = 'Please fill in both email and app password.';
            $message_type = 'error';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = 'Please enter a valid email address.';
            $message_type = 'error';
        } else {
            if (saveEmailAccount($pdo, $me['user_id'], $email, $app_password, $smtp_host, $smtp_port, $encryption)) {
                $message = 'Email account saved successfully!';
                $message_type = 'success';
            } else {
                $message = 'Failed to save email account.';
                $message_type = 'error';
            }
        }
    }
    
    if (isset($_POST['delete_account'])) {
        $account_id = intval($_POST['account_id'] ?? 0);
        if (deleteEmailAccount($pdo, $me['user_id'], $account_id)) {
            $message = 'Email account deleted successfully!';
            $message_type = 'success';
        } else {
            $message = 'Failed to delete email account.';
            $message_type = 'error';
        }
    }
}

$accounts = getUserEmailAccounts($pdo, $me['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Email Accounts · MailApp</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .page-header h2 {
            font-size: 24px;
            color: var(--navy);
        }
        .page-header .badge {
            background: var(--blue);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 13px;
        }
        .card {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: var(--radius);
            padding: 25px;
            margin-bottom: 25px;
            transition: all 0.3s ease;
        }
        .card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        .card h3 {
            margin-bottom: 20px;
            color: var(--navy);
            font-size: 18px;
        }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            font-size: 12.5px;
            font-weight: 500;
            color: var(--muted);
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: .04em;
        }
        .form-group label .required {
            color: var(--red);
            margin-left: 3px;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--line);
            border-radius: 8px;
            font-family: inherit;
            font-size: 14px;
            background: var(--paper);
            transition: all 0.3s ease;
        }
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: var(--blue);
            box-shadow: 0 0 0 3px rgba(58,110,165,.15);
        }
        .form-group .hint {
            font-size: 12px;
            color: var(--muted);
            margin-top: 4px;
        }
        .form-group .hint a {
            color: var(--blue);
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            font-family: inherit;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-primary {
            background: var(--blue);
            color: white;
        }
        .btn-primary:hover {
            background: #2F5C8A;
            transform: translateY(-1px);
        }
        .btn-danger {
            background: var(--red);
            color: white;
        }
        .btn-danger:hover {
            background: #c0392b;
            transform: translateY(-1px);
        }
        .btn-success {
            background: #27ae60;
            color: white;
        }
        .btn-success:hover {
            background: #229954;
            transform: translateY(-1px);
        }
        .btn-warning {
            background: #f39c12;
            color: white;
        }
        .btn-warning:hover {
            background: #d68910;
            transform: translateY(-1px);
        }
        .btn-sm {
            padding: 6px 14px;
            font-size: 12px;
        }
        .btn-block {
            width: 100%;
            display: block;
            text-align: center;
        }
        .account-list {
            display: grid;
            gap: 15px;
        }
        .account-item {
            background: var(--paper);
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 18px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
            flex-wrap: wrap;
            gap: 15px;
        }
        .account-item:hover {
            border-color: var(--blue);
            box-shadow: 0 2px 8px rgba(58,110,165,.1);
        }
        .account-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .account-email {
            font-size: 16px;
            font-weight: 600;
            color: var(--navy);
        }
        .account-details {
            font-size: 13px;
            color: var(--muted);
        }
        .account-details span {
            margin-right: 15px;
        }
        .account-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--muted);
        }
        .empty-state .icon {
            font-size: 48px;
            display: block;
            margin-bottom: 15px;
        }
        .empty-state h4 {
            color: var(--navy);
            margin-bottom: 8px;
        }
        .badge-default {
            background: #27ae60;
            color: white;
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 11px;
            margin-left: 10px;
        }
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .alert-success {
            background: #EAF6EE;
            border: 1px solid #BFE3CB;
            color: #1F7A3E;
        }
        .alert-error {
            background: #FDECEC;
            border: 1px solid #F5B5B8;
            color: #A3242C;
        }
        .alert-info {
            background: #E8F0FE;
            border: 1px solid #B8D0F0;
            color: #1A5A8A;
        }
        .full-width {
            grid-column: 1 / -1;
        }
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            .account-item {
                flex-direction: column;
                align-items: stretch;
            }
            .account-actions {
                justify-content: flex-start;
            }
        }
    </style>
</head>
<body>
<div class="shell">
    <?php include 'includes/sidebar.php'; ?>
    <div class="main">
        <div class="topbar">
            <h2>📧 Manage Email Accounts</h2>
            <span class="mono"><?= count($accounts) ?> account(s)</span>
        </div>
        <div class="content">
            <div class="container">
                <?php if ($message): ?>
                    <div class="alert alert-<?= $message_type === 'success' ? 'success' : 'error' ?>">
                        <?= htmlspecialchars($message) ?>
                    </div>
                <?php endif; ?>

                <!-- Add/Edit Account Form -->
                <div class="card">
                    <h3>➕ Add Email Account</h3>
                    <div class="alert alert-info">
                        <strong>📌 Note:</strong> This only stores email configuration. No user account is created.
                        You must have an existing user account to manage emails.
                    </div>
                    <form method="POST">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Email Address <span class="required">*</span></label>
                                <input type="email" name="email" placeholder="your-email@gmail.com" required>
                            </div>
                            <div class="form-group">
                                <label>App Password <span class="required">*</span></label>
                                <input type="password" name="app_password" placeholder="Enter app password" required>
                                <div class="hint">
                                    <a href="https://myaccount.google.com/apppasswords" target="_blank">
                                        How to get Gmail app password?
                                    </a>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>SMTP Host</label>
                                <input type="text" name="smtp_host" value="smtp.gmail.com">
                            </div>
                            <div class="form-group">
                                <label>SMTP Port</label>
                                <input type="number" name="smtp_port" value="587">
                            </div>
                            <div class="form-group full-width">
                                <label>Encryption</label>
                                <select name="encryption">
                                    <option value="tls">TLS</option>
                                    <option value="ssl">SSL</option>
                                    <option value="none">None</option>
                                </select>
                            </div>
                        </div>
                        <button type="submit" name="save_account" class="btn btn-primary btn-block">💾 Save Account</button>
                    </form>
                </div>

                <!-- Account List -->
                <h3 style="margin-bottom: 15px;">📋 Your Email Accounts</h3>
                
                <?php if (empty($accounts)): ?>
                    <div class="card">
                        <div class="empty-state">
                            <span class="icon">📭</span>
                            <h4>No Email Accounts Configured</h4>
                            <p>Add your email account above to start sending emails.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="account-list">
                        <?php foreach ($accounts as $account): ?>
                            <div class="account-item">
                                <div class="account-info">
                                    <div class="account-email">
                                        <?= htmlspecialchars($account['email']) ?>
                                        <?php if ($account === $accounts[0]): ?>
                                            <span class="badge-default">Default</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="account-details">
                                        <span>🔒 SMTP: <?= htmlspecialchars($account['smtp_host']) ?></span>
                                        <span>🔢 Port: <?= htmlspecialchars($account['smtp_port']) ?></span>
                                        <span>🔐 Encryption: <?= strtoupper(htmlspecialchars($account['encryption'])) ?></span>
                                        <span>📅 Added: <?= date('M j, Y', strtotime($account['created_at'])) ?></span>
                                    </div>
                                </div>
                                <div class="account-actions">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="account_id" value="<?= $account['id'] ?>">
                                        <button type="submit" name="set_default" class="btn btn-sm btn-warning">⭐ Default</button>
                                    </form>
                                    <form method="POST" style="display: inline;" 
                                          onsubmit="return confirm('Are you sure you want to delete this email account?');">
                                        <input type="hidden" name="account_id" value="<?= $account['id'] ?>">
                                        <button type="submit" name="delete_account" class="btn btn-sm btn-danger">🗑️ Delete</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>