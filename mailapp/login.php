<?php
require 'config.php';
require 'includes/auth.php';

// If already logged in, redirect to inbox
if (current_user()) { 
    header('Location: inbox.php'); 
    exit; 
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    // Validate inputs
    if (empty($email) || empty($pass)) {
        $error = 'Please enter both email and password.';
    } else {
        try {
            $stmt = $pdo->prepare('SELECT * FROM users WHERE email_id = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($pass, $user['password_hash'])) {
                // Set all session variables properly
                $_SESSION['user_id']    = $user['user_id'];
                $_SESSION['user_name']  = $user['name'];
                $_SESSION['user_email'] = $user['email_id'];
                $_SESSION['user_role']  = $user['role'] ?? 'User';
                $_SESSION['user_employee_id'] = $user['employee_id'] ?? null;
                
                // Regenerate session ID for security
                session_regenerate_id(true);
                
                header('Location: inbox.php');
                exit;
            } else {
                $error = 'Incorrect email or password.';
            }
        } catch (PDOException $e) {
            $error = 'Database error occurred. Please try again.';
            error_log('Login error: ' . $e->getMessage());
        }
    }
}
// include __DIR__ . '/../sidebar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log in · MailApp</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .auth-wrap {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--navy);
            padding: 20px;
        }
        .auth-card {
            width: 100%;
            max-width: 420px;
            background: var(--panel);
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,.35);
        }
        .auth-card .body {
            padding: 32px 30px;
        }
        .auth-card h1 {
            font-size: 26px;
            color: var(--navy);
            margin-bottom: 8px;
        }
        .auth-card p.tag {
            color: var(--muted);
            margin-bottom: 24px;
            font-size: 13.5px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            background: var(--navy);
            color: #fff;
            font-family: inherit;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn:hover {
            background: var(--navy-soft);
            text-decoration: none;
            transform: translateY(-1px);
        }
        .btn.primary {
            background: var(--blue);
        }
        .btn.primary:hover {
            background: #2F5C8A;
        }
        .btn.block {
            width: 100%;
            display: block;
            text-align: center;
        }
        .switch-link {
            margin-top: 18px;
            font-size: 13.5px;
            text-align: center;
            color: var(--muted);
        }
        .switch-link a {
            color: var(--blue);
            font-weight: 500;
        }
        .switch-link a:hover {
            text-decoration: underline;
        }
        .field {
            margin-bottom: 18px;
        }
        .field label {
            display: block;
            font-size: 12.5px;
            font-weight: 500;
            color: var(--muted);
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: .04em;
        }
        .field input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--line);
            border-radius: 8px;
            font-family: inherit;
            font-size: 14px;
            background: var(--paper);
            transition: all 0.3s ease;
        }
        .field input:focus {
            outline: none;
            border-color: var(--blue);
            box-shadow: 0 0 0 3px rgba(58,110,165,.15);
        }
        .field input.error {
            border-color: var(--red);
        }
        .error-box {
            background: #FDECEC;
            border: 1px solid #F5B5B8;
            color: #A3242C;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 13.5px;
            margin-bottom: 18px;
        }
        .airmail-stripe {
            height: 6px;
            background: repeating-linear-gradient(
                -45deg,
                var(--red) 0 14px,
                var(--paper) 14px 20px,
                var(--blue) 20px 34px,
                var(--paper) 34px 40px
            );
        }
        .logo-text {
            font-family: 'Fraunces', serif;
            font-weight: 600;
            color: var(--navy);
        }
        .logo-text .stamp {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 2px;
            background: var(--red);
            margin-right: 8px;
            vertical-align: middle;
        }
        @media (max-width: 480px) {
            .auth-card .body {
                padding: 24px 20px;
            }
            .auth-card h1 {
                font-size: 22px;
            }
        }
    </style>
</head>
<body>
<div class="auth-wrap">
    <div class="auth-card">
        <div class="airmail-stripe"></div>
        <div class="body">
            <h1><span class="logo-text"><span class="stamp"></span>Welcome back</span></h1>
            <p class="tag">Log in to access your mailbox.</p>

            <?php if ($error): ?>
                <div class="error-box"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="post" novalidate>
                <div class="field">
                    <label for="email">Email address</label>
                    <input 
                        type="email" 
                        id="email"
                        name="email" 
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" 
                        placeholder="your@email.com"
                        required
                    >
                </div>
                <div class="field">
                    <label for="password">Password</label>
                    <input 
                        type="password" 
                        id="password"
                        name="password" 
                        placeholder="Enter your password"
                        required
                    >
                </div>
                <button type="submit" class="btn primary block">Log in</button>
            </form>
            <div class="switch-link">
                Don't have an account? <a href="add_email.php">Create one</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>