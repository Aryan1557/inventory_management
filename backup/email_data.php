<?php
require_once __DIR__ . '/mail_config.php';
require_login();

$me = get_current_user_row($conn);
if (!$me) {
    die('User record not found.');
}

$acc = get_email_account($conn, (int)$me['user_id'], $me['email_id']);

$notice   = '';
$errorMsg = '';

// ---------- POST: register the mailbox (only shown once, first time) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_action'] ?? '') === 'register_email') {
    $email       = trim($_POST['email'] ?? $me['email_id']);
    $appPassword = trim($_POST['app_password'] ?? '');
    $smtpHost    = trim($_POST['smtp_host'] ?? '');
    $smtpPort    = (int)($_POST['smtp_port'] ?? 587);
    $encryption  = in_array($_POST['encryption'] ?? '', ['tls', 'ssl'], true) ? $_POST['encryption'] : 'tls';

    if ($appPassword === '' || $smtpHost === '') {
        $errorMsg = 'Please fill in the app password and SMTP host.';
    } else {
        save_email_account($conn, (int)$me['user_id'], $email, $appPassword, $smtpHost, $smtpPort, $encryption);
        header('Location: email_data.php');
        exit;
    }
}

// ---------- Remove a saved mailbox (in case credentials were mistyped) ----------
if (($_GET['action'] ?? '') === 'forget' && $acc) {
    delete_email_account($conn, (int)$acc['id'], (int)$me['user_id']);
    header('Location: email_data.php');
    exit;
}

// ---------- POST: send mail ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_action'] ?? '') === 'send_mail' && $acc) {
    $to      = trim($_POST['to'] ?? '');
    $subject = trim($_POST['subject'] ?? '') ?: '(no subject)';
    $body    = trim($_POST['body'] ?? '');

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
if (!in_array($tab, ['inbox', 'all', 'sent'], true)) $tab = 'inbox';
$action = $_GET['action'] ?? 'list';

$messages      = [];
$detail        = null;
$imapAvailable = function_exists('imap_open');
$imapError     = '';

if ($view === 'client' && !$imapAvailable) {
    $imapError = 'The PHP "imap" extension isn\'t enabled on this server, so live mail can\'t be fetched yet (sending still works). Enable extension=imap in php.ini and restart Apache.';
}

