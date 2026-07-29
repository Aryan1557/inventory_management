<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$db_host = 'localhost';
$db_user = 'root';
$db_pass = 'ebiztech99';
$db_name = 'inventory_management';

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
if (!$conn) {
  die("Connection failed: " . mysqli_connect_error());
}

// Get all bills from unified view
$bills_query = "SELECT * FROM unified_bills ORDER BY created_at DESC";
$bills_result = mysqli_query($conn, $bills_query);

if (!$bills_result) {
  die("Query failed: " . mysqli_error($conn));
}

// Group bills by customer
$clients = [];

while ($row = mysqli_fetch_assoc($bills_result)) {
  $client_name = trim($row['customer_name']) ?: 'Unknown Client';
  $client_key = strtolower($client_name);

  if (!isset($clients[$client_key])) {
    $clients[$client_key] = [
      'name' => $client_name,
      'contact' => $row['customer_contact_person'] ?? '',
      'email' => $row['customer_email'] ?? '',
      'phone' => $row['customer_phone'] ?? '',
      'address' => $row['customer_address'] ?? '',
      'gst' => $row['customer_gst'] ?? '',
      'company_name' => $row['company_name'] ?? '',
      'bills' => [],
      'total_amount' => 0,
      'paid_amount' => 0,
      'outstanding_amount' => 0
    ];
  }

  $bill_total = floatval($row['grand_total'] ?? 0);
  $bill_status = strtolower($row['status'] ?? 'draft');
  $bill_type = $row['bill_type'] ?? 'unknown';

  $clients[$client_key]['bills'][] = [
    'id' => $row['id'],
    'bill_type' => $bill_type,
    'bill_number' => $row['bill_number'] ?? '',
    'bill_date' => $row['bill_date'] ?? '',
    'due_date' => $row['due_date'] ?? '',
    'status' => $bill_status,
    'total' => $bill_total,
    'currency' => $row['currency'] ?? '₹'
  ];

  $clients[$client_key]['total_amount'] += $bill_total;

  if ($bill_status === 'paid') {
    $clients[$client_key]['paid_amount'] += $bill_total;
  }
}

// Calculate outstanding
foreach ($clients as &$client) {
  $client['outstanding_amount'] = $client['total_amount'] - $client['paid_amount'];
}
unset($client);

// Sort clients alphabetically
ksort($clients);

// Calculate statistics
$total_clients = count($clients);
$total_bills = 0;
foreach ($clients as $c) {
  $total_bills += count($c['bills']);
}

$total_unpaid = 0;
$total_paid = 0;
foreach ($clients as $c) {
  if ($c['outstanding_amount'] > 0)
    $total_unpaid++;
  if ($c['outstanding_amount'] <= 0 && $c['total_amount'] > 0)
    $total_paid++;
}

// Store all client data as JavaScript variable
$clients_json = json_encode($clients);

