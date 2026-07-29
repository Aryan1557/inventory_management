<?php
/**
 * includes/auth.php
 * Simple session-based auth helpers.
 */

function current_user() {
    global $pdo;
    
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    
    // If user_name or user_email is not in session, fetch from database
    if (!isset($_SESSION['user_name']) || !isset($_SESSION['user_email'])) {
        try {
            $stmt = $pdo->prepare('SELECT name, email_id FROM users WHERE user_id = ?');
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
            if ($user) {
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email_id'];
            }
        } catch (Exception $e) {
            // Handle error
        }
    }
    
    return [
        'user_id' => $_SESSION['user_id'] ?? null,
        'name'    => $_SESSION['user_name'] ?? 'User',
        'email'   => $_SESSION['user_email'] ?? '',
        'role'    => $_SESSION['user_role'] ?? 'User',
    ];
}

function require_login() {
    if (!current_user()) {
        header('Location: login.php');
        exit;
    }
}
