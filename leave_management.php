<?php
include 'db_connection.php';
include 'session_check.php';

// Approve Leave
if (isset($_GET['approve'])) {
    $leave_id = mysqli_real_escape_string($conn, $_GET['approve']);

    mysqli_query($conn, "UPDATE leave_management SET status='Approved' WHERE leave_id='$leave_id'");

    header("Location: leave_management.php");
    exit();
}

// Reject Leave
if (isset($_POST['reject_leave'])) {

    $leave_id = mysqli_real_escape_string($conn, $_POST['leave_id']);
    $reject_reason = mysqli_real_escape_string(
        $conn,
        $_POST['reject_reason']
    );

    mysqli_query($conn, "
        UPDATE leave_management
        SET
            status='Rejected',
            rejection_reason='$reject_reason'
        WHERE leave_id='$leave_id'
    ");

    header("Location: leave_management.php");
    exit();
}

include 'sidebar.php';
$pending_count = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) as total
         FROM leave_management
         WHERE status='Pending'"
    )
)['total'];

$approved_count = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) as total
         FROM leave_management
         WHERE status='Approved'"
    )
)['total'];

$rejected_count = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) as total
         FROM leave_management
         WHERE status='Rejected'"
    )
)['total'];

$user_count = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(DISTINCT user_id) as total
         FROM leave_management"
    )
)['total'];
// Simplified query - use employee_name directly from leave_management table
$result = mysqli_query($conn, "SELECT * FROM leave_management ORDER BY leave_id DESC");

