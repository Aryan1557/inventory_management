<?php
require_once __DIR__ . '/mail_config.php';
require_login();

$me = get_current_user_row($conn);
if (!$me) {
    die('User record not found.');
}

$acc = get_email_account($conn, (int) $me['user_id'], $me['email_id']);

$notice = '';
$errorMsg = '';

// ---------- POST: register the mailbox (only shown once, first time) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_action'] ?? '') === 'register_email') {
    $email = trim($_POST['email'] ?? $me['email_id']);
    $appPassword = trim($_POST['app_password'] ?? '');
    $smtpHost = trim($_POST['smtp_host'] ?? '');
    $smtpPort = (int) ($_POST['smtp_port'] ?? 587);
    $encryption = in_array($_POST['encryption'] ?? '', ['tls', 'ssl'], true) ? $_POST['encryption'] : 'tls';

    if ($appPassword === '' || $smtpHost === '') {
        $errorMsg = 'Please fill in the app password and SMTP host.';
    } else {
        save_email_account($conn, (int) $me['user_id'], $email, $appPassword, $smtpHost, $smtpPort, $encryption);
        header('Location: email_data.php');
        exit;
    }
}

// ---------- Remove a saved mailbox (in case credentials were mistyped) ----------
if (($_GET['action'] ?? '') === 'forget' && $acc) {
    delete_email_account($conn, (int) $acc['id'], (int) $me['user_id']);
    header('Location: email_data.php');
    exit;
}

// ---------- POST: send mail ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_action'] ?? '') === 'send_mail' && $acc) {
    $to = trim($_POST['to'] ?? '');
    $subject = trim($_POST['subject'] ?? '') ?: '(no subject)';
    $body = trim($_POST['body'] ?? '');

    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        $errorMsg = 'Enter a valid recipient email address.';
    } elseif ($body === '') {
        $errorMsg = 'Message body cannot be empty.';
    } else {
        try {
            send_mail_as($acc, $to, $subject, $body, $_FILES['attachments'] ?? null);
            header('Location: email_data.php?tab=sent&sent=1');
            exit;
        } catch (\Throwable $e) {
            $errorMsg = 'Message was not sent: ' . $e->getMessage();
        }
    }
}

$view = $acc ? 'client' : 'register';

// ---------- Mail-client data fetching (only once a mailbox is registered) ----------
$tab = $_GET['tab'] ?? 'inbox';
if (!in_array($tab, ['inbox', 'all', 'sent'], true))
    $tab = 'inbox';
$action = $_GET['action'] ?? 'list';

$messages = [];
$detail = null;
$imapAvailable = function_exists('imap_open');
$imapError = '';

if ($view === 'client' && !$imapAvailable) {
    $imapError = 'The PHP "imap" extension isn\'t enabled on this server, so live mail can\'t be fetched yet (sending still works). Enable extension=imap in php.ini and restart Apache.';
}

if ($view === 'client' && $imapAvailable && $action === 'download') {
    $folderKind = ($_GET['folder'] ?? '') === 'sent' ? 'sent' : 'inbox';
    $uid = (int) ($_GET['uid'] ?? 0);
    $partNo = $_GET['part'] ?? '';

    $imap = null;
    if ($folderKind === 'sent') {
        $sentFolder = get_sent_folder_name($acc);
        $imap = $sentFolder ? imap_connect_account($acc, $sentFolder) : false;
    } else {
        $imap = imap_connect_account($acc, 'INBOX');
    }

    if ($imap && $uid && $partNo !== '') {
        $structure = @imap_fetchstructure($imap, $uid, FT_UID);
        $parts = $structure ? imap_list_attachment_parts($structure) : [];
        $target = null;
        foreach ($parts as $p) {
            if ($p['part_no'] === $partNo) {
                $target = $p;
                break;
            }
        }
        if ($target) {
            $raw = imap_fetchbody($imap, $uid, $partNo, FT_UID);
            $data = imap_decode_part_body($raw, $target['encoding']);
            imap_close($imap);
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($target['filename']) . '"');
            header('Content-Length: ' . strlen($data));
            echo $data;
            exit;
        }
    }
    if ($imap)
        imap_close($imap);
    http_response_code(404);
    die('Attachment not found.');
}

