<?php
/**
 * mail_config.php
 * Shared helpers for email_data.php. Include this, not db_connection.php directly.
 *
 * Uses your existing users / email_acc tables:
 *   users(user_id, name, address, employee_id, email_id, email_pass, contact_no,
 *         username, password_hash, designation, role, city, profile_picture,
 *         status, session_token, created_at, updated_at)
 *   email_acc(id, user_id, email, app_password, smtp_host, smtp_port, encryption,
 *             created_at, updated_at)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db_connection.php';           // must define mysqli $conn
require_once __DIR__ . '/vendor/autoload.php';          // composer require phpmailer/phpmailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

// ---------------------------------------------------------------------
// CHANGE THIS to your own random 32-byte value before going live.
// Every app_password in email_acc is encrypted with this key.
// ---------------------------------------------------------------------
define('MAIL_ENC_KEY', 'CHANGE_ME_32_char_minimum_secret_key');

define('MAX_ATTACHMENT_SIZE', 10 * 1024 * 1024); // 10 MB per file
define('MAX_ATTACHMENTS', 5);

// ---------------- Encryption for app_password ----------------

function mail_encrypt(string $plain): string {
    $iv = openssl_random_pseudo_bytes(16);
    $cipher = openssl_encrypt($plain, 'AES-256-CBC', hash('sha256', MAIL_ENC_KEY, true), OPENSSL_RAW_DATA, $iv);
    return base64_encode($iv . $cipher);
}

function mail_decrypt(string $encoded): string {
    $raw = base64_decode($encoded);
    $iv = substr($raw, 0, 16);
    $cipher = substr($raw, 16);
    return openssl_decrypt($cipher, 'AES-256-CBC', hash('sha256', MAIL_ENC_KEY, true), OPENSSL_RAW_DATA, $iv);
}

// ---------------- Current user ----------------

function require_login() {
    if (empty($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit;
    }
}

function get_current_user_row(mysqli $conn): ?array {
    $id = (int)($_SESSION['admin_id'] ?? 0);
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ? LIMIT 1");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

// ---------------- email_acc lookup ----------------

function get_email_account(mysqli $conn, int $userId, string $userEmail): ?array {
    $stmt = $conn->prepare("SELECT * FROM email_acc WHERE user_id = ? OR email = ? LIMIT 1");
    $stmt->bind_param('is', $userId, $userEmail);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

function save_email_account(mysqli $conn, int $userId, string $email, string $appPassword, string $smtpHost, int $smtpPort, string $encryption): void {
    $encPass = mail_encrypt($appPassword);
    $stmt = $conn->prepare(
        "INSERT INTO email_acc (user_id, email, app_password, smtp_host, smtp_port, encryption, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())"
    );
    $stmt->bind_param('isssis', $userId, $email, $encPass, $smtpHost, $smtpPort, $encryption);
    $stmt->execute();
    $stmt->close();
}

function delete_email_account(mysqli $conn, int $accId, int $userId): void {
    $stmt = $conn->prepare("DELETE FROM email_acc WHERE id = ? AND user_id = ?");
    $stmt->bind_param('ii', $accId, $userId);
    $stmt->execute();
    $stmt->close();
}

// ---------------- SMTP host defaults by domain (used to prefill the registration form) ----------------

function guess_smtp_host(string $email): array {
    $domain = strtolower(substr(strrchr($email, '@'), 1));
    $map = [
        'gmail.com'    => ['smtp.gmail.com', 587, 'tls'],
        'outlook.com'  => ['smtp.office365.com', 587, 'tls'],
        'hotmail.com'  => ['smtp.office365.com', 587, 'tls'],
        'live.com'     => ['smtp.office365.com', 587, 'tls'],
        'yahoo.com'    => ['smtp.mail.yahoo.com', 587, 'tls'],
    ];
    return $map[$domain] ?? ['', 587, 'tls'];
}

// ---------------- IMAP host guessing (not stored in your schema, so derived) ----------------

function guess_imap_host(string $email, string $smtpHost): array {
    $domain = strtolower(substr(strrchr($email, '@'), 1));
    $map = [
        'gmail.com'   => ['imap.gmail.com', 993],
        'outlook.com' => ['outlook.office365.com', 993],
        'hotmail.com' => ['outlook.office365.com', 993],
        'live.com'    => ['outlook.office365.com', 993],
        'yahoo.com'   => ['imap.mail.yahoo.com', 993],
    ];
    if (isset($map[$domain])) return $map[$domain];

    // Fallback: swap the "smtp." prefix for "imap." on whatever host was stored
    $guess = preg_match('/^smtp\./i', $smtpHost)
        ? preg_replace('/^smtp\./i', 'imap.', $smtpHost)
        : $smtpHost;
    return [$guess, 993];
}

/** Opens an IMAP connection to a given folder (default INBOX). Returns false on failure. */
function imap_connect_account(array $acc, string $folder = 'INBOX') {
    if (!function_exists('imap_open')) {
        return false; // php-imap extension not enabled
    }
    [$host, $port] = guess_imap_host($acc['email'], $acc['smtp_host']);
    $mailbox = '{' . $host . ':' . $port . '/imap/ssl/novalidate-cert}' . $folder;
    $appPassword = mail_decrypt($acc['app_password']);
    return @imap_open($mailbox, $acc['email'], $appPassword);
}

