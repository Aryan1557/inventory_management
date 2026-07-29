<?php
session_start();
include "db_connection.php";
include 'session_check.php';

include "sidebar.php";

// ==================== CREATE TABLES ====================
$create_tables = [
    "CREATE TABLE IF NOT EXISTS email_folders (
        id INT PRIMARY KEY AUTO_INCREMENT,
        email_address VARCHAR(255) NOT NULL,
        message_uid VARCHAR(255),
        from_email VARCHAR(255),
        from_name VARCHAR(255),
        to_email VARCHAR(255),
        subject VARCHAR(500),
        body LONGTEXT,
        folder ENUM('inbox','starred','snoozed','important','spam','sent') DEFAULT 'inbox',
        is_read TINYINT(1) DEFAULT 0,
        is_starred TINYINT(1) DEFAULT 0,
        has_attachment TINYINT(1) DEFAULT 0,
        message_date DATETIME,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS email_drafts (
        id INT PRIMARY KEY AUTO_INCREMENT,
        provider VARCHAR(50),
        from_email VARCHAR(255),
        to_email VARCHAR(255),
        subject VARCHAR(500),
        body TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )"
];

foreach ($create_tables as $sql) {
    @mysqli_query($conn, $sql);
}

// ==================== CONFIGURATION ====================
$email_config = [
    'gmail' => [
        'smtp_host' => 'smtp.gmail.com',
        'smtp_port' => 587,
        'imap_host' => '{imap.gmail.com:993/imap/ssl}INBOX',
        'name' => 'Gmail',
        'icon' => 'G',
        'requires_app_password' => true,
        'color' => '#ea4335'
    ],
    'rediff' => [
        'smtp_host' => 'smtp.rediffmail.com',
        'smtp_port' => 587,
        'imap_host' => '{imap.rediffmail.com:993/imap/ssl}INBOX',
        'name' => 'Rediff Mail',
        'icon' => 'R',
        'requires_app_password' => false,
        'color' => '#0066cc'
    ]
];

// Initialize session
if (!isset($_SESSION['email_provider'])) $_SESSION['email_provider'] = '';
if (!isset($_SESSION['email_address'])) $_SESSION['email_address'] = '';
if (!isset($_SESSION['email_password'])) $_SESSION['email_password'] = '';
if (!isset($_SESSION['current_folder'])) $_SESSION['current_folder'] = 'inbox';

$notification = '';
$notification_type = '';
$redirect = false;

// ==================== LOGIN ====================
if (isset($_POST['select_provider'])) {
    $provider = $_POST['provider'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($provider) && !empty($email) && !empty($password)) {
        $_SESSION['email_provider'] = $provider;
        $_SESSION['email_address'] = $email;
        $_SESSION['email_password'] = $password;
        $_SESSION['current_folder'] = 'inbox';
        $notification = 'Connected! Click "Fetch Inbox" to download emails.';
        $notification_type = 'success';
        $redirect = true;
    } else {
        $notification = 'Please fill all fields!';
        $notification_type = 'error';
    }
}

// ==================== LOGOUT ====================
if (isset($_GET['logout_email'])) {
    $_SESSION['email_provider'] = '';
    $_SESSION['email_address'] = '';
    $_SESSION['email_password'] = '';
    $_SESSION['current_folder'] = 'inbox';
    $redirect = true;
}

// ==================== FOLDER SWITCH ====================
if (isset($_GET['folder']) && !isset($_POST['send_email']) && !isset($_POST['select_provider']) && !isset($_GET['fetch']) && !isset($_GET['logout_email'])) {
    $allowed = ['inbox', 'starred', 'snoozed', 'sent', 'drafts', 'important', 'spam'];
    if (in_array($_GET['folder'], $allowed)) {
        $_SESSION['current_folder'] = $_GET['folder'];
    }
}

