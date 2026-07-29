<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include 'db_connection.php';

// ========== CREATE OR UPDATE LOGIN ATTEMPTS TABLE ==========
function createLoginAttemptsTable($conn) {
    $tableCheck = "SHOW TABLES LIKE 'login_attempts'";
    $result = mysqli_query($conn, $tableCheck);

    if (mysqli_num_rows($result) == 0) {
        $sql = "CREATE TABLE IF NOT EXISTS login_attempts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            attempts INT DEFAULT 0,
            lock_until DATETIME DEFAULT NULL,
            permanent_lock_until DATETIME DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_username (username),
            INDEX idx_email (email),
            INDEX idx_lock_until (lock_until),
            INDEX idx_permanent_lock (permanent_lock_until)
        )";

        mysqli_query($conn, $sql) or die("Failed to create login_attempts table: " . mysqli_error($conn));
    } else {
        $checkColumn = "SHOW COLUMNS FROM login_attempts LIKE 'permanent_lock_until'";
        $columnResult = mysqli_query($conn, $checkColumn);

        if (mysqli_num_rows($columnResult) == 0) {
            $alterSql = "ALTER TABLE login_attempts ADD COLUMN permanent_lock_until DATETIME DEFAULT NULL";
            mysqli_query($conn, $alterSql) or die("Failed to add permanent_lock_until column: " . mysqli_error($conn));
        }
    }
}

createLoginAttemptsTable($conn);

// ========== CONFIG ==========
const MAX_ATTEMPTS_BEFORE_PERMANENT = 12; // total failed attempts (4+4+4) -> 24h block
const TEMP_LOCK_EVERY = 4;                // repeats at 4, 8, 12
const TEMP_LOCK_SECONDS = 45;
const PERMANENT_LOCK_SECONDS = 86400;     // 24 hours

// ========== LOGOUT HANDLER ==========
if (isset($_GET['logout'])) {
    $user_id = $_SESSION['user_id'] ?? null;
    $employee_id = $_SESSION['employee_id'] ?? $_SESSION['admin_id'] ?? null;
    $employee_name = $_SESSION['name'] ?? $_SESSION['employee_name'] ?? $_SESSION['admin_name'] ?? null;
    $ip_address = $_SERVER['REMOTE_ADDR'];

    if ($employee_id && $employee_name) {
        $sql = "INSERT INTO user_activity
                (employee_id, employee_name, ip_address, activity_type, logout_time, activity_details)
                VALUES
                (?, ?, ?, 'Logout', NOW(), 'User logged out')";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sss", $employee_id, $employee_name, $ip_address);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    if ($user_id) {
        $stmt = mysqli_prepare($conn, "UPDATE users SET session_token = NULL WHERE user_id = ?");
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    session_unset();
    session_destroy();
    header("Location: login.php?logged_out=1");
    exit();
}

// ========== LOGIN HANDLER ==========
$loginError = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);

function showError($error) {
    return !empty($error) ? "<p class='error-message'>$error</p>" : '';
}

