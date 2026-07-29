<?php
// Turn off error reporting for production, but log errors
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

session_start();
include "db_connection.php";
include 'session_check.php';

/*
|--------------------------------------------------------------------------
| IDENTIFY LOGGED IN USER
|--------------------------------------------------------------------------
*/

if (isset($_SESSION['employee_id'])) {
    $logged_id   = $_SESSION['employee_id'];
    $logged_role = 'employee';
} elseif (isset($_SESSION['admin_id'])) {
    $logged_id   = $_SESSION['admin_id'];
    $logged_role = 'admin';
} else {
    header("Location: login.php");
    exit();
}


/*
|--------------------------------------------------------------------------
| AJAX : SEND MESSAGE
|--------------------------------------------------------------------------
*/

if (isset($_POST['action']) && $_POST['action'] == 'send_message') {

    // Clear any output buffers
    while (ob_get_level()) ob_end_clean();
    header('Content-Type: application/json');
    
    $response = ['status' => 'error', 'message' => 'Unknown error'];
    
    try {
        $receiver_id   = intval($_POST['receiver_id']);
        $receiver_role = strtolower(trim($_POST['receiver_role']));
        $message       = trim($_POST['message']);
        $attachment    = '';
        $voice_message = '';

        /* File attachment */
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == 0) {
            $upload_dir = "uploads/chat/";
            if (!is_dir($upload_dir)) {
                if (!mkdir($upload_dir, 0777, true)) {
                    throw new Exception('Failed to create upload directory: ' . $upload_dir);
                }
            }
            $filename = time() . "_" . basename($_FILES['attachment']['name']);
            if (move_uploaded_file($_FILES['attachment']['tmp_name'], $upload_dir . $filename)) {
                $attachment = $filename;
            } else {
                throw new Exception('Failed to upload attachment');
            }
        }

        /* Voice message */
        if (isset($_FILES['voice_message']) && $_FILES['voice_message']['error'] == 0) {
            $voice_dir = "uploads/voice/";
            if (!is_dir($voice_dir)) {
                if (!mkdir($voice_dir, 0777, true)) {
                    throw new Exception('Failed to create voice directory: ' . $voice_dir);
                }
            }
            $voice_message = time() . "_voice.webm";
            if (!move_uploaded_file($_FILES['voice_message']['tmp_name'], $voice_dir . $voice_message)) {
                throw new Exception('Failed to upload voice message');
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
                $logged_id,
                $logged_role,
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
    
    $response = [];
    
    try {
        $receiver_id   = intval($_GET['receiver_id']);
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
            $logged_id,
            $logged_role,
            $receiver_id,
            $receiver_role,
            $receiver_id,
            $receiver_role,
            $logged_id,
            $logged_role
        );
        
        if (!$stmt->execute()) {
            throw new Exception('Database execute failed: ' . $stmt->error);
        }
        
        $result   = $stmt->get_result();
        $messages = [];
        while ($row = $result->fetch_assoc()) $messages[] = $row;

        echo json_encode($messages);
        exit();
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
        exit();
    }
}


/*
|--------------------------------------------------------------------------
| LOAD CHAT CONTACTS
|--------------------------------------------------------------------------
*/

$contacts = [];

if ($logged_role == 'employee') {
    $result = mysqli_query($conn, "SELECT user_id, name FROM users WHERE role='admin' ORDER BY name");
    while ($row = mysqli_fetch_assoc($result)) {
        $row['role'] = 'admin';
        $contacts[] = $row;
    }
}

if ($logged_role == 'admin') {
    $result = mysqli_query($conn, "SELECT user_id, name FROM users WHERE role='employee' ORDER BY name");
    while ($row = mysqli_fetch_assoc($result)) {
        $row['role'] = 'employee';
        $contacts[] = $row;
    }
}

include "sidebar1.php";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat</title>
    <style>
        /* ... (keep your existing styles, they're fine) ... */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
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
            min-height: 100vh;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 35px;
            transition: all .4s ease;
            min-height: 100vh;
        }

        .main-content.expanded {
            margin-left: var(--sidebar-collapsed);
        }

        .chat-wrapper {
            display: flex;
            height: calc(100vh - 70px);
            background: var(--card);
            border-radius: 28px;
            overflow: hidden;
            box-shadow: 0 15px 35px var(--orange-shadow);
            border: 2px solid var(--card-border);
            transition: all .35s ease;
        }

        .chat-wrapper:hover {
            box-shadow: 0 20px 45px var(--orange-shadow);
        }

        /* LEFT PANEL */
        .contacts-panel {
            width: 320px;
            border-right: 2px solid var(--card-border);
            background: var(--card);
            overflow-y: auto;
        }

        .contacts-panel::-webkit-scrollbar {
            width: 6px;
        }

        .contacts-panel::-webkit-scrollbar-track {
            background: transparent;
        }

        .contacts-panel::-webkit-scrollbar-thumb {
            background: var(--orange-light);
            border-radius: 20px;
        }

        .contacts-panel::-webkit-scrollbar-thumb:hover {
            background: var(--orange-primary);
        }

        .contacts-header {
            padding: 25px;
            font-size: 22px;
            font-weight: 700;
            color: var(--orange-primary);
            border-bottom: 2px solid var(--card-border);
            background: var(--card);
        }

        .contact {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 18px 22px;
            cursor: pointer;
            transition: all .3s ease;
            border-bottom: 1px solid transparent;
        }

        .contact:hover {
            background: var(--orange-subtle);
        }

        .contact.active {
            background: var(--orange-subtle);
            border-left: 4px solid var(--orange-primary);
        }

        .avatar {
            width: 50px;
            height: 50px;
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
            width: 55px;
            height: 55px;
            font-size: 20px;
        }

        .contact-info {
            flex: 1;
        }

        .contact-info h4 {
            color: var(--text);
            margin-bottom: 5px;
            font-weight: 600;
        }

        .contact-info p {
            color: var(--secondary);
            font-size: 13px;
        }

        /* RIGHT PANEL */
        .chat-panel {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .chat-header {
            padding: 20px 25px;
            border-bottom: 2px solid var(--card-border);
            background: var(--card);
        }

        .chat-user {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .chat-user h3 {
            color: var(--text);
            font-weight: 600;
        }

        .chat-user span {
            color: #22c55e;
            font-size: 13px;
            font-weight: 500;
        }

        .chat-box {
            flex: 1;
            padding: 25px;
            overflow-y: auto;
            background: var(--bg);
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
            margin-bottom: 18px;
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
            padding: 14px 18px;
            border-radius: 22px;
            line-height: 1.6;
            box-shadow: 0 6px 15px var(--orange-shadow);
            word-wrap: break-word;
        }

        .sent .bubble {
            background: linear-gradient(135deg, var(--orange-gradient-start), var(--orange-primary));
            color: white;
            border-bottom-right-radius: 6px;
        }

        .received .bubble {
            background: var(--card);
            color: var(--text);
            border: 2px solid var(--card-border);
            border-bottom-left-radius: 6px;
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
            margin-top: 10px;
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

        /* ── custom voice player (in bubbles) ── */
        .voice-player {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 10px;
            padding: 10px 14px;
            background: rgba(0, 0, 0, .08);
            border-radius: 30px;
            min-width: 240px;
            max-width: 320px;
        }

        .sent .voice-player {
            background: rgba(255, 255, 255, .18);
        }

        .vp-play {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            border: none;
            cursor: pointer;
            background: white;
            color: var(--orange-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
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
            gap: 5px;
        }

        .vp-bar {
            -webkit-appearance: none;
            appearance: none;
            width: 100%;
            height: 5px;
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
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: var(--orange-primary);
            cursor: pointer;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .3);
        }

        .sent .vp-bar::-webkit-slider-thumb {
            background: white;
        }

        .vp-bar::-moz-range-thumb {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            border: none;
            background: var(--orange-primary);
            cursor: pointer;
        }

        .vp-time {
            font-size: 11px;
            opacity: .7;
            letter-spacing: .3px;
        }

        /* ── VOICE RECORD STATUS (in input bar) ── */
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

        /* ── VOICE PREVIEW (in input bar after recording) ── */
        #voicePreview {
            display: none;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            background: var(--orange-subtle);
            border-radius: 20px;
            flex: 1;
            min-width: 0;
        }

        #voicePreview.show {
            display: flex;
        }

        #previewPlay {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            border: none;
            cursor: pointer;
            background: linear-gradient(135deg, var(--orange-gradient-start), var(--orange-primary));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
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
            height: 5px;
            border-radius: 10px;
            outline: none;
            cursor: pointer;
            background: linear-gradient(to right, var(--orange-primary) 0%, var(--orange-primary) 0%, var(--card-border) 0%);
        }

        #previewBar::-webkit-slider-thumb {
            -webkit-appearance: none;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: var(--orange-primary);
            cursor: pointer;
        }

        #previewTime {
            font-size: 12px;
            color: var(--secondary);
            white-space: nowrap;
            min-width: 70px;
            text-align: right;
        }

        #discardVoice {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            border: none;
            cursor: pointer;
            background: rgba(239, 68, 68, .15);
            color: #ef4444;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
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
            gap: 12px;
            padding: 20px;
            border-top: 2px solid var(--card-border);
            background: var(--card);
            align-items: center;
            flex-wrap: wrap;
        }

        .chat-input input[type=text] {
            flex: 1;
            padding: 14px 18px;
            border-radius: 16px;
            border: 2px solid var(--input-border);
            background: var(--input-bg);
            color: var(--text);
            outline: none;
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
            width: 50px;
            height: 50px;
            border-radius: 16px;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            font-size: 22px;
            transition: all .3s ease;
            border: none;
        }

        .icon-btn:hover {
            transform: translateY(-3px);
        }

        .attach-btn {
            background: var(--orange-subtle);
            color: var(--orange-primary);
            border: 2px solid var(--input-border);
        }

        .attach-btn:hover {
            background: var(--orange-subtle);
            border-color: var(--orange-primary);
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
            50% { transform: scale(1.1) }
            100% { transform: scale(1) }
        }

        .send-btn {
            width: 55px;
            height: 50px;
            background: linear-gradient(135deg, var(--orange-gradient-start), var(--orange-primary));
            color: white;
            font-size: 20px;
            border: none;
            border-radius: 16px;
            cursor: pointer;
            transition: all .3s ease;
            box-shadow: 0 4px 12px var(--orange-shadow);
        }

        .send-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px var(--orange-shadow);
            background: linear-gradient(135deg, var(--orange-primary), var(--orange-dark));
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

        @media(max-width: 768px) {
            .main-content {
                margin-left: 85px;
                padding: 20px;
            }

            .main-content.expanded {
                margin-left: 85px;
            }

            .contacts-panel {
                width: 90px;
            }

            .contact-info {
                display: none;
            }

            .chat-wrapper {
                height: calc(100vh - 40px);
            }

            .chat-input {
                padding: 15px;
                gap: 8px;
            }

            .icon-btn {
                width: 42px;
                height: 42px;
                font-size: 18px;
            }

            .send-btn {
                width: 48px;
                height: 42px;
                font-size: 17px;
            }
        }

        @media(max-width: 480px) {
            .main-content {
                margin-left: 0;
                padding: 10px;
            }

            .main-content.expanded {
                margin-left: 0;
            }

            .contacts-panel {
                width: 70px;
            }

            .avatar {
                width: 40px;
                height: 40px;
                font-size: 14px;
            }

            .chat-header {
                padding: 15px;
            }

            .chat-box {
                padding: 15px;
            }

            .bubble {
                max-width: 90%;
                padding: 12px 14px;
                font-size: 14px;
            }

            .voice-player {
                min-width: 180px;
                max-width: 220px;
            }
        }
    </style>
