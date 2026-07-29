<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$currentPage = basename($_SERVER['PHP_SELF']);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// echo "<pre>";
// print_r($_SESSION);
// echo "</pre>";
// exit();
include 'db_connection.php';

$admin_id = $_SESSION['admin_id'] ?? null;

if (!$admin_id) {
    header("Location: login.php");
    exit();
}

$result = mysqli_query(
    $conn,
    "SELECT profile_picture
     FROM users
     WHERE user_id='$admin_id'"
);

if (!$result) {
    die("Query Error: " . mysqli_error($conn));
}

$user = mysqli_fetch_assoc($result);

// Check if profile picture exists in database
$profile_picture = $user['profile_picture'] ?? '';

// Determine the correct image path
if (!empty($profile_picture)) {
    // Check if the path already contains 'uploads/profiles/' or just the filename
    if (strpos($profile_picture, 'uploads/profiles/') === false && strpos($profile_picture, 'uploads/') === false) {
        // If it's just a filename, prepend the path
        $image_path = 'uploads/profiles/' . $profile_picture;
    } else {
        // If it already has the full path, use it as is
        $image_path = $profile_picture;
    }
    
    // Check if the image file actually exists
    if (!file_exists($image_path)) {
        // Fallback to default if file doesn't exist
        $profile_picture = '';
    }
} else {
    $profile_picture = '';
}