// ==================== FETCH INBOX VIA IMAP ====================
if (isset($_GET['fetch']) && $_GET['fetch'] == 'inbox' && !empty($_SESSION['email_provider'])) {
    $provider = $_SESSION['email_provider'];
    $email = $_SESSION['email_address'];
    $password = $_SESSION['email_password'];
    $imap_host = $email_config[$provider]['imap_host'];
    $config = $email_config[$provider];

    if (!function_exists('imap_open')) {
        $notification = '❌ IMAP extension not enabled!<br>Open php.ini and uncomment: extension=imap';
        $notification_type = 'error';
        $redirect = true;
    } else {
        $inbox = false;

        if ($provider == 'gmail') {
            $inbox = @imap_open($imap_host, $email, $password);
            if (!$inbox) {
                $inbox = @imap_open($imap_host, $email, $password, 0, 1, ['DISABLE_AUTHENTICATOR' => 'PLAIN']);
            }
            if (!$inbox) {
                $alt_host = '{imap.gmail.com:993/imap/ssl/novalidate-cert}INBOX';
                $inbox = @imap_open($alt_host, $email, $password);
            }
        } else {
            $inbox = @imap_open($imap_host, $email, $password);
        }

        if (!$inbox) {
            $error = imap_last_error();
            $help_text = '';
            if ($provider == 'gmail') {
                $help_text = '<br><br><b>For Gmail:</b><br>
                1. Enable 2-Step Verification: <a href="https://myaccount.google.com/security" target="_blank" style="color:var(--orange-primary);">Google Security</a><br>
                2. Generate App Password: <a href="https://myaccount.google.com/apppasswords" target="_blank" style="color:var(--orange-primary);">App Passwords</a><br>
                3. Use the 16-character App Password here<br>
                4. Enable IMAP in Gmail Settings → Forwarding and POP/IMAP';
            }
            $notification = '❌ IMAP connection failed!<br><small>' . $error . '</small>' . $help_text;
            $notification_type = 'error';
            $redirect = true;
        } else {
            $email_count = imap_num_msg($inbox);
            $fetch_count = 0;
            $start = max(1, $email_count - 49);

            for ($i = $email_count; $i >= $start; $i--) {
                $header = @imap_headerinfo($inbox, $i);
                if ($header && isset($header->from[0])) {
                    $message_uid = imap_uid($inbox, $i);
                    $from_email = $header->from[0]->mailbox . '@' . $header->from[0]->host;
                    $from_name = isset($header->from[0]->personal) ? imap_utf8($header->from[0]->personal) : $from_email;
                    $subject = isset($header->subject) ? imap_utf8($header->subject) : '(no subject)';
                    $date = date('Y-m-d H:i:s', strtotime($header->date));
                    $unseen = isset($header->Unseen) && $header->Unseen == 'U';

                    $esc_email = mysqli_real_escape_string($conn, $email);
                    $esc_uid = mysqli_real_escape_string($conn, (string)$message_uid);
                    $check = mysqli_query($conn, "SELECT id FROM email_folders WHERE email_address='$esc_email' AND message_uid='$esc_uid' LIMIT 1");

                    if (!$check || mysqli_num_rows($check) == 0) {
                        $body = '';
                        $structure = @imap_fetchstructure($inbox, $i);
                        if ($structure) {
                            if ($structure->type == 0) {
                                $body = imap_fetchbody($inbox, $i, 1);
                                if ($structure->encoding == 3) $body = base64_decode($body);
                                elseif ($structure->encoding == 4) $body = quoted_printable_decode($body);
                            } elseif ($structure->type == 1 && isset($structure->parts)) {
                                foreach ($structure->parts as $part_num => $part) {
                                    if ($part->type == 0) {
                                        $part_body = imap_fetchbody($inbox, $i, $part_num + 1);
                                        if ($part->encoding == 3) $part_body = base64_decode($part_body);
                                        elseif ($part->encoding == 4) $part_body = quoted_printable_decode($part_body);
                                        $body .= $part_body;
                                    }
                                }
                            }
                        }

                        $body = trim(strip_tags($body));
                        if (strlen($body) > 10000) $body = substr($body, 0, 10000) . '...';
                        if (empty($body)) $body = '(No text content)';

                        $esc_from_email = mysqli_real_escape_string($conn, $from_email);
                        $esc_from_name = mysqli_real_escape_string($conn, $from_name);
                        $esc_subject = mysqli_real_escape_string($conn, $subject);
                        $esc_body = mysqli_real_escape_string($conn, $body);
                        $is_read = $unseen ? 0 : 1;

                        mysqli_query($conn, "INSERT INTO email_folders 
                            (email_address, message_uid, from_email, from_name, subject, body, folder, is_read, message_date, created_at) 
                            VALUES ('$esc_email', '$esc_uid', '$esc_from_email', '$esc_from_name', '$esc_subject', '$esc_body', 'inbox', $is_read, '$date', NOW())");
                        $fetch_count++;
                    }
                }
            }
            imap_close($inbox);

            if ($fetch_count > 0) {
                $notification = "✅ Fetched $fetch_count new emails from " . $config['name'] . "!";
                $notification_type = 'success';
            } else {
                $notification = 'No new emails. Inbox is up to date.';
                $notification_type = 'success';
            }
            $redirect = true;
        }
    }
}

// ==================== SEND EMAIL ====================
if (isset($_POST['send_email']) && !empty($_SESSION['email_provider'])) {
    $provider = $_SESSION['email_provider'];
    $from_email = $_SESSION['email_address'];
    $from_password = $_SESSION['email_password'];
    $to_email = $_POST['to_email'] ?? '';
    $subject = $_POST['subject'] ?? '(no subject)';
    $message_body = $_POST['message_body'] ?? '';
    $is_draft = isset($_POST['save_draft']) && $_POST['save_draft'] == '1';
    $draft_id = isset($_POST['draft_id']) ? (int)$_POST['draft_id'] : 0;

    if (!$is_draft && !empty($to_email)) {
        $sent = false;
        $phpmailer_path = 'PHPMailer/PHPMailer/src/';

        if (file_exists($phpmailer_path . 'PHPMailer.php')) {
            require_once $phpmailer_path . 'PHPMailer.php';
            require_once $phpmailer_path . 'SMTP.php';
            require_once $phpmailer_path . 'Exception.php';

            try {
                $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = $email_config[$provider]['smtp_host'];
                $mail->SMTPAuth = true;
                $mail->Username = $from_email;
                $mail->Password = $from_password;
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;
                $mail->setFrom($from_email, $_SESSION['admin_name'] ?? 'User');
                $mail->addAddress($to_email);
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body = $message_body;
                $mail->send();
                $sent = true;
            } catch (Exception $e) {}
        }

        if (!$sent) {
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $headers .= "From: " . ($_SESSION['admin_name'] ?? 'User') . " <$from_email>\r\n";
            $sent = @mail($to_email, $subject, $message_body, $headers);
        }

        $esc_from = mysqli_real_escape_string($conn, $from_email);
        $esc_to = mysqli_real_escape_string($conn, $to_email);
        $esc_subject = mysqli_real_escape_string($conn, $subject);
        $esc_body = mysqli_real_escape_string($conn, $message_body);

        mysqli_query($conn, "INSERT INTO email_folders 
            (email_address, from_email, to_email, subject, body, folder, is_read, message_date) 
            VALUES ('$esc_from', '$esc_from', '$esc_to', '$esc_subject', '$esc_body', 'sent', 1, NOW())");

        if ($draft_id > 0) {
            mysqli_query($conn, "DELETE FROM email_drafts WHERE id = $draft_id");
        }

        $_SESSION['current_folder'] = 'sent';
        $notification = $sent ? '✅ Email sent!' : '⚠️ Saved to Sent folder locally';
        $notification_type = $sent ? 'success' : 'warning';
        $redirect = true;
    } elseif ($is_draft) {
        $esc_from = mysqli_real_escape_string($conn, $from_email);
        $esc_to = mysqli_real_escape_string($conn, $to_email);
        $esc_subject = mysqli_real_escape_string($conn, $subject);
        $esc_body = mysqli_real_escape_string($conn, $message_body);
        $esc_provider = mysqli_real_escape_string($conn, $provider);

        if ($draft_id > 0) {
            mysqli_query($conn, "UPDATE email_drafts SET to_email='$esc_to', subject='$esc_subject', body='$esc_body' WHERE id=$draft_id");
        } else {
            mysqli_query($conn, "INSERT INTO email_drafts (provider, from_email, to_email, subject, body) 
                VALUES ('$esc_provider', '$esc_from', '$esc_to', '$esc_subject', '$esc_body')");
        }

        $_SESSION['current_folder'] = 'drafts';
        $notification = 'Draft saved!';
        $notification_type = 'success';
        $redirect = true;
    }
}

// ==================== REDIRECT ====================
if ($redirect) {
    header("Location: email.php");
    exit;
}

// ==================== FETCH DATA ====================
$emails = [];
$drafts = [];
$inbox_count = 0;
$drafts_count = 0;

if (!empty($_SESSION['email_provider'])) {
    $folder = $_SESSION['current_folder'];
    $email_addr = mysqli_real_escape_string($conn, $_SESSION['email_address']);

    $dr = mysqli_query($conn, "SELECT * FROM email_drafts WHERE from_email = '$email_addr' ORDER BY created_at DESC");
    if ($dr) {
        while ($row = mysqli_fetch_assoc($dr)) {
            $drafts[] = $row;
        }
        $drafts_count = count($drafts);
    }

    $cr = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM email_folders WHERE email_address = '$email_addr' AND folder = 'inbox'");
    if ($cr) {
        $row = mysqli_fetch_assoc($cr);
        $inbox_count = $row['cnt'] ?? 0;
    }

    if ($folder != 'drafts') {
        $folder_safe = mysqli_real_escape_string($conn, $folder);
        if ($folder == 'starred') {
            $qr = "SELECT * FROM email_folders WHERE email_address = '$email_addr' AND is_starred = 1 ORDER BY created_at DESC LIMIT 50";
        } else {
            $qr = "SELECT * FROM email_folders WHERE email_address = '$email_addr' AND folder = '$folder_safe' ORDER BY created_at DESC LIMIT 50";
        }
        $result = mysqli_query($conn, $qr);
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $emails[] = $row;
            }
        }
    }
}

$current_folder = $_SESSION['current_folder'];
$provider = $_SESSION['email_provider'];
$is_logged_in = !empty($provider);
$imap_available = function_exists('imap_open');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email System</title>
    <style>
        :root {
            --bg: #faf8f6;
            --text: #2c241c;
            --card: #ffffff;
            --card-border: #f0e8e0;
            --secondary: #7a6a5a;
            --sidebar-width: 280px;
            --sidebar-collapsed: 85px;
            
            --orange-primary: #f57c00;
            --orange-light: #ffb74d;
            --orange-dark: #e65100;
            --orange-gradient-start: #ff9800;
            --orange-gradient-end: #f57c00;
            --orange-subtle: rgba(255, 152, 0, 0.08);
            --orange-shadow: rgba(245, 124, 0, 0.15);
            --input-bg: #f8f6f4;
            --input-border: #e8e0d8;
            --table-hover: rgba(255, 152, 0, 0.05);
            --table-stripe: rgba(255, 152, 0, 0.03);
        }

        body.dark {
            --bg: #12100e;
            --text: #f0e8e0;
            --card: #1d1815;
            --card-border: #3a322a;
            --secondary: #a89888;
            --input-bg: #2a2420;
            --input-border: #3a322a;
            --table-hover: rgba(255, 152, 0, 0.08);
            --table-stripe: rgba(255, 152, 0, 0.05);
            
            --orange-primary: #ffa726;
            --orange-light: #ffcc80;
            --orange-dark: #f57c00;
            --orange-gradient-start: #ff9800;
            --orange-gradient-end: #e65100;
            --orange-subtle: rgba(255, 152, 0, 0.12);
            --orange-shadow: rgba(255, 152, 0, 0.2);
            
            background-image:
                radial-gradient(circle at top right,
                    rgba(255, 140, 0, .08),
                    transparent 35%),
                radial-gradient(circle at bottom left,
                    rgba(255, 100, 0, .06),
                    transparent 35%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
        }

        body {
            background: var(--bg);
            color: var(--text);
            transition: all .35s ease;
            background-image:
                radial-gradient(circle at top right,
                    rgba(255, 140, 0, .05),
                    transparent 30%),
                radial-gradient(circle at bottom left,
                    rgba(255, 100, 0, .04),
                    transparent 30%);
            min-height: 100vh;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 25px;
            transition: all .4s ease;
            min-height: 100vh;
        }

        .main-content.expanded {
            margin-left: var(--sidebar-collapsed);
        }

        .provider-selection {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 80vh;
        }

        .provider-card {
            background: var(--card);
            border-radius: 24px;
            padding: 40px;
            border: 2px solid var(--card-border);
            box-shadow: 0 20px 60px var(--orange-shadow);
            max-width: 550px;
            width: 100%;
            transition: all .35s ease;
        }

        .provider-card:hover {
            box-shadow: 0 25px 70px var(--orange-shadow);
        }

        .provider-title {
            text-align: center;
            font-size: 28px;
            margin-bottom: 10px;
            background: linear-gradient(135deg, var(--orange-gradient-start), var(--orange-primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 700;
        }

        .provider-subtitle {
            text-align: center;
            color: var(--secondary);
            margin-bottom: 25px;
            font-size: 14px;
        }

        .provider-options {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
        }

        .provider-option {
            flex: 1;
            padding: 20px;
            border: 2px solid var(--card-border);
            border-radius: 16px;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s ease;
        }

        .provider-option:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px var(--orange-shadow);
        }

        .provider-option.selected {
            border-color: var(--orange-primary);
            background: var(--orange-subtle);
        }

        .provider-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 700;
            margin: 0 auto 10px;
            color: white;
        }

        .gmail-icon {
            background: linear-gradient(135deg, #ea4335, #fbbc04);
        }

        .rediff-icon {
            background: linear-gradient(135deg, #0066cc, #0099ff);
        }

        .provider-name {
            font-weight: 600;
            font-size: 15px;
            color: var(--text);
        }

        .provider-hint {
            font-size: 11px;
            color: var(--secondary);
            margin-top: 5px;
        }

        .form-input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--input-border);
            border-radius: 12px;
            font-size: 14px;
            background: var(--input-bg);
            color: var(--text);
            outline: none;
            margin-bottom: 12px;
            transition: all .35s ease;
        }

        .form-input:focus {
            border-color: var(--orange-primary);
            box-shadow: 0 0 0 4px var(--orange-subtle);
            background: var(--card);
        }

        .form-input::placeholder {
            color: var(--secondary);
            opacity: 0.6;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--orange-gradient-start), var(--orange-primary));
            color: white;
            width: 100%;
            box-shadow: 0 8px 20px var(--orange-shadow);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px var(--orange-shadow);
            background: linear-gradient(135deg, var(--orange-primary), var(--orange-dark));
        }

        .email-client {
            display: flex;
            gap: 20px;
            height: calc(100vh - 100px);
        }

        .email-sidebar {
            width: 260px;
            background: var(--card);
            border-radius: 20px;
            padding: 20px;
            border: 2px solid var(--card-border);
            overflow-y: auto;
            flex-shrink: 0;
            transition: all .35s ease;
        }

        .email-sidebar:hover {
            box-shadow: 0 10px 25px var(--orange-shadow);
        }

        .email-main {
            flex: 1;
            background: var(--card);
            border-radius: 20px;
            border: 2px solid var(--card-border);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            transition: all .35s ease;
        }

        .email-main:hover {
            box-shadow: 0 10px 25px var(--orange-shadow);
        }

        .compose-btn,
        .fetch-btn {
            width: 100%;
            padding: 14px;
            color: white;
            border: none;
            border-radius: 16px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            margin-bottom: 8px;
            transition: all 0.3s ease;
            display: block;
            text-align: center;
            text-decoration: none;
        }

        .compose-btn {
            background: linear-gradient(135deg, var(--orange-gradient-start), var(--orange-primary));
            box-shadow: 0 8px 20px var(--orange-shadow);
        }

        .fetch-btn {
            background: linear-gradient(135deg, #10b981, #059669);
            margin-bottom: 18px;
        }

        .compose-btn:hover,
        .fetch-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px var(--orange-shadow);
        }

        .folder-list {
            list-style: none;
        }

        .folder-item {
            padding: 12px 16px;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
            font-size: 14px;
            color: var(--secondary);
            text-decoration: none;
        }

        .folder-item:hover {
            background: var(--orange-subtle);
            color: var(--text);
        }

        .folder-item.active {
            background: var(--orange-subtle);
            color: var(--orange-primary);
            font-weight: 600;
        }

        .folder-icon {
            font-size: 18px;
            width: 24px;
            text-align: center;
        }

        .folder-badge {
            margin-left: auto;
            background: var(--orange-primary);
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }

        .email-header {
            padding: 20px;
            border-bottom: 2px solid var(--card-border);
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .email-header strong {
            color: var(--orange-primary);
            font-size: 18px;
            text-transform: capitalize;
        }

        .email-list {
            flex: 1;
            overflow-y: auto;
        }

        .email-list::-webkit-scrollbar {
            width: 6px;
        }

        .email-list::-webkit-scrollbar-track {
            background: var(--bg);
        }

        .email-list::-webkit-scrollbar-thumb {
            background: var(--orange-light);
            border-radius: 10px;
        }

        .email-list-item {
            padding: 15px 20px;
            border-bottom: 1px solid var(--card-border);
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .email-list-item:hover {
            background: var(--orange-subtle);
        }

        .email-list-item.unread {
            background: rgba(255, 152, 0, 0.06);
            font-weight: 600;
        }

        .email-sender {
            font-weight: 500;
            min-width: 150px;
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            color: var(--text);
        }

        .email-content {
            flex: 1;
            overflow: hidden;
        }

        .email-subject {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: var(--text);
        }

        .email-preview {
            color: var(--secondary);
            font-size: 13px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-top: 3px;
        }

        .email-time {
            color: var(--secondary);
            font-size: 12px;
            white-space: nowrap;
        }

        .no-emails {
            text-align: center;
            padding: 60px 20px;
            color: var(--secondary);
        }

        .no-emails .icon {
            font-size: 60px;
            margin-bottom: 20px;
        }

        .no-emails h3 {
            color: var(--text);
            margin-bottom: 10px;
        }

        .compose-modal {
            display: none;
            position: fixed;
            bottom: 0;
            right: 50px;
            width: 600px;
            max-height: 600px;
            background: var(--card);
            border-radius: 16px 16px 0 0;
            box-shadow: 0 -5px 30px var(--orange-shadow);
            z-index: 1000;
            border: 2px solid var(--card-border);
        }

        .compose-header {
            padding: 15px 20px;
            background: linear-gradient(135deg, var(--orange-gradient-start), var(--orange-primary));
            border-radius: 16px 16px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
        }

        .compose-header strong {
            color: white;
            font-size: 16px;
        }

        .compose-header span {
            cursor: pointer;
            font-size: 20px;
            opacity: 0.8;
            transition: opacity 0.2s ease;
        }

        .compose-header span:hover {
            opacity: 1;
        }

        .compose-body {
            padding: 20px;
            max-height: 400px;
            overflow-y: auto;
        }

        .compose-input {
            width: 100%;
            padding: 10px;
            border: none;
            border-bottom: 2px solid var(--card-border);
            outline: none;
            font-size: 14px;
            background: transparent;
            color: var(--text);
            transition: all .35s ease;
        }

        .compose-input:focus {
            border-bottom-color: var(--orange-primary);
        }

        .compose-textarea {
            width: 100%;
            min-height: 200px;
            padding: 10px;
            border: none;
            outline: none;
            resize: vertical;
            font-size: 14px;
            background: transparent;
            color: var(--text);
            font-family: inherit;
        }

        .compose-actions {
            padding: 15px 20px;
            display: flex;
            gap: 10px;
            border-top: 1px solid var(--card-border);
            flex-wrap: wrap;
        }

        .btn-send {
            background: linear-gradient(135deg, var(--orange-gradient-start), var(--orange-primary));
            color: white;
            box-shadow: 0 4px 12px var(--orange-shadow);
        }

        .btn-send:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px var(--orange-shadow);
        }

        .btn-draft {
            background: var(--input-border);
            color: var(--text);
        }

        .btn-draft:hover {
            background: var(--secondary);
            color: white;
        }

        .btn-discard {
            background: transparent;
            color: var(--secondary);
        }

        .btn-discard:hover {
            color: var(--text);
            background: var(--orange-subtle);
        }

        .provider-info {
            padding: 15px;
            background: var(--orange-subtle);
            border-radius: 12px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            border: 1px solid rgba(255, 152, 0, 0.2);
        }

        .provider-info strong {
            color: var(--orange-primary);
        }

        .logout-link {
            margin-left: auto;
            color: var(--orange-primary);
            text-decoration: none;
            font-weight: 600;
            font-size: 12px;
            transition: all .3s ease;
        }

        .logout-link:hover {
            color: var(--orange-dark);
            transform: scale(1.05);
        }

        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 99999;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .toast {
            min-width: 320px;
            padding: 16px 20px;
            border-radius: 12px;
            color: white;
            font-weight: 500;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideInRight 0.3s ease;
        }

        .toast-success {
            background: linear-gradient(135deg, #10b981, #059669);
        }

        .toast-error {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }

        .toast-warning {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }

        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .info-box {
            padding: 15px;
            background: var(--orange-subtle);
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 13px;
            border-left: 4px solid var(--orange-primary);
            color: var(--text);
        }

        .info-box a {
            color: var(--orange-primary);
            text-decoration: none;
        }

        .info-box a:hover {
            text-decoration: underline;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--bg);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--orange-light);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--orange-primary);
        }

        @media (max-width: 1024px) {
            .email-client {
                flex-direction: column;
                height: auto;
            }
            .email-sidebar {
                width: 100%;
                margin-bottom: 20px;
            }
            .compose-modal {
                width: 100%;
                right: 0;
                max-height: 90vh;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 15px;
            }
            .provider-options {
                flex-direction: column;
            }
            .provider-card {
                padding: 25px;
            }
            .email-sender {
                min-width: 100px;
                max-width: 120px;
                font-size: 13px;
            }
            .email-list-item {
                padding: 12px 15px;
                gap: 10px;
                flex-wrap: wrap;
            }
            .email-content {
                min-width: 100%;
                order: 3;
            }
            .email-time {
                font-size: 11px;
            }
            .compose-modal {
                max-height: 80vh;
            }
            .compose-body {
                max-height: 300px;
            }
        }

        @media (max-width: 480px) {
            .main-content {
                padding: 10px;
            }
            .provider-title {
                font-size: 22px;
            }
            .email-sidebar {
                padding: 15px;
            }
            .compose-modal {
                max-height: 70vh;
            }
            .compose-textarea {
                min-height: 120px;
            }
            .compose-actions {
                flex-direction: column;
            }
            .compose-actions .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>

<body>
    <div class="toast-container" id="toastContainer"></div>

    <div class="main-content" id="mainContent">
        <?php if (!$is_logged_in): ?>
            <div class="provider-selection">
                <div class="provider-card">
                    <h1 class="provider-title">📧 Email Login</h1>
                    <p class="provider-subtitle">Connect your email account to send and receive messages</p>

                    <?php if (!$imap_available): ?>
                        <div class="info-box" style="border-left-color:#f59e0b; background:rgba(245,158,11,0.08);">
                            ⚠️ <strong>IMAP not enabled!</strong> Enable <code>extension=imap</code> in php.ini to fetch inbox emails
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="email.php">
                        <div class="provider-options">
                            <div class="provider-option" onclick="selectProvider('gmail')" id="gmailOption">
                                <div class="provider-icon gmail-icon">G</div>
                                <div class="provider-name">Gmail</div>
                                <div class="provider-hint">Use App Password</div>
                            </div>
                            <div class="provider-option" onclick="selectProvider('rediff')" id="rediffOption">
                                <div class="provider-icon rediff-icon">R</div>
                                <div class="provider-name">Rediff Mail</div>
                                <div class="provider-hint">Use email password</div>
                            </div>
                        </div>
                        <input type="hidden" name="provider" id="selectedProvider" required>
                        <input type="email" name="email" class="form-input" placeholder="Email Address" required>
                        <input type="password" name="password" class="form-input" placeholder="Password" required>

                        <div class="info-box" id="gmailHint" style="display:none;">
                            🔑 <strong>Gmail:</strong> Use App Password (16 chars)<br>
                            1. Enable 2-Step Verification<br>
                            2. Go to <a href="https://myaccount.google.com/apppasswords" target="_blank">App Passwords</a><br>
                            3. Generate password for "Mail"
                        </div>
                        <div class="info-box" id="rediffHint" style="display:none;">
                            ✅ <strong>Rediff Mail:</strong> Use your regular email password
                        </div>

                        <button type="submit" name="select_provider" class="btn btn-primary">
                            🔗 Connect
                        </button>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="email-client">
                <div class="email-sidebar">
                    <div class="provider-info">
                        <div>
                            <strong><?= $email_config[$provider]['name'] ?></strong>
                            <small style="display:block;word-break:break-all;color:var(--secondary);"><?= htmlspecialchars($_SESSION['email_address']) ?></small>
                        </div>
                        <a href="email.php?logout_email=1" class="logout-link">🚪 Logout</a>
                    </div>

                    <button class="compose-btn" onclick="openCompose()">✏️ Compose</button>
                    <a href="email.php?fetch=inbox" class="fetch-btn">📥 Fetch Inbox Emails</a>

                    <ul class="folder-list">
                        <a href="email.php?folder=inbox" class="folder-item <?= $current_folder == 'inbox' ? 'active' : '' ?>">
                            <span class="folder-icon">📥</span> Inbox
                            <span class="folder-badge"><?= $inbox_count ?></span>
                        </a>
                        <a href="email.php?folder=starred" class="folder-item <?= $current_folder == 'starred' ? 'active' : '' ?>">
                            <span class="folder-icon">⭐</span> Starred
                        </a>
                        <a href="email.php?folder=sent" class="folder-item <?= $current_folder == 'sent' ? 'active' : '' ?>">
                            <span class="folder-icon">📤</span> Sent
                        </a>
                        <a href="email.php?folder=drafts" class="folder-item <?= $current_folder == 'drafts' ? 'active' : '' ?>">
                            <span class="folder-icon">📝</span> Drafts
                            <span class="folder-badge"><?= $drafts_count ?></span>
                        </a>
                        <a href="email.php?folder=spam" class="folder-item <?= $current_folder == 'spam' ? 'active' : '' ?>">
                            <span class="folder-icon">🚫</span> Spam
                        </a>
                    </ul>
                </div>

                <div class="email-main">
                    <div class="email-header">
                        <strong>📁 <?= ucfirst($current_folder) ?></strong>
                        <span style="margin-left:auto;font-size:13px;color:var(--secondary);">
                            <?= $current_folder == 'drafts' ? $drafts_count . ' drafts' : count($emails) . ' emails' ?>
                        </span>
                    </div>
                    <div class="email-list">
                        <?php if ($current_folder == 'drafts'): ?>
                            <?php if (empty($drafts)): ?>
                                <div class="no-emails">
                                    <div class="icon">📝</div>
                                    <h3>No drafts</h3>
                                    <p>Your saved drafts will appear here</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($drafts as $draft): ?>
                                    <div class="email-list-item" onclick='editDraft(<?= htmlspecialchars(json_encode($draft)) ?>)'>
                                        <span>📝</span>
                                        <div class="email-sender">Draft</div>
                                        <div class="email-content">
                                            <div class="email-subject"><?= htmlspecialchars($draft['subject'] ?: '(no subject)') ?></div>
                                        </div>
                                        <div class="email-time"><?= date('M d', strtotime($draft['created_at'] ?? 'now')) ?></div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        <?php elseif (empty($emails)): ?>
                            <div class="no-emails">
                                <div class="icon">📥</div>
                                <h3><?= $current_folder == 'inbox' ? 'Inbox Empty' : 'No emails' ?></h3>
                                <p><?= $current_folder == 'inbox' ? 'Click <strong style="color:var(--orange-primary);">"Fetch Inbox Emails"</strong> to download your emails' : '' ?></p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($emails as $email): ?>
                                <div class="email-list-item <?= empty($email['is_read']) ? 'unread' : '' ?>">
                                    <div class="email-sender"><?= htmlspecialchars($email['from_name'] ?: $email['from_email'] ?: 'Unknown') ?></div>
                                    <div class="email-content">
                                        <div class="email-subject"><?= htmlspecialchars($email['subject'] ?: '(no subject)') ?></div>
                                        <div class="email-preview"><?= htmlspecialchars(substr(strip_tags($email['body'] ?? ''), 0, 80)) ?>...</div>
                                    </div>
                                    <div class="email-time"><?= date('M d', strtotime($email['message_date'] ?? $email['created_at'] ?? 'now')) ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="compose-modal" id="composeModal">
                <div class="compose-header">
                    <strong id="composeTitle">✏️ New Message</strong>
                    <span onclick="closeCompose()">✕</span>
                </div>
                <form method="POST" action="email.php">
                    <input type="hidden" name="draft_id" id="draftId" value="0">
                    <input type="hidden" name="save_draft" id="saveDraft" value="0">
                    <div class="compose-body">
                        <input type="email" name="to_email" class="compose-input" placeholder="To" id="toEmail" required>
                        <input type="text" name="subject" class="compose-input" placeholder="Subject" id="subject">
                        <textarea name="message_body" class="compose-textarea" placeholder="Write your message..." id="messageBody"></textarea>
                    </div>
                    <div class="compose-actions">
                        <button type="submit" name="send_email" class="btn btn-send" onclick="document.getElementById('saveDraft').value='0'">📤 Send</button>
                        <button type="submit" name="send_email" class="btn btn-draft" onclick="document.getElementById('saveDraft').value='1'">💾 Save Draft</button>
                        <button type="button" class="btn btn-discard" onclick="closeCompose()">🗑️ Discard</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function selectProvider(p) {
            document.getElementById('selectedProvider').value = p;
            document.getElementById('gmailOption').classList.remove('selected');
            document.getElementById('rediffOption').classList.remove('selected');
            document.getElementById(p + 'Option').classList.add('selected');
            document.getElementById('gmailHint').style.display = p === 'gmail' ? 'block' : 'none';
            document.getElementById('rediffHint').style.display = p === 'rediff' ? 'block' : 'none';
        }

        function openCompose() {
            document.getElementById('composeTitle').textContent = '✏️ New Message';
            document.getElementById('draftId').value = '0';
            document.getElementById('toEmail').value = '';
            document.getElementById('subject').value = '';
            document.getElementById('messageBody').value = '';
            document.getElementById('composeModal').style.display = 'block';
        }

        function editDraft(d) {
            document.getElementById('composeTitle').textContent = '✏️ Edit Draft';
            document.getElementById('draftId').value = d.id;
            document.getElementById('toEmail').value = d.to_email || '';
            document.getElementById('subject').value = d.subject || '';
            document.getElementById('messageBody').value = d.body || '';
            document.getElementById('composeModal').style.display = 'block';
        }

        function closeCompose() {
            document.getElementById('composeModal').style.display = 'none';
        }

        function showToast(msg, type) {
            var c = document.getElementById('toastContainer');
            var t = document.createElement('div');
            t.className = 'toast toast-' + (type || 'success');
            t.innerHTML = '<span>' + ({
                success: '✅',
                error: '❌',
                warning: '⚠️'
            } [type] || '✅') + '</span><span>' + msg + '</span><span style="cursor:pointer;margin-left:auto;font-weight:bold;" onclick="this.parentElement.remove()">✕</span>';
            c.appendChild(t);
            setTimeout(function() {
                if (t.parentElement) t.remove();
            }, 6000);
        }

        <?php if (!empty($notification)): ?>
            showToast(<?= json_encode($notification) ?>, <?= json_encode($notification_type) ?>);
        <?php endif; ?>

        // Theme and sidebar sync
        document.addEventListener("DOMContentLoaded", function() {
            const mainContent = document.getElementById("mainContent");
            
            // Apply sidebar state
            if (localStorage.getItem("sidebarState") === "collapsed" && mainContent) {
                mainContent.classList.add("expanded");
            }
            
            // Apply theme
            if (localStorage.getItem('theme') === 'dark') {
                document.body.classList.add('dark');
            }
            
            // Sidebar toggle listener
            const sidebarLogo = document.querySelector(".sidebar-logo");
            if (sidebarLogo) {
                sidebarLogo.addEventListener("click", function() {
                    setTimeout(() => {
                        if (mainContent) {
                            const sidebar = document.getElementById("sidebar");
                            if (sidebar && sidebar.classList.contains("collapsed")) {
                                mainContent.classList.add("expanded");
                            } else {
                                mainContent.classList.remove("expanded");
                            }
                        }
                    }, 50);
                });
            }
            
            // Cross-tab synchronization
            window.addEventListener('storage', function(e) {
                if (e.key === 'theme') {
                    if (e.newValue === 'dark') {
                        document.body.classList.add('dark');
                    } else {
                        document.body.classList.remove('dark');
                    }
                }
                if (e.key === 'sidebarState') {
                    if (mainContent) {
                        if (e.newValue === 'collapsed') {
                            mainContent.classList.add('expanded');
                        } else {
                            mainContent.classList.remove('expanded');
                        }
                    }
                }
            });
        });
    </script>
</body>

</html>