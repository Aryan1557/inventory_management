<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include 'db_connection.php';

// ========== LOGOUT HANDLER ==========
if (isset($_GET['logout'])) {
    $user_id = $_SESSION['user_id'];
    $employee_id = $_SESSION['employee_id'] ?? $_SESSION['admin_id'] ?? null;
    $employee_name = $_SESSION['name'] ?? $_SESSION['employee_name'] ?? $_SESSION['admin_name'] ?? null;
    $ip_address = $_SERVER['REMOTE_ADDR'];
    
    // Insert logout activity BEFORE destroying session
    if ($employee_id && $employee_name) {
        $sql = "INSERT INTO user_activity 
                (employee_id, employee_name, ip_address, activity_type, logout_time, activity_details) 
                VALUES 
                ('$employee_id', '$employee_name', '$ip_address', 'Logout', NOW(), 'User logged out')";
        
        mysqli_query($conn, $sql) or die(mysqli_error($conn));
    }
    
    // Update session token in database
    mysqli_query(
        $conn,
        "UPDATE users SET session_token=NULL WHERE user_id='$user_id'"
    );
    
    session_unset();
    session_destroy();
    header("Location: login.php?logged_out=1");
    exit();
}

// ========== LOGIN HANDLER ==========
$loginError = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);

function showError($error)
{
    return !empty($error) ? "<p class='error-message'>$error</p>" : '';
}

if (isset($_POST['login'])) {
    $username_email = trim($_POST['username_email']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email_id = ?");
    $stmt->bind_param("ss", $username_email, $username_email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // ✅ CHECK ACCOUNT STATUS - Added this feature
        if ($user['status'] == 'inactive') {
            $_SESSION['login_error'] = "⚠️ Your account has been deactivated. Please contact administrator.";
            header("Location: login.php");
            exit();
        }

        if (md5($password) == $user['password_hash']) {
            // Generate unique session token
            $session_token = bin2hex(random_bytes(32));

            // Save token in database
            mysqli_query(
                $conn,
                "UPDATE users SET session_token='$session_token' WHERE user_id='{$user['user_id']}'"
            );

            // Store token in PHP session
            $_SESSION['session_token'] = $session_token;
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email_id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['designation'] = $user['designation'];
            $_SESSION['profile_picture'] = $user['profile_picture'];
            $_SESSION['login_time'] = time();

            // Insert login record
            $employee_id = $user['employee_id'];
            $employee_name = $user['name'];
            $ip_address = $_SERVER['REMOTE_ADDR'];
            $user_role = $user['role'];

            $sql = "INSERT INTO user_activity 
                    (employee_id, employee_name, ip_address, activity_type, login_time, activity_details) 
                    VALUES 
                    ('$employee_id', '$employee_name', '$ip_address', 'Login', NOW(), 'User logged in')";

            mysqli_query($conn, $sql) or die(mysqli_error($conn));

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
        }
    }

    $_SESSION['login_error'] = "Invalid Username/Email or Password";
    header("Location: login.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - IMS</title>
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
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

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

        .login-header p {
            color: #cbd5e1;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

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

        .form-group input::placeholder {
            color: #64748b;
        }

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

        .login-btn:hover::before {
            left: 100%;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 40px rgba(255, 152, 0, 0.6);
            background: linear-gradient(135deg, #f57c00, #e65100);
        }

        .login-btn:active {
            transform: translateY(0px);
        }

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

        .back-home {
            text-align: center;
            margin-top: 20px;
        }

        .back-home a {
            color: #ffb74d;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .back-home a:hover {
            color: #f57c00;
            transform: translateX(-3px);
        }

        .login-footer {
            text-align: center;
            margin-top: 20px;
            color: #64748b;
            font-size: 12px;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #0a0a0a;
        }
        ::-webkit-scrollbar-thumb {
            background: #f57c00;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #e65100;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 30px 20px;
            }
            .login-header h2 {
                font-size: 24px;
            }
            .login-logo {
                width: 60px;
                height: 60px;
                font-size: 30px;
            }
        }
    </style>
</head>

<body>
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
            echo '<div class="error-message">
            ⚠️ Your account was logged in from another device.
          </div>';
        }
        ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username_email">Username or Email</label>
                <input type="text" id="username_email" name="username_email" placeholder="Enter your username or email" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>

            <button type="submit" name="login" class="login-btn">🔐 Sign In</button>
        </form>

        <div class="back-home">
            <a href="index.php">← Back to Home</a>
        </div>

        <div class="login-footer">
            &copy; <?php echo date('Y'); ?> Inventory Management System
        </div>
    </div>
</body>

</html>