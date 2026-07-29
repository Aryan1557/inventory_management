<?php
session_start();
include 'db_connection.php';
// include 'session_check.php';
include 'sidebar.php';

// Initialize filter variables
$filter_role = isset($_GET['role']) ? $_GET['role'] : '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$filter_city = isset($_GET['city']) ? $_GET['city'] : '';
$search_query = isset($_GET['search']) ? $_GET['search'] : '';

// Build WHERE clause for filters
$where_conditions = [];
$params = [];
$types = "";

if (!empty($search_query)) {
    $where_conditions[] = "(name LIKE ? OR email_id LIKE ? OR employee_id LIKE ? OR contact_no LIKE ?)";
    $search_param = "%$search_query%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ssss";
}

if (!empty($filter_role)) {
    $where_conditions[] = "role = ?";
    $params[] = $filter_role;
    $types .= "s";
}

if (!empty($filter_status)) {
    $where_conditions[] = "status = ?";
    $params[] = $filter_status;
    $types .= "s";
}

if (!empty($filter_city)) {
    $where_conditions[] = "city = ?";
    $params[] = $filter_city;
    $types .= "s";
}

// Build the final query
$sql = "SELECT * FROM users";
if (!empty($where_conditions)) {
    $sql .= " WHERE " . implode(" AND ", $where_conditions);
}
$sql .= " ORDER BY created_at DESC";

// Execute query with prepared statement
if (!empty($params)) {
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
    } else {
        $result = mysqli_query($conn, $sql);
    }
} else {
    $result = mysqli_query($conn, $sql);
}

$users = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }
}

// Get distinct cities for filter dropdown
$city_sql = "SELECT DISTINCT city FROM users WHERE city IS NOT NULL AND city != '' ORDER BY city";
$city_result = mysqli_query($conn, $city_sql);
$cities = [];
if ($city_result) {
    while ($row = mysqli_fetch_assoc($city_result)) {
        $cities[] = $row['city'];
    }
}

// Handle status toggle
if (isset($_POST['toggle_status'])) {
    $user_id = $_POST['user_id'];
    $current_status = $_POST['current_status'];
    $new_status = ($current_status == 'active') ? 'inactive' : 'active';
    
    $update_sql = "UPDATE users SET status = ? WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $update_sql);
    mysqli_stmt_bind_param($stmt, "si", $new_status, $user_id);
    if (mysqli_stmt_execute($stmt)) {
        echo "<script>alert('Status updated successfully'); window.location.href='manage_user.php" . getFilterQueryString() . "';</script>";
    } else {
        echo mysqli_error($conn);
    }
}

// Handle delete user
if (isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];
    
    // Get profile picture to delete
    $get_pic_sql = "SELECT profile_picture FROM users WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $get_pic_sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $pic_result = mysqli_stmt_get_result($stmt);
    if ($pic_result) {
        $user_data = mysqli_fetch_assoc($pic_result);
        if (!empty($user_data['profile_picture']) && file_exists($user_data['profile_picture'])) {
            unlink($user_data['profile_picture']);
        }
    }
    
    $delete_sql = "DELETE FROM users WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $delete_sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    if (mysqli_stmt_execute($stmt)) {
        echo "<script>alert('User deleted successfully'); window.location.href='manage_user.php" . getFilterQueryString() . "';</script>";
    } else {
        echo mysqli_error($conn);
    }
}

// Handle edit/update user via AJAX
if (isset($_POST['update_user_ajax'])) {
    $user_id = $_POST['user_id'];
    $name = $_POST['name'];
    $address = $_POST['address'];
    $employee_id = $_POST['employee_id'];
    $email_id = $_POST['email_id'];
    $contact_no = $_POST['contact_no'];
    $username = $_POST['username'];
    $designation = $_POST['designation'];
    $role = $_POST['role'];
    $city = $_POST['city'];
    $status = $_POST['status'];
    $password = trim($_POST['password']);

    // Get current profile picture
    $get_pic_sql = "SELECT profile_picture FROM users WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $get_pic_sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $pic_result = mysqli_stmt_get_result($stmt);
    $current_user = mysqli_fetch_assoc($pic_result);
    $profile_picture = $current_user['profile_picture'];

    // Handle profile picture upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['profile_picture'];

        // 1. Check for upload errors first (this is what silently killed uploads before)
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $upload_errors = [
                UPLOAD_ERR_INI_SIZE   => 'The file is larger than this server allows (upload_max_filesize).',
                UPLOAD_ERR_FORM_SIZE  => 'The file is larger than the form allows.',
                UPLOAD_ERR_PARTIAL    => 'The file was only partially uploaded. Please try again.',
                UPLOAD_ERR_NO_TMP_DIR => 'Server is missing a temporary folder.',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk on the server.',
                UPLOAD_ERR_EXTENSION  => 'A server extension stopped the file upload.',
            ];
            echo json_encode([
                'success' => false,
                'message' => 'Photo upload failed: ' . ($upload_errors[$file['error']] ?? ('Unknown upload error (code ' . $file['error'] . ')'))
            ]);
            exit();
        }

        // 2. Verify it's actually an image (don't trust the file name/extension)
        $allowed_types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!isset($allowed_types[$mime])) {
            echo json_encode(['success' => false, 'message' => 'Photo upload failed: only JPG, PNG, GIF, or WEBP images are allowed.']);
            exit();
        }

        // 3. Use an absolute path so this works no matter what the script's working directory is
        $target_dir = __DIR__ . '/uploads/profiles/';

        if (!is_dir($target_dir)) {
            if (!mkdir($target_dir, 0755, true) && !is_dir($target_dir)) {
                echo json_encode(['success' => false, 'message' => 'Photo upload failed: could not create the uploads/profiles folder. Check folder permissions.']);
                exit();
            }
        }

        if (!is_writable($target_dir)) {
            echo json_encode(['success' => false, 'message' => 'Photo upload failed: uploads/profiles folder is not writable by the server (try chmod 755).']);
            exit();
        }

        // 4. Build a safe file name and the relative path we store/display from
        $safe_base = preg_replace('/[^A-Za-z0-9_\-]/', '_', pathinfo($file['name'], PATHINFO_FILENAME));
        $file_name = time() . '_' . $safe_base . '.' . $allowed_types[$mime];
        $target_file = $target_dir . $file_name;
        $relative_path = 'uploads/profiles/' . $file_name;

        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            // Delete old profile picture if it exists
            if (!empty($current_user['profile_picture']) && file_exists($current_user['profile_picture'])) {
                unlink($current_user['profile_picture']);
            }
            $profile_picture = $relative_path;
        } else {
            echo json_encode(['success' => false, 'message' => 'Photo upload failed: move_uploaded_file() could not write to ' . $target_dir . '. Check that the folder exists and is writable.']);
            exit();
        }
    }

    // Build update query
    $update_sql = "UPDATE users SET 
                    name = ?,
                    address = ?,
                    employee_id = ?,
                    email_id = ?,
                    contact_no = ?,
                    username = ?,
                    designation = ?,
                    role = ?,
                    city = ?,
                    profile_picture = ?,
                    status = ?";

    $params = [
        $name, $address, $employee_id, $email_id, 
        $contact_no, $username, $designation, $role, 
        $city, $profile_picture, $status
    ];
    $types = "sssssssssss";

    // Add password to update if provided
    if (!empty($password)) {
        $password_hash = md5($password);
        $update_sql .= ", password_hash = ?";
        $params[] = $password_hash;
        $types .= "s";
    }

    $update_sql .= " WHERE user_id = ?";
    $params[] = $user_id;
    $types .= "i";

    $stmt = mysqli_prepare($conn, $update_sql);
    mysqli_stmt_bind_param($stmt, $types, ...$params);

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => 'User updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating user: ' . mysqli_error($conn)]);
    }
    exit();
}

