<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$currentPage = basename($_SERVER['PHP_SELF']);

include 'db_connection.php';

// Get employee ID from session
$employee_id = $_SESSION['employee_id'] ?? $_SESSION['user_id'] ?? null;

// Default values
$profile_picture = 'images/default-user.png';
$employee_name = $_SESSION['employee_name'] ?? $_SESSION['name'] ?? 'Employee';
$designation = $_SESSION['designation'] ?? 'Employee';

// If employee ID exists, fetch profile picture from database
if ($employee_id) {
    $query = "SELECT profile_picture, name, designation FROM users WHERE user_id = '$employee_id'";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $user_data = mysqli_fetch_assoc($result);
        
        // Update employee name and designation from database
        if (!empty($user_data['name'])) {
            $employee_name = $user_data['name'];
            $_SESSION['employee_name'] = $employee_name;
        }
        if (!empty($user_data['designation'])) {
            $designation = $user_data['designation'];
            $_SESSION['designation'] = $designation;
        }
        
        // Handle profile picture
        $db_profile_pic = $user_data['profile_picture'] ?? '';
        if (!empty($db_profile_pic)) {
            // Check if the path already contains 'uploads/profiles/'
            if (strpos($db_profile_pic, 'uploads/profiles/') === false && strpos($db_profile_pic, 'uploads/') === false) {
                // If it's just a filename, prepend the path
                $image_path = 'uploads/profiles/' . $db_profile_pic;
            } else {
                // If it already has the full path, use it as is
                $image_path = $db_profile_pic;
            }
            
            // Check if the image file actually exists on the server
            if (file_exists($image_path)) {
                $profile_picture = $image_path;
                $_SESSION['employee_profile_picture'] = $profile_picture;
            } else {
                // Fallback to default if file doesn't exist
                $profile_picture = 'images/default-user.png';
                $_SESSION['employee_profile_picture'] = $profile_picture;
            }
        } else {
            $profile_picture = 'images/default-user.png';
            $_SESSION['employee_profile_picture'] = $profile_picture;
        }
    }
}

// If profile picture is still default, check session for any saved path
if ($profile_picture == 'images/default-user.png' && !empty($_SESSION['employee_profile_picture'])) {
    $session_pic = $_SESSION['employee_profile_picture'];
    if (strpos($session_pic, 'uploads/profiles/') !== false || strpos($session_pic, 'uploads/') !== false) {
        if (file_exists($session_pic)) {
            $profile_picture = $session_pic;
        }
    }
}

