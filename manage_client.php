<?php
session_start();

include "db_connection.php";
include 'session_check.php';

include "sidebar.php";

$notification = '';
$notification_type = '';

/*
|--------------------------------------------------------------------------
| DELETE CLIENT
|--------------------------------------------------------------------------
*/
if (isset($_GET['delete'])) {

    $delete_id = (int)$_GET['delete'];

    $delete = mysqli_query(
        $conn,
        "DELETE FROM client WHERE id = '$delete_id'"
    );

    if ($delete) {
        $notification = 'Client deleted successfully!';
        $notification_type = 'success';
    } else {
        $notification = 'Delete Failed: ' . mysqli_error($conn);
        $notification_type = 'error';
    }
}


/*
|--------------------------------------------------------------------------
| UPDATE CLIENT
|--------------------------------------------------------------------------
*/
if (isset($_POST['update_client'])) {

    $id = (int)$_POST['id'];

    $agency_name               = mysqli_real_escape_string($conn, $_POST['agency_name']);
    $owner_name                = mysqli_real_escape_string($conn, $_POST['owner_name']);
    $mobile_no                 = mysqli_real_escape_string($conn, $_POST['mobile_no']);
    $support_alt_no            = mysqli_real_escape_string($conn, $_POST['support_alt_no']);
    $address                   = mysqli_real_escape_string($conn, $_POST['address']);
    $alt_address               = mysqli_real_escape_string($conn, $_POST['alt_address']);
    $mail_id                   = mysqli_real_escape_string($conn, $_POST['mail_id']);

    $purchase_rental           = mysqli_real_escape_string($conn, $_POST['purchase_rental']);
    $only_software             = mysqli_real_escape_string($conn, $_POST['only_software']);

    $gateway_quantity          = (int)$_POST['gateway_quantity'];
    $gateway_name              = mysqli_real_escape_string($conn, $_POST['gateway_name']);
    $gateway_mac_id            = mysqli_real_escape_string($conn, $_POST['gateway_mac_id']);

    $server_quantity           = (int)$_POST['server_quantity'];
    $server_name               = mysqli_real_escape_string($conn, $_POST['server_name']);
    $server_mac_id             = mysqli_real_escape_string($conn, $_POST['server_mac_id']);

    $gateway_price             = (float)$_POST['gateway_price'];
    $server_price              = (float)$_POST['server_price'];

    $amc                       = (float)$_POST['amc'];
    $amc_expiry                = !empty($_POST['amc_expiry'])
        ? "'" . mysqli_real_escape_string($conn, $_POST['amc_expiry']) . "'"
        : "NULL";

    $payment_status            = mysqli_real_escape_string($conn, $_POST['payment_status']);

    $total_outstanding         = (float)$_POST['total_outstanding'];

    $headphones_total_count    = (int)$_POST['headphones_total_count'];
    $headphones_price          = (float)$_POST['headphones_price'];
    $unpaid_headphones_price   = (float)$_POST['unpaid_headphones_price'];

    $gst_number                = mysqli_real_escape_string($conn, $_POST['gst_number']);

    $service                   = mysqli_real_escape_string($conn, $_POST['service']);

    $sql = "
        UPDATE client SET
            agency_name='$agency_name',
            owner_name='$owner_name',
            mobile_no='$mobile_no',
            support_alt_no='$support_alt_no',
            address='$address',
            alt_address='$alt_address',
            mail_id='$mail_id',
            purchase_rental='$purchase_rental',
            only_software='$only_software',
            gateway_quantity='$gateway_quantity',
            gateway_name='$gateway_name',
            gateway_mac_id='$gateway_mac_id',
            server_quantity='$server_quantity',
            server_name='$server_name',
            server_mac_id='$server_mac_id',
            gateway_price='$gateway_price',
            server_price='$server_price',
            amc='$amc',
            amc_expiry=$amc_expiry,
            payment_status='$payment_status',
            total_outstanding='$total_outstanding',
            headphones_total_count='$headphones_total_count',
            headphones_price='$headphones_price',
            unpaid_headphones_price='$unpaid_headphones_price',
            gst_number='$gst_number',
            service='$service'
        WHERE id='$id'
    ";

    $update = mysqli_query($conn, $sql);

    if ($update) {
        $notification = 'Client updated successfully!';
        $notification_type = 'success';
    } else {
        $notification = 'Update Failed: ' . mysqli_error($conn);
        $notification_type = 'error';
    }
}


