<?php
session_start();
include 'db_connection.php';
include 'sidebar.php';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 15;
$offset = ($page - 1) * $limit;

// Filters
$user_filter = isset($_GET['user']) ? mysqli_real_escape_string($conn, $_GET['user']) : '';
$type_filter = isset($_GET['type']) ? mysqli_real_escape_string($conn, $_GET['type']) : '';
$date_from = isset($_GET['date_from']) ? mysqli_real_escape_string($conn, $_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? mysqli_real_escape_string($conn, $_GET['date_to']) : '';

// Build WHERE clause
$where = "WHERE 1=1";
if ($user_filter) {
    $where .= " AND (ua.employee_name LIKE '%$user_filter%' OR ua.employee_id LIKE '%$user_filter%')";
}
if ($type_filter) {
    $where .= " AND ua.activity_type = '$type_filter'";
}
if ($date_from) {
    $where .= " AND DATE(ua.created_at) >= '$date_from'";
}
if ($date_to) {
    $where .= " AND DATE(ua.created_at) <= '$date_to'";
}

// Get total count
$count_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM user_activity ua $where");
$total_records = mysqli_fetch_assoc($count_query)['total'];
$total_pages = ceil($total_records / $limit);

// Get activities
$query = mysqli_query($conn, "
    SELECT 
        ua.*,
        u.profile_picture,
        u.role as user_role
    FROM user_activity ua 
    LEFT JOIN users u ON ua.employee_id = u.employee_id 
    $where 
    ORDER BY ua.created_at DESC 
    LIMIT $limit OFFSET $offset
");

if (!$query) {
    die("Query Failed: " . mysqli_error($conn));
}

// Get unique activity types for filter
$types_query = mysqli_query($conn, "SELECT DISTINCT activity_type FROM user_activity ORDER BY activity_type");
$activity_types = [];
while ($type = mysqli_fetch_assoc($types_query)) {
    $activity_types[] = $type['activity_type'];
}

// Get active users count (unique users who have logged in)
$active_users_query = mysqli_query($conn, "SELECT COUNT(DISTINCT employee_id) as total FROM user_activity WHERE activity_type = 'Login'");
$active_users = mysqli_fetch_assoc($active_users_query)['total'];
?>

<!DOCTYPE html>
<html>

<head>
    <title>User Activity Log</title>
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

        /* Stats Bar */
        .stats-bar {
            display: flex;
            gap: 20px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }

        .stat-item {
            background: var(--card);
            border: 2px solid var(--card-border);
            border-radius: 16px;
            padding: 18px 24px;
            display: flex;
            align-items: center;
            gap: 15px;
            transition: all .35s ease;
            flex: 1;
            min-width: 180px;
        }

        .stat-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px var(--orange-shadow);
            border-color: var(--orange-primary);
        }

        .stat-icon {
            font-size: 32px;
        }

        .stat-info {
            display: flex;
            flex-direction: column;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: var(--orange-primary);
        }

        .stat-label {
            font-size: 13px;
            color: var(--secondary);
            font-weight: 500;
        }

        /* Filters */
        .filters {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
            flex-wrap: wrap;
            align-items: flex-end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .filter-group label {
            font-size: 12px;
            font-weight: 600;
            color: var(--secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .filter-group input,
        .filter-group select {
            padding: 10px 15px;
            border: 2px solid var(--input-border);
            border-radius: 12px;
            font-size: 14px;
            background: var(--input-bg);
            color: var(--text);
            min-width: 170px;
            transition: all .35s ease;
        }

        .filter-group input:focus,
        .filter-group select:focus {
            outline: none;
            border-color: var(--orange-primary);
            box-shadow: 0 0 0 4px var(--orange-subtle);
            background: var(--card);
        }

        .filter-group input::placeholder {
            color: var(--secondary);
            opacity: 0.6;
        }

        .filter-group select option {
            background: var(--card);
            color: var(--text);
        }

        .btn-filter {
            padding: 10px 24px;
            background: linear-gradient(135deg, var(--orange-gradient-start), var(--orange-primary));
            color: white;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all .35s ease;
            height: 42px;
            box-shadow: 0 4px 12px var(--orange-shadow);
        }

        .btn-filter:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px var(--orange-shadow);
            background: linear-gradient(135deg, var(--orange-primary), var(--orange-dark));
        }

        .btn-reset {
            padding: 10px 20px;
            background: transparent;
            color: var(--secondary);
            border: 2px solid var(--input-border);
            border-radius: 12px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all .35s ease;
            text-decoration: none;
            height: 42px;
            display: inline-flex;
            align-items: center;
        }

        .btn-reset:hover {
            border-color: var(--orange-primary);
            color: var(--orange-primary);
            transform: translateY(-2px);
        }

        /* Table */
        .table-wrapper {
            overflow-x: auto;
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
            text-align: left;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
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
            border-bottom: 1px solid var(--card-border);
            color: var(--text);
            font-size: 14px;
        }

        tr:last-child td {
            border-bottom: none;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--orange-gradient-start), var(--orange-primary));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 16px;
            flex-shrink: 0;
        }

        .user-avatar img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .user-name {
            font-weight: 600;
            color: var(--text);
        }

        .user-role {
            font-size: 12px;
            color: var(--secondary);
        }

        .activity-badge {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
            white-space: nowrap;
            letter-spacing: 0.3px;
        }

        .badge-login {
            background: rgba(34, 197, 94, 0.12);
            color: #22c55e;
            border: 1px solid rgba(34, 197, 94, 0.2);
        }

        .badge-logout {
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .badge-create {
            background: rgba(255, 152, 0, 0.12);
            color: var(--orange-primary);
            border: 1px solid rgba(255, 152, 0, 0.2);
        }

        .badge-update {
            background: rgba(245, 158, 11, 0.12);
            color: #f59e0b;
            border: 1px solid rgba(245, 158, 11, 0.2);
        }

        .badge-delete {
            background: rgba(220, 38, 38, 0.15);
            color: #dc2626;
            border: 1px solid rgba(220, 38, 38, 0.2);
        }

        .badge-view {
            background: rgba(6, 182, 212, 0.12);
            color: #06b6d4;
            border: 1px solid rgba(6, 182, 212, 0.2);
        }

        .badge-upload {
            background: rgba(255, 152, 0, 0.12);
            color: var(--orange-primary);
            border: 1px solid rgba(255, 152, 0, 0.2);
        }

        .badge-download {
            background: rgba(236, 72, 153, 0.12);
            color: #ec4899;
            border: 1px solid rgba(236, 72, 153, 0.2);
        }

        .badge-default {
            background: rgba(148, 163, 184, 0.1);
            color: #64748b;
            border: 1px solid rgba(148, 163, 184, 0.2);
        }

        .activity-time {
            font-size: 13px;
            color: var(--secondary);
        }

        .activity-date {
            font-size: 13px;
            font-weight: 500;
            color: var(--text);
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

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: 25px;
            flex-wrap: wrap;
        }

        .pagination a,
        .pagination span {
            padding: 10px 16px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all .35s ease;
            border: 2px solid var(--card-border);
            color: var(--text);
            background: var(--card);
        }

        .pagination a:hover {
            background: linear-gradient(135deg, var(--orange-gradient-start), var(--orange-primary));
            color: white;
            border-color: var(--orange-primary);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px var(--orange-shadow);
        }

        .pagination .active {
            background: linear-gradient(135deg, var(--orange-gradient-start), var(--orange-primary));
            color: white;
            border-color: var(--orange-primary);
            box-shadow: 0 4px 12px var(--orange-shadow);
        }

        .pagination .disabled {
            opacity: 0.5;
            pointer-events: none;
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

        /* Responsive */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 85px;
                padding: 20px;
            }

            .main-content.expanded {
                margin-left: 85px;
            }

            .filters {
                flex-direction: column;
                align-items: stretch;
            }

            .filter-group input,
            .filter-group select {
                min-width: 100%;
            }

            .btn-filter,
            .btn-reset {
                width: 100%;
                justify-content: center;
            }

            .stats-bar {
                flex-direction: column;
            }

            .stat-item {
                min-width: auto;
            }

            .card {
                padding: 20px;
            }

            table {
                font-size: 13px;
                min-width: 600px;
            }

            th,
            td {
                padding: 10px 8px;
            }

            .page-title {
                font-size: 24px;
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

            .card {
                padding: 15px;
            }

            table {
                font-size: 12px;
                min-width: 500px;
            }

            th,
            td {
                padding: 8px 6px;
            }

            .user-avatar {
                width: 32px;
                height: 32px;
                font-size: 13px;
            }

            .activity-badge {
                padding: 4px 10px;
                font-size: 10px;
            }

            .pagination a,
            .pagination span {
                padding: 6px 12px;
                font-size: 12px;
            }
        }
    </style>
</head>

<body>
    <div class="main-content" id="mainContent">
        <div class="page-header">
            <h1 class="page-title">📜 User Activity Log</h1>
        </div>

        <div class="card">
            <!-- Stats -->
            <div class="stats-bar">
                <div class="stat-item">
                    <span class="stat-icon">📊</span>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo $total_records; ?></div>
                        <div class="stat-label">Total Activities</div>
                    </div>
                </div>
                <div class="stat-item">
                    <span class="stat-icon">👤</span>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo $active_users; ?></div>
                        <div class="stat-label">Active Users</div>
                    </div>
                </div>
                <div class="stat-item">
                    <span class="stat-icon">📈</span>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo $total_pages; ?></div>
                        <div class="stat-label">Total Pages</div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <form method="GET" class="filters">
                <div class="filter-group">
                    <label>Search User</label>
                    <input type="text" name="user" placeholder="🔍 Name or ID..." value="<?php echo htmlspecialchars($user_filter); ?>">
                </div>
                <div class="filter-group">
                    <label>Activity Type</label>
                    <select name="type">
                        <option value="">All Types</option>
                        <?php foreach ($activity_types as $type): ?>
                            <option value="<?php echo $type; ?>" <?php echo ($type_filter == $type) ? 'selected' : ''; ?>>
                                <?php echo ucfirst($type); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label>From Date</label>
                    <input type="date" name="date_from" value="<?php echo $date_from; ?>">
                </div>
                <div class="filter-group">
                    <label>To Date</label>
                    <input type="date" name="date_to" value="<?php echo $date_to; ?>">
                </div>
                <button type="submit" class="btn-filter">🔍 Filter</button>
                <a href="user_activity.php" class="btn-reset">✕ Reset</a>
            </form>

            <!-- Table -->
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Activity</th>
                            <th>Description</th>
                            <th>IP Address</th>
                            <th>Date &amp; Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($query) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($query)):
                                $activity_type = $row['activity_type'];
                                $activity_type_lower = strtolower($activity_type);
                                $badge_class = 'badge-default';

                                if (strpos($activity_type_lower, 'login') !== false) $badge_class = 'badge-login';
                                elseif (strpos($activity_type_lower, 'logout') !== false) $badge_class = 'badge-logout';
                                elseif (strpos($activity_type_lower, 'create') !== false || strpos($activity_type_lower, 'add') !== false) $badge_class = 'badge-create';
                                elseif (strpos($activity_type_lower, 'update') !== false || strpos($activity_type_lower, 'edit') !== false) $badge_class = 'badge-update';
                                elseif (strpos($activity_type_lower, 'delete') !== false || strpos($activity_type_lower, 'remove') !== false) $badge_class = 'badge-delete';
                                elseif (strpos($activity_type_lower, 'view') !== false) $badge_class = 'badge-view';
                                elseif (strpos($activity_type_lower, 'upload') !== false) $badge_class = 'badge-upload';
                                elseif (strpos($activity_type_lower, 'download') !== false) $badge_class = 'badge-download';

                                $formatted_date = date('d M Y', strtotime($row['created_at']));
                                $formatted_time = date('h:i A', strtotime($row['created_at']));

                                $user_name = !empty($row['employee_name']) ? $row['employee_name'] : 'Unknown User';
                                $user_role = !empty($row['user_role']) ? $row['user_role'] : 'user';
                                $profile_picture = $row['profile_picture'] ?? null;
                                $activity_details = $row['activity_details'] ?? '';
                                
                                // Format login and logout times
                                $login_time = !empty($row['login_time']) ? date('h:i A', strtotime($row['login_time'])) : '';
                                $logout_time = !empty($row['logout_time']) ? date('h:i A', strtotime($row['logout_time'])) : '';
                            ?>
                                <tr>
                                    <td>
                                        <div class="user-info">
                                            <?php if (!empty($profile_picture) && file_exists($profile_picture)): ?>
                                                <img src="<?php echo htmlspecialchars($profile_picture); ?>" class="user-avatar" alt="Avatar">
                                            <?php else: ?>
                                                <div class="user-avatar">
                                                    <?php echo strtoupper(substr($user_name, 0, 1)); ?>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <div class="user-name"><?php echo htmlspecialchars($user_name); ?></div>
                                                <div class="user-role"><?php echo ucfirst($user_role); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="activity-badge <?php echo $badge_class; ?>">
                                            <?php echo htmlspecialchars($activity_type); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                        // Display activity details with login/logout times
                                        if (!empty($activity_details)) {
                                            echo htmlspecialchars($activity_details);
                                        } elseif ($activity_type_lower == 'login' && !empty($login_time)) {
                                            echo "Logged in at: <strong>" . $login_time . "</strong>";
                                        } elseif ($activity_type_lower == 'logout' && !empty($logout_time)) {
                                            echo "Logged out at: <strong>" . $logout_time . "</strong>";
                                        } elseif (!empty($login_time) && !empty($logout_time)) {
                                            echo "Login: " . $login_time . " | Logout: " . $logout_time;
                                        } elseif (!empty($login_time)) {
                                            echo "Login: " . $login_time;
                                        } elseif (!empty($logout_time)) {
                                            echo "Logout: " . $logout_time;
                                        } else {
                                            echo htmlspecialchars($activity_type);
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <span class="activity-time"><?php echo htmlspecialchars($row['ip_address'] ?? 'N/A'); ?></span>
                                    </td>
                                    <td>
                                        <div class="activity-date"><?php echo $formatted_date; ?></div>
                                        <div class="activity-time"><?php echo $formatted_time; ?></div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5">
                                    <div class="no-data">
                                        <span class="empty-icon">📭</span>
                                        <h3>No Activity Records Found</h3>
                                        <p>User activities will appear here once users start interacting with the system.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php
                    $query_params = $_GET;
                    unset($query_params['page']);
                    $base_url = 'user_activity.php';
                    if (!empty($query_params)) {
                        $base_url .= '?' . http_build_query($query_params) . '&';
                    } else {
                        $base_url .= '?';
                    }
                    ?>

                    <?php if ($page > 1): ?>
                        <a href="<?php echo $base_url; ?>page=<?php echo $page - 1; ?>">← Previous</a>
                    <?php else: ?>
                        <span class="disabled">← Previous</span>
                    <?php endif; ?>

                    <?php
                    $start = max(1, $page - 2);
                    $end = min($total_pages, $page + 2);

                    if ($start > 1) {
                        echo '<a href="' . $base_url . 'page=1">1</a>';
                        if ($start > 2) echo '<span>...</span>';
                    }

                    for ($i = $start; $i <= $end; $i++) {
                        if ($i == $page) {
                            echo '<span class="active">' . $i . '</span>';
                        } else {
                            echo '<a href="' . $base_url . 'page=' . $i . '">' . $i . '</a>';
                        }
                    }

                    if ($end < $total_pages) {
                        if ($end < $total_pages - 1) echo '<span>...</span>';
                        echo '<a href="' . $base_url . 'page=' . $total_pages . '">' . $total_pages . '</a>';
                    }
                    ?>

                    <?php if ($page < $total_pages): ?>
                        <a href="<?php echo $base_url; ?>page=<?php echo $page + 1; ?>">Next →</a>
                    <?php else: ?>
                        <span class="disabled">Next →</span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const mainContent = document.getElementById("mainContent");

            // Apply sidebar state
            if (localStorage.getItem("sidebarState") === "collapsed") {
                if (mainContent) mainContent.classList.add("expanded");
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
        });
    </script>
</body>

</html>