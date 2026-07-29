<?php
// ============================================
// 1. FIRST - Include database connection
// ============================================
include 'sidebar.php';
include 'db_connection.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ============================================
// Handle form submission (SAVE to database)
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_invoice'])) {
    try {
        // Escape and sanitize all inputs
        $invoice_number = mysqli_real_escape_string($conn, $_POST['invoice_number'] ?? '');
        $quote_number = mysqli_real_escape_string($conn, $_POST['quote_number'] ?? '');
        $customer_id = mysqli_real_escape_string($conn, $_POST['customer_id'] ?? '');
        $invoice_date = mysqli_real_escape_string($conn, $_POST['invoice_date'] ?? '');
        $due_date = mysqli_real_escape_string($conn, $_POST['due_date'] ?? '');
        $status = mysqli_real_escape_string($conn, $_POST['status'] ?? 'unpaid');
        $currency = mysqli_real_escape_string($conn, $_POST['currency'] ?? '₹');
        $company_name = mysqli_real_escape_string($conn, $_POST['company_name'] ?? '');
        $company_address = mysqli_real_escape_string($conn, $_POST['company_address'] ?? '');
        $company_contact = mysqli_real_escape_string($conn, $_POST['company_contact'] ?? '');
        $company_email = mysqli_real_escape_string($conn, $_POST['company_email'] ?? '');
        $customer_name = mysqli_real_escape_string($conn, $_POST['customer_name'] ?? '');
        $customer_contact_person = mysqli_real_escape_string($conn, $_POST['customer_contact_person'] ?? '');
        $customer_address = mysqli_real_escape_string($conn, $_POST['customer_address'] ?? '');
        $supplier_gstin = mysqli_real_escape_string($conn, $_POST['supplier_gstin'] ?? '');
        $buyer_gstin = mysqli_real_escape_string($conn, $_POST['buyer_gstin'] ?? '');
        $place_of_supply = mysqli_real_escape_string($conn, $_POST['place_of_supply'] ?? '');
        $hsn_sac_code = mysqli_real_escape_string($conn, $_POST['hsn_sac_code'] ?? '');
        $gst_mode = mysqli_real_escape_string($conn, $_POST['gst_mode'] ?? 'intra');

        // Numeric fields
        $subtotal = floatval($_POST['subtotal'] ?? 0);
        $discount = floatval($_POST['discount'] ?? 0);
        $taxable_value = floatval($_POST['taxable_value'] ?? 0);
        $cgst_rate = floatval($_POST['cgst_rate'] ?? 0);
        $sgst_rate = floatval($_POST['sgst_rate'] ?? 0);
        $igst_rate = floatval($_POST['igst_rate'] ?? 0);
        $cgst_amount = floatval($_POST['cgst_amount'] ?? 0);
        $sgst_amount = floatval($_POST['sgst_amount'] ?? 0);
        $igst_amount = floatval($_POST['igst_amount'] ?? 0);
        $other_charges = floatval($_POST['other_charges'] ?? 0);
        $grand_total = floatval($_POST['grand_total'] ?? 0);

        // Bank details
        $bank_account_name = mysqli_real_escape_string($conn, $_POST['bank_account_name'] ?? '');
        $bank_account_number = mysqli_real_escape_string($conn, $_POST['bank_account_number'] ?? '');
        $bank_ifsc_code = mysqli_real_escape_string($conn, $_POST['bank_ifsc_code'] ?? '');
        $bank_name = mysqli_real_escape_string($conn, $_POST['bank_name'] ?? '');
        $bank_branch = mysqli_real_escape_string($conn, $_POST['bank_branch'] ?? '');

        // Terms and notes
        $terms_conditions = mysqli_real_escape_string($conn, $_POST['terms_conditions'] ?? '');
        $notes = mysqli_real_escape_string($conn, $_POST['notes'] ?? '');
        $client_name_footer = mysqli_real_escape_string($conn, $_POST['client_name_footer'] ?? '');
        $items_json = mysqli_real_escape_string($conn, $_POST['items_json'] ?? '[]');
        $amount_in_words = mysqli_real_escape_string($conn, $_POST['amount_in_words'] ?? '');

        // Additional fields
        $bill_type = mysqli_real_escape_string($conn, $_POST['bill_type'] ?? 'Invoice');
        $bill_number = mysqli_real_escape_string($conn, $_POST['bill_number'] ?? $invoice_number);
        $reference = mysqli_real_escape_string($conn, $_POST['reference'] ?? $quote_number);

        // Check if invoice already exists
        $check_query = "SELECT id FROM invoices WHERE invoice_number = '$invoice_number'";
        $check_result = mysqli_query($conn, $check_result);

        if (mysqli_num_rows($check_result) > 0) {
            // Update existing invoice
            $update_query = "UPDATE invoices SET 
                quote_number = '$quote_number',
                customer_id = '$customer_id',
                invoice_date = '$invoice_date',
                due_date = '$due_date',
                status = '$status',
                currency = '$currency',
                company_name = '$company_name',
                company_address = '$company_address',
                company_contact = '$company_contact',
                company_email = '$company_email',
                customer_name = '$customer_name',
                customer_contact_person = '$customer_contact_person',
                customer_address = '$customer_address',
                supplier_gstin = '$supplier_gstin',
                buyer_gstin = '$buyer_gstin',
                place_of_supply = '$place_of_supply',
                hsn_sac_code = '$hsn_sac_code',
                gst_mode = '$gst_mode',
                subtotal = $subtotal,
                discount = $discount,
                taxable_value = $taxable_value,
                cgst_rate = $cgst_rate,
                sgst_rate = $sgst_rate,
                igst_rate = $igst_rate,
                cgst_amount = $cgst_amount,
                sgst_amount = $sgst_amount,
                igst_amount = $igst_amount,
                other_charges = $other_charges,
                grand_total = $grand_total,
                bank_account_name = '$bank_account_name',
                bank_account_number = '$bank_account_number',
                bank_ifsc_code = '$bank_ifsc_code',
                bank_name = '$bank_name',
                bank_branch = '$bank_branch',
                terms_conditions = '$terms_conditions',
                notes = '$notes',
                client_name_footer = '$client_name_footer',
                items_json = '$items_json',
                amount_in_words = '$amount_in_words',
                bill_type = '$bill_type',
                bill_number = '$bill_number',
                reference = '$reference',
                updated_at = NOW()
                WHERE invoice_number = '$invoice_number'";

            if (mysqli_query($conn, $update_query)) {
                $success_message = "✅ Invoice updated successfully!";
            } else {
                throw new Exception("Update failed: " . mysqli_error($conn));
            }
        } else {
            // Insert new invoice
            $insert_query = "INSERT INTO invoices (
                invoice_number, quote_number, customer_id, invoice_date, due_date, 
                status, currency, company_name, company_address, company_contact, 
                company_email, customer_name, customer_contact_person, customer_address, 
                supplier_gstin, buyer_gstin, place_of_supply, hsn_sac_code, gst_mode,
                subtotal, discount, taxable_value, cgst_rate, sgst_rate, igst_rate,
                cgst_amount, sgst_amount, igst_amount, other_charges, grand_total,
                bank_account_name, bank_account_number, bank_ifsc_code, bank_name, bank_branch,
                terms_conditions, notes, client_name_footer, items_json, amount_in_words,
                bill_type, bill_number, reference, created_at, updated_at
            ) VALUES (
                '$invoice_number', '$quote_number', '$customer_id', '$invoice_date', '$due_date',
                '$status', '$currency', '$company_name', '$company_address', '$company_contact',
                '$company_email', '$customer_name', '$customer_contact_person', '$customer_address',
                '$supplier_gstin', '$buyer_gstin', '$place_of_supply', '$hsn_sac_code', '$gst_mode',
                $subtotal, $discount, $taxable_value, $cgst_rate, $sgst_rate, $igst_rate,
                $cgst_amount, $sgst_amount, $igst_amount, $other_charges, $grand_total,
                '$bank_account_name', '$bank_account_number', '$bank_ifsc_code', '$bank_name', '$bank_branch',
                '$terms_conditions', '$notes', '$client_name_footer', '$items_json', '$amount_in_words',
                '$bill_type', '$bill_number', '$reference',
                NOW(), NOW()
            )";

            if (mysqli_query($conn, $insert_query)) {
                $success_message = "✅ Invoice saved successfully!";
            } else {
                throw new Exception("Insert failed: " . mysqli_error($conn));
            }
        }

        // Show success message
        if (isset($success_message)) {
            echo "<script>alert('" . addslashes($success_message) . "');</script>";
            echo "<script>window.location.href = window.location.href;</script>";
        }

    } catch (Exception $e) {
        echo "<script>alert('❌ Error: " . addslashes($e->getMessage()) . "');</script>";
        error_log("Invoice save error: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>ebiZtech — Invoice</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* ============================================
           ALL YOUR EXISTING CSS STYLES
           ============================================ */
        @import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600&display=swap');

        :root {
            --ink: #1a0a0a;
            --ink-soft: #4a3a3a;
            --ink-faint: #8a7a7a;
            --orange: #f57c00;
            --orange-light: #ffb74d;
            --orange-dark: #e65100;
            --orange-gradient-start: #ff9800;
            --orange-gradient-end: #f57c00;
            --orange-subtle: rgba(255, 152, 0, 0.08);
            --orange-shadow: rgba(245, 124, 0, 0.15);
            --paper: #ffffff;
            --canvas: #faf8f6;
            --line: #f0e8e0;
            --line-strong: #e0d8d0;
            --tint: #f8f4f0;
            --navy: #0a0a0a;
            --green: #22c55e;
            --amber: #fbbf24;
            --mono: 'JetBrains Mono', 'Courier New', monospace;
            --disp: 'Space Grotesk', 'Inter', sans-serif;
            --body: 'Inter', system-ui, sans-serif;
            --r: 14px;
            --sidebar-width: 280px;
            --sidebar-collapsed: 85px;
        }

        /* Dark Mode Variables */
        body.dark {
            --ink: #f1f1f4;
            --ink-soft: #9c9ca8;
            --ink-faint: #6b6b75;
            --paper: #0d0505;
            --canvas: #0a0505;
            --line: #2a0a0a;
            --line-strong: #3b0a0a;
            --tint: #1a0a0a;
            --orange: #ffa726;
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
            background: var(--canvas);
            color: var(--ink);
            transition: all .35s ease;
            font-family: var(--body);
            min-height: 100vh;
            margin-left: 60px;
            transition: margin-left .3s ease, background .35s ease, color .35s ease;
            overflow-x: hidden;
        }

        body.dark .sheet {
            background: var(--paper);
            border-color: var(--line);
        }

        body.dark .head {
            background: linear-gradient(180deg, var(--orange-subtle), transparent 70%);
        }

        body.dark .parties .line:hover {
            background: var(--tint);
        }

        body.dark .parties .line:focus {
            background: var(--tint);
            box-shadow: 0 0 0 1px var(--orange) inset;
        }

        body.dark table.items tbody tr:hover td {
            background: var(--tint);
        }

        body.dark table.items td[contenteditable]:focus {
            background: var(--tint);
            box-shadow: 0 0 0 1px var(--orange) inset;
        }

        body.dark .totals .trow .val:hover {
            background: var(--tint);
        }

        body.dark .totals .trow .val:focus {
            background: var(--tint);
            box-shadow: 0 0 0 1px var(--orange) inset;
        }

        body.dark .bank [contenteditable]:focus {
            background: var(--tint);
            box-shadow: 0 0 0 1px var(--orange) inset;
        }

        body.dark .terms li[contenteditable]:focus {
            background: var(--tint);
            box-shadow: 0 0 0 1px var(--orange) inset;
        }

        body.dark .notes textarea:focus {
            border-color: var(--orange);
            background: var(--tint);
        }

        body.dark .invoice-meta [contenteditable]:focus {
            border-color: var(--orange);
            background: var(--tint);
        }

        body.dark .gst-row .v:focus {
            background: var(--tint);
            box-shadow: 0 0 0 1px var(--orange) inset;
        }

        body.dark .watermark span {
            color: var(--orange);
            opacity: .06;
        }

        body.dark .status-badge.unpaid {
            background: var(--orange-subtle);
            color: var(--orange);
            border-color: rgba(255, 152, 0, 0.2);
        }

        body.dark .status-badge.paid {
            background: rgba(34, 197, 94, 0.1);
            color: #4ade80;
            border-color: rgba(34, 197, 94, 0.2);
        }

        body.dark .status-badge.overdue {
            background: rgba(251, 191, 36, 0.1);
            color: #fbbf24;
            border-color: rgba(251, 191, 36, 0.2);
        }

        body.dark .party.gst-box {
            background: var(--orange-subtle);
            border: 1px solid var(--line);
        }

        body.dark .party.gst-box:hover {
            border-color: var(--orange);
            box-shadow: 0 4px 12px var(--orange-shadow);
        }

        body.dark .stamp {
            border-color: var(--green);
            color: var(--green);
            background: rgba(34, 197, 94, 0.05);
        }

        body.dark .stamp.overdue {
            border-color: var(--orange);
            color: var(--orange);
            background: rgba(245, 124, 0, 0.05);
        }

        body.dark .totals .trow.grand {
            background: linear-gradient(135deg, var(--orange-gradient-start), var(--orange));
            color: #fff;
        }

        body.dark .totals .trow.grand .lbl {
            color: #fff;
        }

        body.dark .totals .trow.grand .val {
            color: #fff;
        }

        body.dark .add-row {
            border: 1.5px dashed var(--line-strong);
            color: var(--ink-soft);
        }

        body.dark .add-row:hover {
            border-color: var(--orange);
            color: var(--orange);
            background: var(--tint);
        }

        body.dark .divider {
            background: linear-gradient(90deg, var(--orange-subtle), var(--orange), var(--orange-subtle));
        }

        body.dark .thanks {
            color: var(--ink-soft);
        }

        body.dark .thanks b {
            color: var(--ink);
        }

        body.dark button {
            border: 1px solid var(--line-strong);
            background: var(--paper);
            color: var(--ink);
        }

        body.dark button:hover {
            border-color: var(--orange);
            color: var(--orange);
            box-shadow: 0 4px 10px var(--orange-shadow);
        }

        body.dark button.primary {
            background: linear-gradient(135deg, var(--orange-gradient-start), var(--orange));
            color: #fff;
            border-color: var(--orange);
        }

        body.dark button.primary:hover {
            background: linear-gradient(135deg, var(--orange), var(--orange-dark));
            border-color: var(--orange-dark);
            color: #fff;
        }

        body.dark select {
            border: 1px solid var(--line-strong);
            background: var(--paper);
            color: var(--ink);
        }

        body.dark .sheet.dark {
            --paper: #0d0505;
            --ink: #f1f1f4;
            --ink-soft: #9c9ca8;
            --line: #2a0a0a;
            --line-strong: #3b0a0a;
            --tint: #241313;
            background: var(--paper);
            color: var(--ink);
        }

        body.dark ::selection {
            background: var(--orange-subtle);
            color: var(--orange);
        }

        body.dark .invoice-title {
            color: var(--ink);
        }

        body.dark .invoice-title span {
            color: var(--orange);
        }

        body.dark .party h4 {
            color: var(--orange);
        }

        body.dark .party h4::before {
            background: var(--orange);
        }

        body.dark .party .line {
            color: var(--ink);
        }

        body.dark .party .line.name {
            color: var(--ink);
        }

        body.dark .gst-row .k {
            color: var(--ink-soft);
        }

        body.dark .gst-row .v {
            color: var(--ink);
        }

        body.dark table.items thead th {
            color: #fff;
            background: linear-gradient(135deg, var(--orange-gradient-start), var(--orange));
        }

        body.dark table.items tbody td {
            color: var(--ink);
            border-bottom: 1px solid var(--line);
        }

        body.dark table.items td.amount {
            color: var(--orange);
        }

        body.dark table.items td.no {
            color: var(--ink-faint);
        }

        body.dark table.items td.period {
            color: var(--ink-soft);
        }

        body.dark .totals .trow {
            border-bottom: 1px solid var(--line);
        }

        body.dark .totals .trow .lbl {
            color: var(--ink-soft);
        }

        body.dark .words {
            color: var(--ink-faint);
        }

        body.dark .invoice-meta .lbl {
            color: var(--ink-faint);
        }

        body.dark .invoice-meta {
            color: var(--ink-soft);
        }

        body.dark .currency-pick label {
            color: var(--ink-faint);
        }

        body.dark .bank .bline b {
            color: var(--ink-soft);
        }

        body.dark .bank {
            color: var(--ink);
        }

        body.dark .notes textarea {
            color: var(--ink-soft);
            border: 1px dashed var(--line-strong);
            background: transparent;
        }

        body.dark .notes textarea::placeholder {
            color: var(--ink-faint);
        }

        body.dark .qr-box {
            border-color: var(--orange);
            background: repeating-conic-gradient(var(--orange-subtle) 0% 25%, transparent 0% 50%) 50% / 10px 10px;
        }

        body.dark .qr-note {
            color: var(--ink-faint);
        }

        body.dark .tagline {
            color: var(--ink-faint);
        }

        /* Main content styles */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 35px;
            transition: all .4s ease;
            min-height: 100vh;
        }

        .main-content.expanded {
            margin-left: var(--sidebar-collapsed);
        }

        body.dark .main-content {
            background: var(--canvas);
        }

        .toolbar {
            max-width: 1040px;
            margin: 0 auto 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }

        .toolbar .hint {
            font-size: 12.5px;
            color: var(--ink-soft);
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .toolbar .hint .dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--orange);
            display: inline-block;
            animation: pulse-dot 1.5s infinite;
        }

        @keyframes pulse-dot {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }

        .btns {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        button {
            font-family: var(--body);
            font-size: 12.5px;
            font-weight: 600;
            border-radius: 9px;
            border: 1px solid var(--line-strong);
            background: var(--paper);
            color: var(--ink);
            padding: 9px 14px;
            cursor: pointer;
            transition: all .15s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        button:hover {
            border-color: var(--orange);
            color: var(--orange);
            transform: translateY(-1px);
            box-shadow: 0 4px 10px var(--orange-shadow);
        }

        button.primary {
            background: linear-gradient(135deg, var(--orange-gradient-start), var(--orange));
            color: #fff;
            border-color: var(--orange);
        }

        button.primary:hover {
            background: linear-gradient(135deg, var(--orange), var(--orange-dark));
            border-color: var(--orange-dark);
            color: #fff;
            box-shadow: 0 6px 16px var(--orange-shadow);
        }

        button.ghost {
            background: transparent;
        }

        button.danger-ghost {
            color: var(--ink-faint);
            border-color: transparent;
            padding: 3px 7px;
            font-size: 12px;
            box-shadow: none !important;
            transform: none !important;
        }

        button.danger-ghost:hover {
            color: var(--orange);
            background: var(--tint);
        }

        button.icon-only {
            padding: 9px 10px;
        }

        select {
            font-family: var(--body);
            font-size: 12.5px;
            font-weight: 600;
            border-radius: 9px;
            border: 1px solid var(--line-strong);
            background: var(--paper);
            color: var(--ink);
            padding: 8px 10px;
            cursor: pointer;
        }

        .sheet {
            max-width: 1040px;
            margin: 0 auto;
            background: var(--paper);
            box-shadow: 0 1px 2px rgba(245, 124, 0, .04), 0 30px 60px -20px var(--orange-shadow);
            border-radius: 20px;
            overflow: hidden;
            position: relative;
            border: 1px solid var(--line);
            transition: all .35s ease;
        }

        .sheet.dark {
            --paper: #0d0505;
            --ink: #f1f1f4;
            --ink-soft: #9c9ca8;
            --line: #2a0a0a;
            --line-strong: #3b0a0a;
            --tint: #241313;
            background: var(--paper);
            color: var(--ink);
        }

        .invoice-wrapper {
            width: calc(100% - 40px);
            max-width: 1200px;
            margin: 30px auto;
            transition: all .3s ease;
        }

        .watermark {
            position: absolute;
            inset: 0;
            pointer-events: none;
            overflow: hidden;
            z-index: 0;
        }

        .watermark span {
            position: absolute;
            top: 38%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-18deg);
            font-family: var(--disp);
            font-weight: 700;
            font-size: 120px;
            letter-spacing: 6px;
            color: var(--orange);
            opacity: .04;
            white-space: nowrap;
        }

        .head {
            position: relative;
            z-index: 1;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 40px 46px 26px;
            gap: 24px;
            background: linear-gradient(180deg, var(--orange-subtle), transparent 70%);
        }

        .brand-stack {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .tagline {
            font-family: var(--mono);
            font-size: 10.5px;
            letter-spacing: 1.6px;
            text-transform: uppercase;
            color: var(--ink-faint);
            margin-top: 2px;
        }

        .head-right {
            text-align: right;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .6px;
            text-transform: uppercase;
            padding: 6px 12px;
            border-radius: 999px;
            margin-bottom: 12px;
            cursor: pointer;
            user-select: none;
            border: 1px solid transparent;
            transition: all .15s ease;
        }

        .status-badge:hover {
            transform: scale(1.02);
        }

        .status-badge .pulse {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            animation: pulse-dot 1.5s infinite;
        }

        .status-badge.unpaid {
            background: var(--orange-subtle);
            color: var(--orange);
            border-color: rgba(255, 152, 0, 0.2);
        }

        .status-badge.unpaid .pulse {
            background: var(--orange);
        }

        .status-badge.paid {
            background: rgba(34, 197, 94, 0.1);
            color: var(--green);
            border-color: rgba(34, 197, 94, 0.2);
        }

        .status-badge.paid .pulse {
            background: var(--green);
        }

        .status-badge.overdue {
            background: rgba(251, 191, 36, 0.1);
            color: var(--amber);
            border-color: rgba(251, 191, 36, 0.2);
        }

        .status-badge.overdue .pulse {
            background: var(--amber);
        }

        .invoice-title {
            font-family: var(--disp);
            font-size: 32px;
            font-weight: 700;
            letter-spacing: .5px;
            color: var(--ink);
            margin: 0 0 10px;
            line-height: 1;
        }

        .invoice-title span {
            color: var(--orange);
        }

        .invoice-meta {
            font-family: var(--mono);
            font-size: 12px;
            color: var(--ink-soft);
        }

        .invoice-meta .row {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 4px;
            align-items: baseline;
        }

        .invoice-meta .lbl {
            color: var(--ink-faint);
        }

        .invoice-meta [contenteditable] {
            min-width: 90px;
            text-align: right;
            border-bottom: 1px dashed transparent;
            outline: none;
            border-radius: 3px;
            transition: all .2s ease;
        }

        .invoice-meta [contenteditable]:hover {
            border-color: var(--line-strong);
        }

        .invoice-meta [contenteditable]:focus {
            border-color: var(--orange);
            background: var(--tint);
        }

        .divider {
            height: 2px;
            background: linear-gradient(90deg, var(--orange-subtle), var(--orange), var(--orange-subtle));
            margin: 0 46px;
            position: relative;
            z-index: 1;
        }

        .parties {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 22px;
            padding: 28px 46px;
        }

        .party h4 {
            font-family: var(--disp);
            font-size: 10.5px;
            text-transform: uppercase;
            letter-spacing: 1.6px;
            color: var(--orange);
            margin: 0 0 11px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .party h4::before {
            content: '';
            width: 14px;
            height: 2px;
            background: var(--orange);
            border-radius: 2px;
        }

        .party .line {
            font-size: 13.5px;
            line-height: 1.65;
            color: var(--ink);
            outline: none;
            border-radius: 5px;
            padding: 1px 4px;
            margin-left: -4px;
            transition: all .2s ease;
        }

        .party .line:hover {
            background: var(--tint);
        }

        .party .line:focus {
            background: var(--tint);
            box-shadow: 0 0 0 1px var(--orange) inset;
        }

        .party .line.name {
            font-weight: 700;
            font-size: 14.5px;
        }

        .party.gst-box {
            background: var(--orange-subtle);
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 14px 16px;
            transition: all .3s ease;
        }

        .party.gst-box:hover {
            border-color: var(--orange);
            box-shadow: 0 4px 12px var(--orange-shadow);
        }

        .gst-grid {
            display: flex;
            flex-direction: column;
            gap: 7px;
        }

        .gst-row {
            display: flex;
            justify-content: space-between;
            font-size: 12.5px;
        }

        .gst-row .k {
            color: var(--ink-soft);
        }

        .gst-row .v {
            font-family: var(--mono);
            font-weight: 600;
            outline: none;
            border-radius: 3px;
            padding: 0 3px;
            transition: all .2s ease;
        }

        .gst-row .v:focus {
            background: var(--tint);
            box-shadow: 0 0 0 1px var(--orange) inset;
        }

        .place-toggle {
            display: flex;
            gap: 6px;
            margin-top: 4px;
        }

        .place-toggle button {
            flex: 1;
            padding: 6px 6px;
            font-size: 11px;
            border-radius: 7px;
            justify-content: center;
        }

        .place-toggle button.active {
            background: linear-gradient(135deg, var(--orange-gradient-start), var(--orange));
            color: #fff;
            border-color: var(--orange);
        }

        table.items {
            position: relative;
            z-index: 1;
            width: calc(100% - 92px);
            margin: 8px 46px 0;
            border-collapse: collapse;
            font-size: 13px;
        }

        table.items thead th {
            text-align: left;
            font-family: var(--disp);
            font-size: 10px;
            letter-spacing: 1.1px;
            text-transform: uppercase;
            color: #fff;
            background: linear-gradient(135deg, var(--orange-gradient-start), var(--orange));
            padding: 11px 10px;
            font-weight: 600;
        }

        table.items thead th:first-child {
            border-radius: 9px 0 0 9px;
            text-align: center;
        }

        table.items thead th:last-child {
            border-radius: 0 9px 9px 0;
            text-align: right;
        }

        table.items thead th.qty,
        table.items thead th.price {
            text-align: right;
        }

        table.items tbody td {
            padding: 12px 10px;
            border-bottom: 1px solid var(--line);
            vertical-align: top;
            color: var(--ink);
        }

        table.items tbody tr {
            transition: all .12s ease;
        }

        table.items tbody tr:hover td {
            background: var(--tint);
        }

        table.items td.no {
            text-align: center;
            color: var(--ink-faint);
            font-family: var(--mono);
            width: 30px;
            font-size: 11.5px;
        }

        table.items td.qty,
        table.items td.price,
        table.items td.amount {
            text-align: right;
            font-family: var(--mono);
            white-space: nowrap;
            width: 84px;
        }

        table.items td.amount {
            font-weight: 700;
            color: var(--orange);
        }

        table.items td[contenteditable] {
            outline: none;
            border-radius: 5px;
            transition: all .2s ease;
        }

        table.items td[contenteditable]:focus {
            background: var(--tint);
            box-shadow: 0 0 0 1px var(--orange) inset;
        }

        table.items td.product {
            font-weight: 700;
            width: 120px;
        }

        table.items td.period {
            width: 100px;
            color: var(--ink-soft);
            font-family: var(--mono);
            font-size: 11.5px;
        }

        .row-actions {
            width: 54px;
            text-align: center;
            white-space: nowrap;
        }

        .row-actions button {
            padding: 3px 6px;
            font-size: 11px;
        }

        .add-row-wrap {
            position: relative;
            z-index: 1;
            padding: 12px 46px 0;
            display: flex;
            gap: 10px;
        }

        .add-row {
            flex: 1;
            border: 1.5px dashed var(--line-strong);
            background: transparent;
            color: var(--ink-soft);
            padding: 11px;
            border-radius: 10px;
            justify-content: center;
            transition: all .3s ease;
        }

        .add-row:hover {
            border-color: var(--orange);
            color: var(--orange);
            background: var(--tint);
        }

        .bottom {
            position: relative;
            z-index: 1;
            display: flex;
            justify-content: space-between;
            gap: 30px;
            padding: 32px 46px 10px;
            align-items: flex-start;
            flex-wrap: wrap;
        }

        .terms {
            flex: 1.3;
            min-width: 230px;
            font-size: 12px;
            color: var(--ink-soft);
            line-height: 1.7;
        }

        .terms h4,
        .bank h4 {
            font-family: var(--disp);
            font-size: 10.5px;
            text-transform: uppercase;
            letter-spacing: 1.3px;
            color: var(--orange);
            margin: 0 0 11px;
            font-weight: 700;
        }

        .terms ol {
            margin: 0;
            padding-left: 16px;
        }

        .terms li[contenteditable] {
            outline: none;
            padding: 1px 3px;
            border-radius: 4px;
            margin-bottom: 4px;
            transition: all .2s ease;
        }

        .terms li[contenteditable]:focus {
            background: var(--tint);
            box-shadow: 0 0 0 1px var(--orange) inset;
        }

        .notes {
            margin-top: 16px;
        }

        .notes textarea {
            width: 100%;
            min-height: 54px;
            resize: vertical;
            font-family: var(--body);
            font-size: 12px;
            color: var(--ink-soft);
            border: 1px dashed var(--line-strong);
            border-radius: 8px;
            padding: 8px 10px;
            background: transparent;
            outline: none;
            transition: all .3s ease;
        }

        .notes textarea:focus {
            border-color: var(--orange);
            background: var(--tint);
        }

        .bank {
            flex: 1;
            min-width: 220px;
            font-size: 12.5px;
            color: var(--ink);
            line-height: 1.85;
        }

        .bank .bline {
            display: flex;
            gap: 6px;
        }

        .bank .bline b {
            color: var(--ink-soft);
            font-weight: 600;
            min-width: 104px;
            display: inline-block;
        }

        .bank [contenteditable] {
            outline: none;
            border-radius: 4px;
            padding: 1px 3px;
            transition: all .2s ease;
        }

        .bank [contenteditable]:focus {
            background: var(--tint);
            box-shadow: 0 0 0 1px var(--orange) inset;
        }

        .qr-note {
            margin-top: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 11px;
            color: var(--ink-faint);
        }

        .qr-box {
            width: 54px;
            height: 54px;
            border-radius: 8px;
            border: 2px solid var(--orange);
            background: repeating-conic-gradient(var(--orange-subtle) 0% 25%, transparent 0% 50%) 50% / 10px 10px;
            flex-shrink: 0;
            transition: all .3s ease;
        }

        .qr-box:hover {
            transform: rotate(5deg) scale(1.05);
        }

        .totals {
            flex: 1;
            min-width: 240px;
        }

        .totals .trow {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
            padding: 8px 0;
            border-bottom: 1px solid var(--line);
        }

        .totals .trow .lbl {
            color: var(--ink-soft);
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .totals .trow .val {
            font-family: var(--mono);
            outline: none;
            text-align: right;
            min-width: 90px;
            border-radius: 5px;
            padding: 2px 5px;
            transition: all .2s ease;
        }

        .totals .trow .val:hover {
            background: var(--tint);
        }

        .totals .trow .val:focus {
            background: var(--tint);
            box-shadow: 0 0 0 1px var(--orange) inset;
        }

        .totals .trow.grand {
            border-bottom: none;
            margin-top: 8px;
            background: linear-gradient(135deg, var(--orange-gradient-start), var(--orange));
            color: #fff;
            padding: 16px 16px;
            border-radius: 12px;
            align-items: center;
            box-shadow: 0 10px 24px -8px var(--orange-shadow);
        }

        .totals .trow.grand .lbl {
            color: #fff;
            font-weight: 700;
            letter-spacing: .4px;
            font-size: 13px;
        }

        .totals .trow.grand .val {
            color: #fff;
            font-weight: 800;
            font-size: 19px;
        }

        .tax-rate {
            width: 36px;
            text-align: right;
            display: inline-block;
        }

        .words {
            font-size: 11px;
            color: var(--ink-faint);
            font-style: italic;
            margin-top: 8px;
            line-height: 1.5;
        }

        .currency-pick {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 8px;
        }

        .currency-pick label {
            font-size: 11px;
            color: var(--ink-faint);
        }

        .gst-split {
            display: none;
            flex-direction: column;
            gap: 0;
        }

        .gst-split.show {
            display: flex;
        }

        .footer {
            position: relative;
            z-index: 1;
            padding: 28px 46px 40px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 20px;
            flex-wrap: wrap;
        }

        .thanks {
            font-size: 13px;
            color: var(--ink-soft);
        }

        .thanks b {
            color: var(--ink);
        }

        .signature {
            text-align: center;
        }

        .signature .sigline {
            width: 170px;
            border-top: 2px solid var(--orange);
            margin-bottom: 6px;
        }

        .signature .lab {
            font-size: 10.5px;
            color: var(--ink-faint);
            letter-spacing: .6px;
            text-transform: uppercase;
            font-family: var(--disp);
        }

        .stamp {
            position: absolute;
            right: 58px;
            top: 300px;
            width: 104px;
            height: 104px;
            border: 2.5px solid var(--green);
            border-radius: 50%;
            color: var(--green);
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            font-weight: 800;
            font-size: 11.5px;
            letter-spacing: 1px;
            transform: rotate(-14deg);
            opacity: .85;
            pointer-events: none;
            text-transform: uppercase;
            line-height: 1.3;
            font-family: var(--disp);
            z-index: 2;
            background: rgba(34, 197, 94, 0.05);
        }

        .stamp.hidden {
            display: none;
        }

        .stamp.overdue {
            border-color: var(--orange);
            color: var(--orange);
            background: rgba(245, 124, 0, 0.05);
        }

        [contenteditable="true"]:empty:before {
            content: attr(data-placeholder);
            color: #5b5b66;
        }

        ::selection {
            background: var(--orange-subtle);
            color: var(--orange);
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--canvas);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--orange);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--orange-dark);
        }

        @media (max-width:760px) {
            .head {
                flex-direction: column;
            }

            .head-right {
                text-align: left;
                width: 100%;
            }

            .invoice-meta .row {
                justify-content: flex-start;
            }

            .parties {
                grid-template-columns: 1fr;
            }

            .bottom {
                flex-direction: column;
            }

            table.items {
                width: calc(100% - 32px);
                margin: 6px 16px 0;
                font-size: 12px;
            }

            .head,
            .parties,
            .footer,
            .add-row-wrap {
                padding-left: 16px !important;
                padding-right: 16px !important;
            }

            .divider {
                margin: 0 16px;
            }

            .stamp {
                display: none;
            }

            .watermark span {
                font-size: 64px;
            }

            .toolbar .hint {
                display: none;
            }

            .btns {
                width: 100%;
                justify-content: center;
            }

            .btns button {
                font-size: 11px;
                padding: 7px 10px;
            }
        }

        @media print {
            .back-btn,
            .print-btn,
            .sidebar {
                display: none !important;
            }

            body {
                background: #fff;
                padding: 0;
                margin-left: 0;
            }

            body.dark {
                background: #fff;
            }

            .toolbar {
                display: none;
            }

            .sheet {
                box-shadow: none;
                border-radius: 0;
                max-width: 100%;
            }

            button.danger-ghost,
            .row-actions {
                display: none !important;
            }

            .add-row-wrap {
                display: none;
            }

            .qr-note {
                display: none;
            }

            .stamp {
                display: block !important;
            }
        }
    </style>
</head>

<body>
    <div class="main-content" id="mainContent">
        <!-- MAIN FORM -->
        <form method="POST" action="" id="invoiceForm">
            <input type="hidden" name="save_invoice" value="1">
            <input type="hidden" name="invoice_number" id="form_invoice_number">
            <input type="hidden" name="quote_number" id="form_quote_number">
            <input type="hidden" name="customer_id" id="form_customer_id">
            <input type="hidden" name="invoice_date" id="form_invoice_date">
            <input type="hidden" name="due_date" id="form_due_date">
            <input type="hidden" name="status" id="form_status">
            <input type="hidden" name="currency" id="form_currency">
            <input type="hidden" name="company_name" id="form_company_name">
            <input type="hidden" name="company_address" id="form_company_address">
            <input type="hidden" name="company_contact" id="form_company_contact">
            <input type="hidden" name="company_email" id="form_company_email">
            <input type="hidden" name="customer_name" id="form_customer_name">
            <input type="hidden" name="customer_contact_person" id="form_customer_contact_person">
            <input type="hidden" name="customer_address" id="form_customer_address">
            <input type="hidden" name="supplier_gstin" id="form_supplier_gstin">
            <input type="hidden" name="buyer_gstin" id="form_buyer_gstin">
            <input type="hidden" name="place_of_supply" id="form_place_of_supply">
            <input type="hidden" name="hsn_sac_code" id="form_hsn_sac_code">
            <input type="hidden" name="gst_mode" id="form_gst_mode">
            <input type="hidden" name="subtotal" id="form_subtotal">
            <input type="hidden" name="discount" id="form_discount">
            <input type="hidden" name="taxable_value" id="form_taxable_value">
            <input type="hidden" name="cgst_rate" id="form_cgst_rate">
            <input type="hidden" name="sgst_rate" id="form_sgst_rate">
            <input type="hidden" name="igst_rate" id="form_igst_rate">
            <input type="hidden" name="cgst_amount" id="form_cgst_amount">
            <input type="hidden" name="sgst_amount" id="form_sgst_amount">
            <input type="hidden" name="igst_amount" id="form_igst_amount">
            <input type="hidden" name="other_charges" id="form_other_charges">
            <input type="hidden" name="grand_total" id="form_grand_total">
            <input type="hidden" name="amount_in_words" id="form_amount_in_words">
            <input type="hidden" name="bank_account_name" id="form_bank_account_name">
            <input type="hidden" name="bank_account_number" id="form_bank_account_number">
            <input type="hidden" name="bank_ifsc_code" id="form_bank_ifsc_code">
            <input type="hidden" name="bank_name" id="form_bank_name">
            <input type="hidden" name="bank_branch" id="form_bank_branch">
            <input type="hidden" name="terms_conditions" id="form_terms_conditions">
            <input type="hidden" name="notes" id="form_notes">
            <input type="hidden" name="client_name_footer" id="form_client_name_footer">
            <input type="hidden" name="items_json" id="form_items_json">
            <input type="hidden" name="bill_type" id="form_bill_type" value="Invoice">
            <input type="hidden" name="bill_number" id="form_bill_number">
            <input type="hidden" name="reference" id="form_reference">
        </form>

        <div class="invoice-wrapper">
            <div class="toolbar">
                <div class="hint"><span class="dot"></span>Click any text to edit · everything recalculates live</div>
                <div class="btns">
                    <select id="currencySel" title="Currency">
                        <option value="₹" selected>₹ INR</option>
                        <option value="$">$ USD</option>
                        <option value="€">€ EUR</option>
                        <option value="£">£ GBP</option>
                        <option value="AED">AED</option>
                    </select>
                    <button id="addItemTop">+ Add Item</button>
                    <button id="toggleStamp">Stamp</button>
                    <button id="toggleDark" class="ghost">🌓 Theme</button>
                    <button id="exportJson" class="ghost">⤓ Save Draft</button>
                    <button id="importJsonBtn" class="ghost">⤒ Load Draft</button>
                    <input type="file" id="importJson" accept="application/json" style="display:none;">
                    <button id="saveDatabaseBtn" class="primary"
                        style="background: linear-gradient(135deg, #22c55e, #16a34a); border-color: #22c55e; color: #fff;">💾
                        Save to Database</button>
                    <button class="primary" id="printBtn">⬇ Download PDF</button>
                </div>
            </div>

            <div class="sheet" id="sheet">
                <div class="watermark"><span>EBIZTECH</span></div>
                <div class="stamp hidden" id="stamp">PAID<br>ON TIME</div>

                <div class="head">
                    <div class="brand-stack">
                        <span class="tagline">E-Business Technology Solutions</span>
                    </div>
                    <div class="head-right">
                        <div class="status-badge unpaid" id="statusBadge"><span class="pulse"></span><span
                                id="statusLabel">Unpaid</span></div>
                        <div class="invoice-title">INVOICE<span>.</span></div>
                        <div class="invoice-meta">
                            <div class="row"><span class="lbl">Invoice No</span><span contenteditable="true"
                                    id="invNo">2026/1021</span></div>
                            <div class="row"><span class="lbl">Date</span><span contenteditable="true" id="dateField">08
                                    May 2026</span></div>
                            <div class="row"><span class="lbl">Quote No</span><span contenteditable="true"
                                    id="quoteNo">2025/1021</span></div>
                            <div class="row"><span class="lbl">Customer ID</span><span contenteditable="true"
                                    id="custId">PUN/106</span></div>
                            <div class="row"><span class="lbl">Due Date</span><span contenteditable="true"
                                    id="dueDate">17 May 2026</span></div>
                        </div>
                    </div>
                </div>

                <div class="divider"></div>

                <div class="parties">
                    <div class="party" id="companyParty">
                        <h4>From</h4>
                        <div class="line name" contenteditable="true" id="companyName">E Business Technology Solutions
                        </div>
                        <div class="line" contenteditable="true">Office No-89, D-Wing, 4th Floor</div>
                        <div class="line" contenteditable="true">Dhankawadi, Pune – 411043.</div>
                        <div class="line" contenteditable="true" id="companyContact">Contact: 77 55 97 97 97 / 92 70 40 97 97</div>
                        <div class="line" contenteditable="true" id="companyEmail">Email: info@ebiztech.in</div>
                    </div>
                    <div class="party" id="customerParty">
                        <h4>Bill To</h4>
                        <div class="line name" contenteditable="true" id="customerName">Blink Finance</div>
                        <div class="line" contenteditable="true" id="customerContact">Ashish Ghadge</div>
                        <div class="line" contenteditable="true">Sai Prasad Complex, A 001/002, Mira Bhayander Link Rd,</div>
                        <div class="line" contenteditable="true">beside Sagar Sangam Hotel, CHSL, Geeta Nagar,</div>
                        <div class="line" contenteditable="true">Mira Road East, Thane, Mira Bhayandar, Maharashtra 401107</div>
                    </div>
                    <div class="party gst-box">
                        <h4>GST Details</h4>
                        <div class="gst-grid">
                            <div class="gst-row"><span class="k">Supplier GSTIN</span><span class="v"
                                    contenteditable="true" id="supplierGSTIN">27AAMFE3315J1ZD</span></div>
                            <div class="gst-row"><span class="k">Buyer GSTIN</span><span class="v"
                                    contenteditable="true" id="buyerGSTIN" data-placeholder="—"></span></div>
                            <div class="gst-row"><span class="k">Place of Supply</span><span class="v"
                                    contenteditable="true" id="placeOfSupply">Maharashtra (27)</span></div>
                            <div class="gst-row"><span class="k">HSN/SAC</span><span class="v" contenteditable="true"
                                    id="hsnSac">998314</span></div>
                        </div>
                        <div class="place-toggle">
                            <button id="intraBtn" class="active">Intra-state</button>
                            <button id="interBtn">Inter-state</button>
                        </div>
                    </div>
                </div>

                <table class="items" id="itemsTable">
                    <thead>
                        <tr>
                            <th class="no">No.</th>
                            <th>Product</th>
                            <th>Description</th>
                            <th>Billing Period</th>
                            <th class="qty">Qty</th>
                            <th class="price">Price</th>
                            <th>Amount</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody">
                        <tr>
                            <td class="no">1</td>
                            <td class="product" contenteditable="true">Gateway</td>
                            <td contenteditable="true">32 Port GSM Gateway</td>
                            <td class="period" contenteditable="true">06 May 2026</td>
                            <td class="qty" contenteditable="true">1</td>
                            <td class="price" contenteditable="true">23000</td>
                            <td class="amount">23,000.00</td>
                            <td class="row-actions">
                                <button class="danger-ghost" onclick="duplicateRow(this)" title="Duplicate">⧉</button>
                                <button class="danger-ghost" onclick="removeRow(this)" title="Remove">✕</button>
                            </td>
                        </tr>
                        <tr>
                            <td class="no">2</td>
                            <td class="product" contenteditable="true">Dialer Server</td>
                            <td contenteditable="true">Dell PowerEdge-R710, 16 Core, 16GB RAM, 1TB HDD (Refurbished) — Dialer Server</td>
                            <td class="period" contenteditable="true">06 May 2026</td>
                            <td class="qty" contenteditable="true">1</td>
                            <td class="price" contenteditable="true">0</td>
                            <td class="amount">0.00</td>
                            <td class="row-actions">
                                <button class="danger-ghost" onclick="duplicateRow(this)" title="Duplicate">⧉</button>
                                <button class="danger-ghost" onclick="removeRow(this)" title="Remove">✕</button>
                            </td>
                        </tr>
                        <tr>
                            <td class="no">3</td>
                            <td class="product" contenteditable="true">AMC</td>
                            <td contenteditable="true">Domestic Call Center Open Source Omni-channel Contact Center Suite Software — Installation, Configuration and AMC</td>
                            <td class="period" contenteditable="true">06 May 2026</td>
                            <td class="qty" contenteditable="true">1</td>
                            <td class="price" contenteditable="true">0</td>
                            <td class="amount">0.00</td>
                            <td class="row-actions">
                                <button class="danger-ghost" onclick="duplicateRow(this)" title="Duplicate">⧉</button>
                                <button class="danger-ghost" onclick="removeRow(this)" title="Remove">✕</button>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="add-row-wrap">
                    <button class="add-row" id="addItemBottom">+ Add Item Row</button>
                </div>

                <div class="bottom">
                    <div class="terms">
                        <h4>Terms &amp; Conditions</h4>
                        <ol id="termsList">
                            <li contenteditable="true">Hardware warranty and support will be provided by the manufacturer as per manufacturer's policy.</li>
                            <li contenteditable="true">Payments should be made in favor of "E Business Technology Solutions".</li>
                            <li contenteditable="true">Services may be deactivated without prior notice if payment is not made on time.</li>
                            <li contenteditable="true">Payment can be made after deducting applicable taxes.</li>
                            <li contenteditable="true">Payment should be made within the defined credit period.</li>
                        </ol>
                        <div class="notes">
                            <h4 style="margin-top:18px;">Notes</h4>
                            <textarea id="notesArea"
                                data-placeholder="Add any extra notes or remarks for the client here…"
                                placeholder="Add any extra notes or remarks for the client here…"></textarea>
                        </div>
                    </div>

                    <div class="bank">
                        <h4>Bank Details</h4>
                        <div class="bline"><b>Account Name</b><span contenteditable="true" id="bankAccountName">E Business Technology Solutions</span></div>
                        <div class="bline"><b>Account No.</b><span contenteditable="true" id="bankAccountNo">610000000062910</span></div>
                        <div class="bline"><b>IFSC Code</b><span contenteditable="true" id="bankIFSC">SRCB0000038</span></div>
                        <div class="bline"><b>Bank Name</b><span contenteditable="true" id="bankName">Saraswat Co-Op Bank Ltd.</span></div>
                        <div class="bline"><b>Branch</b><span contenteditable="true" id="bankBranch">Tilak Road, Pune</span></div>
                        <div class="qr-note">
                            <div class="qr-box"></div>
                            <span>Scan to pay via UPI<br>(replace with your QR image when printing)</span>
                        </div>
                    </div>

                    <div class="totals">
                        <div class="currency-pick"><label>Currency:</label><b id="curDisplay">₹</b></div>
                        <div class="trow"><span class="lbl">Subtotal</span><span class="val" id="subtotal">23,000.00</span></div>
                        <div class="trow"><span class="lbl">Discount</span><span class="val" contenteditable="true" id="discount">0</span></div>
                        <div class="trow"><span class="lbl">Taxable Value</span><span class="val" id="lessDiscount">23,000.00</span></div>

                        <div class="gst-split show" id="intraSplit">
                            <div class="trow"><span class="lbl">CGST <span contenteditable="true" class="tax-rate" id="cgstRate">9</span>%</span><span class="val" id="cgstAmount">2,070.00</span></div>
                            <div class="trow"><span class="lbl">SGST <span contenteditable="true" class="tax-rate" id="sgstRate">9</span>%</span><span class="val" id="sgstAmount">2,070.00</span></div>
                        </div>
                        <div class="gst-split" id="interSplit">
                            <div class="trow"><span class="lbl">IGST <span contenteditable="true" class="tax-rate" id="igstRate">18</span>%</span><span class="val" id="igstAmount">4,140.00</span></div>
                        </div>

                        <div class="trow"><span class="lbl">Other Charges</span><span class="val" contenteditable="true" id="other">0</span></div>
                        <div class="trow grand"><span class="lbl">TOTAL</span><span class="val" id="grandTotal">₹27,140.00</span></div>
                        <div class="words" id="amountWords">Amount in words: Rupees Twenty-Seven Thousand One Hundred Forty Only</div>
                    </div>
                </div>

                <div class="footer">
                    <div class="thanks">Thank you for your business, <b contenteditable="true" id="clientNameFooter">Blink Finance</b>.<br>For queries regarding this invoice, contact <span contenteditable="true">info@ebiztech.in</span></div>
                </div>

            </div>
        </div>
    </div>

    <script>
        // All existing JavaScript functions
        function fmt(n) {
            n = isNaN(n) ? 0 : n;
            return n.toLocaleString('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function parseNum(text) {
            const v = parseFloat(String(text).replace(/,/g, '').trim());
            return isNaN(v) ? 0 : v;
        }
        let CUR = '₹';
        let GST_MODE = 'intra';

        function renumber() {
            document.querySelectorAll('#itemsBody tr').forEach((tr, i) => {
                tr.querySelector('.no').textContent = i + 1;
            });
        }

        const ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        const tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

        function twoDigits(n) {
            if (n < 20) return ones[n];
            return tens[Math.floor(n / 10)] + (n % 10 ? ' ' + ones[n % 10] : '');
        }

        function threeDigits(n) {
            if (n < 100) return twoDigits(n);
            return ones[Math.floor(n / 100)] + ' Hundred' + (n % 100 ? ' ' + twoDigits(n % 100) : '');
        }

        function numToWords(num) {
            num = Math.round(num);
            if (num === 0) return 'Zero';
            let str = '';
            const crore = Math.floor(num / 10000000);
            num %= 10000000;
            const lakh = Math.floor(num / 100000);
            num %= 100000;
            const thousand = Math.floor(num / 1000);
            num %= 1000;
            const hundred = num;
            if (crore) str += threeDigits(crore) + ' Crore ';
            if (lakh) str += threeDigits(lakh) + ' Lakh ';
            if (thousand) str += threeDigits(thousand) + ' Thousand ';
            if (hundred) str += threeDigits(hundred);
            return str.trim();
        }

        function recalc() {
            let subtotal = 0;
            document.querySelectorAll('#itemsBody tr').forEach(tr => {
                const qty = parseNum(tr.querySelector('.qty').textContent);
                const price = parseNum(tr.querySelector('.price').textContent);
                const amt = qty * price;
                tr.querySelector('.amount').textContent = fmt(amt);
                subtotal += amt;
            });
            const discount = parseNum(document.getElementById('discount').textContent);
            const other = parseNum(document.getElementById('other').textContent);
            const lessDiscount = subtotal - discount;

            let taxAmount = 0;
            if (GST_MODE === 'intra') {
                const cgstRate = parseNum(document.getElementById('cgstRate').textContent);
                const sgstRate = parseNum(document.getElementById('sgstRate').textContent);
                const cgstAmt = lessDiscount * (cgstRate / 100);
                const sgstAmt = lessDiscount * (sgstRate / 100);
                document.getElementById('cgstAmount').textContent = fmt(cgstAmt);
                document.getElementById('sgstAmount').textContent = fmt(sgstAmt);
                taxAmount = cgstAmt + sgstAmt;
            } else {
                const igstRate = parseNum(document.getElementById('igstRate').textContent);
                const igstAmt = lessDiscount * (igstRate / 100);
                document.getElementById('igstAmount').textContent = fmt(igstAmt);
                taxAmount = igstAmt;
            }

            const grandTotal = lessDiscount + taxAmount + other;

            document.getElementById('subtotal').textContent = fmt(subtotal);
            document.getElementById('lessDiscount').textContent = fmt(lessDiscount);
            document.getElementById('grandTotal').textContent = CUR + fmt(grandTotal);
            document.getElementById('amountWords').textContent =
                'Amount in words: ' + (CUR === '₹' ? 'Rupees ' : '') + numToWords(grandTotal) + (CUR === '₹' ? ' Only' : '');
        }

        function makeRow() {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="no">0</td>
                <td class="product" contenteditable="true" data-placeholder="Product">New Item</td>
                <td contenteditable="true" data-placeholder="Description"></td>
                <td class="period" contenteditable="true" data-placeholder="dd Mon yyyy"></td>
                <td class="qty" contenteditable="true">1</td>
                <td class="price" contenteditable="true">0</td>
                <td class="amount">0.00</td>
                <td class="row-actions">
                    <button class="danger-ghost" onclick="duplicateRow(this)" title="Duplicate">⧉</button>
                    <button class="danger-ghost" onclick="removeRow(this)" title="Remove">✕</button>
                </td>
            `;
            return tr;
        }

        function addRow(atTop) {
            const body = document.getElementById('itemsBody');
            const row = makeRow();
            if (atTop && body.firstChild) {
                body.insertBefore(row, body.firstChild);
            } else {
                body.appendChild(row);
            }
            renumber();
            recalc();
            row.querySelector('.product').focus();
        }

        function duplicateRow(btn) {
            const tr = btn.closest('tr');
            const clone = tr.cloneNode(true);
            tr.after(clone);
            renumber();
            recalc();
        }

        function removeRow(btn) {
            const body = document.getElementById('itemsBody');
            if (body.children.length <= 1) {
                return;
            }
            btn.closest('tr').remove();
            renumber();
            recalc();
        }

        let statusIdx = 0;
        const statuses = [
            { cls: 'unpaid', label: 'Unpaid' },
            { cls: 'paid', label: 'Paid' },
            { cls: 'overdue', label: 'Overdue' }
        ];

        function bindSheetEvents() {
            document.getElementById('itemsTable').addEventListener('input', (e) => {
                if (e.target.classList.contains('qty') || e.target.classList.contains('price')) {
                    recalc();
                }
            });
            document.getElementById('discount').addEventListener('input', recalc);
            document.getElementById('other').addEventListener('input', recalc);
            ['cgstRate', 'sgstRate', 'igstRate'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.addEventListener('input', recalc);
            });

            document.getElementById('toggleStamp').onclick = () => {
                document.getElementById('stamp').classList.toggle('hidden');
            };

            document.getElementById('toggleDark').onclick = () => {
                document.body.classList.toggle('dark');
                const themeBtn = document.querySelector(".theme-toggle");
                if (document.body.classList.contains('dark')) {
                    localStorage.setItem('theme', 'dark');
                    if (themeBtn) {
                        themeBtn.innerHTML = '<span>☀️</span><span class="text">Light Mode</span>';
                    }
                    document.getElementById('toggleDark').innerHTML = '☀️ Light';
                } else {
                    localStorage.setItem('theme', 'light');
                    if (themeBtn) {
                        themeBtn.innerHTML = '<span>🌙</span><span class="text">Dark Mode</span>';
                    }
                    document.getElementById('toggleDark').innerHTML = '🌓 Theme';
                }
            };

            document.getElementById('statusBadge').addEventListener('click', () => {
                statusIdx = (statusIdx + 1) % statuses.length;
                const badge = document.getElementById('statusBadge');
                badge.className = 'status-badge ' + statuses[statusIdx].cls;
                document.getElementById('statusLabel').textContent = statuses[statusIdx].label;
                const stamp = document.getElementById('stamp');
                if (statuses[statusIdx].cls === 'paid') {
                    stamp.innerHTML = 'PAID<br>ON TIME';
                    stamp.classList.remove('overdue');
                    stamp.classList.remove('hidden');
                } else if (statuses[statusIdx].cls === 'overdue') {
                    stamp.innerHTML = 'PAYMENT<br>OVERDUE';
                    stamp.classList.add('overdue');
                    stamp.classList.remove('hidden');
                } else {
                    stamp.classList.add('hidden');
                }
            });

            document.getElementById('intraBtn').addEventListener('click', () => {
                GST_MODE = 'intra';
                document.getElementById('intraBtn').classList.add('active');
                document.getElementById('interBtn').classList.remove('active');
                document.getElementById('intraSplit').classList.add('show');
                document.getElementById('interSplit').classList.remove('show');
                recalc();
            });
            document.getElementById('interBtn').addEventListener('click', () => {
                GST_MODE = 'inter';
                document.getElementById('interBtn').classList.add('active');
                document.getElementById('intraBtn').classList.remove('active');
                document.getElementById('interSplit').classList.add('show');
                document.getElementById('intraSplit').classList.remove('show');
                recalc();
            });
        }

        document.getElementById('addItemTop').addEventListener('click', () => addRow(false));
        document.getElementById('addItemBottom').addEventListener('click', () => addRow(false));
        document.getElementById('printBtn').addEventListener('click', () => window.print());

        document.getElementById('currencySel').addEventListener('change', (e) => {
            CUR = e.target.value;
            document.getElementById('curDisplay').textContent = CUR;
            recalc();
        });

        document.addEventListener('keydown', (e) => {
            const el = e.target;
            if (el && el.isContentEditable && e.key === 'Enter' && el.tagName !== 'LI') {
                e.preventDefault();
                el.blur();
            }
        });

        bindSheetEvents();

        function collectData() {
            const items = Array.from(document.querySelectorAll('#itemsBody tr')).map(tr => ({
                product: tr.querySelector('.product').textContent,
                desc: tr.children[2].textContent,
                period: tr.querySelector('.period').textContent,
                qty: tr.querySelector('.qty').textContent,
                price: tr.querySelector('.price').textContent,
            }));
            return {
                html: document.getElementById('sheet').outerHTML,
                items,
                currency: CUR,
                gstMode: GST_MODE,
                savedAt: new Date().toISOString()
            };
        }

        document.getElementById('exportJson').addEventListener('click', () => {
            const data = collectData();
            const blob = new Blob([JSON.stringify(data, null, 2)], {
                type: 'application/json'
            });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'invoice-draft-' + (document.getElementById('invNo').textContent || 'untitled') + '.json';
            a.click();
            URL.revokeObjectURL(url);
        });

        document.getElementById('importJsonBtn').addEventListener('click', () => {
            document.getElementById('importJson').click();
        });

        document.getElementById('importJson').addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = (ev) => {
                try {
                    const data = JSON.parse(ev.target.result);
                    if (data.html) {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(data.html, 'text/html');
                        const newSheet = doc.getElementById('sheet');
                        if (newSheet) {
                            document.getElementById('sheet').replaceWith(newSheet);
                            newSheet.id = 'sheet';
                            bindSheetEvents();
                            recalc();
                        }
                    }
                } catch (err) {
                    alert('Could not read that file as a valid invoice draft.');
                }
            };
            reader.readAsText(file);
        });

        function collectInvoiceData() {
            const items = [];
            document.querySelectorAll('#itemsBody tr').forEach((tr, index) => {
                const productEl = tr.querySelector('.product');
                const descriptionEl = tr.children[2];
                const periodEl = tr.querySelector('.period');
                const qtyEl = tr.querySelector('.qty');
                const priceEl = tr.querySelector('.price');
                const amountEl = tr.querySelector('.amount');

                if (productEl && descriptionEl && periodEl && qtyEl && priceEl && amountEl) {
                    items.push({
                        number: index + 1,
                        product: productEl.textContent.trim(),
                        description: descriptionEl.textContent.trim(),
                        period: periodEl.textContent.trim(),
                        quantity: parseFloat(qtyEl.textContent.replace(/,/g, '')) || 0,
                        price: parseFloat(priceEl.textContent.replace(/,/g, '')) || 0,
                        amount: parseFloat(amountEl.textContent.replace(/,/g, '')) || 0
                    });
                }
            });

            const invNo = document.getElementById('invNo');
            const dateField = document.getElementById('dateField');
            const quoteNo = document.getElementById('quoteNo');
            const custId = document.getElementById('custId');
            const dueDate = document.getElementById('dueDate');
            const statusLabel = document.getElementById('statusLabel');
            const curDisplay = document.getElementById('curDisplay');
            const companyName = document.getElementById('companyName');
            const companyContact = document.getElementById('companyContact');
            const companyEmail = document.getElementById('companyEmail');
            const customerName = document.getElementById('customerName');
            const customerContact = document.getElementById('customerContact');
            const supplierGSTIN = document.getElementById('supplierGSTIN');
            const buyerGSTIN = document.getElementById('buyerGSTIN');
            const placeOfSupply = document.getElementById('placeOfSupply');
            const hsnSac = document.getElementById('hsnSac');
            const bankAccountName = document.getElementById('bankAccountName');
            const bankAccountNo = document.getElementById('bankAccountNo');
            const bankIFSC = document.getElementById('bankIFSC');
            const bankName = document.getElementById('bankName');
            const bankBranch = document.getElementById('bankBranch');
            const clientNameFooter = document.getElementById('clientNameFooter');
            const notesArea = document.getElementById('notesArea');

            const companyParty = document.getElementById('companyParty');
            const companyLines = companyParty ? companyParty.querySelectorAll('.line') : [];
            let companyAddress = '';
            if (companyLines.length >= 3) {
                companyAddress = companyLines[1].textContent.trim() + ', ' + companyLines[2].textContent.trim();
            }

            const customerParty = document.getElementById('customerParty');
            const customerLines = customerParty ? customerParty.querySelectorAll('.line') : [];
            let customerAddress = '';
            if (customerLines.length >= 5) {
                customerAddress = customerLines[2].textContent.trim() + ', ' +
                    customerLines[3].textContent.trim() + ', ' +
                    customerLines[4].textContent.trim();
            } else if (customerLines.length >= 3) {
                customerAddress = customerLines[2].textContent.trim();
            }

            const data = {
                invoice_number: invNo ? invNo.textContent.trim() : '',
                quote_number: quoteNo ? quoteNo.textContent.trim() : '',
                customer_id: custId ? custId.textContent.trim() : '',
                invoice_date: dateField ? dateField.textContent.trim() : '',
                due_date: dueDate ? dueDate.textContent.trim() : '',
                status: statusLabel ? statusLabel.textContent.trim().toLowerCase() : 'unpaid',
                currency: curDisplay ? curDisplay.textContent.trim() : '₹',
                company_name: companyName ? companyName.textContent.trim() : '',
                company_address: companyAddress,
                company_contact: companyContact ? companyContact.textContent.trim() : '',
                company_email: companyEmail ? companyEmail.textContent.trim() : '',
                customer_name: customerName ? customerName.textContent.trim() : '',
                customer_contact_person: customerContact ? customerContact.textContent.trim() : '',
                customer_address: customerAddress,
                supplier_gstin: supplierGSTIN ? supplierGSTIN.textContent.trim() : '',
                buyer_gstin: buyerGSTIN ? buyerGSTIN.textContent.trim() : '',
                place_of_supply: placeOfSupply ? placeOfSupply.textContent.trim() : '',
                hsn_sac_code: hsnSac ? hsnSac.textContent.trim() : '',
                gst_mode: GST_MODE,
                subtotal: parseFloat((document.getElementById('subtotal')?.textContent || '0').replace(/,/g, '')) || 0,
                discount: parseFloat((document.getElementById('discount')?.textContent || '0').replace(/,/g, '')) || 0,
                taxable_value: parseFloat((document.getElementById('lessDiscount')?.textContent || '0').replace(/,/g, '')) || 0,
                cgst_rate: parseFloat((document.getElementById('cgstRate')?.textContent || '0')) || 0,
                sgst_rate: parseFloat((document.getElementById('sgstRate')?.textContent || '0')) || 0,
                igst_rate: parseFloat((document.getElementById('igstRate')?.textContent || '0')) || 0,
                cgst_amount: parseFloat((document.getElementById('cgstAmount')?.textContent || '0').replace(/,/g, '')) || 0,
                sgst_amount: parseFloat((document.getElementById('sgstAmount')?.textContent || '0').replace(/,/g, '')) || 0,
                igst_amount: parseFloat((document.getElementById('igstAmount')?.textContent || '0').replace(/,/g, '')) || 0,
                other_charges: parseFloat((document.getElementById('other')?.textContent || '0').replace(/,/g, '')) || 0,
                grand_total: parseFloat((document.getElementById('grandTotal')?.textContent || '0').replace(/[^0-9.]/g, '')) || 0,
                amount_in_words: (document.getElementById('amountWords')?.textContent || '').replace('Amount in words: ', ''),
                bank_name: bankName ? bankName.textContent.trim() : '',
                bank_account_name: bankAccountName ? bankAccountName.textContent.trim() : '',
                bank_account_number: bankAccountNo ? bankAccountNo.textContent.trim() : '',
                bank_ifsc_code: bankIFSC ? bankIFSC.textContent.trim() : '',
                bank_branch: bankBranch ? bankBranch.textContent.trim() : '',
                terms_conditions: Array.from(document.querySelectorAll('#termsList li')).map(li => li.textContent.trim()).join('\n'),
                notes: notesArea ? notesArea.value : '',
                client_name_footer: clientNameFooter ? clientNameFooter.textContent.trim() : '',
                items_json: JSON.stringify(items),
                bill_type: 'Invoice',
                bill_number: invNo ? invNo.textContent.trim() : '',
                reference: quoteNo ? quoteNo.textContent.trim() : ''
            };

            return data;
        }

        // Save Database function
        document.getElementById('saveDatabaseBtn').addEventListener('click', function(e) {
            e.preventDefault();
            
            if (!confirm('💾 Save this invoice to database?')) return;
            
            try {
                const form = document.getElementById('invoiceForm');
                
                // Collect invoice data
                const data = collectInvoiceData();
                
                // Set all form hidden fields
                for (const key in data) {
                    if (data.hasOwnProperty(key)) {
                        const field = document.getElementById('form_' + key);
                        if (field) {
                            field.value = data[key];
                        }
                    }
                }
                
                // Show saving state
                const btn = this;
                const originalText = btn.innerHTML;
                btn.innerHTML = '⏳ Saving...';
                btn.disabled = true;
                
                // Submit form
                form.submit();
                
                // Reset button after timeout (in case of errors)
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }, 3000);
                
            } catch (error) {
                alert('❌ Error: ' + error.message);
                console.error('Save error:', error);
                document.getElementById('saveDatabaseBtn').innerHTML = '💾 Save to Database';
                document.getElementById('saveDatabaseBtn').disabled = false;
            }
        });

        // Theme and sidebar sync
        document.addEventListener("DOMContentLoaded", function () {
            const mainContent = document.getElementById("mainContent");

            // Apply sidebar state
            if (localStorage.getItem("sidebarState") === "collapsed" && mainContent) {
                mainContent.classList.add("expanded");
            }

            // Apply theme from localStorage
            if (localStorage.getItem('theme') === 'dark') {
                document.body.classList.add('dark');
                const toggleDarkBtn = document.getElementById('toggleDark');
                if (toggleDarkBtn) {
                    toggleDarkBtn.innerHTML = '☀️ Light';
                }
            }

            // Sidebar toggle listener
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
        });

        recalc();
    </script>
</body>

</html>