// Check if account is locked and get lock time.
// IMPORTANT: when a *temporary* lock expires we only clear lock_until,
// we do NOT reset the attempts counter — otherwise the count could
// never climb to 8 or 12 because it would keep resetting every 45s.
function getLockStatus($conn, $identifier) {
    $stmt = mysqli_prepare($conn, "SELECT lock_until, permanent_lock_until, attempts FROM login_attempts WHERE (username = ? OR email = ?)");
    mysqli_stmt_bind_param($stmt, "ss", $identifier, $identifier);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $attempt = mysqli_fetch_assoc($result);
        $current_time = time();

        // Permanent lock (24 hours)
        if ($attempt['permanent_lock_until'] !== null) {
            $permanent_lock_until = strtotime($attempt['permanent_lock_until']);
            if ($current_time < $permanent_lock_until) {
                $remaining = $permanent_lock_until - $current_time;
                mysqli_stmt_close($stmt);
                return ['locked' => true, 'remaining' => $remaining, 'attempts' => $attempt['attempts'], 'permanent' => true];
            } else {
                // Permanent lock expired -> full reset, user gets a clean slate
                $stmt2 = mysqli_prepare($conn, "UPDATE login_attempts SET attempts = 0, lock_until = NULL, permanent_lock_until = NULL WHERE (username = ? OR email = ?)");
                mysqli_stmt_bind_param($stmt2, "ss", $identifier, $identifier);
                mysqli_stmt_execute($stmt2);
                mysqli_stmt_close($stmt2);
                mysqli_stmt_close($stmt);
                return ['locked' => false];
            }
        }

        // Temporary lock (45 seconds, repeats every 4 attempts)
        if ($attempt['lock_until'] !== null) {
            $lock_until = strtotime($attempt['lock_until']);
            if ($current_time < $lock_until) {
                $remaining = $lock_until - $current_time;
                mysqli_stmt_close($stmt);
                return ['locked' => true, 'remaining' => $remaining, 'attempts' => $attempt['attempts'], 'permanent' => false];
            } else {
                // Temp lock expired -> clear the lock only, KEEP the attempt count
                $stmt2 = mysqli_prepare($conn, "UPDATE login_attempts SET lock_until = NULL WHERE (username = ? OR email = ?)");
                mysqli_stmt_bind_param($stmt2, "ss", $identifier, $identifier);
                mysqli_stmt_execute($stmt2);
                mysqli_stmt_close($stmt2);
                mysqli_stmt_close($stmt);
                return ['locked' => false];
            }
        }
    }
    mysqli_stmt_close($stmt);
    return ['locked' => false];
}

// Handle a failed login attempt.
// Locks happen at attempt 4 and attempt 8 (45 seconds each),
// and at attempt 12 (4+4+4) the account is blocked for 24 hours directly
// - there is no third 45s lock at 12, it goes straight to the 24h block.
function handleFailedAttempt($conn, $identifier, $username, $email) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM login_attempts WHERE username = ? OR email = ?");
    mysqli_stmt_bind_param($stmt, "ss", $identifier, $identifier);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $attempt = mysqli_fetch_assoc($result);
        $new_attempts = $attempt['attempts'] + 1;
    } else {
        $new_attempts = 1;
    }
    mysqli_stmt_close($stmt);

    if ($new_attempts >= MAX_ATTEMPTS_BEFORE_PERMANENT) {
        // 24 hour block
        $permanent_lock_until = date('Y-m-d H:i:s', time() + PERMANENT_LOCK_SECONDS);
        upsertAttempt($conn, $identifier, $username, $email, $new_attempts, null, $permanent_lock_until);
        return ['locked' => true, 'remaining' => PERMANENT_LOCK_SECONDS, 'permanent' => true, 'attempts' => $new_attempts];
    } elseif ($new_attempts % TEMP_LOCK_EVERY === 0) {
        // 45 second lock (only reached for attempt 4 and attempt 8;
        // attempt 12 is already caught by the permanent-block branch above)
        $lock_until = date('Y-m-d H:i:s', time() + TEMP_LOCK_SECONDS);
        upsertAttempt($conn, $identifier, $username, $email, $new_attempts, $lock_until, null);
        return ['locked' => true, 'remaining' => TEMP_LOCK_SECONDS, 'permanent' => false, 'attempts' => $new_attempts];
    } else {
        upsertAttempt($conn, $identifier, $username, $email, $new_attempts, null, null);
        return ['locked' => false, 'attempts' => $new_attempts];
    }
}