// Get employee initial for collapsed view
$employee_initial = strtoupper(substr($employee_name, 0, 1));
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Sidebar</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background: linear-gradient(180deg, #1a0e0a 0%, #0d0805 100%);
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            min-height: 100vh;
        }

        body.dark .sidebar-menu li a.active {
            background: rgba(255, 152, 0, .18);
            color: #ffcc80;
            border-left: 4px solid #ffcc80;
        }

        :root {
            --bg: #0d0805;
            --sidebar-bg: #1a0e0a;
            --card-bg: #1a0e0a;
            --text: #f8fafc;
            --sidebar-text: #ffffff;
            --border: #3a2a1a;
            --hover: #ff9800;
            --orange-primary: #f57c00;
            --orange-light: #ffb74d;
            --orange-dark: #e65100;
            --orange-gradient-start: #ff9800;
            --orange-gradient-end: #f57c00;
            --orange-subtle: rgba(255, 152, 0, 0.08);
            --orange-shadow: rgba(245, 124, 0, 0.15);
        }

        body.dark {
            --bg: #0a0805;
            --text: #f8fafc;
            --sidebar-bg: #1a0e0a;
            --sidebar-text: #ffffff;
            --card-bg: #1a0e0a;
            --border: #3a2a1a;
            --hover: #ffa726;
            --orange-primary: #ffa726;
            --orange-light: #ffcc80;
            --orange-dark: #f57c00;
            --orange-gradient-start: #ff9800;
            --orange-gradient-end: #e65100;
            --orange-subtle: rgba(255, 152, 0, 0.12);
            --orange-shadow: rgba(255, 152, 0, 0.2);
        }

        .chat-wrapper,
        .card,
        .dashboard-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            box-shadow: 0 10px 25px var(--orange-shadow), 0 2px 8px var(--orange-subtle);
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 280px;
            height: 100vh;
            border-right: 1px solid rgba(255, 152, 0, 0.2);
            box-shadow: 0 10px 30px var(--orange-shadow), 0 2px 8px var(--orange-subtle);
            background: var(--sidebar-bg);
            color: var(--sidebar-text);
            border-right: 1px solid var(--border);
            box-shadow: 0 10px 30px var(--orange-shadow);
            transition: .3s;
            overflow-y: auto;
            overflow-x: visible;
            transition: all 0.4s ease;
            z-index: 1000;
        }

        .sidebar.collapsed {
            width: 85px;
            overflow-x: visible !important;
        }

        .sidebar-logo {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            cursor: pointer;
            border-bottom: 1px solid rgba(255, 152, 0, 0.15);
        }

        .sidebar-logo h2 {
            margin-left: 10px;
            white-space: nowrap;
            transition: 0.3s;
        }

        .sidebar.collapsed .sidebar-logo h2 {
            display: none;
        }

        .sidebar-menu li a {
            border: 1px solid transparent;
            display: flex;
            align-items: center;
            gap: 12px;
            position: relative;
        }

        .sidebar-menu li a:hover {
            border-color: var(--orange-dark);
            background: rgba(255, 152, 0, 0.08);
        }

        .sidebar-menu li a .text {
            transition: 0.3s;
            white-space: nowrap;
        }

        .sidebar.collapsed .text {
            display: none;
        }

        .sidebar.collapsed .sidebar-menu li a {
            justify-content: center;
        }

        .sidebar.collapsed .sidebar-footer {
            display: none;
        }

        .sidebar.collapsed .sidebar-menu span {
            font-size: 24px;
        }

        .sidebar-logo img {
            width: 45px;
            height: 45px;
            padding: 4px;
            border-radius: 50%;
            background: var(--orange-subtle);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 15px var(--orange-shadow);
            object-fit: cover;
        }

        .sidebar-logo h2 {
            color: white;
            font-size: 22px;
            font-weight: bold;
        }

        .sidebar-menu {
            list-style: none;
            padding: 20px 10px;
        }

        .sidebar-menu li {
            margin-bottom: 10px;
            position: relative;
        }

        .sidebar-menu li a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 18px;
            text-decoration: none;
            border-radius: 12px;
            color: var(--sidebar-text);
            background: transparent;
            border-radius: 12px;
            transition: .3s;
            transition: all 0.3s ease;
        }

        .sidebar-menu li a:hover {
            transform: translateX(5px);
            background: var(--orange-subtle);
            color: var(--hover);
            transform: translateX(6px);
            box-shadow: 0 12px 30px var(--orange-shadow);
        }

        .sidebar-menu li a.active {
            background: rgba(255, 152, 0, 0.12);
            color: var(--orange-light);
            border-left: 4px solid var(--orange-primary);
            font-weight: 600;
            box-shadow: 0 4px 12px var(--orange-shadow);
        }

        .sidebar-menu li a.active span {
            color: inherit;
        }

        body.dark .sidebar-menu li a.active {
            background: rgba(255, 152, 0, .15);
            border-left: 4px solid var(--orange-primary);
            color: var(--orange-light);
        }

        .sidebar-menu li a.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            width: 4px;
            height: 100%;
            background: var(--orange-primary);
        }

        .sidebar-menu span {
            min-width: 25px;
            text-align: center;
            font-size: 17px;
            flex-shrink: 0;
        }

        .sidebar-footer {
            position: absolute;
            bottom: 20px;
            left: 0;
            width: 100%;
            text-align: center;
            color: rgba(255, 255, 255, 0.6);
            font-size: 13px;
        }

        .employee-profile {
            backdrop-filter: blur(16px);
            padding: 20px 15px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 152, 0, 0.15);
        }

        .employee-profile img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 2px solid rgba(255, 152, 0, 0.3);
            object-fit: cover;
            margin-bottom: 10px;
            transition: all 0.3s ease;
            background: #2d0a0a;
        }

        .employee-profile img:hover {
            border-color: var(--orange-primary);
            box-shadow: 0 0 20px var(--orange-shadow);
        }

        .employee-profile h3 {
            color: var(--sidebar-text);
            font-size: 18px;
            margin-bottom: 5px;
        }

        .employee-profile p {
            font-size: 13px;
            color: #cbd5e1;
        }

        .sidebar.collapsed .employee-profile p {
            display: none;
        }

        .sidebar.collapsed .full-name {
            display: none;
        }

        .sidebar.collapsed .short-name {
            display: block;
            font-size: 18px;
            font-weight: bold;
            color: var(--sidebar-text);
        }

        .short-name {
            display: none;
        }

        .sidebar.collapsed .full-name {
            display: none;
        }

        .sidebar.collapsed .short-name {
            display: block;
            font-size: 18px;
            font-weight: bold;
            color: var(--sidebar-text);
        }

        .sidebar.collapsed .employee-profile img {
            width: 45px;
            height: 45px;
            margin-bottom: 0;
        }

        .theme-toggle {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 15px 0;
            padding: 14px 18px;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            background: var(--orange-subtle);
            color: var(--sidebar-text);
            font-size: 15px;
            transition: all 0.3s ease;
        }

        .theme-toggle:hover {
            transform: translateX(8px);
            background: rgba(255, 152, 0, 0.20);
            box-shadow: 0 10px 25px var(--orange-shadow);
        }

        .sidebar.collapsed .theme-toggle {
            justify-content: center;
        }

        .sidebar.collapsed .theme-toggle .text {
            display: none;
        }

        .logout-btn {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 15px 10px;
            padding: 14px 18px;
            text-decoration: none;
            border-radius: 12px;
            color: var(--orange-primary);
            background: var(--orange-subtle);
            transition: all 0.3s ease;
        }

        .sidebar-footer {
            bottom: 10px;
        }

        .logout-btn:hover {
            transform: translateX(8px);
            color: #ffffff;
            background: rgba(255, 152, 0, 0.25);
            box-shadow: 0 10px 25px var(--orange-shadow);
        }

        .sidebar.collapsed .logout-btn {
            justify-content: center;
        }

        .sidebar.collapsed .logout-btn .text {
            display: none;
        }

        .sidebar.collapsed .logout-btn {
            justify-content: center;
        }

        .sidebar.collapsed .logout-btn .text {
            display: none;
        }

        /* ===== TOOLTIP STYLES - FIXED VERSION ===== */
        /* Only show tooltips when sidebar is collapsed */
        .sidebar.collapsed .sidebar-menu li {
            position: relative;
        }

        .sidebar.collapsed .sidebar-menu li a {
            position: relative;
            justify-content: center;
            padding: 14px !important;
            min-height: 50px;
            overflow: visible !important;
        }

        /* Tooltip using data-tooltip attribute - appears on the right */
        .sidebar.collapsed .sidebar-menu li a {
            cursor: pointer;
        }

        .sidebar.collapsed .sidebar-menu li a[data-tooltip]:hover::after,
        .sidebar.collapsed .sidebar-menu li a[data-tooltip]:focus::after {
            content: attr(data-tooltip);
            position: fixed;
            left: 95px;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(20, 12, 8, 0.97);
            backdrop-filter: blur(16px);
            color: #fff;
            padding: 10px 18px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            white-space: nowrap;
            z-index: 999999;
            border: 1px solid rgba(255, 152, 0, 0.3);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.6), 0 0 20px rgba(255, 152, 0, 0.12);
            letter-spacing: 0.3px;
            pointer-events: none;
            animation: tooltipFade 0.2s ease forwards;
        }

        /* Small arrow/triangle pointer */
        .sidebar.collapsed .sidebar-menu li a[data-tooltip]:hover::before,
        .sidebar.collapsed .sidebar-menu li a[data-tooltip]:focus::before {
            content: '';
            position: fixed;
            left: 87px;
            top: 50%;
            transform: translateY(-50%);
            border: 7px solid transparent;
            border-right-color: rgba(255, 152, 0, 0.3);
            z-index: 999999;
            pointer-events: none;
            animation: tooltipFade 0.2s ease forwards;
        }

        /* Tooltip for Theme Toggle */
        .sidebar.collapsed .theme-toggle {
            cursor: pointer;
        }

        .sidebar.collapsed .theme-toggle[data-tooltip]:hover::after,
        .sidebar.collapsed .theme-toggle[data-tooltip]:focus::after {
            content: attr(data-tooltip);
            position: fixed;
            left: 95px;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(20, 12, 8, 0.97);
            backdrop-filter: blur(16px);
            color: #fff;
            padding: 10px 18px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            white-space: nowrap;
            z-index: 999999;
            border: 1px solid rgba(255, 152, 0, 0.3);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.6), 0 0 20px rgba(255, 152, 0, 0.12);
            pointer-events: none;
            animation: tooltipFade 0.2s ease forwards;
        }

        .sidebar.collapsed .theme-toggle[data-tooltip]:hover::before,
        .sidebar.collapsed .theme-toggle[data-tooltip]:focus::before {
            content: '';
            position: fixed;
            left: 87px;
            top: 50%;
            transform: translateY(-50%);
            border: 7px solid transparent;
            border-right-color: rgba(255, 152, 0, 0.3);
            z-index: 999999;
            pointer-events: none;
            animation: tooltipFade 0.2s ease forwards;
        }

        .sidebar.collapsed .theme-toggle {
            position: relative;
        }

        /* Tooltip for Logout Button */
        .sidebar.collapsed .logout-btn {
            cursor: pointer;
        }

        .sidebar.collapsed .logout-btn[data-tooltip]:hover::after,
        .sidebar.collapsed .logout-btn[data-tooltip]:focus::after {
            content: attr(data-tooltip);
            position: fixed;
            left: 95px;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(20, 12, 8, 0.97);
            backdrop-filter: blur(16px);
            color: #ff6b6b;
            padding: 10px 18px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            white-space: nowrap;
            z-index: 999999;
            border: 1px solid rgba(255, 107, 107, 0.3);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.6), 0 0 20px rgba(255, 107, 107, 0.12);
            pointer-events: none;
            animation: tooltipFade 0.2s ease forwards;
        }

        .sidebar.collapsed .logout-btn[data-tooltip]:hover::before,
        .sidebar.collapsed .logout-btn[data-tooltip]:focus::before {
            content: '';
            position: fixed;
            left: 87px;
            top: 50%;
            transform: translateY(-50%);
            border: 7px solid transparent;
            border-right-color: rgba(255, 107, 107, 0.3);
            z-index: 999999;
            pointer-events: none;
            animation: tooltipFade 0.2s ease forwards;
        }

        .sidebar.collapsed .logout-btn {
            position: relative;
        }

        @keyframes tooltipFade {
            from {
                opacity: 0;
                transform: translateY(-50%) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(-50%) scale(1);
            }
        }

        /* Ensure tooltips show on top of everything */
        .sidebar.collapsed .sidebar-menu li a:hover {
            z-index: 99999;
        }

        /* Custom Scrollbar - Orange Theme */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: rgba(255, 152, 0, 0.05);
            border-radius: 20px;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 152, 0, 0.2);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 0 10px var(--orange-shadow);
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 152, 0, 0.35);
            box-shadow: 0 0 15px var(--orange-shadow), 0 0 20px rgba(255, 152, 0, 0.3);
        }

        /* Firefox */
        .sidebar {
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 152, 0, 0.3) rgba(255, 152, 0, 0.05);
        }

        /* Active link glow effect */
        .sidebar-menu li a.active::after {
            content: '';
            position: absolute;
            right: 0;
            top: 0;
            width: 3px;
            height: 100%;
            background: linear-gradient(180deg, var(--orange-primary), var(--orange-light));
            border-radius: 0 4px 4px 0;
            opacity: 0.6;
        }

        /* Hover animation for icons */
        .sidebar-menu li a:hover span {
            transform: scale(1.1);
            transition: transform 0.3s ease;
        }

        .sidebar-menu li a span {
            transition: transform 0.3s ease;
        }

        /* Collapsed state improvements */
        .sidebar.collapsed .sidebar-menu li a {
            padding: 14px;
            justify-content: center;
        }

        .sidebar.collapsed .sidebar-menu li a span {
            font-size: 22px;
        }

        .sidebar.collapsed .sidebar-menu li {
            margin-bottom: 8px;
        }

        /* Profile section improvements */
        .employee-profile .role-badge {
            display: inline-block;
            padding: 3px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            background: var(--orange-subtle);
            color: var(--orange-light);
            border: 1px solid rgba(255, 152, 0, 0.2);
            margin-top: 5px;
        }

        .sidebar.collapsed .employee-profile .role-badge {
            display: none;
        }

        /* Prevent tooltip from being cut off */
        .sidebar.collapsed .sidebar-menu li a {
            overflow: visible !important;
        }

        /* Fix for sidebar overflow */
        .sidebar {
            overflow-x: visible !important;
        }

        /* =========================================================
           MOBILE MENU TOGGLE (hamburger) + OVERLAY
           Hidden on desktop, shown only on small screens.
        ========================================================= */
        .mobile-menu-toggle {
            display: none;
            position: fixed;
            top: 16px;
            left: 16px;
            z-index: 1200;
            width: 48px;
            height: 48px;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(255, 152, 0, 0.3);
            border-radius: 12px;
            background: rgba(20, 12, 8, 0.92);
            backdrop-filter: blur(12px);
            color: #fff;
            font-size: 20px;
            cursor: pointer;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.35);
            transition: all 0.25s ease;
        }

        .mobile-menu-toggle:hover {
            background: rgba(245, 124, 0, 0.25);
            border-color: var(--orange-primary);
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.55);
            backdrop-filter: blur(2px);
            z-index: 999;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .sidebar-overlay.active {
            display: block;
            opacity: 1;
        }

        /* =========================================================
           RESPONSIVE / MOBILE VIEW
        ========================================================= */
        @media(max-width: 768px) {
            .mobile-menu-toggle {
                display: flex;
            }

            .sidebar {
                width: 260px;
                transform: translateX(-100%);
                box-shadow: none;
            }

            /* On mobile the sidebar always shows full labels (no icon-only
               mode) once opened, so tab names are always readable without
               needing to hover — solves the "tab name not visible" issue
               on touch screens where hover doesn't exist. */
            .sidebar.mobile-open {
                transform: translateX(0);
                box-shadow: 10px 0 40px rgba(0, 0, 0, 0.5);
            }

            .sidebar.collapsed {
                width: 260px;
            }

            .sidebar.collapsed .text,
            .sidebar.collapsed .sidebar-logo h2,
            .sidebar.collapsed .employee-profile p,
            .sidebar.collapsed .full-name,
            .sidebar.collapsed .employee-profile .role-badge {
                display: revert;
            }

            .sidebar.collapsed .short-name {
                display: none;
            }

            .sidebar.collapsed .employee-profile img {
                width: 100px;
                height: 100px;
                margin-bottom: 10px;
            }

            .sidebar.collapsed .sidebar-menu li a,
            .sidebar.collapsed .theme-toggle,
            .sidebar.collapsed .logout-btn {
                justify-content: flex-start;
            }

            .sidebar.collapsed .sidebar-menu span {
                font-size: 17px;
            }

            .sidebar.collapsed .sidebar-menu li a[data-tooltip]:hover::after,
            .sidebar.collapsed .sidebar-menu li a[data-tooltip]:hover::before {
                display: none;
            }

            .sidebar-menu li a,
            .theme-toggle,
            .logout-btn {
                padding: 16px 18px;
            }
        }

        @media(max-width: 768px) {
            .sidebar {
                width: 260px;
            }
            .sidebar.collapsed {
                width: 260px;
            }
        }
    </style>
