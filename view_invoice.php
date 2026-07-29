<?php
session_start();
include 'db_connection.php';
include 'session_check.php';

$invoice_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($invoice_id == 0) {
    header("Location: bill.php");
    exit();
}

// Fetch invoice details
$query = "SELECT * FROM invoices WHERE id = $invoice_id";
$result = mysqli_query($conn, $query);
$invoice = mysqli_fetch_assoc($result);

if (!$invoice) {
    die("Invoice not found");
}

// Parse items JSON
$items = json_decode($invoice['items_json'], true);
if (!$items) {
    $items = [];
}

include 'sidebar.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?= htmlspecialchars($invoice['invoice_number']) ?> - EbizTech</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box
        }

        :root {
            --orange: #f57c00;
            --orange-light: #ffb74d;
            --orange-dark: #e65100;
            --orange-gradient-start: #ff9800;
            --orange-gradient-end: #f57c00;
            --orange-subtle: rgba(255, 152, 0, 0.08);
            --orange-shadow: rgba(245, 124, 0, 0.15);
            --black: #0a0a0a;
            --card: #1a0a0a;
            --card-hover: #2d0a0a;
            --border: #3b0a0a;
            --border-active: rgba(255, 152, 0, .5);
            --text: #f8fafc;
            --text-dim: #9c9ca8;
            --text-muted: #6b6b75;
            --green: #4ade80;
            --green-bg: rgba(74, 222, 128, .1);
            --green-border: rgba(74, 222, 128, .3);
            --yellow: #fbbf24;
            --yellow-bg: rgba(251, 191, 36, .1);
            --blue: #60a5fa;
            --sidebar-width: 280px;
        }

        body {
            background: var(--black);
            color: var(--text);
            font-family: 'Segoe UI', sans-serif;
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }

        .main-content {
            padding: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .invoice-wrapper {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 10px 30px var(--orange-shadow);
        }

        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--orange);
        }

        .invoice-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--orange);
        }

        .invoice-title small {
            font-size: 16px;
            color: var(--text-dim);
            font-weight: 400;
        }

        .status-badge {
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-paid {
            background: var(--green-bg);
            color: var(--green);
            border: 1px solid var(--green-border);
        }

        .status-unpaid {
            background: var(--orange-subtle);
            color: var(--orange);
            border: 1px solid rgba(255, 152, 0, .3);
        }

        .status-partial {
            background: var(--yellow-bg);
            color: var(--yellow);
            border: 1px solid rgba(251, 191, 36, .3);
        }

        .status-draft {
            background: rgba(96, 165, 250, .1);
            color: var(--blue);
            border: 1px solid rgba(96, 165, 250, .3);
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .info-box {
            background: var(--orange-subtle);
            border-radius: 8px;
            padding: 15px;
        }

        .info-box h4 {
            color: var(--orange);
            font-size: 12px;
            text-transform: uppercase;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }

        .info-box p {
            color: var(--text);
            line-height: 1.6;
        }

        .info-box .label {
            color: var(--text-dim);
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        thead {
            background: var(--orange);
            color: #fff;
        }

        th {
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
        }

        td {
            padding: 12px 15px;
            border-bottom: 1px solid var(--border);
        }

        tbody tr:hover {
            background: var(--card-hover);
        }

        .text-right {
            text-align: right;
        }

        .totals {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid var(--orange);
            max-width: 450px;
            margin-left: auto;
        }

        .totals .row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
        }

        .totals .grand-total {
            font-size: 20px;
            font-weight: 700;
            color: var(--orange);
            border-top: 2px solid var(--border);
            padding-top: 10px;
            margin-top: 10px;
        }

        .back-btn {
            display: inline-block;
            padding: 10px 20px;
            background: var(--orange);
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background: var(--orange-dark);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px var(--orange-shadow);
        }

        .print-btn {
            display: inline-block;
            padding: 10px 20px;
            background: #2d6bcf;
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-weight: 600;
            margin-left: 10px;
            transition: all 0.3s ease;
        }

        .print-btn:hover {
            background: #1a4f9e;
            transform: translateY(-2px);
        }

        .action-bar {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }

        @media print {

            .back-btn,
            .print-btn,
            .sidebar {
                display: none !important;
            }

            body {
                margin-left: 0 !important;
            }

            .main-content {
                padding: 10px;
            }

            .invoice-wrapper {
                box-shadow: none;
                border: none;
            }

            .mobile-menu-toggle {
                display: none !important;
            }
        }

        @media (max-width: 768px) {
            body {
                margin-left: 85px;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .invoice-header {
                flex-direction: column;
                gap: 15px;
            }

            .totals {
                max-width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="main-content">
        <div class="invoice-wrapper">
            <div class="invoice-header">
                <div>
                    <div class="invoice-title">
                        INVOICE <small>#<?= htmlspecialchars($invoice['invoice_number']) ?></small>
                    </div>
                    <?php if ($invoice['quote_number']): ?>
                        <div style="color: var(--text-dim); font-size: 13px; margin-top: 5px;">
                            Quote: <?= htmlspecialchars($invoice['quote_number']) ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div>
                    <span class="status-badge status-<?= strtolower($invoice['status']) ?>">
                        <?= htmlspecialchars($invoice['status']) ?>
                    </span>
                    <div style="margin-top: 8px; font-size: 13px; color: var(--text-dim);">
                        Date: <?= date('d M Y', strtotime($invoice['invoice_date'])) ?>
                    </div>
                    <?php if ($invoice['due_date']): ?>
                        <div style="font-size: 13px; color: var(--text-dim);">
                            Due: <?= date('d M Y', strtotime($invoice['due_date'])) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="info-grid">
                <div class="info-box">
                    <h4>From</h4>
                    <p><strong><?= htmlspecialchars($invoice['company_name']) ?></strong></p>
                    <p><?= nl2br(htmlspecialchars($invoice['company_address'])) ?></p>
                    <p><?= htmlspecialchars($invoice['company_contact']) ?></p>
                    <p><?= htmlspecialchars($invoice['company_email']) ?></p>
                    <?php if ($invoice['supplier_gstin']): ?>
                        <p><span class="label">GST:</span> <?= htmlspecialchars($invoice['supplier_gstin']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="info-box">
                    <h4>Bill To</h4>
                    <p><strong><?= htmlspecialchars($invoice['customer_name']) ?></strong></p>
                    <p><?= nl2br(htmlspecialchars($invoice['customer_address'])) ?></p>
                    <?php if ($invoice['customer_contact_person']): ?>
                        <p>Attn: <?= htmlspecialchars($invoice['customer_contact_person']) ?></p>
                    <?php endif; ?>
                    <?php if ($invoice['buyer_gstin']): ?>
                        <p><span class="label">GST:</span> <?= htmlspecialchars($invoice['buyer_gstin']) ?></p>
                    <?php endif; ?>
                    <?php if ($invoice['place_of_supply']): ?>
                        <p><span class="label">Place of Supply:</span> <?= htmlspecialchars($invoice['place_of_supply']) ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($items)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Description</th>
                            <th>Period</th>
                            <th class="text-right">Qty</th>
                            <th class="text-right">Price</th>
                            <th class="text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $index => $item): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= htmlspecialchars($item['product'] ?? '') ?></td>
                                <td><?= htmlspecialchars($item['desc'] ?? '') ?></td>
                                <td><?= htmlspecialchars($item['period'] ?? '') ?></td>
                                <td class="text-right"><?= $item['qty'] ?? 0 ?></td>
                                <td class="text-right">₹<?= number_format($item['price'] ?? 0, 2) ?></td>
                                <td class="text-right">₹<?= number_format($item['amount'] ?? 0, 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <div class="totals">
                <div class="row">
                    <span>Subtotal</span>
                    <span>₹<?= number_format($invoice['subtotal'] ?? 0, 2) ?></span>
                </div>

                <?php if (($invoice['discount'] ?? 0) > 0): ?>
                    <div class="row" style="color: var(--orange);">
                        <span>Discount</span>
                        <span>- ₹<?= number_format($invoice['discount'] ?? 0, 2) ?></span>
                    </div>
                <?php endif; ?>

                <div class="row" style="border-bottom: 1px solid var(--border); padding-bottom: 10px;">
                    <span>Taxable Value</span>
                    <span>₹<?= number_format($invoice['taxable_value'] ?? 0, 2) ?></span>
                </div>

                <?php if (($invoice['cgst_amount'] ?? 0) > 0): ?>
                    <div class="row">
                        <span>CGST (<?= number_format($invoice['cgst_rate'] ?? 0, 2) ?>%)</span>
                        <span>₹<?= number_format($invoice['cgst_amount'] ?? 0, 2) ?></span>
                    </div>
                <?php endif; ?>

                <?php if (($invoice['sgst_amount'] ?? 0) > 0): ?>
                    <div class="row">
                        <span>SGST (<?= number_format($invoice['sgst_rate'] ?? 0, 2) ?>%)</span>
                        <span>₹<?= number_format($invoice['sgst_amount'] ?? 0, 2) ?></span>
                    </div>
                <?php endif; ?>

                <?php if (($invoice['igst_amount'] ?? 0) > 0): ?>
                    <div class="row">
                        <span>IGST (<?= number_format($invoice['igst_rate'] ?? 0, 2) ?>%)</span>
                        <span>₹<?= number_format($invoice['igst_amount'] ?? 0, 2) ?></span>
                    </div>
                <?php endif; ?>

                <?php if (($invoice['other_charges'] ?? 0) > 0): ?>
                    <div class="row">
                        <span>Other Charges</span>
                        <span>₹<?= number_format($invoice['other_charges'] ?? 0, 2) ?></span>
                    </div>
                <?php endif; ?>

                <div class="row grand-total">
                    <span>Grand Total</span>
                    <span>₹<?= number_format($invoice['grand_total'] ?? 0, 2) ?></span>
                </div>

                <?php if ($invoice['amount_in_words']): ?>
                    <div style="font-size: 12px; color: var(--text-dim); margin-top: 10px; font-style: italic;">
                        <?= htmlspecialchars($invoice['amount_in_words']) ?>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($invoice['terms_conditions']): ?>
                <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid var(--border);">
                    <h4 style="color: var(--orange); font-size: 12px; text-transform: uppercase; margin-bottom: 8px;">Terms
                        & Conditions</h4>
                    <p style="color: var(--text-dim); font-size: 13px; line-height: 1.6;">
                        <?= nl2br(htmlspecialchars($invoice['terms_conditions'])) ?>
                    </p>
                </div>
            <?php endif; ?>

            <?php if ($invoice['notes']): ?>
                <div style="margin-top: 15px;">
                    <h4 style="color: var(--orange); font-size: 12px; text-transform: uppercase; margin-bottom: 8px;">Notes
                    </h4>
                    <p style="color: var(--text-dim); font-size: 13px; line-height: 1.6;">
                        <?= nl2br(htmlspecialchars($invoice['notes'])) ?>
                    </p>
                </div>
            <?php endif; ?>

            <?php if ($invoice['bank_account_name']): ?>
                <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid var(--border);">
                    <h4 style="color: var(--orange); font-size: 12px; text-transform: uppercase; margin-bottom: 8px;">Bank
                        Details</h4>
                    <div
                        style="display: grid; grid-template-columns: 1fr 1fr; gap: 5px; font-size: 13px; color: var(--text);">
                        <div><span style="color: var(--text-dim);">Account Name:</span>
                            <?= htmlspecialchars($invoice['bank_account_name']) ?></div>
                        <div><span style="color: var(--text-dim);">Account No.:</span>
                            <?= htmlspecialchars($invoice['bank_account_number']) ?></div>
                        <div><span style="color: var(--text-dim);">IFSC:</span>
                            <?= htmlspecialchars($invoice['bank_ifsc_code']) ?></div>
                        <div><span style="color: var(--text-dim);">Bank:</span>
                            <?= htmlspecialchars($invoice['bank_name']) ?></div>
                        <div><span style="color: var(--text-dim);">Branch:</span>
                            <?= htmlspecialchars($invoice['bank_branch']) ?></div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="action-bar">
                <a href="bill.php" class="back-btn">← Back to Dashboard</a>
                <button onclick="window.print()" class="print-btn">🖨 Print</button>
            </div>
        </div>
    </div>

    <script>
        // Theme sync
        document.addEventListener("DOMContentLoaded", function () {
            if (localStorage.getItem('theme') === 'dark') {
                document.body.classList.add('dark');
            }

            // Sidebar state
            const mainContent = document.querySelector('.main-content');
            if (localStorage.getItem("sidebarState") === "collapsed") {
                if (mainContent) {
                    mainContent.classList.add("expanded");
                }
            }
        });
    </script>
</body>

</html>