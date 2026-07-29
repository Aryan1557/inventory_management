<?php
session_start();
include 'db_connection.php';
include 'session_check.php';

include 'sidebar1.php';

$user_id = $_SESSION['user_id'] ?? null;

// ========== SELF-HEALING WELCOME NAME ==========
$employee_name = $_SESSION['name'] ?? '';
if (trim($employee_name) === '' && $user_id) {
    $stmt = mysqli_prepare($conn, "SELECT name FROM users WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    if ($res && mysqli_num_rows($res) > 0) {
        $employee_name = mysqli_fetch_assoc($res)['name'];
        $_SESSION['name'] = $employee_name;
    }
    mysqli_stmt_close($stmt);
}
if (trim($employee_name) === '') {
    $employee_name = 'Employee';
}

// Time-of-day greeting
$hour = (int) date('G');
if ($hour < 12) {
    $greeting = 'Good morning';
} elseif ($hour < 17) {
    $greeting = 'Good afternoon';
} else {
    $greeting = 'Good evening';
}

// Fetch employee statistics
$total_leaves = 0;
$pending_leaves = 0;
$today_attendance = 'Not Marked';
$today_attendance_class = 'not-marked';
$unread_messages = 0;

if ($user_id) {
    // Total leaves taken
    $stmt = mysqli_prepare($conn, "SELECT COUNT(*) as total FROM leave_management WHERE user_id = ? AND status = 'Approved'");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $leaves_result = mysqli_stmt_get_result($stmt);
    if ($leaves_result) {
        $total_leaves = mysqli_fetch_assoc($leaves_result)['total'];
    }
    mysqli_stmt_close($stmt);

    // Pending leaves
    $stmt = mysqli_prepare($conn, "SELECT COUNT(*) as total FROM leave_management WHERE user_id = ? AND status = 'Pending'");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $pending_result = mysqli_stmt_get_result($stmt);
    if ($pending_result) {
        $pending_leaves = mysqli_fetch_assoc($pending_result)['total'];
    }
    mysqli_stmt_close($stmt);

    // Today's attendance
    $today = date('Y-m-d');
    $stmt = mysqli_prepare($conn, "SELECT status FROM attendance WHERE employee_id = ? AND attendance_date = ?");
    mysqli_stmt_bind_param($stmt, "is", $user_id, $today);
    mysqli_stmt_execute($stmt);
    $attendance_result = mysqli_stmt_get_result($stmt);
    if ($attendance_result && mysqli_num_rows($attendance_result) > 0) {
        $attendance_data = mysqli_fetch_assoc($attendance_result);
        $today_attendance = $attendance_data['status'];
        $normalized_status = strtolower(trim($today_attendance));
        if (in_array($normalized_status, ['present', 'absent'])) {
            $today_attendance_class = $normalized_status;
        } else {
            $today_attendance_class = 'not-marked';
        }
    }
    mysqli_stmt_close($stmt);

    // Unread messages
    $stmt = mysqli_prepare($conn, "SELECT COUNT(*) as total FROM chat_messages WHERE receiver_id = ? AND receiver_role = 'employee'");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $messages_result = mysqli_stmt_get_result($stmt);
    if ($messages_result) {
        $unread_messages = mysqli_fetch_assoc($messages_result)['total'];
    }
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes, viewport-fit=cover">
    <title>Employee Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
        }

        body {
            background: var(--bg);
            color: var(--text);
            transition: .35s;
            background-image:
                radial-gradient(circle at top right,
                    rgba(255, 140, 0, .06),
                    transparent 30%),
                radial-gradient(circle at bottom left,
                    rgba(255, 100, 0, .04),
                    transparent 30%);
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            overflow-x: hidden;
            width: 100%;
        }

        :root {
            /* Light Theme - Vibrant Orange */
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
            --orange-hover: #e65100;
        }

        body.dark {
            --bg: #12100e;
            --text: #f0e8e0;

            --card: #1d1815;
            --card-border: #3a322a;
            background-image:
                radial-gradient(circle at top right,
                    rgba(255, 140, 0, .08),
                    transparent 35%),
                radial-gradient(circle at bottom left,
                    rgba(255, 100, 0, .06),
                    transparent 35%);
            --secondary: #a89888;
            
            --orange-primary: #ffa726;
            --orange-light: #ffcc80;
            --orange-dark: #f57c00;
            --orange-gradient-start: #ff9800;
            --orange-gradient-end: #e65100;
            --orange-subtle: rgba(255, 152, 0, 0.12);
            --orange-shadow: rgba(255, 152, 0, 0.2);
            --orange-hover: #ffb74d;
        }

        .main-content {
            background: transparent;
            margin-left: var(--sidebar-width);
            padding: clamp(16px, 2.5vw, 35px);
            transition: margin-left .4s ease, padding .3s ease;
            min-height: 100vh;
        }

        .main-content.expanded {
            margin-left: 105px;
            border-radius: 30px;
        }

        /* Welcome Banner - Vibrant Orange Gradient */
        .welcome-card {
            background: linear-gradient(135deg,
                    var(--orange-gradient-start),
                    var(--orange-primary),
                    var(--orange-dark));
            color: white;
            position: relative;
            overflow: hidden;
            padding: clamp(24px, 4vw, 40px);
            border-radius: 24px;
            margin-bottom: clamp(24px, 4vw, 40px);
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
            background: rgba(255, 255, 255, .08);
            top: -60px;
            right: -60px;
        }

        .welcome-card h1 {
            margin-bottom: 10px;
            font-size: clamp(22px, 3vw, 32px);
            color: white;
            position: relative;
            z-index: 1;
        }

        .welcome-card p {
            opacity: .9;
            color: #ffe0b0;
            position: relative;
            z-index: 1;
            font-size: clamp(14px, 1.2vw, 16px);
        }

        /* Statistics */
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: clamp(14px, 2vw, 20px);
            margin-bottom: clamp(24px, 4vw, 35px);
        }

        body.dark .section-title {
            color: #f0e8d0;
        }

        .card {
            background: var(--card);
            border: 1px solid var(--card-border);
            border-radius: 22px;
            padding: clamp(18px, 2.5vw, 28px);
            box-shadow: 0 8px 24px rgba(60, 50, 40, .06);
            transition: .35s;
            position: relative;
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 18px 35px var(--orange-shadow);
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            border-radius: 22px 22px 0 0;
        }

        .card h2 {
            color: var(--orange-primary);
            font-size: clamp(36px, 5vw, 50px);
            font-weight: 700;
        }

        .card p {
            font-size: clamp(13px, 1.1vw, 15px);
            font-weight: 600;
            letter-spacing: .3px;
            color: var(--secondary);
        }

        /* Individual card colors - Orange variations */
        .purple-card::before {
            background: var(--orange-primary);
        }

        .cyan-card::before {
            background: var(--orange-light);
        }

        .green-card::before {
            background: var(--orange-dark);
        }

        .orange-card::before {
            background: var(--orange-gradient-start);
        }

        /* AMC SECTION - for consistency */
        .amc-alert {
            background: linear-gradient(135deg, var(--orange-primary), var(--orange-dark));
            color: white;
            padding: 14px 20px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 10px 25px var(--orange-shadow);
            overflow: hidden;
            border: 1px solid rgba(255, 152, 0, 0.2);
        }

        .amc-alert marquee {
            font-size: 15px;
            font-weight: 600;
        }

        body.dark .amc-alert {
            background: linear-gradient(135deg, #f57c00, #e65100);
            border: 1px solid rgba(255, 152, 0, 0.3);
        }

        /* Section Heading */
        .section-title {
            font-size: clamp(20px, 2.4vw, 28px);
            font-weight: 700;
            padding-left: 12px;
            margin-bottom: clamp(16px, 2vw, 25px);
            color: var(--text);
            border-left: 5px solid var(--orange-primary);
        }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: clamp(14px, 1.8vw, 20px);
            margin-bottom: clamp(24px, 4vw, 35px);
        }

        .action-card {
            position: relative;
            overflow: hidden;
            background: var(--card);
            border: 2px solid var(--card-border);
            border-radius: 24px;
            padding: clamp(20px, 2.5vw, 30px);
            text-decoration: none;
            color: var(--text);
            box-shadow: 0 8px 20px var(--orange-subtle), 0 18px 40px var(--orange-shadow);
            transition: all 0.35s ease;
        }

        /* Top Accent Border - Orange */
        .action-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--orange-primary), var(--orange-light), var(--orange-dark));
            border-radius: 24px 24px 0 0;
        }

        /* Decorative Circle */
        .action-card::after {
            content: '';
            position: absolute;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: var(--orange-subtle);
            top: -40px;
            right: -40px;
            transition: 0.4s ease;
        }

        /* Hover Effects */
        .action-card:hover {
            transform: translateY(-10px) scale(1.02);
            border-color: var(--orange-primary);
            box-shadow: 0 15px 35px var(--orange-shadow), 0 25px 60px rgba(245, 124, 0, 0.15);
        }

        .action-card:hover::after {
            transform: scale(1.4);
        }

        /* Title */
        .action-card h3 {
            font-size: clamp(16px, 1.5vw, 20px);
            font-weight: 700;
            color: var(--orange-primary);
            margin-bottom: 12px;
            text-shadow: none;
            position: relative;
            z-index: 2;
            transition: color 0.3s ease;
        }

        /* Description */
        .action-card p {
            color: var(--secondary);
            line-height: 1.6;
            position: relative;
            z-index: 2;
            font-size: clamp(13px, 1.1vw, 14px);
        }

        /* Dark Mode adjustments */
        body.dark .action-card {
            background: var(--card);
            border: 2px solid var(--card-border);
        }

        body.dark .action-card:hover {
            border-color: var(--orange-primary);
            box-shadow: 0 18px 40px var(--orange-shadow), 0 25px 60px rgba(255, 152, 0, 0.15);
        }

        body.dark .action-card h3 {
            color: var(--orange-light);
        }

        body.dark .action-card:hover h3 {
            color: var(--orange-primary);
        }

        body.dark .action-card p {
            color: var(--secondary);
        }

        /* Different accent colors for cards - all orange variations */
        .action-card:nth-child(1)::before {
            background: linear-gradient(90deg, var(--orange-primary), var(--orange-light));
        }

        .action-card:nth-child(2)::before {
            background: linear-gradient(90deg, var(--orange-light), var(--orange-dark));
        }

        .action-card:nth-child(3)::before {
            background: linear-gradient(90deg, var(--orange-dark), var(--orange-gradient-start));
        }

        .action-card:nth-child(4)::before {
            background: linear-gradient(90deg, var(--orange-gradient-start), var(--orange-primary));
        }

        /* Additional subtle touches */
        .card h2, .action-card h3 {
            transition: color 0.3s ease;
        }

        .card:hover h2 {
            color: var(--orange-dark);
        }

        .action-card:hover h3 {
            color: var(--orange-dark);
        }

        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: clamp(5px, 0.6vw, 7px) clamp(12px, 1.5vw, 18px);
            border-radius: 20px;
            font-weight: 600;
            font-size: clamp(13px, 1.2vw, 16px);
        }

        .status-badge.present {
            background: rgba(34, 197, 94, 0.12);
            color: #22c55e;
        }

        .status-badge.absent {
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444;
        }

        .status-badge.not-marked {
            background: rgba(120, 113, 108, 0.12);
            color: var(--secondary);
            font-size: clamp(11px, 1vw, 14px);
        }

        /* Lower Grid - Employee Info + Date/Time */
        .lower-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: clamp(14px, 2vw, 20px);
            align-items: start;
        }

        .welcome-info-card,
        .datetime {
            background: var(--card);
            border-radius: var(--radius-md, 22px);
            padding: clamp(18px, 2.5vw, 26px);
            border: 2px solid var(--card-border);
            box-shadow: 0 10px 25px var(--orange-shadow), 0 20px 50px rgba(255, 152, 0, 0.06);
            transition: box-shadow .3s ease;
        }

        .welcome-info-card:hover,
        .datetime:hover {
            box-shadow: 0 14px 32px var(--orange-shadow), 0 24px 55px rgba(255, 152, 0, 0.08);
        }

        .welcome-info-card h2 {
            color: var(--orange-primary);
            margin-bottom: 12px;
            font-size: clamp(18px, 1.8vw, 21px);
        }

        .welcome-info-card p {
            color: var(--secondary);
            line-height: 1.75;
            font-size: clamp(14px, 1.1vw, 15px);
        }

        .datetime h3 {
            margin-bottom: 12px;
            color: var(--text);
            font-size: clamp(15px, 1.4vw, 17px);
        }

        #clock {
            font-size: clamp(22px, 2.5vw, 26px);
            font-weight: bold;
            color: var(--orange-primary);
            font-variant-numeric: tabular-nums;
        }

        #date {
            font-size: clamp(13px, 1.1vw, 15px);
            color: var(--secondary);
            margin-top: 6px;
        }

        /* Smooth scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
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

        /* Additional glow effects for cards */
        .card::after {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, var(--orange-subtle), transparent 70%);
            opacity: 0;
            transition: opacity 0.5s ease;
            pointer-events: none;
        }

        .card:hover::after {
            opacity: 1;
        }

        .action-card .icon {
            font-size: 32px;
            margin-bottom: 10px;
            display: block;
        }

        /* privacy overlay shown when tab loses focus (screenshot deterrent) */
        .privacy-overlay {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: var(--bg);
            backdrop-filter: blur(20px);
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: 600;
            color: var(--secondary);
        }

        .privacy-overlay.active {
            display: flex;
        }

        /* =========================================================
           RESPONSIVE / MOBILE VIEW
           On small screens the sidebar becomes an off-canvas menu
           (opened via the hamburger button from sidebar.php), so the
           main content no longer needs to leave space for it and
           instead needs room at the top for the hamburger button.
        ========================================================= */
        @media (max-width: 992px) {
            .main-content {
                margin-left: 0;
                padding: 24px 18px;
            }

            .main-content.expanded {
                margin-left: 0;
                border-radius: 0;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 80px 16px 24px;
            }

            .welcome-card {
                padding: 24px 20px;
                border-radius: 18px;
                margin-bottom: 24px;
            }

            .welcome-card h1 {
                font-size: 22px;
            }

            .welcome-card p {
                font-size: 14px;
            }

            .amc-alert {
                padding: 10px 14px;
                border-radius: 12px;
                margin-bottom: 20px;
            }

            .amc-alert marquee {
                font-size: 13px;
            }

            .stats {
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
                margin-bottom: 24px;
            }

            .card {
                padding: 18px;
                border-radius: 16px;
            }

            .card h2 {
                font-size: 32px;
            }

            .card p {
                font-size: 13px;
            }

            .section-title {
                font-size: 20px;
                margin-bottom: 16px;
            }

            .quick-actions {
                grid-template-columns: 1fr 1fr;
                gap: 12px;
            }

            .action-card {
                padding: 18px 16px;
                border-radius: 16px;
            }

            .action-card h3 {
                font-size: 15px;
                margin-bottom: 6px;
            }

            .action-card p {
                font-size: 12px;
            }

            .action-card::after {
                width: 80px;
                height: 80px;
                top: -30px;
                right: -30px;
            }

            .lower-grid {
                grid-template-columns: 1fr;
                gap: 14px;
            }

            .welcome-info-card,
            .datetime {
                padding: 18px;
                border-radius: 16px;
            }

            .welcome-info-card h2 {
                font-size: 18px;
            }

            .welcome-info-card p {
                font-size: 14px;
            }

            .datetime h3 {
                font-size: 15px;
            }

            #clock {
                font-size: 22px;
            }

            #date {
                font-size: 13px;
            }

            .privacy-overlay {
                font-size: 15px;
                padding: 0 20px;
                text-align: center;
            }
        }

        @media (max-width: 480px) {
            .main-content {
                padding: 70px 10px 16px;
            }

            .welcome-card {
                padding: 18px 16px;
                border-radius: 14px;
                margin-bottom: 16px;
            }

            .welcome-card h1 {
                font-size: 18px;
            }

            .welcome-card p {
                font-size: 13px;
            }

            .stats {
                gap: 8px;
                margin-bottom: 16px;
            }

            .card {
                padding: 12px 10px;
                border-radius: 12px;
            }

            .card h2 {
                font-size: 24px;
            }

            .card p {
                font-size: 11px;
            }

            .section-title {
                font-size: 17px;
                padding-left: 8px;
                margin-bottom: 12px;
            }

            .quick-actions {
                grid-template-columns: 1fr 1fr;
                gap: 8px;
                margin-bottom: 16px;
            }

            .action-card {
                padding: 14px 12px;
                border-radius: 12px;
            }

            .action-card h3 {
                font-size: 13px;
                margin-bottom: 4px;
            }

            .action-card p {
                font-size: 11px;
                line-height: 1.4;
            }

            .action-card::after {
                width: 60px;
                height: 60px;
                top: -20px;
                right: -20px;
            }

            .action-card:hover {
                transform: translateY(-4px) scale(1.01);
            }

            .lower-grid {
                gap: 10px;
            }

            .welcome-info-card,
            .datetime {
                padding: 14px;
                border-radius: 12px;
            }

            .welcome-info-card h2 {
                font-size: 16px;
                margin-bottom: 8px;
            }

            .welcome-info-card p {
                font-size: 13px;
                line-height: 1.6;
            }

            .datetime h3 {
                font-size: 14px;
                margin-bottom: 8px;
            }

            #clock {
                font-size: 18px;
            }

            #date {
                font-size: 12px;
            }

            .status-badge {
                font-size: 11px;
                padding: 3px 10px;
            }
        }

        @media (max-width: 380px) {
            .main-content {
                padding: 60px 6px 12px;
            }

            .stats {
                grid-template-columns: 1fr 1fr;
                gap: 6px;
            }

            .card {
                padding: 10px 8px;
                border-radius: 10px;
            }

            .card h2 {
                font-size: 20px;
            }

            .card p {
                font-size: 10px;
            }

            .quick-actions {
                grid-template-columns: 1fr 1fr;
                gap: 6px;
            }

            .action-card {
                padding: 10px 8px;
                border-radius: 10px;
            }

            .action-card h3 {
                font-size: 12px;
            }

            .action-card p {
                font-size: 10px;
            }

            .action-card::after {
                width: 40px;
                height: 40px;
                top: -15px;
                right: -15px;
            }

            .welcome-card h1 {
                font-size: 16px;
            }

            .welcome-card p {
                font-size: 12px;
            }

            .section-title {
                font-size: 15px;
            }

            #clock {
                font-size: 16px;
            }
        }

        /* Landscape Mode on Phones */
        @media (max-height: 500px) and (orientation: landscape) {
            .main-content {
                padding: 60px 16px 12px;
            }

            .welcome-card {
                padding: 14px 20px;
                margin-bottom: 14px;
            }

            .welcome-card h1 {
                font-size: 18px;
            }

            .welcome-card p {
                font-size: 13px;
            }

            .stats {
                grid-template-columns: repeat(4, 1fr);
                gap: 8px;
                margin-bottom: 14px;
            }

            .card {
                padding: 10px;
            }

            .card h2 {
                font-size: 22px;
            }

            .quick-actions {
                grid-template-columns: repeat(4, 1fr);
                gap: 8px;
                margin-bottom: 14px;
            }

            .action-card {
                padding: 12px;
            }

            .action-card h3 {
                font-size: 13px;
            }

            .action-card p {
                display: none;
            }

            .action-card::after {
                width: 50px;
                height: 50px;
                top: -15px;
                right: -15px;
            }

            .lower-grid {
                grid-template-columns: 1fr 1fr;
                gap: 10px;
            }

            .welcome-info-card,
            .datetime {
                padding: 12px;
            }

            #clock {
                font-size: 18px;
            }
        }

        /* Touch-friendly improvements */
        @media (pointer: coarse) {
            .action-card,
            .card {
                touch-action: manipulation;
                -webkit-tap-highlight-color: transparent;
            }

            .action-card:active {
                transform: scale(0.97);
            }

            .card:active {
                transform: scale(0.98);
            }
        }
    </style>
