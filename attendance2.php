<?php

session_start();
include 'db_connection.php';
include 'session_check.php';

include 'sidebar1.php';
date_default_timezone_set('Asia/Kolkata');

$employee_id = $_SESSION['employee_id'] ?? 0;
$employee_name = $_SESSION['employee_name'] ?? 'Employee';

$today = date('Y-m-d');
$current_time = date('H:i:s');

// Handle Check In
if (isset($_POST['checkin'])) {
    $check = mysqli_query(
        $conn,
        "SELECT * FROM attendance
         WHERE employee_id='$employee_id'
         AND attendance_date='$today'"
    );

    if (mysqli_num_rows($check) == 0) {
        $insert = mysqli_query(
            $conn,
            "INSERT INTO attendance
            (employee_id, employee_name, attendance_date, check_in, status)
            VALUES
            ('$employee_id', '$employee_name', '$today', '$current_time', 'Present')"
        );

        if ($insert) {
            echo "<script>alert('Checked In Successfully at $current_time');</script>";
            echo "<script>window.location.href='attendance.php';</script>";
        } else {
            die("Insert Error: " . mysqli_error($conn));
        }
    } else {
        echo "<script>alert('You have already checked in today');</script>";
    }
}

// Handle Check Out
if (isset($_POST['checkout'])) {
    $update = mysqli_query(
        $conn,
        "UPDATE attendance
        SET check_out='$current_time'
        WHERE employee_id='$employee_id'
        AND attendance_date='$today'"
    );
    
    if ($update) {
        echo "<script>alert('Checked Out Successfully at $current_time');</script>";
        echo "<script>window.location.href='attendance.php';</script>";
    }
}