// Get admin name
$admin_name = $_SESSION['admin_name'] ?? 'Admin';
$admin_initial = strtoupper(substr($admin_name, 0, 1));
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMS Sidebar</title>

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

        .admin-profile {
            backdrop-filter: blur(16px);
            padding: 20px 15px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 152, 0, 0.15);
        }

        .admin-profile img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 2px solid rgba(255, 152, 0, 0.3);
            object-fit: cover;
            margin-bottom: 10px;
            transition: all 0.3s ease;
        }

        .admin-profile img:hover {
            border-color: var(--orange-primary);
            box-shadow: 0 0 20px var(--orange-shadow);
        }

        .admin-profile h3 {
            color: var(--sidebar-text);
            font-size: 18px;
            margin-bottom: 5px;
        }

        .admin-profile p {
            font-size: 13px;
            color: #cbd5e1;
        }

        .sidebar.collapsed .admin-profile p {
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

        .sidebar.collapsed .admin-profile img {
            width: 45px;
            height: 45px;
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

        /* Tooltip using data-tooltip attribute - appears on the right.
           cursor:pointer added so it's clear the icon is hoverable/clickable,
           and tooltip now also triggers on :focus (keyboard / tap-and-hold on
           some mobile browsers) not just :hover. */
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
        .admin-profile .role-badge {
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

        .sidebar.collapsed .admin-profile .role-badge {
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
            .sidebar.collapsed .admin-profile p,
            .sidebar.collapsed .full-name,
            .sidebar.collapsed .admin-profile .role-badge {
                display: revert;
            }

            .sidebar.collapsed .short-name {
                display: none;
            }

            .sidebar.collapsed .admin-profile img {
                width: 100px;
                height: 100px;
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

        <div class="admin-profile">
            <div class="sidebar-logo" onclick="toggleSidebar()">
                <?php 
                // Check if profile picture exists and is not empty
                if (!empty($profile_picture)) {
                    // Construct the image path
                    $image_path = 'uploads/profiles/' . $profile_picture;
                    
                    // Check if the image file actually exists
                    if (file_exists($image_path)) {
                        echo '<img src="' . htmlspecialchars($image_path) . '" alt="Profile pic">';
                    } else {
                        // Fallback to default if file doesn't exist
                        echo '<img src="images/default-user.png" alt="Profile pic">';
                    }
                } else {
                    echo '<img src="images/default-user.png" alt="Profile pic">';
                }
                ?>
            </div>
            <h3 class="full-name">
                <?= htmlspecialchars($admin_name); ?>
            </h3>
            <div class="short-name">
                <?= htmlspecialchars($admin_initial); ?>
            </div>
            <p>Administrator</p>
            <span class="role-badge">🔑 Admin</span>
        </div>

        <ul class="sidebar-menu">
            <li>
                <a href="admin_page.php" class="<?= ($currentPage == 'admin_page.php') ? 'active' : ''; ?>" data-tooltip="Dashboard">
                    <span>📊</span>
                    <span class="text">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="upload_data.php" class="<?= ($currentPage == 'upload_data.php') ? 'active' : ''; ?>" data-tooltip="Upload Data">
                    <span>📤</span>
                    <span class="text">Upload Data</span>
                </a>
            </li>
            <li>
                <a href="manage_data.php" class="<?= ($currentPage == 'manage_data.php') ? 'active' : ''; ?>" data-tooltip="Manage Data">
                    <span>🗂️</span>
                    <span class="text">Manage Data</span>
                </a>
            </li>
            <li>
                <a href="add_client.php" class="<?= ($currentPage == 'add_client.php') ? 'active' : ''; ?>" data-tooltip="Add Client">
                    <span>➕</span>
                    <span class="text">Add Client</span>
                </a>
            </li>
            <li>
                <a href="manage_client.php" class="<?= ($currentPage == 'manage_client.php') ? 'active' : ''; ?>" data-tooltip="Manage Client">
                    <span>🏢</span>
                    <span class="text">Manage Client</span>
                </a>
            </li>
            <li>
                <a href="attendance_management.php"
                    class="<?= ($currentPage == 'attendance_management.php') ? 'active' : ''; ?>" data-tooltip="Attendance Management">
                    <span>📅</span>
                    <span class="text">Attendance Management</span>
                </a>
            </li>
            <li>
                <a href="leave_management.php" class="<?= ($currentPage == 'leave_management.php') ? 'active' : ''; ?>" data-tooltip="Leave Management">
                    <span>🕒</span>
                    <span class="text">Leave Management</span>
                </a>
            </li>

            <li>
                <a href="add_user.php" class="<?= ($currentPage == 'add_user.php') ? 'active' : ''; ?>" data-tooltip="Add User">
                    <span>👤</span>
                    <span class="text">Add User</span>
                </a>
            </li>
            <li>
                <a href="manage_user.php" class="<?= ($currentPage == 'manage_user.php') ? 'active' : ''; ?>" data-tooltip="Manage Users">
                    <span>🔍</span>
                    <span class="text">Manage Users</span>
                </a>
            </li>

            <li>
                <a href="user_activity.php" class="<?= ($currentPage == 'user_activity.php') ? 'active' : ''; ?>" data-tooltip="User Activity">
                    <span>📋</span>
                    <span class="text">User Activity</span>
                </a>
            </li>
            <li>
                <a href="admin_chat.php" class="<?= ($currentPage == 'admin_chat.php') ? 'active' : ''; ?>" data-tooltip="Chat">
                    <span>💬</span>
                    <span class="text">Chat</span>
                </a>
            </li>
            <li>
                <a href="meeting_admin.php" class="<?= ($currentPage == 'meeting_admin.php') ? 'active' : ''; ?>" data-tooltip="Meeting">
                    <span>👥</span>
                    <span class="text">Meeting</span>
                </a>
            </li>
            <li>
                <a href="email_data.php" class="<?= ($currentPage == 'email_data.php') ? 'active' : ''; ?>" data-tooltip="Email Data">
                    <span>📧</span>
                    <span class="text">Email Data</span>
                </a>
            </li>
            <li>
                <a href="generate_invoice.php" class="<?= ($currentPage == 'generate_invoice.php') ? 'active' : ''; ?>" data-tooltip="Generate Invoice">
                    <span>🧾</span>
                    <span class="text">Generate Invoice</span>
                </a>
            </li>
            <li>
                <a href="quotation.php" class="<?= ($currentPage == 'quotation.php') ? 'active' : ''; ?>" data-tooltip="Generate Quotation">
                    <span>📜</span>
                    <span class="text">Generate Quotation</span>
                </a>
            </li>
            <li>
                <a href="bill.php" class="<?= ($currentPage == 'bill.php') ? 'active' : ''; ?>" data-tooltip="Bills">
                    <span>📝</span>
                    <span class="text">Bills</span>
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