<?php
// =============================================
// GST QUOTATION - PROFESSIONAL PHP PAGE v6
// Theme: Orange · Black · White | EbizTech
// =============================================
session_start();

include 'db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// =============================================
// HANDLE FORM SUBMISSION FOR SAVING QUOTATION
// =============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_quotation'])) {
    saveQuotationToDatabase($_POST);
}

function saveQuotationToDatabase($data)
{
    global $conn;

    // Escape all fields
    $quotation_number = mysqli_real_escape_string($conn, $data['quotation_number'] ?? '');
    $quotation_date = mysqli_real_escape_string($conn, $data['quotation_date'] ?? '');
    $reference = mysqli_real_escape_string($conn, $data['reference'] ?? '');
    $customer_id = mysqli_real_escape_string($conn, $data['customer_id'] ?? '');
    $valid_until = mysqli_real_escape_string($conn, $data['valid_until'] ?? '');
    $status = mysqli_real_escape_string($conn, $data['status'] ?? 'draft');

    $company_name = mysqli_real_escape_string($conn, $data['company_name'] ?? '');
    $company_address = mysqli_real_escape_string($conn, $data['company_address'] ?? '');
    $company_contact = mysqli_real_escape_string($conn, $data['company_contact'] ?? '');
    $company_email = mysqli_real_escape_string($conn, $data['company_email'] ?? '');
    $company_gst = mysqli_real_escape_string($conn, $data['company_gst'] ?? '');

    $customer_company = mysqli_real_escape_string($conn, $data['customer_company'] ?? '');
    $customer_contact = mysqli_real_escape_string($conn, $data['customer_contact'] ?? '');
    $customer_address = mysqli_real_escape_string($conn, $data['customer_address'] ?? '');
    $customer_gst = mysqli_real_escape_string($conn, $data['customer_gst'] ?? '');
    $customer_email = mysqli_real_escape_string($conn, $data['customer_email'] ?? '');
    $customer_phone = mysqli_real_escape_string($conn, $data['customer_phone'] ?? '');

    $subtotal = floatval($data['subtotal'] ?? 0);
    $discount = floatval($data['discount'] ?? 0);
    $taxable_value = floatval($data['taxable_value'] ?? 0);
    $gst_rate = floatval($data['gst_rate'] ?? 18);
    $gst_amount = floatval($data['gst_amount'] ?? 0);
    $other_charges = floatval($data['other_charges'] ?? 0);
    $grand_total = floatval($data['grand_total'] ?? 0);
    $grand_total_without_gst = floatval($data['grand_total_without_gst'] ?? 0);
    $show_gst = isset($data['show_gst']) ? intval($data['show_gst']) : 1;

    $bank_account_name = mysqli_real_escape_string($conn, $data['bank_account_name'] ?? '');
    $bank_account_number = mysqli_real_escape_string($conn, $data['bank_account_number'] ?? '');
    $bank_ifsc = mysqli_real_escape_string($conn, $data['bank_ifsc'] ?? '');
    $bank_name = mysqli_real_escape_string($conn, $data['bank_name'] ?? '');
    $bank_branch = mysqli_real_escape_string($conn, $data['bank_branch'] ?? '');

    $terms = mysqli_real_escape_string($conn, $data['terms'] ?? '');
    $items_json = mysqli_real_escape_string($conn, $data['items_json'] ?? '[]');

    // Check if quotation already exists
    $check_query = "SELECT id FROM quotations WHERE quotation_number = '$quotation_number'";
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        // Update existing quotation
        $query = "UPDATE quotations SET 
            quotation_date = '$quotation_date',
            reference = '$reference',
            customer_id = '$customer_id',
            valid_until = '$valid_until',
            status = '$status',
            company_name = '$company_name',
            company_address = '$company_address',
            company_contact = '$company_contact',
            company_email = '$company_email',
            company_gst = '$company_gst',
            customer_company = '$customer_company',
            customer_contact = '$customer_contact',
            customer_address = '$customer_address',
            customer_gst = '$customer_gst',
            customer_email = '$customer_email',
            customer_phone = '$customer_phone',
            subtotal = $subtotal,
            discount = $discount,
            taxable_value = $taxable_value,
            gst_rate = $gst_rate,
            gst_amount = $gst_amount,
            other_charges = $other_charges,
            grand_total = $grand_total,
            grand_total_without_gst = $grand_total_without_gst,
            show_gst = $show_gst,
            bank_account_name = '$bank_account_name',
            bank_account_number = '$bank_account_number',
            bank_ifsc = '$bank_ifsc',
            bank_name = '$bank_name',
            bank_branch = '$bank_branch',
            terms_conditions = '$terms',
            items_json = '$items_json',
            updated_at = NOW()
            WHERE quotation_number = '$quotation_number'";
    } else {
        // Insert new quotation
        $query = "INSERT INTO quotations (
            quotation_number, quotation_date, reference, customer_id, valid_until, status,
            company_name, company_address, company_contact, company_email, company_gst,
            customer_company, customer_contact, customer_address, customer_gst, customer_email, customer_phone,
            subtotal, discount, taxable_value, gst_rate, gst_amount, other_charges, grand_total,
            grand_total_without_gst, show_gst,
            bank_account_name, bank_account_number, bank_ifsc, bank_name, bank_branch,
            terms_conditions, items_json, created_at, updated_at
        ) VALUES (
            '$quotation_number', '$quotation_date', '$reference', '$customer_id', '$valid_until', '$status',
            '$company_name', '$company_address', '$company_contact', '$company_email', '$company_gst',
            '$customer_company', '$customer_contact', '$customer_address', '$customer_gst', '$customer_email', '$customer_phone',
            $subtotal, $discount, $taxable_value, $gst_rate, $gst_amount, $other_charges, $grand_total,
            $grand_total_without_gst, $show_gst,
            '$bank_account_name', '$bank_account_number', '$bank_ifsc', '$bank_name', '$bank_branch',
            '$terms', '$items_json', NOW(), NOW()
        )";
    }

    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = "Quotation #$quotation_number saved successfully!";
    } else {
        $_SESSION['error'] = "Error saving quotation: " . mysqli_error($conn);
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Company data
$company = [
    'name' => 'E BUSINESS TECHNOLOGY SOLUTIONS',
    'short' => 'EbizTech',
    'address' => 'Office No-89, D-Wing, 4th Floor, Dhankawadi, Pune- 411043.',
    'contact' => '77 55 97 97 97 / 92 70 40 97 97',
    'email' => 'info@ebiztech.in',
    'gst' => '27AAMFE3315J1ZD',
    'logo' => 'logo.jpeg',
    'website' => 'www.ebiztech.in',
];

// Bank data
$bank = [
    'account_name' => 'E BUSINESS TECHNOLOGY SOLUTIONS',
    'account_number' => '610000000062910',
    'ifsc' => 'SRCB0000038',
    'bank_name' => 'Saraswat Co-Op Bank Ltd.',
    'branch' => 'Tilak Road, Pune',
];

// Terms
$terms = [
    'This quotation is valid for <strong>15 days</strong> from the date of issue.',
    'All payments shall be made in favor of <strong>E Business Technology Solutions</strong>.',
    'A <strong>minimum lock-in period of 6 months</strong> is mandatory from the date of service activation.',
    'Prices quoted are exclusive of GST and other applicable taxes.',
    'Hardware warranty, if applicable, shall be covered by the respective manufacturer\'s warranty policy.',
    'Any additional customization, training, or support beyond the agreed scope shall be chargeable.',
    'All payments made towards setup, licensing, subscription, and support services are <strong>non-refundable</strong>.',
    'Any disputes arising from the services shall be subject to the jurisdiction of <strong>Pune, Maharashtra</strong> only.',
    'This quotation is subject to stock availability and price changes without prior notice.',
    'Acceptance of this quotation constitutes an agreement to the terms and conditions stated herein.',
];

$iso = 'ISO 9001:2015';

// Quotation data
$quotation = [
    'number' => 'Q2026/1021',
    'date' => date('d-m-Y'),
    'reference' => 'REF/2026/001',
    'customer_id' => 'PUN/106',
    'valid_until' => date('d-m-Y', strtotime('+15 days')),
];

// Bill to data
$bill_to = [
    'company' => 'Blink Finance',
    'contact' => 'Ashish Ghadge',
    'address' => 'Sai Prasad Complex, A 001/002, Mira Bhayander Link Rd, beside SAGAR SANGAM HOTEL, CHSL, Geeta Nagar, Mira Road East, THANE, Mira Bhayandar, Maharashtra 401107',
    'gst' => '',
    'email' => '',
    'phone' => '',
];

$tax_rate = 0.18;
$totalSlots = 12;

// Product rows
$rows = [
    ['product' => 'GATEWAY', 'desc' => '32 Port GSM Gateway', 'period' => '01-06-2026', 'qty' => 1, 'price' => 23000],
    ['product' => 'DIALER SERVER', 'desc' => 'Dell PowerEdge-R710, 16 Core, 16GB RAM, 1TB HDD (Refurbished) - Dialer Server', 'period' => '01-06-2026', 'qty' => 1, 'price' => 27500],
    ['product' => 'AMC', 'desc' => 'Domestic Call Center Open Source Omni-channel Contact Center Suite - Installation, Configuration & AMC', 'period' => '01-06-2026', 'qty' => 1, 'price' => 45000],
];

$show_gst = isset($_GET['gst']) ? $_GET['gst'] : 'yes';

// Display session messages
if (isset($_SESSION['success']) || isset($_SESSION['error'])) {
    $message_type = isset($_SESSION['success']) ? 'success' : 'error';
    $message_text = isset($_SESSION['success']) ? $_SESSION['success'] : $_SESSION['error'];
    $bg_color = $message_type === 'success' ? 'rgba(34,197,94,0.1)' : 'rgba(239,68,68,0.1)';
    $border_color = $message_type === 'success' ? '#22c55e' : '#ef4444';
    $text_color = $message_type === 'success' ? '#4ade80' : '#f87171';

    echo '<div style="max-width:960px;margin:10px auto;padding:15px 20px;border-radius:10px;background:' . $bg_color . ';border:1px solid ' . $border_color . ';color:' . $text_color . ';font-weight:600;text-align:center;font-family:Arial,sans-serif;">';
    echo ($message_type === 'success' ? '✅ ' : '❌ ') . htmlspecialchars($message_text);
    echo '</div>';

    if ($message_type === 'success') {
        unset($_SESSION['success']);
    } else {
        unset($_SESSION['error']);
    }
}

include 'sidebar.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quotation <?= htmlspecialchars($quotation['number']) ?> - EbizTech</title>
    <style>
        /* ================================================================
        RESET & BASE
        ================================================================ */
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0
        }

        :root {
            --orange: #f57c00;
            --orange-dark: #e65100;
            --orange-light: #ffb74d;
            --orange-gradient-start: #ff9800;
            --orange-gradient-end: #f57c00;
            --orange-shadow: rgba(245, 124, 0, 0.15);
            --black: #0a0a0a;
            --gray-dark: #cbd5e1;
            --gray: #94a3b8;
            --gray-light: #64748b;
            --border: #3a2a1a;
            --bg: #0d0805;
            --bg-secondary: #1a0e0a;
            --bg-hover: #2d1a0a;
            --bg-editable: #2a1a0a;
            --white: #1a0e0a;
            --text-primary: #f8fafc;
            --text-secondary: #cbd5e1;
            --text-muted: #94a3b8;
            --text-light: #64748b;
            --border-color: #3a2a1a;
            --border-light: #5a3a1a;
            --card-bg: #1a0e0a;
            --table-stripe: #0d0805;
            --badge-bg: #f57c00;
            --badge-text: #fff;
            --section-bg: #0d0805;
            --gst-toggle-bg: rgba(255, 152, 0, 0.08);
            --watermark-color: rgba(245, 124, 0, 0.06);
            --total-bg: #f57c00;
            --total-text: #fff;
            --iso-border: #f57c00;
            --iso-bg: rgba(255, 152, 0, 0.08);
            --meta-bg: rgba(255, 152, 0, 0.08);
            --status-bg: #f57c00;
            --scrollbar-track: #0d0805;
            --scrollbar-thumb: #f57c00;
            --scrollbar-thumb-hover: #e65100;
        }

        body.dark {
            --bg: #0a0805;
            --bg-secondary: #1a0e0a;
            --bg-hover: #2d1a0a;
            --bg-editable: #2a1a0a;
            --text-primary: #f8fafc;
            --text-secondary: #cbd5e1;
            --text-muted: #94a3b8;
            --text-light: #64748b;
            --border-color: #3a2a1a;
            --border-light: #5a3a1a;
            --card-bg: #1a0e0a;
            --table-stripe: #0d0805;
            --white: #1a0e0a;
            --orange: #ffa726;
            --orange-dark: #f57c00;
            --orange-light: #ffcc80;
            --orange-gradient-start: #ff9800;
            --orange-gradient-end: #e65100;
            --orange-shadow: rgba(255, 152, 0, 0.2);
            --watermark-color: rgba(255, 152, 0, 0.08);
            --total-bg: #ffa726;
            --total-text: #fff;
            --iso-border: #ffa726;
            --iso-bg: rgba(255, 152, 0, 0.12);
            --meta-bg: rgba(255, 152, 0, 0.12);
            --status-bg: #ffa726;
            --badge-bg: #ffa726;
            --scrollbar-track: #0a0805;
            --scrollbar-thumb: #ffa726;
            --scrollbar-thumb-hover: #f57c00;
            --gst-toggle-bg: rgba(255, 152, 0, 0.12);
        }

        body:not(.dark) {
            --bg: #f5f0eb;
            --bg-secondary: #ffffff;
            --bg-hover: #f0e8e0;
            --bg-editable: #f5f0eb;
            --text-primary: #1a1a1a;
            --text-secondary: #424242;
            --text-muted: #757575;
            --text-light: #9e9e9e;
            --border-color: #e0d6cc;
            --border-light: #d0c0b0;
            --card-bg: #ffffff;
            --table-stripe: #f5f0eb;
            --white: #ffffff;
            --orange: #e65100;
            --orange-dark: #bf360c;
            --orange-light: #ff9800;
            --orange-shadow: rgba(230, 81, 0, 0.15);
            --watermark-color: rgba(230, 81, 0, 0.06);
            --total-bg: #e65100;
            --total-text: #fff;
            --iso-border: #e65100;
            --iso-bg: rgba(230, 81, 0, 0.08);
            --meta-bg: rgba(230, 81, 0, 0.08);
            --status-bg: #e65100;
            --badge-bg: #e65100;
            --scrollbar-track: #f5f0eb;
            --scrollbar-thumb: #e65100;
            --scrollbar-thumb-hover: #bf360c;
            --gst-toggle-bg: rgba(230, 81, 0, 0.08);
            --border: #e0d6cc;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 13px;
            background: var(--bg);
            color: var(--text-primary);
            line-height: 1.5;
            margin-left: 280px;
            min-height: 100vh;
            transition: background 0.3s ease, color 0.3s ease, margin-left 0.3s ease;
        }

        body.sidebar-collapsed {
            margin-left: 85px;
        }

        .main-wrapper {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        /* ================================================================
        TOOLBAR
        ================================================================ */
        #toolbar {
            background: var(--bg-secondary);
            color: var(--text-primary);
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            position: sticky;
            top: 0;
            z-index: 200;
            box-shadow: 0 2px 10px var(--orange-shadow);
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }

        #toolbar .tb-brand {
            font-size: 14px;
            font-weight: 800;
            letter-spacing: 1px;
            color: var(--orange);
            flex: 1;
        }

        #toolbar .tb-brand span {
            color: var(--text-primary);
            font-weight: 400
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .5px;
            transition: all .18s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn:hover {
            filter: brightness(1.15);
            transform: translateY(-1px)
        }

        .btn-green {
            background: #1a9148;
            color: #fff
        }
        .btn-orange {
            background: var(--orange);
            color: #fff
        }
        .btn-col {
            background: #2d6bcf;
            color: #fff
        }
        .btn-addcol {
            background: #8338a8;
            color: #fff
        }
        .btn-print {
            background: #e07b00;
            color: #fff
        }
        .btn-save {
            background: #1c9e6e;
            color: #fff;
            font-size: 12px
        }

        /* ================================================================
        PANELS
        ================================================================ */
        .panel {
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 12px 16px;
            position: absolute;
            top: 52px;
            z-index: 300;
            min-width: 200px;
            box-shadow: 0 6px 20px var(--orange-shadow);
            display: none;
            color: var(--text-primary);
            transition: background 0.3s ease, border-color 0.3s ease;
        }

        #colPanel {
            right: 230px
        }
        #addColPanel {
            right: 10px
        }

        .panel .panel-title {
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 1px;
            color: var(--orange);
            text-transform: uppercase;
            margin-bottom: 8px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 5px;
        }

        .panel label {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 3px 0;
            cursor: pointer;
            color: var(--text-primary);
            font-size: 12.5px;
        }

        .panel input[type=text] {
            width: 100%;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            padding: 5px 8px;
            font-size: 12px;
            margin-bottom: 6px;
            background: var(--bg);
            color: var(--text-primary);
        }

        .panel .btn-sm {
            padding: 5px 12px;
            font-size: 11px;
            font-weight: 700;
            background: var(--orange);
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        /* ================================================================
        QUOTATION WRAPPER
        ================================================================ */
        #quotationWrap {
            max-width: 960px;
            margin: 0 auto 24px;
            background: var(--bg-secondary);
            position: relative;
            overflow: visible;
            box-shadow: 0 4px 30px var(--orange-shadow);
            border-radius: 8px;
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }

        .stripe-top {
            height: 7px;
            background: linear-gradient(90deg, var(--orange-dark) 0%, var(--orange) 60%, var(--orange-light) 100%);
            border-radius: 8px 8px 0 0;
            transition: background 0.3s ease;
        }

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-35deg);
            font-size: 100px;
            font-weight: 900;
            letter-spacing: 8px;
            color: var(--watermark-color);
            pointer-events: none;
            user-select: none;
            white-space: nowrap;
            z-index: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            text-transform: uppercase;
            width: 100%;
            text-align: center;
            transition: color 0.3s ease;
        }

        .quo-body {
            position: relative;
            z-index: 1;
            padding: 24px 32px 18px
        }

        /* ================================================================
        HEADER
        ================================================================ */
        .quo-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 16px;
            margin-bottom: 16px;
            border-bottom: 2.5px solid var(--orange);
            transition: border-color 0.3s ease;
        }

        .co-left {
            display: flex;
            flex-direction: column;
            max-width: 420px
        }

        .co-logo-wrap {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .co-logo {
            max-height: 100px;
            max-width: 260px;
            object-fit: contain;
            display: block;
            border-radius: 6px;
            background: #fff;
            padding: 4px;
            box-shadow: 0 2px 8px var(--orange-shadow);
            transition: 0.2s;
        }

        .co-logo-text {
            font-size: 28px;
            font-weight: 900;
            color: var(--orange);
            letter-spacing: 3px;
            margin-bottom: 8px;
            text-transform: uppercase;
            display: none;
            transition: color 0.3s ease;
        }

        .co-name {
            font-size: 15px;
            font-weight: 800;
            color: var(--text-primary);
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: .5px;
            transition: color 0.3s ease;
        }

        .co-info {
            color: var(--text-muted);
            font-size: 12px;
            line-height: 1.8;
            transition: color 0.3s ease;
        }

        .co-gst {
            color: var(--text-primary);
            font-weight: 700;
            font-size: 12px;
            margin-top: 4px;
            border-top: 1px dashed var(--border-color);
            padding-top: 4px;
            transition: all 0.3s ease;
        }

        .quo-right {
            text-align: right;
            min-width: 250px
        }

        .quo-title-word {
            font-size: 42px;
            font-weight: 900;
            letter-spacing: 5px;
            color: var(--orange);
            text-transform: uppercase;
            line-height: 1;
            margin-bottom: 6px;
            transition: color 0.3s ease;
        }

        .iso-seal {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 2.5px solid var(--iso-border);
            border-radius: 6px;
            padding: 5px 14px;
            margin-bottom: 12px;
            color: var(--orange);
            font-size: 14px;
            font-weight: 900;
            letter-spacing: 2px;
            background: var(--iso-bg);
            transition: all 0.3s ease;
        }

        .iso-cert-text {
            font-size: 9px;
            font-weight: 700;
            color: var(--text-muted);
            letter-spacing: 1px;
            text-transform: uppercase;
            display: block;
            line-height: 1;
            margin-top: 1px;
            transition: color 0.3s ease;
        }

        .meta-table {
            border-collapse: collapse;
            margin-left: auto
        }

        .meta-table td {
            padding: 3px 5px;
            font-size: 12px
        }

        .meta-table .ml {
            color: var(--text-muted);
            font-weight: 600;
            text-align: right;
            white-space: nowrap;
            transition: color 0.3s ease;
        }

        .meta-table .mv {
            font-weight: 700;
            color: var(--text-primary);
            background: var(--meta-bg);
            border-left: 3px solid var(--orange);
            padding-left: 8px;
            border-radius: 0 3px 3px 0;
            min-width: 120px;
            transition: all 0.3s ease;
        }

        .meta-table .mv[contenteditable]:focus {
            background: var(--bg-editable);
            outline: 1.5px solid var(--orange);
        }

        .quo-status {
            display: inline-block;
            background: var(--status-bg);
            color: var(--badge-text);
            font-size: 10px;
            font-weight: 900;
            letter-spacing: 2px;
            padding: 3px 14px;
            border-radius: 20px;
            text-transform: uppercase;
            margin-top: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        /* ================================================================
        BILL TO BAND
        ================================================================ */
        .bill-band {
            display: grid;
            grid-template-columns: 1fr 1fr;
            margin-bottom: 20px;
            border: 1.5px solid var(--border-color);
            border-radius: 6px;
            overflow: hidden;
            transition: border-color 0.3s ease;
        }

        .bill-section {
            padding: 12px 16px
        }

        .bill-section:first-child {
            border-right: 1.5px solid var(--border-color);
            transition: border-color 0.3s ease;
        }

        .bill-head {
            font-size: 10px;
            font-weight: 900;
            letter-spacing: 2px;
            color: #fff;
            background: var(--orange);
            text-transform: uppercase;
            padding: 5px 16px;
            margin: -12px -16px 10px;
            transition: background 0.3s ease;
        }

        .bill-company {
            font-size: 14px;
            font-weight: 800;
            color: var(--orange);
            margin-bottom: 3px;
            transition: color 0.3s ease;
        }

        .bill-info {
            font-size: 12px;
            color: var(--text-secondary);
            line-height: 1.8;
            transition: color 0.3s ease;
        }

        [contenteditable]:focus {
            background: var(--bg-editable);
            outline: 1.5px solid var(--orange);
            border-radius: 3px;
            transition: all 0.3s ease;
        }

        /* ================================================================
        PRODUCT TABLE
        ================================================================ */
        .table-wrap {
            overflow-x: auto;
            margin-bottom: 20px;
            position: relative;
            z-index: 1
        }

        #productTable {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            border-top: 2.5px solid var(--orange);
            transition: border-color 0.3s ease;
        }

        #productTable thead tr {
            background: var(--orange);
            color: #fff;
            transition: background 0.3s ease;
        }

        #productTable th {
            padding: 10px 10px;
            font-weight: 700;
            letter-spacing: .3px;
            white-space: nowrap;
            border-right: 1px solid rgba(255, 255, 255, .12);
            font-size: 11.5px;
            color: #fff !important;
            background: var(--orange) !important;
            transition: background 0.3s ease;
        }

        #productTable th:last-child {
            border-right: none
        }

        #productTable td {
            padding: 8px 10px;
            border-bottom: 1px solid var(--border-color);
            border-right: 1px solid var(--border-color);
            vertical-align: top;
            color: var(--text-primary);
            transition: all 0.3s ease;
        }

        #productTable td:last-child {
            border-right: none
        }

        #productTable tbody tr:nth-child(odd) {
            background: var(--bg-secondary);
            transition: background 0.3s ease;
        }

        #productTable tbody tr:nth-child(even) {
            background: var(--table-stripe);
            transition: background 0.3s ease;
        }

        #productTable tbody tr:hover {
            background: var(--bg-hover);
            transition: background 0.3s ease;
        }

        #productTable .td-no {
            text-align: center;
            font-weight: 700;
            color: var(--orange);
            background: var(--meta-bg) !important;
            width: 36px;
            transition: all 0.3s ease;
        }

        #productTable .td-amount {
            text-align: right;
            font-weight: 700;
            color: var(--text-primary);
            white-space: nowrap;
            transition: color 0.3s ease;
        }

        #productTable .td-qty,
        #productTable .td-price {
            text-align: right
        }

        .row-hidden {
            display: none !important
        }
        .col-hidden {
            display: none !important
        }

        .row-actions {
            text-align: center;
            white-space: nowrap;
            width: 62px
        }

        .row-actions button {
            border: none;
            background: none;
            cursor: pointer;
            font-size: 11px;
            padding: 1px 4px;
            border-radius: 3px;
            transition: background .15s;
            color: var(--text-muted);
        }

        .row-actions button:hover {
            background: var(--bg-hover);
            color: var(--text-primary);
        }

        /* ================================================================
        BOTTOM GRID: BANK + TOTALS
        ================================================================ */
        .bottom-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
            border-top: 2.5px solid var(--orange);
            transition: border-color 0.3s ease;
        }

        .bot-cell {
            padding: 14px 18px
        }

        .bot-cell:first-child {
            border-right: 1.5px solid var(--border-color);
            transition: border-color 0.3s ease;
        }

        .sec-head {
            font-size: 10px;
            font-weight: 900;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #fff;
            background: var(--orange);
            padding: 5px 10px;
            margin: -14px -18px 10px;
            display: block;
            transition: background 0.3s ease;
        }

        .bank-grid {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 5px 10px;
            font-size: 11.5px
        }

        .bank-lbl {
            color: var(--text-muted);
            font-weight: 600;
            white-space: nowrap;
            transition: color 0.3s ease;
        }

        .bank-val {
            font-weight: 700;
            color: var(--text-primary);
            transition: color 0.3s ease;
        }

        .totals-table {
            width: 100%;
            border-collapse: collapse
        }

        .totals-table td {
            padding: 5px 8px;
            font-size: 12px;
            color: var(--text-primary);
            transition: color 0.3s ease;
        }

        .totals-table .tl {
            color: var(--text-muted);
            font-size: 11.5px;
            transition: color 0.3s ease;
        }

        .totals-table .tv {
            text-align: right;
            font-weight: 700
        }

        .totals-table .td-divider td {
            border-top: 1px solid var(--border-color);
            transition: border-color 0.3s ease;
        }

        .total-final {
            background: var(--total-bg) !important;
            transition: background 0.3s ease;
        }

        .total-final td {
            color: var(--total-text) !important;
            font-size: 15px !important;
            font-weight: 900 !important;
            padding: 9px 8px !important;
        }

        .total-final .tl {
            color: var(--text-secondary) !important;
            font-size: 12px !important
        }

        .total-final .tv {
            color: var(--total-text) !important
        }

        .gst-toggle-area {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
            padding: 6px 10px;
            background: var(--gst-toggle-bg);
            border-radius: 6px;
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }

        .gst-toggle-area label {
            font-size: 11px;
            color: var(--text-muted);
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .gst-toggle-area .toggle-switch {
            position: relative;
            display: inline-block;
            width: 40px;
            height: 22px;
        }

        .gst-toggle-area .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .gst-toggle-area .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: var(--border-light);
            transition: .3s;
            border-radius: 22px;
        }

        .gst-toggle-area .slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 3px;
            bottom: 3px;
            background: white;
            transition: .3s;
            border-radius: 50%;
        }

        .gst-toggle-area input:checked+.slider {
            background: var(--orange);
        }

        .gst-toggle-area input:checked+.slider:before {
            transform: translateX(18px);
        }

        .gst-label {
            font-size: 11px;
            font-weight: 700;
            color: var(--orange);
            transition: color 0.3s ease;
        }

        /* ================================================================
        TERMS FULL WIDTH
        ================================================================ */
        .terms-full {
            border-top: 1.5px solid var(--border-color);
            padding: 14px 18px 10px;
            background: var(--section-bg);
            transition: all 0.3s ease;
        }

        .terms-full .sec-head {
            margin: -14px -18px 10px;
        }

        .terms-full .terms-list {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 5px 20px;
            color: var(--text-muted);
            padding-left: 15px;
            transition: color 0.3s ease;
        }

        .terms-full .terms-list li {
            font-size: 10.2px;
            line-height: 1.4;
        }

        /* ================================================================
        FOOTER
        ================================================================ */
        .quo-footer {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            padding: 20px 32px 18px;
            border-top: 2px solid var(--orange);
            background: var(--section-bg);
            transition: all 0.3s ease;
        }

        .footer-left {
            font-size: 11px;
            color: var(--text-muted);
            transition: color 0.3s ease;
        }

        .footer-left strong {
            color: var(--text-primary);
            font-size: 12px;
            transition: color 0.3s ease;
        }

        .sig-box {
            text-align: center;
            min-width: 190px
        }

        .sig-company {
            font-size: 10px;
            color: var(--text-muted);
            margin-bottom: 2px;
            text-transform: uppercase;
            letter-spacing: .5px;
            transition: color 0.3s ease;
        }

        .sig-line {
            border-top: 1.5px solid var(--border-color);
            padding-top: 5px;
            font-size: 11px;
            color: var(--text-secondary);
            margin-top: 48px;
            transition: all 0.3s ease;
        }

        /* ================================================================
        SAVE FORM - Hidden
        ================================================================ */
        #saveQuotationForm {
            display: none;
        }

        /* ================================================================
        RESPONSIVE
        ================================================================ */
        @media (max-width:768px) {
            body {
                margin-left: 0 !important;
            }
            .main-wrapper {
                padding: 10px;
            }
            #toolbar {
                border-radius: 0;
                margin-bottom: 10px;
            }
            .quo-header {
                flex-direction: column;
            }
            .quo-right {
                text-align: left;
                width: 100%;
                margin-top: 15px;
            }
            .bill-band {
                grid-template-columns: 1fr;
            }
            .bill-section:first-child {
                border-right: none;
                border-bottom: 1.5px solid var(--border-color);
            }
            .bottom-grid {
                grid-template-columns: 1fr;
            }
            .bot-cell:first-child {
                border-right: none;
                border-bottom: 1.5px solid var(--border-color);
            }
            .terms-full .terms-list {
                grid-template-columns: 1fr;
            }
            .quo-footer {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
        }

        /* ================================================================
        PRINT STYLES
        ================================================================ */
        @media print {
            .back-btn,
            .print-btn,
            .sidebar {
                display: none !important;
            }
            body {
                background: #fff;
                margin-left: 0 !important;
                color: #000;
            }
            .main-wrapper {
                padding: 0;
                max-width: 100%;
            }
            #toolbar,
            .panel {
                display: none !important
            }
            #quotationWrap {
                margin: 0;
                box-shadow: none;
                border: none;
                overflow: visible;
                border-radius: 0;
            }
            .stripe-top {
                border-radius: 0
            }
            .quo-body {
                padding: 12px 18px 10px
            }
            .no-col-toggle,
            th[data-col="actions"],
            td[data-col="actions"] {
                display: none !important;
            }
            .col-hidden,
            .row-hidden {
                display: none !important
            }
            .stripe-top {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
            .sec-head {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
            [contenteditable] {
                outline: none !important;
                background: transparent !important
            }
            #productTable thead tr {
                background: var(--orange) !important;
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
            #productTable th {
                color: #fff !important;
                background: var(--orange) !important;
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
            .watermark {
                opacity: 0.08;
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
                color: var(--watermark-color);
                z-index: 0;
            }
            .table-wrap {
                position: relative;
                z-index: 1;
            }
            .terms-full {
                background: #fff;
            }
            @page {
                size: A4;
                margin: 8mm
            }
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: var(--scrollbar-track);
            transition: background 0.3s ease;
        }
        ::-webkit-scrollbar-thumb {
            background: var(--scrollbar-thumb);
            border-radius: 10px;
            transition: background 0.3s ease;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: var(--scrollbar-thumb-hover);
        }
    </style>
</head>

<body>
    <div class="main-wrapper" id="mainContent">
        <!-- Toolbar -->
        <div id="toolbar">
            <div class="tb-brand">EBIZ<span>TECH</span> &middot; Quotation Builder</div>
            <button class="btn btn-green" onclick="addRow()">+ Add Row</button>
            <button class="btn btn-orange" onclick="removeLastVisible()">- Hide Row</button>
            <button class="btn btn-col" onclick="togglePanel('colPanel')">Columns</button>
            <button class="btn btn-addcol" onclick="togglePanel('addColPanel')">+ Add Column</button>
            <button class="btn btn-save" onclick="saveQuotation()">💾 Save</button>
            <button class="btn btn-print" onclick="window.print()">🖨 Print / PDF</button>

            <div id="colPanel" class="panel">
                <div class="panel-title">Show / Hide Columns</div>
            </div>

            <div id="addColPanel" class="panel">
                <div class="panel-title">Add New Column</div>
                <input type="text" id="newColName" placeholder="Column header name...">
                <button class="btn-sm" onclick="addColumn()">Add Column</button>
            </div>
        </div>

        <!-- Quotation Document -->
        <div id="quotationWrap">
            <div class="stripe-top"></div>
            <div class="watermark"><?= htmlspecialchars($company['short']) ?></div>

            <div class="quo-body">
                <!-- Header -->
                <div class="quo-header">
                    <div class="co-left">
                        <div class="co-logo-wrap">
                            <img src="<?= htmlspecialchars($company['logo']) ?>" class="co-logo"
                                alt="<?= htmlspecialchars($company['name']) ?>"
                                onerror="this.style.display='none';document.querySelector('.co-logo-text').style.display='block'">
                            <div class="co-logo-text"><?= htmlspecialchars($company['short']) ?></div>
                        </div>
                        <div class="co-name"><?= htmlspecialchars($company['name']) ?></div>
                        <div class="co-info">
                            <?= htmlspecialchars($company['address']) ?><br>
                            Contact: <?= htmlspecialchars($company['contact']) ?><br>
                            Email: <?= htmlspecialchars($company['email']) ?>&nbsp;&nbsp;|&nbsp;&nbsp;Web:
                            <?= htmlspecialchars($company['website']) ?>
                        </div>
                        <div class="co-gst">GST No.: <?= htmlspecialchars($company['gst']) ?></div>
                    </div>

                    <div class="quo-right">
                        <div class="quo-title-word">Quotation</div>
                        <div style="margin-bottom:8px">
                            <span class="quo-status" id="statusBadge" onclick="cycleStatus()">Draft</span>
                        </div>
                        <div style="margin-bottom:12px">
                            <span class="iso-seal">
                                <span style="font-size:20px;line-height:1">&#9679;</span>
                                <span>
                                    <?= htmlspecialchars($iso) ?>
                                    <span class="iso-cert-text">Certified Company</span>
                                </span>
                            </span>
                        </div>
                        <table class="meta-table">
                            <tr>
                                <td class="ml">Quotation No.</td>
                                <td class="mv" contenteditable="true" id="quoNumber"><?= htmlspecialchars($quotation['number']) ?></td>
                            </tr>
                            <tr>
                                <td class="ml">Date</td>
                                <td class="mv" contenteditable="true" id="quoDate"><?= htmlspecialchars($quotation['date']) ?></td>
                            </tr>
                            <tr>
                                <td class="ml">Reference</td>
                                <td class="mv" contenteditable="true" id="quoRef"><?= htmlspecialchars($quotation['reference']) ?></td>
                            </tr>
                            <tr>
                                <td class="ml">Customer ID</td>
                                <td class="mv" contenteditable="true" id="quoCustId"><?= htmlspecialchars($quotation['customer_id']) ?></td>
                            </tr>
                            <tr>
                                <td class="ml">Valid Until</td>
                                <td class="mv" contenteditable="true" id="quoValid"><?= htmlspecialchars($quotation['valid_until']) ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Bill To -->
                <div class="bill-band">
                    <div class="bill-section">
                        <div class="bill-head">Quotation To</div>
                        <div class="bill-company" contenteditable="true" id="custCompany"><?= htmlspecialchars($bill_to['company']) ?></div>
                        <div class="bill-info">
                            <div contenteditable="true" id="custContact"><?= htmlspecialchars($bill_to['contact']) ?></div>
                            <div contenteditable="true" id="custAddress"><?= htmlspecialchars($bill_to['address']) ?></div>
                        </div>
                    </div>
                    <div class="bill-section">
                        <div class="bill-head">Client Details</div>
                        <div class="bill-info">
                            <div><strong>GST No. :</strong> <span contenteditable="true" id="custGst"><?= htmlspecialchars($bill_to['gst'] ?: '—') ?></span></div>
                            <div><strong>Email &nbsp;&nbsp;:</strong> <span contenteditable="true" id="custEmail"><?= htmlspecialchars($bill_to['email'] ?: '—') ?></span></div>
                            <div><strong>Phone &nbsp;:</strong> <span contenteditable="true" id="custPhone"><?= htmlspecialchars($bill_to['phone'] ?: '—') ?></span></div>
                        </div>
                    </div>
                </div>

                <!-- Product Table -->
                <div class="table-wrap">
                    <table id="productTable">
                        <thead>
                            <tr id="theadRow">
                                <th data-col="no" style="width:36px;text-align:center">#</th>
                                <th data-col="product">Product / Service</th>
                                <th data-col="desc">Description</th>
                                <th data-col="period">Billing Period</th>
                                <th data-col="qty" style="width:60px;text-align:right">Qty</th>
                                <th data-col="price" style="width:105px;text-align:right">Unit Price (Rs.)</th>
                                <th data-col="amount" style="width:115px;text-align:right">Amount (Rs.)</th>
                                <th data-col="actions" class="no-col-toggle" style="width:62px;text-align:center">Act.</th>
                            </tr>
                        </thead>
                        <tbody id="productBody">
                            <?php
                            for ($i = 0; $i < $totalSlots; $i++) {
                                $r = $rows[$i] ?? null;
                                $cls = ($r === null) ? 'row-hidden' : '';
                                $amt = $r ? ($r['qty'] * $r['price']) : 0;
                                echo "<tr data-row=\"" . ($i + 1) . "\" class=\"$cls\">";
                                echo "<td data-col=\"no\" class=\"td-no\">" . ($r ? ($i + 1) : '') . "</td>";
                                echo "<td data-col=\"product\" contenteditable=\"true\">" . ($r ? htmlspecialchars($r['product']) : '') . "</td>";
                                echo "<td data-col=\"desc\"    contenteditable=\"true\">" . ($r ? htmlspecialchars($r['desc']) : '') . "</td>";
                                echo "<td data-col=\"period\"  contenteditable=\"true\">" . ($r ? htmlspecialchars($r['period']) : '') . "</td>";
                                echo "<td data-col=\"qty\"     contenteditable=\"true\" class=\"td-qty num-cell\" onblur=\"recalcRow(this)\">" . ($r ? $r['qty'] : '') . "</td>";
                                echo "<td data-col=\"price\"   contenteditable=\"true\" class=\"td-price num-cell\" onblur=\"recalcRow(this)\">" . ($r ? $r['price'] : '') . "</td>";
                                echo "<td data-col=\"amount\"  class=\"td-amount amount-cell\">" . ($r && $amt > 0 ? number_format($amt, 2) : '') . "</td>";
                                echo "<td data-col=\"actions\" class=\"row-actions no-col-toggle\">";
                                echo "<button onclick=\"showRow(" . ($i + 1) . ")\" title=\"Show row\">Show</button> ";
                                echo "<button onclick=\"hideRow(" . ($i + 1) . ")\" title=\"Hide row\">Hide</button>";
                                echo "</td></tr>\n";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

            </div><!-- /quo-body -->

            <!-- Bottom Grid -->
            <div class="bottom-grid">
                <div class="bot-cell">
                    <span class="sec-head">Bank Account Details</span>
                    <div class="bank-grid">
                        <div class="bank-lbl">Account Name:</div>
                        <div class="bank-val" contenteditable="true" id="bankAccName"><?= htmlspecialchars($bank['account_name']) ?></div>
                        <div class="bank-lbl">Account No.:</div>
                        <div class="bank-val" contenteditable="true" id="bankAccNum"><?= htmlspecialchars($bank['account_number']) ?></div>
                        <div class="bank-lbl">IFSC Code:</div>
                        <div class="bank-val" contenteditable="true" id="bankIfsc"><?= htmlspecialchars($bank['ifsc']) ?></div>
                        <div class="bank-lbl">Bank Name:</div>
                        <div class="bank-val" contenteditable="true" id="bankName"><?= htmlspecialchars($bank['bank_name']) ?></div>
                        <div class="bank-lbl">Branch:</div>
                        <div class="bank-val" contenteditable="true" id="bankBranch"><?= htmlspecialchars($bank['branch']) ?></div>
                    </div>
                </div>

                <div class="bot-cell">
                    <div class="gst-toggle-area no-col-toggle">
                        <label class="toggle-switch">
                            <input type="checkbox" id="gstToggle" <?= $show_gst == 'yes' ? 'checked' : '' ?> onchange="toggleGSTCalculations()">
                            <span class="slider"></span>
                        </label>
                        <span class="gst-label">Apply 18% GST Calculations</span>
                    </div>

                    <table class="totals-table">
                        <tr>
                            <td class="tl">Sub Total</td>
                            <td class="tv" id="valSubtotal">0.00</td>
                        </tr>
                        <tr>
                            <td class="tl">Discount</td>
                            <td class="tv" contenteditable="true" id="valDiscount" onblur="calculateTotals()">0.00</td>
                        </tr>
                        <tr class="td-divider">
                            <td class="tl">Taxable Value</td>
                            <td class="tv" id="valTaxable">0.00</td>
                        </tr>
                        <tr id="gstRow" <?= $show_gst != 'yes' ? 'style="display:none"' : '' ?>>
                            <td class="tl">CGST (9%) + SGST (9%)</td>
                            <td class="tv" id="valGst">0.00</td>
                        </tr>
                        <tr>
                            <td class="tl">Other Charges</td>
                            <td class="tv" contenteditable="true" id="valOtherCharges" onblur="calculateTotals()">0.00</td>
                        </tr>
                        <tr class="total-final">
                            <td class="tl">Grand Total (Rs.)</td>
                            <td class="tv" id="valGrandTotal">0.00</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Terms -->
            <div class="terms-full">
                <span class="sec-head">Terms & Conditions</span>
                <ol class="terms-list" contenteditable="true" id="quoTerms">
                    <?php foreach ($terms as $term): ?>
                        <li><?= $term ?></li>
                    <?php endforeach; ?>
                </ol>
            </div>

            <!-- Footer -->
            <div class="quo-footer">
                <div class="footer-left">
                    Thank you for your business!<br>
                    For any queries regarding this quotation, please reach out to
                    <strong><?= htmlspecialchars($company['email']) ?></strong>.
                </div>
            </div>

        </div><!-- /quotationWrap -->
    </div><!-- /main-wrapper -->

    <!-- Save Form (Hidden) -->
    <form id="saveQuotationForm" method="POST" action="">
        <input type="hidden" name="save_quotation" value="1">
        <input type="hidden" name="quotation_number" id="formQuoNumber">
        <input type="hidden" name="quotation_date" id="formQuoDate">
        <input type="hidden" name="reference" id="formQuoRef">
        <input type="hidden" name="customer_id" id="formQuoCustId">
        <input type="hidden" name="valid_until" id="formQuoValid">
        <input type="hidden" name="status" id="formStatus">
        <input type="hidden" name="company_name" value="<?= htmlspecialchars($company['name']) ?>">
        <input type="hidden" name="company_address" value="<?= htmlspecialchars($company['address']) ?>">
        <input type="hidden" name="company_contact" value="<?= htmlspecialchars($company['contact']) ?>">
        <input type="hidden" name="company_email" value="<?= htmlspecialchars($company['email']) ?>">
        <input type="hidden" name="company_gst" value="<?= htmlspecialchars($company['gst']) ?>">
        <input type="hidden" name="customer_company" id="formCustCompany">
        <input type="hidden" name="customer_contact" id="formCustContact">
        <input type="hidden" name="customer_address" id="formCustAddress">
        <input type="hidden" name="customer_gst" id="formCustGst">
        <input type="hidden" name="customer_email" id="formCustEmail">
        <input type="hidden" name="customer_phone" id="formCustPhone">
        <input type="hidden" name="subtotal" id="formSubtotal">
        <input type="hidden" name="discount" id="formDiscount">
        <input type="hidden" name="taxable_value" id="formTaxable">
        <input type="hidden" name="gst_rate" id="formGstRate" value="18">
        <input type="hidden" name="gst_amount" id="formGstAmount">
        <input type="hidden" name="other_charges" id="formOtherCharges">
        <input type="hidden" name="grand_total" id="formGrandTotal">
        <input type="hidden" name="grand_total_without_gst" id="formGrandTotalWithoutGst">
        <input type="hidden" name="show_gst" id="formShowGst" value="1">
        <input type="hidden" name="bank_account_name" id="formBankAccName">
        <input type="hidden" name="bank_account_number" id="formBankAccNum">
        <input type="hidden" name="bank_ifsc" id="formBankIfsc">
        <input type="hidden" name="bank_name" id="formBankName">
        <input type="hidden" name="bank_branch" id="formBankBranch">
        <input type="hidden" name="terms" id="formTerms">
        <input type="hidden" name="items_json" id="formItemsJson">
    </form>

    <script>
        // ============================================
        // ALL JAVASCRIPT FUNCTIONS
        // ============================================
        
        const statusOptions = ['Draft', 'Sent', 'Approved', 'Declined'];
        let currentStatusIndex = 0;

        // Listen for sidebar theme changes
        document.addEventListener("DOMContentLoaded", function () {
            initColumnPanel();
            calculateTotals();

            // Watch for sidebar collapse state
            const sidebar = document.getElementById('sidebar');
            if (sidebar) {
                const sidebarObserver = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                            if (sidebar.classList.contains('collapsed')) {
                                document.body.classList.add('sidebar-collapsed');
                            } else {
                                document.body.classList.remove('sidebar-collapsed');
                            }
                        }
                    });
                });
                sidebarObserver.observe(sidebar, { attributes: true });
                
                // Set initial state
                if (sidebar.classList.contains('collapsed')) {
                    document.body.classList.add('sidebar-collapsed');
                }
            }
        });

        function togglePanel(panelId) {
            const target = document.getElementById(panelId);
            target.style.display = (target.style.display === 'block') ? 'none' : 'block';
            const alternate = (panelId === 'colPanel') ? 'addColPanel' : 'colPanel';
            document.getElementById(alternate).style.display = 'none';
        }

        function cycleStatus() {
            currentStatusIndex = (currentStatusIndex + 1) % statusOptions.length;
            document.getElementById('statusBadge').innerText = statusOptions[currentStatusIndex];
        }

        function addRow() {
            const hiddenRows = document.querySelectorAll('#productBody tr.row-hidden');
            if (hiddenRows.length > 0) {
                hiddenRows[0].classList.remove('row-hidden');
                reindexRows();
            } else {
                alert("Maximum standard items slot limit reached.");
            }
        }

        function removeLastVisible() {
            const visibleRows = Array.from(document.querySelectorAll('#productBody tr')).filter(r => !r.classList.contains('row-hidden'));
            if (visibleRows.length > 1) {
                const last = visibleRows[visibleRows.length - 1];
                last.classList.add('row-hidden');
                clearRowData(last);
                reindexRows();
                calculateTotals();
            }
        }

        function showRow(index) {
            const row = document.querySelector(`#productBody tr[data-row="${index}"]`);
            if (row) {
                row.classList.remove('row-hidden');
                reindexRows();
                calculateTotals();
            }
        }

        function hideRow(index) {
            const row = document.querySelector(`#productBody tr[data-row="${index}"]`);
            if (row) {
                row.classList.add('row-hidden');
                clearRowData(row);
                reindexRows();
                calculateTotals();
            }
        }

        function clearRowData(row) {
            row.querySelectorAll('[contenteditable="true"]').forEach(el => el.innerText = '');
            row.querySelector('.td-amount').innerText = '';
        }

        function reindexRows() {
            let index = 1;
            document.querySelectorAll('#productBody tr').forEach(row => {
                if (!row.classList.contains('row-hidden')) {
                    row.querySelector('.td-no').innerText = index++;
                }
            });
        }

        function recalcRow(cell) {
            const row = cell.closest('tr');
            const qty = parseFloat(row.querySelector('.td-qty').innerText.replace(/,/g, '')) || 0;
            const price = parseFloat(row.querySelector('.td-price').innerText.replace(/,/g, '')) || 0;
            const amountCell = row.querySelector('.td-amount');
            const amount = qty * price;
            amountCell.innerText = amount > 0 ? amount.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '';
            calculateTotals();
        }

        function toggleGSTCalculations() {
            const gstRow = document.getElementById('gstRow');
            gstRow.style.display = document.getElementById('gstToggle').checked ? 'table-row' : 'none';
            calculateTotals();
        }

        function calculateTotals() {
            let subtotal = 0;
            document.querySelectorAll('#productBody tr').forEach(row => {
                if (!row.classList.contains('row-hidden')) {
                    const amtText = row.querySelector('.td-amount').innerText.replace(/,/g, '');
                    subtotal += parseFloat(amtText) || 0;
                }
            });

            const discount = parseFloat(document.getElementById('valDiscount').innerText.replace(/,/g, '')) || 0;
            const taxable = Math.max(0, subtotal - discount);
            let gstAmount = 0;
            if (document.getElementById('gstToggle').checked) {
                gstAmount = taxable * 0.18;
            }
            const otherCharges = parseFloat(document.getElementById('valOtherCharges').innerText.replace(/,/g, '')) || 0;
            const grandTotal = taxable + gstAmount + otherCharges;
            const grandTotalWithoutGst = taxable + otherCharges;

            document.getElementById('valSubtotal').innerText = subtotal.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            document.getElementById('valTaxable').innerText = taxable.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            document.getElementById('valGst').innerText = gstAmount.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            document.getElementById('valGrandTotal').innerText = grandTotal.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            window.calculatedValues = { subtotal, discount, taxable, gstAmount, otherCharges, grandTotal, grandTotalWithoutGst };
        }

        function initColumnPanel() {
            const panel = document.getElementById('colPanel');
            document.querySelectorAll('#productTable th').forEach(th => {
                const colKey = th.getAttribute('data-col');
                if (th.classList.contains('no-col-toggle')) return;
                const label = document.createElement('label');
                label.innerHTML = `<input type="checkbox" checked data-target="${colKey}" onchange="toggleColumnVisibility(this)"> ${th.innerText}`;
                panel.appendChild(label);
            });
        }

        function toggleColumnVisibility(cb) {
            const colKey = cb.getAttribute('data-target');
            document.querySelectorAll(`#productTable [data-col="${colKey}"]`).forEach(el => {
                if (cb.checked) el.classList.remove('col-hidden');
                else el.classList.add('col-hidden');
            });
        }

        function addColumn() {
            const input = document.getElementById('newColName');
            const colName = input.value.trim();
            if (!colName) return alert("Please enter a valid column name.");
            const colKey = 'custom_' + Date.now();

            const th = document.createElement('th');
            th.setAttribute('data-col', colKey);
            th.innerText = colName;
            th.contentEditable = "true";
            const theadRow = document.getElementById('theadRow');
            theadRow.insertBefore(th, theadRow.querySelector('[data-col="amount"]'));

            document.querySelectorAll('#productBody tr').forEach(row => {
                const td = document.createElement('td');
                td.setAttribute('data-col', colKey);
                td.contentEditable = "true";
                row.insertBefore(td, row.querySelector('.td-amount'));
            });

            const panel = document.getElementById('colPanel');
            const label = document.createElement('label');
            label.innerHTML = `<input type="checkbox" checked data-target="${colKey}" onchange="toggleColumnVisibility(this)"> ${colName}`;
            panel.appendChild(label);

            input.value = '';
            document.getElementById('addColPanel').style.display = 'none';
        }

        function saveQuotation() {
            calculateTotals();

            const items = [];
            document.querySelectorAll('#productBody tr').forEach(row => {
                if (!row.classList.contains('row-hidden')) {
                    const item = {
                        product: row.querySelector('[data-col="product"]').innerText.trim(),
                        desc: row.querySelector('[data-col="desc"]').innerText.trim(),
                        period: row.querySelector('[data-col="period"]').innerText.trim(),
                        qty: parseFloat(row.querySelector('.td-qty').innerText.replace(/,/g, '')) || 0,
                        price: parseFloat(row.querySelector('.td-price').innerText.replace(/,/g, '')) || 0,
                        amount: parseFloat(row.querySelector('.td-amount').innerText.replace(/,/g, '')) || 0
                    };
                    row.querySelectorAll('td[data-col^="custom_"]').forEach(td => {
                        item[td.getAttribute('data-col')] = td.innerText.trim();
                    });
                    items.push(item);
                }
            });

            document.getElementById('formQuoNumber').value = document.getElementById('quoNumber').innerText.trim();
            document.getElementById('formQuoDate').value = document.getElementById('quoDate').innerText.trim();
            document.getElementById('formQuoRef').value = document.getElementById('quoRef').innerText.trim();
            document.getElementById('formQuoCustId').value = document.getElementById('quoCustId').innerText.trim();
            document.getElementById('formQuoValid').value = document.getElementById('quoValid').innerText.trim();
            document.getElementById('formStatus').value = document.getElementById('statusBadge').innerText.trim().toLowerCase();

            document.getElementById('formCustCompany').value = document.getElementById('custCompany').innerText.trim();
            document.getElementById('formCustContact').value = document.getElementById('custContact').innerText.trim();
            document.getElementById('formCustAddress').value = document.getElementById('custAddress').innerText.trim();
            document.getElementById('formCustGst').value = document.getElementById('custGst').innerText.trim();
            document.getElementById('formCustEmail').value = document.getElementById('custEmail').innerText.trim();
            document.getElementById('formCustPhone').value = document.getElementById('custPhone').innerText.trim();

            document.getElementById('formBankAccName').value = document.getElementById('bankAccName').innerText.trim();
            document.getElementById('formBankAccNum').value = document.getElementById('bankAccNum').innerText.trim();
            document.getElementById('formBankIfsc').value = document.getElementById('bankIfsc').innerText.trim();
            document.getElementById('formBankName').value = document.getElementById('bankName').innerText.trim();
            document.getElementById('formBankBranch').value = document.getElementById('bankBranch').innerText.trim();

            document.getElementById('formTerms').value = document.getElementById('quoTerms').innerHTML;
            document.getElementById('formItemsJson').value = JSON.stringify(items);

            document.getElementById('formSubtotal').value = window.calculatedValues.subtotal;
            document.getElementById('formDiscount').value = window.calculatedValues.discount;
            document.getElementById('formTaxable').value = window.calculatedValues.taxable;
            document.getElementById('formGstAmount').value = window.calculatedValues.gstAmount;
            document.getElementById('formOtherCharges').value = window.calculatedValues.otherCharges;
            document.getElementById('formGrandTotal').value = window.calculatedValues.grandTotal;
            document.getElementById('formGrandTotalWithoutGst').value = window.calculatedValues.grandTotalWithoutGst;
            document.getElementById('formShowGst').value = document.getElementById('gstToggle').checked ? 1 : 0;

            document.getElementById('saveQuotationForm').submit();
        }
    </script>
</body>
</html>