</head>

<body>

    <button class="mobile-menu-toggle" id="mobileMenuToggle" onclick="toggleMobileSidebar()" aria-label="Open menu">
        ☰
    </button>
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeMobileSidebar()"></div>

    <div class="sidebar" id="sidebar">

        <div class="employee-profile">
            <div onclick="toggleSidebar()" style="cursor: pointer;">
                <img src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Employee" onerror="this.src='images/default-user.png'">
            </div>
            <h3 class="full-name">
                <?= htmlspecialchars($employee_name); ?>
            </h3>
            <div class="short-name">
                <?= htmlspecialchars($employee_initial); ?>
            </div>
            <p><?= htmlspecialchars($designation); ?></p>
            <span class="role-badge">👤 Employee</span>
        </div>

        <ul class="sidebar-menu">
            <li>
                <a href="emp_page.php" class="<?= ($currentPage == 'emp_page.php') ? 'active' : ''; ?>" data-tooltip="Dashboard">
                    <span>📊</span>
                    <span class="text">Dashboard</span>
                </a>
            </li>

            <li>
                <a href="attendance.php" class="<?= ($currentPage == 'attendance.php') ? 'active' : ''; ?>" data-tooltip="Attendance">
                    <span>🕒</span>
                    <span class="text">Attendance</span>
                </a>
            </li>

            <li>
                <a href="leave.php" class="<?= ($currentPage == 'leave.php') ? 'active' : ''; ?>" data-tooltip="Leave">
                    <span>📝</span>
                    <span class="text">Leave</span>
                </a>
            </li>

            <li>
                <a href="client_chat.php" class="<?= ($currentPage == 'client_chat.php') ? 'active' : ''; ?>" data-tooltip="Chat">
                    <span>💬</span>
                    <span class="text">Chat</span>
                </a>
            </li>

            <li>
                <a href="meeting_client.php" class="<?= ($currentPage == 'meeting_client.php') ? 'active' : ''; ?>" data-tooltip="Meetings">
                    <span>👥</span>
                    <span class="text">Meetings</span>
                </a>
            </li>
        </ul>

        <li style="padding: 0 10px; list-style: none;">
            <button class="theme-toggle" onclick="toggleTheme()" data-tooltip="Toggle Theme">
                <span>🌙</span>
                <span class="text">Theme</span>
            </button>
        </li>

        <a href="login.php?logout=1" class="logout-btn" data-tooltip="Logout">
            <span>🚪</span>
            <span class="text">Logout</span>
        </a>
    </div>

