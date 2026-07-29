<?php
/**
 * includes/email_auth.php
 * Handles email account authentication and management
 */

function get_user_email_accounts($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT * FROM email_acc WHERE user_id = ? ORDER BY id DESC");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

function get_user_email_account($pdo, $user_id, $email) {
    $stmt = $pdo->prepare("SELECT * FROM email_acc WHERE user_id = ? AND email = ?");
    $stmt->execute([$user_id, $email]);
    return $stmt->fetch();
}

function get_default_email_account($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT * FROM email_acc WHERE user_id = ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

function save_email_account($pdo, $user_id, $email, $app_password, $smtp_host = 'smtp.gmail.com', $smtp_port = 587, $encryption = 'tls') {
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
            INSERT INTO email_acc (user_id, email, app_password, smtp_host, smtp_port, encryption)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$user_id, $email, $app_password, $smtp_host, $smtp_port, $encryption]);
    }
}

function delete_email_account($pdo, $user_id, $account_id) {
    $stmt = $pdo->prepare("DELETE FROM email_acc WHERE id = ? AND user_id = ?");
    return $stmt->execute([$account_id, $user_id]);
}

function get_user_email_config($pdo, $user_id) {
    $account = get_default_email_account($pdo, $user_id);
    if ($account) {
        return [
            'smtp_host' => $account['smtp_host'],
            'smtp_port' => $account['smtp_port'],
            'smtp_user' => $account['email'],
            'smtp_pass' => $account['app_password'],
            'encryption' => $account['encryption']
        ];
    }
    return null;
}