// Insert a new attempts row or update the existing one
function upsertAttempt($conn, $identifier, $username, $email, $attempts, $lock_until, $permanent_lock_until) {
    $exists = mysqli_prepare($conn, "SELECT id FROM login_attempts WHERE username = ? OR email = ?");
    mysqli_stmt_bind_param($exists, "ss", $identifier, $identifier);
    mysqli_stmt_execute($exists);
    $res = mysqli_stmt_get_result($exists);

    if (mysqli_num_rows($res) > 0) {
        mysqli_stmt_close($exists);
        $stmt = mysqli_prepare($conn, "UPDATE login_attempts SET attempts = ?, lock_until = ?, permanent_lock_until = ? WHERE username = ? OR email = ?");
        mysqli_stmt_bind_param($stmt, "issss", $attempts, $lock_until, $permanent_lock_until, $identifier, $identifier);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    } else {
        mysqli_stmt_close($exists);
        $stmt = mysqli_prepare($conn, "INSERT INTO login_attempts (username, email, attempts, lock_until, permanent_lock_until) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssiss", $username, $email, $attempts, $lock_until, $permanent_lock_until);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

// Reset login attempts on successful login
function resetLoginAttempts($conn, $identifier) {
    $stmt = mysqli_prepare($conn, "DELETE FROM login_attempts WHERE username = ? OR email = ?");
    mysqli_stmt_bind_param($stmt, "ss", $identifier, $identifier);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// Sets the session vars used to render the lock screen / countdown,
// and builds the right message depending on lock type & attempts left.
function applyLockOrWarningSession($attemptResult) {
    if ($attemptResult['locked']) {
        if ($attemptResult['permanent']) {
            $_SESSION['login_error'] = "🚫 Too many failed attempts (12). Account is locked for 24 hours.";
            $_SESSION['lock_time'] = time() + $attemptResult['remaining'];
            $_SESSION['permanent_lock'] = true;
        } else {
            $_SESSION['login_error'] = "⚠️ Too many failed attempts. Account is temporarily locked for 45 seconds.";
            $_SESSION['lock_time'] = time() + $attemptResult['remaining'];
            $_SESSION['permanent_lock'] = false;
        }
        return;
    }

    unset($_SESSION['lock_time']);
    unset($_SESSION['permanent_lock']);

    $attempts = $attemptResult['attempts'];
    $remaining_attempts = MAX_ATTEMPTS_BEFORE_PERMANENT - $attempts;
    $attempts_until_next_lock = TEMP_LOCK_EVERY - ($attempts % TEMP_LOCK_EVERY);

    if ($remaining_attempts <= 4) {
        $_SESSION['login_error'] = "⚠️ Warning: Only <strong>{$remaining_attempts}</strong> attempt(s) left before your account is blocked for <strong>24 hours</strong>!";
    } else {
        $_SESSION['login_error'] = "Invalid credentials. <strong>{$remaining_attempts}</strong> attempts remaining before a 24-hour block. (Locks for 45s every {$attempts_until_next_lock} more failed attempt(s).)";
    }
}

// Check lock status for current session (e.g. on page reload while locked)
$lockStatus = null;
if (isset($_SESSION['lock_identifier'])) {
    $lockStatus = getLockStatus($conn, $_SESSION['lock_identifier']);
    // Keep session lock display in sync with the DB (covers reloads after expiry)
    if (!$lockStatus['locked']) {
        unset($_SESSION['lock_time']);
        unset($_SESSION['permanent_lock']);
    }
}

if (isset($_POST['login'])) {
    $username_email = trim(mysqli_real_escape_string($conn, $_POST['username_email']));
    $password = trim($_POST['password']);

    $_SESSION['lock_identifier'] = $username_email;

    // Check if account is already locked
    $lockStatus = getLockStatus($conn, $username_email);
    if ($lockStatus['locked']) {
        $remaining = ceil($lockStatus['remaining']);
        if ($lockStatus['permanent']) {
            $hours = floor($remaining / 3600);
            $minutes = floor(($remaining % 3600) / 60);
            $_SESSION['login_error'] = "🚫 Account is locked for 24 hours. Time remaining: <strong>{$hours}h {$minutes}m</strong>";
            $_SESSION['lock_time'] = time() + $remaining;
            $_SESSION['permanent_lock'] = true;
        } else {
            $_SESSION['login_error'] = "⚠️ Account is temporarily locked. Please wait <strong>{$remaining}</strong> seconds.";
            $_SESSION['lock_time'] = time() + $remaining;
            $_SESSION['permanent_lock'] = false;
        }
        header("Location: login.php");
        exit();
    }

    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username = ? OR email_id = ?");
    mysqli_stmt_bind_param($stmt, "ss", $username_email, $username_email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        if ($user['status'] == 'inactive') {
            $_SESSION['login_error'] = "⚠️ Your account has been deactivated. Please contact administrator.";
            mysqli_stmt_close($stmt);
            header("Location: login.php");
            exit();
        }

        if (md5($password) == $user['password_hash']) {
            // Successful login - reset attempts
            resetLoginAttempts($conn, $username_email);
            unset($_SESSION['lock_identifier']);
            unset($_SESSION['lock_time']);
            unset($_SESSION['permanent_lock']);

            $session_token = bin2hex(random_bytes(32));

            $stmt2 = mysqli_prepare($conn, "UPDATE users SET session_token = ? WHERE user_id = ?");
            mysqli_stmt_bind_param($stmt2, "si", $session_token, $user['user_id']);
            mysqli_stmt_execute($stmt2);
            mysqli_stmt_close($stmt2);

            $_SESSION['session_token'] = $session_token;
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email_id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['designation'] = $user['designation'];
            $_SESSION['profile_picture'] = $user['profile_picture'];
            $_SESSION['login_time'] = time();

            $employee_id = $user['employee_id'];
            $employee_name = $user['name'];
            $ip_address = $_SERVER['REMOTE_ADDR'];

            $sql = "INSERT INTO user_activity
                    (employee_id, employee_name, ip_address, activity_type, login_time, activity_details)
                    VALUES
                    (?, ?, ?, 'Login', NOW(), 'User logged in')";

            $stmt3 = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt3, "sss", $employee_id, $employee_name, $ip_address);
            mysqli_stmt_execute($stmt3);
            mysqli_stmt_close($stmt3);

            mysqli_stmt_close($stmt);

            if (strtolower($user['role']) == 'admin') {
                $_SESSION['admin_id'] = $user['user_id'];
                $_SESSION['admin_name'] = $user['name'];
                $_SESSION['admin_email'] = $user['email_id'];
                $_SESSION['admin_designation'] = $user['designation'];
                $_SESSION['admin_profile_picture'] = $user['profile_picture'];
                header("Location: admin_page.php");
                exit();
            } else {
                $_SESSION['employee_id'] = $user['user_id'];
                $_SESSION['employee_name'] = $user['name'];
                $_SESSION['employee_email'] = $user['email_id'];
                $_SESSION['employee_designation'] = $user['designation'];
                $_SESSION['employee_profile_picture'] = $user['profile_picture'];
                $_SESSION['employee_code'] = $user['employee_id'];
                header("Location: emp_page.php");
                exit();
            }
        } else {
            // Wrong password
            $attemptResult = handleFailedAttempt($conn, $username_email, $user['username'], $user['email_id']);
            applyLockOrWarningSession($attemptResult);
            mysqli_stmt_close($stmt);
            header("Location: login.php");
            exit();
        }
    } else {
        // User not found - still counts as a failed attempt against this identifier
        $attemptResult = handleFailedAttempt($conn, $username_email, $username_email, $username_email);
        applyLockOrWarningSession($attemptResult);
        header("Location: login.php");
        exit();
    }
}

// Get lock time for rendering/JS countdown, re-verified against the DB each load
$lockTime = $_SESSION['lock_time'] ?? null;
$isPermanentLock = $_SESSION['permanent_lock'] ?? false;
if ($lockTime && time() >= $lockTime) {
    unset($_SESSION['lock_time']);
    unset($_SESSION['permanent_lock']);
    $lockTime = null;
    $isPermanentLock = false;
}
$isLocked = $lockTime && time() < $lockTime;

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isLocked ? 'Account Locked - IMS' : 'Login - IMS'; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #0a0a0a 0%, #1a0a0a 50%, #2d0a0a 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 152, 0, 0.15), transparent);
            top: -100px;
            right: -100px;
        }

        body::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(245, 124, 0, 0.1), transparent);
            bottom: -100px;
            left: -100px;
        }

        .login-container {
            width: 100%;
            max-width: 450px;
            background: rgba(26, 10, 10, 0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 152, 0, 0.25);
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6), 0 0 80px rgba(255, 152, 0, 0.15);
            animation: slideUp 0.5s ease;
            position: relative;
            z-index: 1;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-header { text-align: center; margin-bottom: 30px; }

        .login-logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 15px;
            background: linear-gradient(135deg, #ff9800, #f57c00);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            box-shadow: 0 8px 25px rgba(255, 152, 0, 0.3);
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .login-header h2 {
            color: white;
            font-size: 28px;
            margin-bottom: 5px;
            background: linear-gradient(135deg, #ffb74d, #f57c00);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .login-header p { color: #cbd5e1; font-size: 14px; }

        .form-group { margin-bottom: 20px; }

        .form-group label {
            display: block;
            color: #cbd5e1;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 14px 18px;
            background: rgba(0, 0, 0, 0.4);
            border: 2px solid rgba(255, 152, 0, 0.2);
            border-radius: 12px;
            color: white;
            font-size: 16px;
            transition: all 0.3s ease;
            outline: none;
        }

        .form-group input:focus {
            border-color: #f57c00;
            background: rgba(0, 0, 0, 0.6);
            box-shadow: 0 0 20px rgba(255, 152, 0, 0.2);
        }

        .form-group input::placeholder { color: #64748b; }

        .login-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #ff9800, #f57c00);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(255, 152, 0, 0.4);
            position: relative;
            overflow: hidden;
        }

        .login-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.6s ease;
        }

        .login-btn:hover::before { left: 100%; }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 40px rgba(255, 152, 0, 0.6);
            background: linear-gradient(135deg, #f57c00, #e65100);
        }

        .login-btn:active { transform: translateY(0px); }

        .error-message {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.4);
            color: #f87171;
            padding: 12px 18px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
            animation: shake 0.5s ease;
        }

        .error-message strong { color: #fca5a5; }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .success-message {
            background: rgba(34, 197, 94, 0.15);
            border: 1px solid rgba(34, 197, 94, 0.4);
            color: #4ade80;
            padding: 12px 18px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .back-home { text-align: center; margin-top: 20px; }

        .back-home a {
            color: #ffb74d;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .back-home a:hover { color: #f57c00; transform: translateX(-3px); }

        .login-footer {
            text-align: center;
            margin-top: 20px;
            color: #64748b;
            font-size: 12px;
        }

        /* ===== Blank lockout page ===== */
        .lock-page {
            width: 100%;
            max-width: 420px;
            background: rgba(26, 10, 10, 0.9);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(239, 68, 68, 0.35);
            border-radius: 24px;
            padding: 50px 35px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6), 0 0 80px rgba(239, 68, 68, 0.15);
            text-align: center;
            animation: slideUp 0.5s ease;
            position: relative;
            z-index: 1;
        }

        .lock-page.temp {
            border-color: rgba(255, 152, 0, 0.35);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6), 0 0 80px rgba(255, 152, 0, 0.15);
        }

        .lock-page .lock-icon {
            font-size: 56px;
            display: block;
            margin-bottom: 15px;
            animation: pulse 2s ease-in-out infinite;
        }

        .lock-page h2 {
            color: #f87171;
            font-size: 24px;
            margin-bottom: 10px;
        }

        .lock-page.temp h2 { color: #ffb74d; }

        .lock-page .countdown {
            color: #ef4444;
            font-size: 44px;
            font-weight: 700;
            display: block;
            margin: 20px 0;
            letter-spacing: 1px;
        }

        .lock-page.temp .countdown { color: #f57c00; font-size: 52px; }

        .lock-page .message {
            color: #cbd5e1;
            font-size: 14px;
            line-height: 1.5;
            margin-bottom: 10px;
        }

        @media (max-width: 480px) {
            .login-container { padding: 30px 20px; }
            .login-header h2 { font-size: 24px; }
            .login-logo { width: 60px; height: 60px; font-size: 30px; }
            .lock-page { padding: 40px 20px; }
            .lock-page .countdown { font-size: 34px; }
            .lock-page.temp .countdown { font-size: 40px; }
        }
    </style>
</head>

<body>
<?php if ($isLocked): ?>
    <!-- ===================== BLANK LOCKOUT PAGE ===================== -->
    <div class="lock-page <?php echo $isPermanentLock ? '' : 'temp'; ?>" id="lockPage">
        <span class="lock-icon"><?php echo $isPermanentLock ? '🚫' : '🔒'; ?></span>
        <h2><?php echo $isPermanentLock ? 'Account Locked - 24 Hours' : 'Account Temporarily Locked'; ?></h2>
        <span class="countdown" id="countdown">
            <?php echo $isPermanentLock ? gmdate('H\h i\m s\s', max(0, $lockTime - time())) : (max(0, $lockTime - time())); ?>
        </span>
        <div class="message">
            <?php echo $isPermanentLock
                ? 'Too many failed login attempts (12). Please wait 24 hours before trying again.'
                : 'Too many failed login attempts. Please wait for the timer to finish.'; ?>
        </div>
    </div>

    <script>
        (function () {
            const lockTime = <?php echo json_encode($lockTime); ?>;
            const isPermanent = <?php echo $isPermanentLock ? 'true' : 'false'; ?>;
            const countdownEl = document.getElementById('countdown');

            function formatPermanent(remaining) {
                const h = Math.floor(remaining / 3600);
                const m = Math.floor((remaining % 3600) / 60);
                const s = remaining % 60;
                return `${h}h ${m}m ${s}s`;
            }

            const interval = setInterval(function () {
                const now = Math.floor(Date.now() / 1000);
                const remaining = lockTime - now;

                if (remaining <= 0) {
                    clearInterval(interval);
                    // Reload to bring back the login form once the lock expires
                    window.location.href = 'login.php';
                    return;
                }

                countdownEl.textContent = isPermanent ? formatPermanent(remaining) : remaining;
            }, 1000);
        })();
    </script>
<?php else: ?>
    <!-- ===================== NORMAL LOGIN FORM ===================== -->
    <div class="login-container">
        <div class="login-header">
            <div class="login-logo">📦</div>
            <h2>Welcome Back</h2>
            <p>Sign in to your account</p>
        </div>

        <?php
        echo showError($loginError);

        if (isset($_GET['logged_out'])) {
            echo '<div class="success-message">✅ You have been logged out successfully.</div>';
        }
        if (isset($_GET['msg']) && $_GET['msg'] == 'another_login') {
            echo '<div class="error-message">⚠️ Your account was logged in from another device.</div>';
        }
        ?>

        <form method="POST" action="" id="loginForm">
            <div class="form-group">
                <label for="username_email">Username or Email</label>
                <input type="text" id="username_email" name="username_email" placeholder="Enter your username or email" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>

            <button type="submit" name="login" class="login-btn" id="loginBtn">🔐 Sign In</button>
        </form>

        <div class="back-home">
            <a href="index.php">← Back to Home</a>
        </div>

        <div class="login-footer">
            &copy; <?php echo date('Y'); ?> Inventory Management System
        </div>
    </div>
<?php endif; ?>
</body>

</html>