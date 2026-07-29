<?php
session_start();
include 'db_connection.php';
include 'session_check.php';

/* ---------------------------------------------------------
   Logged-in user's name
   Adjust the $_SESSION keys below to match whatever your
   login script actually stores (check session_check.php /
   login.php to confirm the exact key names).
--------------------------------------------------------- */
$logged_in_name = "Admin"; // fallback if nothing is found

if (isset($_SESSION['user_name'])) {
    $logged_in_name = $_SESSION['user_name'];
} elseif (isset($_SESSION['username'])) {
    $logged_in_name = $_SESSION['username'];
} elseif (isset($_SESSION['name'])) {
    $logged_in_name = $_SESSION['name'];
} elseif (isset($_SESSION['user_id'])) {
    // fallback: look the name up from the users table using a prepared statement
    $stmt = mysqli_prepare($conn, "SELECT name FROM users WHERE id = ? LIMIT 1");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $db_name);
        if (mysqli_stmt_fetch($stmt) && !empty($db_name)) {
            $logged_in_name = $db_name;
        }
        mysqli_stmt_close($stmt);
    }
}

/* Total Agencies */
$agency_result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM agency");
$total_agencies = mysqli_fetch_assoc($agency_result)['total'];

/* Total Clients */
$client_result = mysqli_query($conn, "SELECT COUNT(DISTINCT agency_name) AS total FROM client");

if (!$client_result) {
    die("Client Query Failed: " . mysqli_error($conn));
}

$total_clients = mysqli_fetch_assoc($client_result)['total'];

/* Total Users */
$user_result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users");
$total_users = mysqli_fetch_assoc($user_result)['total'];

/* Attendance Percentage */
$attendance_result = mysqli_query($conn, "
    SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN status='Present' THEN 1 ELSE 0 END) AS present_count
    FROM attendance
");

$attendance = mysqli_fetch_assoc($attendance_result);

if ($attendance['total'] > 0) {
    $attendance_percentage = round(
        ($attendance['present_count'] / $attendance['total']) * 100
    );
} else {
    $attendance_percentage = 0;
}

/* Expired AMC Agencies */
$expired_amc = [];