</body>

</html>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById("sidebar");
        const content = document.getElementById("mainContent");
        sidebar.classList.toggle("collapsed");
        if (content) {
            content.classList.toggle("expanded");
        }
        localStorage.setItem(
            "sidebarState",
            sidebar.classList.contains("collapsed") ?
                "collapsed" :
                "expanded"
        );
    }

    /* ---------- Mobile slide-in sidebar ---------- */
    function toggleMobileSidebar() {
        const sidebar = document.getElementById("sidebar");
        const overlay = document.getElementById("sidebarOverlay");
        sidebar.classList.toggle("mobile-open");
        overlay.classList.toggle("active");
    }

    function closeMobileSidebar() {
        const sidebar = document.getElementById("sidebar");
        const overlay = document.getElementById("sidebarOverlay");
        sidebar.classList.remove("mobile-open");
        overlay.classList.remove("active");
    }

    document.addEventListener("DOMContentLoaded", function () {
        const sidebar = document.getElementById("sidebar");
        const content = document.getElementById("mainContent");
        if (localStorage.getItem("sidebarState") === "collapsed") {
            sidebar.classList.add("collapsed");
            if (content) {
                content.classList.add("expanded");
            }
        }
        const themeBtn = document.querySelector(".theme-toggle");
        if (localStorage.getItem('theme') === 'dark') {
            document.body.classList.add('dark');
            if (themeBtn) {
                themeBtn.innerHTML =
                    '<span>☀️</span><span class="text">Light Mode</span>';
            }
        }

        window.toggleTheme = function () {
            document.body.classList.toggle('dark');
            const themeBtn = document.querySelector(".theme-toggle");
            if (document.body.classList.contains('dark')) {
                localStorage.setItem('theme', 'dark');
                themeBtn.innerHTML =
                    '<span>☀️</span><span class="text">Light Mode</span>';
            } else {
                localStorage.setItem('theme', 'light');
                themeBtn.innerHTML =
                    '<span>🌙</span><span class="text">Dark Mode</span>';
            }
        };

        // Auto-close the mobile sidebar whenever a nav link is tapped,
        // and auto-close it if the window is resized back to desktop width.
        document.querySelectorAll('.sidebar-menu li a, .logout-btn').forEach(function (link) {
            link.addEventListener('click', function () {
                if (window.innerWidth <= 768) {
                    closeMobileSidebar();
                }
            });
        });

        window.addEventListener('resize', function () {
            if (window.innerWidth > 768) {
                closeMobileSidebar();
            }
        });
    });

    setInterval(function () {
        fetch('check_session_ajax.php')
            .then(response => response.text())
            .then(data => {
                if (data.trim() === 'logout') {
                    alert('Your account was logged in from another account.');
                    window.location.href =
                        'login.php?msg=another_login';
                }
            });
    }, 500);
</script>