// Helper function to preserve filter query string
function getFilterQueryString() {
    $params = [];
    if (!empty($_GET['role'])) $params[] = "role=" . urlencode($_GET['role']);
    if (!empty($_GET['status'])) $params[] = "status=" . urlencode($_GET['status']);
    if (!empty($_GET['city'])) $params[] = "city=" . urlencode($_GET['city']);
    if (!empty($_GET['search'])) $params[] = "search=" . urlencode($_GET['search']);
    return !empty($params) ? "?" . implode("&", $params) : "";
}

// Helper function to get filter query string without a specific parameter
function getFilterQueryStringWithout($exclude) {
    $params = [];
    if (!empty($_GET['role']) && $_GET['role'] != $exclude) $params[] = "role=" . urlencode($_GET['role']);
    if (!empty($_GET['status']) && $_GET['status'] != $exclude) $params[] = "status=" . urlencode($_GET['status']);
    if (!empty($_GET['city']) && $_GET['city'] != $exclude) $params[] = "city=" . urlencode($_GET['city']);
    if (!empty($_GET['search']) && $_GET['search'] != $exclude) $params[] = "search=" . urlencode($_GET['search']);
    return !empty($params) ? "?" . implode("&", $params) : "";
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Manage Users</title>
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
            
            --badge-active-bg: #e8f5e9;
            --badge-active-text: #2e7d32;
            --badge-inactive-bg: #fce4ec;
            --badge-inactive-text: #c62828;
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
            
            --badge-active-bg: #1b3a1b;
            --badge-active-text: #81c784;
            --badge-inactive-bg: #3a1b1b;
            --badge-inactive-text: #ef9a9a;
            
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
            font-family: 'Segoe UI', sans-serif;
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

        .container {
            max-width: 1300px;
            margin: 0 auto;
            background: var(--card);
            color: var(--text);
            border-radius: 28px;
            padding: 35px;
            border: 2px solid var(--card-border);
            box-shadow: 0 10px 25px var(--orange-shadow), 0 20px 50px rgba(255, 152, 0, 0.06);
            transition: all .35s ease;
        }

        .container:hover {
            box-shadow: 0 15px 35px var(--orange-shadow), 0 25px 60px rgba(255, 152, 0, 0.08);
        }

        .welcome-card {
            background: linear-gradient(135deg,
                    var(--orange-gradient-start),
                    var(--orange-primary),
                    var(--orange-dark));
            color: white;
            padding: 40px;
            border-radius: 24px;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
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
            background: rgba(255, 255, 255, .06);
            top: -60px;
            right: -60px;
        }

        .welcome-card h1 {
            color: white;
            position: relative;
            z-index: 1;
            font-size: 28px;
            font-weight: 700;
        }

        .welcome-card p {
            color: #ffe0b0;
            position: relative;
            z-index: 1;
            opacity: .9;
            margin-top: 8px;
            font-size: 16px;
        }

        /* Filter Section Styles */
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

        .filter-stats {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid var(--card-border);
        }

        .filter-stats .stat-item {
            font-size: 14px;
            color: var(--secondary);
        }

        .filter-stats .stat-item strong {
            color: var(--text);
            font-weight: 700;
        }

        .active-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .filter-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            background: var(--orange-subtle);
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            color: var(--orange-primary);
        }

        .filter-tag .remove-filter {
            cursor: pointer;
            font-weight: 700;
            color: var(--secondary);
            transition: color .3s ease;
            text-decoration: none;
        }

        .filter-tag .remove-filter:hover {
            color: #c62828;
        }

        .header-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 25px;
        }

        .search-box {
            display: flex;
            gap: 10px;
            flex: 1;
            max-width: 400px;
        }

        .search-box form {
            display: flex;
            gap: 10px;
            flex: 1;
        }

        .search-box input {
            flex: 1;
            padding: 12px 16px;
            border: 2px solid var(--input-border);
            border-radius: 14px;
            background: var(--input-bg);
            color: var(--text);
            transition: all .35s ease;
            font-size: 14px;
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--orange-primary);
            box-shadow: 0 0 0 4px var(--orange-subtle);
        }

        .btn-add {
            padding: 12px 28px;
            border: none;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--orange-gradient-start), var(--orange-primary));
            color: white;
            font-weight: 700;
            cursor: pointer;
            transition: all .35s ease;
            box-shadow: 0 8px 20px var(--orange-shadow);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-add:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px var(--orange-shadow);
            background: linear-gradient(135deg, var(--orange-primary), var(--orange-dark));
        }

        .table-responsive {
            overflow-x: auto;
            border-radius: 16px;
            border: 2px solid var(--card-border);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }

        thead {
            background: var(--orange-subtle);
        }

        th {
            padding: 16px 18px;
            text-align: left;
            font-weight: 700;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--orange-primary);
            border-bottom: 2px solid var(--card-border);
        }

        td {
            padding: 16px 18px;
            border-bottom: 1px solid var(--card-border);
            font-size: 14px;
            vertical-align: middle;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover {
            background: var(--orange-subtle);
        }

        .user-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--orange-light);
        }

        .user-avatar-placeholder {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: var(--orange-subtle);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: var(--orange-primary);
            border: 2px solid var(--orange-light);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-details {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-weight: 600;
            color: var(--text);
        }

        .user-email {
            font-size: 12px;
            color: var(--secondary);
        }

        .badge {
            display: inline-block;
            padding: 5px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.3px;
        }

        .badge-active {
            background: var(--badge-active-bg);
            color: var(--badge-active-text);
        }

        .badge-inactive {
            background: var(--badge-inactive-bg);
            color: var(--badge-inactive-text);
        }

        .badge-role {
            background: var(--orange-subtle);
            color: var(--orange-primary);
        }

        .action-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn-action {
            padding: 8px 14px;
            border: none;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all .35s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-action:hover {
            transform: translateY(-2px);
        }

        .btn-edit {
            background: var(--orange-subtle);
            color: var(--orange-primary);
        }

        .btn-edit:hover {
            background: var(--orange-primary);
            color: white;
        }

        .btn-toggle {
            background: var(--badge-active-bg);
            color: var(--badge-active-text);
        }

        .btn-toggle:hover {
            background: var(--badge-inactive-bg);
            color: var(--badge-inactive-text);
        }

        .btn-delete {
            background: #fce4ec;
            color: #c62828;
        }

        .btn-delete:hover {
            background: #c62828;
            color: white;
        }

        body.dark .btn-delete {
            background: #3a1b1b;
            color: #ef9a9a;
        }

        body.dark .btn-delete:hover {
            background: #c62828;
            color: white;
        }

        .no-users {
            text-align: center;
            padding: 60px 20px;
            color: var(--secondary);
        }

        .no-users .icon {
            font-size: 60px;
            margin-bottom: 15px;
            display: block;
        }

        .no-users h3 {
            font-size: 20px;
            margin-bottom: 8px;
            color: var(--text);
        }

        /* Modal Styles - Matching Main Theme */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(8px);
            z-index: 9999;
            justify-content: center;
            align-items: center;
            padding: 20px;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal {
            background: var(--card);
            color: var(--text);
            border-radius: 28px;
            max-width: 950px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            padding: 40px;
            border: 2px solid var(--card-border);
            box-shadow: 0 20px 60px var(--orange-shadow), 0 30px 80px rgba(0, 0, 0, 0.4);
            animation: modalSlideIn 0.35s ease;
            position: relative;
            transition: all .35s ease;
        }

        .modal:hover {
            box-shadow: 0 25px 70px var(--orange-shadow), 0 35px 90px rgba(0, 0, 0, 0.4);
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-40px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 18px;
            border-bottom: 2px solid var(--card-border);
        }

        .modal-header h2 {
            color: var(--orange-primary);
            font-size: 26px;
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .modal-header h2::before {
            content: '✏️';
            font-size: 28px;
        }

        .modal-close {
            background: var(--orange-subtle);
            border: none;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            font-size: 24px;
            cursor: pointer;
            color: var(--secondary);
            transition: all .35s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-close:hover {
            background: var(--orange-primary);
            color: white;
            transform: rotate(90deg) scale(1.1);
            box-shadow: 0 8px 20px var(--orange-shadow);
        }

        /* Avatar Section in Modal */
        .modal .avatar-section {
            display: flex;
            align-items: center;
            gap: 25px;
            padding: 20px 25px;
            background: var(--orange-subtle);
            border-radius: 16px;
            border: 2px solid var(--card-border);
            margin-bottom: 25px;
            flex-wrap: wrap;
        }

        .modal .avatar-section .avatar-display {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .modal .avatar-section .avatar-display img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--orange-primary);
            box-shadow: 0 4px 15px var(--orange-shadow);
        }

        .modal .avatar-section .avatar-display .avatar-placeholder {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: var(--orange-subtle);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            font-weight: 700;
            color: var(--orange-primary);
            border: 3px solid var(--orange-primary);
        }

        .modal .avatar-section .avatar-info p {
            margin: 0;
            font-size: 13px;
            color: var(--secondary);
        }

        .modal .avatar-section .avatar-info .label {
            font-weight: 600;
            color: var(--text);
        }

        .modal .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 18px;
        }

        .modal .form-group {
            margin-bottom: 10px;
        }

        .modal .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            font-size: 13px;
            color: var(--text);
            letter-spacing: 0.3px;
        }

        .modal .form-group label .required {
            color: #ef4444;
            margin-left: 3px;
        }

        .modal .form-group input,
        .modal .form-group select,
        .modal .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid var(--input-border);
            border-radius: 12px;
            background: var(--input-bg);
            color: var(--text);
            transition: all .35s ease;
            font-size: 14px;
            font-family: 'Segoe UI', sans-serif;
        }

        .modal .form-group input:focus,
        .modal .form-group select:focus,
        .modal .form-group textarea:focus {
            outline: none;
            border-color: var(--orange-primary);
            box-shadow: 0 0 0 4px var(--orange-subtle);
            background: var(--card);
        }

        .modal .form-group input::placeholder,
        .modal .form-group textarea::placeholder {
            color: var(--secondary);
            opacity: 0.6;
        }

        .modal .form-group select option {
            background: var(--card);
            color: var(--text);
        }

        .modal .form-group textarea {
            resize: vertical;
            min-height: 50px;
        }

        /* Upload Area in Modal */
        .modal .upload-area {
            border: 3px dashed var(--orange-primary);
            border-radius: 16px;
            padding: 25px;
            text-align: center;
            background: var(--orange-subtle);
            cursor: pointer;
            transition: all .35s ease;
            position: relative;
            overflow: hidden;
        }

        .modal .upload-area:hover {
            background: rgba(255, 152, 0, 0.12);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px var(--orange-shadow);
        }

        .modal .upload-area.dragover {
            background: rgba(255, 152, 0, 0.18);
            border-color: var(--orange-dark);
            transform: scale(1.02);
            box-shadow: 0 0 30px var(--orange-shadow);
        }

        .modal .upload-area .upload-icon {
            font-size: 36px;
            margin-bottom: 5px;
            display: block;
        }

        .modal .upload-area .upload-content h4 {
            color: var(--text);
            font-weight: 600;
            margin: 5px 0;
            font-size: 15px;
        }

        .modal .upload-area .upload-content p {
            color: var(--secondary);
            margin: 3px 0;
            font-size: 13px;
        }

        .modal .upload-area .browse-btn {
            margin-top: 8px;
            padding: 10px 24px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--orange-gradient-start), var(--orange-primary));
            color: white;
            cursor: pointer;
            box-shadow: 0 4px 12px var(--orange-shadow);
            transition: all .35s ease;
            font-weight: 600;
            font-size: 13px;
        }

        .modal .upload-area .browse-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px var(--orange-shadow);
            background: linear-gradient(135deg, var(--orange-primary), var(--orange-dark));
        }

        .modal .upload-area #editFileName {
            display: block;
            margin-top: 10px;
            font-size: 13px;
            color: var(--orange-primary);
            font-weight: 600;
        }

        /* Button Group in Modal */
        .modal .btn-group {
            display: flex;
            gap: 15px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid var(--card-border);
        }

        .modal .btn-group .btn {
            flex: 1;
            padding: 14px 20px;
            border: none;
            border-radius: 14px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: all .35s ease;
            text-align: center;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .modal .btn-group .btn-primary {
            background: linear-gradient(135deg, var(--orange-gradient-start), var(--orange-primary));
            color: white;
            box-shadow: 0 8px 20px var(--orange-shadow);
        }

        .modal .btn-group .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px var(--orange-shadow);
            background: linear-gradient(135deg, var(--orange-primary), var(--orange-dark));
        }

        .modal .btn-group .btn-secondary {
            background: var(--card);
            color: var(--text);
            border: 2px solid var(--card-border);
        }

        .modal .btn-group .btn-secondary:hover {
            background: var(--orange-subtle);
            border-color: var(--orange-primary);
            transform: translateY(-3px);
            box-shadow: 0 8px 20px var(--orange-shadow);
        }

        .modal .btn-group .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
        }

        /* Custom Scrollbar for Modal */
        .modal::-webkit-scrollbar {
            width: 6px;
        }

        .modal::-webkit-scrollbar-track {
            background: var(--bg);
            border-radius: 10px;
        }

        .modal::-webkit-scrollbar-thumb {
            background: var(--orange-light);
            border-radius: 10px;
        }

        .modal::-webkit-scrollbar-thumb:hover {
            background: var(--orange-primary);
        }

        /* Dark mode specific modal styles */
        body.dark .modal {
            background: var(--card);
            border-color: var(--card-border);
        }

        body.dark .modal .avatar-section {
            background: var(--orange-subtle);
            border-color: var(--card-border);
        }

        body.dark .modal .upload-area {
            background: var(--orange-subtle);
            border-color: var(--orange-primary);
        }

        body.dark .modal .upload-area:hover {
            background: rgba(255, 152, 0, 0.15);
        }

        body.dark .modal .upload-area.dragover {
            background: rgba(255, 152, 0, 0.2);
        }

        body.dark .modal .btn-group .btn-secondary {
            background: var(--card);
            color: var(--text);
            border-color: var(--card-border);
        }

        body.dark .modal .btn-group .btn-secondary:hover {
            background: var(--orange-subtle);
            border-color: var(--orange-primary);
        }

        body.dark .modal .modal-close {
            background: var(--orange-subtle);
            color: var(--secondary);
        }

        body.dark .modal .modal-close:hover {
            background: var(--orange-primary);
            color: white;
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
            
            .container {
                padding: 30px 25px;
                border-radius: 22px;
            }
            
            .welcome-card {
                padding: 30px 25px;
            }
            
            .welcome-card h1 {
                font-size: 24px;
            }
            
            .filter-grid {
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            }
            
            .modal {
                padding: 30px;
                max-width: 90%;
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

            .container {
                padding: 25px 20px;
                border-radius: 18px;
            }

            .container:hover {
                transform: none;
            }

            .welcome-card {
                padding: 25px 20px;
                border-radius: 18px;
                margin-bottom: 20px;
            }

            .welcome-card h1 {
                font-size: 22px;
            }

            .welcome-card p {
                font-size: 14px;
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
            .btn-reset:hover {
                transform: none;
            }

            .filter-stats {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
                margin-top: 12px;
                padding-top: 12px;
            }

            .header-actions {
                flex-direction: column;
                align-items: stretch;
                gap: 12px;
                margin-bottom: 20px;
            }

            .search-box {
                max-width: 100%;
            }

            .search-box form {
                flex-wrap: wrap;
            }

            .search-box input {
                padding: 10px 14px;
                font-size: 16px;
                border-radius: 12px;
            }

            .btn-add {
                justify-content: center;
                padding: 10px 20px;
                font-size: 14px;
                border-radius: 12px;
            }

            .btn-add:hover {
                transform: none;
            }

            .table-responsive {
                border-radius: 12px;
            }

            table {
                min-width: 600px;
                font-size: 13px;
            }

            th {
                padding: 12px 14px;
                font-size: 12px;
            }

            td {
                padding: 12px 14px;
                font-size: 13px;
            }

            .user-avatar {
                width: 35px;
                height: 35px;
            }

            .user-avatar-placeholder {
                width: 35px;
                height: 35px;
                font-size: 16px;
            }

            .user-info {
                gap: 10px;
            }

            .user-name {
                font-size: 13px;
            }

            .user-email {
                font-size: 11px;
            }

            .badge {
                padding: 4px 12px;
                font-size: 11px;
            }

            .action-buttons {
                gap: 5px;
            }

            .btn-action {
                padding: 6px 10px;
                font-size: 11px;
                border-radius: 8px;
            }

            .btn-action:hover {
                transform: none;
            }

            /* Modal responsive */
            .modal {
                padding: 25px 20px;
                max-height: 95vh;
                border-radius: 20px;
                max-width: 95%;
            }

            .modal .form-row {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .modal .btn-group {
                flex-direction: column;
                gap: 10px;
            }

            .modal .btn-group .btn {
                width: 100%;
                padding: 12px 16px;
                font-size: 14px;
            }

            .modal .avatar-section {
                flex-direction: column;
                text-align: center;
                padding: 15px;
                gap: 15px;
            }

            .modal .avatar-section .avatar-display {
                flex-direction: column;
            }

            .modal-header h2 {
                font-size: 22px;
            }

            .modal .upload-area {
                padding: 18px 15px;
            }

            .modal .upload-area .upload-icon {
                font-size: 30px;
            }

            .modal .upload-area .browse-btn {
                padding: 8px 18px;
                font-size: 12px;
            }

            .modal .upload-area #editFileName {
                font-size: 12px;
            }

            .no-users {
                padding: 40px 15px;
            }

            .no-users .icon {
                font-size: 40px;
            }

            .no-users h3 {
                font-size: 18px;
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

            .container {
                padding: 18px 14px;
                border-radius: 16px;
                border-width: 1.5px;
            }

            .container:hover {
                box-shadow: 0 10px 25px var(--orange-shadow), 0 20px 50px rgba(255, 152, 0, 0.06);
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
                margin-top: 4px;
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
                font-size: 16px;
                padding: 8px 12px;
                min-height: 38px;
                border-radius: 8px;
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

            .filter-stats {
                font-size: 13px;
            }

            .filter-tag {
                font-size: 11px;
                padding: 3px 10px;
            }

            .search-box input {
                font-size: 16px;
                padding: 8px 12px;
                border-radius: 10px;
            }

            .btn-add {
                font-size: 13px;
                padding: 10px 16px;
                border-radius: 10px;
            }

            .table-responsive {
                border-radius: 10px;
                border-width: 1.5px;
            }

            table {
                min-width: 500px;
                font-size: 12px;
            }

            th {
                padding: 10px 10px;
                font-size: 11px;
                letter-spacing: 0.3px;
            }

            td {
                padding: 10px 10px;
                font-size: 12px;
            }

            .user-avatar {
                width: 30px;
                height: 30px;
                border-width: 1.5px;
            }

            .user-avatar-placeholder {
                width: 30px;
                height: 30px;
                font-size: 14px;
                border-width: 1.5px;
            }

            .user-info {
                gap: 8px;
            }

            .user-name {
                font-size: 12px;
            }

            .user-email {
                font-size: 10px;
            }

            .badge {
                padding: 3px 10px;
                font-size: 10px;
                border-radius: 14px;
            }

            .action-buttons {
                flex-direction: column;
                gap: 4px;
                align-items: stretch;
            }

            .btn-action {
                padding: 6px 10px;
                font-size: 10px;
                border-radius: 6px;
                justify-content: center;
                width: 100%;
                min-width: 60px;
            }

            /* Modal responsive */
            .modal {
                padding: 16px 14px;
                border-radius: 16px;
                max-width: 98%;
                max-height: 95vh;
            }

            .modal-header {
                margin-bottom: 16px;
                padding-bottom: 12px;
            }

            .modal-header h2 {
                font-size: 18px;
            }

            .modal-header h2::before {
                font-size: 20px;
            }

            .modal-close {
                width: 36px;
                height: 36px;
                font-size: 18px;
            }

            .modal .avatar-section {
                padding: 12px;
                border-radius: 12px;
                margin-bottom: 16px;
                gap: 10px;
            }

            .modal .avatar-section .avatar-display img,
            .modal .avatar-section .avatar-display .avatar-placeholder {
                width: 60px;
                height: 60px;
                font-size: 24px;
            }

            .modal .avatar-section .avatar-info p {
                font-size: 12px;
            }

            .modal .form-row {
                gap: 10px;
            }

            .modal .form-group label {
                font-size: 12px;
            }

            .modal .form-group input,
            .modal .form-group select,
            .modal .form-group textarea {
                padding: 8px 12px;
                font-size: 16px;
                border-radius: 8px;
            }

            .modal .form-group textarea {
                min-height: 40px;
            }

            .modal .upload-area {
                padding: 14px 12px;
                border-radius: 10px;
            }

            .modal .upload-area .upload-icon {
                font-size: 24px;
            }

            .modal .upload-area .upload-content h4 {
                font-size: 13px;
            }

            .modal .upload-area .upload-content p {
                font-size: 11px;
            }

            .modal .upload-area .browse-btn {
                padding: 6px 14px;
                font-size: 11px;
                border-radius: 8px;
            }

            .modal .upload-area #editFileName {
                font-size: 11px;
            }

            .modal .btn-group {
                gap: 8px;
                margin-top: 14px;
                padding-top: 14px;
            }

            .modal .btn-group .btn {
                padding: 10px 14px;
                font-size: 13px;
                border-radius: 10px;
            }

            .no-users {
                padding: 30px 12px;
            }

            .no-users .icon {
                font-size: 32px;
                margin-bottom: 8px;
            }

            .no-users h3 {
                font-size: 16px;
            }

            .no-users p {
                font-size: 13px;
            }

            /* Disable hover effects on mobile for performance */
            .container:hover,
            .welcome-card:hover,
            .filter-section:hover,
            .btn-filter:hover,
            .btn-reset:hover,
            .btn-add:hover,
            .btn-action:hover,
            .modal .btn-group .btn-primary:hover,
            .modal .btn-group .btn-secondary:hover,
            .modal .upload-area:hover,
            .modal .upload-area .browse-btn:hover,
            .modal-close:hover {
                transform: none !important;
            }

            .btn-action:active,
            .btn-filter:active,
            .btn-add:active,
            .modal .btn-group .btn:active {
                transform: scale(0.97);
            }
        }

        /* Very small phones */
        @media (max-width: 380px) {
            .main-content {
                padding: 8px 6px;
            }

            .container {
                padding: 14px 10px;
                border-radius: 14px;
            }

            .welcome-card {
                padding: 14px 12px;
            }

            .welcome-card h1 {
                font-size: 16px;
            }

            .welcome-card p {
                font-size: 12px;
            }

            table {
                min-width: 400px;
                font-size: 11px;
            }

            th {
                padding: 8px 6px;
                font-size: 10px;
            }

            td {
                padding: 8px 6px;
                font-size: 11px;
            }

            .badge {
                font-size: 9px;
                padding: 2px 8px;
            }

            .btn-action {
                font-size: 9px;
                padding: 4px 8px;
                min-width: 50px;
            }

            .modal {
                padding: 12px 10px;
            }

            .modal-header h2 {
                font-size: 16px;
            }

            .modal .form-group input,
            .modal .form-group select,
            .modal .form-group textarea {
                font-size: 16px;
                padding: 6px 10px;
            }

            .modal .btn-group .btn {
                font-size: 12px;
                padding: 8px 12px;
            }

            .modal .avatar-section .avatar-display img,
            .modal .avatar-section .avatar-display .avatar-placeholder {
                width: 50px;
                height: 50px;
                font-size: 20px;
            }
        }

        /* Landscape mode on phones */
        @media (max-height: 500px) and (orientation: landscape) {
            .main-content {
                padding: 10px 15px;
            }

            .container {
                padding: 20px;
            }

            .welcome-card {
                padding: 15px 20px;
                margin-bottom: 15px;
            }

            .welcome-card h1 {
                font-size: 18px;
            }

            .welcome-card p {
                font-size: 13px;
            }

            .filter-grid {
                grid-template-columns: 1fr 1fr 1fr;
                gap: 10px;
            }

            .filter-actions {
                flex-direction: row;
            }

            .header-actions {
                flex-direction: row;
                gap: 10px;
            }

            .search-box {
                max-width: 200px;
            }

            .btn-add {
                padding: 8px 16px;
                font-size: 12px;
            }

            table {
                font-size: 11px;
                min-width: 500px;
            }

            th {
                padding: 8px 10px;
                font-size: 10px;
            }

            td {
                padding: 8px 10px;
                font-size: 11px;
            }

            .modal {
                max-height: 95vh;
                padding: 20px;
            }

            .modal .form-row {
                grid-template-columns: 1fr 1fr;
                gap: 10px;
            }

            .modal .btn-group {
                flex-direction: row;
            }

            .modal .btn-group .btn {
                padding: 10px 16px;
                font-size: 13px;
            }

            .action-buttons {
                flex-direction: row;
            }

            .btn-action {
                padding: 4px 10px;
                font-size: 10px;
            }
        }

        /* Touch-friendly improvements for mobile */
        @media (pointer: coarse) {
            input,
            textarea,
            select,
            .btn-filter,
            .btn-reset,
            .btn-add,
            .btn-action,
            .modal .btn-group .btn,
            .modal .upload-area,
            .modal-close {
                touch-action: manipulation;
                -webkit-tap-highlight-color: transparent;
            }

            input,
            textarea,
            select {
                font-size: 16px !important;
            }

            .btn-filter,
            .btn-reset,
            .btn-add,
            .btn-action,
            .modal .btn-group .btn {
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
            
            .container {
                overflow-x: hidden;
            }
            
            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
        }
    </style>

</head>

<body>
    <div class="main-content" id="mainContent">
        <div class="container">

            <div class="welcome-card">
                <h1>👥 Manage Users</h1>
                <p>View, manage, and control user accounts.</p>
            </div>

            <!-- Filter Section -->
            <div class="filter-section">
                <div class="filter-title">
                    <span>🔍</span> Filter Users
                </div>
                
                <form method="GET" action="">
                    <div class="filter-grid">
                        <div class="filter-group">
                            <label for="filter_role">Role</label>
                            <select id="filter_role" name="role">
                                <option value="">All Roles</option>
                                <option value="Admin" <?php echo ($filter_role == 'Admin') ? 'selected' : ''; ?>>Admin</option>
                                <option value="User" <?php echo ($filter_role == 'User') ? 'selected' : ''; ?>>User</option>
                                <option value="Manager" <?php echo ($filter_role == 'Manager') ? 'selected' : ''; ?>>Manager</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label for="filter_status">Status</label>
                            <select id="filter_status" name="status">
                                <option value="">All Status</option>
                                <option value="active" <?php echo ($filter_status == 'active') ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo ($filter_status == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label for="filter_city">City</label>
                            <select id="filter_city" name="city">
                                <option value="">All Cities</option>
                                <?php foreach ($cities as $city): ?>
                                    <option value="<?php echo htmlspecialchars($city); ?>" 
                                        <?php echo ($filter_city == $city) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($city); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="filter-actions">
                            <button type="submit" class="btn-filter">Apply Filters</button>
                            <a href="manage_user.php" class="btn-reset">Reset</a>
                        </div>
                    </div>

                    <!-- Active Filters Display -->
                    <?php if (!empty($filter_role) || !empty($filter_status) || !empty($filter_city) || !empty($search_query)): ?>
                        <div class="filter-stats">
                            <span class="stat-item">
                                Found <strong><?php echo count($users); ?></strong> user(s)
                            </span>
                            <div class="active-filters">
                                <?php if (!empty($search_query)): ?>
                                    <span class="filter-tag">
                                        Search: "<?php echo htmlspecialchars($search_query); ?>"
                                        <a href="manage_user.php<?php echo getFilterQueryStringWithout('search'); ?>" 
                                           class="remove-filter">✕</a>
                                    </span>
                                <?php endif; ?>
                                <?php if (!empty($filter_role)): ?>
                                    <span class="filter-tag">
                                        Role: <?php echo htmlspecialchars($filter_role); ?>
                                        <a href="manage_user.php<?php echo getFilterQueryStringWithout('role'); ?>" 
                                           class="remove-filter">✕</a>
                                    </span>
                                <?php endif; ?>
                                <?php if (!empty($filter_status)): ?>
                                    <span class="filter-tag">
                                        Status: <?php echo ucfirst(htmlspecialchars($filter_status)); ?>
                                        <a href="manage_user.php<?php echo getFilterQueryStringWithout('status'); ?>" 
                                           class="remove-filter">✕</a>
                                    </span>
                                <?php endif; ?>
                                <?php if (!empty($filter_city)): ?>
                                    <span class="filter-tag">
                                        City: <?php echo htmlspecialchars($filter_city); ?>
                                        <a href="manage_user.php<?php echo getFilterQueryStringWithout('city'); ?>" 
                                           class="remove-filter">✕</a>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Search and Add Section -->
            <div class="header-actions">
                <div class="search-box">
                    <form method="GET" action="">
                        <input type="text" 
                               id="searchInput" 
                               name="search" 
                               placeholder="🔍 Search users by name, email, or ID..."
                               value="<?php echo htmlspecialchars($search_query); ?>">
                        <?php if (!empty($filter_role)): ?>
                            <input type="hidden" name="role" value="<?php echo htmlspecialchars($filter_role); ?>">
                        <?php endif; ?>
                        <?php if (!empty($filter_status)): ?>
                            <input type="hidden" name="status" value="<?php echo htmlspecialchars($filter_status); ?>">
                        <?php endif; ?>
                        <?php if (!empty($filter_city)): ?>
                            <input type="hidden" name="city" value="<?php echo htmlspecialchars($filter_city); ?>">
                        <?php endif; ?>
                        <button type="submit" class="btn-filter" style="padding: 10px 20px;">Search</button>
                    </form>
                </div>
                <a href="add_user.php" class="btn-add">
                    ➕ Add New User
                </a>
            </div>

            <!-- Users Table -->
            <div class="table-responsive">
                <table id="userTable">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Employee ID</th>
                            <th>Contact</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($users) > 0): ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>
                                        <div class="user-info">
                                            <?php if (!empty($user['profile_picture']) && file_exists($user['profile_picture'])): ?>
                                                <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" 
                                                     alt="Profile" 
                                                     class="user-avatar">
                                            <?php else: ?>
                                                <div class="user-avatar-placeholder">
                                                    <?php echo strtoupper(substr($user['name'], 0, 2)); ?>
                                                </div>
                                            <?php endif; ?>
                                            <div class="user-details">
                                                <span class="user-name"><?php echo htmlspecialchars($user['name']); ?></span>
                                                <span class="user-email"><?php echo htmlspecialchars($user['email_id']); ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['employee_id']); ?></td>
                                    <td><?php echo htmlspecialchars($user['contact_no']); ?></td>
                                    <td>
                                        <span class="badge badge-role">
                                            <?php echo htmlspecialchars($user['role']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo ($user['status'] == 'active') ? 'badge-active' : 'badge-inactive'; ?>">
                                            <?php echo ucfirst($user['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button type="button" 
                                                    class="btn-action btn-edit"
                                                    onclick="openEditModal(<?php echo htmlspecialchars(json_encode($user)); ?>)">
                                                ✏️ Edit
                                            </button>
                                            
                                            <form method="post" style="display: inline;">
                                                <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                                <input type="hidden" name="current_status" value="<?php echo $user['status']; ?>">
                                                <button type="submit" 
                                                        name="toggle_status" 
                                                        class="btn-action btn-toggle"
                                                        onclick="return confirm('Toggle status for <?php echo addslashes($user['name']); ?>?')">
                                                    <?php echo ($user['status'] == 'active') ? '🔴' : '🟢'; ?>
                                                    <?php echo ($user['status'] == 'active') ? 'Deactivate' : 'Activate'; ?>
                                                </button>
                                            </form>
                                            
                                            <form method="post" style="display: inline;" 
                                                  onsubmit="return confirm('Are you sure you want to delete <?php echo addslashes($user['name']); ?>? This action cannot be undone.')">
                                                <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                                <button type="submit" 
                                                        name="delete_user" 
                                                        class="btn-action btn-delete">
                                                    🗑️ Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6">
                                    <div class="no-users">
                                        <span class="icon">👤</span>
                                        <h3>No Users Found</h3>
                                        <p>Try adjusting your filters or add a new user.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal-overlay" id="editModal">
        <div class="modal">
            <div class="modal-header">
                <h2>Edit User</h2>
                <button class="modal-close" onclick="closeEditModal()">✕</button>
            </div>

            <form id="editForm" enctype="multipart/form-data">
                <input type="hidden" id="edit_user_id" name="user_id">
                <input type="hidden" name="update_user_ajax" value="1">

                <!-- Avatar Section -->
                <div class="avatar-section">
                    <div class="avatar-display">
                        <img id="currentAvatarImg" src="" alt="Current Profile" style="display: none;">
                        <div id="currentAvatarPlaceholder" class="avatar-placeholder" style="display: none;"></div>
                    </div>
                    <div class="avatar-info">
                        <p><span class="label">Current Profile Picture</span></p>
                        <p style="font-size: 12px; color: var(--secondary);">Upload a new image below to change</p>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Full Name <span class="required">*</span></label>
                        <input type="text" id="edit_name" name="name" placeholder="Enter full name" required>
                    </div>

                    <div class="form-group">
                        <label>Employee ID <span class="required">*</span></label>
                        <input type="text" id="edit_employee_id" name="employee_id" placeholder="Enter employee ID" required>
                    </div>

                    <div class="form-group">
                        <label>Email ID <span class="required">*</span></label>
                        <input type="email" id="edit_email_id" name="email_id" placeholder="Enter email address" required>
                    </div>

                    <div class="form-group">
                        <label>Contact Number <span class="required">*</span></label>
                        <input type="text" id="edit_contact_no" name="contact_no" placeholder="Enter contact number" required>
                    </div>

                    <div class="form-group">
                        <label>Username <span class="required">*</span></label>
                        <input type="text" id="edit_username" name="username" placeholder="Choose username" required>
                    </div>

                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" id="edit_password" name="password" placeholder="Leave blank to keep current">
                    </div>

                    <div class="form-group">
                        <label>Designation</label>
                        <input type="text" id="edit_designation" name="designation" placeholder="Enter designation">
                    </div>

                    <div class="form-group">
                        <label>Role <span class="required">*</span></label>
                        <select id="edit_role" name="role" required>
                            <option value="Admin">Admin</option>
                            <option value="User">User</option>
                            <option value="Manager">Manager</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>City</label>
                        <input type="text" id="edit_city" name="city" placeholder="Enter city">
                    </div>

                    <div class="form-group">
                        <label>Address</label>
                        <textarea id="edit_address" name="address" rows="2" placeholder="Enter address"></textarea>
                    </div>

                    <div class="form-group">
                        <label>Status <span class="required">*</span></label>
                        <select id="edit_status" name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Profile Picture</label>
                    <div class="upload-area" id="editDropZone">
                        <input type="file"
                            id="edit_profile_picture"
                            name="profile_picture"
                            hidden
                            accept="image/*">

                        <div class="upload-content">
                            <span class="upload-icon">🖼️</span>
                            <h4>Upload New Profile Picture</h4>
                            <p>Drag & drop or click to browse</p>
                            <button type="button"
                                class="browse-btn"
                                onclick="document.getElementById('edit_profile_picture').click()">
                                📎 Choose Image
                            </button>
                            <span id="editFileName">No file selected</span>
                        </div>
                    </div>
                </div>

                <div class="btn-group">
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">
                        ↩️ Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="updateBtn">
                        💾 Update User
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Theme and sidebar sync
        document.addEventListener("DOMContentLoaded", function() {
            const mainContent = document.getElementById("mainContent");
            
            // Apply sidebar state
            if (localStorage.getItem("sidebarState") === "collapsed" && mainContent) {
                mainContent.classList.add("expanded");
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
                if (e.key === 'theme') {
                    if (e.newValue === 'dark') {
                        document.body.classList.add('dark');
                    } else {
                        document.body.classList.remove('dark');
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

        // Edit Modal Functions
        function openEditModal(user) {
            // Populate form fields
            document.getElementById('edit_user_id').value = user.user_id;
            document.getElementById('edit_name').value = user.name || '';
            document.getElementById('edit_employee_id').value = user.employee_id || '';
            document.getElementById('edit_email_id').value = user.email_id || '';
            document.getElementById('edit_contact_no').value = user.contact_no || '';
            document.getElementById('edit_username').value = user.username || '';
            document.getElementById('edit_designation').value = user.designation || '';
            document.getElementById('edit_city').value = user.city || '';
            document.getElementById('edit_address').value = user.address || '';
            document.getElementById('edit_status').value = user.status || 'active';
            
            // Set role
            const roleSelect = document.getElementById('edit_role');
            if (user.role) {
                for (let option of roleSelect.options) {
                    if (option.value === user.role) {
                        option.selected = true;
                        break;
                    }
                }
            }

            // Update avatar
            const avatarImg = document.getElementById('currentAvatarImg');
            const avatarPlaceholder = document.getElementById('currentAvatarPlaceholder');
            
            if (user.profile_picture && user.profile_picture !== '' && user.profile_picture !== null) {
                // Check if file exists (try to load it)
                const img = new Image();
                img.onload = function() {
                    avatarImg.src = user.profile_picture;
                    avatarImg.style.display = 'block';
                    avatarPlaceholder.style.display = 'none';
                };
                img.onerror = function() {
                    // If image fails to load, show placeholder
                    avatarImg.style.display = 'none';
                    avatarPlaceholder.style.display = 'flex';
                    avatarPlaceholder.textContent = (user.name || 'U').substring(0, 2).toUpperCase();
                };
                img.src = user.profile_picture;
            } else {
                avatarImg.style.display = 'none';
                avatarPlaceholder.style.display = 'flex';
                avatarPlaceholder.textContent = (user.name || 'U').substring(0, 2).toUpperCase();
            }

            // Show modal
            document.getElementById('editModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.remove('active');
            document.body.style.overflow = '';
            // Reset file input
            document.getElementById('edit_profile_picture').value = '';
            document.getElementById('editFileName').textContent = 'No file selected';
        }

        // Close modal on overlay click
        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditModal();
            }
        });

        // File upload handling for edit modal
        const editDropZone = document.getElementById('editDropZone');
        const editFileInput = document.getElementById('edit_profile_picture');
        const editFileName = document.getElementById('editFileName');

        editDropZone.addEventListener('click', () => {
            editFileInput.click();
        });

        editFileInput.addEventListener('change', () => {
            if (editFileInput.files.length) {
                editFileName.textContent = '📄 ' + editFileInput.files[0].name;
            }
        });

        editDropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            editDropZone.classList.add('dragover');
        });

        editDropZone.addEventListener('dragleave', () => {
            editDropZone.classList.remove('dragover');
        });

        editDropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            editDropZone.classList.remove('dragover');
            const files = e.dataTransfer.files;
            if (files.length) {
                editFileInput.files = files;
                editFileName.textContent = '📄 ' + files[0].name;
            }
        });

        // Handle Edit Form Submission via AJAX
        document.getElementById('editForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            // Show loading state
            const submitBtn = document.getElementById('updateBtn');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '⏳ Updating...';
            submitBtn.disabled = true;

            fetch('manage_user.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ ' + data.message);
                    location.reload();
                } else {
                    alert('❌ ' + data.message);
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            })
            .catch(error => {
                alert('❌ Error: ' + error.message);
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeEditModal();
            }
        });
    </script>
</body>

</html>