$amc_query = mysqli_query($conn, "
    SELECT agency_name
    FROM client
    WHERE amc_expiry < CURDATE()
");

if ($amc_query) {
    while ($row = mysqli_fetch_assoc($amc_query)) {
        $expired_amc[] = $row['agency_name'];
    }
}
include 'sidebar.php';

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>

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
            /* stop text selection so page can't be selected/copied easily */
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
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

            padding: 35px;

            transition: .4s;

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
            padding: 40px;
            border-radius: 24px;
            margin-bottom: 40px;
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
            font-size: 32px;
            color: white;
            position: relative;
            z-index: 1;
        }

        .welcome-card p {
            opacity: .9;
            color: #ffe0b0;
            position: relative;
            z-index: 1;
        }

        /* Statistics */
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 35px;
        }

        body.dark .section-title {
            color: #f0e8d0;
        }

        .card {

            background: var(--card);

            border: 1px solid var(--card-border);

            border-radius: 22px;

            padding: 28px;

            box-shadow:
                0 8px 24px rgba(60, 50, 40, .06);

            transition: .35s;
            position: relative;
            overflow: hidden;

        }

        .card:hover {

            transform: translateY(-8px);

            box-shadow:
                0 18px 35px var(--orange-shadow);

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

        .card,
        .action-card {
            background: var(--card);
            border: 1px solid var(--card-border);
            box-shadow: 0 4px 10px var(--orange-subtle), 0 10px 25px var(--orange-shadow);
        }

        .card h2 {

            color: var(--orange-primary);

            font-size: 50px;

            font-weight: 700;

        }

        .card p {
            font-size: 15px;
            font-weight: 600;
            letter-spacing: .3px;
            color: var(--secondary);
        }

        .card p,
        .action-card p {
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

        /* AMC SECTION */
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
            font-size: 28px;
            font-weight: 700;
            padding-left: 12px;
            margin-bottom: 25px;
            color: var(--text);
            border-left: 5px solid var(--orange-primary);
        }

        .theme-btn {
            position: fixed;
            right: 30px;
            top: 25px;
            left: auto;
            background: var(--orange-primary);
            color: white;
            border: none;
            border-radius: 50px;
            padding: 12px 20px;
            cursor: pointer;
            box-shadow: 0 10px 30px var(--orange-shadow);
            transition: all 0.3s ease;
            font-weight: 600;
        }

        .theme-btn:hover {
            transform: translateY(-3px) scale(1.05);
            background: var(--orange-dark);
            box-shadow: 0 15px 35px var(--orange-shadow);
        }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .action-card {
            position: relative;
            overflow: hidden;
            background: var(--card);
            border: 2px solid var(--card-border);
            border-radius: 24px;
            padding: 30px;
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
            font-size: 20px;
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

        .action-card:nth-child(5)::before {
            background: linear-gradient(90deg, var(--orange-primary), var(--orange-dark));
        }

        .action-card:nth-child(6)::before {
            background: linear-gradient(90deg, var(--orange-light), var(--orange-gradient-start));
        }

        .action-card:nth-child(7)::before {
            background: linear-gradient(90deg, var(--orange-dark), var(--orange-light));
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
                grid-template-columns: 1fr;
                gap: 14px;
            }

            .action-card {
                padding: 20px;
                border-radius: 16px;
            }

            .action-card h3 {
                font-size: 17px;
            }

            .action-card p {
                font-size: 14px;
            }

            .privacy-overlay {
                font-size: 15px;
                padding: 0 20px;
                text-align: center;
            }
        }

        @media (max-width: 420px) {
            .stats {
                grid-template-columns: 1fr 1fr;
            }

            .card h2 {
                font-size: 28px;
            }
        }
    </style>
</head>

<body>
    <div class="privacy-overlay" id="privacyOverlay">Content hidden — window not in focus</div>
    <br>
    <div class="main-content" id="mainContent">

        <div class="welcome-card">
            <h1>Welcome back, <?php echo htmlspecialchars($logged_in_name); ?> 👋</h1>
            <p>Here's an overview of your agencies, users, attendance and inventory data.</p>
        </div>
        <?php if (!empty($expired_amc)) { ?>
            <div class="amc-alert">
                <marquee behavior="scroll" direction="left" scrollamount="6">
                    <?php
                    foreach ($expired_amc as $agency) {
                        echo "⚠️ " . htmlspecialchars($agency) . "'s AMC has expired &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
                    }
                    ?>
                </marquee>
            </div>
        <?php } ?>
        <div class="stats">

            <div class="card purple-card">
                <h2><?php echo $total_agencies; ?></h2>
                <p>Total Agencies</p>
            </div>

            <div class="card cyan-card">
                <h2><?php echo $total_clients; ?></h2>
                <p>Total Clients</p>
            </div>

            <div class="card green-card">
                <h2><?php echo $total_users; ?></h2>
                <p>Users</p>
            </div>

            <div class="card orange-card">
                <h2><?php echo $attendance_percentage; ?>%</h2>
                <p>Attendance</p>
            </div>

        </div>

        <h2 class="section-title">Quick Actions</h2>

        <div class="quick-actions">

            <a href="upload_data.php" class="action-card">
                <h3>📤 Upload Data</h3>
                <p>Add new agency records.</p>
            </a>

            <a href="manage_data.php" class="action-card">
                <h3>🗂️ Manage Data</h3>
                <p>View, edit and delete records.</p>
            </a>

            <a href="add_agency.php" class="action-card">
                <h3>➕ Add Client</h3>
                <p>Create new agency profiles.</p>
            </a>

            <a href="manage_client.php" class="action-card">
                <h3>🏢 Manage Client</h3>
                <p>Update agency information.</p>
            </a>

            <a href="attendance_management.php" class="action-card">
                <h3>📅 Attendance</h3>
                <p>Track employee attendance.</p>
            </a>

            <a href="add_user.php" class="action-card">
                <h3>👤 Add User</h3>
                <p>Create new users.</p>
            </a>

            <a href="user_activity.php" class="action-card">
                <h3>📋 User Activity</h3>
                <p>Monitor user actions.</p>
            </a>

            <a href="leave_management.php" class="action-card">
                <h3>🕒 Leave</h3>
                <p>employee's leaves .</p>
            </a>

            <a href="manage_user.php" class="action-card">
                <h3>🔍 Manage User</h3>
                <p>Manage the users and there activity.</p>
            </a>

            <a href="admin_chat.php" class="action-card">
                <h3>💬 Chats</h3>
                <p>one to one chats.</p>
            </a>

            <a href="meeting_admin.php" class="action-card">
                <h3>👥 Meetings</h3>
                <p>online meetings with employee's.</p>
            </a>

            <a href="email_data.php" class="action-card">
                <h3>📧 Emails</h3>
                <p>Emails.</p>
            </a>

            <a href="generate_invoice.php" class="action-card">
                <h3>🧾 Invoice</h3>
                <p>different agency's invoices.</p>
            </a>
            
            <a href="quotation.php" class="action-card">
                <h3>📜 Quotation</h3>
                <p>different agency's quotations.</p>
            </a>

            <a href="bill.php" class="action-card">
                <h3>📝 Bills</h3>
                <p>bills of different agency's.</p>
            </a>

        </div>

    </div>

</body>
<script>
    const btn = document.getElementById('theme-toggle');

    if (btn) {
        if (localStorage.getItem('theme') === 'dark') {
            document.body.classList.add('dark');
            btn.innerHTML = '☀️ Light Mode';
        }

        btn.addEventListener('click', () => {
            document.body.classList.toggle('dark');
            if (document.body.classList.contains('dark')) {
                localStorage.setItem('theme', 'dark');
                btn.innerHTML = '☀️ Light Mode';
            } else {
                localStorage.setItem('theme', 'light');
                btn.innerHTML = '🌙 Dark Mode';
            }
        });
    }

    // /* ---------- Disable right-click / context menu ---------- */
    // document.addEventListener('contextmenu', function (e) {
    //     e.preventDefault();
    // });

    // /* ---------- Block common "view/copy/inspect" shortcuts ----------
    //    Note: this only stops a casual user from right-clicking or using
    //    these keyboard shortcuts inside the browser. It cannot stop
    //    someone determined to take a screenshot with an OS tool, a phone
    //    camera, or browser dev tools opened another way — no website can
    //    truly block that. */
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

    /* ---------- Hide page content when the tab/window loses focus ----------
       This blurs/hides the dashboard when the user switches tabs, alt-tabs,
       or the window loses focus — a common (but not foolproof) deterrent
       against screen-recording/screenshot tools that run in the background. */
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

</html>