/** Tries to find the provider's "Sent" folder name by scanning the folder list. */
function imap_guess_sent_folder($imapStream, array $acc): ?string {
    [$host, $port] = guess_imap_host($acc['email'], $acc['smtp_host']);
    $base = '{' . $host . ':' . $port . '/imap/ssl/novalidate-cert}';
    $list = @imap_list($imapStream, $base, '*');
    if (!$list) return null;
    foreach ($list as $folder) {
        $short = str_replace($base, '', $folder);
        if (stripos($short, 'sent') !== false) {
            return $short;
        }
    }
    return null;
}

// ---------------- IMAP message helpers ----------------

/** Returns a simple list of messages for the given search criteria, newest first. */
function imap_message_list($imapStream, string $criteria = 'ALL', int $limit = 50): array {
    $uids = @imap_search($imapStream, $criteria, SE_UID);
    if (!$uids) return [];
    rsort($uids);
    $uids = array_slice($uids, 0, $limit);

    $messages = [];
    foreach ($uids as $uid) {
        $overview = @imap_fetch_overview($imapStream, (string)$uid, FT_UID);
        if (empty($overview)) continue;
        $o = $overview[0];
        $messages[] = [
            'uid'     => $uid,
            'subject' => isset($o->subject) ? imap_utf8($o->subject) : '(no subject)',
            'from'    => isset($o->from) ? imap_utf8($o->from) : 'Unknown sender',
            'date'    => isset($o->date) ? date('M j, g:ia', strtotime($o->date)) : '',
            'seen'    => !empty($o->seen),
            'has_attachments' => imap_has_attachments($imapStream, $uid),
        ];
    }
    return $messages;
}

function imap_has_attachments($imapStream, $uid): bool {
    $structure = @imap_fetchstructure($imapStream, $uid, FT_UID);
    if (!$structure || empty($structure->parts)) return false;
    foreach ($structure->parts as $part) {
        if (!empty($part->ifdisposition) && strtolower($part->disposition) === 'attachment') return true;
        if (!empty($part->ifdparameters)) {
            foreach ($part->dparameters as $p) {
                if (strtolower($p->attribute) === 'filename') return true;
            }
        }
    }
    return false;
}