if ($view === 'client' && $imapAvailable && $action === 'view') {
    $folderKind = ($_GET['folder'] ?? '') === 'sent' ? 'sent' : 'inbox';
    $uid = (int) ($_GET['uid'] ?? 0);

    $imap = null;
    if ($folderKind === 'sent') {
        $sentFolder = get_sent_folder_name($acc);
        $imap = $sentFolder ? imap_connect_account($acc, $sentFolder) : false;
    } else {
        $imap = imap_connect_account($acc, 'INBOX');
    }

    if ($imap) {
        $detail = get_message_detail($imap, $uid);
        imap_close($imap);
    } else {
        $imapError = 'Could not connect to your mailbox.';
    }
}

if ($view === 'client' && $imapAvailable && $action === 'list') {
    if ($tab === 'inbox') {
        $imap = imap_connect_account($acc, 'INBOX');
        if ($imap) {
            $messages = imap_message_list($imap, 'UNSEEN');
            imap_close($imap);
        } else
            $imapError = 'Could not connect to your mailbox. Double check the app password and SMTP host.';
    } elseif ($tab === 'all') {
        $imap = imap_connect_account($acc, 'INBOX');
        if ($imap) {
            $messages = imap_message_list($imap, 'ALL');
            imap_close($imap);
        } else
            $imapError = 'Could not connect to your mailbox.';
    } elseif ($tab === 'sent') {
        $sentFolder = get_sent_folder_name($acc);
        if ($sentFolder) {
            $imap2 = imap_connect_account($acc, $sentFolder);
            if ($imap2) {
                $messages = imap_message_list($imap2, 'ALL');
                imap_close($imap2);
            } else
                $imapError = 'Could not open your Sent folder.';
        } else {
            $imapError = "Couldn't find a Sent folder on your mail server.";
        }
    }
}