// Get today's attendance status
$today_status = 'Not Marked';
$today_check_in = '';
$today_check_out = '';
$query = mysqli_query($conn, "SELECT * FROM attendance WHERE employee_id='$employee_id' AND attendance_date='$today'");
if ($query && mysqli_num_rows($query) > 0) {
    $row = mysqli_fetch_assoc($query);
    $today_status = $row['status'] ?? 'Not Marked';
    $today_check_in = $row['check_in'] ?? '';
    $today_check_out = $row['check_out'] ?? '';
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Attendance</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes, viewport-fit=cover">

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
            --orange-hover: #e65100;
            --input-bg: #f8f6f4;
            --input-border: #e8e0d8;
            --container-bg: rgba(255, 255, 255, 0.95);
        }

        body.dark {
            --bg: #12100e;
            --text: #f0e8e0;
            --card: #1d1815;
            --card-border: #3a322a;
            --secondary: #a89888;
            --input-bg: #2a2420;
            --input-border: #3a322a;
            --container-bg: rgba(26, 16, 12, 0.95);
            
            --orange-primary: #ffa726;
            --orange-light: #ffcc80;
            --orange-dark: #f57c00;
            --orange-gradient-start: #ff9800;
            --orange-gradient-end: #e65100;
            --orange-subtle: rgba(255, 152, 0, 0.12);
            --orange-shadow: rgba(255, 152, 0, 0.2);
            --orange-hover: #ffb74d;
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
            min-height: 100vh;
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
            padding: clamp(12px, 2.5vw, 35px);
            transition: all .4s ease;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }

        .main-content.expanded {
            margin-left: var(--sidebar-collapsed);
        }

        .container {
            background: var(--container-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 24px;
            border: 2px solid var(--card-border);
            padding: clamp(15px, 3vw, 35px);
            box-shadow: 0 20px 50px var(--orange-shadow);
            transition: all .35s ease;
            max-width: 1200px;
            width: 100%;
            margin: 0 auto;
            overflow: hidden;
        }

        .container:hover {
            box-shadow: 0 25px 60px var(--orange-shadow);
        }

        .title {
            text-align: center;
            margin-bottom: 10px;
            color: var(--orange-primary);
            font-size: clamp(22px, 3vw, 32px);
            font-weight: 700;
            position: relative;
        }

        .title::after {
            content: '';
            display: block;
            width: 60px;
            height: 4px;
            background: linear-gradient(90deg, var(--orange-gradient-start), var(--orange-primary));
            margin: 10px auto 0;
            border-radius: 10px;
        }

        .subtitle {
            text-align: center;
            color: var(--secondary);
            margin-bottom: 25px;
            font-size: clamp(14px, 1.2vw, 16px);
        }

        /* Today's Status Card */
        .status-card {
            background: var(--card);
            border: 2px solid var(--card-border);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 25px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            box-shadow: 0 4px 12px var(--orange-shadow);
        }

        .status-item {
            text-align: center;
            padding: 10px;
        }

        .status-item .label {
            font-size: 12px;
            color: var(--secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        .status-item .value {
            font-size: clamp(16px, 1.8vw, 20px);
            font-weight: 700;
            margin-top: 4px;
            color: var(--text);
        }

        .status-item .value.present {
            color: #22c55e;
        }

        .status-item .value.absent {
            color: #ef4444;
        }

        .status-item .value.not-marked {
            color: var(--secondary);
        }

        .btn-area {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .btn {
            border: none;
            padding: clamp(10px, 1.2vw, 14px) clamp(20px, 2.5vw, 30px);
            border-radius: 14px;
            color: white;
            cursor: pointer;
            font-size: clamp(13px, 1.2vw, 16px);
            font-weight: 700;
            transition: all .35s ease;
            min-width: clamp(100px, 12vw, 150px);
            letter-spacing: 0.5px;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.6s ease;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn:hover {
            transform: translateY(-4px) scale(1.02);
            box-shadow: 0 12px 30px rgba(0, 0, 0, .3);
        }

        .btn:active {
            transform: translateY(0px) scale(0.98);
        }

        .checkin {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            box-shadow: 0 8px 25px rgba(34, 197, 94, .3);
        }

        .checkin:hover {
            background: linear-gradient(135deg, #16a34a, #15803d);
            box-shadow: 0 12px 35px rgba(34, 197, 94, .4);
        }

        .checkin:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none !important;
        }

        .checkout {
            background: linear-gradient(135deg, var(--orange-gradient-start), var(--orange-primary));
            box-shadow: 0 8px 25px var(--orange-shadow);
        }

        .checkout:hover {
            background: linear-gradient(135deg, var(--orange-primary), var(--orange-dark));
            box-shadow: 0 12px 35px var(--orange-shadow);
        }

        .checkout:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none !important;
        }

        .legend {
            display: flex;
            justify-content: center;
            gap: clamp(10px, 2vw, 30px);
            flex-wrap: wrap;
            margin-bottom: 30px;
        }

        .legend div {
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--card);
            padding: clamp(6px, 0.8vw, 10px) clamp(12px, 1.5vw, 20px);
            border-radius: 30px;
            border: 2px solid var(--card-border);
            box-shadow: 0 4px 12px var(--orange-shadow);
            transition: all .3s ease;
            color: var(--text);
            font-size: clamp(11px, 1vw, 14px);
        }

        .legend div:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px var(--orange-shadow);
        }

        .box {
            width: clamp(14px, 1.2vw, 18px);
            height: clamp(14px, 1.2vw, 18px);
            border-radius: 50%;
            flex-shrink: 0;
        }

        .green {
            background: #22c55e;
        }

        .red {
            background: #ef4444;
        }

        .blue {
            background: #06b6d4;
        }

        .orange-box {
            background: var(--orange-primary);
        }

        .calendar-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .calendar-header h2 {
            color: var(--orange-primary);
            font-size: clamp(18px, 2vw, 24px);
            font-weight: 700;
        }

        /* ============================================================
           CALENDAR - SCROLLABLE WRAPPER WITH VISIBLE SCROLL INDICATOR
           ============================================================ */
        .calendar-wrapper {
            overflow-x: auto;
            overflow-y: visible;
            -webkit-overflow-scrolling: touch;
            scroll-behavior: smooth;
            padding: 5px 0 15px;
            margin: 0 -10px;
            position: relative;
            cursor: grab;
        }

        .calendar-wrapper:active {
            cursor: grabbing;
        }

        /* Scrollbar styling */
        .calendar-wrapper::-webkit-scrollbar {
            height: 8px;
            width: 8px;
        }

        .calendar-wrapper::-webkit-scrollbar-track {
            background: var(--bg);
            border-radius: 10px;
            margin: 0 10px;
        }

        .calendar-wrapper::-webkit-scrollbar-thumb {
            background: var(--orange-primary);
            border-radius: 10px;
            transition: background 0.3s ease;
        }

        .calendar-wrapper::-webkit-scrollbar-thumb:hover {
            background: var(--orange-dark);
        }

        /* Firefox scrollbar */
        .calendar-wrapper {
            scrollbar-width: auto;
            scrollbar-color: var(--orange-primary) var(--bg);
        }

        /* Scroll indicator arrows */
        .calendar-wrapper::before,
        .calendar-wrapper::after {
            content: '';
            position: absolute;
            top: 0;
            bottom: 0;
            width: 30px;
            pointer-events: none;
            z-index: 5;
        }

        .calendar-wrapper::before {
            left: 0;
            background: linear-gradient(to right, var(--bg), transparent);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .calendar-wrapper::after {
            right: 0;
            background: linear-gradient(to left, var(--bg), transparent);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .calendar-wrapper.scroll-left::before {
            opacity: 0.5;
        }

        .calendar-wrapper.scroll-right::after {
            opacity: 0.5;
        }

        .calendar {
            display: grid;
            grid-template-columns: repeat(7, minmax(90px, 1fr));
            gap: 10px;
            min-width: 700px;
            padding: 0 10px;
            margin-top: 10px;
        }

        .day-header {
            background: linear-gradient(135deg, var(--orange-gradient-start), var(--orange-primary));
            color: white;
            padding: 12px 10px;
            text-align: center;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            min-width: 60px;
        }

        .day {
            min-height: 100px;
            border-radius: 12px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            transition: all .3s ease;
            border: 2px solid var(--card-border);
            background: var(--card);
            box-shadow: 0 2px 8px var(--orange-shadow);
            position: relative;
            overflow: hidden;
            padding: 10px 6px;
            cursor: default;
            min-width: 55px;
        }

        .day:hover {
            transform: translateY(-4px) scale(1.02);
            box-shadow: 0 8px 20px var(--orange-shadow);
            z-index: 2;
        }

        .date-number {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 2px;
            color: var(--text);
            line-height: 1.2;
        }

        .day-name {
            font-size: 10px;
            opacity: .7;
            color: var(--secondary);
            text-transform: uppercase;
            line-height: 1.2;
        }

        .empty {
            min-height: 100px;
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
            cursor: default;
        }

        .empty:hover {
            transform: none !important;
            box-shadow: none !important;
        }

        /* Status Styles */
        .present {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.9), rgba(22, 163, 74, 0.95)) !important;
            border-color: #16a34a !important;
        }

        .present .date-number,
        .present .day-name {
            color: white !important;
        }

        .present::after {
            content: '✓';
            position: absolute;
            top: 6px;
            right: 8px;
            font-size: 18px;
            font-weight: bold;
            color: white;
        }

        .absent {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.9), rgba(220, 38, 38, 0.95)) !important;
            border-color: #dc2626 !important;
        }

        .absent .date-number,
        .absent .day-name {
            color: white !important;
        }

        .absent::after {
            content: '✕';
            position: absolute;
            top: 6px;
            right: 8px;
            font-size: 18px;
            font-weight: bold;
            color: white;
        }

        .leave {
            background: linear-gradient(135deg, rgba(6, 182, 212, 0.9), rgba(8, 145, 178, 0.95)) !important;
            border-color: #0891b2 !important;
        }

        .leave .date-number,
        .leave .day-name {
            color: white !important;
        }

        .leave::after {
            content: '📝';
            position: absolute;
            top: 4px;
            right: 6px;
            font-size: 16px;
        }

        .saturday {
            background: linear-gradient(135deg, var(--orange-gradient-start), var(--orange-primary)) !important;
            border-color: var(--orange-dark) !important;
        }

        .saturday .date-number,
        .saturday .day-name {
            color: white !important;
        }

        .saturday::after {
            content: 'SAT';
            position: absolute;
            top: 6px;
            right: 8px;
            font-size: 8px;
            font-weight: 700;
            color: white;
            letter-spacing: 0.5px;
        }

        .sunday {
            background: linear-gradient(135deg, rgba(168, 85, 247, 0.9), rgba(147, 51, 234, 0.95)) !important;
            border-color: #9333ea !important;
        }

        .sunday .date-number,
        .sunday .day-name {
            color: white !important;
        }

        .sunday::after {
            content: 'SUN';
            position: absolute;
            top: 6px;
            right: 8px;
            font-size: 8px;
            font-weight: 700;
            color: white;
            letter-spacing: 0.5px;
        }

        .today {
            border: 3px solid #fbbf24 !important;
            box-shadow: 0 0 15px rgba(251, 191, 36, .4), 0 0 30px rgba(251, 191, 36, .2) !important;
            animation: todayGlow 1.5s ease-in-out infinite;
            z-index: 3;
        }

        @keyframes todayGlow {
            0%, 100% {
                box-shadow: 0 0 15px rgba(251, 191, 36, .4), 0 0 30px rgba(251, 191, 36, .2);
            }
            50% {
                box-shadow: 0 0 25px rgba(251, 191, 36, .6), 0 0 50px rgba(251, 191, 36, .3);
            }
        }

        /* Dark mode overrides */
        body.dark .present {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.85), rgba(22, 163, 74, 0.95)) !important;
        }

        body.dark .absent {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.85), rgba(220, 38, 38, 0.95)) !important;
        }

        body.dark .leave {
            background: linear-gradient(135deg, rgba(6, 182, 212, 0.85), rgba(8, 145, 178, 0.95)) !important;
        }

        body.dark .saturday {
            background: linear-gradient(135deg, #f57c00, #e65100) !important;
        }

        body.dark .sunday {
            background: linear-gradient(135deg, rgba(168, 85, 247, 0.85), rgba(147, 51, 234, 0.95)) !important;
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

        /* ============================================================
           RESPONSIVE BREAKPOINTS
           ============================================================ */

        /* Tablets & Small Laptops */
        @media (max-width: 1024px) {
            .calendar {
                grid-template-columns: repeat(7, minmax(80px, 1fr));
                gap: 8px;
                min-width: 600px;
            }
            .day {
                min-height: 85px;
                border-radius: 10px;
            }
            .day-header {
                padding: 10px 8px;
                font-size: 12px;
                border-radius: 10px;
            }
            .date-number {
                font-size: 20px;
            }
            .day-name {
                font-size: 9px;
            }
            .container {
                max-width: 95%;
            }
        }

        /* iPads & Tablets */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 85px;
                padding: 16px 12px;
            }
            .container {
                padding: 18px;
                border-radius: 18px;
                max-width: 98%;
            }
            .calendar-wrapper {
                margin: 0 -8px;
                padding: 5px 0 15px;
            }
            .calendar {
                grid-template-columns: repeat(7, minmax(70px, 1fr));
                gap: 6px;
                min-width: 520px;
                padding: 0 8px;
            }
            .day {
                min-height: 70px;
                border-radius: 10px;
                padding: 8px 4px;
            }
            .day-header {
                padding: 8px 6px;
                font-size: 10px;
                border-radius: 8px;
                letter-spacing: 0.3px;
            }
            .date-number {
                font-size: 18px;
            }
            .day-name {
                font-size: 8px;
            }
            .title {
                font-size: 24px;
            }
            .btn {
                padding: 12px 20px;
                font-size: 14px;
                min-width: 120px;
            }
            .legend {
                gap: 10px;
            }
            .legend div {
                padding: 6px 14px;
                font-size: 12px;
            }
            .status-card {
                grid-template-columns: repeat(2, 1fr);
                padding: 15px;
                gap: 10px;
            }
            .present::after,
            .absent::after {
                font-size: 14px;
                top: 4px;
                right: 5px;
            }
            .leave::after {
                font-size: 12px;
                top: 3px;
                right: 4px;
            }
            .saturday::after,
            .sunday::after {
                font-size: 7px;
                top: 4px;
                right: 5px;
            }
        }

        /* Mobile Phones */
        @media (max-width: 480px) {
            .main-content {
                margin-left: 0;
                padding: 8px 4px;
                justify-content: center;
                align-items: flex-start;
            }
            .main-content.expanded {
                margin-left: 0;
            }
            .container {
                padding: 12px 6px;
                border-radius: 12px;
                max-width: 100%;
            }
            .container:hover {
                box-shadow: 0 20px 50px var(--orange-shadow);
            }
            .title {
                font-size: 18px;
                margin-bottom: 6px;
            }
            .title::after {
                width: 40px;
                height: 3px;
                margin-top: 6px;
            }
            .subtitle {
                font-size: 12px;
                margin-bottom: 15px;
            }
            .status-card {
                grid-template-columns: 1fr 1fr;
                gap: 6px;
                padding: 10px;
                border-radius: 12px;
                margin-bottom: 15px;
            }
            .status-item {
                padding: 4px;
            }
            .status-item .label {
                font-size: 10px;
            }
            .status-item .value {
                font-size: 14px;
            }
            .btn-area {
                gap: 8px;
                margin-bottom: 15px;
            }
            .btn {
                padding: 8px 14px;
                font-size: 12px;
                min-width: 80px;
                border-radius: 10px;
            }
            .btn:hover {
                transform: none;
            }
            .btn:active {
                transform: scale(0.95);
            }
            .legend {
                gap: 4px;
                margin-bottom: 15px;
            }
            .legend div {
                padding: 3px 8px;
                font-size: 9px;
                border-radius: 16px;
                gap: 4px;
            }
            .box {
                width: 10px;
                height: 10px;
            }
            .calendar-header h2 {
                font-size: 16px;
                margin-bottom: 6px;
            }
            .calendar-wrapper {
                margin: 0 -10px;
                padding: 5px 0 12px;
            }
            .calendar-wrapper::-webkit-scrollbar {
                height: 6px;
            }
            .calendar {
                grid-template-columns: repeat(7, minmax(60px, 1fr));
                gap: 5px;
                min-width: 420px;
                padding: 0 10px;
            }
            .day-header {
                padding: 6px 4px;
                font-size: 9px;
                border-radius: 6px;
                letter-spacing: 0.3px;
            }
            .day {
                min-height: 55px;
                border-radius: 8px;
                padding: 6px 3px;
                border-width: 1.5px;
            }
            .day:hover {
                transform: none;
                box-shadow: 0 2px 8px var(--orange-shadow);
            }
            .day:active {
                transform: scale(0.95);
            }
            .date-number {
                font-size: 15px;
                margin-bottom: 0;
            }
            .day-name {
                font-size: 7px;
            }
            .present::after,
            .absent::after {
                font-size: 11px;
                top: 3px;
                right: 4px;
            }
            .leave::after {
                font-size: 10px;
                top: 2px;
                right: 3px;
            }
            .saturday::after,
            .sunday::after {
                font-size: 6px;
                top: 3px;
                right: 4px;
            }
            .today {
                border-width: 2px;
                box-shadow: 0 0 10px rgba(251, 191, 36, .4), 0 0 20px rgba(251, 191, 36, .2) !important;
            }
            .empty {
                min-height: 55px;
            }
        }

        /* Very Small Phones (iPhone SE) */
        @media (max-width: 380px) {
            .main-content {
                padding: 4px 2px;
            }
            .container {
                padding: 8px 4px;
                border-radius: 10px;
            }
            .title {
                font-size: 16px;
                margin-bottom: 4px;
            }
            .subtitle {
                font-size: 11px;
                margin-bottom: 10px;
            }
            .status-card {
                grid-template-columns: 1fr 1fr;
                gap: 4px;
                padding: 8px;
                border-radius: 10px;
                margin-bottom: 10px;
            }
            .status-item .value {
                font-size: 13px;
            }
            .btn {
                padding: 6px 10px;
                font-size: 11px;
                min-width: 70px;
                border-radius: 8px;
            }
            .btn-area {
                gap: 6px;
                margin-bottom: 10px;
            }
            .legend {
                gap: 3px;
                margin-bottom: 10px;
            }
            .legend div {
                padding: 2px 6px;
                font-size: 8px;
                border-radius: 12px;
            }
            .box {
                width: 8px;
                height: 8px;
            }
            .calendar-header h2 {
                font-size: 14px;
            }
            .calendar-wrapper {
                margin: 0 -8px;
                padding: 5px 0 10px;
            }
            .calendar-wrapper::-webkit-scrollbar {
                height: 5px;
            }
            .calendar {
                grid-template-columns: repeat(7, minmax(50px, 1fr));
                gap: 4px;
                min-width: 350px;
                padding: 0 8px;
            }
            .day-header {
                padding: 5px 3px;
                font-size: 8px;
                border-radius: 5px;
            }
            .day {
                min-height: 45px;
                border-radius: 6px;
                padding: 4px 2px;
                border-width: 1px;
            }
            .date-number {
                font-size: 13px;
            }
            .day-name {
                font-size: 6px;
            }
            .present::after,
            .absent::after {
                font-size: 9px;
                top: 2px;
                right: 3px;
            }
            .leave::after {
                font-size: 8px;
                top: 1px;
                right: 2px;
            }
            .saturday::after,
            .sunday::after {
                font-size: 5px;
                top: 2px;
                right: 3px;
            }
            .empty {
                min-height: 45px;
            }
        }

        /* Landscape Mode on Phones */
        @media (max-height: 500px) and (orientation: landscape) {
            .main-content {
                padding: 6px 12px;
            }
            .container {
                padding: 10px 14px;
                border-radius: 14px;
            }
            .title {
                font-size: 18px;
                margin-bottom: 6px;
            }
            .subtitle {
                font-size: 12px;
                margin-bottom: 10px;
            }
            .status-card {
                padding: 10px;
                margin-bottom: 12px;
                grid-template-columns: repeat(4, 1fr);
            }
            .status-item .value {
                font-size: 14px;
            }
            .btn-area {
                margin-bottom: 10px;
                gap: 8px;
            }
            .btn {
                padding: 6px 12px;
                font-size: 11px;
                min-width: 70px;
            }
            .legend {
                margin-bottom: 10px;
                gap: 6px;
            }
            .legend div {
                padding: 3px 10px;
                font-size: 10px;
            }
            .calendar-wrapper {
                margin: 0 -8px;
                padding: 5px 0 10px;
            }
            .calendar {
                grid-template-columns: repeat(7, minmax(65px, 1fr));
                gap: 5px;
                min-width: 450px;
                padding: 0 8px;
            }
            .day {
                min-height: 50px;
            }
            .date-number {
                font-size: 16px;
            }
            .day-header {
                padding: 6px 4px;
                font-size: 9px;
            }
            .empty {
                min-height: 50px;
            }
        }

        /* Touch-friendly improvements */
        @media (pointer: coarse) {
            .btn,
            .day {
                touch-action: manipulation;
                -webkit-tap-highlight-color: transparent;
            }
            .btn:active {
                transform: scale(0.95);
            }
        }

        /* Reduced motion preference */
        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                animation-duration: 0.001ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.001ms !important;
            }
            .today {
                animation: none !important;
            }
        }
    </style>