/** Walks a message structure and returns a flat list of downloadable attachment parts. */
function imap_list_attachment_parts($structure, string $prefix = ''): array {
    $out = [];
    if (empty($structure->parts)) return $out;

    foreach ($structure->parts as $i => $part) {
        $partNo = $prefix === '' ? (string)($i + 1) : $prefix . '.' . ($i + 1);
        $filename = null;
        if (!empty($part->ifdparameters)) {
            foreach ($part->dparameters as $p) {
                if (strtolower($p->attribute) === 'filename') $filename = $p->value;
            }
        }
        if (!$filename && !empty($part->ifparameters)) {
            foreach ($part->parameters as $p) {
                if (strtolower($p->attribute) === 'name') $filename = $p->value;
            }
        }
        if ($filename) {
            $out[] = ['part_no' => $partNo, 'filename' => $filename, 'encoding' => $part->encoding];
        }
        if (!empty($part->parts)) {
            $out = array_merge($out, imap_list_attachment_parts($part, $partNo));
        }
    }
    return $out;
}

function imap_decode_part_body(string $data, int $encoding): string {
    switch ($encoding) {
        case 3: return base64_decode($data);          // BASE64
        case 4: return quoted_printable_decode($data); // QUOTED-PRINTABLE
        default: return $data;
    }
}

/** Extracts the readable plain-text (or first text) body from a structure. */
function imap_extract_body($imapStream, $uid, $structure): string {
    if (empty($structure->parts)) {
        $body = imap_fetchbody($imapStream, $uid, '1', FT_UID);
        return imap_decode_part_body($body, $structure->encoding ?? 0);
    }
    foreach ($structure->parts as $i => $part) {
        if ($part->type === 0 && empty($part->ifdisposition)) { // TEXT part, not an attachment
            $body = imap_fetchbody($imapStream, $uid, (string)($i + 1), FT_UID);
            return imap_decode_part_body($body, $part->encoding);
        }
    }
    return '(no readable body found)';
}

function get_message_detail($imapStream, $uid): ?array {
    $structure = @imap_fetchstructure($imapStream, $uid, FT_UID);
    $overview  = @imap_fetch_overview($imapStream, (string)$uid, FT_UID);
    if (!$structure || empty($overview)) return null;
    $o = $overview[0];

    // Mark as read
    @imap_setflag_full($imapStream, (string)$uid, '\\Seen', ST_UID);

    return [
        'uid'         => $uid,
        'subject'     => isset($o->subject) ? imap_utf8($o->subject) : '(no subject)',
        'from'        => isset($o->from) ? imap_utf8($o->from) : 'Unknown sender',
        'to'          => isset($o->to) ? imap_utf8($o->to) : '',
        'date'        => isset($o->date) ? date('M j, Y \a\t g:ia', strtotime($o->date)) : '',
        'body'        => imap_extract_body($imapStream, $uid, $structure),
        'attachments' => imap_list_attachment_parts($structure),
    ];
}

// ---------------- PHPMailer sender ----------------

/**
 * Sends mail as the given email_acc row. $attachments is the raw $_FILES['attachments'] array (or null).
 * Returns true on success, throws PHPMailerException on failure.
 */
function send_mail_as(array $acc, string $toEmail, string $subject, string $body, ?array $attachments = null): void {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = $acc['smtp_host'];
    $mail->Port       = (int)$acc['smtp_port'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $acc['email'];
    $mail->Password   = mail_decrypt($acc['app_password']);
    $mail->SMTPSecure = $acc['encryption'] === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;

    $mail->setFrom($acc['email']);
    $mail->addAddress($toEmail);
    $mail->Subject = $subject;
    $mail->Body    = $body;
    $mail->isHTML(false);

    if ($attachments && !empty($attachments['name'][0])) {
        $count = count($attachments['name']);
        for ($i = 0; $i < $count; $i++) {
            if ($attachments['error'][$i] === UPLOAD_ERR_NO_FILE) continue;
            if ($attachments['error'][$i] !== UPLOAD_ERR_OK) {
                throw new PHPMailerException('Upload error on file: ' . $attachments['name'][$i]);
            }
            if ($attachments['size'][$i] > MAX_ATTACHMENT_SIZE) {
                throw new PHPMailerException('"' . $attachments['name'][$i] . '" exceeds 10 MB.');
            }
            $mail->addAttachment($attachments['tmp_name'][$i], $attachments['name'][$i]);
        }
    }

    $mail->send();
}
