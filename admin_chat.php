<?php
// Turn off error reporting for production
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

session_start();
include 'db_connection.php';
include 'session_check.php';

$admin_id = $_SESSION['admin_id'];   // Logged in admin
$admin_role = 'admin';

/*
|--------------------------------------------------------------------------
| AJAX : SEND MESSAGE (with voice support)
|--------------------------------------------------------------------------
*/

if (isset($_POST['action']) && $_POST['action'] == 'send_message') {

    // Clear any output buffers
    while (ob_get_level()) ob_end_clean();
    header('Content-Type: application/json');
    
    $response = ['status' => 'error', 'message' => 'Unknown error'];
    
    try {
        $receiver_id = intval($_POST['receiver_id']);
        $receiver_role = strtolower(trim($_POST['receiver_role']));
        $message = trim($_POST['message']);
        $attachment = '';
        $voice_message = '';

        /* File attachment */
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == 0) {
            $upload_dir = "uploads/chat_files/";
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            // Try to make it writable
            @chmod($upload_dir, 0755);
            
            // Check if directory is writable
            if (!is_writable($upload_dir)) {
                // Try to change permissions
                @chmod($upload_dir, 0777);
                if (!is_writable($upload_dir)) {
                    throw new Exception('Chat files directory is not writable. Please check permissions.');
                }
            }
            
            $filename = time() . "_" . basename($_FILES['attachment']['name']);
            $target_path = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['attachment']['tmp_name'], $target_path)) {
                $attachment = $filename;
            } else {
                throw new Exception('Failed to upload attachment. Error code: ' . $_FILES['attachment']['error']);
            }
        }

        /* Voice message */
        if (isset($_FILES['voice_message']) && $_FILES['voice_message']['error'] == 0) {
            $voice_dir = "uploads/voice/";
            // Create directory if it doesn't exist
            if (!file_exists($voice_dir)) {
                mkdir($voice_dir, 0755, true);
            }
            // Try to make it writable
            @chmod($voice_dir, 0755);
            
            // Check if directory is writable
            if (!is_writable($voice_dir)) {
                // Try to change permissions
                @chmod($voice_dir, 0777);
                if (!is_writable($voice_dir)) {
                    throw new Exception('Voice directory is not writable. Please check permissions.');
                }
            }
            
            $voice_message = time() . "_voice.webm";
            $target_path = $voice_dir . $voice_message;
            
            if (!move_uploaded_file($_FILES['voice_message']['tmp_name'], $target_path)) {
                throw new Exception('Failed to upload voice message. Error code: ' . $_FILES['voice_message']['error']);
            }
        }

        /* Save to DB */
        if ($message != '' || $attachment != '' || $voice_message != '') {
            $stmt = $conn->prepare("
                INSERT INTO chat_messages
                    (sender_id, sender_role, receiver_id, receiver_role, message, attachment, voice_message)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            if (!$stmt) {
                throw new Exception('Database prepare failed: ' . $conn->error);
            }
            
            $stmt->bind_param(
                "isissss",
                $admin_id,
                $admin_role,
                $receiver_id,
                $receiver_role,
                $message,
                $attachment,
                $voice_message
            );
            
            if (!$stmt->execute()) {
                throw new Exception('Database execute failed: ' . $stmt->error);
            }
            
            $response = ['status' => 'success', 'message' => 'Message sent successfully'];
            $stmt->close();
        } else {
            $response = ['status' => 'empty', 'message' => 'No content to send'];
        }
    } catch (Exception $e) {
        $response = ['status' => 'error', 'message' => $e->getMessage()];
    }
    
    echo json_encode($response);
    exit();
}


/*
|--------------------------------------------------------------------------
| AJAX : LOAD MESSAGES
|--------------------------------------------------------------------------
*/

if (isset($_GET['action']) && $_GET['action'] == 'load_messages') {

    // Clear any output buffers
    while (ob_get_level()) ob_end_clean();
    header('Content-Type: application/json');
    
    try {
        $receiver_id = intval($_GET['receiver_id']);
        $receiver_role = strtolower(trim($_GET['receiver_role']));

        $stmt = $conn->prepare("
            SELECT * FROM chat_messages
            WHERE
                (sender_id = ? AND sender_role = ? AND receiver_id = ? AND receiver_role = ?)
                OR
                (sender_id = ? AND sender_role = ? AND receiver_id = ? AND receiver_role = ?)
            ORDER BY created_at ASC
        ");
        
        if (!$stmt) {
            throw new Exception('Database prepare failed: ' . $conn->error);
        }
        
        $stmt->bind_param(
            "isisisis",
            $admin_id,
            $admin_role,
            $receiver_id,
            $receiver_role,
            $receiver_id,
            $receiver_role,
            $admin_id,
            $admin_role
        );
        
        if (!$stmt->execute()) {
            throw new Exception('Database execute failed: ' . $stmt->error);
        }
        
        $result = $stmt->get_result();
        $messages = [];
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }

        echo json_encode($messages);
        exit();
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
        exit();
    }
}

/*
|--------------------------------------------------------------------------
| AJAX : DELETE MESSAGE
|--------------------------------------------------------------------------
*/

if (isset($_POST['action']) && $_POST['action'] == 'delete_message') {

    // Clear any output buffers
    while (ob_get_level()) ob_end_clean();
    header('Content-Type: application/json');
    
    $response = ['status' => 'error', 'message' => 'Unknown error'];
    
    try {
        $message_id = intval($_POST['message_id']);
        
        // First, get the message details to delete files
        $stmt = $conn->prepare("SELECT attachment, voice_message FROM chat_messages WHERE id = ? AND sender_id = ? AND sender_role = ?");
        if (!$stmt) {
            throw new Exception('Database prepare failed: ' . $conn->error);
        }
        
        $stmt->bind_param("iis", $message_id, $admin_id, $admin_role);
        if (!$stmt->execute()) {
            throw new Exception('Database execute failed: ' . $stmt->error);
        }
        
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            // Delete attachment file if exists
            if (!empty($row['attachment'])) {
                $file_path = "uploads/chat_files/" . $row['attachment'];
                if (file_exists($file_path)) {
                    @unlink($file_path);
                }
            }
            
            // Delete voice file if exists
            if (!empty($row['voice_message'])) {
                $voice_path = "uploads/voice/" . $row['voice_message'];
                if (file_exists($voice_path)) {
                    @unlink($voice_path);
                }
            }
        }
        $stmt->close();
        
        // Delete the message from database
        $stmt = $conn->prepare("DELETE FROM chat_messages WHERE id = ? AND sender_id = ? AND sender_role = ?");
        if (!$stmt) {
            throw new Exception('Database prepare failed: ' . $conn->error);
        }
        
        $stmt->bind_param("iis", $message_id, $admin_id, $admin_role);
        
        if (!$stmt->execute()) {
            throw new Exception('Database execute failed: ' . $stmt->error);
        }
        
        if ($stmt->affected_rows > 0) {
            $response = ['status' => 'success', 'message' => 'Message deleted successfully'];
        } else {
            $response = ['status' => 'error', 'message' => 'Message not found or you don\'t have permission to delete it'];
        }
        
        $stmt->close();
    } catch (Exception $e) {
        $response = ['status' => 'error', 'message' => $e->getMessage()];
    }
    
    echo json_encode($response);
    exit();
}