</head>

<body>

    <div class="main-content" id="mainContent">
        <div class="container">

            <h1 class="title">📋 Attendance Management</h1>
            <p class="subtitle">Track your daily attendance, check in/out and monitor your monthly records.</p>

            <!-- Today's Status -->
            <div class="status-card">
                <div class="status-item">
                    <div class="label">Date</div>
                    <div class="value"><?php echo date('d M Y'); ?></div>
                </div>
                <div class="status-item">
                    <div class="label">Status</div>
                    <div class="value <?php echo strtolower(str_replace(' ', '-', $today_status)); ?>">
                        <?php echo htmlspecialchars($today_status); ?>
                    </div>
                </div>
                <div class="status-item">
                    <div class="label">Check In</div>
                    <div class="value"><?php echo $today_check_in ? date('h:i A', strtotime($today_check_in)) : '--:--'; ?></div>
                </div>
                <div class="status-item">
                    <div class="label">Check Out</div>
                    <div class="value"><?php echo $today_check_out ? date('h:i A', strtotime($today_check_out)) : '--:--'; ?></div>
                </div>
            </div>

            <form method="post">
                <div class="btn-area">
                    <button
                        class="btn checkin"
                        name="checkin"
                        <?php echo ($today_status != 'Not Marked') ? 'disabled' : ''; ?>>
                        ✅ Check In
                    </button>

                    <button
                        class="btn checkout"
                        name="checkout"
                        <?php echo ($today_status == 'Not Marked' || $today_check_out) ? 'disabled' : ''; ?>>
                        🚪 Check Out
                    </button>
                </div>
            </form>

            <div class="legend">
                <div>
                    <span class="box green"></span>
                    Present
                </div>
                <div>
                    <span class="box red"></span>
                    Absent
                </div>
                <div>
                    <span class="box blue"></span>
                    Leave
                </div>
                <div>
                    <span class="box orange-box"></span>
                    Weekend
                </div>
            </div>

            <div class="calendar-header">
                <h2>📅 <?php echo date('F Y'); ?></h2>
            </div>

            <!-- Scrollable Calendar Wrapper -->
            <div class="calendar-wrapper" id="calendarWrapper">
                <div class="calendar">

                    <div class="day-header">Sun</div>
                    <div class="day-header">Mon</div>
                    <div class="day-header">Tue</div>
                    <div class="day-header">Wed</div>
                    <div class="day-header">Thu</div>
                    <div class="day-header">Fri</div>
                    <div class="day-header">Sat</div>

                    <?php
                    $year = date('Y');
                    $month = date('m');

                    $firstDay = date('w', strtotime("$year-$month-01"));
                    $totalDays = date('t');

                    // Empty boxes before first day
                    for ($i = 0; $i < $firstDay; $i++) {
                        echo "<div class='day empty'></div>";
                    }

                    // Calendar dates
                    for ($day = 1; $day <= $totalDays; $day++) {
                        $date = "$year-$month-" . str_pad($day, 2, '0', STR_PAD_LEFT);
                        $dayName = date('l', strtotime($date));
                        $todayClass = '';

                        if ($date == $today) {
                            $todayClass = 'today';
                        }

                        if ($dayName == 'Saturday') {
                            $class = 'saturday';
                        } elseif ($dayName == 'Sunday') {
                            $class = 'sunday';
                        } else {
                            $query = mysqli_query(
                                $conn,
                                "SELECT status
                                 FROM attendance
                                 WHERE employee_id='$employee_id'
                                 AND attendance_date='$date'"
                            );

                            if (mysqli_num_rows($query) > 0) {
                                $row = mysqli_fetch_assoc($query);

                                if ($row['status'] == 'Present') {
                                    $class = 'present';
                                } elseif ($row['status'] == 'Leave') {
                                    $class = 'leave';
                                } else {
                                    $class = 'absent';
                                }
                            } else {
                                if ($date < $today) {
                                    $class = 'absent';
                                } else {
                                    $class = '';
                                }
                            }
                        }

                        echo "
                            <div class='day $class $todayClass'>
                                <div class='date-number'>$day</div>
                                <div class='day-name'>" . date('D', strtotime($date)) . "</div>
                            </div>";
                    }
                    ?>

                </div>
            </div>

            <!-- Scroll hint (shows on mobile) -->
            <div style="text-align: center; margin-top: 10px; font-size: 12px; color: var(--secondary); display: none;" id="scrollHint">
                👈 Swipe to see more days 👉
            </div>

        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const mc = document.getElementById("mainContent");
            
            if (localStorage.getItem("sidebarState") === "collapsed" && mc) {
                mc.classList.add("expanded");
            }
            
            if (localStorage.getItem('theme') === 'dark') {
                document.body.classList.add('dark');
            }
            
            const profileImg = document.querySelector(".employee-profile img");
            if (profileImg) {
                profileImg.addEventListener("click", function() {
                    setTimeout(() => {
                        if (mc) {
                            const sb = document.getElementById("sidebar");
                            if (sb && sb.classList.contains("collapsed")) {
                                mc.classList.add("expanded");
                            } else {
                                mc.classList.remove("expanded");
                            }
                        }
                    }, 50);
                });
            }
            
            const sl = document.querySelector(".sidebar-logo");
            if (sl) {
                sl.addEventListener("click", function() {
                    setTimeout(() => {
                        if (mc) {
                            const sb = document.getElementById("sidebar");
                            if (sb && sb.classList.contains("collapsed")) {
                                mc.classList.add("expanded");
                            } else {
                                mc.classList.remove("expanded");
                            }
                        }
                    }, 50);
                });
            }
            
            window.addEventListener('storage', function(e) {
                if (e.key === 'theme') {
                    if (e.newValue === 'dark') {
                        document.body.classList.add('dark');
                    } else {
                        document.body.classList.remove('dark');
                    }
                }
                if (e.key === 'sidebarState') {
                    if (mc) {
                        if (e.newValue === 'collapsed') {
                            mc.classList.add('expanded');
                        } else {
                            mc.classList.remove('expanded');
                        }
                    }
                }
            });

            // Scroll hint visibility for mobile
            const wrapper = document.getElementById('calendarWrapper');
            const hint = document.getElementById('scrollHint');
            
            if (wrapper && hint) {
                function checkScroll() {
                    if (wrapper.scrollWidth > wrapper.clientWidth) {
                        hint.style.display = 'block';
                    } else {
                        hint.style.display = 'none';
                    }
                }
                
                checkScroll();
                window.addEventListener('resize', checkScroll);
                
                // Hide hint after scrolling
                wrapper.addEventListener('scroll', function() {
                    hint.style.opacity = '0';
                    setTimeout(() => {
                        hint.style.display = 'none';
                    }, 500);
                });
            }
        });
    </script>

</body>
</html>