include 'sidebar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Billing Dashboard — EbizTech</title>
  <style>
    /* ============ DARK MODE (DEFAULT) ============ */
    :root {
      --bg: #0d0805;
      --card-bg: #1a0e0a;
      --card-hover: #24140d;
      --border: #3a2a1a;
      --text: #f8fafc;
      --text-dim: #9ca3af;
      --text-muted: #6b7280;
      --orange: #f57c00;
      --orange-light: #ffb74d;
      --orange-dark: #e65100;
      --orange-subtle: rgba(255, 152, 0, 0.08);
      --orange-shadow: rgba(245, 124, 0, 0.15);
      --green: #4ade80;
      --green-bg: rgba(74, 222, 128, 0.1);
      --red: #ef4444;
      --red-bg: rgba(239, 68, 68, 0.1);
      --blue: #60a5fa;
      --blue-bg: rgba(96, 165, 250, 0.1);
      --yellow: #fbbf24;
      --yellow-bg: rgba(251, 191, 36, 0.1);
      --input-bg: #24140d;
      --hover-overlay: rgba(255, 255, 255, 0.02);
      --modal-overlay: rgba(0, 0, 0, 0.85);
    }

    /* ============ LIGHT MODE ============ */
    body.light-mode {
      --bg: #f8f6f3;
      --card-bg: #ffffff;
      --card-hover: #faf7f4;
      --border: #e5d5c0;
      --text: #1a1410;
      --text-dim: #6b5e52;
      --text-muted: #9c8b7a;
      --orange: #f57c00;
      --orange-light: #e65100;
      --orange-dark: #bf360c;
      --orange-subtle: rgba(255, 152, 0, 0.08);
      --orange-shadow: rgba(245, 124, 0, 0.12);
      --green: #16a34a;
      --green-bg: rgba(22, 163, 74, 0.1);
      --red: #dc2626;
      --red-bg: rgba(220, 38, 38, 0.1);
      --blue: #2563eb;
      --blue-bg: rgba(37, 99, 235, 0.1);
      --yellow: #d97706;
      --yellow-bg: rgba(217, 119, 6, 0.1);
      --input-bg: #ffffff;
      --hover-overlay: rgba(0, 0, 0, 0.02);
      --modal-overlay: rgba(0, 0, 0, 0.6);
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
      background: var(--bg);
      color: var(--text);
      min-height: 100vh;
      transition: background 0.3s ease, color 0.3s ease;
    }

    .app-wrapper {
      display: flex;
      min-height: 100vh;
    }

    .main-content {
      flex: 1;
      margin-left: 280px;
      padding: 32px;
      transition: margin-left 0.4s ease;
    }

    .main-content.expanded {
      margin-left: 85px;
    }

    /* Header */
    .page-header {
      margin-bottom: 32px;
    }

    .page-header h1 {
      font-size: 28px;
      font-weight: 700;
      color: var(--text);
      position: relative;
      padding-bottom: 16px;
    }

    .page-header h1::after {
      content: "";
      position: absolute;
      bottom: 0;
      left: 0;
      width: 60px;
      height: 3px;
      border-radius: 2px;
      background: linear-gradient(90deg, var(--orange), var(--orange-light));
    }

    /* Stats Row */
    .stats-row {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 16px;
      margin-bottom: 32px;
    }

    .stat-pill {
      background: var(--card-bg);
      border: 1px solid var(--border);
      border-radius: 16px;
      padding: 24px;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }

    .stat-pill::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 3px;
      background: linear-gradient(90deg, var(--orange), var(--orange-light));
      opacity: 0;
      transition: opacity 0.3s;
    }

    .stat-pill:hover::before {
      opacity: 1;
    }

    .stat-pill:hover {
      border-color: var(--orange);
      transform: translateY(-2px);
      box-shadow: 0 8px 25px var(--orange-shadow);
    }

    .stat-pill .stat-label {
      font-size: 13px;
      font-weight: 500;
      color: var(--text-dim);
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 8px;
    }

    .stat-pill .stat-value {
      font-size: 32px;
      font-weight: 700;
      color: var(--orange);
    }

    /* Toolbar */
    .toolbar {
      display: flex;
      gap: 12px;
      margin-bottom: 32px;
    }

    .toolbar input {
      flex: 1;
      padding: 12px 16px;
      background: var(--input-bg);
      border: 1px solid var(--border);
      border-radius: 12px;
      color: var(--text);
      font-size: 14px;
      transition: all 0.3s;
    }

    .toolbar input:focus {
      outline: none;
      border-color: var(--orange);
      box-shadow: 0 0 0 3px var(--orange-subtle);
    }

    .toolbar input::placeholder {
      color: var(--text-muted);
    }

    .toolbar select {
      padding: 12px 16px;
      background: var(--input-bg);
      border: 1px solid var(--border);
      border-radius: 12px;
      color: var(--text);
      font-size: 14px;
      cursor: pointer;
      transition: all 0.3s;
    }

    .toolbar select:focus {
      outline: none;
      border-color: var(--orange);
      box-shadow: 0 0 0 3px var(--orange-subtle);
    }

    .toolbar select option {
      background: var(--card-bg);
      color: var(--text);
    }

    /* Cards Grid */
    .cards-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 20px;
    }

    .client-card {
      background: var(--card-bg);
      border: 1px solid var(--border);
      border-radius: 20px;
      padding: 24px;
      cursor: pointer;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
      overflow: hidden;
    }

    .client-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      bottom: 0;
      width: 4px;
      transition: width 0.3s;
    }

    .client-card.paid::before {
      background: var(--green);
    }

    .client-card.partial::before {
      background: var(--yellow);
    }

    .client-card.unpaid::before {
      background: var(--orange);
    }

    .client-card.draft::before {
      background: var(--blue);
    }

    .client-card:hover {
      transform: translateY(-4px);
      border-color: var(--orange);
      box-shadow: 0 12px 30px var(--orange-shadow);
      background: var(--card-hover);
    }

    .client-card:hover::before {
      width: 6px;
    }

    .card-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 16px;
    }

    .card-avatar {
      width: 48px;
      height: 48px;
      border-radius: 14px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      color: white;
      font-size: 18px;
      background: linear-gradient(135deg, var(--orange), var(--orange-dark));
      box-shadow: 0 4px 12px var(--orange-shadow);
    }

    .card-badge {
      padding: 4px 12px;
      border-radius: 6px;
      font-size: 11px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .badge-paid {
      background: var(--green-bg);
      color: var(--green);
    }

    .badge-unpaid {
      background: var(--red-bg);
      color: var(--red);
    }

    .badge-partial {
      background: var(--yellow-bg);
      color: var(--yellow);
    }

    .badge-draft {
      background: var(--blue-bg);
      color: var(--blue);
    }

    .card-name {
      font-size: 18px;
      font-weight: 600;
      color: var(--text);
      margin-bottom: 4px;
    }

    .card-contact {
      font-size: 13px;
      color: var(--text-dim);
      margin-bottom: 20px;
    }

    .card-stats {
      display: grid;
      grid-template-columns: 1fr 1fr 1fr;
      gap: 8px;
      margin-bottom: 20px;
      padding: 12px;
      background: var(--hover-overlay);
      border-radius: 12px;
    }

    .card-stat {
      text-align: center;
    }

    .stat-val {
      font-size: 22px;
      font-weight: 700;
      color: var(--orange);
      margin-bottom: 2px;
    }

    .stat-lbl {
      font-size: 10px;
      color: var(--text-dim);
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .card-amount {
      text-align: center;
      padding-top: 16px;
      border-top: 1px solid var(--border);
    }

    .card-amount .amt {
      font-size: 24px;
      font-weight: 700;
      color: var(--orange);
      margin-bottom: 4px;
    }

    .card-amount .amt-sub {
      font-size: 12px;
      color: var(--text-dim);
    }

    /* Modal Styles */
    #detailModal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: var(--modal-overlay);
      backdrop-filter: blur(4px);
      z-index: 99999;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }

    #detailModal.show {
      display: flex !important;
      animation: modalFadeIn 0.3s ease;
    }

    @keyframes modalFadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    #modalBox {
      background: var(--card-bg);
      border: 1px solid var(--border);
      border-radius: 24px;
      padding: 32px;
      width: 100%;
      max-width: 850px;
      max-height: 85vh;
      overflow-y: auto;
      box-shadow: 0 25px 60px rgba(0, 0, 0, 0.3);
      animation: modalSlideIn 0.3s ease;
    }

    @keyframes modalSlideIn {
      from { transform: translateY(-20px); opacity: 0; }
      to { transform: translateY(0); opacity: 1; }
    }

    #modalBox::-webkit-scrollbar {
      width: 6px;
    }

    #modalBox::-webkit-scrollbar-track {
      background: transparent;
    }

    #modalBox::-webkit-scrollbar-thumb {
      background: var(--orange);
      border-radius: 3px;
    }

    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 24px;
      padding-bottom: 20px;
      border-bottom: 2px solid var(--border);
    }

    .modal-header h2 {
      font-size: 24px;
      font-weight: 700;
      color: var(--text);
    }

    .modal-close {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      background: var(--hover-overlay);
      border: 1px solid var(--border);
      color: var(--text-dim);
      font-size: 20px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.3s;
    }

    .modal-close:hover {
      background: var(--red-bg);
      border-color: var(--red);
      color: var(--red);
      transform: rotate(90deg);
    }

    .detail-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px;
      margin-bottom: 24px;
    }

    .detail-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 12px 16px;
      background: var(--hover-overlay);
      border-radius: 10px;
      border: 1px solid var(--border);
    }

    .detail-label {
      color: var(--text-dim);
      font-size: 13px;
      font-weight: 500;
    }

    .detail-value {
      font-weight: 600;
      font-size: 14px;
      color: var(--text);
      text-align: right;
    }

    .summary-box {
      display: grid;
      grid-template-columns: 1fr 1fr 1fr;
      gap: 16px;
      margin-bottom: 24px;
      padding: 20px;
      background: var(--orange-subtle);
      border: 1px solid var(--border);
      border-radius: 16px;
      text-align: center;
    }

    .summary-item .summary-label {
      font-size: 12px;
      color: var(--text-dim);
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 8px;
    }

    .summary-item .summary-value {
      font-size: 20px;
      font-weight: 700;
    }

    .bill-list {
      margin-top: 24px;
    }

    .bill-list-title {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 16px;
    }

    .bill-list-title h3 {
      font-size: 18px;
      font-weight: 600;
      color: var(--text);
    }

    .bill-list-title .bill-count {
      font-size: 13px;
      color: var(--text-dim);
      background: var(--hover-overlay);
      padding: 4px 12px;
      border-radius: 20px;
    }

    .bill-item {
      display: flex;
      align-items: center;
      gap: 16px;
      padding: 16px;
      background: var(--hover-overlay);
      border: 1px solid var(--border);
      border-radius: 12px;
      margin-bottom: 10px;
      transition: all 0.3s;
    }

    .bill-item:hover {
      background: var(--card-hover);
      border-color: var(--orange);
    }

    .bill-item.invoice {
      border-left: 4px solid var(--orange);
    }

    .bill-item.quotation {
      border-left: 4px solid var(--blue);
    }

    .bill-type-badge {
      padding: 6px 12px;
      border-radius: 8px;
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      white-space: nowrap;
    }

    .bill-type-badge.invoice {
      background: rgba(245, 124, 0, 0.15);
      color: var(--orange);
    }

    .bill-type-badge.quotation {
      background: rgba(96, 165, 250, 0.15);
      color: var(--blue);
    }

    .bill-info {
      flex: 1;
      min-width: 0;
    }

    .bill-number {
      font-weight: 600;
      font-size: 14px;
      color: var(--text);
      margin-bottom: 4px;
    }

    .bill-date {
      font-size: 12px;
      color: var(--text-dim);
    }

    .bill-status-badge {
      padding: 4px 10px;
      border-radius: 6px;
      font-size: 11px;
      font-weight: 600;
      text-transform: uppercase;
      white-space: nowrap;
    }

    .status-paid {
      background: var(--green-bg);
      color: var(--green);
    }

    .status-draft {
      background: var(--blue-bg);
      color: var(--blue);
    }

    .status-sent,
    .status-pending {
      background: var(--yellow-bg);
      color: var(--yellow);
    }

    .status-overdue,
    .status-rejected {
      background: var(--red-bg);
      color: var(--red);
    }

    .status-accepted {
      background: var(--green-bg);
      color: var(--green);
    }

    .bill-amount {
      font-weight: 700;
      font-size: 16px;
      white-space: nowrap;
    }

    .bill-actions {
      display: flex;
      gap: 8px;
    }

    .bill-btn {
      padding: 8px 16px;
      border: none;
      border-radius: 8px;
      font-size: 12px;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 4px;
      transition: all 0.3s;
      white-space: nowrap;
    }

    .btn-view {
      background: rgba(245, 124, 0, 0.15);
      color: var(--orange);
    }

    .btn-view:hover {
      background: rgba(245, 124, 0, 0.25);
      transform: translateY(-1px);
    }

    .btn-pdf {
      background: rgba(96, 165, 250, 0.15);
      color: var(--blue);
    }

    .btn-pdf:hover {
      background: rgba(96, 165, 250, 0.25);
      transform: translateY(-1px);
    }

    .empty-state {
      text-align: center;
      padding: 80px 20px;
      color: var(--text-dim);
      grid-column: 1 / -1;
    }

    .empty-state .icon {
      font-size: 64px;
      margin-bottom: 16px;
      display: block;
    }

    .empty-state h2 {
      font-size: 24px;
      color: var(--text);
      margin-bottom: 8px;
    }

    .empty-state p {
      font-size: 14px;
    }

    /* Responsive */
    @media (max-width: 1200px) {
      .stats-row {
        grid-template-columns: repeat(2, 1fr);
      }
    }

    @media (max-width: 768px) {
      .main-content {
        margin-left: 85px;
        padding: 20px;
      }
      
      .main-content.expanded {
        margin-left: 85px;
      }

      .stats-row {
        grid-template-columns: 1fr;
      }

      .cards-grid {
        grid-template-columns: 1fr;
      }

      .detail-grid {
        grid-template-columns: 1fr;
      }

      .summary-box {
        grid-template-columns: 1fr;
      }

      .bill-item {
        flex-direction: column;
        align-items: flex-start;
      }

      .bill-actions {
        width: 100%;
        justify-content: flex-end;
      }

      #modalBox {
        padding: 20px;
        max-width: 100%;
      }
    }

    @media (max-width: 480px) {
      .main-content {
        margin-left: 0;
        padding: 16px;
      }

      .main-content.expanded {
        margin-left: 0;
      }

      .stats-row {
        grid-template-columns: 1fr 1fr;
        gap: 10px;
      }

      .stat-pill {
        padding: 16px;
      }

      .stat-pill .stat-value {
        font-size: 24px;
      }
    }
  </style>