/* Employees list with last message time */
$employees = mysqli_query($conn, "
SELECT
    u.user_id,
    u.name,
    MAX(cm.created_at) AS last_message
FROM users u
LEFT JOIN chat_messages cm
ON (
    (cm.sender_id = u.user_id AND cm.sender_role='employee')
    OR
    (cm.receiver_id = u.user_id AND cm.receiver_role='employee')
)
GROUP BY u.user_id, u.name
ORDER BY
    last_message DESC,
    u.name ASC
");

if (!$employees) {
    die("EMPLOYEE QUERY FAILED: " . mysqli_error($conn));
}

$selected_employee = isset($_GET['employee_id']) ? intval($_GET['employee_id']) : 0;

include 'sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Admin Chat - EbizTech</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
            -webkit-tap-highlight-color: transparent;
        }

        html {
            -webkit-text-size-adjust: 100%;
            height: 100%;
            overflow: hidden;
        }

        body {
            height: 100%;
            overflow: hidden;
            position: relative;
        }

        :root {
            --bg: #faf8f6;
            --card: #ffffff;
            --text: #2c241c;
            --secondary: #7a6a5a;
            --card-border: #f0e8e0;
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
            --card: #1d1815;
            --text: #f0e8e0;
            --secondary: #a89888;
            --card-border: #3a322a;
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
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 20px;
            transition: all .4s ease;
            height: 100vh;
            display: flex;
            flex-direction: column;
            min-height: 0;
        }

        .main-content.expanded {
            margin-left: var(--sidebar-collapsed);
        }

        .chat-wrapper {
            display: flex;
            flex: 1;
            min-height: 0;
            background: var(--card);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 15px 35px var(--orange-shadow);
            border: 2px solid var(--card-border);
            transition: all .35s ease;
            position: relative;
        }

        .chat-wrapper:hover {
            box-shadow: 0 20px 45px var(--orange-shadow);
        }

        /* LEFT PANEL */
        .contacts-panel {
            width: 320px;
            border-right: 2px solid var(--card-border);
            background: var(--card);
            display: flex;
            flex-direction: column;
            height: 100%;
            flex-shrink: 0;
            overflow: hidden;
        }

        .contacts-header {
            padding: 20px 24px;
            font-size: 20px;
            font-weight: 700;
            color: var(--orange-primary);
            border-bottom: 2px solid var(--card-border);
            background: var(--card);
            flex-shrink: 0;
        }

        .contacts-list {
            flex: 1;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            padding: 4px 0;
        }

        .contacts-list::-webkit-scrollbar {
            width: 6px;
        }

        .contacts-list::-webkit-scrollbar-track {
            background: transparent;
        }

        .contacts-list::-webkit-scrollbar-thumb {
            background: var(--orange-light);
            border-radius: 20px;
        }

        .contacts-list::-webkit-scrollbar-thumb:hover {
            background: var(--orange-primary);
        }

        .contact {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 20px;
            cursor: pointer;
            transition: all .3s ease;
            border-bottom: 1px solid transparent;
            position: relative;
        }

        .contact:hover {
            background: var(--orange-subtle);
        }

        .contact.active {
            background: var(--orange-subtle);
            border-left: 4px solid var(--orange-primary);
        }

        .avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--orange-gradient-start), var(--orange-primary));
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: 700;
            font-size: 18px;
            flex-shrink: 0;
            box-shadow: 0 4px 12px var(--orange-shadow);
        }

        .avatar.large {
            width: 50px;
            height: 50px;
            font-size: 20px;
        }

        .contact-info {
            flex: 1;
            min-width: 0;
        }

        .contact-info h4 {
            color: var(--text);
            margin-bottom: 3px;
            font-weight: 600;
            font-size: 15px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .contact-info p {
            color: var(--secondary);
            font-size: 13px;
        }

        .last-msg-time {
            font-size: 11px;
            color: var(--secondary);
            opacity: 0.7;
        }

        /* RIGHT PANEL */
        .chat-panel {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
            height: 100%;
            background: var(--card);
        }

        .chat-header {
            padding: 16px 24px;
            border-bottom: 2px solid var(--card-border);
            background: var(--card);
            flex-shrink: 0;
            min-height: 80px;
            display: flex;
            align-items: center;
        }

        .chat-user {
            display: flex;
            align-items: center;
            gap: 14px;
            min-width: 0;
            width: 100%;
        }

        .chat-user h3 {
            color: var(--text);
            font-weight: 600;
            font-size: 17px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .chat-user span {
            color: #22c55e;
            font-size: 13px;
            font-weight: 500;
        }

        .chat-box {
            flex: 1;
            padding: 20px 24px;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            background: var(--bg);
            min-height: 0;
        }

        .chat-box::-webkit-scrollbar {
            width: 6px;
        }

        .chat-box::-webkit-scrollbar-track {
            background: transparent;
        }

        .chat-box::-webkit-scrollbar-thumb {
            background: var(--orange-light);
            border-radius: 20px;
        }

        .chat-box::-webkit-scrollbar-thumb:hover {
            background: var(--orange-primary);
        }

        .message {
            display: flex;
            margin-bottom: 16px;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .sent {
            justify-content: flex-end;
        }

        .received {
            justify-content: flex-start;
        }

        .bubble {
            max-width: 70%;
            padding: 12px 16px;
            border-radius: 20px;
            line-height: 1.6;
            box-shadow: 0 4px 12px var(--orange-shadow);
            word-wrap: break-word;
            font-size: 15px;
            position: relative;
        }

        .sent .bubble {
            background: linear-gradient(135deg, var(--orange-gradient-start), var(--orange-primary));
            color: white;
            border-bottom-right-radius: 4px;
        }

        .received .bubble {
            background: var(--card);
            color: var(--text);
            border: 2px solid var(--card-border);
            border-bottom-left-radius: 4px;
        }

        .bubble a {
            color: inherit;
            text-decoration: underline;
            font-weight: 500;
        }

        .sent .bubble a {
            color: white;
        }

        .msg-time {
            display: block;
            margin-top: 8px;
            opacity: .7;
            font-size: 11px;
            text-align: right;
        }

        .received .msg-time {
            color: var(--secondary);
        }

        .sent .msg-time {
            color: rgba(255, 255, 255, 0.8);
        }

        /* Attachment styles */
        .attachment-container {
            margin-top: 8px;
            padding: 8px 12px;
            background: rgba(0, 0, 0, .06);
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            max-width: 100%;
            transition: all 0.3s ease;
        }

        .sent .attachment-container {
            background: rgba(255, 255, 255, .15);
        }

        .attachment-container:hover {
            transform: scale(1.02);
        }

        .attachment-container a {
            color: inherit;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            word-break: break-all;
        }

        .attachment-container a:hover {
            text-decoration: underline;
        }

        .attachment-icon {
            font-size: 24px;
            flex-shrink: 0;
        }

        .attachment-info {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .attachment-name {
            font-weight: 500;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .attachment-size {
            font-size: 11px;
            opacity: .7;
        }

        /* Image preview */
        .image-preview {
            margin-top: 8px;
            max-width: 300px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .image-preview:hover {
            transform: scale(1.03);
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
        }

        .image-preview img {
            width: 100%;
            height: auto;
            display: block;
            max-height: 300px;
            object-fit: contain;
            background: #f0f0f0;
        }

        .sent .image-preview {
            background: rgba(255,255,255,0.1);
        }

        .received .image-preview {
            background: var(--bg);
        }

        /* Image viewer modal */
        .image-modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.9);
            animation: fadeIn 0.3s ease;
            cursor: pointer;
        }

        .image-modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .image-modal img {
            max-width: 90%;
            max-height: 90%;
            object-fit: contain;
            border-radius: 8px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.5);
        }

        .image-modal .close-modal {
            position: absolute;
            top: 20px;
            right: 30px;
            color: white;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .image-modal .close-modal:hover {
            transform: scale(1.2);
        }

        /* Delete button */
        .delete-btn {
            background: var(--card);
            border: 2px solid var(--card-border);
            color: #ef4444;
            cursor: pointer;
            font-size: 13px;
            padding: 2px 6px;
            border-radius: 8px;
            transition: all .2s ease;
            opacity: 0;
            position: absolute;
            top: -10px;
            right: -10px;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(0,0,0,.1);
        }

        .message:hover .delete-btn {
            opacity: 1;
        }

        .sent .delete-btn {
            right: -10px;
        }

        .received .delete-btn {
            left: -10px;
            right: auto;
        }

        .delete-btn:hover {
            background: #ef4444;
            color: white;
            border-color: #ef4444;
            transform: scale(1.1);
        }

        /* Voice player */
        .voice-player {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 10px;
            padding: 8px 12px;
            background: rgba(0, 0, 0, .08);
            border-radius: 30px;
            min-width: 200px;
            max-width: 280px;
        }

        .sent .voice-player {
            background: rgba(255, 255, 255, .18);
        }

        .vp-play {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            border: none;
            cursor: pointer;
            background: white;
            color: var(--orange-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            flex-shrink: 0;
            transition: all .2s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .15);
        }

        .sent .vp-play {
            background: rgba(255, 255, 255, .9);
            color: var(--orange-primary);
        }

        .vp-play:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 12px var(--orange-shadow);
        }

        .vp-track {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 4px;
            min-width: 0;
        }

        .vp-bar {
            -webkit-appearance: none;
            appearance: none;
            width: 100%;
            height: 4px;
            border-radius: 10px;
            outline: none;
            cursor: pointer;
            background: linear-gradient(to right, var(--orange-primary) 0%, var(--orange-primary) 0%, rgba(255, 152, 0, .25) 0%);
        }

        .sent .vp-bar {
            background: linear-gradient(to right, white 0%, white 0%, rgba(255, 255, 255, .3) 0%);
        }

        .vp-bar::-webkit-slider-thumb {
            -webkit-appearance: none;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--orange-primary);
            cursor: pointer;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .3);
        }

        .sent .vp-bar::-webkit-slider-thumb {
            background: white;
        }

        .vp-time {
            font-size: 10px;
            opacity: .7;
            letter-spacing: .3px;
        }

        /* Voice record status */
        .voice-status {
            display: none;
            align-items: center;
            gap: 8px;
            padding: 6px 14px;
            background: var(--orange-subtle);
            border-radius: 20px;
            font-size: 13px;
            color: var(--orange-primary);
            white-space: nowrap;
        }

        .voice-status.active {
            display: flex;
        }

        .voice-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--orange-primary);
            animation: blink 1s infinite;
        }

        .rec-timer {
            font-weight: 600;
            min-width: 36px;
        }

        @keyframes blink {
            0%, 100% { opacity: 1 }
            50% { opacity: .2 }
        }

        /* Voice preview */
        #voicePreview {
            display: none;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            background: var(--orange-subtle);
            border-radius: 20px;
            flex: 1;
            min-width: 0;
        }

        #voicePreview.show {
            display: flex;
        }

        #previewPlay {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            border: none;
            cursor: pointer;
            background: linear-gradient(135deg, var(--orange-gradient-start), var(--orange-primary));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            flex-shrink: 0;
            transition: all .2s ease;
            box-shadow: 0 2px 8px var(--orange-shadow);
        }

        #previewPlay:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 12px var(--orange-shadow);
        }

        #previewBar {
            -webkit-appearance: none;
            appearance: none;
            flex: 1;
            height: 4px;
            border-radius: 10px;
            outline: none;
            cursor: pointer;
            background: linear-gradient(to right, var(--orange-primary) 0%, var(--orange-primary) 0%, var(--card-border) 0%);
        }

        #previewBar::-webkit-slider-thumb {
            -webkit-appearance: none;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--orange-primary);
            cursor: pointer;
        }

        #previewTime {
            font-size: 11px;
            color: var(--secondary);
            white-space: nowrap;
            min-width: 60px;
            text-align: right;
        }

        #discardVoice {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            border: none;
            cursor: pointer;
            background: rgba(239, 68, 68, .15);
            color: #ef4444;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            flex-shrink: 0;
            transition: all .2s ease;
        }

        #discardVoice:hover {
            transform: scale(1.1);
            background: rgba(239, 68, 68, .25);
        }

        /* CHAT INPUT */
        .chat-input {
            display: flex;
            gap: 10px;
            padding: 16px 20px;
            border-top: 2px solid var(--card-border);
            background: var(--card);
            align-items: center;
            flex-wrap: nowrap;
            flex-shrink: 0;
            min-height: 70px;
        }

        .chat-input input[type=text] {
            flex: 1;
            min-width: 50px;
            padding: 12px 16px;
            border-radius: 14px;
            border: 2px solid var(--input-border);
            background: var(--input-bg);
            color: var(--text);
            outline: none;
            font-size: 15px;
            transition: all .35s ease;
        }

        .chat-input input[type=text]:focus {
            border-color: var(--orange-primary);
            box-shadow: 0 0 0 4px var(--orange-subtle);
            background: var(--card);
        }

        .chat-input input[type=text]::placeholder {
            color: var(--secondary);
            opacity: 0.6;
        }

        .icon-btn {
            width: 46px;
            height: 46px;
            border-radius: 14px;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            font-size: 20px;
            transition: all .3s ease;
            border: none;
            flex-shrink: 0;
        }

        .icon-btn:hover {
            transform: translateY(-2px);
        }

        .attach-btn {
            background: var(--orange-subtle);
            color: var(--orange-primary);
            border: 2px solid var(--input-border);
            position: relative;
            cursor: pointer;
        }

        .attach-btn:hover {
            background: var(--orange-subtle);
            border-color: var(--orange-primary);
        }

        .attach-btn input[type="file"] {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
            left: 0;
            top: 0;
        }

        .mic-btn {
            background: linear-gradient(135deg, var(--orange-gradient-start), var(--orange-primary));
            color: white;
            box-shadow: 0 4px 12px var(--orange-shadow);
        }

        .mic-btn:hover {
            box-shadow: 0 8px 20px var(--orange-shadow);
        }

        .mic-btn.recording {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            animation: pulse 1s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1) }
            50% { transform: scale(1.08) }
            100% { transform: scale(1) }
        }

        .send-btn {
            width: 50px;
            height: 46px;
            background: linear-gradient(135deg, var(--orange-gradient-start), var(--orange-primary));
            color: white;
            font-size: 18px;
            border: none;
            border-radius: 14px;
            cursor: pointer;
            transition: all .3s ease;
            box-shadow: 0 4px 12px var(--orange-shadow);
            flex-shrink: 0;
        }

        .send-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px var(--orange-shadow);
            background: linear-gradient(135deg, var(--orange-primary), var(--orange-dark));
        }

        .send-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
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

        .no-contact-selected {
            text-align: center;
            color: var(--secondary);
            margin-top: 50px;
            font-size: 17px;
        }

        .back-btn {
            display: none;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: none;
            background: var(--orange-subtle);
            color: var(--orange-primary);
            font-size: 18px;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            flex-shrink: 0;
            transition: all .2s ease;
        }

        .back-btn:hover {
            background: var(--orange-primary);
            color: white;
        }

        /* ============================================
           MOBILE RESPONSIVE - FIXED VISIBILITY
           ============================================ */
        @media(max-width: 768px) {
            .main-content {
                margin-left: 85px;
                padding: 10px;
                height: 100vh;
            }

            .main-content.expanded {
                margin-left: 85px;
            }

            .chat-wrapper {
                border-radius: 16px;
                position: relative;
                overflow: hidden;
            }

            /* Contacts panel - always visible by default */
            .contacts-panel {
                width: 100%;
                height: 100%;
                display: flex !important;
                flex-direction: column;
                position: relative;
                z-index: 1;
                border-right: none;
                border-radius: 16px;
            }

            /* Chat panel - hidden by default, shown when active */
            .chat-panel {
                display: none !important;
                width: 100%;
                height: 100%;
                position: absolute;
                top: 0;
                left: 0;
                z-index: 10;
                background: var(--card);
                border-radius: 16px;
                overflow: hidden;
            }

            /* When mobile-chat-active is added, hide contacts and show chat */
            .chat-wrapper.mobile-chat-active .contacts-panel {
                display: none !important;
            }

            .chat-wrapper.mobile-chat-active .chat-panel {
                display: flex !important;
            }

            .back-btn {
                display: flex !important;
            }

            .contacts-header {
                padding: 16px 20px;
                font-size: 18px;
            }

            .contact {
                padding: 12px 16px;
                gap: 12px;
            }

            .avatar {
                width: 42px;
                height: 42px;
                font-size: 16px;
            }

            .avatar.large {
                width: 44px;
                height: 44px;
                font-size: 18px;
            }

            .chat-header {
                padding: 12px 16px;
                min-height: 64px;
            }

            .chat-user h3 {
                font-size: 15px;
            }

            .chat-box {
                padding: 16px;
            }

            .bubble {
                max-width: 85%;
                padding: 10px 14px;
                font-size: 14px;
            }

            .chat-input {
                padding: 12px 16px;
                gap: 8px;
                min-height: 60px;
            }

            .icon-btn {
                width: 40px;
                height: 40px;
                font-size: 17px;
            }

            .send-btn {
                width: 44px;
                height: 40px;
                font-size: 16px;
            }

            .chat-input input[type=text] {
                padding: 10px 14px;
                font-size: 14px;
            }

            .voice-player {
                min-width: 160px;
                max-width: 200px;
                padding: 6px 10px;
            }

            .vp-play {
                width: 30px;
                height: 30px;
                font-size: 12px;
            }

            #voicePreview {
                padding: 4px 10px;
            }

            #previewPlay {
                width: 28px;
                height: 28px;
                font-size: 12px;
            }
            
            .delete-btn {
                opacity: 1 !important;
                width: 26px;
                height: 26px;
                font-size: 11px;
                top: -8px;
                right: -8px;
            }
            
            .received .delete-btn {
                left: -8px;
                right: auto;
            }

            .image-preview {
                max-width: 200px;
            }
        }

        @media(max-width: 480px) {
            .main-content {
                margin-left: 0;
                padding: 6px;
                height: 100vh;
            }

            .main-content.expanded {
                margin-left: 0;
            }

            .chat-wrapper {
                border-radius: 12px;
            }

            .contacts-panel {
                border-radius: 12px;
            }

            .chat-panel {
                border-radius: 12px;
            }

            .contacts-header {
                padding: 12px 14px;
                font-size: 16px;
            }

            .contact {
                padding: 10px 12px;
                gap: 10px;
            }

            .avatar {
                width: 36px;
                height: 36px;
                font-size: 14px;
            }

            .avatar.large {
                width: 38px;
                height: 38px;
                font-size: 15px;
            }

            .contact-info h4 {
                font-size: 14px;
            }

            .contact-info p {
                font-size: 12px;
            }

            .chat-header {
                padding: 10px 12px;
                min-height: 56px;
            }

            .chat-user {
                gap: 10px;
            }

            .chat-user h3 {
                font-size: 14px;
            }

            .chat-user span {
                font-size: 11px;
            }

            .chat-box {
                padding: 12px;
            }

            .message {
                margin-bottom: 12px;
            }

            .bubble {
                max-width: 90%;
                padding: 8px 12px;
                font-size: 13px;
                border-radius: 16px;
            }

            .msg-time {
                font-size: 10px;
                margin-top: 6px;
            }

            .chat-input {
                padding: 8px 10px;
                gap: 6px;
                min-height: 52px;
            }

            .chat-input input[type=text] {
                padding: 8px 12px;
                font-size: 13px;
                border-radius: 12px;
                min-width: 30px;
            }

            .icon-btn {
                width: 36px;
                height: 36px;
                font-size: 15px;
                border-radius: 12px;
            }

            .send-btn {
                width: 38px;
                height: 36px;
                font-size: 14px;
                border-radius: 12px;
            }

            .voice-player {
                min-width: 140px;
                max-width: 180px;
                padding: 4px 8px;
                gap: 6px;
            }

            .vp-play {
                width: 26px;
                height: 26px;
                font-size: 11px;
            }

            .vp-bar {
                height: 3px;
            }

            .vp-time {
                font-size: 9px;
            }

            .voice-status {
                font-size: 11px;
                padding: 4px 10px;
            }

            #voicePreview {
                padding: 4px 8px;
                gap: 6px;
            }

            #previewPlay {
                width: 24px;
                height: 24px;
                font-size: 11px;
            }

            #previewTime {
                font-size: 10px;
                min-width: 50px;
            }

            #discardVoice {
                width: 22px;
                height: 22px;
                font-size: 12px;
            }

            .back-btn {
                width: 30px;
                height: 30px;
                font-size: 15px;
            }

            .no-contact-selected {
                font-size: 14px;
                margin-top: 30px;
            }

            .image-preview {
                max-width: 150px;
            }
        }

        /* Fix for very small screens */
        @media(max-width: 360px) {
            .main-content {
                padding: 4px;
            }

            .chat-input {
                padding: 4px 6px;
                gap: 4px;
                min-height: 44px;
            }

            .chat-input input[type=text] {
                padding: 6px 8px;
                font-size: 12px;
                border-radius: 10px;
            }

            .icon-btn {
                width: 30px;
                height: 30px;
                font-size: 12px;
                border-radius: 10px;
            }

            .send-btn {
                width: 32px;
                height: 30px;
                font-size: 12px;
                border-radius: 10px;
            }

            .bubble {
                font-size: 12px;
                padding: 6px 10px;
            }

            .chat-box {
                padding: 8px;
            }

            .contacts-header {
                padding: 8px 10px;
                font-size: 14px;
            }

            .contact {
                padding: 6px 8px;
                gap: 6px;
            }

            .avatar {
                width: 28px;
                height: 28px;
                font-size: 11px;
            }

            .chat-header {
                padding: 6px 8px;
                min-height: 44px;
            }

            .chat-user h3 {
                font-size: 12px;
            }
            
            .delete-btn {
                width: 20px;
                height: 20px;
                font-size: 9px;
                top: -6px;
                right: -6px;
            }

            .image-preview {
                max-width: 120px;
            }
        }

        a:focus-visible,
        button:focus-visible,
        input:focus-visible {
            outline: 2px solid var(--orange-primary);
            outline-offset: 2px;
        }
    </style>