</head>

<body>

    <div class="main-content" id="mainContent">
        <div class="chat-wrapper">

            <!-- LEFT: contacts -->
            <div class="contacts-panel">
                <div class="contacts-header">💬 <?= $logged_role === 'admin' ? 'Employees' : 'Admin Contacts' ?></div>

                <?php foreach ($contacts as $contact): ?>
                    <div class="contact"
                        onclick="selectContact(
                     <?= $contact['user_id'] ?>,
                     '<?= htmlspecialchars($contact['name'], ENT_QUOTES) ?>',
                     '<?= $contact['role'] ?>',
                     this
                 )">
                        <div class="avatar"><?= strtoupper(substr($contact['name'], 0, 1)) ?></div>
                        <div class="contact-info">
                            <h4><?= htmlspecialchars($contact['name']) ?></h4>
                            <p><?= ucfirst($contact['role']) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- RIGHT: chat -->
            <div class="chat-panel">

                <div class="chat-header">
                    <div class="chat-user">
                        <div class="avatar large" id="headerAvatar">?</div>
                        <div>
                            <h3 id="headerName">Select a contact</h3>
                            <span>🟢 Available</span>
                        </div>
                    </div>
                </div>

                <div class="chat-box" id="chatBox">
                    <div style="text-align:center;color:var(--secondary);margin-top:50px;font-size:18px;">
                        💬 Select a contact to start chatting.
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

    <script>
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

        const LOGGED_ID = <?= (int) $logged_id ?>;

        let currentReceiverId = "";
        let currentReceiverRole = "";
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

        function fmt(sec) {
            sec = Math.floor(sec || 0);
            return Math.floor(sec / 60) + ':' + String(sec % 60).padStart(2, '0');
        }

        function selectContact(id, name, role, el) {
            currentReceiverId = id;
            currentReceiverRole = role;
            document.getElementById('headerName').textContent = name;
            document.getElementById('headerAvatar').textContent = name.charAt(0).toUpperCase();
            document.querySelectorAll('.contact').forEach(c => c.classList.remove('active'));
            el.classList.add('active');
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
                console.log('No receiver selected');
                return;
            }
            
            const url = `client_chat.php?action=load_messages&receiver_id=${currentReceiverId}&receiver_role=${currentReceiverRole}`;
            console.log('Loading messages from:', url);
            
            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.text(); // Get as text first to see what we're getting
                })
                .then(text => {
                    console.log('Raw response:', text.substring(0, 200)); // Log first 200 chars
                    
                    // Try to parse as JSON
                    let data;
                    try {
                        data = JSON.parse(text);
                    } catch (e) {
                        console.error('Failed to parse JSON:', e);
                        throw new Error('Server returned invalid JSON. Response starts with: ' + text.substring(0, 100));
                    }
                    
                    // Check if we got an error response
                    if (data.error) {
                        console.error('Server error:', data.error);
                        return;
                    }
                    
                    // Handle the messages
                    let html = '';
                    if (!data || data.length === 0) {
                        html = '<div style="text-align:center;color:var(--secondary);margin-top:50px;font-size:16px;">💬 No messages yet.</div>';
                    } else {
                        data.forEach(msg => {
                            const isSent = msg.sender_id == LOGGED_ID;
                            const cls = isSent ? 'sent' : 'received';
                            const attachHtml = msg.attachment ?
                                `<br><a href="uploads/chat/${msg.attachment}" target="_blank">📎 Attachment</a>` : '';
                            const voiceHtml = msg.voice_message ?
                                buildPlayerHTML('uploads/voice/' + msg.voice_message, isSent) : '';
                            html += `
                        <div class="message ${cls}">
                            <div class="bubble">
                                ${msg.message ? msg.message : ''}
                                ${attachHtml}
                                ${voiceHtml}
                                <span class="msg-time">${msg.created_at}</span>
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
                    // Don't show alert here to avoid spamming
                });
        }

        function sendMessage() {
            if (!currentReceiverId) {
                alert('Please select a contact first.');
                return;
            }
            
            const text = messageInput.value.trim();
            const file = attachment.files[0];
            
            // Check if there's anything to send
            if (!text && !file && !voiceBlob) {
                console.log('No message content to send');
                return;
            }
            
            console.log('Preparing to send message...');
            
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
            
            fetch('client_chat.php', {
                    method: 'POST',
                    body: fd
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.text(); // Get as text first
                })
                .then(text => {
                    console.log('Raw send response:', text.substring(0, 200));
                    
                    let data;
                    try {
                        data = JSON.parse(text);
                    } catch (e) {
                        console.error('Failed to parse JSON:', e);
                        throw new Error('Server returned invalid JSON. Response starts with: ' + text.substring(0, 100));
                    }
                    
                    console.log('Server response:', data);
                    
                    if (data.status === 'success') {
                        // Clear input fields
                        messageInput.value = '';
                        attachment.value = '';
                        clearVoicePreview();
                        voiceBlob = null;
                        // Reload messages
                        loadMessages();
                    } else {
                        // Show the actual error message from server
                        const errorMsg = data.message || 'Failed to send message';
                        alert('Error: ' + errorMsg);
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    alert('Failed to send message. Please check your connection.\n\nError: ' + error.message);
                })
                .finally(() => {
                    // Re-enable send button
                    sendBtn.disabled = false;
                    sendBtn.textContent = '📤';
                });
        }

        // Event listeners
        sendBtn.addEventListener('click', sendMessage);
        
        messageInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        // Voice recording logic
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

        window.selectContact = selectContact;
        window.togglePlay = togglePlay;
        window.updateBar = updateBar;
        window.initBar = initBar;
        window.seekAudio = seekAudio;
        window.resetPlay = resetPlay;

        // Auto-select first contact on load
        document.addEventListener('DOMContentLoaded', function() {
            const first = document.querySelector('.contact');
            if (first) first.click();
        });
    </script>

</body>

</html>