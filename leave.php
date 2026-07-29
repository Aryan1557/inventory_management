<?php
session_start();

include 'db_connection.php';
include 'session_check.php';

$user_id = $_SESSION['user_id'];

$message = "";

if (isset($_POST['apply_leave'])) {
    $leave_type = mysqli_real_escape_string($conn, $_POST['leave_type']);
    $from_date = $_POST['from_date'];
    $to_date = $_POST['to_date'];
    $reason = mysqli_real_escape_string($conn, $_POST['reason']);

    // Validate dates
    if ($from_date > $to_date) {
        $_SESSION['success_message'] = "From Date cannot be greater than To Date.";
    } else {
        // Check duplicate application
        $check = mysqli_query($conn, "
            SELECT leave_id
            FROM leave_management
            WHERE user_id='$user_id'
            AND leave_type='$leave_type'
            AND from_date='$from_date'
            AND to_date='$to_date'
        ");

        if (mysqli_num_rows($check) > 0) {
            $_SESSION['success_message'] = "You have already applied for this leave.";
        } else {
            // Get employee name from session or users table
            $name = isset($_SESSION['name']) ? $_SESSION['name'] : '';

            // If name not in session, get it from database
            if (empty($name)) {
                $user_query = mysqli_query($conn, "SELECT name FROM users WHERE id='$user_id'");
                if ($user_row = mysqli_fetch_assoc($user_query)) {
                    $name = $user_row['name'];
                }
            }

            $name = mysqli_real_escape_string($conn, $name);

            $insert = mysqli_query($conn, "
                INSERT INTO leave_management
                (user_id, name, leave_type, from_date, to_date, reason, status)
                VALUES
                ('$user_id', '$name', '$leave_type', '$from_date', '$to_date', '$reason', 'Pending')
            ");

            if ($insert) {
                $_SESSION['success_message'] = "Leave application submitted successfully.";
            } else {
                $_SESSION['success_message'] = "Insert Failed: " . mysqli_error($conn);
            }
        }
    }

    header("Location: leave.php");
    exit();
}

// Leave statistics - ONLY for current user
$stats = mysqli_query($conn, "
    SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN status='Approved' THEN 1 ELSE 0 END) AS approved,
        SUM(CASE WHEN status='Rejected' THEN 1 ELSE 0 END) AS rejected,
        SUM(CASE WHEN status='Pending' THEN 1 ELSE 0 END) AS pending
    FROM leave_management
    WHERE user_id='$user_id'
");

$leave_stats = mysqli_fetch_assoc($stats);
include 'sidebar1.php';
?>

<!DOCTYPE html>
<html>

<head>
    <title>Leave Application</title>
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
            --table-hover: rgba(255, 152, 0, 0.05);
            --table-stripe: rgba(255, 152, 0, 0.03);
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

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 35px;
            transition: all .4s ease;
            min-height: 100vh;
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

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
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
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px var(--orange-shadow);
        }

        .stat-number {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .stat-label {
            color: var(--secondary);
            font-size: 14px;
            font-weight: 500;
        }

        .total .stat-number {
            color: var(--orange-primary);
        }

        .approved-card .stat-number {
            color: #22c55e;
        }

        .rejected-card .stat-number {
            color: #ef4444;
        }

        .pending-card .stat-number {
            color: #f59e0b;
        }

        .leave-card {
            background: var(--card);
            border-radius: 24px;
            padding: 35px;
            box-shadow: 0 10px 25px var(--orange-shadow), 0 20px 50px rgba(255, 152, 0, 0.06);
            border: 2px solid var(--card-border);
            transition: all .35s ease;
        }

        .leave-card:hover {
            box-shadow: 0 15px 35px var(--orange-shadow), 0 25px 60px rgba(255, 152, 0, 0.08);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text);
            font-size: 14px;
            letter-spacing: 0.3px;
        }

        .form-control {
            padding: 14px 18px;
            border-radius: 14px;
            border: 2px solid var(--input-border);
            font-size: 15px;
            background: var(--input-bg);
            color: var(--text);
            transition: all .35s ease;
            font-family: 'Segoe UI', sans-serif;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--orange-primary);
            box-shadow: 0 0 0 4px var(--orange-subtle);
            background: var(--card);
        }

        .form-control::placeholder {
            color: var(--secondary);
            opacity: 0.6;
        }

        .form-control option {
            background: var(--card);
            color: var(--text);
        }

        textarea {
            resize: none;
            height: 120px;
        }

        .submit-btn {
            margin-top: 25px;
            background: linear-gradient(135deg, var(--orange-gradient-start), var(--orange-primary));
            color: white;
            border: none;
            padding: 15px 35px;
            border-radius: 14px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 700;
            transition: all .35s ease;
            box-shadow: 0 8px 20px var(--orange-shadow);
            width: 100%;
            letter-spacing: 0.5px;
            position: relative;
            overflow: hidden;
        }

        .submit-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.6s ease;
        }

        .submit-btn:hover::before {
            left: 100%;
        }

        .submit-btn:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 35px var(--orange-shadow);
            background: linear-gradient(135deg, var(--orange-primary), var(--orange-dark));
        }

        .submit-btn:active {
            transform: translateY(0px);
        }

        .success {
            background: rgba(34, 197, 94, 0.1);
            color: #22c55e;
            padding: 15px 20px;
            border-radius: 14px;
            margin-bottom: 20px;
            font-weight: 500;
            border: 1px solid rgba(34, 197, 94, 0.3);
        }

        .error {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            padding: 15px 20px;
            border-radius: 14px;
            margin-bottom: 20px;
            font-weight: 500;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .history-card {
            margin-top: 35px;
            background: var(--card);
            border-radius: 24px;
            padding: 30px;
            box-shadow: 0 10px 25px var(--orange-shadow), 0 20px 50px rgba(255, 152, 0, 0.06);
            overflow-x: auto;
            border: 2px solid var(--card-border);
            transition: all .35s ease;
        }

        .history-card:hover {
            box-shadow: 0 15px 35px var(--orange-shadow), 0 25px 60px rgba(255, 152, 0, 0.08);
        }

        .history-title {
            color: var(--orange-primary);
            margin-bottom: 20px;
            font-size: 22px;
            font-weight: 700;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 500px;
        }

        thead {
            background: linear-gradient(135deg, var(--orange-gradient-start), var(--orange-primary));
            color: white;
        }

        th {
            color: white;
            padding: 14px 15px;
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
            padding: 12px 15px;
            text-align: center;
            border-bottom: 1px solid var(--card-border);
            color: var(--text);
            font-size: 14px;
        }

        tr:last-child td {
            border-bottom: none;
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

        .no-data {
            text-align: center;
            padding: 40px;
            color: var(--secondary);
        }

        .no-data .empty-icon {
            font-size: 50px;
            display: block;
            margin-bottom: 15px;
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

        /* view reason  */
        .reason-btn {
            background: linear-gradient(135deg,
                    var(--orange-gradient-start),
                    var(--orange-primary));

            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            transition: .3s;
        }

        .reason-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px var(--orange-shadow);
        }

        /* pop up     */
        .reason-box {
            background: var(--input-bg);
            border: 2px solid var(--input-border);
            border-radius: 14px;
            padding: 20px;
            margin-top: 15px;
            line-height: 1.7;
            color: var(--text);
        }

        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .65);
            backdrop-filter: blur(5px);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            width: 450px;
            max-width: 90%;
            background: var(--card);
            color: var(--text);
            border: 2px solid var(--card-border);
            border-radius: 24px;
            padding: 25px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .25);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .close-btn {
            background: none;
            border: none;
            color: var(--secondary);
            font-size: 28px;
            cursor: pointer;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 85px;
                padding: 20px;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .page-title {
                font-size: 24px;
            }

            .theme-btn {
                width: 100%;
                justify-content: center;
            }

            .stats-container {
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
            }

            .stat-card {
                padding: 20px;
            }

            .stat-number {
                font-size: 26px;
            }

            .leave-card {
                padding: 25px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .history-card {
                padding: 20px;
            }

            table {
                font-size: 13px;
                min-width: 400px;
            }

            th,
            td {
                padding: 10px 8px;
            }
        }

        @media (max-width: 480px) {
            .main-content {
                padding: 15px;
            }

            .stats-container {
                grid-template-columns: 1fr 1fr;
                gap: 10px;
            }

            .stat-card {
                padding: 15px;
            }

            .stat-number {
                font-size: 22px;
            }

            .stat-label {
                font-size: 12px;
            }

            .leave-card {
                padding: 20px;
            }

            .submit-btn {
                padding: 12px 20px;
                font-size: 14px;
            }

            .history-card {
                padding: 15px;
            }

            table {
                font-size: 12px;
                min-width: 350px;
            }

            th,
            td {
                padding: 8px 6px;
            }

            .status {
                padding: 4px 10px;
                font-size: 11px;
            }
        }
    </style>
</head>

<body>
    <div class="main-content" id="mainContent">
        <div class="page-header">
            <h1 class="page-title">📝 Leave Application</h1>
            <button id="theme-toggle" class="theme-btn">🌙 Dark Mode</button>
        </div>

        <div class="stats-container">

            <div class="stat-card total">
                <div class="stat-number">
                    <?= $leave_stats['total'] ?? 0 ?>
                </div>
                <div class="stat-label">
                    Total Leaves
                </div>
            </div>

            <div class="stat-card approved-card">
                <div class="stat-number">
                    <?= $leave_stats['approved'] ?? 0 ?>
                </div>
                <div class="stat-label">
                    ✅ Approved
                </div>
            </div>

            <div class="stat-card rejected-card">
                <div class="stat-number">
                    <?= $leave_stats['rejected'] ?? 0 ?>
                </div>
                <div class="stat-label">
                    ❌ Rejected
                </div>
            </div>

            <div class="stat-card pending-card">
                <div class="stat-number">
                    <?= $leave_stats['pending'] ?? 0 ?>
                </div>
                <div class="stat-label">
                    ⏳ Pending
                </div>
            </div>

        </div>

        <?php
        if (isset($_SESSION['success_message'])) {
            $msg_type = strpos($_SESSION['success_message'], 'successfully') !== false ? 'success' : 'error';
            echo "<div class='{$msg_type}'>{$_SESSION['success_message']}</div>";
            unset($_SESSION['success_message']);
        }
        ?>

        <div class="leave-card">
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Leave Type</label>
                        <select name="leave_type" class="form-control" required>
                            <option value="">Select Leave Type</option>
                            <option value="Casual Leave">Casual Leave</option>
                            <option value="Sick Leave">Sick Leave</option>
                            <option value="Emergency Leave">Emergency Leave</option>
                            <option value="Paid Leave">Paid Leave</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>From Date</label>
                        <input type="date" name="from_date" class="form-control" required
                            min="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <div class="form-group">
                        <label>To Date</label>
                        <input type="date" name="to_date" class="form-control" required
                            min="<?php echo date('Y-m-d'); ?>">
                    </div>
                </div>

                <div class="form-group" style="margin-top:20px;">
                    <label>Reason</label>
                    <textarea name="reason" class="form-control" required
                        placeholder="Please provide a detailed reason for your leave..."></textarea>
                </div>

                <button type="submit" name="apply_leave" class="submit-btn">
                    📤 Submit Application
                </button>
            </form>
        </div>

        <div class="history-card">
            <h2 class="history-title">📋 Leave History</h2>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Leave ID</th>
                            <th>Type</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Status</th>
                            <th>View Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // FIXED: Added WHERE clause to filter by user_id
                        $history = mysqli_query($conn, "
                            SELECT leave_id, leave_type, from_date, to_date, status, rejection_reason 
                            FROM leave_management 
                            WHERE user_id='$user_id'
                            ORDER BY leave_id DESC
                        ");

                        if (!$history) {
                            echo "<tr><td colspan='6' style='color: var(--orange-primary); padding: 20px;'>Query Failed: " . mysqli_error($conn) . "</td></tr>";
                        } elseif (mysqli_num_rows($history) == 0) {
                            echo "<tr><td colspan='6' class='no-data'>
                                    <span class='empty-icon'>📭</span>
                                    No leave history found
                                  </td></tr>";
                        } else {
                            while ($row = mysqli_fetch_assoc($history)) {
                                $status = strtolower($row['status']);
                                echo "<tr>
                                    <td><strong>#{$row['leave_id']}</strong></td>
                                    <td>{$row['leave_type']}</td>
                                    <td>" . date('d M Y', strtotime($row['from_date'])) . "</td>
                                    <td>" . date('d M Y', strtotime($row['to_date'])) . "</td>
                                    <td>
                                        <span class='status $status'>
                                            {$row['status']}
                                        </span>
                                    </td>
                                    <td>";

                                // Check if the leave is rejected AND has a rejection reason
                                if ($row['status'] == 'Rejected' && !empty($row['rejection_reason'])) {
                                    // Escape the reason for JavaScript
                                    $escaped_reason = htmlspecialchars($row['rejection_reason'], ENT_QUOTES, 'UTF-8');
                                    echo "
                                        <button
                                            class='reason-btn'
                                            onclick='showReason(\"" . addslashes($escaped_reason) . "\")'>
                                            👁 View
                                        </button>";
                                } else {
                                    echo "-";
                                }
                                echo "</td></tr>";
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- MODAL HTML - ADDED THIS -->
    <div class="modal-overlay" id="reasonModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 style="color: var(--orange-primary);">📋 Rejection Reason</h2>
                <button class="close-btn" onclick="closeReason()">✕</button>
            </div>
            <div class="reason-box" id="reasonText">
                <!-- Reason will be displayed here -->
            </div>
        </div>
    </div>

    <script>
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

            // Close modal when clicking outside
            document.getElementById('reasonModal').addEventListener('click', function (e) {
                if (e.target === this) {
                    closeReason();
                }
            });

            // Close modal with Escape key
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    closeReason();
                }
            });
        });

        function showReason(reason) {
            document.getElementById('reasonText').innerHTML = reason;
            document.getElementById('reasonModal').style.display = 'flex';
        }

        function closeReason() {
            document.getElementById('reasonModal').style.display = 'none';
        }
    </script>
</body>

</html>