if ($view === 'client' && $imapAvailable && $action === 'download') {
    $folderKind = ($_GET['folder'] ?? '') === 'sent' ? 'sent' : 'inbox';
    $uid    = (int)($_GET['uid'] ?? 0);
    $partNo = $_GET['part'] ?? '';

    $imap = null;
    if ($folderKind === 'sent') {
        $tmp = imap_connect_account($acc, 'INBOX');
        if ($tmp) {
            $sentFolder = imap_guess_sent_folder($tmp, $acc);
            imap_close($tmp);
            $imap = $sentFolder ? imap_connect_account($acc, $sentFolder) : false;
        }
    } else {
        $imap = imap_connect_account($acc, 'INBOX');
    }

    if ($imap && $uid && $partNo !== '') {
        $structure = @imap_fetchstructure($imap, $uid, FT_UID);
        $parts = $structure ? imap_list_attachment_parts($structure) : [];
        $target = null;
        foreach ($parts as $p) { if ($p['part_no'] === $partNo) { $target = $p; break; } }
        if ($target) {
            $raw  = imap_fetchbody($imap, $uid, $partNo, FT_UID);
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
    if ($imap) imap_close($imap);
    http_response_code(404);
    die('Attachment not found.');
}

if ($view === 'client' && $imapAvailable && $action === 'view') {
    $folderKind = ($_GET['folder'] ?? '') === 'sent' ? 'sent' : 'inbox';
    $uid = (int)($_GET['uid'] ?? 0);

    $imap = null;
    if ($folderKind === 'sent') {
        $tmp = imap_connect_account($acc, 'INBOX');
        if ($tmp) {
            $sentFolder = imap_guess_sent_folder($tmp, $acc);
            imap_close($tmp);
            $imap = $sentFolder ? imap_connect_account($acc, $sentFolder) : false;
        }
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
        if ($imap) { $messages = imap_message_list($imap, 'UNSEEN'); imap_close($imap); }
        else $imapError = 'Could not connect to your mailbox. Double check the app password and SMTP host.';
    } elseif ($tab === 'all') {
        $imap = imap_connect_account($acc, 'INBOX');
        if ($imap) { $messages = imap_message_list($imap, 'ALL'); imap_close($imap); }
        else $imapError = 'Could not connect to your mailbox.';
    } elseif ($tab === 'sent') {
        $imap = imap_connect_account($acc, 'INBOX');
        if ($imap) {
            $sentFolder = imap_guess_sent_folder($imap, $acc);
            imap_close($imap);
            if ($sentFolder) {
                $imap2 = imap_connect_account($acc, $sentFolder);
                if ($imap2) { $messages = imap_message_list($imap2, 'ALL'); imap_close($imap2); }
                else $imapError = 'Could not open your Sent folder.';
            } else {
                $imapError = "Couldn't find a Sent folder on your mail server.";
            }
        } else {
            $imapError = 'Could not connect to your mailbox.';
        }
    }
}

function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES); }
$smtpDefaults = guess_smtp_host($me['email_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Email Data · IMS</title>
<style>
    /* ---- Matches the sidebar's orange/dark theme ---- */
    * { margin:0; padding:0; box-sizing:border-box; font-family: Arial, sans-serif; }
    body {
        background: linear-gradient(180deg, #1a0e0a 0%, #0d0805 100%);
        min-height: 100vh;
        color: #f8fafc;
    }
    :root {
        --card-bg:#1a0e0a; --text:#f8fafc; --border:#3a2a1a;
        --orange-primary:#f57c00; --orange-light:#ffb74d; --orange-dark:#e65100;
        --orange-subtle:rgba(255,152,0,.08); --orange-shadow:rgba(245,124,0,.15);
        --unread-bg: rgba(255,152,0,.10);
    }
    #mainContent {
        margin-left: 280px;
        padding: 30px 36px;
        transition: all .4s ease;
        min-height: 100vh;
    }
    #mainContent.expanded { margin-left: 85px; }

    h1,h2,h3 { font-weight:600; }
    a { color: var(--orange-light); text-decoration:none; }
    a:hover { text-decoration: underline; }

    .card {
        background: var(--card-bg);
        border: 1px solid var(--border);
        border-radius: 14px;
        box-shadow: 0 10px 25px var(--orange-shadow), 0 2px 8px var(--orange-subtle);
    }

    .topbar { display:flex; align-items:center; justify-content:space-between; margin-bottom:22px; }
    .topbar h1 { font-size:24px; }

    .btn {
        display:inline-block; padding:10px 18px; border-radius:10px; border:none;
        background: var(--orange-primary); color:#1a0e0a; font-weight:600; font-size:14px;
        cursor:pointer; transition:.25s;
    }
    .btn:hover { background: var(--orange-light); text-decoration:none; box-shadow:0 8px 20px var(--orange-shadow); }
    .btn.ghost { background: var(--orange-subtle); color: var(--orange-light); border:1px solid var(--border); }
    .btn.ghost:hover { background: rgba(255,152,0,.18); }

    .tabs { display:flex; gap:6px; margin-bottom:18px; }
    .tabs a {
        padding:10px 18px; border-radius:10px 10px 0 0; font-size:14px; font-weight:600;
        color: rgba(248,250,252,.65); background: rgba(255,152,0,.04);
    }
    .tabs a:hover { text-decoration:none; color:var(--orange-light); }
    .tabs a.active { background: var(--card-bg); color: var(--orange-light); border:1px solid var(--border); border-bottom:none; }

    .field { margin-bottom:16px; }
    .field label { display:block; font-size:12.5px; font-weight:600; text-transform:uppercase; letter-spacing:.04em; color: var(--orange-light); margin-bottom:6px; }
    .field input, .field select, .field textarea {
        width:100%; padding:11px 13px; border-radius:9px; border:1px solid var(--border);
        background: rgba(255,255,255,.03); color:var(--text); font-size:14px; font-family:inherit;
    }
    .field input:focus, .field select:focus, .field textarea:focus {
        outline:none; border-color: var(--orange-primary); box-shadow:0 0 0 3px var(--orange-shadow);
    }
    .field textarea { min-height:200px; resize:vertical; }
    .hint { font-size:12.5px; color:rgba(248,250,252,.5); margin-top:6px; }

    .grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:16px; }

    .msg-list .row {
        display:flex; align-items:center; gap:16px; padding:14px 20px;
        border-bottom:1px solid var(--border); cursor:pointer;
    }
    .msg-list .row:last-child { border-bottom:none; }
    .msg-list .row:hover { background: rgba(255,152,0,.06); }
    .msg-list .row.unread { background: var(--unread-bg); font-weight:700; }
    .msg-list .from { width:230px; flex-shrink:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .msg-list .subject { flex:1; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .msg-list .date { flex-shrink:0; font-size:12.5px; color:rgba(248,250,252,.55); }
    .dot { width:7px; height:7px; border-radius:50%; background:var(--orange-primary); flex-shrink:0; }
    .clip { font-size:12.5px; color: var(--orange-light); margin-left:8px; }

    .empty { text-align:center; padding:60px 20px; color:rgba(248,250,252,.55); }

    .notice { padding:12px 16px; border-radius:10px; margin-bottom:18px; font-size:13.5px; }
    .notice.error { background:rgba(230,57,70,.12); border:1px solid rgba(230,57,70,.4); color:#ffb4ba; }
    .notice.success { background:rgba(76,175,80,.12); border:1px solid rgba(76,175,80,.4); color:#b7f0bb; }
    .notice.warn { background:rgba(255,193,7,.10); border:1px solid rgba(255,193,7,.35); color:#ffe082; }

    .detail-meta { font-size:13.5px; color:rgba(248,250,252,.65); border-bottom:1px solid var(--border); padding-bottom:14px; margin-bottom:18px; }
    .detail-meta b { color:var(--text); }
    .detail-body { white-space:pre-wrap; line-height:1.7; font-size:15px; margin-bottom:22px; }
    .attachment-chip {
        display:inline-flex; align-items:center; gap:8px; padding:8px 12px; margin:4px 6px 0 0;
        border:1px solid var(--border); border-radius:9px; background:rgba(255,255,255,.03); font-size:13px;
    }
    .file-drop { border:1.5px dashed var(--border); border-radius:9px; padding:14px; font-size:13px; color:rgba(248,250,252,.6); }
</style>
</head>
<body>

<?php include __DIR__ . '/sidebar.php'; ?>

<div id="mainContent">

<?php if ($view === 'register'): ?>

    <div class="topbar"><h1>Connect your mailbox</h1></div>
    <div class="card" style="max-width:560px; padding:28px 30px;">
        <p style="color:rgba(248,250,252,.7); margin-bottom:22px; font-size:14px;">
            We don't have mail server credentials for <b><?= h($me['email_id']) ?></b> yet.
            Enter them once below — after this you won't be asked again.
        </p>

        <?php if ($errorMsg): ?><div class="notice error"><?= h($errorMsg) ?></div><?php endif; ?>

        <form method="post">
            <input type="hidden" name="form_action" value="register_email">
            <div class="field">
                <label>Email address</label>
                <input type="email" name="email" value="<?= h($me['email_id']) ?>" readonly>
            </div>
            <div class="field">
                <label>App password</label>
                <input type="password" name="app_password" placeholder="16-character app password" required>
                <div class="hint">For Gmail: Google Account → Security → 2-Step Verification → App Passwords. Use that here, not your normal password.</div>
            </div>
            <div class="grid-2">
                <div class="field">
                    <label>SMTP host</label>
                    <input type="text" name="smtp_host" value="<?= h($smtpDefaults[0]) ?>" placeholder="smtp.gmail.com" required>
                </div>
                <div class="field">
                    <label>SMTP port</label>
                    <input type="number" name="smtp_port" value="<?= h($smtpDefaults[1]) ?>" required>
                </div>
            </div>
            <div class="field">
                <label>Encryption</label>
                <select name="encryption">
                    <option value="tls" <?= $smtpDefaults[2] === 'tls' ? 'selected' : '' ?>>STARTTLS</option>
                    <option value="ssl">SSL</option>
                </select>
            </div>
            <button type="submit" class="btn">Save & connect</button>
        </form>
    </div>

<?php else: /* view === 'client' */ ?>

    <?php if ($action === 'compose'): ?>

        <div class="topbar"><h1>Compose</h1></div>
        <div class="card" style="max-width:640px; padding:26px 28px;">
            <?php if ($errorMsg): ?><div class="notice error"><?= h($errorMsg) ?></div><?php endif; ?>
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="form_action" value="send_mail">
                <div class="field">
                    <label>From</label>
                    <input type="text" value="<?= h($acc['email']) ?>" disabled>
                </div>
                <div class="field">
                    <label>To</label>
                    <input type="email" name="to" placeholder="recipient@example.com" required>
                </div>
                <div class="field">
                    <label>Subject</label>
                    <input type="text" name="subject" required>
                </div>
                <div class="field">
                    <label>Message</label>
                    <textarea name="body" required></textarea>
                </div>
                <div class="field">
                    <label>Attachments (up to 5, 10&nbsp;MB each)</label>
                    <div class="file-drop"><input type="file" name="attachments[]" multiple></div>
                </div>
                <button type="submit" class="btn">Send</button>
                <a href="email_data.php" class="btn ghost">Cancel</a>
            </form>
        </div>

    <?php elseif ($action === 'view'): ?>

        <div class="topbar"><h1>Message</h1></div>
        <a href="email_data.php?tab=<?= h($_GET['folder'] ?? 'inbox') ?>">&larr; Back</a><br><br>

        <?php if ($imapError): ?>
            <div class="notice error"><?= h($imapError) ?></div>
        <?php elseif (!$detail): ?>
            <div class="notice error">Message not found.</div>
        <?php else: ?>
            <div class="card" style="max-width:760px; padding:26px 30px;">
                <h2 style="margin-bottom:14px;"><?= h($detail['subject']) ?></h2>
                <div class="detail-meta">
                    <div><b>From:</b> <?= h($detail['from']) ?></div>
                    <?php if ($detail['to']): ?><div><b>To:</b> <?= h($detail['to']) ?></div><?php endif; ?>
                    <div><?= h($detail['date']) ?></div>
                </div>
                <div class="detail-body"><?= h($detail['body']) ?></div>
                <?php if (!empty($detail['attachments'])): ?>
                    <div>
                        <?php foreach ($detail['attachments'] as $a): ?>
                            <a class="attachment-chip" href="email_data.php?action=download&folder=<?= h($_GET['folder'] ?? 'inbox') ?>&uid=<?= (int)$detail['uid'] ?>&part=<?= h($a['part_no']) ?>">
                                📎 <?= h($a['filename']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    <?php else: /* action === list */ ?>

        <div class="topbar">
            <h1>Mail</h1>
            <div>
                <a href="email_data.php?action=compose" class="btn">+ Compose</a>
                <a href="email_data.php?action=forget" class="btn ghost" onclick="return confirm('Remove saved mailbox credentials? You\'ll need to re-enter them.');">Change mailbox</a>
            </div>
        </div>

        <?php if (isset($_GET['sent'])): ?><div class="notice success">Message sent.</div><?php endif; ?>
        <?php if ($imapError): ?><div class="notice warn"><?= h($imapError) ?></div><?php endif; ?>

        <div class="tabs">
            <a href="email_data.php?tab=inbox" class="<?= $tab === 'inbox' ? 'active' : '' ?>">Inbox (unread)</a>
            <a href="email_data.php?tab=all" class="<?= $tab === 'all' ? 'active' : '' ?>">All Mail</a>
            <a href="email_data.php?tab=sent" class="<?= $tab === 'sent' ? 'active' : '' ?>">Sent</a>
        </div>

        <div class="card msg-list">
            <?php if (empty($messages)): ?>
                <div class="empty">Nothing here yet.</div>
            <?php else: ?>
                <?php foreach ($messages as $m): ?>
                    <a class="row <?= (!$m['seen'] && $tab !== 'sent') ? 'unread' : '' ?>"
                       href="email_data.php?action=view&folder=<?= $tab === 'sent' ? 'sent' : 'inbox' ?>&uid=<?= (int)$m['uid'] ?>">
                        <?php if (!$m['seen'] && $tab !== 'sent'): ?><span class="dot"></span><?php else: ?><span style="width:7px"></span><?php endif; ?>
                        <span class="from"><?= h($m['from']) ?></span>
                        <span class="subject">
                            <?= h($m['subject']) ?>
                            <?php if ($m['has_attachments']): ?><span class="clip">📎</span><?php endif; ?>
                        </span>
                        <span class="date"><?= h($m['date']) ?></span>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    <?php endif; ?>

<?php endif; ?>

</div>
</body>
</html>
