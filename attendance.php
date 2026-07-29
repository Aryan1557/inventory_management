<?php

session_start();
include 'db_connection.php';

include 'session_check.php';

include 'sidebar1.php';
date_default_timezone_set('Asia/Kolkata');


$employee_id = $_SESSION['user_id'];
$employee_name = $_SESSION['name'];

$today = date('Y-m-d');
$current_time = date('H:i:s');

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
    (employee_id, name, attendance_date, check_in, status)
    VALUES
    ('$employee_id', '$employee_name', '$today', '$current_time', 'Present')"
        );

        if ($insert) {
            echo "<script>alert('Checked In Successfully');</script>";
        } else {
            die("Insert Error: " . mysqli_error($conn));
        }
    } else {
        echo "<script>alert('You have already checked in today');</script>";
    }
}

if (isset($_POST['checkout'])) {
    mysqli_query(
        $conn,
        "UPDATE attendance
    SET check_out='$current_time'
    WHERE employee_id='$employee_id'
    AND attendance_date='$today'"
    );
}

echo "Today: " . $today . "<br>";
echo "Current Time: " . $current_time . "<br>";
echo "Employee ID: " . $employee_id . "<br>";
?>

<!DOCTYPE html>
<html>

<head>
    <title>Attendance</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
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
        }

        body.dark {
            --bg: #12100e;
            --text: #f0e8e0;
            --card: #1d1815;
            --card-border: #3a322a;
            --secondary: #a89888;
            --input-bg: #2a2420;
            --input-border: #3a322a;
            
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

        /* Welcome Card */
        .welcome-card {
            background: linear-gradient(135deg,
                    var(--orange-gradient-start),
                    var(--orange-primary),
                    var(--orange-dark));
            position: relative;
            overflow: hidden;
            padding: 40px;
            border-radius: 24px;
            margin-bottom: 40px;
            box-shadow: 0 20px 50px var(--orange-shadow);
            color: white;
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
            margin-bottom: 10px;
            font-size: 32px;
            position: relative;
            z-index: 1;
            color: white;
        }

        .welcome-card p {
            opacity: .9;
            position: relative;
            z-index: 1;
            font-size: 16px;
            color: #ffe0b0;
        }

        /* Alert Messages */
        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-weight: 500;
            text-align: center;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.1);
            color: #22c55e;
            border: 1px solid rgba(34, 197, 94, 0.3);
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.4);
        }

        /* Action Buttons */
        .action-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 35px;
        }

        .action-card {
            background: var(--card);
            border: 2px solid var(--card-border);
            border-radius: 24px;
            padding: 30px;
            cursor: pointer;
            transition: all .35s ease;
            box-shadow: 0 8px 20px var(--orange-shadow), 0 18px 40px rgba(255, 152, 0, 0.06);
            text-align: left;
            width: 100%;
            color: var(--text);
            position: relative;
            overflow: hidden;
        }

        .action-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
        }

        .action-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 20px 40px var(--orange-shadow);
        }

        .action-card h3 {
            font-size: 22px;
            margin-bottom: 10px;
        }

        .action-card p {
            color: var(--secondary);
        }

        .checkin-card {
            border-top: 6px solid #22c55e;
        }

        .checkin-card::before {
            background: #22c55e;
        }

        .checkin-card:hover {
            border-color: #22c55e;
            box-shadow: 0 20px 40px rgba(34, 197, 94, 0.2);
        }

        .checkout-card {
            border-top: 6px solid var(--orange-primary);
        }

        .checkout-card::before {
            background: var(--orange-primary);
        }

        .checkout-card:hover {
            border-color: var(--orange-primary);
            box-shadow: 0 20px 40px var(--orange-shadow);
        }

        /* Legend */
        .legend {
            display: flex;
            gap: 20px;
            margin-bottom: 25px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 500;
            color: var(--secondary);
            background: var(--card);
            padding: 10px 18px;
            border-radius: 30px;
            border: 2px solid var(--card-border);
            box-shadow: 0 4px 12px var(--orange-shadow);
            transition: all .3s ease;
        }

        .legend-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px var(--orange-shadow);
        }

        .legend-dot {
            width: 14px;
            height: 14px;
            border-radius: 50%;
        }

        .legend-dot.green {
            background: #22c55e;
        }

        .legend-dot.red {
            background: #ef4444;
        }

        .legend-dot.blue {
            background: #06b6d4;
        }

        .legend-dot.orange {
            background: var(--orange-primary);
        }

        /* Calendar Card */
        .calendar-card {
            background: var(--card);
            border: 2px solid var(--card-border);
            border-radius: 24px;
            padding: 30px;
            box-shadow: 0 10px 25px var(--orange-shadow), 0 20px 50px rgba(255, 152, 0, 0.06);
            transition: all .35s ease;
        }

        .calendar-card:hover {
            box-shadow: 0 15px 35px var(--orange-shadow), 0 25px 60px rgba(255, 152, 0, 0.08);
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .calendar-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--orange-primary);
            position: relative;
        }

        .calendar-title::after {
            content: '';
            display: block;
            width: 40px;
            height: 4px;
            background: linear-gradient(90deg, var(--orange-gradient-start), var(--orange-primary));
            margin-top: 5px;
            border-radius: 10px;
        }

        .calendar-nav {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .nav-btn {
            padding: 10px 20px;
            border: 2px solid var(--card-border);
            border-radius: 12px;
            text-decoration: none;
            color: var(--text);
            font-weight: 600;
            transition: all 0.3s ease;
            background: var(--card);
        }

        .nav-btn:hover {
            border-color: var(--orange-primary);
            color: var(--orange-primary);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px var(--orange-shadow);
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 8px;
        }

        .day-header {
            padding: 12px;
            text-align: center;
            font-weight: 700;
            font-size: 13px;
            color: var(--secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .day {
            aspect-ratio: 1;
            border-radius: 14px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            font-weight: 600;
            min-height: 60px;
            border: 2px solid transparent;
            background: var(--card);
            color: var(--text);
        }

        .day:hover {
            transform: scale(1.08);
            z-index: 3;
            box-shadow: 0 8px 20px var(--orange-shadow);
        }

        .day.empty {
            cursor: default;
            background: transparent !important;
            border: none !important;
            color: transparent !important;
        }

        .day.empty:hover {
            transform: none;
        }

        .date-num {
            font-size: 18px;
            font-weight: 700;
            position: relative;
            z-index: 1;
        }

        .day-name {
            font-size: 10px;
            text-transform: uppercase;
            margin-top: 2px;
            position: relative;
            z-index: 1;
        }

        /* PRESENT - Green */
        .day.present {
            background: #22c55e !important;
            border-color: #16a34a !important;
            color: white !important;
            box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3);
        }

        body.dark .day.present {
            background: #22c55e !important;
            border-color: #4ade80 !important;
            color: white !important;
        }

        /* ABSENT - Red */
        .day.absent {
            background: #ef4444 !important;
            border-color: #dc2626 !important;
            color: white !important;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        body.dark .day.absent {
            background: #ef4444 !important;
            border-color: #f87171 !important;
            color: white !important;
        }

        /* LEAVE - Blue */
        .day.leave {
            background: #06b6d4 !important;
            border-color: #0891b2 !important;
            color: white !important;
            box-shadow: 0 4px 12px rgba(6, 182, 212, 0.3);
        }

        body.dark .day.leave {
            background: #06b6d4 !important;
            border-color: #22d3ee !important;
            color: white !important;
        }

        /* WEEKEND - Orange */
        .day.saturday {
            background: var(--orange-primary) !important;
            border-color: var(--orange-dark) !important;
            color: white !important;
            box-shadow: 0 4px 12px var(--orange-shadow);
        }

        .day.sunday {
            background: #a855f7 !important;
            border-color: #9333ea !important;
            color: white !important;
            box-shadow: 0 4px 12px rgba(168, 85, 247, 0.3);
        }

        body.dark .day.saturday {
            background: var(--orange-primary) !important;
            border-color: var(--orange-light) !important;
            color: white !important;
        }

        body.dark .day.sunday {
            background: #a855f7 !important;
            border-color: #c084fc !important;
            color: white !important;
        }

        /* FUTURE */
        .day:not(.present):not(.absent):not(.leave):not(.saturday):not(.sunday):not(.empty) {
            background: var(--card);
            border-color: var(--card-border);
            color: var(--secondary);
        }

        /* TODAY - Gold Glow */
        .day.today {
            border: 4px solid #fbbf24 !important;
            box-shadow: 0 0 20px rgba(251, 191, 36, 0.7), 0 0 50px rgba(251, 191, 36, 0.4), inset 0 0 20px rgba(251, 191, 36, 0.1) !important;
            animation: todayGlow 1.5s ease-in-out infinite;
            z-index: 2;
        }

        @keyframes todayGlow {

            0%,
            100% {
                box-shadow: 0 0 20px rgba(251, 191, 36, 0.7), 0 0 50px rgba(251, 191, 36, 0.4), inset 0 0 20px rgba(251, 191, 36, 0.1);
            }

            50% {
                box-shadow: 0 0 35px rgba(251, 191, 36, 0.9), 0 0 80px rgba(251, 191, 36, 0.6), inset 0 0 30px rgba(251, 191, 36, 0.2);
            }
        }

        /* Status Indicators */
        .day.present::after {
            content: '✓';
            position: absolute;
            top: 4px;
            right: 6px;
            font-size: 10px;
            font-weight: bold;
        }

        .day.absent::after {
            content: '✕';
            position: absolute;
            top: 4px;
            right: 6px;
            font-size: 10px;
            font-weight: bold;
        }

        .day.leave::after {
            content: '📝';
            position: absolute;
            top: 2px;
            right: 4px;
            font-size: 10px;
        }

        .day.saturday::after {
            content: 'SAT';
            position: absolute;
            top: 4px;
            right: 6px;
            font-size: 9px;
            font-weight: bold;
        }

        .day.sunday::after {
            content: 'SUN';
            position: absolute;
            top: 4px;
            right: 6px;
            font-size: 9px;
            font-weight: bold;
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

            .calendar-grid {
                gap: 4px;
            }

            .day {
                min-height: 40px;
                border-radius: 8px;
            }

            .date-num {
                font-size: 14px;
            }

            .welcome-card {
                padding: 25px 20px;
            }

            .welcome-card h1 {
                font-size: 24px;
            }

            .action-grid {
                grid-template-columns: 1fr;
            }

            .action-card {
                padding: 20px;
            }
        }

        @media (max-width: 480px) {
            .main-content {
                padding: 10px;
            }

            .calendar-card {
                padding: 15px;
            }

            .day {
                min-height: 35px;
                border-radius: 6px;
            }

            .date-num {
                font-size: 12px;
            }

            .day-name {
                font-size: 8px;
            }

            .legend-item {
                padding: 6px 12px;
                font-size: 11px;
            }
        }
    </style>
</head>

<body>
    <div class="main-content" id="mainContent">

        <div class="welcome-card">
            <h1>Attendance Management 🕒</h1>
            <p>Track your daily attendance, check in/out and monitor your monthly records.</p>
        </div>

        <form method="post">
            <div class="action-grid">
                <button class="action-card checkin-card" name="checkin">
                    <h3>✅ Check In</h3>
                    <p>Mark your attendance for today.</p>
                </button>
                <button class="action-card checkout-card" name="checkout">
                    <h3>🚪 Check Out</h3>
                    <p>Record your departure time.</p>
                </button>
            </div>
        </form>

        <div class="legend">
            <div class="legend-item"><span class="legend-dot green"></span> Present</div>
            <div class="legend-item"><span class="legend-dot red"></span> Absent</div>
            <div class="legend-item"><span class="legend-dot blue"></span> Leave</div>
            <div class="legend-item"><span class="legend-dot orange"></span> Weekend</div>
        </div>

        <div class="calendar-card">
            <div class="calendar-header">
                <h2 class="calendar-title">📅 <?php echo date('F Y'); ?></h2>
            </div>

            <div class="calendar-grid">
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

                for ($i = 0; $i < $firstDay; $i++) {
                    echo "<div class='day empty'></div>";
                }

                for ($day = 1; $day <= $totalDays; $day++) {
                    $date = "$year-$month-" . str_pad($day, 2, '0', STR_PAD_LEFT);
                    $dayName = date('l', strtotime($date));
                    $todayClass = ($date == $today) ? 'today' : '';

                    if ($dayName == 'Saturday') {
                        $class = 'saturday';
                    } elseif ($dayName == 'Sunday') {
                        $class = 'sunday';
                    } else {
                        $query = mysqli_query($conn, "SELECT status FROM attendance WHERE employee_id='$employee_id' AND attendance_date='$date'");
                        if (mysqli_num_rows($query) > 0) {
                            $row = mysqli_fetch_assoc($query);
                            if ($row['status'] == 'Present') $class = 'present';
                            elseif ($row['status'] == 'Leave') $class = 'leave';
                            else $class = 'absent';
                        } else {
                            if ($date < $today) $class = 'absent';
                            else $class = '';
                        }
                    }

                    $allClasses = trim("day $class $todayClass");
                    echo "<div class='$allClasses'>
                        <span class='date-num'>$day</span>
                        <span class='day-name'>" . date('D', strtotime($date)) . "</span>
                      </div>";
                }
                ?>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const mc = document.getElementById("mainContent");
            if (localStorage.getItem("sidebarState") === "collapsed" && mc) mc.classList.add("expanded");
            
            // Theme handling
            if (localStorage.getItem('theme') === 'dark') {
                document.body.classList.add('dark');
            }
            
            const sl = document.querySelector(".sidebar-logo");
            if (sl) sl.addEventListener("click", function() {
                setTimeout(() => {
                    if (mc) {
                        const sb = document.getElementById("sidebar");
                        if (sb && sb.classList.contains("collapsed")) mc.classList.add("expanded");
                        else mc.classList.remove("expanded");
                    }
                }, 50);
            });
            
            // Cross-tab sync
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
        });
    </script>
</body>

</html>