function h($s)
{
    return htmlspecialchars((string) $s, ENT_QUOTES);
}
$smtpDefaults = guess_smtp_host($me['email_id']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Data · IMS</title>
    <style>
        /* ---- Global Reset & Variables ---- */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            /* Light Mode */
            --bg-primary: #f0f2f5;
            --bg-card: #ffffff;
            --bg-input: #f8f9fa;
            --text-primary: #1a1a2e;
            --text-secondary: #4a4a6a;
            --text-muted: #6c6c8a;
            --border-color: #e2e8f0;
            --border-hover: #cbd5e1;
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.06);
            --shadow-md: 0 4px 20px rgba(0, 0, 0, 0.08);
            --shadow-lg: 0 8px 40px rgba(0, 0, 0, 0.12);
            --orange-primary: #f57c00;
            --orange-hover: #e65100;
            --orange-light: #ff9800;
            --orange-bg: rgba(245, 124, 0, 0.08);
            --orange-bg-hover: rgba(245, 124, 0, 0.15);
            --success: #10b981;
            --success-bg: rgba(16, 185, 129, 0.1);
            --error: #ef4444;
            --error-bg: rgba(239, 68, 68, 0.1);
            --warning: #f59e0b;
            --warning-bg: rgba(245, 158, 11, 0.1);
            --radius: 12px;
            --radius-sm: 8px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Dark Mode */
        [data-theme="dark"] {
            --bg-primary: #0a0a0f;
            --bg-card: #14141e;
            --bg-input: #1a1a28;
            --text-primary: #e8e8f0;
            --text-secondary: #a8a8c0;
            --text-muted: #7878a0;
            --border-color: #2a2a3e;
            --border-hover: #3a3a52;
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.3);
            --shadow-md: 0 4px 20px rgba(0, 0, 0, 0.4);
            --shadow-lg: 0 8px 40px rgba(0, 0, 0, 0.5);
            --orange-bg: rgba(255, 152, 0, 0.08);
            --orange-bg-hover: rgba(255, 152, 0, 0.18);
            --success-bg: rgba(16, 185, 129, 0.15);
            --error-bg: rgba(239, 68, 68, 0.15);
            --warning-bg: rgba(245, 158, 11, 0.15);
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            transition: var(--transition);
            min-height: 100vh;
        }

        /* ---- Main Content Layout ---- */
        #mainContent {
            margin-left: 280px;
            padding: 32px 40px 60px;
            transition: var(--transition);
            min-height: 100vh;
        }

        #mainContent.expanded {
            margin-left: 85px;
        }

        /* ---- Scrollbar Styling ---- */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: var(--bg-primary);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--orange-primary);
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--orange-hover);
        }

        /* ---- Typography ---- */
        h1 {
            font-size: 28px;
            font-weight: 700;
            letter-spacing: -0.5px;
            color: var(--text-primary);
        }

        h2 {
            font-size: 22px;
            font-weight: 600;
            color: var(--text-primary);
        }

        h3 {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-primary);
        }

        a {
            color: var(--orange-primary);
            text-decoration: none;
            transition: var(--transition);
        }

        a:hover {
            color: var(--orange-hover);
            text-decoration: none;
        }

        /* ---- Cards ---- */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            overflow: hidden;
        }

        .card:hover {
            box-shadow: var(--shadow-md);
        }

        .card-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-body {
            padding: 24px;
        }

        /* ---- Top Bar ---- */
        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 28px;
            gap: 16px;
            flex-wrap: wrap;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .topbar-left .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border-radius: var(--radius-sm);
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            color: var(--text-secondary);
            font-size: 14px;
            transition: var(--transition);
            cursor: pointer;
        }

        .topbar-left .back-btn:hover {
            background: var(--orange-bg);
            border-color: var(--orange-primary);
            color: var(--orange-primary);
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        /* ---- Buttons ---- */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: var(--radius-sm);
            border: none;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: var(--transition);
            background: var(--orange-primary);
            color: #ffffff;
            line-height: 1;
        }

        .btn:hover {
            background: var(--orange-hover);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(245, 124, 0, 0.3);
            color: #ffffff;
            text-decoration: none;
        }

        .btn:active {
            transform: translateY(0);
        }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--border-color);
            color: var(--text-secondary);
        }

        .btn-outline:hover {
            background: var(--orange-bg);
            border-color: var(--orange-primary);
            color: var(--orange-primary);
            box-shadow: none;
            transform: none;
        }

        .btn-danger-outline {
            background: transparent;
            border: 1px solid var(--border-color);
            color: var(--error);
        }

        .btn-danger-outline:hover {
            background: var(--error-bg);
            border-color: var(--error);
            color: var(--error);
            box-shadow: none;
            transform: none;
        }

        .btn-success {
            background: var(--success);
            color: #ffffff;
        }

        .btn-success:hover {
            background: #059669;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
            color: #ffffff;
        }

        /* ---- Form Elements ---- */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 6px;
            letter-spacing: 0.3px;
        }

        .form-control {
            width: 100%;
            padding: 10px 14px;
            border-radius: var(--radius-sm);
            border: 1px solid var(--border-color);
            background: var(--bg-input);
            color: var(--text-primary);
            font-size: 14px;
            transition: var(--transition);
            font-family: inherit;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--orange-primary);
            box-shadow: 0 0 0 3px var(--orange-bg);
        }

        .form-control:disabled,
        .form-control[readonly] {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .form-control::placeholder {
            color: var(--text-muted);
        }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236c6c8a' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            padding-right: 36px;
            cursor: pointer;
        }

        textarea.form-control {
            min-height: 180px;
            resize: vertical;
            font-family: inherit;
        }

        .form-hint {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 4px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        @media (max-width: 640px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }

        /* ---- Tabs ---- */
        .tabs {
            display: flex;
            gap: 4px;
            margin-bottom: 24px;
            background: var(--bg-card);
            padding: 4px;
            border-radius: var(--radius-sm);
            border: 1px solid var(--border-color);
            flex-wrap: wrap;
        }

        .tabs a {
            padding: 8px 18px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            color: var(--text-secondary);
            transition: var(--transition);
            background: transparent;
            border: none;
        }

        .tabs a:hover {
            background: var(--orange-bg);
            color: var(--orange-primary);
        }

        .tabs a.active {
            background: var(--orange-primary);
            color: #ffffff;
        }

        .tabs a.active:hover {
            background: var(--orange-hover);
            color: #ffffff;
        }

        /* ---- Message List ---- */
        .msg-list {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            overflow: hidden;
        }

        .msg-item {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 14px 20px;
            border-bottom: 1px solid var(--border-color);
            transition: var(--transition);
            cursor: pointer;
            color: var(--text-primary);
        }

        .msg-item:last-child {
            border-bottom: none;
        }

        .msg-item:hover {
            background: var(--orange-bg);
        }

        .msg-item.unread {
            background: var(--orange-bg);
            border-left: 3px solid var(--orange-primary);
        }

        .msg-item.unread:hover {
            background: var(--orange-bg-hover);
        }

        .msg-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--orange-primary);
            flex-shrink: 0;
        }

        .msg-from {
            min-width: 180px;
            max-width: 220px;
            font-weight: 500;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .msg-subject {
            flex: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            color: var(--text-secondary);
        }

        .msg-item.unread .msg-subject {
            color: var(--text-primary);
            font-weight: 600;
        }

        .msg-date {
            flex-shrink: 0;
            font-size: 12px;
            color: var(--text-muted);
            white-space: nowrap;
        }

        @media (max-width: 768px) {
            .msg-from {
                min-width: 120px;
                max-width: 140px;
            }

            .msg-subject {
                font-size: 13px;
            }

            .msg-item {
                padding: 12px 16px;
                gap: 12px;
                flex-wrap: wrap;
            }
        }

        /* ---- Empty State ---- */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-muted);
        }

        .empty-state .icon {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.5;
        }

        .empty-state h3 {
            color: var(--text-secondary);
            margin-bottom: 8px;
        }

        /* ---- Notices ---- */
        .notice {
            padding: 14px 18px;
            border-radius: var(--radius-sm);
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .notice-success {
            background: var(--success-bg);
            border: 1px solid rgba(16, 185, 129, 0.2);
            color: var(--success);
        }

        .notice-error {
            background: var(--error-bg);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: var(--error);
        }

        .notice-warning {
            background: var(--warning-bg);
            border: 1px solid rgba(245, 158, 11, 0.2);
            color: var(--warning);
        }

        /* ---- Message Detail ---- */
        .detail-meta {
            font-size: 14px;
            color: var(--text-secondary);
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 16px;
            margin-bottom: 20px;
        }

        .detail-meta div {
            margin-bottom: 4px;
        }

        .detail-meta strong {
            color: var(--text-primary);
            font-weight: 600;
        }

        .detail-body {
            white-space: pre-wrap;
            line-height: 1.8;
            font-size: 15px;
            margin-bottom: 24px;
            color: var(--text-primary);
        }

        /* ---- Attachments ---- */
        .attachment-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 8px;
        }

        .attachment-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 14px;
            border: 1px solid var(--border-color);
            border-radius: 20px;
            background: var(--bg-input);
            font-size: 13px;
            color: var(--text-secondary);
            transition: var(--transition);
        }

        .attachment-chip:hover {
            background: var(--orange-bg);
            border-color: var(--orange-primary);
            color: var(--orange-primary);
        }

        /* ---- File Upload ---- */
        .file-drop {
            border: 2px dashed var(--border-color);
            border-radius: var(--radius-sm);
            padding: 20px;
            text-align: center;
            color: var(--text-muted);
            transition: var(--transition);
            cursor: pointer;
        }

        .file-drop:hover {
            border-color: var(--orange-primary);
            background: var(--orange-bg);
        }

        .file-drop input[type="file"] {
            display: block;
            width: 100%;
            padding: 8px 0;
            cursor: pointer;
        }

        /* ---- Responsive ---- */
        @media (max-width: 1024px) {
            #mainContent {
                margin-left: 240px;
                padding: 24px 28px 40px;
            }
        }

        @media (max-width: 768px) {
            #mainContent {
                margin-left: 0 !important;
                padding: 16px 20px 40px;
            }

            .topbar {
                flex-direction: column;
                align-items: stretch;
            }

            .topbar-actions {
                justify-content: stretch;
            }

            .topbar-actions .btn {
                flex: 1;
                justify-content: center;
            }

            .card-header {
                flex-direction: column;
                gap: 12px;
                align-items: stretch;
            }
        }

        @media (max-width: 480px) {
            #mainContent {
                padding: 12px 14px 32px;
            }

            h1 {
                font-size: 22px;
            }

            .msg-item {
                padding: 10px 14px;
                flex-wrap: wrap;
            }

            .msg-from {
                min-width: 100%;
                max-width: 100%;
            }
        }
    </style>
