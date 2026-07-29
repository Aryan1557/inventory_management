<?php
session_start();
include 'db_connection.php';
include 'session_check.php';


// --- Handle meeting scheduling ---
if (isset($_POST['schedule_meeting'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $meeting_link = mysqli_real_escape_string($conn, $_POST['meeting_link']);
    $meeting_date = $_POST['meeting_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $created_by = $_SESSION['user_id'] ?? 1;

    mysqli_query($conn, "
        INSERT INTO meetings
        (title, description, meeting_link, meeting_date, start_time, end_time, created_by, status)
        VALUES ('$title', '$description', '$meeting_link', '$meeting_date', '$start_time', '$end_time', '$created_by', 'Scheduled')
    ");
    $success = true;
    $meeting_id = mysqli_insert_id($conn);

    // Notify all employees
    $users = mysqli_query($conn, "SELECT user_id FROM users WHERE role IN ('User', 'Employee') AND status='active'");
    while ($u = mysqli_fetch_assoc($users)) {
        mysqli_query($conn, "
            INSERT INTO meeting_notifications (meeting_id, user_id, notification_type)
            VALUES ('$meeting_id', '{$u['user_id']}', 'Scheduled')
        ");
    }
}

// --- Handle starting a meeting ---
if (isset($_GET['start_meeting'])) {
    $meeting_id = (int)$_GET['start_meeting'];
    mysqli_query($conn, "UPDATE meetings SET status='Live' WHERE id=$meeting_id");

    $users = mysqli_query($conn, "SELECT user_id FROM users WHERE role IN ('User', 'Employee') AND status='active'");
    while ($u = mysqli_fetch_assoc($users)) {
        mysqli_query($conn, "
            INSERT INTO meeting_notifications (meeting_id, user_id, notification_type)
            VALUES ('$meeting_id', '{$u['user_id']}', 'Started')
            ON DUPLICATE KEY UPDATE notification_type='Started', is_read=0, created_at=CURRENT_TIMESTAMP
        ");
    }
    header('Location: meeting_admin.php');
    exit;
}

// --- Handle ending a meeting ---
if (isset($_GET['end_meeting'])) {
    $meeting_id = (int)$_GET['end_meeting'];
    mysqli_query($conn, "UPDATE meetings SET status='Ended' WHERE id=$meeting_id");
    header('Location: meeting_admin.php');
    exit;
}

// --- Handle cancelling a meeting ---
if (isset($_GET['cancel_meeting'])) {
    $meeting_id = (int)$_GET['cancel_meeting'];
    mysqli_query($conn, "UPDATE meetings SET status='Cancelled' WHERE id=$meeting_id");

    $users = mysqli_query($conn, "SELECT user_id FROM users WHERE role IN ('User', 'Employee') AND status='active'");
    while ($u = mysqli_fetch_assoc($users)) {
        mysqli_query($conn, "
            INSERT INTO meeting_notifications (meeting_id, user_id, notification_type)
            VALUES ('$meeting_id', '{$u['user_id']}', 'Cancelled')
            ON DUPLICATE KEY UPDATE notification_type='Cancelled', is_read=0, created_at=CURRENT_TIMESTAMP
        ");
    }
    header('Location: meeting_admin.php');
    exit;
}

// --- Stats ---
$total_meetings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) total FROM meetings"))['total'];
$today_meetings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) total FROM meetings WHERE meeting_date = CURDATE()"))['total'];
$live_meetings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) total FROM meetings WHERE status='Live'"))['total'];
$scheduled_meetings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) total FROM meetings WHERE status='Scheduled'"))['total'];

// Generate meeting link
$room = "meeting_" . time();
$meeting_link = "https://meet.jit.si/" . $room;

include 'sidebar.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin · Meeting Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }

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
            padding: 35px;
            margin-left: var(--sidebar-width);
            transition: all .4s ease;
            min-height: 100vh;
        }

        .main-content.expanded {
            margin-left: var(--sidebar-collapsed);
        }

        .welcome-card {
            background: linear-gradient(135deg,
                    var(--orange-gradient-start),
                    var(--orange-primary),
                    var(--orange-dark));
            padding: 30px 40px;
            border-radius: 24px;
            margin-bottom: 40px;
            color: white;
            position: relative;
            overflow: hidden;
            box-shadow: 0 20px 50px var(--orange-shadow);
        }

        .welcome-card::before {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: rgba(255, 200, 100, .1);
            bottom: -100px;
            left: -100px;
        }

        .welcome-card::after {
            content: '';
            position: absolute;
            width: 220px;
            height: 220px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .06);
            top: -60px;
            right: -60px;
        }

        .welcome-card h1 {
            font-size: 32px;
            margin-bottom: 8px;
            position: relative;
            z-index: 1;
            color: white;
        }

        .welcome-card p {
            opacity: .9;
            color: #ffe0b0;
            position: relative;
            z-index: 1;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 35px;
        }

        .card {
            background: var(--card);
            border-radius: 22px;
            padding: 28px;
            text-align: center;
            box-shadow: 0 10px 25px var(--orange-shadow);
            transition: all .35s ease;
            border: 2px solid var(--card-border);
            position: relative;
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 40px var(--orange-shadow);
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
        }

        .card h2 {
            font-size: 52px;
            font-weight: 700;
            margin-bottom: 6px;
            color: var(--orange-primary);
        }

        .card p {
            font-size: 15px;
            font-weight: 600;
            color: var(--secondary);
        }

        .purple-card::before { background: var(--orange-primary); }
        .cyan-card::before { background: var(--orange-gradient-start); }
        .green-card::before { background: #22c55e; }
        .orange-card::before { background: var(--orange-dark); }

        .section-title {
            font-size: 28px;
            font-weight: 700;
            padding-left: 12px;
            border-left: 5px solid var(--orange-primary);
            margin: 30px 0 20px 0;
            color: var(--text);
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .action-card {
            background: var(--card);
            border-radius: 24px;
            padding: 24px;
            border: 2px solid var(--card-border);
            text-decoration: none;
            color: var(--text);
            transition: all .35s ease;
            display: block;
            box-shadow: 0 8px 20px var(--orange-shadow);
        }

        .action-card:hover {
            transform: translateY(-6px);
            border-color: var(--orange-primary);
            box-shadow: 0 15px 35px var(--orange-shadow);
        }

        .action-card h3 {
            color: var(--orange-primary);
            margin-bottom: 8px;
        }

        .action-card p {
            color: var(--secondary);
        }

        .meeting-form {
            max-width: 700px;
            margin: 0 auto 30px;
        }

        .meeting-form input,
        .meeting-form textarea {
            width: 100%;
            padding: 14px 18px;
            margin-bottom: 15px;
            border: 2px solid var(--input-border);
            border-radius: 14px;
            background: var(--input-bg);
            color: var(--text);
            outline: none;
            font-size: 1rem;
            transition: all .35s ease;
        }

        .meeting-form input:focus,
        .meeting-form textarea:focus {
            border-color: var(--orange-primary);
            box-shadow: 0 0 0 4px var(--orange-subtle);
            background: var(--card);
        }

        .meeting-form input::placeholder,
        .meeting-form textarea::placeholder {
            color: var(--secondary);
            opacity: 0.6;
        }

        .meeting-form textarea {
            height: 120px;
            resize: none;
        }

        .row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .meeting-form button {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--orange-gradient-start), var(--orange-primary));
            color: white;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all .35s ease;
            box-shadow: 0 8px 20px var(--orange-shadow);
        }

        .meeting-form button:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px var(--orange-shadow);
            background: linear-gradient(135deg, var(--orange-primary), var(--orange-dark));
        }

        .success-box {
            background: rgba(34, 197, 94, 0.1);
            color: #4ade80;
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-weight: 600;
            border: 1px solid rgba(34, 197, 94, 0.3);
        }

        .meeting-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 24px;
            margin-top: 10px;
        }

        .meeting-card {
            background: var(--card);
            border-radius: 20px;
            padding: 22px;
            border: 2px solid var(--card-border);
            box-shadow: 0 4px 12px var(--orange-shadow);
            transition: all .35s ease;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .meeting-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 28px var(--orange-shadow);
        }

        .meeting-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--text);
        }

        .meeting-meta {
            display: flex;
            gap: 16px;
            color: var(--secondary);
            font-size: 0.95rem;
            flex-wrap: wrap;
        }

        .meeting-meta i {
            width: 18px;
        }

        .meeting-status {
            display: inline-block;
            padding: 4px 14px;
            border-radius: 30px;
            font-size: 0.8rem;
            font-weight: 600;
            align-self: flex-start;
        }

        .status-Scheduled {
            background: var(--orange-subtle);
            color: var(--orange-primary);
        }

        .status-Live {
            background: rgba(34, 197, 94, 0.12);
            color: #4ade80;
        }

        .status-Ended {
            background: rgba(203, 213, 225, 0.1);
            color: #94a3b8;
        }

        .status-Cancelled {
            background: rgba(220, 38, 38, 0.15);
            color: #f87171;
        }

        .btn {
            border: none;
            padding: 0.7rem 1.5rem;
            border-radius: 60px;
            font-weight: 600;
            font-size: 0.95rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            transition: all .35s ease;
            text-decoration: none;
            justify-content: center;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--orange-gradient-start), var(--orange-primary));
            color: white;
            box-shadow: 0 4px 12px var(--orange-shadow);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--orange-primary), var(--orange-dark));
            transform: scale(0.97);
            box-shadow: 0 8px 20px var(--orange-shadow);
        }

        .btn-success {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: white;
            box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3);
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #16a34a, #15803d);
            transform: scale(0.97);
            box-shadow: 0 8px 20px rgba(34, 197, 94, 0.4);
        }

        .btn-danger {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        .btn-danger:hover {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            transform: scale(0.97);
            box-shadow: 0 8px 20px rgba(239, 68, 68, 0.4);
        }

        .btn-warning {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
        }

        .btn-warning:hover {
            background: linear-gradient(135deg, #d97706, #b45309);
            transform: scale(0.97);
        }

        .btn-outline {
            background: transparent;
            border: 2px solid var(--card-border);
            color: var(--secondary);
        }

        .btn-outline:hover {
            background: var(--orange-subtle);
            border-color: var(--orange-primary);
            color: var(--orange-primary);
        }

        .flex-between {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .mt-3 {
            margin-top: 1.5rem;
        }

        .empty-state {
            padding: 40px;
            background: var(--card);
            border-radius: 24px;
            text-align: center;
            color: var(--secondary);
            border: 2px solid var(--card-border);
        }

        .empty-state i {
            color: var(--orange-primary);
            opacity: 0.5;
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

        @media (max-width: 768px) {
            .main-content {
                margin-left: 85px;
                padding: 20px;
            }

            .main-content.expanded {
                margin-left: 85px;
            }

            .row {
                grid-template-columns: 1fr;
            }

            .stats {
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
            }

            .card h2 {
                font-size: 36px;
            }

            .welcome-card h1 {
                font-size: 24px;
            }

            .welcome-card {
                padding: 25px 20px;
            }

            .section-title {
                font-size: 22px;
            }

            .quick-actions {
                grid-template-columns: 1fr 1fr;
            }

            .meeting-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .main-content {
                margin-left: 0;
                padding: 15px;
            }

            .main-content.expanded {
                margin-left: 0;
            }

            .stats {
                grid-template-columns: 1fr 1fr;
                gap: 10px;
            }

            .card {
                padding: 20px;
            }

            .card h2 {
                font-size: 28px;
            }

            .quick-actions {
                grid-template-columns: 1fr;
            }

            .meeting-form {
                padding: 0;
            }
        }
    </style>
</head>

<body>
    <div class="main-content" id="mainContent">
        <div class="welcome-card">
            <h1>Welcome Admin 👋</h1>
            <p>Schedule, manage, and monitor all meetings from one place.</p>
        </div>

        <!-- Stats -->
        <div class="stats">
            <div class="card purple-card">
                <h2><?= $total_meetings ?></h2>
                <p>Total Meetings</p>
            </div>
            <div class="card cyan-card">
                <h2><?= $scheduled_meetings ?></h2>
                <p>Scheduled</p>
            </div>
            <div class="card green-card">
                <h2><?= $live_meetings ?></h2>
                <p>Live Now</p>
            </div>
            <div class="card orange-card">
                <h2><?= $today_meetings ?></h2>
                <p>Today's Meetings</p>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <a href="#scheduleMeeting" class="action-card">
                <h3>📅 Schedule Meeting</h3>
                <p>Create a new online meeting.</p>
            </a>
            <a href="manage_meetings.php" class="action-card">
                <h3>📝 Manage Meetings</h3>
                <p>Edit or cancel meetings.</p>
            </a>
            <a href="meeting_history.php" class="action-card">
                <h3>📜 Meeting History</h3>
                <p>View completed meetings.</p>
            </a>
            <a href="participants.php" class="action-card">
                <h3>👥 Participants</h3>
                <p>Track attendance.</p>
            </a>
        </div>

        <!-- Success Message -->
        <?php if (isset($success)): ?>
            <div class="success-box"><i class="fas fa-check-circle"></i> ✅ Meeting Scheduled Successfully</div>
        <?php endif; ?>

        <!-- Schedule Meeting Form -->
        <div class="card" id="scheduleMeeting" style="padding:30px; margin-bottom:30px;">
            <h2 style="font-size:24px; margin-bottom:20px; color:var(--orange-primary);"><i class="fas fa-calendar-plus" style="margin-right:12px;"></i> Schedule Meeting</h2>
            <div class="meeting-form">
                <form method="POST">
                    <input type="text" name="title" placeholder="Meeting Title" required>
                    <textarea name="description" placeholder="Meeting Description"></textarea>
                    <div class="row">
                        <input type="date" name="meeting_date" required>
                        <input type="time" name="start_time" required>
                    </div>
                    <input type="time" name="end_time" required>
                    <input type="hidden" name="meeting_link" value="<?= $meeting_link ?>">
                    <button type="submit" name="schedule_meeting"><i class="fas fa-plus-circle"></i> Schedule Meeting</button>
                </form>
            </div>
        </div>

        <!-- Meeting List -->
        <div class="section-title"><i class="fas fa-list" style="margin-right:12px;"></i> All Meetings</div>
        <?php
        $meetings = mysqli_query($conn, "SELECT * FROM meetings ORDER BY meeting_date DESC, start_time DESC");
        if (mysqli_num_rows($meetings) === 0): ?>
            <div class="empty-state"><i class="fas fa-calendar-alt fa-3x" style="margin-bottom:16px;"></i><br>No meetings scheduled yet.</div>
        <?php else: ?>
            <div class="meeting-grid">
                <?php while ($m = mysqli_fetch_assoc($meetings)): ?>
                    <div class="meeting-card">
                        <div class="flex-between">
                            <span class="meeting-title"><?= htmlspecialchars($m['title']) ?></span>
                            <span class="meeting-status status-<?= $m['status'] ?>"><?= $m['status'] ?></span>
                        </div>
                        <div class="meeting-meta">
                            <span><i class="fas fa-calendar-day"></i> <?= date('M d, Y', strtotime($m['meeting_date'])) ?></span>
                            <span><i class="fas fa-clock"></i> <?= date('h:i A', strtotime($m['start_time'])) ?> - <?= date('h:i A', strtotime($m['end_time'])) ?></span>
                        </div>
                        <?php if (!empty($m['description'])): ?>
                            <p style="color:var(--secondary); font-size:0.9rem;"><?= htmlspecialchars(substr($m['description'], 0, 100)) ?>...</p>
                        <?php endif; ?>
                        <div style="display:flex; gap:0.6rem; flex-wrap:wrap; margin-top:8px;">
                            <?php if ($m['status'] === 'Scheduled'): ?>
                                <a href="?start_meeting=<?= $m['id'] ?>" class="btn btn-success"><i class="fas fa-play"></i> Start</a>
                                <a href="?cancel_meeting=<?= $m['id'] ?>" class="btn btn-danger" onclick="return confirm('Cancel this meeting?')"><i class="fas fa-times"></i> Cancel</a>
                            <?php elseif ($m['status'] === 'Live'): ?>
                                <span class="btn btn-warning" style="opacity:0.8;"><i class="fas fa-video"></i> Live</span>
                                <a href="<?= $m['meeting_link'] ?? '#' ?>" target="_blank" class="btn btn-primary"><i class="fas fa-sign-in-alt"></i> Join</a>
                                <a href="?end_meeting=<?= $m['id'] ?>" class="btn btn-danger" onclick="return confirm('End this meeting?')"><i class="fas fa-stop"></i> End</a>
                            <?php elseif ($m['status'] === 'Ended'): ?>
                                <span class="btn btn-outline" style="opacity:0.5;"><i class="fas fa-check-circle"></i> Ended</span>
                            <?php elseif ($m['status'] === 'Cancelled'): ?>
                                <span class="btn btn-outline" style="opacity:0.5;"><i class="fas fa-ban"></i> Cancelled</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
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
    </script>
</body>

</html>