/*
|--------------------------------------------------------------------------
| SEARCH
|--------------------------------------------------------------------------
*/
$search = '';

if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
}


/*
|--------------------------------------------------------------------------
| PAGINATION
|--------------------------------------------------------------------------
*/
$limit = 10;

$page = isset($_GET['page'])
    ? max(1, (int)$_GET['page'])
    : 1;

$offset = ($page - 1) * $limit;


/*
|--------------------------------------------------------------------------
| TOTAL RECORDS
|--------------------------------------------------------------------------
*/
$search_escaped = mysqli_real_escape_string($conn, $search);

$count_sql = "
    SELECT COUNT(*) AS total
    FROM client
    WHERE
        agency_name LIKE '%$search_escaped%'
        OR owner_name LIKE '%$search_escaped%'
        OR mobile_no LIKE '%$search_escaped%'
        OR mail_id LIKE '%$search_escaped%'
        OR gst_number LIKE '%$search_escaped%'
";

$count_result = mysqli_query($conn, $count_sql);
$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_records / $limit);


/*
|--------------------------------------------------------------------------
| FETCH CLIENTS
|--------------------------------------------------------------------------
*/
$query = "
    SELECT *
    FROM client
    WHERE
        agency_name LIKE '%$search_escaped%'
        OR owner_name LIKE '%$search_escaped%'
        OR mobile_no LIKE '%$search_escaped%'
        OR mail_id LIKE '%$search_escaped%'
        OR gst_number LIKE '%$search_escaped%'
    ORDER BY id DESC
    LIMIT $offset, $limit
