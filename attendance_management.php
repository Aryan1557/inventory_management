<?php
session_start();
include 'db_connection.php';
include 'session_check.php';
include 'sidebar.php';

// Fetch statistics
// 1. Total employees (excluding admins)
$total_employees_query = mysqli_query($conn, "
    SELECT COUNT(*) as total 
    FROM users 
    WHERE role != 'admin'
");
$total_employees = mysqli_fetch_assoc($total_employees_query)['total'];

// 2. Present today (excluding admins)
$today = date('Y-m-d');
$present_today_query = mysqli_query($conn, "
    SELECT COUNT(DISTINCT a.id) as present 
    FROM attendance a
    INNER JOIN users u ON a.employee_id = u.user_id
    WHERE a.attendance_date = '$today' 
    AND a.status = 'Present'
    AND u.role != 'admin'
");
$present_today = mysqli_fetch_assoc($present_today_query)['present'];

// 3. Active right now (checked in but not checked out today, excluding admins)
$active_now_query = mysqli_query($conn, "
    SELECT COUNT(DISTINCT a.id) as active 
    FROM attendance a
    INNER JOIN users u ON a.employee_id = u.user_id
    WHERE a.attendance_date = '$today' 
    AND a.check_in IS NOT NULL 
    AND a.check_out IS NULL
    AND u.role != 'admin'
");
$active_now = mysqli_fetch_assoc($active_now_query)['active'];

// Get filter values
$filter_employee = isset($_GET['employee']) ? $_GET['employee'] : '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$filter_date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$filter_date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Build WHERE clause for filters
$where_conditions = [];
$where_conditions[] = "u.role != 'admin'";

if (!empty($filter_employee)) {
    $where_conditions[] = "(a.name LIKE '%$filter_employee%' OR u.employee_id LIKE '%$filter_employee%')";
}

if (!empty($filter_status)) {
    $where_conditions[] = "a.status = '$filter_status'";
}

if (!empty($filter_date_from)) {
    $where_conditions[] = "a.attendance_date >= '$filter_date_from'";
}

if (!empty($filter_date_to)) {
    $where_conditions[] = "a.attendance_date <= '$filter_date_to'";
}

$where_clause = "WHERE " . implode(" AND ", $where_conditions);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Employee Attendance</title>
    <!-- Added viewport meta tag for mobile responsiveness -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes, viewport-fit=cover">
    
    <style>
        /* ============================================ */
        /* ========== ORIGINAL CSS (KEPT INTACT) ====== */
        /* ============================================ */
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
            overflow-x: hidden;
            width: 100%;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 35px;
            transition: all .4s ease;
            min-height: 100vh;
            width: auto;
            max-width: 100%;
            overflow-x: hidden;
        }

        .main-content.expanded {
            margin-left: var(--sidebar-collapsed);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            gap: 20px;
            flex-wrap: wrap;
        }

        .page-title {
            color: var(--orange-primary);
            font-size: 28px;
            font-weight: 700;
            position: relative;
        }

        .page-title::after {
            content: '';
            display: block;
            width: 40px;
            height: 4px;
            background: linear-gradient(90deg, var(--orange-gradient-start), var(--orange-primary));
            margin-top: 5px;
            border-radius: 10px;
        }

        /* Stats Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--card);
            padding: 25px;
            border-radius: 20px;
            box-shadow: 0 10px 25px var(--orange-shadow);
            text-align: center;
            transition: all .35s ease;
            border: 2px solid var(--card-border);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--orange-gradient-start), var(--orange-primary));
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .stat-card:hover::before {
            opacity: 1;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px var(--orange-shadow);
        }

        .stat-icon {
            font-size: 32px;
            margin-bottom: 10px;
            display: block;
        }

        .stat-number {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 5px;
            background: linear-gradient(135deg, var(--orange-gradient-start), var(--orange-primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-label {
            color: var(--secondary);
            font-size: 14px;
            font-weight: 500;
        }

        .stat-card.total .stat-number {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-card.present .stat-number {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-card.active .stat-number {
            background: linear-gradient(135deg, #06b6d4, #0891b2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Filter Section */
        .filter-section {
            background: var(--orange-subtle);
            border-radius: 20px;
            padding: 20px 25px;
            margin-bottom: 25px;
            border: 2px solid var(--card-border);
            transition: all .35s ease;
        }

        .filter-section:hover {
            border-color: var(--orange-light);
        }

        .filter-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            font-size: 16px;
            color: var(--text);
            margin-bottom: 15px;
        }

        .filter-title span {
            color: var(--orange-primary);
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            align-items: end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .filter-group label {
            font-size: 13px;
            font-weight: 600;
            color: var(--secondary);
            letter-spacing: 0.3px;
        }

        .filter-group select,
        .filter-group input {
            padding: 10px 14px;
            border: 2px solid var(--input-border);
            border-radius: 12px;
            background: var(--input-bg);
            color: var(--text);
            transition: all .35s ease;
            font-size: 14px;
            font-family: 'Segoe UI', sans-serif;
            width: 100%;
        }

        .filter-group select:focus,
        .filter-group input:focus {
            outline: none;
            border-color: var(--orange-primary);
            box-shadow: 0 0 0 4px var(--orange-subtle);
            background: var(--card);
        }

        .filter-group select option {
            background: var(--card);
            color: var(--text);
        }

        .filter-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .btn-filter {
            padding: 10px 24px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--orange-gradient-start), var(--orange-primary));
            color: white;
            font-weight: 700;
            cursor: pointer;
            transition: all .35s ease;
            font-size: 14px;
            font-family: 'Segoe UI', sans-serif;
            white-space: nowrap;
        }

        .btn-filter:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px var(--orange-shadow);
            background: linear-gradient(135deg, var(--orange-primary), var(--orange-dark));
        }

        .btn-reset {
            padding: 10px 20px;
            border: 2px solid var(--input-border);
            border-radius: 12px;
            background: transparent;
            color: var(--text);
            font-weight: 600;
            cursor: pointer;
            transition: all .35s ease;
            font-size: 14px;
            font-family: 'Segoe UI', sans-serif;
            white-space: nowrap;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .btn-reset:hover {
            border-color: var(--orange-primary);
            background: var(--orange-subtle);
            transform: translateY(-2px);
        }

        .controls {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .search-box {
            flex: 1;
            min-width: 200px;
            max-width: 350px;
        }

        .search-box input {
            width: 100%;
            padding: 12px 18px;
            border: 2px solid var(--input-border);
            border-radius: 14px;
            background: var(--input-bg);
            color: var(--text);
            outline: none;
            font-size: 15px;
            transition: all .35s ease;
        }

        .search-box input:focus {
            border-color: var(--orange-primary);
            box-shadow: 0 0 0 4px var(--orange-subtle);
            background: var(--card);
        }

        .search-box input::placeholder {
            color: var(--secondary);
            opacity: 0.6;
        }

        .theme-btn {
            padding: 12px 24px;
            border: none;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--orange-gradient-start), var(--orange-primary));
            color: white;
            cursor: pointer;
            font-weight: 600;
            box-shadow: 0 8px 20px var(--orange-shadow);
            transition: all .35s ease;
            font-size: 15px;
            white-space: nowrap;
        }

        .theme-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px var(--orange-shadow);
            background: linear-gradient(135deg, var(--orange-primary), var(--orange-dark));
        }

        .theme-btn:active {
            transform: translateY(0px);
        }

        .card {
            background: var(--card);
            border-radius: 24px;
            padding: 30px;
            border: 2px solid var(--card-border);
            box-shadow: 0 10px 25px var(--orange-shadow), 0 20px 50px rgba(255, 152, 0, 0.06);
            overflow-x: auto;
            transition: all .35s ease;
        }

        .card:hover {
            box-shadow: 0 15px 35px var(--orange-shadow), 0 25px 60px rgba(255, 152, 0, 0.08);
        }

        .table-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }

        thead {
            background: linear-gradient(135deg, var(--orange-gradient-start), var(--orange-primary));
            color: white;
        }

        th {
            color: white;
            padding: 16px 15px;
            text-align: center;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }

        tbody tr:nth-child(even) {
            background: var(--table-stripe);
        }

        tbody tr:hover {
            background: var(--table-hover);
            transition: background 0.2s ease;
        }

        td {
            padding: 14px 15px;
            text-align: center;
            border-bottom: 1px solid var(--card-border);
            color: var(--text);
            font-size: 14px;
        }

        tr:last-child td {
            border-bottom: none;
        }

        .status-badge {
            padding: 6px 14px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 13px;
            display: inline-block;
            letter-spacing: 0.3px;
        }

        .status-badge.Present {
            background: rgba(34, 197, 94, 0.12);
            color: #22c55e;
            border: 1px solid rgba(34, 197, 94, 0.2);
        }

        .status-badge.Absent {
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .status-badge.Leave {
            background: rgba(6, 182, 212, 0.12);
            color: #06b6d4;
            border: 1px solid rgba(6, 182, 212, 0.2);
        }

        .no-data {
            text-align: center;
            padding: 60px 20px;
            color: var(--secondary);
            font-size: 16px;
        }

        .no-data .empty-icon {
            font-size: 60px;
            margin-bottom: 15px;
            display: block;
        }

        .no-data h3 {
            color: var(--text);
            margin-bottom: 10px;
            font-size: 20px;
        }

        .id-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            background: var(--orange-subtle);
            color: var(--orange-primary);
            font-weight: 700;
            font-size: 13px;
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

        /* ============================================ */
        /* ========== MOBILE RESPONSIVE CSS =========== */
        /* ============================================ */

        /* Tablets and small laptops */
        @media (max-width: 1024px) {
            .main-content {
                padding: 25px 20px;
            }
            
            .page-title {
                font-size: 24px;
            }
            
            .stats-container {
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
                gap: 15px;
            }
            
            .filter-grid {
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            }
        }

        /* iPads and tablets */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 85px;
                padding: 20px 15px;
            }

            .main-content.expanded {
                margin-left: 85px;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
                margin-bottom: 20px;
            }

            .page-title {
                font-size: 22px;
            }

            .page-title::after {
                width: 30px;
                height: 3px;
            }

            .controls {
                width: 100%;
                flex-direction: column;
                align-items: stretch;
                gap: 10px;
            }

            .search-box {
                max-width: 100%;
                min-width: auto;
            }

            .search-box input {
                padding: 10px 14px;
                font-size: 16px;
            }

            .theme-btn {
                width: 100%;
                justify-content: center;
                padding: 10px 20px;
                font-size: 14px;
            }

            .card {
                padding: 20px 15px;
                border-radius: 16px;
            }

            .card:hover {
                transform: none;
            }

            .stats-container {
                grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
                gap: 12px;
                margin-bottom: 20px;
            }

            .stat-card {
                padding: 18px 15px;
                border-radius: 16px;
            }

            .stat-card:hover {
                transform: none;
            }

            .stat-number {
                font-size: 28px;
            }

            .stat-icon {
                font-size: 26px;
                margin-bottom: 6px;
            }

            .stat-label {
                font-size: 12px;
            }

            .filter-section {
                padding: 15px 18px;
                border-radius: 16px;
                margin-bottom: 20px;
            }

            .filter-title {
                font-size: 14px;
                margin-bottom: 12px;
            }

            .filter-grid {
                grid-template-columns: 1fr 1fr;
                gap: 12px;
            }

            .filter-group label {
                font-size: 12px;
            }

            .filter-group select,
            .filter-group input {
                padding: 8px 12px;
                font-size: 16px;
                border-radius: 10px;
                min-height: 40px;
            }

            .filter-actions {
                grid-column: 1 / -1;
                justify-content: stretch;
                flex-direction: row;
            }

            .filter-actions .btn-filter,
            .filter-actions .btn-reset {
                flex: 1;
                text-align: center;
                justify-content: center;
                padding: 10px 16px;
                font-size: 13px;
                border-radius: 10px;
            }

            .btn-filter:hover,
            .btn-reset:hover,
            .theme-btn:hover {
                transform: none;
            }

            .table-wrapper {
                margin: 0 -5px;
            }

            table {
                font-size: 13px;
                min-width: 500px;
            }

            th {
                padding: 12px 10px;
                font-size: 12px;
            }

            td {
                padding: 10px 8px;
                font-size: 12px;
            }

            .status-badge {
                padding: 4px 12px;
                font-size: 11px;
            }

            .id-badge {
                font-size: 11px;
                padding: 3px 10px;
            }
        }

        /* Mobile phones */
        @media (max-width: 480px) {
            .main-content {
                margin-left: 0;
                padding: 12px 10px;
            }

            .main-content.expanded {
                margin-left: 0;
            }

            .page-title {
                font-size: 20px;
            }

            .page-title::after {
                width: 25px;
                height: 3px;
            }

            .card {
                padding: 15px 10px;
                border-radius: 14px;
                border-width: 1.5px;
            }

            .stats-container {
                grid-template-columns: 1fr 1fr;
                gap: 10px;
                margin-bottom: 15px;
            }

            .stat-card {
                padding: 14px 10px;
                border-radius: 14px;
                border-width: 1.5px;
            }

            .stat-number {
                font-size: 22px;
            }

            .stat-icon {
                font-size: 22px;
                margin-bottom: 4px;
            }

            .stat-label {
                font-size: 11px;
            }

            .filter-section {
                padding: 12px 14px;
                border-radius: 14px;
                margin-bottom: 15px;
                border-width: 1.5px;
            }

            .filter-title {
                font-size: 13px;
                margin-bottom: 10px;
            }

            .filter-grid {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            .filter-group select,
            .filter-group input {
                padding: 8px 12px;
                font-size: 16px;
                border-radius: 8px;
                min-height: 38px;
            }

            .filter-actions {
                flex-direction: column;
                gap: 8px;
            }

            .filter-actions .btn-filter,
            .filter-actions .btn-reset {
                width: 100%;
                padding: 10px 14px;
                font-size: 13px;
            }

            .controls {
                gap: 8px;
            }

            .search-box input {
                padding: 8px 12px;
                font-size: 16px;
                border-radius: 10px;
            }

            .theme-btn {
                padding: 10px 16px;
                font-size: 13px;
                border-radius: 10px;
            }

            table {
                font-size: 12px;
                min-width: 400px;
            }

            th {
                padding: 10px 6px;
                font-size: 11px;
                letter-spacing: 0.3px;
            }

            td {
                padding: 8px 6px;
                font-size: 11px;
            }

            .status-badge {
                padding: 3px 10px;
                font-size: 10px;
                border-radius: 14px;
            }

            .id-badge {
                font-size: 10px;
                padding: 2px 8px;
                border-radius: 8px;
            }

            .no-data {
                padding: 40px 15px;
            }

            .no-data .empty-icon {
                font-size: 40px;
                margin-bottom: 10px;
            }

            .no-data h3 {
                font-size: 16px;
            }

            .no-data p {
                font-size: 13px;
            }

            /* Disable hover effects on mobile for performance */
            .stat-card:hover,
            .card:hover,
            .filter-section:hover,
            .btn-filter:hover,
            .btn-reset:hover,
            .theme-btn:hover {
                transform: none;
            }

            .stat-card::before {
                opacity: 0;
            }

            .stat-card:active {
                transform: scale(0.97);
            }

            .btn-filter:active,
            .theme-btn:active {
                transform: scale(0.97);
            }
        }

        /* Very small phones */
        @media (max-width: 380px) {
            .main-content {
                padding: 8px 6px;
            }

            .page-title {
                font-size: 17px;
            }

            .card {
                padding: 12px 8px;
                border-radius: 12px;
            }

            .stats-container {
                gap: 6px;
            }

            .stat-card {
                padding: 10px 8px;
                border-radius: 10px;
            }

            .stat-number {
                font-size: 18px;
            }

            .stat-icon {
                font-size: 18px;
            }

            .stat-label {
                font-size: 10px;
            }

            .filter-section {
                padding: 10px 10px;
                border-radius: 12px;
            }

            .filter-group select,
            .filter-group input {
                font-size: 16px;
                padding: 6px 10px;
                min-height: 34px;
            }

            .filter-actions .btn-filter,
            .filter-actions .btn-reset {
                font-size: 12px;
                padding: 8px 12px;
            }

            table {
                min-width: 350px;
                font-size: 11px;
            }

            th {
                padding: 8px 4px;
                font-size: 10px;
            }

            td {
                padding: 6px 4px;
                font-size: 10px;
            }

            .status-badge {
                padding: 2px 8px;
                font-size: 9px;
            }

            .id-badge {
                font-size: 9px;
                padding: 2px 6px;
            }

            .search-box input {
                font-size: 16px;
                padding: 6px 10px;
            }

            .theme-btn {
                font-size: 12px;
                padding: 8px 12px;
            }
        }

        /* Landscape mode on phones */
        @media (max-height: 500px) and (orientation: landscape) {
            .main-content {
                padding: 10px 15px;
            }

            .page-header {
                margin-bottom: 12px;
                gap: 10px;
            }

            .page-title {
                font-size: 18px;
            }

            .stats-container {
                grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
                gap: 10px;
                margin-bottom: 15px;
            }

            .stat-card {
                padding: 12px 10px;
            }

            .stat-number {
                font-size: 20px;
            }

            .stat-icon {
                font-size: 20px;
                margin-bottom: 4px;
            }

            .stat-label {
                font-size: 11px;
            }

            .filter-section {
                padding: 10px 15px;
                margin-bottom: 15px;
            }

            .filter-grid {
                grid-template-columns: 1fr 1fr 1fr;
                gap: 10px;
            }

            .filter-group select,
            .filter-group input {
                padding: 6px 10px;
                font-size: 13px;
                min-height: 32px;
            }

            .filter-actions {
                flex-direction: row;
            }

            .card {
                padding: 15px;
            }

            table {
                min-width: 500px;
                font-size: 12px;
            }

            th {
                padding: 8px 8px;
                font-size: 11px;
            }

            td {
                padding: 6px 8px;
                font-size: 11px;
            }

            .controls {
                flex-direction: row;
            }

            .search-box {
                max-width: 200px;
            }

            .theme-btn {
                padding: 8px 16px;
                font-size: 13px;
            }
        }

        /* Touch-friendly improvements for mobile */
        @media (pointer: coarse) {
            input,
            select,
            .btn-filter,
            .btn-reset,
            .theme-btn,
            .stat-card {
                touch-action: manipulation;
                -webkit-tap-highlight-color: transparent;
            }

            input,
            select {
                font-size: 16px !important;
            }

            .btn-filter,
            .btn-reset,
            .theme-btn {
                min-height: 44px;
            }

            .stat-card {
                min-height: 80px;
            }
        }

        /* Prevent horizontal scroll */
        @media (max-width: 768px) {
            body {
                overflow-x: hidden;
            }
            
            .main-content {
                overflow-x: hidden;
            }
            
            .card {
                overflow: hidden;
            }
            
            .table-wrapper {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
        }
    </style>
</head>

<body>
    <div class="main-content" id="mainContent">
        <!-- Stats Cards -->
        <div class="stats-container">
            <div class="stat-card total">
                <span class="stat-icon">👥</span>
                <div class="stat-number"><?= $total_employees ?></div>
                <div class="stat-label">Total Employees</div>
            </div>

            <div class="stat-card present">
                <span class="stat-icon">✅</span>
                <div class="stat-number"><?= $present_today ?></div>
                <div class="stat-label">Present Today</div>
            </div>

            <div class="stat-card active">
                <span class="stat-icon">🟢</span>
                <div class="stat-number"><?= $active_now ?></div>
                <div class="stat-label">Active Right Now</div>
            </div>
        </div>

        <div class="card">
            <div class="page-header">
                <h1 class="page-title">📋 Employee Attendance Records</h1>

                <div class="controls">
                    <div class="search-box">
                        <input
                            type="text"
                            id="searchInput"
                            placeholder="🔍 Search employee, date, status..."
                            onkeyup="searchAttendance()">
                    </div>
                    <button id="theme-toggle" class="theme-btn">🌙 Dark Mode</button>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="filter-section">
                <div class="filter-title">
                    <span>🔍</span> Filter Attendance Records
                </div>
                
                <form method="GET" action="">
                    <div class="filter-grid">
                        <div class="filter-group">
                            <label for="filter_employee">Employee</label>
                            <input type="text" id="filter_employee" name="employee" 
                                   placeholder="Search by name or ID..."
                                   value="<?php echo htmlspecialchars($filter_employee); ?>">
                        </div>

                        <div class="filter-group">
                            <label for="filter_status">Status</label>
                            <select id="filter_status" name="status">
                                <option value="">All Status</option>
                                <option value="Present" <?php echo ($filter_status == 'Present') ? 'selected' : ''; ?>>Present</option>
                                <option value="Absent" <?php echo ($filter_status == 'Absent') ? 'selected' : ''; ?>>Absent</option>
                                <option value="Leave" <?php echo ($filter_status == 'Leave') ? 'selected' : ''; ?>>Leave</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label for="filter_date_from">From Date</label>
                            <input type="date" id="filter_date_from" name="date_from" 
                                   value="<?php echo htmlspecialchars($filter_date_from); ?>">
                        </div>

                        <div class="filter-group">
                            <label for="filter_date_to">To Date</label>
                            <input type="date" id="filter_date_to" name="date_to" 
                                   value="<?php echo htmlspecialchars($filter_date_to); ?>">
                        </div>

                        <div class="filter-actions">
                            <button type="submit" class="btn-filter">Apply Filters</button>
                            <a href="attendance_management.php" class="btn-reset">Reset</a>
                        </div>
                    </div>
                </form>
            </div>

            <div class="table-wrapper">
                <table id="attendanceTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Employee Name</th>
                            <th>Employee ID</th>
                            <th>Date</th>
                            <th>Check In</th>
                            <th>Check Out</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        error_reporting(E_ALL);
                        ini_set('display_errors', 1);

                        if (!isset($conn)) {
                            echo "<tr><td colspan='7' class='no-data' style='color: var(--orange-primary);'>
                                    <span class='empty-icon'>❌</span>
                                    <h3>Database Connection Failed</h3>
                                    <p>Unable to connect to the database. Please check your connection settings.</p>
                                  </td></tr>";
                        } else {
                            // Modified query to join on employee_id correctly
                            $query = mysqli_query(
                                $conn,
                                "SELECT 
                                    a.*, 
                                    u.name as user_name,
                                    u.employee_id as emp_id
                                 FROM attendance a
                                 INNER JOIN users u ON a.employee_id = u.user_id
                                 $where_clause
                                 ORDER BY a.attendance_date DESC, a.check_in DESC"
                            );

                            if (!$query) {
                                echo "<tr><td colspan='7' class='no-data' style='color: var(--orange-primary);'>
                                        <span class='empty-icon'>❌</span>
                                        <h3>Query Error</h3>
                                        <p>" . mysqli_error($conn) . "</p>
                                      </td></tr>";
                            } elseif (mysqli_num_rows($query) == 0) {
                                echo "<tr><td colspan='7' class='no-data'>
                                        <span class='empty-icon'>📭</span>
                                        <h3>No Attendance Records Found</h3>
                                        <p>Attendance records will appear here once employees start checking in.</p>
                                      </td></tr>";
                            } else {
                                while ($row = mysqli_fetch_assoc($query)) {
                                    $statusClass = $row['status'];
                                    $formattedDate = date('d M Y', strtotime($row['attendance_date']));
                                    $checkIn = $row['check_in'] ? date('h:i A', strtotime($row['check_in'])) : 'N/A';
                                    $checkOut = $row['check_out'] ? date('h:i A', strtotime($row['check_out'])) : 'N/A';
                                    $employeeName = !empty($row['user_name']) ? $row['user_name'] : $row['name'];

                                    echo "<tr>
                                        <td><span class='id-badge'>#" . $row['id'] . "</span></td>
                                        <td><strong>" . htmlspecialchars($employeeName) . "</strong></td>
                                        <td>" . htmlspecialchars($row['emp_id'] ?? 'N/A') . "</td>
                                        <td>" . $formattedDate . "</td>
                                        <td>" . $checkIn . "</td>
                                        <td>" . $checkOut . "</td>
                                        <td><span class='status-badge " . $statusClass . "'>" . $row['status'] . "</span></td>
                                    </tr>";
                                }
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Wait for DOM to be ready
        document.addEventListener("DOMContentLoaded", function() {
            const mainContent = document.getElementById("mainContent");
            const themeBtn = document.getElementById('theme-toggle');

            // Apply sidebar state from localStorage
            if (localStorage.getItem("sidebarState") === "collapsed") {
                if (mainContent) {
                    mainContent.classList.add("expanded");
                }
            }

            // Apply theme from localStorage
            if (localStorage.getItem('theme') === 'dark') {
                document.body.classList.add('dark');
                if (themeBtn) {
                    themeBtn.innerHTML = '☀️ Light Mode';
                }
            }

            // Theme toggle button click handler
            if (themeBtn) {
                themeBtn.addEventListener('click', function() {
                    document.body.classList.toggle('dark');

                    if (document.body.classList.contains('dark')) {
                        localStorage.setItem('theme', 'dark');
                        themeBtn.innerHTML = '☀️ Light Mode';
                    } else {
                        localStorage.setItem('theme', 'light');
                        themeBtn.innerHTML = '🌙 Dark Mode';
                    }
                });
            }

            // Listen for sidebar toggle events
            const sidebarLogo = document.querySelector(".sidebar-logo");
            if (sidebarLogo) {
                sidebarLogo.addEventListener("click", function() {
                    setTimeout(() => {
                        if (mainContent) {
                            if (document.getElementById("sidebar").classList.contains("collapsed")) {
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
                        if (themeBtn) themeBtn.innerHTML = '☀️ Light Mode';
                    } else {
                        document.body.classList.remove('dark');
                        if (themeBtn) themeBtn.innerHTML = '🌙 Dark Mode';
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

        function searchAttendance() {
            let input = document.getElementById("searchInput");
            let filter = input.value.toLowerCase();

            let table = document.getElementById("attendanceTable");
            let rows = table.getElementsByTagName("tr");

            for (let i = 1; i < rows.length; i++) {
                let cells = rows[i].getElementsByTagName("td");
                let found = false;

                for (let j = 0; j < cells.length; j++) {
                    if (
                        cells[j] &&
                        cells[j].innerText.toLowerCase().includes(filter)
                    ) {
                        found = true;
                        break;
                    }
                }

                rows[i].style.display = found ? "" : "none";
            }
        }
    </script>
</body>

</html>