</head>

<body>

    <div class="main-content" id="mainContent">
        <div class="chat-wrapper" id="chatWrapper">

            <!-- LEFT: contacts -->
            <div class="contacts-panel">
                <div class="contacts-header">💬 Employees</div>
                <div class="contacts-list" id="contactsList">
                    <?php while ($emp = mysqli_fetch_assoc($employees)): ?>
                        <div class="contact <?= ($selected_employee == $emp['user_id']) ? 'active' : ''; ?>"
                             data-id="<?= $emp['user_id'] ?>"
                             data-name="<?= htmlspecialchars($emp['name'], ENT_QUOTES) ?>"
                             data-role="employee"
                             onclick="selectContact(
                                 <?= $emp['user_id'] ?>,
                                 '<?= htmlspecialchars($emp['name'], ENT_QUOTES) ?>',
                                 'employee',
                                 this
                             )">
                            <div class="avatar"><?= strtoupper(substr($emp['name'], 0, 1)) ?></div>
                            <div class="contact-info">
                                <h4><?= htmlspecialchars($emp['name']) ?></h4>
                                <p>Employee</p>
                                <?php if ($emp['last_message']): ?>
                                    <span class="last-msg-time">Last: <?= date('h:i A', strtotime($emp['last_message'])) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <!-- RIGHT: chat -->
            <div class="chat-panel">

                <div class="chat-header">
                    <div class="chat-user">
                        <button class="back-btn" id="backBtn" type="button" title="Back to employees" aria-label="Back to employees">←</button>
                        <div class="avatar large" id="headerAvatar">?</div>
                        <div style="min-width:0; flex:1;">
                            <h3 id="headerName">Select an employee</h3>
                            <span>🟢 Available</span>
                        </div>
                    </div>
                </div>

                <div class="chat-box" id="chatBox">
                    <div class="no-contact-selected">
                        💬 Select an employee to start chatting.
                    </div>
                </div>

                <!-- Input bar -->
                <div class="chat-input">

                    <!-- Attach file -->
                    <label class="icon-btn attach-btn" title="Attach file">
                        📎
                        <input type="file" id="attachment" hidden>
                    </label>

                    <!-- Mic button -->
                    <button class="icon-btn mic-btn" id="micBtn" type="button" title="Record voice message">🎤</button>

                    <!-- Live recording indicator -->
                    <div class="voice-status" id="voiceStatus">
                        <span class="voice-dot"></span>
                        Recording&#8230;
                        <span class="rec-timer" id="recTimer">0:00</span>
                        &mdash; tap mic to stop
                    </div>

                    <!-- Voice preview player -->
                    <div id="voicePreview">
                        <button id="previewPlay" type="button">&#9654;</button>
                        <input type="range" id="previewBar" min="0" max="100" value="0" step="0.1">
                        <span id="previewTime">0:00 / 0:00</span>
                        <button id="discardVoice" type="button" title="Discard">✕</button>
                    </div>

                    <input type="text" id="message" placeholder="Type your message&#8230;">

                    <button class="icon-btn send-btn" id="sendBtn" type="button">📤</button>
                </div>

            </div>
        </div>
    </div>

    <!-- Image Viewer Modal -->
    <div class="image-modal" id="imageModal" onclick="closeImageModal()">
        <span class="close-modal">&times;</span>
        <img id="modalImage" src="" alt="Preview">
    </div>

    <script>
        // Theme and sidebar sync
        document.addEventListener("DOMContentLoaded", function () {
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
                sidebarLogo.addEventListener("click", function () {
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
            window.addEventListener('storage', function (e) {
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

        const LOGGED_ID = <?= (int) $admin_id ?>;
        const LOGGED_ROLE = 'admin';

        let currentReceiverId = <?= $selected_employee ?: 0 ?>;
        let currentReceiverRole = 'employee';
        let pollTimer = null;

        let mediaRecorder = null;
        let activeStream = null;
        let audioChunks = [];
        let voiceBlob = null;
        let recSeconds = 0;
        let recInterval = null;

        let previewAudio = null;

        const micBtn = document.getElementById('micBtn');
        const voiceStatus = document.getElementById('voiceStatus');
        const recTimer = document.getElementById('recTimer');
        const voicePreview = document.getElementById('voicePreview');
        const previewPlay = document.getElementById('previewPlay');
        const previewBar = document.getElementById('previewBar');
        const previewTime = document.getElementById('previewTime');
        const discardVoice = document.getElementById('discardVoice');
        const messageInput = document.getElementById('message');
        const sendBtn = document.getElementById('sendBtn');
        const chatBox = document.getElementById('chatBox');
        const attachment = document.getElementById('attachment');
        const chatWrapper = document.getElementById('chatWrapper');
        const backBtn = document.getElementById('backBtn');
        const imageModal = document.getElementById('imageModal');
        const modalImage = document.getElementById('modalImage');

        function fmt(sec) {
            sec = Math.floor(sec || 0);
            return Math.floor(sec / 60) + ':' + String(sec % 60).padStart(2, '0');
        }

        function getFileIcon(filename) {
            const ext = filename.split('.').pop().toLowerCase();
            const icons = {
                'pdf': '📄',
                'doc': '📝',
                'docx': '📝',
                'xls': '📊',
                'xlsx': '📊',
                'ppt': '📽️',
                'pptx': '📽️',
                'jpg': '🖼️',
                'jpeg': '🖼️',
                'png': '🖼️',
                'gif': '🖼️',
                'svg': '🖼️',
                'webp': '🖼️',
                'bmp': '🖼️',
                'ico': '🖼️',
                'mp4': '🎬',
                'avi': '🎬',
                'mov': '🎬',
                'mp3': '🎵',
                'wav': '🎵',
                'zip': '📦',
                'rar': '📦',
                '7z': '📦',
                'txt': '📃',
                'csv': '📊',
                'json': '📋',
                'xml': '📋',
                'html': '🌐',
                'css': '🎨',
                'js': '📜',
                'php': '💻',
                'exe': '⚙️',
                'msi': '⚙️'
            };
            return icons[ext] || '📎';
        }

        function isImageFile(filename) {
            const ext = filename.split('.').pop().toLowerCase();
            const imageExts = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'bmp', 'ico'];
            return imageExts.includes(ext);
        }

        function formatFileSize(bytes) {
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / 1048576).toFixed(1) + ' MB';
        }

        function openImageModal(imageSrc) {
            modalImage.src = imageSrc;
            imageModal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeImageModal() {
            imageModal.classList.remove('active');
            document.body.style.overflow = '';
        }

        // Close modal on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeImageModal();
            }
        });

        function deleteMessage(messageId, element) {
            if (!confirm('Are you sure you want to delete this message?')) {
                return;
            }
            
            const fd = new FormData();
            fd.append('action', 'delete_message');
            fd.append('message_id', messageId);
            
            fetch('admin_chat.php', {
                method: 'POST',
                body: fd
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text();
            })
            .then(text => {
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    console.error('Failed to parse JSON:', e);
                    throw new Error('Server returned invalid JSON');
                }
                
                if (data.status === 'success') {
                    // Remove the message from DOM with animation
                    const messageElement = element.closest('.message');
                    if (messageElement) {
                        messageElement.style.transition = 'all 0.3s ease';
                        messageElement.style.opacity = '0';
                        messageElement.style.transform = 'translateX(-20px)';
                        setTimeout(() => {
                            messageElement.remove();
                            // Check if no messages left
                            const remainingMessages = chatBox.querySelectorAll('.message');
                            if (remainingMessages.length === 0) {
                                chatBox.innerHTML = '<div class="no-contact-selected">💬 No messages yet. Start the conversation!</div>';
                            }
                        }, 300);
                    }
                } else {
                    alert('Error deleting message: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Delete error:', error);
                alert('Error deleting message: ' + error.message);
            });
        }

        function selectContact(id, name, role, el, switchView = true) {
            currentReceiverId = id;
            currentReceiverRole = role;
            document.getElementById('headerName').textContent = name;
            document.getElementById('headerAvatar').textContent = name.charAt(0).toUpperCase();
            document.querySelectorAll('.contact').forEach(c => c.classList.remove('active'));
            el.classList.add('active');

            // On mobile, swap from the contacts list to the full-screen chat view
            if (switchView && chatWrapper) {
                chatWrapper.classList.add('mobile-chat-active');
            }
            
            // Clear input fields when switching contacts
            messageInput.value = '';
            attachment.value = '';
            clearVoicePreview();
            voiceBlob = null;
            
            // Reset lastHTML to force refresh
            lastHTML = '';
            loadMessages();
            
            clearInterval(pollTimer);
            pollTimer = setInterval(() => {
                if (!isAudioPlaying()) {
                    loadMessages();
                }
            }, 2000);
        }

        function buildPlayerHTML(src, isSent) {
            const uid = 'vp_' + Math.random().toString(36).slice(2, 8);
            return `
        <div class="voice-player" data-uid="${uid}">
            <button class="vp-play" onclick="togglePlay('${uid}',this)" type="button">&#9654;</button>
            <div class="vp-track">
                <input class="vp-bar" type="range" min="0" max="100" value="0" step="0.1"
                       oninput="seekAudio('${uid}',this.value)">
                <span class="vp-time" id="time_${uid}">0:00 / 0:00</span>
            </div>
        </div>
        <audio id="audio_${uid}" src="${src}" preload="metadata"
               ontimeupdate="updateBar('${uid}')"
               onloadedmetadata="initBar('${uid}')"
               onended="resetPlay('${uid}')">
        </audio>`;
        }

        function togglePlay(uid, btn) {
            const audio = document.getElementById('audio_' + uid);
            if (!audio) return;
            if (audio.currentTime >= audio.duration) {
                audio.currentTime = 0;
            }
            if (audio.paused) {
                audio.play();
                btn.innerHTML = '&#9646;&#9646;';
            } else {
                audio.pause();
                btn.innerHTML = '&#9654;';
            }
        }

        function updateBar(uid) {
            const audio = document.getElementById('audio_' + uid);
            const bar = document.querySelector(`[data-uid="${uid}"] .vp-bar`);
            const label = document.getElementById('time_' + uid);
            if (!audio || !bar) return;
            const pct = audio.duration ? (audio.currentTime / audio.duration) * 100 : 0;
            bar.value = pct;
            bar.style.background = `linear-gradient(to right,var(--orange-primary) ${pct}%,rgba(255,152,0,.25) ${pct}%)`;
            if (label) label.textContent = fmt(audio.currentTime) + ' / ' + fmt(audio.duration);
        }

        function initBar(uid) {
            const audio = document.getElementById('audio_' + uid);
            const label = document.getElementById('time_' + uid);
            if (audio && label) label.textContent = '0:00 / ' + fmt(audio.duration);
        }

        function seekAudio(uid, val) {
            const audio = document.getElementById('audio_' + uid);
            if (audio && audio.duration) audio.currentTime = (val / 100) * audio.duration;
        }

        function resetPlay(uid) {
            const btn = document.querySelector(`[data-uid="${uid}"] .vp-play`);
            if (btn) btn.innerHTML = '&#9654;';
        }

        function isAudioPlaying() {
            const audios = document.querySelectorAll('audio');
            for (let audio of audios) {
                if (!audio.paused && !audio.ended) {
                    return true;
                }
            }
            return false;
        }

        let lastHTML = "";

        function loadMessages() {
            if (!currentReceiverId) {
                chatBox.innerHTML = '<div class="no-contact-selected">💬 Select an employee to start chatting.</div>';
                return;
            }
            
            const url = `admin_chat.php?action=load_messages&receiver_id=${currentReceiverId}&receiver_role=${currentReceiverRole}`;
            
            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.text();
                })
                .then(text => {
                    let messages;
                    try {
                        messages = JSON.parse(text);
                    } catch (e) {
                        console.error('Failed to parse JSON:', e);
                        chatBox.innerHTML = '<div class="no-contact-selected">⚠️ Error loading messages. Invalid response from server.</div>';
                        return;
                    }
                    
                    if (messages && messages.error) {
                        console.error('Server error:', messages.error);
                        chatBox.innerHTML = '<div class="no-contact-selected">⚠️ ' + messages.error + '</div>';
                        return;
                    }
                    
                    let html = '';
                    if (!messages || messages.length === 0) {
                        html = '<div class="no-contact-selected">💬 No messages yet. Start the conversation!</div>';
                    } else {
                        messages.forEach(msg => {
                            const isSent = msg.sender_id == LOGGED_ID;
                            const cls = isSent ? 'sent' : 'received';
                            
                            // Attachment HTML with proper preview
                            let attachHtml = '';
                            if (msg.attachment) {
                                const filePath = 'uploads/chat_files/' + msg.attachment;
                                const fileName = msg.attachment.substring(msg.attachment.indexOf('_') + 1);
                                
                                // Check if it's an image
                                if (isImageFile(msg.attachment)) {
                                    attachHtml = `
                                        <div class="image-preview" onclick="openImageModal('${filePath}')">
                                            <img src="${filePath}" alt="${fileName}" loading="lazy">
                                        </div>
                                        <div class="attachment-container">
                                            <a href="${filePath}" target="_blank" download>
                                                <span class="attachment-icon">🖼️</span>
                                                <span class="attachment-info">
                                                    <span class="attachment-name">${fileName}</span>
                                                    <span class="attachment-size">Click to download</span>
                                                </span>
                                            </a>
                                        </div>
                                    `;
                                } else {
                                    const fileIcon = getFileIcon(msg.attachment);
                                    attachHtml = `
                                        <div class="attachment-container">
                                            <a href="${filePath}" target="_blank" download>
                                                <span class="attachment-icon">${fileIcon}</span>
                                                <span class="attachment-info">
                                                    <span class="attachment-name">${fileName}</span>
                                                    <span class="attachment-size">Click to download</span>
                                                </span>
                                            </a>
                                        </div>
                                    `;
                                }
                            }
                            
                            // Voice message HTML
                            const voiceHtml = msg.voice_message ?
                                buildPlayerHTML('uploads/voice/' + msg.voice_message, isSent) : '';
                            
                            const msgText = msg.message ? msg.message : '';
                            
                            // Delete button (only for sent messages)
                            const deleteBtn = isSent ? 
                                `<button class="delete-btn" onclick="deleteMessage(${msg.id}, this)" title="Delete message">🗑️</button>` : '';
                            
                            html += `
                                <div class="message ${cls}">
                                    <div class="bubble">
                                        ${msgText}
                                        ${attachHtml}
                                        ${voiceHtml}
                                        <span class="msg-time">${msg.created_at}</span>
                                        ${deleteBtn}
                                    </div>
                                </div>`;
                        });
                    }
                    
                    if (html !== lastHTML) {
                        chatBox.innerHTML = html;
                        chatBox.scrollTop = chatBox.scrollHeight;
                        lastHTML = html;
                    }
                })
                .catch(error => {
                    console.error('Error loading messages:', error);
                    chatBox.innerHTML = '<div class="no-contact-selected">⚠️ Error loading messages. Please refresh the page.</div>';
                });
        }

        function sendMessage() {
            if (!currentReceiverId) {
                alert('Please select an employee first.');
                return;
            }
            
            const text = messageInput.value.trim();
            const file = attachment.files[0];
            
            if (!text && !file && !voiceBlob) {
                console.log('No message content to send');
                return;
            }

            const fd = new FormData();
            fd.append('action', 'send_message');
            fd.append('receiver_id', currentReceiverId);
            fd.append('receiver_role', currentReceiverRole);
            fd.append('message', text);
            if (file) fd.append('attachment', file);
            if (voiceBlob) fd.append('voice_message', voiceBlob, 'voice.webm');

            // Disable send button to prevent double submission
            sendBtn.disabled = true;
            sendBtn.textContent = '⏳';

            fetch('admin_chat.php', {
                method: 'POST',
                body: fd
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.text();
                })
                .then(text => {
                    let data;
                    try {
                        data = JSON.parse(text);
                    } catch (e) {
                        console.error('Failed to parse JSON:', e);
                        throw new Error('Server returned invalid JSON. Response starts with: ' + text.substring(0, 100));
                    }
                    
                    if (data.status === 'success') {
                        // Clear input fields
                        messageInput.value = '';
                        attachment.value = '';
                        clearVoicePreview();
                        voiceBlob = null;
                        // Reload messages
                        loadMessages();
                    } else {
                        alert('Error sending message: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Send error:', error);
                    alert('Error sending message: ' + error.message);
                })
                .finally(() => {
                    sendBtn.disabled = false;
                    sendBtn.textContent = '📤';
                });
        }

        if (backBtn) {
            backBtn.addEventListener('click', () => {
                // Stop an in-progress recording so it doesn't keep running
                if (mediaRecorder && mediaRecorder.state === 'recording') {
                    mediaRecorder.stop();
                }
                if (chatWrapper) chatWrapper.classList.remove('mobile-chat-active');
            });
        }

        sendBtn.addEventListener('click', sendMessage);
        messageInput.addEventListener('keydown', e => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        micBtn.addEventListener('click', async () => {
            if (mediaRecorder && mediaRecorder.state === 'recording') {
                mediaRecorder.stop();
                return;
            }
            try {
                activeStream = await navigator.mediaDevices.getUserMedia({ audio: true });
                audioChunks = [];
                const mimeType = MediaRecorder.isTypeSupported('audio/webm;codecs=opus') ?
                    'audio/webm;codecs=opus' :
                    MediaRecorder.isTypeSupported('audio/webm') ? 'audio/webm' : '';
                mediaRecorder = mimeType ? new MediaRecorder(activeStream, { mimeType }) : new MediaRecorder(activeStream);

                mediaRecorder.ondataavailable = e => {
                    if (e.data && e.data.size > 0) audioChunks.push(e.data);
                };

                mediaRecorder.onstop = () => {
                    voiceBlob = new Blob(audioChunks, { type: mediaRecorder.mimeType || 'audio/webm' });
                    activeStream.getTracks().forEach(t => t.stop());
                    activeStream = null;
                    clearInterval(recInterval);
                    recInterval = null;
                    micBtn.classList.remove('recording');
                    micBtn.innerHTML = '🎤';
                    voiceStatus.classList.remove('active');
                    showVoicePreview(voiceBlob);
                };

                mediaRecorder.start(250);
                recSeconds = 0;
                recTimer.textContent = '0:00';
                recInterval = setInterval(() => {
                    recSeconds++;
                    recTimer.textContent = fmt(recSeconds);
                }, 1000);
                micBtn.classList.add('recording');
                micBtn.innerHTML = '⏹';
                voiceStatus.classList.add('active');
            } catch (err) {
                console.error('Microphone error:', err);
                alert('Microphone permission denied. Please allow microphone access in your browser settings.');
            }
        });

        function showVoicePreview(blob) {
            const url = URL.createObjectURL(blob);
            previewAudio = new Audio(url);
            previewAudio.onloadedmetadata = () => {
                previewTime.textContent = '0:00 / ' + fmt(previewAudio.duration);
                previewBar.value = 0;
            };
            previewAudio.ontimeupdate = () => {
                if (!previewAudio.duration) return;
                const pct = (previewAudio.currentTime / previewAudio.duration) * 100;
                previewBar.value = pct;
                previewBar.style.background = `linear-gradient(to right,var(--orange-primary) ${pct}%,var(--card-border) ${pct}%)`;
                previewTime.textContent = fmt(previewAudio.currentTime) + ' / ' + fmt(previewAudio.duration);
            };
            previewAudio.onended = () => {
                previewPlay.innerHTML = '▶';
                previewBar.value = 0;
            };
            voicePreview.classList.add('show');
            messageInput.style.display = 'none';
        }

        previewPlay.addEventListener('click', () => {
            if (!previewAudio) return;
            if (previewAudio.paused) {
                previewAudio.play();
                previewPlay.innerHTML = '⏸';
            } else {
                previewAudio.pause();
                previewPlay.innerHTML = '▶';
            }
        });

        previewBar.addEventListener('input', () => {
            if (previewAudio && previewAudio.duration) {
                previewAudio.currentTime = (previewBar.value / 100) * previewAudio.duration;
            }
        });

        discardVoice.addEventListener('click', () => {
            clearVoicePreview();
            voiceBlob = null;
        });

        function clearVoicePreview() {
            if (previewAudio) {
                previewAudio.pause();
                previewAudio = null;
            }
            voicePreview.classList.remove('show');
            messageInput.style.display = '';
            previewPlay.innerHTML = '▶';
            previewBar.value = 0;
            previewTime.textContent = '0:00 / 0:00';
        }

        // Make functions globally accessible
        window.selectContact = selectContact;
        window.togglePlay = togglePlay;
        window.updateBar = updateBar;
        window.initBar = initBar;
        window.seekAudio = seekAudio;
        window.resetPlay = resetPlay;
        window.deleteMessage = deleteMessage;
        window.openImageModal = openImageModal;
        window.closeImageModal = closeImageModal;

        // Auto-select first contact if any
        <?php if ($selected_employee > 0): ?>
            const firstContact = document.querySelector('.contact.active');
            if (firstContact) {
                if (chatWrapper) chatWrapper.classList.add('mobile-chat-active');
                loadMessages();
            }
        <?php else: ?>
            const firstContact = document.querySelector('.contact');
            if (firstContact) {
                selectContact(
                    parseInt(firstContact.dataset.id, 10),
                    firstContact.dataset.name,
                    firstContact.dataset.role,
                    firstContact,
                    false // background selection only — don't jump to the full-screen chat view
                );
            }
        <?php endif; ?>
    </script>

</body>

</html>