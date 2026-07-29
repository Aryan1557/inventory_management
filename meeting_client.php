<?php
session_start();
include 'db_connection.php';
include 'session_check.php';


$user_id = $_SESSION['user_id'] ?? 1;

// --- Handle joining a meeting ---
if (isset($_GET['join_meeting'])) {
    $meeting_id = (int)$_GET['join_meeting'];

    // Record attendance
    mysqli_query($conn, "
        INSERT INTO meeting_attendees (meeting_id, user_id, joined_at, attendance_status)
        VALUES ('$meeting_id', '$user_id', NOW(), 'Present')
        ON DUPLICATE KEY UPDATE joined_at=NOW(), attendance_status='Present', left_at=NULL
    ");

    // Mark notification as read
    mysqli_query($conn, "
        UPDATE meeting_notifications 
        SET is_read=1, read_at=NOW()
        WHERE meeting_id='$meeting_id' AND user_id='$user_id'
    ");

    // Get meeting link
    $meeting = mysqli_fetch_assoc(mysqli_query($conn, "SELECT meeting_link FROM meetings WHERE id=$meeting_id"));
    if ($meeting) {
        header('Location: ' . $meeting['meeting_link']);
        exit;
    }
}

// --- Clear all notifications ---
if (isset($_GET['clear_notification'])) {
    mysqli_query($conn, "
        UPDATE meeting_notifications 
        SET is_read=1, read_at=NOW()
        WHERE user_id='$user_id' AND is_read=0
    ");
    header('Location: meeting_client.php');
    exit;
}

// --- Get unread notification count ---
$unread_count = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) total FROM meeting_notifications 
    WHERE user_id='$user_id' AND is_read=0
"))['total'];

// --- Get meetings ---
$live_meetings = mysqli_query($conn, "
    SELECT * FROM meetings 
    WHERE status='Live' 
    ORDER BY meeting_date ASC, start_time ASC
");

$upcoming_meetings = mysqli_query($conn, "
    SELECT * FROM meetings 
    WHERE status='Scheduled' AND meeting_date >= CURDATE()
    ORDER BY meeting_date ASC, start_time ASC
");

$past_meetings = mysqli_query($conn, "
    SELECT * FROM meetings 
    WHERE status='Ended' OR status='Cancelled'
    ORDER BY meeting_date DESC, start_time DESC
    LIMIT 5
");

// Get user name
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT name FROM users WHERE user_id='$user_id'"));
$user_name = $user['name'] ?? 'Employee';

// Get today's meetings count
$today_meetings = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) total FROM meetings 
    WHERE meeting_date = CURDATE() AND (status='Live' OR status='Scheduled')
"))['total'];

include 'sidebar1.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee · Meetings</title>
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

        .cyan-card::before { background: var(--orange-primary); }
        .green-card::before { background: #22c55e; }
        .orange-card::before { background: var(--orange-gradient-start); }
        .purple-card::before { background: var(--orange-dark); }

        .section-title {
            font-size: 28px;
            font-weight: 700;
            padding-left: 12px;
            border-left: 5px solid var(--orange-primary);
            margin: 30px 0 20px 0;
            color: var(--text);
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

        .btn-disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .flex-between {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
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

        .notification-bar {
            background: var(--orange-subtle);
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            border: 2px solid rgba(255, 152, 0, 0.2);
        }

        .notification-bar a {
            color: var(--orange-primary);
            font-weight: 600;
            text-decoration: none;
            transition: all .3s ease;
        }

        .notification-bar a:hover {
            text-decoration: underline;
            color: var(--orange-dark);
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

            .notification-bar {
                flex-direction: column;
                gap: 10px;
                align-items: flex-start;
            }
        }
    </style>
</head>

<body>
    <div class="main-content" id="mainContent">
        <div class="welcome-card">
            <h1>Welcome, <?= htmlspecialchars($user_name) ?> 👋</h1>
            <p>View and join your scheduled meetings.</p>
        </div>

        <!-- Stats -->
        <?php
        $total_meetings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) total FROM meetings WHERE status='Live' OR status='Scheduled'"))['total'];
        $live_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) total FROM meetings WHERE status='Live'"))['total'];
        ?>
        <div class="stats">
            <div class="card cyan-card">
                <h2><?= $live_count ?></h2>
                <p>Live Now</p>
            </div>
            <div class="card green-card">
                <h2><?= $total_meetings ?></h2>
                <p>Active Meetings</p>
            </div>
            <div class="card orange-card">
                <h2><?= $unread_count ?></h2>
                <p>Notifications</p>
            </div>
            <div class="card purple-card">
                <h2><?= $today_meetings ?></h2>
                <p>Today's Meetings</p>
            </div>
        </div>

        <!-- Notification Bar -->
        <?php if ($unread_count > 0): ?>
            <div class="notification-bar">
                <span><i class="fas fa-bell" style="color:var(--orange-primary);"></i> You have <strong><?= $unread_count ?></strong> new notification(s)</span>
                <a href="?clear_notification=1"><i class="fas fa-check"></i> Mark all as read</a>
            </div>
        <?php endif; ?>

        <!-- Live Meetings -->
        <div class="section-title"><i class="fas fa-video" style="margin-right:12px;"></i> Live Meetings</div>
        <?php if (mysqli_num_rows($live_meetings) === 0): ?>
            <div class="empty-state">
                <i class="fas fa-hourglass-half fa-3x" style="margin-bottom:16px;"></i><br>
                No meetings are currently live. Check back later!
            </div>
        <?php else: ?>
            <div class="meeting-grid">
                <?php while ($m = mysqli_fetch_assoc($live_meetings)): ?>
                    <div class="meeting-card" style="border-left:6px solid #22c55e;">
                        <div class="flex-between">
                            <span class="meeting-title"><?= htmlspecialchars($m['title']) ?></span>
                            <span class="meeting-status status-Live">● Live</span>
                        </div>
                        <div class="meeting-meta">
                            <span><i class="fas fa-calendar-day"></i> <?= date('M d, Y', strtotime($m['meeting_date'])) ?></span>
                            <span><i class="fas fa-clock"></i> <?= date('h:i A', strtotime($m['start_time'])) ?> - <?= date('h:i A', strtotime($m['end_time'])) ?></span>
                        </div>
                        <?php if (!empty($m['description'])): ?>
                            <p style="color:var(--secondary); font-size:0.9rem;"><?= htmlspecialchars(substr($m['description'], 0, 100)) ?>...</p>
                        <?php endif; ?>
                        <a href="?join_meeting=<?= $m['id'] ?>" class="btn btn-success"><i class="fas fa-sign-in-alt"></i> Join Meeting</a>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>

        <!-- Upcoming Meetings -->
        <div class="section-title" style="margin-top:40px;"><i class="fas fa-calendar-alt" style="margin-right:12px;"></i> Upcoming Meetings</div>
        <?php if (mysqli_num_rows($upcoming_meetings) === 0): ?>
            <div class="empty-state" style="padding:20px;">No upcoming meetings scheduled.</div>
        <?php else: ?>
            <div class="meeting-grid">
                <?php while ($m = mysqli_fetch_assoc($upcoming_meetings)): ?>
                    <div class="meeting-card" style="opacity:0.85;">
                        <div class="flex-between">
                            <span class="meeting-title"><?= htmlspecialchars($m['title']) ?></span>
                            <span class="meeting-status status-Scheduled">Scheduled</span>
                        </div>
                        <div class="meeting-meta">
                            <span><i class="fas fa-calendar-day"></i> <?= date('M d, Y', strtotime($m['meeting_date'])) ?></span>
                            <span><i class="fas fa-clock"></i> <?= date('h:i A', strtotime($m['start_time'])) ?> - <?= date('h:i A', strtotime($m['end_time'])) ?></span>
                        </div>
                        <span class="btn btn-outline btn-disabled" style="opacity:0.5;"><i class="fas fa-clock"></i> Not started yet</span>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>

        <!-- Past Meetings -->
        <div class="section-title" style="margin-top:40px;"><i class="fas fa-history" style="margin-right:12px;"></i> Recent Past Meetings</div>
        <?php if (mysqli_num_rows($past_meetings) === 0): ?>
            <div class="empty-state" style="padding:20px;">No past meetings.</div>
        <?php else: ?>
            <div class="meeting-grid">
                <?php while ($m = mysqli_fetch_assoc($past_meetings)): ?>
                    <div class="meeting-card" style="opacity:0.7;">
                        <div class="flex-between">
                            <span class="meeting-title"><?= htmlspecialchars($m['title']) ?></span>
                            <span class="meeting-status status-<?= $m['status'] ?>"><?= $m['status'] ?></span>
                        </div>
                        <div class="meeting-meta">
                            <span><i class="fas fa-calendar-day"></i> <?= date('M d, Y', strtotime($m['meeting_date'])) ?></span>
                            <span><i class="fas fa-clock"></i> <?= date('h:i A', strtotime($m['start_time'])) ?></span>
                        </div>
                        <span class="btn btn-outline btn-disabled" style="opacity:0.4;"><i class="fas fa-check-circle"></i> Completed</span>
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