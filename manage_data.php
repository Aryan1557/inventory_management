<?php
session_start();
include 'session_check.php';

$conn = mysqli_connect("localhost", "root", "ebiztech99", "inventory_management");

$delete_message = '';
$delete_status = '';

/* ADD AGENCY */
if (isset($_POST['add_agency'])) {
    mysqli_query($conn, "
        INSERT INTO agency
        (agency_name,customer_name,mob_number,alt_number,mail_id,city,state,address,feedback)
        VALUES
        (
            '{$_POST['agency_name']}',
            '{$_POST['customer_name']}',
            '{$_POST['mob_number']}',
            '{$_POST['alt_number']}',
            '{$_POST['mail_id']}',
            '{$_POST['city']}',
            '{$_POST['state']}',
            '{$_POST['address']}',
            '{$_POST['feedback']}'
        )
    ");

    $_SESSION['message'] = 'Agency added successfully!';
    $_SESSION['status'] = 'success';
    header("Location: manage_data.php");
    exit();
}

/* UPDATE AGENCY */
if (isset($_POST['update_agency'])) {
    $id = $_POST['id'];

    mysqli_query($conn, "
        UPDATE agency SET
        agency_name='{$_POST['agency_name']}',
        customer_name='{$_POST['customer_name']}',
        mob_number='{$_POST['mob_number']}',
        alt_number='{$_POST['alt_number']}',
        mail_id='{$_POST['mail_id']}',
        city='{$_POST['city']}',
        state='{$_POST['state']}',
        address='{$_POST['address']}',
        feedback='{$_POST['feedback']}'
        WHERE id='$id'
    ");

    $_SESSION['message'] = 'Agency updated successfully!';
    $_SESSION['status'] = 'success';
    header("Location: manage_data.php");
    exit();
}

/* DELETE */
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    $delete_query = mysqli_query($conn, "DELETE FROM agency WHERE id='$id'");

    if ($delete_query) {
        $_SESSION['message'] = 'Agency deleted successfully!';
        $_SESSION['status'] = 'success';
    } else {
        $_SESSION['message'] = 'Error deleting agency!';
        $_SESSION['status'] = 'error';
    }

    header("Location: manage_data.php");
    exit();
}

// Get flash messages
if (isset($_SESSION['message'])) {
    $delete_message = $_SESSION['message'];
    $delete_status = $_SESSION['status'];
    unset($_SESSION['message']);
    unset($_SESSION['status']);
}

include 'sidebar.php';

// Get distinct cities and states for filter dropdowns
$city_result = mysqli_query($conn, "SELECT DISTINCT city FROM agency WHERE city != '' ORDER BY city");
$state_result = mysqli_query($conn, "SELECT DISTINCT state FROM agency WHERE state != '' ORDER BY state");

// Build query with filters
$where_conditions = [];
$filter_city = isset($_GET['filter_city']) ? mysqli_real_escape_string($conn, $_GET['filter_city']) : '';
$filter_state = isset($_GET['filter_state']) ? mysqli_real_escape_string($conn, $_GET['filter_state']) : '';
$filter_search = isset($_GET['filter_search']) ? mysqli_real_escape_string($conn, $_GET['filter_search']) : '';

if ($filter_city) {
    $where_conditions[] = "city = '$filter_city'";
}
if ($filter_state) {
    $where_conditions[] = "state = '$filter_state'";
}
if ($filter_search) {
    $where_conditions[] = "(agency_name LIKE '%$filter_search%' OR customer_name LIKE '%$filter_search%' OR mob_number LIKE '%$filter_search%' OR mail_id LIKE '%$filter_search%')";
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Pagination Configuration
$records_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page); // Ensure page is at least 1
$offset = ($page - 1) * $records_per_page;

// Get total records count for pagination
$count_query = "SELECT COUNT(*) as total FROM agency $where_clause";
$count_result = mysqli_query($conn, $count_query);
$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_records / $records_per_page);

// Ensure current page doesn't exceed total pages
if ($page > $total_pages && $total_pages > 0) {
    $page = $total_pages;
    $offset = ($page - 1) * $records_per_page;
}