</head>

<body>

    <?php include __DIR__ . '/sidebar.php'; ?>

    <div id="mainContent">

        <?php if ($view === 'register'): ?>

            <div class="topbar">
                <h1>📧 Connect Your Mailbox</h1>
            </div>

            <div class="card" style="max-width: 600px;">
                <div class="card-body">
                    <p style="color: var(--text-secondary); margin-bottom: 24px; font-size: 15px; line-height: 1.6;">
                        We don't have mail server credentials for <strong><?= h($me['email_id']) ?></strong> yet.
                        Enter them once below — after this you won't be asked again.
                    </p>

                    <?php if ($errorMsg): ?>
                        <div class="notice notice-error">⚠️ <?= h($errorMsg) ?></div>
                    <?php endif; ?>

                    <form method="post">
                        <input type="hidden" name="form_action" value="register_email">

                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" name="email" class="form-control" value="<?= h($me['email_id']) ?>"
                                readonly>
                        </div>

                        <div class="form-group">
                            <label>App Password</label>
                            <input type="password" name="app_password" class="form-control"
                                placeholder="16-character app password" required>
                            <div class="form-hint">For Gmail: Google Account → Security → 2-Step Verification → App
                                Passwords</div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>SMTP Host</label>
                                <input type="text" name="smtp_host" class="form-control" value="<?= h($smtpDefaults[0]) ?>"
                                    placeholder="smtp.gmail.com" required>
                            </div>
                            <div class="form-group">
                                <label>SMTP Port</label>
                                <input type="number" name="smtp_port" class="form-control"
                                    value="<?= h($smtpDefaults[1]) ?>" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Encryption</label>
                            <select name="encryption" class="form-control">
                                <option value="tls" <?= $smtpDefaults[2] === 'tls' ? 'selected' : '' ?>>STARTTLS</option>
                                <option value="ssl">SSL</option>
                            </select>
                        </div>

                        <button type="submit" class="btn" style="width: 100%; justify-content: center;">
                            🔗 Save & Connect
                        </button>
                    </form>
                </div>
            </div>

        <?php else: /* view === 'client' */ ?>

            <?php if ($action === 'compose'): ?>

                <div class="topbar">
                    <div class="topbar-left">
                        <a href="email_data.php" class="back-btn">← Back</a>
                        <h1>✏️ Compose</h1>
                    </div>
                </div>

                <div class="card" style="max-width: 700px;">
                    <div class="card-body">
                        <?php if ($errorMsg): ?>
                            <div class="notice notice-error">⚠️ <?= h($errorMsg) ?></div>
                        <?php endif; ?>

                        <form method="post" enctype="multipart/form-data">
                            <input type="hidden" name="form_action" value="send_mail">

                            <div class="form-group">
                                <label>From</label>
                                <input type="text" class="form-control" value="<?= h($acc['email']) ?>" disabled>
                            </div>

                            <div class="form-group">
                                <label>To <span style="color: var(--error);">*</span></label>
                                <input type="email" name="to" class="form-control" placeholder="recipient@example.com" required>
                            </div>

                            <div class="form-group">
                                <label>Subject</label>
                                <input type="text" name="subject" class="form-control" placeholder="Enter subject">
                            </div>

                            <div class="form-group">
                                <label>Message <span style="color: var(--error);">*</span></label>
                                <textarea name="body" class="form-control" placeholder="Write your message here..."
                                    required></textarea>
                            </div>

                            <div class="form-group">
                                <label>Attachments</label>
                                <div class="file-drop">
                                    <input type="file" name="attachments[]" multiple>
                                    <div class="form-hint" style="margin-top: 8px;">Up to 5 files, 10MB each</div>
                                </div>
                            </div>

                            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                                <button type="submit" class="btn">📤 Send</button>
                                <a href="email_data.php" class="btn btn-outline">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>

            <?php elseif ($action === 'view'): ?>

                <div class="topbar">
                    <div class="topbar-left">
                        <a href="email_data.php?tab=<?= h($_GET['folder'] ?? 'inbox') ?>" class="back-btn">← Back</a>
                        <h1>📄 Message</h1>
                    </div>
                </div>

                <?php if ($imapError): ?>
                    <div class="notice notice-error">⚠️ <?= h($imapError) ?></div>
                <?php elseif (!$detail): ?>
                    <div class="notice notice-error">⚠️ Message not found.</div>
                <?php else: ?>
                    <div class="card">
                        <div class="card-body">
                            <h2 style="margin-bottom: 16px;"><?= h($detail['subject']) ?></h2>

                            <div class="detail-meta">
                                <div><strong>From:</strong> <?= h($detail['from']) ?></div>
                                <?php if ($detail['to']): ?>
                                    <div><strong>To:</strong> <?= h($detail['to']) ?></div>
                                <?php endif; ?>
                                <div><strong>Date:</strong> <?= h($detail['date']) ?></div>
                            </div>

                            <div class="detail-body"><?= h($detail['body']) ?></div>

                            <?php if (!empty($detail['attachments'])): ?>
                                <div>
                                    <strong style="color: var(--text-secondary); font-size: 14px;">Attachments:</strong>
                                    <div class="attachment-list">
                                        <?php foreach ($detail['attachments'] as $a): ?>
                                            <a class="attachment-chip"
                                                href="email_data.php?action=download&folder=<?= h($_GET['folder'] ?? 'inbox') ?>&uid=<?= (int) $detail['uid'] ?>&part=<?= h($a['part_no']) ?>">
                                                📎 <?= h($a['filename']) ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

            <?php else: /* action === list */ ?>

                <div class="topbar">
                    <h1>📬 Mail</h1>
                    <div class="topbar-actions">
                        <a href="email_data.php?action=compose" class="btn">✏️ Compose</a>
                        <a href="email_data.php?action=forget" class="btn btn-danger-outline"
                            onclick="return confirm('Remove saved mailbox credentials? You\'ll need to re-enter them.');">
                            🔄 Change Mailbox
                        </a>
                    </div>
                </div>

                <?php if (isset($_GET['sent'])): ?>
                    <div class="notice notice-success">✅ Message sent successfully!</div>
                <?php endif; ?>

                <?php if ($imapError): ?>
                    <div class="notice notice-warning">⚠️ <?= h($imapError) ?></div>
                <?php endif; ?>

                <div class="tabs">
                    <a href="email_data.php?tab=inbox" class="<?= $tab === 'inbox' ? 'active' : '' ?>">📥 Inbox</a>
                    <a href="email_data.php?tab=all" class="<?= $tab === 'all' ? 'active' : '' ?>">📂 All Mail</a>
                    <a href="email_data.php?tab=sent" class="<?= $tab === 'sent' ? 'active' : '' ?>">📤 Sent</a>
                </div>

                <div class="msg-list">
                    <?php if (empty($messages)): ?>
                        <div class="empty-state">
                            <div class="icon">📭</div>
                            <h3>Nothing here yet</h3>
                            <p style="color: var(--text-muted);">Your mailbox is empty for this view</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($messages as $m): ?>
                            <a class="msg-item <?= (!$m['seen'] && $tab !== 'sent') ? 'unread' : '' ?>"
                                href="email_data.php?action=view&folder=<?= $tab === 'sent' ? 'sent' : 'inbox' ?>&uid=<?= (int) $m['uid'] ?>">
                                <?php if (!$m['seen'] && $tab !== 'sent'): ?>
                                    <span class="msg-dot"></span>
                                <?php else: ?>
                                    <span style="width: 8px; flex-shrink: 0;"></span>
                                <?php endif; ?>
                                <span class="msg-from"><?= h($m['from']) ?></span>
                                <span class="msg-subject"><?= h($m['subject']) ?></span>
                                <span class="msg-date"><?= h($m['date']) ?></span>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

            <?php endif; ?>

        <?php endif; ?>

    </div>

    <!-- Dark mode sync script -->
    <script>
        (function () {
            // Get theme from sidebar's data attribute or localStorage
            function getTheme() {
                const stored = localStorage.getItem('theme');
                if (stored === 'dark' || stored === 'light') return stored;
                return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }

            function applyTheme(theme) {
                document.documentElement.setAttribute('data-theme', theme);
                localStorage.setItem('theme', theme);
            }

            // Initial apply
            const currentTheme = getTheme();
            applyTheme(currentTheme);

            // Listen for theme changes from sidebar
            const observer = new MutationObserver(function (mutations) {
                mutations.forEach(function (mutation) {
                    if (mutation.attributeName === 'data-theme') {
                        const newTheme = document.documentElement.getAttribute('data-theme') || getTheme();
                        applyTheme(newTheme);
                    }
                });
            });

            observer.observe(document.documentElement, {
                attributes: true,
                attributeFilter: ['data-theme']
            });

            // Also listen for storage changes (in case sidebar updates localStorage directly)
            window.addEventListener('storage', function (e) {
                if (e.key === 'theme') {
                    const newTheme = e.newValue || getTheme();
                    applyTheme(newTheme);
                }
            });

            // Check for sidebar theme toggle clicks via event delegation
            document.addEventListener('click', function (e) {
                const toggle = e.target.closest('.theme-toggle, [data-theme-toggle]');
                if (toggle) {
                    // Let sidebar handle the toggle, we'll catch the change via mutation observer
                    setTimeout(function () {
                        const theme = document.documentElement.getAttribute('data-theme') || getTheme();
                        applyTheme(theme);
                    }, 50);
                }
            });
        })();
    </script>

</body>

</html>