<?php
require 'config.php';
require 'includes/auth.php';
require_login();
$me = current_user();

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT a.*, e.sender_id, e.recipient_id
    FROM attachments a
    JOIN emails e ON e.id = a.email_id
    WHERE a.id = ?
");
$stmt->execute([$id]);
$att = $stmt->fetch();

// Only the sender or the recipient of the parent email may download it
if (!$att || ($att['sender_id'] != $me['user_id'] && $att['recipient_id'] != $me['user_id'])) {
    http_response_code(404);
    die('File not found.');
}

$path = UPLOAD_DIR . $att['stored_name'];
if (!file_exists($path)) {
    http_response_code(404);
    die('File no longer exists on the server.');
}

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($att['original_name']) . '"');
header('Content-Length: ' . filesize($path));
readfile($path);
exit;