// Main query with pagination
$result = mysqli_query($conn, "
    SELECT *
    FROM agency
    $where_clause
    ORDER BY id DESC
    LIMIT $offset, $records_per_page
");

if (!$result) {
    die("Query Error: " . mysqli_error($conn));
}

$filter_active = ($filter_city || $filter_state || $filter_search);
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes, viewport-fit=cover">
    <title>Manage Data</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* ============================================ */
        /* ========== ORIGINAL CSS (KEPT INTACT) ====== */
        /* ============================================ */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
            -webkit-tap-highlight-color: transparent;
        }

        html {
            -webkit-text-size-adjust: 100%;
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
            overflow-x: hidden;
            width: 100%;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 35px;
            transition: all .4s ease;
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
            flex-wrap: wrap;
            gap: 15px;
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

        .header-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .add-btn {
            background: linear-gradient(135deg, var(--orange-gradient-start), var(--orange-primary));
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 14px;
            cursor: pointer;
            font-weight: 600;
            box-shadow: 0 8px 20px var(--orange-shadow);
            transition: all .35s ease;
            font-size: 15px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
        }

        .add-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px var(--orange-shadow);
            background: linear-gradient(135deg, var(--orange-primary), var(--orange-dark));
        }

        .add-btn:active {
            transform: translateY(0px);
        }

        /* Filter Section */
        .filter-section {
            background: var(--card);
            border-radius: 16px;
            padding: 20px 25px;
            margin-bottom: 20px;
            border: 2px solid var(--card-border);
            box-shadow: 0 4px 12px var(--orange-shadow);
            transition: all .35s ease;
        }

        .filter-section:hover {
            box-shadow: 0 8px 20px var(--orange-shadow);
        }

        .filter-row {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: flex-end;
        }

        .filter-group {
            flex: 1;
            min-width: 150px;
        }

        .filter-group label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--secondary);
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .filter-group input,
        .filter-group select {
            width: 100%;
            padding: 10px 14px;
            border: 2px solid var(--input-border);
            border-radius: 10px;
            background: var(--input-bg);
            color: var(--text);
            font-size: 14px;
            transition: all .35s ease;
            font-family: inherit;
            min-height: 44px;
        }

        .filter-group input:focus,
        .filter-group select:focus {
            outline: none;
            border-color: var(--orange-primary);
            box-shadow: 0 0 0 4px var(--orange-subtle);
            background: var(--card);
        }

        .filter-group select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%237a6a5a' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            padding-right: 35px;
        }

        .filter-actions {
            display: flex;
            gap: 8px;
            align-items: center;
            min-width: 120px;
        }

        .filter-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all .35s ease;
            font-size: 14px;
            white-space: nowrap;
        }

        .filter-apply {
            background: linear-gradient(135deg, var(--orange-gradient-start), var(--orange-primary));
            color: white;
            box-shadow: 0 4px 12px var(--orange-shadow);
        }

        .filter-apply:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px var(--orange-shadow);
            background: linear-gradient(135deg, var(--orange-primary), var(--orange-dark));
        }

        .filter-reset {
            background: var(--input-border);
            color: var(--text);
        }

        .filter-reset:hover {
            background: var(--secondary);
            color: white;
            transform: translateY(-2px);
        }

        .filter-stats {
            font-size: 14px;
            color: var(--secondary);
            padding: 10px 0 0 0;
            margin-top: 10px;
            border-top: 1px solid var(--card-border);
        }

        .filter-stats strong {
            color: var(--orange-primary);
        }

        .filter-toggle {
            display: none;
            background: var(--card);
            border: 2px solid var(--card-border);
            border-radius: 10px;
            padding: 8px 16px;
            cursor: pointer;
            color: var(--text);
            font-weight: 600;
            transition: all .3s ease;
        }

        .filter-toggle:hover {
            border-color: var(--orange-primary);
            color: var(--orange-primary);
        }

        .search-container {
            margin-bottom: 20px;
        }

        #searchInput {
            width: 100%;
            max-width: 450px;
            padding: 12px 18px;
            border: 2px solid var(--input-border);
            border-radius: 14px;
            background: var(--input-bg);
            color: var(--text);
            font-size: 15px;
            outline: none;
            transition: all .35s ease;
        }

        #searchInput:focus {
            border-color: var(--orange-primary);
            box-shadow: 0 0 0 4px var(--orange-subtle);
            background: var(--card);
        }

        #searchInput::placeholder {
            color: var(--secondary);
            opacity: 0.6;
        }

        .card {
            background: var(--card);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 10px 25px var(--orange-shadow), 0 20px 50px rgba(255, 152, 0, 0.06);
            border: 2px solid var(--card-border);
            transition: all .35s ease;
        }

        .card:hover {
            box-shadow: 0 15px 35px var(--orange-shadow), 0 25px 60px rgba(255, 152, 0, 0.08);
        }

        .table-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-gutter: stable;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 700px;
        }

        thead {
            background: linear-gradient(135deg, var(--orange-gradient-start), var(--orange-primary));
            color: white;
        }

        th {
            color: white;
            font-weight: 600;
            padding: 16px 15px;
            text-align: center;
            font-size: 14px;
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

        .action-buttons {
            display: flex;
            gap: 6px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .edit-btn,
        .delete-btn {
            text-decoration: none;
            color: white;
            padding: 8px 16px;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            transition: all .3s ease;
        }

        .edit-btn {
            background: linear-gradient(135deg, var(--orange-gradient-start), var(--orange-primary));
            box-shadow: 0 4px 12px var(--orange-shadow);
        }

        .edit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px var(--orange-shadow);
            background: linear-gradient(135deg, var(--orange-primary), var(--orange-dark));
        }

        .delete-btn {
            background: linear-gradient(135deg, #e65100, #bf360c);
            box-shadow: 0 4px 12px rgba(230, 81, 0, 0.3);
        }

        .delete-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(230, 81, 0, 0.4);
            background: linear-gradient(135deg, #bf360c, #8d2e00);
        }

        /* Pagination Styles */
        .pagination-container {
            padding: 20px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            border-top: 2px solid var(--card-border);
            background: var(--card);
            border-radius: 0 0 24px 24px;
        }

        .pagination-info {
            color: var(--secondary);
            font-size: 14px;
        }

        .pagination-info strong {
            color: var(--orange-primary);
        }

        .pagination {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
            align-items: center;
        }

        .pagination a,
        .pagination span {
            padding: 8px 14px;
            border-radius: 10px;
            text-decoration: none;
            color: var(--text);
            background: var(--input-bg);
            border: 2px solid var(--input-border);
            transition: all .3s ease;
            font-size: 14px;
            font-weight: 500;
            min-width: 40px;
            text-align: center;
        }

        .pagination a:hover {
            background: var(--orange-subtle);
            border-color: var(--orange-primary);
            color: var(--orange-primary);
            transform: translateY(-2px);
        }

        .pagination .active {
            background: linear-gradient(135deg, var(--orange-gradient-start), var(--orange-primary));
            color: white;
            border-color: var(--orange-primary);
            box-shadow: 0 4px 12px var(--orange-shadow);
        }

        .pagination .disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .pagination .disabled:hover {
            transform: none;
            background: var(--input-bg);
            border-color: var(--input-border);
            color: var(--text);
        }

        .pagination .ellipsis {
            background: transparent;
            border: none;
            cursor: default;
        }

        .pagination .ellipsis:hover {
            transform: none;
            background: transparent;
            color: var(--text);
        }

        .records-per-page {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--secondary);
            font-size: 14px;
        }

        .records-per-page select {
            padding: 6px 12px;
            border: 2px solid var(--input-border);
            border-radius: 8px;
            background: var(--input-bg);
            color: var(--text);
            font-size: 14px;
            cursor: pointer;
            transition: all .3s ease;
        }

        .records-per-page select:focus {
            outline: none;
            border-color: var(--orange-primary);
        }

        /* Toast Notification */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
        }

        .toast {
            padding: 16px 24px;
            border-radius: 12px;
            color: white;
            font-weight: 500;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 300px;
            max-width: 500px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            animation: slideIn 0.3s ease;
            cursor: pointer;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .toast.success {
            background: linear-gradient(135deg, #22c55e, #16a34a);
        }

        .toast.error {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }

        .toast.info {
            background: linear-gradient(135deg, var(--orange-gradient-start), var(--orange-primary));
        }

        .toast-close {
            margin-left: auto;
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            font-size: 18px;
            padding: 0 5px;
            opacity: 0.8;
            transition: opacity 0.2s ease;
        }

        .toast-close:hover {
            opacity: 1;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }

            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }

        /* Delete Confirmation Modal */
        .confirm-modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .8);
            justify-content: center;
            align-items: center;
            z-index: 9999;
            backdrop-filter: blur(4px);
        }

        .confirm-content {
            background: var(--card);
            padding: 35px;
            border-radius: 24px;
            text-align: center;
            max-width: 400px;
            width: 90%;
            box-shadow: 0 20px 50px var(--orange-shadow);
            border: 2px solid var(--card-border);
            animation: modalPop 0.3s ease;
        }

        @keyframes modalPop {
            from {
                transform: scale(0.9);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        .confirm-content h3 {
            margin-bottom: 12px;
            color: var(--orange-primary);
            font-size: 22px;
        }

        .confirm-content p {
            margin-bottom: 25px;
            color: var(--secondary);
            line-height: 1.6;
        }

        .confirm-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .confirm-yes,
        .confirm-no {
            padding: 10px 28px;
            border-radius: 12px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            color: white;
            transition: all .3s ease;
            font-size: 14px;
        }

        .confirm-yes {
            background: linear-gradient(135deg, #e65100, #bf360c);
            box-shadow: 0 4px 12px rgba(230, 81, 0, 0.3);
        }

        .confirm-yes:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(230, 81, 0, 0.4);
        }

        .confirm-no {
            background: #64748b;
        }

        .confirm-no:hover {
            background: #475569;
            transform: translateY(-2px);
        }

        /* MODAL */
        .modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .8);
            justify-content: center;
            align-items: center;
            z-index: 9999;
            backdrop-filter: blur(4px);
        }

        .modal-content {
            width: 90%;
            max-width: 700px;
            background: var(--card);
            padding: 35px;
            border-radius: 24px;
            border: 2px solid var(--card-border);
            box-shadow: 0 20px 50px var(--orange-shadow);
            animation: modalPop 0.3s ease;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-content h2 {
            margin-bottom: 20px;
            color: var(--orange-primary);
            font-size: 24px;
            font-weight: 700;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        input,
        textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--input-border);
            border-radius: 12px;
            background: var(--input-bg);
            color: var(--text);
            font-size: 14px;
            transition: all .35s ease;
            font-family: inherit;
        }

        input:focus,
        textarea:focus {
            outline: none;
            border-color: var(--orange-primary);
            box-shadow: 0 0 0 4px var(--orange-subtle);
            background: var(--card);
        }

        input::placeholder,
        textarea::placeholder {
            color: var(--secondary);
            opacity: 0.6;
        }

        textarea {
            grid-column: span 2;
            resize: vertical;
            min-height: 60px;
        }

        .modal-buttons {
            margin-top: 20px;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .save-btn {
            background: linear-gradient(135deg, var(--orange-gradient-start), var(--orange-primary));
            color: white;
            border: none;
            padding: 12px 28px;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 600;
            transition: all .35s ease;
            box-shadow: 0 4px 12px var(--orange-shadow);
            font-size: 15px;
        }

        .save-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px var(--orange-shadow);
            background: linear-gradient(135deg, var(--orange-primary), var(--orange-dark));
        }

        .close-btn {
            background: var(--input-border);
            color: var(--text);
            border: none;
            padding: 12px 28px;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 600;
            transition: all .35s ease;
            font-size: 15px;
        }

        .close-btn:hover {
            background: var(--secondary);
            color: white;
            transform: translateY(-2px);
        }

        a:focus-visible,
        button:focus-visible {
            outline: 2px solid var(--orange-primary);
            outline-offset: 2px;
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

        /* Empty state styling */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--secondary);
        }

        .empty-state .empty-icon {
            font-size: 60px;
            margin-bottom: 15px;
            display: block;
        }

        .empty-state h3 {
            color: var(--text);
            margin-bottom: 10px;
        }

        /* Badge for filter count */
        .filter-badge {
            display: inline-block;
            background: var(--orange-primary);
            color: white;
            border-radius: 50%;
            padding: 2px 8px;
            font-size: 12px;
            font-weight: 700;
            margin-left: 5px;
        }

        /* Loading spinner for pagination */
        .pagination-loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid var(--input-border);
            border-top: 3px solid var(--orange-primary);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
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
            
            .filter-row {
                gap: 10px;
            }
            
            .filter-group {
                min-width: 120px;
            }
        }

        /* iPads and tablets */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 85px;
                padding: 20px 15px;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }

            .page-title {
                font-size: 22px;
            }

            .header-actions {
                width: 100%;
                flex-wrap: wrap;
            }

            .add-btn {
                width: 100%;
                justify-content: center;
                padding: 12px 20px;
                font-size: 14px;
                white-space: normal;
            }

            .filter-toggle {
                display: inline-block;
                width: 100%;
                justify-content: center;
                padding: 10px 16px;
                font-size: 14px;
            }

            .filter-section {
                padding: 15px;
            }

            .filter-content {
                display: none;
            }

            .filter-content.show {
                display: block;
            }

            .filter-row {
                flex-direction: column;
                align-items: stretch;
            }

            .filter-group {
                min-width: auto;
                width: 100%;
            }

            .filter-group input,
            .filter-group select {
                font-size: 16px;
                padding: 12px 14px;
            }

            .filter-actions {
                flex-direction: column;
                width: 100%;
                gap: 8px;
            }

            .filter-actions button,
            .filter-actions a {
                width: 100%;
                justify-content: center;
                padding: 12px 20px;
                font-size: 14px;
            }

            .filter-stats {
                font-size: 13px;
            }

            .search-container {
                margin-bottom: 15px;
            }

            #searchInput {
                max-width: 100%;
                font-size: 16px;
                padding: 12px 16px;
            }

            .card {
                border-radius: 16px;
            }

            .table-wrapper {
                margin: 0;
                border-radius: 0;
            }

            table {
                min-width: 600px;
                font-size: 13px;
            }

            th {
                padding: 12px 10px;
                font-size: 12px;
            }

            td {
                padding: 10px 10px;
                font-size: 13px;
            }

            .action-buttons {
                flex-direction: column;
                gap: 4px;
            }

            .edit-btn,
            .delete-btn {
                padding: 6px 12px;
                font-size: 12px;
                width: 100%;
                min-width: 70px;
            }

            .pagination-container {
                flex-direction: column;
                align-items: stretch;
                text-align: center;
                padding: 15px;
                gap: 12px;
            }

            .pagination {
                justify-content: center;
                gap: 4px;
            }

            .pagination a,
            .pagination span {
                padding: 6px 10px;
                font-size: 12px;
                min-width: 32px;
            }

            .records-per-page {
                justify-content: center;
                font-size: 13px;
            }

            .records-per-page select {
                padding: 6px 10px;
                font-size: 13px;
            }

            .pagination-info {
                font-size: 13px;
            }

            .toast {
                min-width: auto;
                max-width: 90%;
                font-size: 14px;
                padding: 14px 18px;
            }

            .toast-container {
                right: 10px;
                top: 10px;
                left: 10px;
            }

            .modal-content {
                padding: 25px 20px;
                max-width: 95%;
                max-height: 85vh;
            }

            .modal-content h2 {
                font-size: 20px;
            }

            .form-grid {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            textarea {
                grid-column: span 1;
            }

            .form-grid input,
            .form-grid textarea {
                font-size: 16px;
                padding: 12px 14px;
            }

            .modal-buttons {
                flex-direction: column;
                gap: 8px;
            }

            .modal-buttons button {
                width: 100%;
                padding: 12px 20px;
                font-size: 15px;
            }

            .confirm-content {
                padding: 25px 20px;
                width: 95%;
            }

            .confirm-content h3 {
                font-size: 20px;
            }

            .confirm-buttons {
                flex-direction: column;
                gap: 8px;
            }

            .confirm-buttons button {
                width: 100%;
                padding: 12px 20px;
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
                width: 30px;
                height: 3px;
            }

            .add-btn {
                padding: 10px 16px;
                font-size: 13px;
                border-radius: 12px;
            }

            .filter-toggle {
                font-size: 13px;
                padding: 8px 14px;
            }

            .filter-section {
                padding: 12px;
                border-radius: 12px;
            }

            .filter-group label {
                font-size: 11px;
            }

            .filter-group input,
            .filter-group select {
                font-size: 16px;
                padding: 10px 12px;
                min-height: 40px;
                border-radius: 8px;
            }

            .filter-btn {
                font-size: 13px;
                padding: 10px 16px;
            }

            .filter-stats {
                font-size: 12px;
                padding-top: 8px;
                margin-top: 8px;
            }

            #searchInput {
                font-size: 16px;
                padding: 10px 14px;
                border-radius: 12px;
            }

            .card {
                border-radius: 14px;
                border-width: 1.5px;
            }

            table {
                min-width: 500px;
                font-size: 12px;
            }

            th {
                padding: 10px 8px;
                font-size: 11px;
                white-space: nowrap;
            }

            td {
                padding: 8px 8px;
                font-size: 12px;
            }

            .edit-btn,
            .delete-btn {
                font-size: 11px;
                padding: 5px 10px;
                border-radius: 8px;
                min-width: 60px;
            }

            .pagination-container {
                padding: 12px;
                gap: 10px;
                border-radius: 0 0 14px 14px;
            }

            .pagination-info {
                font-size: 12px;
            }

            .pagination {
                gap: 3px;
            }

            .pagination a,
            .pagination span {
                padding: 4px 8px;
                font-size: 11px;
                min-width: 28px;
                border-radius: 8px;
                border-width: 1.5px;
            }

            .records-per-page {
                font-size: 12px;
            }

            .records-per-page select {
                padding: 4px 8px;
                font-size: 12px;
            }

            .toast {
                font-size: 13px;
                padding: 12px 16px;
                min-width: auto;
            }

            .toast-close {
                font-size: 16px;
            }

            .modal-content {
                padding: 20px 15px;
                border-radius: 16px;
                max-width: 98%;
                max-height: 90vh;
            }

            .modal-content h2 {
                font-size: 18px;
                margin-bottom: 15px;
            }

            .form-grid {
                gap: 10px;
            }

            .form-grid input,
            .form-grid textarea {
                font-size: 16px;
                padding: 10px 12px;
                border-radius: 10px;
            }

            textarea {
                min-height: 50px;
            }

            .modal-buttons button {
                font-size: 14px;
                padding: 10px 16px;
                border-radius: 10px;
            }

            .save-btn,
            .close-btn {
                font-size: 14px;
                padding: 10px 16px;
            }

            .confirm-content {
                padding: 20px 16px;
                border-radius: 16px;
            }

            .confirm-content h3 {
                font-size: 18px;
            }

            .confirm-content p {
                font-size: 14px;
            }

            .confirm-buttons button {
                font-size: 14px;
                padding: 10px 16px;
            }

            .empty-state {
                padding: 40px 15px;
            }

            .empty-state .empty-icon {
                font-size: 40px;
            }

            .empty-state h3 {
                font-size: 16px;
            }

            .empty-state p {
                font-size: 13px;
            }

            /* Disable hover effects on mobile for performance */
            .add-btn:hover,
            .filter-apply:hover,
            .filter-reset:hover,
            .edit-btn:hover,
            .delete-btn:hover,
            .save-btn:hover,
            .close-btn:hover,
            .confirm-yes:hover,
            .confirm-no:hover,
            .pagination a:hover {
                transform: none;
            }

            .card:hover,
            .filter-section:hover {
                transform: none;
            }

            .add-btn:active,
            .filter-apply:active,
            .edit-btn:active,
            .delete-btn:active,
            .save-btn:active {
                transform: scale(0.97);
            }
        }

        /* Very small phones */
        @media (max-width: 380px) {
            .main-content {
                padding: 8px 6px;
            }

            .page-title {
                font-size: 18px;
            }

            .add-btn {
                font-size: 12px;
                padding: 8px 12px;
            }

            .filter-section {
                padding: 10px;
            }

            .filter-group input,
            .filter-group select {
                font-size: 16px;
                padding: 8px 10px;
                min-height: 36px;
            }

            #searchInput {
                font-size: 16px;
                padding: 8px 12px;
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
                padding: 6px 6px;
                font-size: 11px;
            }

            .edit-btn,
            .delete-btn {
                font-size: 10px;
                padding: 4px 8px;
                min-width: 50px;
            }

            .pagination a,
            .pagination span {
                padding: 3px 6px;
                font-size: 10px;
                min-width: 24px;
            }

            .modal-content {
                padding: 15px 12px;
            }

            .form-grid input,
            .form-grid textarea {
                font-size: 16px;
                padding: 8px 10px;
            }

            .modal-buttons button {
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
                margin-bottom: 10px;
                gap: 8px;
            }

            .page-title {
                font-size: 18px;
            }

            .filter-section {
                padding: 10px 15px;
                margin-bottom: 10px;
            }

            .filter-row {
                flex-direction: row;
                flex-wrap: wrap;
            }

            .filter-group {
                min-width: 100px;
                flex: 1;
            }

            .filter-group input,
            .filter-group select {
                padding: 6px 10px;
                min-height: 32px;
                font-size: 13px;
            }

            .filter-actions {
                flex-direction: row;
                min-width: auto;
            }

            .filter-btn {
                padding: 6px 12px;
                font-size: 12px;
            }

            #searchInput {
                padding: 6px 12px;
                font-size: 13px;
                margin-bottom: 10px;
            }

            .card {
                border-radius: 12px;
            }

            table {
                min-width: 500px;
            }

            th {
                padding: 6px 8px;
                font-size: 11px;
            }

            td {
                padding: 6px 8px;
                font-size: 11px;
            }

            .action-buttons {
                flex-direction: row;
            }

            .edit-btn,
            .delete-btn {
                padding: 4px 10px;
                font-size: 10px;
            }

            .pagination-container {
                padding: 10px;
            }

            .pagination a,
            .pagination span {
                padding: 4px 8px;
                font-size: 11px;
                min-width: 28px;
            }

            .modal-content {
                max-height: 95vh;
                padding: 20px;
            }

            .form-grid {
                grid-template-columns: 1fr 1fr;
                gap: 8px;
            }

            textarea {
                grid-column: span 2;
                min-height: 40px;
            }

            .form-grid input,
            .form-grid textarea {
                padding: 8px 12px;
                font-size: 14px;
            }

            .modal-buttons {
                flex-direction: row;
            }

            .modal-buttons button {
                padding: 8px 16px;
                font-size: 13px;
            }
        }

        /* Dark mode specific mobile adjustments */
        body.dark .filter-section {
            background: var(--card);
        }

        body.dark .modal-content {
            background: var(--card);
        }

        body.dark .confirm-content {
            background: var(--card);
        }

        /* Touch-friendly improvements for mobile */
        @media (pointer: coarse) {
            .add-btn,
            .filter-btn,
            .edit-btn,
            .delete-btn,
            .save-btn,
            .close-btn,
            .confirm-yes,
            .confirm-no,
            .pagination a,
            .filter-toggle {
                min-height: 44px;
                touch-action: manipulation;
            }

            .filter-group input,
            .filter-group select,
            #searchInput,
            .form-grid input,
            .form-grid textarea {
                font-size: 16px !important; /* Prevents iOS zoom */
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

    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- Delete Confirmation Modal -->
    <div class="confirm-modal" id="deleteModal">
        <div class="confirm-content">
            <h3>⚠️ Confirm Delete</h3>
            <p>Are you sure you want to delete this agency? This action cannot be undone.</p>
            <div class="confirm-buttons">
                <button class="confirm-yes" id="confirmDelete">Yes, Delete</button>
                <button class="confirm-no" onclick="closeDeleteModal()">Cancel</button>
            </div>
        </div>
    </div>

    <div class="main-content" id="mainContent">
        <div class="page-header">
            <h1 class="page-title">Manage Data</h1>
            <div class="header-actions">
                <button class="filter-toggle" onclick="toggleFilter()">🔍 Filters</button>
                <button class="add-btn" onclick="openAddModal()">➕ Add Agency</button>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <div class="filter-content" id="filterContent">
                <form method="GET" action="" id="filterForm">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label>🔍 Search</label>
                            <input type="text" 
                                   name="filter_search" 
                                   placeholder="Search by name, customer, mobile, email..."
                                   value="<?= htmlspecialchars($filter_search) ?>">
                        </div>
                        <div class="filter-group">
                            <label>🏙️ City</label>
                            <select name="filter_city">
                                <option value="">All Cities</option>
                                <?php 
                                // Reset pointer for city result
                                mysqli_data_seek($city_result, 0);
                                while($city = mysqli_fetch_assoc($city_result)): ?>
                                    <option value="<?= htmlspecialchars($city['city']) ?>" 
                                        <?= $filter_city == $city['city'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($city['city']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>📍 State</label>
                            <select name="filter_state">
                                <option value="">All States</option>
                                <?php 
                                mysqli_data_seek($state_result, 0);
                                while($state = mysqli_fetch_assoc($state_result)): ?>
                                    <option value="<?= htmlspecialchars($state['state']) ?>"
                                        <?= $filter_state == $state['state'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($state['state']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="filter-actions">
                            <button type="submit" class="filter-btn filter-apply">Apply Filters</button>
                            <a href="manage_data.php" class="filter-btn filter-reset" style="text-decoration:none;display:inline-flex;align-items:center;justify-content:center;">Reset</a>
                        </div>
                    </div>
                </form>
                <div class="filter-stats">
                    Showing <strong><?= min($total_records, ($page - 1) * $records_per_page + mysqli_num_rows($result)) ?></strong> 
                    of <strong><?= $total_records ?></strong> records 
                    <?php if($filter_active): ?>
                        <span style="color: var(--orange-primary);">(filtered)</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="search-container">
            <input
                type="text"
                id="searchInput"
                placeholder="🔍 Quick search by agency, customer, mobile, city, state..."
                onkeyup="searchTable()">
        </div>

        <div class="card">
            <div class="table-wrapper">
                <table id="agencyTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Agency</th>
                            <th>Customer</th>
                            <th>Mobile</th>
                            <th>Email</th>
                            <th>City</th>
                            <th>State</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0) { ?>
                            <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                                <tr>
                                    <td><?= $row['id']; ?></td>
                                    <td><?= htmlspecialchars($row['agency_name']); ?></td>
                                    <td><?= htmlspecialchars($row['customer_name']); ?></td>
                                    <td><?= htmlspecialchars($row['mob_number']); ?></td>
                                    <td><?= htmlspecialchars($row['mail_id']); ?></td>
                                    <td><?= htmlspecialchars($row['city']); ?></td>
                                    <td><?= htmlspecialchars($row['state']); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="edit-btn"
                                                onclick='editAgency(
                                                    "<?= $row['id']; ?>",
                                                    "<?= addslashes($row['agency_name']); ?>",
                                                    "<?= addslashes($row['customer_name']); ?>",
                                                    "<?= $row['mob_number']; ?>",
                                                    "<?= $row['alt_number']; ?>",
                                                    "<?= $row['mail_id']; ?>",
                                                    "<?= addslashes($row['city']); ?>",
                                                    "<?= addslashes($row['state']); ?>",
                                                    "<?= addslashes($row['address']); ?>",
                                                    "<?= addslashes($row['feedback']); ?>"
                                                )'>
                                                ✏️ Edit
                                            </button>

                                            <button class="delete-btn" onclick="openDeleteModal(<?= $row['id']; ?>)">
                                                🗑️ Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="8">
                                    <div class="empty-state">
                                        <span class="empty-icon">📭</span>
                                        <h3>No Records Found</h3>
                                        <p>
                                            <?php if($filter_active): ?>
                                                No agencies match your filter criteria. Try adjusting your filters.
                                            <?php else: ?>
                                                Start by adding your first agency using the "Add Agency" button above.
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_records > 0): ?>
            <div class="pagination-container">
                <div class="pagination-info">
                    Showing <strong><?= ($page - 1) * $records_per_page + 1 ?></strong> 
                    to <strong><?= min($page * $records_per_page, $total_records) ?></strong> 
                    of <strong><?= $total_records ?></strong> entries
                </div>
                
                <div class="pagination">
                    <!-- First Page -->
                    <?php if ($page > 1): ?>
                        <a href="?page=1<?= $filter_active ? '&' . http_build_query(array_filter(['filter_city' => $filter_city, 'filter_state' => $filter_state, 'filter_search' => $filter_search])) : '' ?>" title="First Page">««</a>
                    <?php else: ?>
                        <span class="disabled">««</span>
                    <?php endif; ?>
                    
                    <!-- Previous Page -->
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?><?= $filter_active ? '&' . http_build_query(array_filter(['filter_city' => $filter_city, 'filter_state' => $filter_state, 'filter_search' => $filter_search])) : '' ?>" title="Previous">‹</a>
                    <?php else: ?>
                        <span class="disabled">‹</span>
                    <?php endif; ?>
                    
                    <!-- Page Numbers -->
                    <?php
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);
                    
                    if ($start_page > 1) {
                        echo '<a href="?page=1' . ($filter_active ? '&' . http_build_query(array_filter(['filter_city' => $filter_city, 'filter_state' => $filter_state, 'filter_search' => $filter_search])) : '') . '">1</a>';
                        if ($start_page > 2) {
                            echo '<span class="ellipsis">…</span>';
                        }
                    }
                    
                    for ($i = $start_page; $i <= $end_page; $i++): 
                        $query_params = array_filter(['filter_city' => $filter_city, 'filter_state' => $filter_state, 'filter_search' => $filter_search]);
                        $url = "?page=$i" . ($filter_active ? '&' . http_build_query($query_params) : '');
                    ?>
                        <?php if ($i == $page): ?>
                            <span class="active"><?= $i ?></span>
                        <?php else: ?>
                            <a href="<?= $url ?>"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php
                    if ($end_page < $total_pages) {
                        if ($end_page < $total_pages - 1) {
                            echo '<span class="ellipsis">…</span>';
                        }
                        echo '<a href="?page=' . $total_pages . ($filter_active ? '&' . http_build_query(array_filter(['filter_city' => $filter_city, 'filter_state' => $filter_state, 'filter_search' => $filter_search])) : '') . '">' . $total_pages . '</a>';
                    }
                    ?>
                    
                    <!-- Next Page -->
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?= $page + 1 ?><?= $filter_active ? '&' . http_build_query(array_filter(['filter_city' => $filter_city, 'filter_state' => $filter_state, 'filter_search' => $filter_search])) : '' ?>" title="Next">›</a>
                    <?php else: ?>
                        <span class="disabled">›</span>
                    <?php endif; ?>
                    
                    <!-- Last Page -->
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?= $total_pages ?><?= $filter_active ? '&' . http_build_query(array_filter(['filter_city' => $filter_city, 'filter_state' => $filter_state, 'filter_search' => $filter_search])) : '' ?>" title="Last Page">»»</a>
                    <?php else: ?>
                        <span class="disabled">»»</span>
                    <?php endif; ?>
                </div>
                
                <div class="records-per-page">
                    <span>Show:</span>
                    <select onchange="window.location.href='?per_page='+this.value+'&page=1<?= $filter_active ? '&' . http_build_query(array_filter(['filter_city' => $filter_city, 'filter_state' => $filter_state, 'filter_search' => $filter_search])) : '' ?>'">
                        <option value="10" <?= $records_per_page == 10 ? 'selected' : '' ?>>10</option>
                        <option value="25" <?= $records_per_page == 25 ? 'selected' : '' ?>>25</option>
                        <option value="50" <?= $records_per_page == 50 ? 'selected' : '' ?>>50</option>
                        <option value="100" <?= $records_per_page == 100 ? 'selected' : '' ?>>100</option>
                    </select>
                    <span>per page</span>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ADD / EDIT MODAL -->
    <div class="modal" id="agencyModal">
        <div class="modal-content">
            <h2 id="modalTitle">Add Agency</h2>
            <form method="POST">
                <input type="hidden" name="id" id="id">
                <div class="form-grid">
                    <input type="text" name="agency_name" id="agency_name" placeholder="🏢 Agency Name" required>
                    <input type="text" name="customer_name" id="customer_name" placeholder="👤 Customer Name" required>
                    <input type="text" name="mob_number" id="mob_number" placeholder="📱 Mobile Number">
                    <input type="text" name="alt_number" id="alt_number" placeholder="📞 Alternate Number">
                    <input type="email" name="mail_id" id="mail_id" placeholder="📧 Email">
                    <input type="text" name="city" id="city" placeholder="🏙️ City">
                    <input type="text" name="state" id="state" placeholder="📍 State">
                    <textarea name="address" id="address" placeholder="🏠 Address" rows="2"></textarea>
                    <textarea name="feedback" id="feedback" placeholder="💬 Feedback" rows="2"></textarea>
                </div>
                <div class="modal-buttons">
                    <button type="submit" name="add_agency" id="saveBtn" class="save-btn">💾 Save</button>
                    <button type="button" class="close-btn" onclick="closeModal()">❌ Close</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Theme
        if (localStorage.getItem('theme') === 'dark') {
            document.body.classList.add('dark');
        }

        // Toast Notification System
        function showToast(message, type = 'success') {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;

            const icons = {
                success: '✅',
                error: '❌',
                info: 'ℹ️'
            };

            toast.innerHTML = `
                <span>${icons[type] || 'ℹ️'}</span>
                <span>${message}</span>
                <button class="toast-close" onclick="this.parentElement.remove()">×</button>
            `;

            container.appendChild(toast);

            // Auto remove after 3 seconds
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.style.animation = 'slideOut 0.3s ease forwards';
                    setTimeout(() => toast.remove(), 300);
                }
            }, 3000);

            // Click to dismiss
            toast.addEventListener('click', () => {
                toast.style.animation = 'slideOut 0.3s ease forwards';
                setTimeout(() => toast.remove(), 300);
            });
        }

        // Delete Confirmation
        let deleteId = null;

        function openDeleteModal(id) {
            deleteId = id;
            document.getElementById('deleteModal').style.display = 'flex';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
            deleteId = null;
        }

        // Confirm Delete
        document.getElementById('confirmDelete').addEventListener('click', function() {
            if (deleteId) {
                window.location.href = '?delete=' + deleteId;
            }
        });

        // Close modal when clicking outside
        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDeleteModal();
            }
        });

        // Add/Edit Modal Functions
        function openAddModal() {
            document.getElementById('agencyModal').style.display = 'flex';
            document.getElementById('modalTitle').innerHTML = '➕ Add Agency';
            document.querySelector('#agencyModal form').reset();
            document.getElementById('saveBtn').name = 'add_agency';
        }

        function closeModal() {
            document.getElementById('agencyModal').style.display = 'none';
        }

        function editAgency(id, agency, customer, mobile, alt, email, city, state, address, feedback) {
            document.getElementById('agencyModal').style.display = 'flex';
            document.getElementById('modalTitle').innerHTML = '✏️ Edit Agency';
            document.getElementById('id').value = id;
            document.getElementById('agency_name').value = agency;
            document.getElementById('customer_name').value = customer;
            document.getElementById('mob_number').value = mobile;
            document.getElementById('alt_number').value = alt;
            document.getElementById('mail_id').value = email;
            document.getElementById('city').value = city;
            document.getElementById('state').value = state;
            document.getElementById('address').value = address;
            document.getElementById('feedback').value = feedback;
            document.getElementById('saveBtn').name = 'update_agency';
        }

        // Close modals with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
                closeDeleteModal();
            }
        });

        // Close modals by clicking outside
        document.getElementById('agencyModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        // Show flash messages on page load
        <?php if ($delete_message): ?>
            showToast('<?= addslashes($delete_message); ?>', '<?= $delete_status; ?>');
        <?php endif; ?>

        // Toggle filter section on mobile
        function toggleFilter() {
            const content = document.getElementById('filterContent');
            content.classList.toggle('show');
        }

        function searchTable() {
            let input = document.getElementById("searchInput");
            let filter = input.value.toLowerCase();
            let table = document.getElementById("agencyTable");
            let rows = table.getElementsByTagName("tr");

            // Skip header row
            for (let i = 1; i < rows.length; i++) {
                let cells = rows[i].getElementsByTagName("td");
                let found = false;

                for (let j = 0; j < cells.length - 1; j++) { // exclude Action column
                    if (cells[j].innerText.toLowerCase().includes(filter)) {
                        found = true;
                        break;
                    }
                }

                rows[i].style.display = found ? "" : "none";
            }
        }

        // Auto-submit filter on Enter key in search field
        document.querySelector('input[name="filter_search"]').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('filterForm').submit();
            }
        });

        // Auto-submit filter on dropdown change
        document.querySelectorAll('select[name="filter_city"], select[name="filter_state"]').forEach(select => {
            select.addEventListener('change', function() {
                document.getElementById('filterForm').submit();
            });
        });

        // Preserve filters when changing records per page
        document.querySelector('.records-per-page select').addEventListener('change', function() {
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('per_page', this.value);
            currentUrl.searchParams.set('page', 1);
            window.location.href = currentUrl.toString();
        });

        // Close mobile filter when clicking apply or reset
        document.querySelector('.filter-apply').addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                document.getElementById('filterContent').classList.remove('show');
            }
        });

        document.querySelector('.filter-reset').addEventListener('click', function(e) {
            if (window.innerWidth <= 768) {
                document.getElementById('filterContent').classList.remove('show');
            }
        });
    </script>
</body>

</html>