";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Clients</title>
    <style>
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
            
            --success: #10b981;
            --error: #ef4444;
            --warning: #f59e0b;
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

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Segoe UI", sans-serif;
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

        .card {
            background: var(--card);
            border-radius: 24px;
            padding: 30px;
            border: 2px solid var(--card-border);
            box-shadow: 0 10px 25px var(--orange-shadow), 0 20px 50px rgba(255, 152, 0, 0.06);
            transition: all .35s ease;
        }

        .card:hover {
            box-shadow: 0 15px 35px var(--orange-shadow), 0 25px 60px rgba(255, 152, 0, 0.08);
        }

        .page-title {
            color: var(--orange-primary);
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 10px;
            position: relative;
        }

        .page-title::after {
            content: '';
            display: block;
            width: 50px;
            height: 4px;
            background: linear-gradient(90deg, var(--orange-gradient-start), var(--orange-primary));
            margin-top: 5px;
            border-radius: 10px;
        }

        .page-subtitle {
            color: var(--secondary);
            margin-bottom: 25px;
            font-size: 15px;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
            margin-bottom: 25px;
        }

        .total-clients {
            color: var(--secondary);
            font-size: 15px;
        }

        .total-clients strong {
            color: var(--orange-primary);
            font-size: 18px;
        }

        .search-box {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .search-box input {
            width: 320px;
            max-width: 100%;
            padding: 14px 16px;
            border: 2px solid var(--input-border);
            border-radius: 14px;
            background: var(--input-bg);
            color: var(--text);
            outline: none;
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

        .btn {
            border: none;
            cursor: pointer;
            border-radius: 14px;
            padding: 14px 24px;
            font-weight: 600;
            transition: all .35s ease;
            color: white;
            font-size: 14px;
        }

        .btn-search {
            background: linear-gradient(135deg, var(--orange-gradient-start), var(--orange-primary));
            box-shadow: 0 8px 20px var(--orange-shadow);
        }

        .btn-search:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px var(--orange-shadow);
            background: linear-gradient(135deg, var(--orange-primary), var(--orange-dark));
        }

        .btn-update {
            background: linear-gradient(135deg, var(--orange-gradient-start), var(--orange-primary));
            box-shadow: 0 8px 20px var(--orange-shadow);
            width: 100%;
            padding: 16px;
            font-size: 16px;
        }

        .btn-update:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px var(--orange-shadow);
            background: linear-gradient(135deg, var(--orange-primary), var(--orange-dark));
        }

        .table-wrapper {
            overflow-x: auto;
            border-radius: 18px;
            margin-top: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1100px;
            background: var(--card);
        }

        thead {
            background: linear-gradient(135deg, var(--orange-gradient-start), var(--orange-primary));
        }

        th {
            color: white;
            padding: 16px;
            white-space: nowrap;
            font-weight: 600;
            font-size: 14px;
            letter-spacing: 0.5px;
        }

        td {
            padding: 14px;
            border-bottom: 1px solid var(--card-border);
            white-space: nowrap;
            color: var(--text);
            font-size: 14px;
        }

        tbody tr:nth-child(even) {
            background: var(--table-stripe);
        }

        tbody tr:hover {
            background: var(--table-hover);
            transition: background 0.2s ease;
        }

        .action-btn {
            text-decoration: none;
            color: white;
            padding: 8px 14px;
            border-radius: 10px;
            display: inline-block;
            transition: all .3s ease;
            margin: 2px;
            font-size: 12px;
            font-weight: 600;
        }

        .view-btn {
            background: linear-gradient(135deg, var(--orange-gradient-start), var(--orange-primary));
            box-shadow: 0 4px 12px var(--orange-shadow);
        }

        .view-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px var(--orange-shadow);
        }

        .edit-btn {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3);
        }

        .edit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(34, 197, 94, 0.4);
        }

        .delete-btn {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        .delete-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(239, 68, 68, 0.4);
        }

        .pagination {
            margin-top: 25px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .pagination a {
            text-decoration: none;
            padding: 12px 16px;
            border-radius: 12px;
            background: var(--card);
            color: var(--orange-primary);
            border: 2px solid var(--card-border);
            font-weight: 600;
            transition: all .3s ease;
        }

        .pagination a.active {
            background: linear-gradient(135deg, var(--orange-gradient-start), var(--orange-primary));
            color: white;
            border-color: var(--orange-primary);
            box-shadow: 0 4px 12px var(--orange-shadow);
        }

        .pagination a:hover:not(.active) {
            transform: translateY(-2px);
            background: var(--orange-subtle);
            border-color: var(--orange-primary);
        }

        .modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .75);
            z-index: 9999;
            overflow-y: auto;
            padding: 30px;
            backdrop-filter: blur(4px);
        }

        .modal-content {
            max-width: 950px;
            margin: 40px auto;
            background: var(--card);
            color: var(--text);
            border-radius: 24px;
            padding: 35px;
            position: relative;
            border: 2px solid var(--card-border);
            box-shadow: 0 20px 50px var(--orange-shadow);
            animation: modalPop 0.3s ease;
        }

        @keyframes modalPop {
            from {
                transform: scale(0.95);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        .modal-title {
            color: var(--orange-primary);
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .close-modal {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 30px;
            cursor: pointer;
            font-weight: bold;
            color: var(--secondary);
            transition: all .3s ease;
        }

        .close-modal:hover {
            color: var(--orange-primary);
            transform: rotate(90deg);
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .detail-item {
            background: var(--orange-subtle);
            padding: 14px;
            border-radius: 12px;
            border: 1px solid rgba(255, 152, 0, 0.15);
        }

        .detail-item strong {
            display: block;
            margin-bottom: 5px;
            color: var(--orange-primary);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .detail-item span {
            font-size: 14px;
            font-weight: 500;
        }

        /* Enhanced Edit Form Styles */
        .edit-form {
            width: 100%;
            margin-top: 20px;
        }

        .edit-section {
            background: var(--orange-subtle);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid rgba(255, 152, 0, 0.12);
        }

        .edit-section-title {
            color: var(--orange-primary);
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid rgba(255, 152, 0, 0.2);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .edit-section-title span {
            font-size: 18px;
        }

        .edit-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 15px;
        }

        .edit-field {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .edit-field label {
            font-size: 13px;
            font-weight: 600;
            color: var(--secondary);
            letter-spacing: 0.3px;
        }

        .edit-field label .required {
            color: #ef4444;
            margin-left: 2px;
        }

        .edit-field input,
        .edit-field select {
            padding: 12px 14px;
            border-radius: 12px;
            border: 2px solid var(--input-border);
            background: var(--input-bg);
            color: var(--text);
            outline: none;
            transition: all .35s ease;
            font-size: 14px;
            width: 100%;
        }

        .edit-field input:focus,
        .edit-field select:focus {
            border-color: var(--orange-primary);
            box-shadow: 0 0 0 4px var(--orange-subtle);
            background: var(--card);
        }

        .edit-field input::placeholder {
            color: var(--secondary);
            opacity: 0.5;
        }

        .edit-field select option {
            background: var(--card);
            color: var(--text);
        }

        .edit-field.full-width {
            grid-column: 1 / -1;
        }

        /* Toast Notification Styles */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 99999;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .toast {
            min-width: 320px;
            max-width: 450px;
            padding: 16px 20px;
            border-radius: 12px;
            color: white;
            font-weight: 500;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideInRight 0.3s ease, fadeOut 0.3s ease 3.7s forwards;
            backdrop-filter: blur(10px);
        }

        .toast-success {
            background: linear-gradient(135deg, #10b981, #059669);
            border-left: 4px solid #047857;
        }

        .toast-error {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            border-left: 4px solid #991b1b;
        }

        .toast-warning {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            border-left: 4px solid #92400e;
        }

        .toast-info {
            background: linear-gradient(135deg, var(--orange-gradient-start), var(--orange-primary));
            border-left: 4px solid var(--orange-dark);
        }

        .toast-icon {
            font-size: 24px;
            flex-shrink: 0;
        }

        .toast-message {
            flex: 1;
            font-size: 14px;
        }

        .toast-close {
            cursor: pointer;
            font-size: 20px;
            opacity: 0.7;
            transition: 0.3s;
            flex-shrink: 0;
        }

        .toast-close:hover {
            opacity: 1;
        }

        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes fadeOut {
            from {
                opacity: 1;
            }

            to {
                opacity: 0;
                transform: translateX(100%);
            }
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

        @media(max-width: 768px) {
            .main-content {
                margin-left: 85px;
                padding: 20px;
            }

            .main-content.expanded {
                margin-left: 85px;
            }

            .top-bar {
                flex-direction: column;
                align-items: stretch;
            }

            .search-box input {
                width: 100%;
            }

            .modal-content {
                padding: 20px;
                margin: 20px auto;
            }

            .detail-grid {
                grid-template-columns: 1fr;
            }

            .edit-grid {
                grid-template-columns: 1fr;
            }

            .toast {
                min-width: 280px;
                max-width: 90vw;
            }
        }

        @media(max-width: 480px) {
            .main-content {
                margin-left: 0;
                padding: 15px;
            }

            .main-content.expanded {
                margin-left: 0;
            }

            .page-title {
                font-size: 24px;
            }

            .card {
                padding: 20px;
            }

            .action-btn {
                padding: 6px 10px;
                font-size: 11px;
            }

            table {
                min-width: 800px;
            }

            th,
            td {
                padding: 10px 8px;
                font-size: 12px;
            }
        }
    </style>
</head>

<body>

    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>

    <div class="main-content" id="mainContent">
        <div class="card">
            <h2 class="page-title">🏢 Manage Clients</h2>
            <p class="page-subtitle">View, search, edit and manage all client records from one place.</p>

            <div class="top-bar">
                <form method="GET" class="search-box">
                    <input
                        type="text"
                        name="search"
                        placeholder="🔍 Search agency, owner, mobile..."
                        value="<?= htmlspecialchars($search) ?>">
                    <button class="btn btn-search">🔎 Search</button>
                </form>
                <div class="total-clients">
                    Total Clients : <strong><?= $total_records ?></strong>
                </div>
            </div>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Agency</th>
                            <th>Owner</th>
                            <th>Mobile</th>
                            <th>Email</th>
                            <th>Purchase</th>
                            <th>Gateway Qty</th>
                            <th>Server Qty</th>
                            <th>Outstanding</th>
                            <th>Service</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><strong>#<?= $row['id']; ?></strong></td>
                                <td><?= htmlspecialchars($row['agency_name']); ?></td>
                                <td><?= htmlspecialchars($row['owner_name']); ?></td>
                                <td><?= htmlspecialchars($row['mobile_no']); ?></td>
                                <td><?= htmlspecialchars($row['mail_id']); ?></td>
                                <td><?= $row['purchase_rental']; ?></td>
                                <td><?= $row['gateway_quantity']; ?></td>
                                <td><?= $row['server_quantity']; ?></td>
                                <td>₹<?= number_format($row['total_outstanding'], 2); ?></td>
                                <td><span style="color: <?= strtoupper($row['service']) == 'ON' ? '#22c55e' : '#ef4444' ?>; font-weight:600;"><?= strtoupper($row['service']); ?></span></td>
                                <td>
                                    <a href="javascript:void(0)"
                                        class="action-btn view-btn"
                                        onclick='openViewModal(<?= json_encode($row, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>
                                        👁️ View
                                    </a>
                                    <a href="javascript:void(0)"
                                        class="action-btn edit-btn"
                                        onclick='openEditModal(<?= json_encode($row, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>
                                        ✏️ Edit
                                    </a>
                                    <a href="javascript:void(0)"
                                        class="action-btn delete-btn"
                                        onclick="confirmDelete(<?= $row['id']; ?>)">
                                        🗑️ Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?search=<?= urlencode($search) ?>&page=<?= $page - 1 ?>">⬅ Previous</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?search=<?= urlencode($search) ?>&page=<?= $i ?>"
                            class="<?= ($i == $page) ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <a href="?search=<?= urlencode($search) ?>&page=<?= $page + 1 ?>">Next ➡</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- VIEW MODAL -->
    <div id="viewModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeViewModal()">&times;</span>
            <h2 class="modal-title">👁️ Client Full Details</h2>
            <div class="detail-grid" id="viewContent"></div>
        </div>
    </div>

    <!-- EDIT MODAL - Enhanced with sections and labels -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeEditModal()">&times;</span>
            <h2 class="modal-title">✏️ Edit Client</h2>
            <form method="POST" id="editForm" class="edit-form">
                <input type="hidden" name="id" id="edit_id">

                <!-- Basic Information -->
                <div class="edit-section">
                    <div class="edit-section-title"><span>👤</span> Basic Information</div>
                    <div class="edit-grid">
                        <div class="edit-field">
                            <label>Agency Name <span class="required">*</span></label>
                            <input type="text" name="agency_name" id="edit_agency_name" placeholder="Enter agency name" required>
                        </div>
                        <div class="edit-field">
                            <label>Owner Name <span class="required">*</span></label>
                            <input type="text" name="owner_name" id="edit_owner_name" placeholder="Enter owner name" required>
                        </div>
                        <div class="edit-field">
                            <label>Mobile No <span class="required">*</span></label>
                            <input type="text" name="mobile_no" id="edit_mobile_no" placeholder="Enter mobile number" required>
                        </div>
                        <div class="edit-field">
                            <label>Support Alt No</label>
                            <input type="text" name="support_alt_no" id="edit_support_alt_no" placeholder="Enter alternative number">
                        </div>
                        <div class="edit-field full-width">
                            <label>Address</label>
                            <input type="text" name="address" id="edit_address" placeholder="Enter address">
                        </div>
                        <div class="edit-field full-width">
                            <label>Alternative Address</label>
                            <input type="text" name="alt_address" id="edit_alt_address" placeholder="Enter alternative address">
                        </div>
                        <div class="edit-field">
                            <label>Email</label>
                            <input type="email" name="mail_id" id="edit_mail_id" placeholder="Enter email address">
                        </div>
                        <div class="edit-field">
                            <label>GST Number</label>
                            <input type="text" name="gst_number" id="edit_gst_number" placeholder="Enter GST number">
                        </div>
                    </div>
                </div>

                <!-- Purchase & Software Details -->
                <div class="edit-section">
                    <div class="edit-section-title"><span>🛒</span> Purchase & Software Details</div>
                    <div class="edit-grid">
                        <div class="edit-field">
                            <label>Purchase Type</label>
                            <select name="purchase_rental" id="edit_purchase_rental">
                                <option value="Purchase">Purchase</option>
                                <option value="Rental">Rental</option>
                            </select>
                        </div>
                        <div class="edit-field">
                            <label>Only Software</label>
                            <select name="only_software" id="edit_only_software">
                                <option value="No">No</option>
                                <option value="Yes">Yes</option>
                            </select>
                        </div>
                        <div class="edit-field">
                            <label>Service Status</label>
                            <select name="service" id="edit_service">
                                <option value="on">On</option>
                                <option value="off">Off</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Gateway Details -->
                <div class="edit-section">
                    <div class="edit-section-title"><span>🌐</span> Gateway Details</div>
                    <div class="edit-grid">
                        <div class="edit-field">
                            <label>Gateway Quantity</label>
                            <input type="number" name="gateway_quantity" id="edit_gateway_quantity" placeholder="0" min="0">
                        </div>
                        <div class="edit-field">
                            <label>Gateway Name</label>
                            <select name="gateway_name" id="edit_gateway_name">
                                <option value="">Select Gateway</option>
                                <option value="OpenVox">OpenVox</option>
                                <option value="Dinstar">Dinstar</option>
                            </select>
                        </div>
                        <div class="edit-field">
                            <label>Gateway MAC ID</label>
                            <input type="text" name="gateway_mac_id" id="edit_gateway_mac_id" placeholder="Enter MAC address">
                        </div>
                        <div class="edit-field">
                            <label>Gateway Price (₹)</label>
                            <input type="number" step="0.01" name="gateway_price" id="edit_gateway_price" placeholder="0.00" min="0">
                        </div>
                    </div>
                </div>

                <!-- Server Details -->
                <div class="edit-section">
                    <div class="edit-section-title"><span>🖥️</span> Server Details</div>
                    <div class="edit-grid">
                        <div class="edit-field">
                            <label>Server Quantity</label>
                            <input type="number" name="server_quantity" id="edit_server_quantity" placeholder="0" min="0">
                        </div>
                        <div class="edit-field">
                            <label>Server Name</label>
                            <input type="text" name="server_name" id="edit_server_name" placeholder="Enter server name">
                        </div>
                        <div class="edit-field">
                            <label>Server MAC ID</label>
                            <input type="text" name="server_mac_id" id="edit_server_mac_id" placeholder="Enter MAC address">
                        </div>
                        <div class="edit-field">
                            <label>Server Price (₹)</label>
                            <input type="number" step="0.01" name="server_price" id="edit_server_price" placeholder="0.00" min="0">
                        </div>
                    </div>
                </div>

                <!-- AMC & Payment -->
                <div class="edit-section">
                    <div class="edit-section-title"><span>💰</span> AMC & Payment Details</div>
                    <div class="edit-grid">
                        <div class="edit-field">
                            <label>AMC Amount (₹)</label>
                            <input type="number" step="0.01" name="amc" id="edit_amc" placeholder="0.00" min="0">
                        </div>
                        <div class="edit-field">
                            <label>AMC Expiry Date</label>
                            <input type="date" name="amc_expiry" id="edit_amc_expiry">
                        </div>
                        <div class="edit-field">
                            <label>Payment Status</label>
                            <select name="payment_status" id="edit_payment_status">
                                <option value="Paid">Paid</option>
                                <option value="Unpaid">Unpaid</option>
                            </select>
                        </div>
                        <div class="edit-field">
                            <label>Total Outstanding (₹)</label>
                            <input type="number" step="0.01" name="total_outstanding" id="edit_total_outstanding" placeholder="0.00" min="0">
                        </div>
                    </div>
                </div>

                <!-- Headphones Details -->
                <div class="edit-section">
                    <div class="edit-section-title"><span>🎧</span> Headphones Details</div>
                    <div class="edit-grid">
                        <div class="edit-field">
                            <label>Total Count</label>
                            <input type="number" name="headphones_total_count" id="edit_headphones_total_count" placeholder="0" min="0">
                        </div>
                        <div class="edit-field">
                            <label>Price (₹)</label>
                            <input type="number" step="0.01" name="headphones_price" id="edit_headphones_price" placeholder="0.00" min="0">
                        </div>
                        <div class="edit-field">
                            <label>Unpaid Price (₹)</label>
                            <input type="number" step="0.01" name="unpaid_headphones_price" id="edit_unpaid_headphones_price" placeholder="0.00" min="0">
                        </div>
                    </div>
                </div>

                <button type="submit" name="update_client" class="btn btn-update">
                    💾 Update Client
                </button>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content" style="max-width: 450px; text-align: center;">
            <span class="close-modal" onclick="closeDeleteModal()">&times;</span>
            <div style="font-size: 60px; margin: 20px 0;">⚠️</div>
            <h2 style="color: var(--orange-primary); margin-bottom: 15px; font-size: 24px;">Confirm Delete</h2>
            <p style="color: var(--secondary); margin-bottom: 25px; font-size: 15px;">Are you sure you want to delete this client? This action cannot be undone.</p>
            <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                <button onclick="closeDeleteModal()" class="btn" style="background: #64748b;">Cancel</button>
                <a href="#" id="confirmDeleteBtn" class="btn" style="background: linear-gradient(135deg, #ef4444, #dc2626); text-decoration: none; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);">🗑️ Delete Client</a>
            </div>
        </div>
    </div>

    <script>
        // ========== TOAST NOTIFICATION SYSTEM ==========
        function showToast(message, type = 'success') {
            const container = document.getElementById('toastContainer');

            const icons = {
                success: '✅',
                error: '❌',
                warning: '⚠️',
                info: 'ℹ️'
            };

            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.innerHTML = `
                <span class="toast-icon">${icons[type] || icons.info}</span>
                <span class="toast-message">${message}</span>
                <span class="toast-close" onclick="this.parentElement.remove()">✕</span>
            `;

            container.appendChild(toast);

            // Auto remove after 4 seconds
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.remove();
                }
            }, 4000);
        }

        // ========== DELETE CONFIRMATION ==========
        function confirmDelete(clientId) {
            document.getElementById('confirmDeleteBtn').href = `manage_client.php?delete=${clientId}`;
            document.getElementById('deleteModal').style.display = 'block';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        // ========== VIEW MODAL ==========
        function openViewModal(data) {
            const container = document.getElementById("viewContent");
            container.innerHTML = `
                <div class="detail-item"><strong>ID</strong> <span>${data.id}</span></div>
                <div class="detail-item"><strong>Agency</strong> <span>${data.agency_name || '-'}</span></div>
                <div class="detail-item"><strong>Owner</strong> <span>${data.owner_name || '-'}</span></div>
                <div class="detail-item"><strong>Mobile</strong> <span>${data.mobile_no || '-'}</span></div>
                <div class="detail-item"><strong>Alt Mobile</strong> <span>${data.support_alt_no || '-'}</span></div>
                <div class="detail-item"><strong>Email</strong> <span>${data.mail_id || '-'}</span></div>
                <div class="detail-item"><strong>Address</strong> <span>${data.address || '-'}</span></div>
                <div class="detail-item"><strong>Alt Address</strong> <span>${data.alt_address || '-'}</span></div>
                <div class="detail-item"><strong>Purchase Type</strong> <span>${data.purchase_rental}</span></div>
                <div class="detail-item"><strong>Only Software</strong> <span>${data.only_software}</span></div>
                <div class="detail-item"><strong>Gateway Qty</strong> <span>${data.gateway_quantity}</span></div>
                <div class="detail-item"><strong>Gateway Name</strong> <span>${data.gateway_name || '-'}</span></div>
                <div class="detail-item"><strong>Gateway MAC</strong> <span>${data.gateway_mac_id || '-'}</span></div>
                <div class="detail-item"><strong>Gateway Price</strong> <span>₹${data.gateway_price}</span></div>
                <div class="detail-item"><strong>Server Qty</strong> <span>${data.server_quantity}</span></div>
                <div class="detail-item"><strong>Server Name</strong> <span>${data.server_name || '-'}</span></div>
                <div class="detail-item"><strong>Server MAC</strong> <span>${data.server_mac_id || '-'}</span></div>
                <div class="detail-item"><strong>Server Price</strong> <span>₹${data.server_price}</span></div>
                <div class="detail-item"><strong>AMC</strong> <span>₹${data.amc}</span></div>
                <div class="detail-item"><strong>AMC Expiry</strong> <span>${data.amc_expiry || '-'}</span></div>
                <div class="detail-item"><strong>Payment Status</strong> <span>${data.payment_status}</span></div>
                <div class="detail-item"><strong>Total Outstanding</strong> <span>₹${data.total_outstanding}</span></div>
                <div class="detail-item"><strong>Headphones Count</strong> <span>${data.headphones_total_count}</span></div>
                <div class="detail-item"><strong>Headphones Price</strong> <span>₹${data.headphones_price}</span></div>
                <div class="detail-item"><strong>Unpaid Headphones</strong> <span>₹${data.unpaid_headphones_price}</span></div>
                <div class="detail-item"><strong>GST</strong> <span>${data.gst_number || '-'}</span></div>
                <div class="detail-item"><strong>Service</strong> <span>${data.service}</span></div>
            `;
            document.getElementById("viewModal").style.display = "block";
        }

        function closeViewModal() {
            document.getElementById("viewModal").style.display = "none";
        }

        // ========== EDIT MODAL - Enhanced ==========
        function openEditModal(data) {
            // Basic Information
            document.getElementById("edit_id").value = data.id;
            document.getElementById("edit_agency_name").value = data.agency_name || '';
            document.getElementById("edit_owner_name").value = data.owner_name || '';
            document.getElementById("edit_mobile_no").value = data.mobile_no || '';
            document.getElementById("edit_support_alt_no").value = data.support_alt_no || '';
            document.getElementById("edit_address").value = data.address || '';
            document.getElementById("edit_alt_address").value = data.alt_address || '';
            document.getElementById("edit_mail_id").value = data.mail_id || '';
            document.getElementById("edit_gst_number").value = data.gst_number || '';

            // Purchase & Software
            document.getElementById("edit_purchase_rental").value = data.purchase_rental || 'Purchase';
            document.getElementById("edit_only_software").value = data.only_software || 'No';
            document.getElementById("edit_service").value = data.service || 'off';

            // Gateway Details
            document.getElementById("edit_gateway_quantity").value = data.gateway_quantity || 0;
            document.getElementById("edit_gateway_name").value = data.gateway_name || '';
            document.getElementById("edit_gateway_mac_id").value = data.gateway_mac_id || '';
            document.getElementById("edit_gateway_price").value = data.gateway_price || 0;

            // Server Details
            document.getElementById("edit_server_quantity").value = data.server_quantity || 0;
            document.getElementById("edit_server_name").value = data.server_name || '';
            document.getElementById("edit_server_mac_id").value = data.server_mac_id || '';
            document.getElementById("edit_server_price").value = data.server_price || 0;

            // AMC & Payment
            document.getElementById("edit_amc").value = data.amc || 0;
            document.getElementById("edit_amc_expiry").value = data.amc_expiry || '';
            document.getElementById("edit_payment_status").value = data.payment_status || 'Unpaid';
            document.getElementById("edit_total_outstanding").value = data.total_outstanding || 0;

            // Headphones
            document.getElementById("edit_headphones_total_count").value = data.headphones_total_count || 0;
            document.getElementById("edit_headphones_price").value = data.headphones_price || 0;
            document.getElementById("edit_unpaid_headphones_price").value = data.unpaid_headphones_price || 0;

            document.getElementById("editModal").style.display = "block";
        }

        function closeEditModal() {
            document.getElementById("editModal").style.display = "none";
        }

        // ========== CLOSE MODALS ON OUTSIDE CLICK ==========
        window.addEventListener("click", function(e) {
            if (e.target === document.getElementById("viewModal")) {
                closeViewModal();
            }
            if (e.target === document.getElementById("editModal")) {
                closeEditModal();
            }
            if (e.target === document.getElementById("deleteModal")) {
                closeDeleteModal();
            }
        });

        // ========== CLOSE MODALS ON ESC ==========
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeViewModal();
                closeEditModal();
                closeDeleteModal();
            }
        });

        // ========== SHOW NOTIFICATION ON PAGE LOAD ==========
        <?php if ($notification): ?>
            showToast(<?= json_encode($notification) ?>, <?= json_encode($notification_type) ?>);
        <?php endif; ?>

        // ========== THEME AND SIDEBAR SYNC ==========
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
    </script>
</body>

</html>