if (!$result) {
    die("Query Failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Leave Management</title>
    <!-- Added viewport meta tag for mobile responsiveness -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes, viewport-fit=cover">
    
    <style>
        /* ============================================ */
        /* ========== ORIGINAL CSS (KEPT INTACT) ====== */
        /* ============================================ */
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
            font-family: 'Segoe UI', sans-serif;
            transition: all .35s ease;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background-image:
                radial-gradient(circle at top right,
                    rgba(255, 140, 0, .05),
                    transparent 30%),
                radial-gradient(circle at bottom left,
                    rgba(255, 100, 0, .04),
                    transparent 30%);
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

        .controls {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: var(--card);
            border: 2px solid var(--card-border);
            padding: 25px;
            border-radius: 20px;
            box-shadow: 0 8px 20px var(--orange-shadow);
        }

        .stat-number {
            font-size: 32px;
            font-weight: 700;
            margin-top: 10px;
        }

        .pending-card {
            border-left: 5px solid #f59e0b;
        }

        .approved-card {
            border-left: 5px solid #22c55e;
        }

        .rejected-card {
            border-left: 5px solid #ef4444;
        }

        .user-card {
            border-left: 5px solid #3b82f6;
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

        .history-card {
            background: var(--card);
            color: var(--text);
            padding: 30px;
            border-radius: 24px;
            border: 2px solid var(--card-border);
            box-shadow: 0 10px 25px var(--orange-shadow), 0 20px 50px rgba(255, 152, 0, 0.06);
            overflow-x: auto;
            transition: all .35s ease;
        }

        .history-card:hover {
            box-shadow: 0 15px 35px var(--orange-shadow), 0 25px 60px rgba(255, 152, 0, 0.08);
        }

        .table-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
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

        .leave-id {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            background: var(--orange-subtle);
            color: var(--orange-primary);
            font-weight: 700;
            font-size: 13px;
        }

        .leave-type {
            color: var(--orange-primary);
            font-weight: 600;
        }

        .status {
            padding: 6px 14px;
            border-radius: 20px;
            color: white;
            font-size: 13px;
            font-weight: 600;
            display: inline-block;
            letter-spacing: 0.3px;
        }

        .status.pending {
            background: #f59e0b;
            border: 1px solid rgba(245, 158, 11, 0.3);
        }

        .status.approved {
            background: #22c55e;
            border: 1px solid rgba(34, 197, 94, 0.3);
        }

        .status.rejected {
            background: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .action-cell {
            display: flex;
            gap: 8px;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
        }

        .approve-btn,
        .reject-btn {
            text-decoration: none;
            color: white;
            padding: 8px 16px;
            border-radius: 10px;
            font-weight: 600;
            transition: all .3s ease;
            display: inline-block;
            font-size: 13px;
            border: none;
            cursor: pointer;
        }

        .approve-btn {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3);
        }

        .approve-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(34, 197, 94, 0.4);
            background: linear-gradient(135deg, #16a34a, #15803d);
        }

        .reject-btn {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        .reject-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(239, 68, 68, 0.4);
            background: linear-gradient(135deg, #dc2626, #b91c1c);
        }

        .no-action {
            color: var(--secondary);
            font-size: 13px;
            font-style: italic;
        }

        .no-data {
            text-align: center;
            color: var(--secondary);
            padding: 60px 20px;
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

        /* Modal Styles */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .65);
            backdrop-filter: blur(6px);
            z-index: 9999;
            justify-content: center;
            align-items: center;
            animation: fadeIn .25s ease;
        }

        .modal-content {
            width: 420px;
            max-width: 90%;
            background: var(--card);
            color: var(--text);
            border: 2px solid var(--card-border);
            border-radius: 24px;
            padding: 28px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .25), 0 10px 30px var(--orange-shadow);
            animation: popup .25s ease;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .modal-header h2 {
            margin: 0;
            color: var(--orange-primary);
            font-size: 22px;
        }

        .close-btn {
            background: none;
            border: none;
            color: var(--secondary);
            font-size: 28px;
            cursor: pointer;
            transition: .2s;
        }

        .close-btn:hover {
            color: #ef4444;
            transform: scale(1.1);
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .form-group textarea {
            width: 100%;
            box-sizing: border-box;
            background: var(--input-bg);
            color: var(--text);
            border: 2px solid var(--input-border);
            border-radius: 14px;
            padding: 15px;
            resize: vertical;
            outline: none;
            font-size: 14px;
            transition: .3s;
        }

        .form-group textarea:focus {
            border-color: var(--orange-primary);
            box-shadow: 0 0 0 4px var(--orange-subtle);
        }

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 25px;
        }

        .cancel-btn {
            background: var(--input-bg);
            color: var(--text);
            border: 2px solid var(--input-border);
            padding: 12px 22px;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 600;
        }

        .confirm-reject-btn {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            border: none;
            padding: 12px 22px;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 600;
            box-shadow: 0 6px 18px rgba(239, 68, 68, .3);
        }

        .confirm-reject-btn:hover {
            transform: translateY(-2px);
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes popup {
            from { opacity: 0; transform: scale(.9); }
            to { opacity: 1; transform: scale(1); }
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
            
            .stat-card {
                padding: 20px;
            }
            
            .stat-number {
                font-size: 28px;
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
                border-radius: 12px;
            }

            .theme-btn {
                width: 100%;
                justify-content: center;
                padding: 10px 20px;
                font-size: 14px;
                border-radius: 12px;
            }

            .history-card {
                padding: 20px 15px;
                border-radius: 16px;
            }

            .history-card:hover {
                transform: none;
            }

            .stats-container {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                gap: 12px;
                margin-bottom: 20px;
            }

            .stat-card {
                padding: 16px 15px;
                border-radius: 16px;
                border-left-width: 4px;
            }

            .stat-number {
                font-size: 24px;
                margin-top: 6px;
            }

            .stat-card div:first-child {
                font-size: 13px;
            }

            .table-wrapper {
                margin: 0 -5px;
            }

            table {
                font-size: 13px;
                min-width: 600px;
            }

            th {
                padding: 12px 10px;
                font-size: 12px;
            }

            td {
                padding: 10px 8px;
                font-size: 12px;
            }

            .status {
                padding: 4px 12px;
                font-size: 11px;
            }

            .leave-id {
                font-size: 11px;
                padding: 3px 10px;
            }

            .leave-type {
                font-size: 12px;
            }

            .action-cell {
                gap: 5px;
            }

            .approve-btn,
            .reject-btn {
                padding: 6px 12px;
                font-size: 11px;
                border-radius: 8px;
            }

            .no-action {
                font-size: 11px;
            }

            .modal-content {
                padding: 22px 20px;
                border-radius: 18px;
            }

            .modal-header h2 {
                font-size: 20px;
            }

            .form-group textarea {
                padding: 12px;
                font-size: 16px;
            }

            .cancel-btn,
            .confirm-reject-btn {
                padding: 10px 18px;
                font-size: 14px;
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
                font-size: 18px;
            }

            .page-title::after {
                width: 25px;
                height: 3px;
            }

            .history-card {
                padding: 15px 10px;
                border-radius: 14px;
                border-width: 1.5px;
            }

            .stats-container {
                grid-template-columns: 1fr 1fr;
                gap: 8px;
                margin-bottom: 15px;
            }

            .stat-card {
                padding: 12px 10px;
                border-radius: 12px;
                border-left-width: 3px;
            }

            .stat-number {
                font-size: 20px;
                margin-top: 4px;
            }

            .stat-card div:first-child {
                font-size: 11px;
            }

            .search-box input {
                padding: 8px 12px;
                font-size: 16px;
                border-radius: 10px;
            }

            .theme-btn {
                padding: 8px 16px;
                font-size: 13px;
                border-radius: 10px;
            }

            .table-wrapper {
                margin: 0 -5px;
            }

            table {
                font-size: 11px;
                min-width: 480px;
            }

            th {
                padding: 8px 6px;
                font-size: 10px;
                letter-spacing: 0.3px;
            }

            td {
                padding: 8px 6px;
                font-size: 11px;
            }

            .status {
                padding: 3px 8px;
                font-size: 10px;
                border-radius: 12px;
            }

            .leave-id {
                font-size: 10px;
                padding: 2px 8px;
                border-radius: 8px;
            }

            .leave-type {
                font-size: 11px;
            }

            .action-cell {
                flex-direction: column;
                gap: 4px;
                align-items: stretch;
            }

            .approve-btn,
            .reject-btn {
                padding: 6px 10px;
                font-size: 10px;
                border-radius: 6px;
                text-align: center;
                width: 100%;
                min-width: 60px;
            }

            .no-action {
                font-size: 10px;
            }

            .no-data {
                padding: 30px 15px;
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

            /* Modal responsive */
            .modal-content {
                padding: 18px 16px;
                border-radius: 16px;
                max-width: 95%;
            }

            .modal-header h2 {
                font-size: 18px;
            }

            .modal-header .close-btn {
                font-size: 24px;
            }

            .form-group label {
                font-size: 13px;
                margin-bottom: 6px;
            }

            .form-group textarea {
                padding: 10px 12px;
                font-size: 16px;
                border-radius: 10px;
                min-height: 80px;
            }

            .modal-actions {
                flex-direction: column;
                gap: 8px;
                margin-top: 18px;
            }

            .cancel-btn,
            .confirm-reject-btn {
                width: 100%;
                justify-content: center;
                padding: 10px 16px;
                font-size: 14px;
                border-radius: 10px;
                text-align: center;
            }

            /* Disable hover effects on mobile for performance */
            .stat-card:hover,
            .history-card:hover,
            .approve-btn:hover,
            .reject-btn:hover,
            .theme-btn:hover,
            .confirm-reject-btn:hover {
                transform: none;
            }

            .stat-card:active {
                transform: scale(0.97);
            }

            .approve-btn:active,
            .reject-btn:active,
            .theme-btn:active,
            .confirm-reject-btn:active {
                transform: scale(0.97);
            }
        }

        /* Very small phones */
        @media (max-width: 380px) {
            .main-content {
                padding: 8px 6px;
            }

            .page-title {
                font-size: 16px;
            }

            .history-card {
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
                font-size: 17px;
            }

            .stat-card div:first-child {
                font-size: 10px;
            }

            table {
                min-width: 400px;
                font-size: 10px;
            }

            th {
                padding: 6px 4px;
                font-size: 9px;
            }

            td {
                padding: 6px 4px;
                font-size: 10px;
            }

            .status {
                padding: 2px 6px;
                font-size: 9px;
            }

            .leave-id {
                font-size: 9px;
                padding: 2px 6px;
            }

            .approve-btn,
            .reject-btn {
                font-size: 9px;
                padding: 4px 8px;
                min-width: 50px;
            }

            .modal-content {
                padding: 14px 12px;
                border-radius: 14px;
            }

            .modal-header h2 {
                font-size: 16px;
            }

            .form-group textarea {
                font-size: 16px;
                padding: 8px 10px;
                min-height: 60px;
            }

            .cancel-btn,
            .confirm-reject-btn {
                font-size: 13px;
                padding: 8px 14px;
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
                margin-bottom: 12px;
            }

            .stat-card {
                padding: 10px 12px;
            }

            .stat-number {
                font-size: 18px;
                margin-top: 4px;
            }

            .stat-card div:first-child {
                font-size: 11px;
            }

            .history-card {
                padding: 15px;
            }

            table {
                font-size: 11px;
                min-width: 550px;
            }

            th {
                padding: 6px 8px;
                font-size: 10px;
            }

            td {
                padding: 6px 8px;
                font-size: 10px;
            }

            .controls {
                flex-direction: row;
            }

            .search-box {
                max-width: 150px;
            }

            .theme-btn {
                padding: 6px 14px;
                font-size: 12px;
                width: auto;
            }

            .modal-content {
                max-height: 85vh;
                padding: 18px;
            }

            .form-group textarea {
                min-height: 60px;
            }

            .modal-actions {
                flex-direction: row;
            }

            .cancel-btn,
            .confirm-reject-btn {
                width: auto;
                padding: 8px 16px;
            }
        }

        /* Touch-friendly improvements for mobile */
        @media (pointer: coarse) {
            input,
            textarea,
            .approve-btn,
            .reject-btn,
            .theme-btn,
            .cancel-btn,
            .confirm-reject-btn,
            .close-btn {
                touch-action: manipulation;
                -webkit-tap-highlight-color: transparent;
            }

            input,
            textarea,
            select {
                font-size: 16px !important;
            }

            .approve-btn,
            .reject-btn,
            .theme-btn,
            .cancel-btn,
            .confirm-reject-btn {
                min-height: 40px;
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
            
            .history-card {
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
        <div class="history-card">
            <div class="page-header">
                <h1 class="page-title">📅 Leave Management</h1>

                <div class="controls">
                    <div class="search-box">
                        <input type="text" id="searchInput" placeholder="🔍 Search employee, type, status..."
                            onkeyup="searchLeave()">
                    </div>

                    <button id="theme-toggle" class="theme-btn">
                        🌙 Dark Mode
                    </button>
                </div>
            </div>
            <div class="stats-container">

                <div class="stat-card pending-card">
                    <div>⏳ Pending Leaves</div>
                    <div class="stat-number">
                        <?= $pending_count ?>
                    </div>
                </div>

                <div class="stat-card approved-card">
                    <div>✅ Approved Leaves</div>
                    <div class="stat-number">
                        <?= $approved_count ?>
                    </div>
                </div>

                <div class="stat-card rejected-card">
                    <div>❌ Rejected Leaves</div>
                    <div class="stat-number">
                        <?= $rejected_count ?>
                    </div>
                </div>

                <div class="stat-card user-card">
                    <div>👥 Users Applied</div>
                    <div class="stat-number">
                        <?= $user_count ?>
                    </div>
                </div>

            </div>
            <div class="table-wrapper">
                <table id="leaveTable">
                    <thead>
                        <tr>
                            <th>Leave ID</th>
                            <th>Employee Name</th>
                            <th>Type</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                $formattedFrom = date('d M Y', strtotime($row['from_date']));
                                $formattedTo = date('d M Y', strtotime($row['to_date']));
                                ?>
                                <tr>
                                    <td><span class="leave-id">#<?php echo htmlspecialchars($row['leave_id']); ?></span></td>
                                    <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                                    <td>
                                        <span class="leave-type">
                                            <?php echo htmlspecialchars($row['leave_type']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $formattedFrom; ?></td>
                                    <td><?php echo $formattedTo; ?></td>
                                    <td><?php echo htmlspecialchars($row['reason']); ?></td>
                                    <td>
                                        <span class="status <?php echo strtolower($row['status']); ?>">
                                            <?php echo htmlspecialchars($row['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-cell">
                                            <?php if ($row['status'] == 'Pending') { ?>
                                                <a href="?approve=<?php echo $row['leave_id']; ?>" class="approve-btn"
                                                    onclick="return confirm('Approve this leave request?')">
                                                    ✅ Approve
                                                </a>
                                                <button class="reject-btn" onclick="openRejectModal(<?= $row['leave_id'] ?>)">
                                                    ❌ Reject
                                                </button>
                                            <?php } else { ?>
                                                <span class="no-action">— No Action —</span>
                                            <?php } ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo "<tr><td colspan='8' class='no-data'>
                                    <span class='empty-icon'>📭</span>
                                    <h3>No Leave Records Found</h3>
                                    <p>Leave requests will appear here once employees submit them.</p>
                                  </td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div id="rejectModal" class="modal-overlay">
        <div class="modal-content">

            <div class="modal-header">
                <h2>❌ Reject Leave Request</h2>
                <button type="button" class="close-btn" onclick="closeRejectModal()">
                    ×
                </button>
            </div>

            <form method="POST">

                <input type="hidden" id="leave_id" name="leave_id">

                <div class="form-group">
                    <label>Reason for Rejection</label>

                    <textarea name="reject_reason" required rows="5"
                        placeholder="Enter the reason for rejecting this leave request..."></textarea>
                </div>

                <div class="modal-actions">
                    <button type="button" class="cancel-btn" onclick="closeRejectModal()">
                        Cancel
                    </button>

                    <button type="submit" name="reject_leave" class="confirm-reject-btn">
                        Reject Leave
                    </button>
                </div>

            </form>

        </div>
    </div>
    </div>
    <script>
        // Wait for DOM to be ready
        document.addEventListener("DOMContentLoaded", function () {
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
                themeBtn.addEventListener('click', function () {
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

        function searchLeave() {
            let input = document.getElementById("searchInput");
            let filter = input.value.toLowerCase();

            let table = document.getElementById("leaveTable");
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
        function openRejectModal(id) {
            document.getElementById('leave_id').value = id;
            document.getElementById('rejectModal').style.display = 'flex';
        }

        function closeRejectModal() {
            document.getElementById('rejectModal').style.display = 'none';
        }
    </script>
</body>

</html>