</head>
<body>
  <div class="app-wrapper">
    <div class="main-content" id="mainContent">

      <div class="page-header">
        <h1>Billing Dashboard</h1>
      </div>

      <div class="stats-row">
        <div class="stat-pill">
          <div class="stat-label">Total Clients</div>
          <div class="stat-value"><?= $total_clients ?></div>
        </div>
        <div class="stat-pill">
          <div class="stat-label">Total Bills</div>
          <div class="stat-value"><?= $total_bills ?></div>
        </div>
        <div class="stat-pill">
          <div class="stat-label">Paid Clients</div>
          <div class="stat-value"><?= $total_paid ?></div>
        </div>
        <div class="stat-pill">
          <div class="stat-label">Unpaid Clients</div>
          <div class="stat-value"><?= $total_unpaid ?></div>
        </div>
      </div>

      <div class="toolbar">
        <input type="text" id="searchInput" placeholder="🔍 Search by company name..." onkeyup="filterCards()">
        <select id="statusFilter" onchange="filterCards()">
          <option value="all">All Status</option>
          <option value="paid">Paid</option>
          <option value="unpaid">Unpaid</option>
          <option value="partial">Partial</option>
          <option value="draft">Draft</option>
        </select>
      </div>

      <div class="cards-grid" id="cardsGrid">
        <?php foreach ($clients as $client_key => $client):
          $outstanding = $client['outstanding_amount'];
          if ($client['total_amount'] > 0 && $outstanding <= 0) {
            $status = 'paid';
            $status_text = 'Paid';
          } elseif ($client['paid_amount'] > 0 && $outstanding > 0) {
            $status = 'partial';
            $status_text = 'Partial';
          } elseif ($client['total_amount'] == 0) {
            $status = 'draft';
            $status_text = 'Draft';
          } else {
            $status = 'unpaid';
            $status_text = 'Unpaid';
          }
          $initials = strtoupper(substr($client['name'], 0, 2));
          $total_bills_count = count($client['bills']);
          $invoice_count = count(array_filter($client['bills'], function ($b) {
            return $b['bill_type'] == 'invoice';
          }));
          $quote_count = count(array_filter($client['bills'], function ($b) {
            return $b['bill_type'] == 'quotation';
          }));
          ?>
          <div class="client-card <?= $status ?>" 
               data-client-key="<?= htmlspecialchars($client_key) ?>"
               data-status="<?= $status ?>" 
               data-name="<?= htmlspecialchars(strtolower($client['name'])) ?>"
               onclick="showClientModal('<?= htmlspecialchars($client_key, ENT_QUOTES) ?>')">
            <div class="card-header">
              <div class="card-avatar"><?= htmlspecialchars($initials) ?></div>
              <span class="card-badge badge-<?= $status ?>"><?= $status_text ?></span>
            </div>
            <div class="card-name"><?= htmlspecialchars($client['name']) ?></div>
            <div class="card-contact"><?= htmlspecialchars($client['contact'] ?: 'No contact') ?></div>
            <div class="card-stats">
              <div class="card-stat">
                <div class="stat-val"><?= $total_bills_count ?></div>
                <div class="stat-lbl">Total</div>
              </div>
              <div class="card-stat">
                <div class="stat-val"><?= $invoice_count ?></div>
                <div class="stat-lbl">Invoices</div>
              </div>
              <div class="card-stat">
                <div class="stat-val"><?= $quote_count ?></div>
                <div class="stat-lbl">Quotes</div>
              </div>
            </div>
            <div class="card-amount">
              <div class="amt">₹<?= number_format($client['total_amount'], 0) ?></div>
              <div class="amt-sub">Outstanding: ₹<?= number_format($outstanding, 0) ?></div>
            </div>
          </div>
        <?php endforeach; ?>

        <?php if (empty($clients)): ?>
          <div class="empty-state">
            <span class="icon">📭</span>
            <h2>No Billing Records</h2>
            <p>Create invoices or quotations to see them here</p>
          </div>
        <?php endif; ?>
      </div>

    </div>
  </div>

  <!-- Modal -->
  <div id="detailModal">
    <div id="modalBox">
      <div class="modal-header">
        <h2 id="modalTitle">Client Details</h2>
        <button class="modal-close" onclick="closeModal()">✕</button>
      </div>
      <div id="modalBody"></div>
    </div>
  </div>

  <script>
    // Store all client data
    var clientsData = <?= $clients_json ?>;

    console.log('Dashboard loaded successfully');
    console.log('Clients loaded:', Object.keys(clientsData).length);

    // Theme management
    function applyTheme() {
      var theme = localStorage.getItem('theme');
      if (theme === 'light') {
        document.body.classList.add('light-mode');
      } else {
        document.body.classList.remove('light-mode');
      }
    }

    // Apply theme on load
    applyTheme();

    // Listen for theme changes from sidebar or other components
    window.addEventListener('storage', function(e) {
      if (e.key === 'theme') {
        applyTheme();
      }
    });

    // Also check theme periodically (for same-tab changes)
    setInterval(function() {
      applyTheme();
    }, 500);

    // Function to show client modal
    function showClientModal(clientKey) {
      console.log('Opening modal for client:', clientKey);

      var modal = document.getElementById('detailModal');
      var modalBody = document.getElementById('modalBody');
      var modalTitle = document.getElementById('modalTitle');

      if (!modal || !modalBody || !modalTitle) {
        console.error('Modal elements not found!');
        return;
      }

      var client = clientsData[clientKey];
      if (!client) {
        console.error('Client not found:', clientKey);
        return;
      }

      var bills = client.bills || [];
      var outstanding = parseFloat(client.outstanding_amount || 0);

      // Determine status
      var status = 'Unpaid';
      var statusColor = '#ef4444';
      if (client.total_amount > 0 && outstanding <= 0) {
        status = 'Paid';
        statusColor = '#4ade80';
      } else if (client.paid_amount > 0 && outstanding > 0) {
        status = 'Partial';
        statusColor = '#fbbf24';
      } else if (client.total_amount == 0) {
        status = 'Draft';
        statusColor = '#60a5fa';
      }

      // Set title
      modalTitle.textContent = client.name || 'Unknown Client';

      // Build content
      var html = '';

      // Client details grid
      html += '<div class="detail-grid">';
      html += buildDetailItem('Company', client.company_name);
      html += buildDetailItem('Contact Person', client.contact);
      html += buildDetailItem('Email', client.email);
      html += buildDetailItem('Phone', client.phone);
      html += buildDetailItem('GST Number', client.gst);
      html += '<div class="detail-item"><span class="detail-label">Status</span><span class="detail-value" style="color:' + statusColor + '">' + status + '</span></div>';
      html += '</div>';

      html += '<div class="detail-item"><span class="detail-label">Address</span><span class="detail-value" style="text-align:left;font-weight:400">' + escapeHtml(client.address || '—') + '</span></div>';

      // Summary box
      html += '<div class="summary-box">';
      html += '<div class="summary-item"><div class="summary-label">Total Amount</div><div class="summary-value" style="color:#fbbf24">₹' + formatNumber(client.total_amount) + '</div></div>';
      html += '<div class="summary-item"><div class="summary-label">Paid Amount</div><div class="summary-value" style="color:#4ade80">₹' + formatNumber(client.paid_amount) + '</div></div>';
      html += '<div class="summary-item"><div class="summary-label">Outstanding</div><div class="summary-value" style="color:' + (outstanding > 0 ? '#ef4444' : '#4ade80') + '">₹' + formatNumber(outstanding) + '</div></div>';
      html += '</div>';

      // Bills list
      if (bills.length > 0) {
        html += '<div class="bill-list">';
        html += '<div class="bill-list-title">';
        html += '<h3>📋 All Bills & Quotations</h3>';
        html += '<span class="bill-count">' + bills.length + ' records</span>';
        html += '</div>';

        // Sort by date (newest first)
        bills.sort(function(a, b) {
          return new Date(b.bill_date || 0) - new Date(a.bill_date || 0);
        });

        bills.forEach(function(bill) {
          var isInvoice = bill.bill_type == 'invoice';
          var typeClass = isInvoice ? 'invoice' : 'quotation';
          var typeLabel = isInvoice ? 'Invoice' : 'Quotation';
          var statusClass = 'status-' + bill.status;

          var viewUrl = isInvoice ? 'view_invoice.php?id=' + bill.id : 'view_quotation.php?id=' + bill.id;
          var pdfUrl = isInvoice ? 'generate_invoice_pdf.php?id=' + bill.id : 'generate_quotation_pdf.php?id=' + bill.id;

          html += '<div class="bill-item ' + typeClass + '">';
          html += '<span class="bill-type-badge ' + typeClass + '">' + typeLabel + '</span>';
          html += '<div class="bill-info">';
          html += '<div class="bill-number">' + escapeHtml(bill.bill_number || 'N/A') + '</div>';
          html += '<div class="bill-date">📅 ' + (bill.bill_date || 'No date');
          if (bill.due_date) html += ' &nbsp;⏳&nbsp; ' + bill.due_date;
          html += '</div>';
          html += '</div>';
          html += '<span class="bill-status-badge ' + statusClass + '">' + bill.status + '</span>';
          html += '<div class="bill-amount" style="color:' + (bill.status == 'paid' ? '#4ade80' : '#fbbf24') + '">' + (bill.currency || '₹') + formatNumber(bill.total) + '</div>';
          html += '<div class="bill-actions">';
          html += '<a href="' + viewUrl + '" target="_blank" class="bill-btn btn-view">📄 View</a>';
          html += '<a href="' + pdfUrl + '" target="_blank" class="bill-btn btn-pdf">📥 PDF</a>';
          html += '</div>';
          html += '</div>';
        });

        html += '</div>';
      } else {
        html += '<div class="empty-state">';
        html += '<span class="icon">📭</span>';
        html += '<h3>No bills found</h3>';
        html += '</div>';
      }

      // Set content and show modal
      modalBody.innerHTML = html;
      modal.classList.add('show');
      document.body.style.overflow = 'hidden';
    }

    function buildDetailItem(label, value) {
      return '<div class="detail-item"><span class="detail-label">' + label + '</span><span class="detail-value">' + escapeHtml(value || '—') + '</span></div>';
    }

    // Close modal
    function closeModal() {
      var modal = document.getElementById('detailModal');
      if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
      }
    }

    // Close with Escape key
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape' || e.keyCode === 27) {
        closeModal();
      }
    });

    // Close when clicking outside modal
    document.getElementById('detailModal').addEventListener('click', function(e) {
      if (e.target === this) {
        closeModal();
      }
    });

    // Filter cards
    function filterCards() {
      var search = document.getElementById('searchInput').value.toLowerCase();
      var statusFilter = document.getElementById('statusFilter').value;

      document.querySelectorAll('.client-card').forEach(function(card) {
        var nameMatch = card.getAttribute('data-name').includes(search);
        var statusMatch = statusFilter == 'all' || card.getAttribute('data-status') == statusFilter;
        card.style.display = (nameMatch && statusMatch) ? '' : 'none';
      });
    }

    // Helper functions
    function formatNumber(num) {
      return parseFloat(num || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function escapeHtml(text) {
      if (!text) return '—';
      var div = document.createElement('div');
      div.appendChild(document.createTextNode(text));
      return div.innerHTML;
    }
  </script>
</body>
</html>