</head>

<body>
    <div class="privacy-overlay" id="privacyOverlay">Content hidden — window not in focus</div>
    <br>
    <div class="main-content" id="mainContent">

        <div class="welcome-card">
            <h1><?php echo htmlspecialchars($greeting); ?>, <?php echo htmlspecialchars($employee_name); ?> 👋</h1>
            <p>Manage your attendance, leaves, and communication from one place.</p>
        </div>

        <!-- Statistics -->
        <div class="stats">
            <div class="card purple-card">
                <h2>
                    <?php if ($today_attendance_class === 'not-marked'): ?>
                        <span class="status-badge not-marked">Not Marked</span>
                    <?php else: ?>
                        <span class="status-badge <?php echo $today_attendance_class; ?>">
                            <?php echo htmlspecialchars($today_attendance); ?>
                        </span>
                    <?php endif; ?>
                </h2>
                <p>Today's Attendance</p>
            </div>

            <div class="card cyan-card">
                <h2><?php echo (int) $total_leaves; ?></h2>
                <p>Leaves Taken</p>
            </div>

            <div class="card orange-card">
                <h2><?php echo (int) $pending_leaves; ?></h2>
                <p>Pending Leaves</p>
            </div>

            <div class="card green-card">
                <h2><?php echo (int) $unread_messages; ?></h2>
                <p>Messages</p>
            </div>
        </div>

        <!-- Quick Actions -->
        <h2 class="section-title">Quick Actions</h2>
        <div class="quick-actions">
            <a href="attendance.php" class="action-card">
                <h3>🕒 Mark Attendance</h3>
                <p>Record your daily attendance.</p>
            </a>

            <a href="leave.php" class="action-card">
                <h3>📝 Apply Leave</h3>
                <p>Submit leave applications.</p>
            </a>

            <a href="client_chat.php" class="action-card">
                <h3>💬 Chat</h3>
                <p>Communicate with management.</p>
            </a>

            <a href="profile.php" class="action-card">
                <h3>👤 Profile</h3>
                <p>View and update your profile.</p>
            </a>
        </div>

        <!-- Employee Information + Date & Time -->
        <div class="lower-grid">
            <div class="welcome-info-card">
                <h2>📋 Employee Information</h2>
                <p>
                    Access your attendance records, apply for leave,
                    communicate with management and track your work
                    activities from this dashboard. Stay updated with
                    your daily tasks and important announcements.
                </p>
            </div>

            <div class="datetime">
                <h3>📅 Current Date & Time</h3>
                <div id="clock"></div>
                <div id="date"></div>
            </div>
        </div>

    </div>

    <script>
        // Sidebar handling
        document.addEventListener("DOMContentLoaded", function () {
            const mainContent = document.getElementById("mainContent");

            // Apply saved sidebar state
            if (localStorage.getItem("sidebarState") === "collapsed") {
                if (mainContent) {
                    mainContent.classList.add("expanded");
                }
            }

            // Apply saved theme
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
                if (e.key === 'sidebarState' && mainContent) {
                    if (e.newValue === 'collapsed') {
                        mainContent.classList.add('expanded');
                    } else {
                        mainContent.classList.remove('expanded');
                    }
                }
                if (e.key === 'theme') {
                    if (e.newValue === 'dark') {
                        document.body.classList.add('dark');
                    } else {
                        document.body.classList.remove('dark');
                    }
                }
            });

            // Handle sidebar state from mobile toggle
            const sidebar = document.getElementById("sidebar");
            if (sidebar) {
                const observer = new MutationObserver(function() {
                    if (sidebar.classList.contains('collapsed')) {
                        mainContent.classList.add('expanded');
                    } else {
                        mainContent.classList.remove('expanded');
                    }
                });
                observer.observe(sidebar, { attributes: true, attributeFilter: ['class'] });
            }
        });

        // Real-time clock
        function updateClock() {
            const now = new Date();

            const timeStr = now.toLocaleString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true
            });
            const dateStr = now.toLocaleString('en-US', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });

            document.getElementById("clock").textContent = timeStr;
            document.getElementById("date").textContent = dateStr;
        }

        setInterval(updateClock, 1000);
        updateClock();

        // /* ---------- Disable right-click / context menu ---------- */
        // document.addEventListener('contextmenu', function (e) {
        //     e.preventDefault();
        // });

        // /* ---------- Block common "view/copy/inspect" shortcuts ---------- */
        // document.addEventListener('keydown', function (e) {
        //     const key = e.key ? e.key.toLowerCase() : '';
        //     if (
        //         key === 'f12' ||
        //         (e.ctrlKey && e.shiftKey && (key === 'i' || key === 'c' || key === 'j')) ||
        //         (e.ctrlKey && (key === 'u' || key === 's' || key === 'p'))
        //     ) {
        //         e.preventDefault();
        //     }
        // });

        // /* ---------- Hide page content when the tab/window loses focus ---------- */
        // const privacyOverlay = document.getElementById('privacyOverlay');

        // function showPrivacyOverlay() {
        //     privacyOverlay.classList.add('active');
        // }
        // function hidePrivacyOverlay() {
        //     privacyOverlay.classList.remove('active');
        // }

        // document.addEventListener('visibilitychange', function () {
        //     if (document.hidden) {
        //         showPrivacyOverlay();
        //     } else {
        //         hidePrivacyOverlay();
        //     }
        // });
        // window.addEventListener('blur', showPrivacyOverlay);
        // window.addEventListener('focus', hidePrivacyOverlay);
    </script>
</body>
</html>