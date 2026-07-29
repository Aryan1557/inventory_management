<?php
// ============================================================
// LAND LIVE AUCTION - Display all land auctions from assets table
// ============================================================

// ============================================================
// 1. DATABASE CONFIGURATION
// ============================================================
$host = 'localhost';
$dbname = 'auction';
$username = 'root';
$password = 'ebiztech99';

// ============================================================
// 2. DATABASE CONNECTION
// ============================================================
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $dbConnected = true;
} catch (PDOException $e) {
    $dbError = $e->getMessage();
    $pdo = null;
    $dbConnected = false;
}

// ============================================================
// 3. SESSION START
// ============================================================
session_start();
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
}
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'User';

// ============================================================
// 3b. PER-ASSET BID LIMIT CONSTANT
// ============================================================
define('MAX_BIDS_PER_ASSET', 6);

// ============================================================
// 3c. CATEGORY ID FOR LAND
// ============================================================
define('LAND_CATEGORY_ID', 5);

// ============================================================
// 4. CREATE LAND_BIDS TABLE (if not exists)
// ============================================================
if ($pdo) {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS land_bids (
            bid_id INT(11) PRIMARY KEY AUTO_INCREMENT,
            land_id INT(11) NOT NULL,
            user_id INT(11) NOT NULL,
            bid_amount DECIMAL(12,2) NOT NULL,
            bid_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            expires_at TIMESTAMP NULL,
            locked TINYINT(1) DEFAULT 0,
            locked_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_land_id (land_id),
            INDEX idx_user_id (user_id)
        )");
    } catch (PDOException $e) {
        // Table creation failed, continue
    }

    // ===== CREATE WISHLIST TABLE IF NOT EXISTS =====
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS land_wishlist (
            id INT(11) PRIMARY KEY AUTO_INCREMENT,
            user_id INT(11) NOT NULL,
            asset_id INT(11) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY user_asset (user_id, asset_id),
            KEY asset_id (asset_id)
        )");
    } catch (PDOException $e) {
        // ignore
    }
}

// ============================================================
// 5. HANDLE AJAX REQUESTS
// ============================================================
if (isset($_POST['action']) && $_POST['action'] == 'place_bid') {
    header('Content-Type: application/json');

    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }

    $asset_id = isset($_POST['asset_id']) ? intval($_POST['asset_id']) : 0;
    $bid_amount = isset($_POST['bid_amount']) ? floatval($_POST['bid_amount']) : 0;

    if ($asset_id <= 0 || $bid_amount <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid bid data']);
        exit;
    }

    try {
        // Check if land exists in assets table with category_id = 5
        $landStmt = $pdo->prepare("SELECT a.*, l.land_id as land_upload_id 
                                   FROM assets a 
                                   LEFT JOIN land_upload_data l ON a.asset_id = l.asset_id 
                                   WHERE a.asset_id = ? AND a.category_id = ? AND a.asset_status IN ('active', 'live')");
        $landStmt->execute([$asset_id, LAND_CATEGORY_ID]);
        $land = $landStmt->fetch();
        if (!$land) {
            echo json_encode(['success' => false, 'message' => 'Land not available for bidding']);
            exit;
        }

        // Use asset_id as land_id
        $land_id = $asset_id;

        // Count active bids (locked = 1) for this user on this land
        $countStmt = $pdo->prepare("SELECT COUNT(*) as count FROM land_bids WHERE user_id = ? AND land_id = ? AND locked = 1");
        $countStmt->execute([$user_id, $land_id]);
        $bidsOnThisLand = (int)$countStmt->fetch()['count'];
        
        // CHECK: If user has reached max bids on this land
        if ($bidsOnThisLand >= MAX_BIDS_PER_ASSET) {
            echo json_encode([
                'success' => false, 
                'message' => '❌ You have reached the maximum of ' . MAX_BIDS_PER_ASSET . ' bids on this land. Please cancel one first.',
                'bid_count' => $bidsOnThisLand,
                'max_bids' => MAX_BIDS_PER_ASSET
            ]);
            exit;
        }

        // Check if bid amount is higher than current price
        $currentPrice = floatval($land['current_price'] ?? $land['starting_price'] ?? 0);
        if ($bid_amount <= $currentPrice) {
            echo json_encode(['success' => false, 'message' => 'Bid amount must be higher than current price of ₹' . number_format($currentPrice, 2)]);
            exit;
        }

        // Insert bid into land_bids table
        $insertStmt = $pdo->prepare("INSERT INTO land_bids (land_id, user_id, bid_amount, locked, locked_at, created_at) VALUES (?, ?, ?, 1, NOW(), NOW())");
        $insertStmt->execute([$land_id, $user_id, $bid_amount]);

        // Update current price in assets table
        $updateStmt = $pdo->prepare("UPDATE assets SET current_price = ? WHERE asset_id = ?");
        $updateStmt->execute([$bid_amount, $asset_id]);

        $newCount = $bidsOnThisLand + 1;
        $remainingBids = MAX_BIDS_PER_ASSET - $newCount;
        
        echo json_encode([
            'success' => true,
            'message' => '✅ Bid placed successfully! (' . $newCount . '/' . MAX_BIDS_PER_ASSET . ' bids on this land)',
            'bid_count' => $newCount,
            'max_bids' => MAX_BIDS_PER_ASSET,
            'remaining_bids' => $remainingBids,
            'new_price' => $bid_amount
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// ============================================================
// 5b. HANDLE WATCHLIST TOGGLE (already uses DB, ensure table exists)
// ============================================================
if (isset($_POST['toggle_watchlist']) && isset($_POST['asset_id'])) {
    header('Content-Type: application/json');
    
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }
    
    $asset_id = intval($_POST['asset_id']);
    
    try {
        $checkStmt = $pdo->prepare("SELECT COUNT(*) as count FROM land_wishlist WHERE user_id = ? AND asset_id = ?");
        $checkStmt->execute([$user_id, $asset_id]);
        $exists = $checkStmt->fetch()['count'] > 0;
        
        if ($exists) {
            $deleteStmt = $pdo->prepare("DELETE FROM land_wishlist WHERE user_id = ? AND asset_id = ?");
            $deleteStmt->execute([$user_id, $asset_id]);
            echo json_encode(['success' => true, 'action' => 'removed']);
        } else {
            $insertStmt = $pdo->prepare("INSERT INTO land_wishlist (user_id, asset_id) VALUES (?, ?)");
            $insertStmt->execute([$user_id, $asset_id]);
            echo json_encode(['success' => true, 'action' => 'added']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// ============================================================
// 6. GET FILTER VALUES & FETCH DATA
// ============================================================
$filterStatus = isset($_GET['status']) ? trim($_GET['status']) : '';
$filterCity = isset($_GET['city']) ? trim($_GET['city']) : '';
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

$assets = [];
$totalAssets = 0;
$activeBidCount = 0;
$liveAuctionCount = 0;
$totalLands = 0;
$watchlistCount = 0;
$totalCategories = 0;
$userBidCountsPerLand = [];

if ($pdo) {
    try {
        // Count total lands
        $landCountStmt = $pdo->prepare("SELECT COUNT(*) as count FROM assets WHERE category_id = ?");
        $landCountStmt->execute([LAND_CATEGORY_ID]);
        $landCountResult = $landCountStmt->fetch();
        $totalLands = $landCountResult ? $landCountResult['count'] : 0;

        // Count live auctions
        $liveQuery = "SELECT COUNT(*) as count FROM assets WHERE category_id = ? AND asset_status IN ('active', 'live')";
        $liveStmt = $pdo->prepare($liveQuery);
        $liveStmt->execute([LAND_CATEGORY_ID]);
        $liveAuctionCount = $liveStmt->fetch()['count'];

        // Count watchlist
        try {
            $wishStmt = $pdo->prepare("SELECT COUNT(*) as count FROM land_wishlist WHERE user_id = ?");
            $wishStmt->execute([$user_id]);
            $wishResult = $wishStmt->fetch();
            $watchlistCount = $wishResult ? $wishResult['count'] : 0;
        } catch (PDOException $e) {
            $watchlistCount = 0;
        }

        // Count categories
        $catStmt = $pdo->query("SELECT COUNT(DISTINCT category_id) as count FROM assets WHERE category_id IS NOT NULL");
        $catResult = $catStmt->fetch();
        $totalCategories = $catResult ? $catResult['count'] : 0;

        // Build WHERE conditions
        $whereConditions = [];
        $params = [];
        $whereConditions[] = "a.category_id = ?";
        $params[] = LAND_CATEGORY_ID;
        $whereConditions[] = "a.asset_status IN ('active', 'scheduled', 'live')";
        if ($filterStatus) {
            $whereConditions[] = "a.asset_status = ?";
            $params[] = $filterStatus;
        }
        if ($filterCity) {
            $whereConditions[] = "a.city = ?";
            $params[] = $filterCity;
        }
        if ($searchQuery) {
            $whereConditions[] = "(a.title LIKE ? OR a.asset_code LIKE ? OR a.city LIKE ? OR a.state LIKE ?)";
            $searchParam = "%$searchQuery%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }
        $whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

        // Count total assets with filters
        $countQuery = "SELECT COUNT(*) as total FROM assets a $whereClause";
        $countStmt = $pdo->prepare($countQuery);
        $countStmt->execute($params);
        $totalAssets = $countStmt->fetch()['total'];

        // Get assets with JOIN to land_upload_data for additional land details
        $filterParams = $params;
        $query = "SELECT 
                    a.*, 
                    l.land_id as land_upload_id, 
                    l.survey_number, 
                    l.land_area, 
                    l.land_type, 
                    l.village, 
                    l.taluka, 
                    l.district, 
                    l.images,
                    l.asset_status as land_upload_status
                  FROM assets a
                  LEFT JOIN land_upload_data l ON a.asset_id = l.asset_id
                  $whereClause 
                  ORDER BY a.created_at DESC 
                  LIMIT " . (int)$per_page . " OFFSET " . (int)$offset;
        $stmt = $pdo->prepare($query);
        $stmt->execute($filterParams);
        $assets = $stmt->fetchAll();

        // Get wishlist IDs
        try {
            $wishStmt = $pdo->prepare("SELECT asset_id FROM land_wishlist WHERE user_id = ?");
            $wishStmt->execute([$user_id]);
            $wishlistIds = $wishStmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            $wishlistIds = [];
        }

        // Count bids per land from land_bids table
        if (!empty($assets)) {
            $assetIds = array_column($assets, 'asset_id');
            $placeholders = implode(',', array_fill(0, count($assetIds), '?'));
            $bidStmt = $pdo->prepare("SELECT land_id, COUNT(*) as cnt FROM land_bids WHERE land_id IN ($placeholders) AND user_id = ? AND locked = 1 GROUP BY land_id");
            $bidParams = array_merge($assetIds, [$user_id]);
            $bidStmt->execute($bidParams);
            $rows = $bidStmt->fetchAll();
            foreach ($rows as $row) {
                $userBidCountsPerLand[$row['land_id']] = (int)$row['cnt'];
            }
        }

        // Count total active bids for sidebar
        try {
            $bidStmt = $pdo->prepare("SELECT COUNT(*) as count FROM land_bids WHERE user_id = ? AND locked = 1");
            $bidStmt->execute([$user_id]);
            $activeBidCount = $bidStmt->fetch()['count'];
        } catch (PDOException $e) {
            $activeBidCount = 0;
        }

    } catch (PDOException $e) {
        $dbError = $e->getMessage();
    }
}

// Get cities and statuses for filters
$cities = [];
$statuses = [];
if ($pdo) {
    try {
        $cityStmt = $pdo->prepare("SELECT DISTINCT city FROM assets WHERE category_id = ? AND city IS NOT NULL AND city != '' ORDER BY city");
        $cityStmt->execute([LAND_CATEGORY_ID]);
        $cities = $cityStmt->fetchAll(PDO::FETCH_COLUMN);

        $statusStmt = $pdo->prepare("SELECT DISTINCT asset_status FROM assets WHERE category_id = ? AND asset_status IS NOT NULL AND asset_status != '' ORDER BY asset_status");
        $statusStmt->execute([LAND_CATEGORY_ID]);
        $statuses = $statusStmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {}
}

$total_pages = ceil($totalAssets / $per_page);
$currentDate = date('d-m-Y');
$currentTime = date('H:i');
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Auction — Auction Hub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,400;1,500&family=Quicksand:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <style>
        /* ... (your existing CSS) ... */
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --primary: #FF6B35;
            --primary-gradient: linear-gradient(135deg, #FF6B35, #FF8A5C);
            --secondary: #2EC4B6;
            --bg-main: #E8ECEF;
            --bg-card: #FFFFFF;
            --bg-surface: #F4F6F8;
            --border: #D5DCE0;
            --border-light: #E8ECEF;
            --text-dark: #1A1E24;
            --text-gray: #6B7A8A;
            --text-muted: #8A949E;
            --shadow-soft: 0 4px 20px rgba(255, 107, 53, 0.08);
            --shadow-hover: 0 12px 48px rgba(26, 30, 36, 0.14);
            --shadow-glow: 0 8px 32px rgba(255, 107, 53, 0.20);
            --radius: 18px;
            --transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            --sidebar-width: 280px;
        }
        body {
            background: var(--bg-main);
            font-family: 'Quicksand', sans-serif;
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            font-size: 16px;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            -webkit-touch-callout: none;
        }
        @media print { body * { display: none !important; } body::after { content: "Screenshots are disabled on this page"; display: block; text-align: center; padding: 50px; font-size: 24px; color: #EF476F; } }
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: var(--bg-main); }
        ::-webkit-scrollbar-thumb { background: var(--primary); border-radius: 4px; }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--bg-card);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            padding: 28px 24px;
            z-index: 1000;
            overflow-y: auto;
            transition: transform 0.3s ease;
            box-shadow: 2px 0 20px rgba(0,0,0,0.03);
        }
        .sidebar-brand { display: flex; flex-direction: column; align-items: flex-start; text-decoration: none; padding-bottom: 28px; border-bottom: 1px solid var(--border); margin-bottom: 28px; }
        .sidebar-brand-top { display: flex; align-items: center; gap: 12px; }
        .sidebar-logo { width: 48px; height: 48px; border-radius: 12px; background: var(--primary-gradient); display: flex; align-items: center; justify-content: center; box-shadow: var(--shadow-glow); flex-shrink: 0; position: relative; overflow: hidden; }
        .sidebar-logo .ti { font-size: 26px; color: white; position: relative; z-index: 1; }
        .sidebar-wordmark { font-family: 'Playfair Display', serif; font-size: 24px; font-weight: 700; font-style: italic; color: var(--text-dark); }
        .sidebar-wordmark span { background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .sidebar-tagline { font-size: 8px; font-weight: 700; letter-spacing: 3.5px; text-transform: uppercase; color: var(--text-muted); margin-top: 3px; margin-left: 60px; }
        .sidebar-profile { display: flex; align-items: center; gap: 16px; padding: 18px 16px; background: var(--bg-surface); border-radius: 14px; margin-bottom: 28px; border: 1px solid var(--border); }
        .sidebar-avatar { width: 56px; height: 56px; border-radius: 50%; background: var(--primary-gradient); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 22px; color: white; flex-shrink: 0; }
        .sidebar-user-info { flex: 1; min-width: 0; }
        .sidebar-user-name { font-size: 16px; font-weight: 700; }
        .sidebar-user-role { font-size: 13px; color: var(--text-muted); }
        .sidebar-user-status { display: inline-block; padding: 3px 12px; border-radius: 20px; font-size: 10px; font-weight: 600; text-transform: uppercase; background: rgba(46, 196, 182, 0.15); color: var(--secondary); margin-top: 3px; }
        .sidebar-nav { flex: 1; display: flex; flex-direction: column; gap: 4px; }
        .sidebar-nav a { display: flex; align-items: center; gap: 14px; padding: 14px 16px; border-radius: 12px; color: var(--text-gray); text-decoration: none; font-size: 15px; font-weight: 600; transition: var(--transition); }
        .sidebar-nav a .ti { font-size: 20px; width: 24px; text-align: center; }
        .sidebar-nav a:hover { background: rgba(255, 107, 53, 0.06); color: var(--primary); }
        .sidebar-nav a.active { background: rgba(255, 107, 53, 0.10); color: var(--primary); box-shadow: inset 3px 0 0 var(--primary); }
        .sidebar-nav a .badge-nav { margin-left: auto; padding: 3px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; background: rgba(255, 107, 53, 0.12); color: var(--primary); }
        .sidebar-nav a .badge-nav.live { background: #e74c3c; color: white; animation: pulse-badge 2s infinite; }
        @keyframes pulse-badge { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.05); } }
        .sidebar-footer { padding-top: 24px; border-top: 1px solid var(--border); margin-top: auto; }
        .sidebar-footer a { display: flex; align-items: center; gap: 14px; padding: 14px 16px; border-radius: 12px; color: var(--text-gray); text-decoration: none; font-size: 15px; font-weight: 600; transition: var(--transition); }
        .sidebar-footer a:hover { background: rgba(239, 71, 111, 0.06); color: #EF476F; }
        .sidebar-footer a.logout { color: #EF476F; }

        .main-content { margin-left: var(--sidebar-width); flex: 1; min-height: 100vh; padding: 28px 40px 48px; width: calc(100% - var(--sidebar-width)); }
        .top-bar { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 18px; margin-bottom: 32px; padding: 24px 32px; background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius); box-shadow: var(--shadow-soft); position: relative; overflow: hidden; }
        .top-bar::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px; background: var(--primary-gradient); }
        .top-bar-left { display: flex; align-items: center; gap: 20px; flex-wrap: wrap; }
        .top-bar-left .title-group h1 { font-family: 'Playfair Display', serif; font-size: 30px; font-weight: 700; font-style: italic; }
        .top-bar-left .title-group h1 .highlight { background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .top-bar-left .title-group .subtitle { font-size: 14px; color: var(--text-gray); margin-top: 2px; }
        .top-bar-left .breadcrumb { display: flex; align-items: center; gap: 8px; font-size: 14px; color: var(--text-gray); }
        .top-bar-left .breadcrumb .active-page { color: var(--primary); font-weight: 600; }
        .top-bar-right { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
        .top-bar-right .stats-badge { padding: 8px 16px; border-radius: 10px; background: var(--bg-surface); border: 1px solid var(--border); font-size: 13px; font-weight: 600; color: var(--text-gray); display: flex; align-items: center; gap: 8px; }
        .top-bar-right .stats-badge .ti { color: var(--primary); }
        .top-bar-right .stats-badge .number { color: var(--text-dark); font-weight: 700; }

        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 24px; }
        .stat-card { background: var(--bg-card); border-radius: var(--radius); padding: 24px; border: 1px solid var(--border); box-shadow: var(--shadow-soft); text-align: center; transition: var(--transition); position: relative; overflow: hidden; }
        .stat-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; background: var(--primary-gradient); }
        .stat-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-hover); }
        .stat-card .stat-icon { font-size: 36px; color: var(--primary); margin-bottom: 8px; }
        .stat-card .stat-number { font-size: 32px; font-weight: 700; }
        .stat-card .stat-label { font-size: 14px; color: var(--text-gray); margin-top: 4px; }

        .search-filter { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 24px; padding: 16px 20px; background: var(--bg-card); border-radius: 12px; border: 1px solid var(--border); }
        .search-filter input, .search-filter select { padding: 10px 16px; border: 1px solid var(--border); border-radius: 10px; font-family: 'Quicksand', sans-serif; font-size: 14px; background: var(--bg-surface); color: var(--text-dark); transition: var(--transition); flex: 1; min-width: 150px; }
        .search-filter input:focus, .search-filter select:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(255,107,53,0.1); }
        .search-filter .btn { padding: 10px 24px; border: none; border-radius: 10px; font-weight: 600; font-size: 14px; cursor: pointer; transition: var(--transition); font-family: 'Quicksand', sans-serif; }
        .search-filter .btn-primary { background: var(--primary-gradient); color: white; }
        .search-filter .btn-primary:hover { transform: scale(1.02); box-shadow: var(--shadow-glow); }
        .search-filter .btn-secondary { background: var(--bg-surface); color: var(--text-dark); border: 1px solid var(--border); }
        .search-filter .btn-secondary:hover { background: var(--border); }

        .products-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }

        .product-card {
            background: var(--bg-card);
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid var(--border);
            transition: var(--transition);
            box-shadow: var(--shadow-soft);
            position: relative;
        }
        .product-card:hover { transform: translateY(-6px); box-shadow: var(--shadow-hover); }
        .product-card .card-image { width: 100%; height: 180px; object-fit: cover; background: var(--bg-surface); }
        .product-card .card-body { padding: 16px 20px 20px; }
        .product-card .card-category { font-size: 12px; color: var(--text-gray); display: flex; align-items: center; gap: 6px; margin-bottom: 4px; }
        .product-card .card-category .ti { color: var(--primary); }
        .product-card .card-title { font-size: 16px; font-weight: 700; margin-bottom: 2px; }
        .product-card .card-brand { font-size: 13px; color: var(--text-gray); margin-bottom: 2px; }
        .product-card .card-price { font-size: 20px; font-weight: 700; color: var(--primary); margin: 6px 0; }
        .product-card .card-condition { font-size: 12px; color: var(--text-muted); display: inline-block; padding: 2px 10px; border-radius: 10px; background: var(--bg-surface); border: 1px solid var(--border-light); }
        .product-card .card-meta { display: flex; justify-content: space-between; font-size: 12px; color: var(--text-gray); padding-top: 10px; border-top: 1px solid var(--border-light); margin-top: 6px; }
        .product-card .card-actions { display: flex; gap: 8px; margin-top: 10px; }
        .product-card .card-actions button { flex: 1; padding: 8px; border: none; border-radius: 8px; font-weight: 600; font-size: 12px; cursor: pointer; transition: var(--transition); font-family: 'Quicksand', sans-serif; }
        .product-card .card-actions .btn-view { background: var(--primary-gradient); color: white; }
        .product-card .card-actions .btn-view:hover { transform: scale(1.02); box-shadow: var(--shadow-glow); }
        .product-card .card-actions .btn-view:disabled { opacity: 0.5; cursor: not-allowed; transform: none !important; box-shadow: none !important; }
        .product-card .card-actions .btn-bid { background: var(--bg-surface); color: var(--text-dark); border: 1px solid var(--border); }
        .product-card .card-actions .btn-bid:hover { background: var(--secondary); color: white; border-color: var(--secondary); }
        
        .product-card .heart-btn {
            position: absolute;
            top: 12px;
            right: 12px;
            background: rgba(255, 255, 255, 0.9);
            border: none;
            border-radius: 50%;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            z-index: 5;
            font-size: 18px;
            color: #ccc;
        }
        .product-card .heart-btn:hover { transform: scale(1.1); box-shadow: 0 4px 12px rgba(0,0,0,0.2); }
        .product-card .heart-btn.active { color: #EF476F; }
        .product-card .heart-btn.loading { opacity: 0.5; cursor: not-allowed; transform: none !important; }

        .status-badge { font-size: 12px; padding: 3px 12px; border-radius: 20px; font-weight: 600; }
        .status-badge.active { background: rgba(46, 196, 182, 0.15); color: var(--secondary); }
        .status-badge.live { background: rgba(46, 196, 182, 0.15); color: var(--secondary); animation: pulse-badge 2s infinite; }
        .status-badge.scheduled { background: rgba(255, 183, 3, 0.15); color: #FFB703; }
        .status-badge.ended { background: rgba(239, 71, 111, 0.1); color: #EF476F; }

        .timer-badge { font-size: 11px; color: var(--text-gray); margin-top: 4px; }
        .timer-badge .ti { color: var(--primary); }
        .timer-badge.urgent { color: #EF476F; font-weight: 600; }

        .bid-progress {
            margin-top: 8px;
            padding: 6px 10px;
            background: var(--bg-surface);
            border-radius: 8px;
            border: 1px solid var(--border-light);
        }
        .bid-progress .progress-label {
            font-size: 11px;
            color: var(--text-gray);
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }
        .bid-progress .progress-bar {
            height: 6px;
            background: var(--border);
            border-radius: 4px;
            overflow: hidden;
        }
        .bid-progress .progress-bar .progress-fill {
            height: 100%;
            border-radius: 4px;
            transition: width 0.5s ease;
            background: var(--primary-gradient);
        }
        .bid-progress .progress-bar .progress-fill.warning {
            background: #FFB703;
        }
        .bid-progress .progress-bar .progress-fill.danger {
            background: #EF476F;
        }
        .bid-progress .progress-bar .progress-fill.full {
            background: #2EC4B6;
        }

        .sidebar-toggle { display: none; background: var(--bg-card); border: 1px solid var(--border); border-radius: 12px; padding: 10px 14px; cursor: pointer; font-size: 22px; color: var(--text-dark); transition: var(--transition); box-shadow: var(--shadow-soft); flex-shrink: 0; }
        .sidebar-toggle:hover { border-color: var(--primary); color: var(--primary); }
        .sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.3); z-index: 999; backdrop-filter: blur(4px); }

        .bid-modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); backdrop-filter: blur(8px); z-index: 9999; justify-content: center; align-items: center; animation: fadeIn 0.3s ease; }
        .bid-modal-overlay.active { display: flex; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        .bid-modal { background: var(--bg-card); border-radius: var(--radius); padding: 40px; max-width: 480px; width: 90%; max-height: 90vh; overflow-y: auto; box-shadow: var(--shadow-hover); animation: slideUp 0.4s ease; position: relative; }
        @keyframes slideUp { from { transform: translateY(30px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .bid-modal .modal-close { position: absolute; top: 16px; right: 20px; background: none; border: none; font-size: 28px; color: var(--text-muted); cursor: pointer; transition: var(--transition); line-height: 1; }
        .bid-modal .modal-close:hover { color: var(--text-dark); transform: rotate(90deg); }
        .bid-modal .modal-icon { font-size: 48px; color: var(--primary); margin-bottom: 16px; }
        .bid-modal h2 { font-family: 'Playfair Display', serif; font-size: 26px; font-weight: 700; margin-bottom: 8px; }
        .bid-modal .product-name { color: var(--text-gray); font-size: 16px; margin-bottom: 20px; padding: 12px 16px; background: var(--bg-surface); border-radius: 10px; border: 1px solid var(--border); }
        .bid-modal .form-group { margin-bottom: 20px; }
        .bid-modal .form-group label { display: block; font-weight: 600; font-size: 14px; margin-bottom: 6px; }
        .bid-modal .form-group input { width: 100%; padding: 12px 16px; border: 1px solid var(--border); border-radius: 10px; font-family: 'Quicksand', sans-serif; font-size: 15px; transition: var(--transition); background: var(--bg-surface); }
        .bid-modal .form-group input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(255,107,53,0.1); }
        .bid-modal .current-price { padding: 12px 16px; background: rgba(255, 107, 53, 0.05); border-radius: 10px; border: 1px solid var(--border); margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
        .bid-modal .current-price .label { font-weight: 600; color: var(--text-gray); }
        .bid-modal .current-price .value { font-weight: 700; font-size: 20px; color: var(--primary); }
        .bid-modal .modal-actions { display: flex; gap: 12px; margin-top: 20px; }
        .bid-modal .modal-actions .btn { flex: 1; justify-content: center; padding: 14px; font-size: 16px; border: none; border-radius: 10px; font-weight: 700; cursor: pointer; transition: var(--transition); font-family: 'Quicksand', sans-serif; }
        .bid-modal .modal-actions .btn-primary { background: var(--primary-gradient); color: white; }
        .bid-modal .modal-actions .btn-primary:hover { transform: scale(1.02); box-shadow: var(--shadow-glow); }
        .bid-modal .modal-actions .btn-primary:disabled { opacity: 0.5; cursor: not-allowed; transform: none !important; box-shadow: none !important; }
        .bid-modal .modal-actions .btn-secondary { background: var(--bg-surface); color: var(--text-dark); border: 1px solid var(--border); }
        .bid-modal .modal-actions .btn-secondary:hover { background: var(--border); }
        .bid-message { padding: 12px 16px; border-radius: 10px; margin-bottom: 16px; font-weight: 600; }
        .bid-message.success { background: rgba(46, 196, 182, 0.1); border: 1px solid #2EC4B6; color: #2EC4B6; }
        .bid-message.error { background: rgba(239, 71, 111, 0.1); border: 1px solid #EF476F; color: #EF476F; }
        .bid-message.warning { background: rgba(255, 183, 3, 0.1); border: 1px solid #FFB703; color: #FFB703; }

        .toast-notification { position: fixed; bottom: 100px; right: 30px; background: white; padding: 16px 24px; border-radius: 12px; box-shadow: 0 8px 30px rgba(0,0,0,0.15); border: 1px solid var(--border); display: flex; align-items: center; gap: 12px; font-family: 'Quicksand', sans-serif; font-weight: 600; font-size: 14px; z-index: 10000; animation: slideUpToast 0.4s ease; color: var(--text-dark); max-width: 90%; }
        @keyframes slideUpToast { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }

        .pagination { display: flex; justify-content: center; gap: 8px; margin-top: 24px; flex-wrap: wrap; }
        .pagination .btn { padding: 8px 16px; border: 1px solid var(--border); border-radius: 8px; background: var(--bg-card); color: var(--text-dark); text-decoration: none; font-weight: 600; font-size: 14px; transition: var(--transition); font-family: 'Quicksand', sans-serif; cursor: pointer; }
        .pagination .btn:hover { background: var(--bg-surface); border-color: var(--primary); }
        .pagination .btn-primary { background: var(--primary-gradient); color: white; border-color: var(--primary); }
        .pagination .btn-primary:hover { transform: scale(1.05); box-shadow: var(--shadow-glow); }

        .chatbot-toggle { position: fixed; bottom: 30px; right: 30px; width: 64px; height: 64px; border-radius: 50%; background: var(--primary-gradient); border: none; color: white; font-size: 28px; cursor: pointer; box-shadow: 0 4px 24px rgba(255, 107, 53, 0.4); transition: var(--transition); z-index: 9998; display: flex; align-items: center; justify-content: center; text-decoration: none; }
        .chatbot-toggle:hover { transform: scale(1.1); box-shadow: 0 8px 40px rgba(255, 107, 53, 0.5); }
        .chatbot-toggle .notification-dot { position: absolute; top: 4px; right: 4px; width: 16px; height: 16px; background: #EF476F; border-radius: 50%; border: 3px solid white; animation: pulse-dot 2s infinite; }
        @keyframes pulse-dot { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.2); } }

        @media (max-width: 1200px) { .products-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); width: 300px; }
            .sidebar.open { transform: translateX(0); }
            .sidebar-overlay.open { display: block; }
            .sidebar-toggle { display: block; }
            .main-content { margin-left: 0; width: 100%; padding: 16px; }
            .top-bar { padding: 18px 20px; flex-direction: column; align-items: stretch; gap: 14px; }
            .top-bar-left .title-group h1 { font-size: 24px; }
            .products-grid { grid-template-columns: 1fr 1fr; }
            .search-filter { flex-direction: column; }
            .search-filter input, .search-filter select { min-width: 100%; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .bid-modal { padding: 24px 20px; }
            .bid-modal h2 { font-size: 22px; }
        }
        @media (max-width: 480px) {
            .top-bar { padding: 14px 16px; }
            .top-bar-left .title-group h1 { font-size: 20px; }
            .products-grid { grid-template-columns: 1fr; }
            .product-card .card-actions { flex-direction: column; }
            .stats-grid { grid-template-columns: 1fr; }
            .bid-modal { padding: 20px; }
        }
    </style>
</head>
<body>

<!-- ===== SCREENSHOT PROTECTION ===== -->
<script>
document.addEventListener('contextmenu', function(e) { e.preventDefault(); return false; });
document.addEventListener('keydown', function(e) {
    if ((e.metaKey || e.ctrlKey) && e.shiftKey && (e.key === 's' || e.key === 'S')) {
        e.preventDefault(); e.stopPropagation();
        showProtectionToast('📸 Screenshots are disabled on this page');
        return false;
    }
    if (e.key === 'S' && e.shiftKey && (e.metaKey || e.ctrlKey)) {
        e.preventDefault(); e.stopPropagation();
        showProtectionToast('📸 Screenshots are disabled on this page');
        return false;
    }
    if (e.key === 'PrintScreen') {
        e.preventDefault(); e.stopPropagation();
        showProtectionToast('📸 Screenshots are disabled on this page');
        return false;
    }
    if (e.ctrlKey && (e.key === 's' || e.key === 'S' || e.key === 'p' || e.key === 'P' || e.key === 'u' || e.key === 'U')) {
        e.preventDefault(); e.stopPropagation(); return false;
    }
    if (e.ctrlKey && e.shiftKey && (e.key === 'i' || e.key === 'I' || e.key === 'j' || e.key === 'J' || e.key === 'c' || e.key === 'C')) {
        e.preventDefault(); e.stopPropagation(); return false;
    }
    if (e.key === 'F12') { e.preventDefault(); e.stopPropagation(); return false; }
}, true);
document.addEventListener('dragstart', function(e) { e.preventDefault(); return false; });
document.addEventListener('copy', function(e) { e.preventDefault(); return false; });
document.addEventListener('cut', function(e) { e.preventDefault(); return false; });
document.querySelectorAll('img').forEach(function(img) {
    img.setAttribute('draggable', 'false');
    img.addEventListener('dragstart', function(e) { e.preventDefault(); return false; });
});
window.print = function() { showProtectionToast('📸 Screenshots and printing are disabled on this page.'); return false; };
document.addEventListener('keydown', function(e) {
    if (e.key === 'F12' || (e.ctrlKey && e.shiftKey && (e.key === 'I' || e.key === 'J' || e.key === 'C')) || (e.ctrlKey && e.key === 'U')) {
        e.preventDefault(); return false;
    }
});
if (typeof window.console !== 'undefined') { console.log('%c⚠️ Screenshots are disabled on this page', 'font-size: 20px; color: red;'); }
document.querySelectorAll('img').forEach(function(img) {
    img.addEventListener('contextmenu', function(e) { e.preventDefault(); return false; });
});
document.addEventListener('selectstart', function(e) { e.preventDefault(); return false; });
function showProtectionToast(message) {
    var existing = document.querySelector('.protection-toast');
    if (existing) existing.remove();
    var toast = document.createElement('div');
    toast.className = 'protection-toast';
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(function() {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(20px)';
        toast.style.transition = 'all 0.3s ease';
        setTimeout(function() { if (toast.parentNode) toast.remove(); }, 400);
    }, 3000);
}
var watermark = document.createElement('div');
watermark.style.cssText = 'position:fixed;bottom:20px;right:20px;color:rgba(255,107,53,0.15);font-size:14px;font-weight:700;font-family:"Quicksand",sans-serif;pointer-events:none;z-index:99999;transform:rotate(-5deg);letter-spacing:2px;';
watermark.textContent = '🔒 Confidential • AuctionHub';
document.body.appendChild(watermark);
</script>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<aside class="sidebar" id="sidebar">
    <a href="../index.php" class="sidebar-brand">
        <div class="sidebar-brand-top">
            <div class="sidebar-logo"><i class="ti ti-gavel"></i></div>
            <span class="sidebar-wordmark">Auction<span>Hub</span></span>
        </div>
        <span class="sidebar-tagline">PREMIUM AUCTION PLATFORM</span>
    </a>
    <div class="sidebar-profile">
        <div class="sidebar-avatar"><?= strtoupper(substr($username ?? 'U', 0, 2)) ?></div>
        <div class="sidebar-user-info">
            <div class="sidebar-user-name"><?= htmlspecialchars($username ?? 'User') ?></div>
            <div class="sidebar-user-role">Agent</div>
            <span class="sidebar-user-status">● Active</span>
        </div>
    </div>
    <nav class="sidebar-nav">
        <a href="land_dashboard.php"><i class="ti ti-home"></i> Land Dashboard</a>
        <a href="land_live_auction.php" class="active"><i class="ti ti-gavel"></i> Live Auction <span class="badge-nav live" id="liveAuctionBadge"><?= $liveAuctionCount ?></span></a>
        <a href="land_products.php"><i class="ti ti-package"></i> Products</a>
        <a href="land_bids.php"><i class="ti ti-chart-line"></i> My Bids <span class="badge-nav" id="myBidsBadge"><?= $activeBidCount ?></span></a>
        <a href="land_watchlist.php"><i class="ti ti-heart"></i> Watchlist <span class="badge-nav" id="wishlistBadge"><?= $watchlistCount ?></span></a>
        <a href="land_categories.php"><i class="ti ti-tags"></i> Categories</a>
        <!-- Analytics removed -->
    </nav>
    <div class="sidebar-footer">
        <a href="logout.php" class="logout"><i class="ti ti-logout"></i> Logout</a>
    </div>
</aside>

<div class="main-content">
    <div class="top-bar">
        <div class="top-bar-left">
            <button class="sidebar-toggle" id="sidebarToggle"><i class="ti ti-menu-2"></i></button>
            <div>
                <div class="breadcrumb">
                    <span class="active-page">Live Auction</span>
                </div>
                <div class="title-group">
                    <h1><span class="highlight">Live Auction</span></h1>
                    <div class="subtitle">Browse, bid, and own the perfect property</div>
                </div>
            </div>
        </div>
        <div class="top-bar-right">
            <div class="stats-badge"><i class="ti ti-home"></i> Listings: <span class="number"><?= $totalAssets ?></span></div>
            <div class="stats-badge"><i class="ti ti-clock"></i> Live: <span class="number"><?= $liveAuctionCount ?></span></div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon"><i class="ti ti-package"></i></div>
            <div class="stat-number"><?= $totalAssets ?? 0 ?></div>
            <div class="stat-label">Total Listings</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="ti ti-ruler"></i></div>
            <div class="stat-number">123.00</div>
            <div class="stat-label">Total Area (Acres)</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="ti ti-currency-rupee"></i></div>
            <div class="stat-number">₹400,000</div>
            <div class="stat-label">Total Purchase</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="ti ti-tags"></i></div>
            <div class="stat-number"><?= $liveAuctionCount ?></div>
            <div class="stat-label">Live Auction</div>
        </div>
    </div>

    <!-- Search Filter -->
    <div class="search-filter">
        <input type="text" placeholder="Search lands..." id="searchInput" onkeyup="filterProducts()" value="<?= htmlspecialchars($searchQuery) ?>">
        <select id="typeFilter" onchange="filterProducts()">
            <option value="">All Status</option>
            <?php foreach ($statuses as $status): ?>
                <option value="<?= htmlspecialchars($status) ?>" <?= $filterStatus === $status ? 'selected' : '' ?>>
                    <?= ucfirst(htmlspecialchars($status)) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <select id="districtFilter" onchange="filterProducts()">
            <option value="">All Cities</option>
            <?php foreach ($cities as $city): ?>
                <option value="<?= htmlspecialchars($city) ?>" <?= $filterCity === $city ? 'selected' : '' ?>>
                    <?= htmlspecialchars($city) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button class="btn btn-primary" onclick="applyFilters()">
            <i class="ti ti-search"></i> Search
        </button>
        <button class="btn btn-secondary" onclick="clearFilters()">
            <i class="ti ti-x"></i> Clear
        </button>
        <span style="font-size:14px;color:var(--text-gray);margin-left:auto;">
            <strong><?= $totalAssets ?></strong> land listings found
        </span>
    </div>

    <!-- Products Grid -->
    <div class="products-grid" id="productsGrid">
        <?php if (count($assets) > 0): ?>
            <?php foreach ($assets as $asset): 
                $inWishlist = isset($wishlistIds) ? in_array($asset['asset_id'], $wishlistIds) : false;
                $bidCountOnLand = isset($userBidCountsPerLand[$asset['asset_id']]) ? $userBidCountsPerLand[$asset['asset_id']] : 0;
                $bidLimitReachedForLand = ($bidCountOnLand >= MAX_BIDS_PER_ASSET);
                $remainingBids = MAX_BIDS_PER_ASSET - $bidCountOnLand;
                $progressPercent = ($bidCountOnLand / MAX_BIDS_PER_ASSET) * 100;
                
                $isEndingSoon = false;
                $timeRemaining = '';
                if (!empty($asset['auction_end_date'])) {
                    $endDate = new DateTime($asset['auction_end_date']);
                    $now = new DateTime();
                    $diff = $now->diff($endDate);
                    if ($diff->days == 0 && $diff->h < 24 && $diff->invert == 0) {
                        $isEndingSoon = true;
                        $timeRemaining = $diff->h . 'h ' . $diff->i . 'm remaining';
                    } elseif ($diff->invert == 0) {
                        $timeRemaining = $diff->days . 'd ' . $diff->h . 'h remaining';
                    } else {
                        $timeRemaining = 'Ended';
                    }
                }
                
                $image_url = 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=400&h=300&fit=crop&crop=center';
                if (!empty($asset['images'])) {
                    $images = json_decode($asset['images'], true);
                    if (is_array($images) && count($images) > 0) {
                        $image_url = $images[0];
                    }
                }
                
                // Determine progress bar color
                $progressClass = 'full';
                if ($progressPercent < 50) {
                    $progressClass = '';
                } elseif ($progressPercent < 80) {
                    $progressClass = 'warning';
                } elseif ($progressPercent < 100) {
                    $progressClass = 'danger';
                } else {
                    $progressClass = 'full';
                }
            ?>
                <div class="product-card" id="product-card-<?= $asset['asset_id'] ?>">
                    <img src="<?= htmlspecialchars($image_url) ?>" alt="<?= htmlspecialchars($asset['title'] ?? 'Land') ?>" class="card-image" draggable="false" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=400&h=300&fit=crop&crop=center';">
                    
                    <button class="heart-btn <?= $inWishlist ? 'active' : '' ?>" 
                            data-asset-id="<?= (int)$asset['asset_id'] ?>"
                            onclick="toggleWatchlist(<?= (int)$asset['asset_id'] ?>, this)"
                            title="<?= $inWishlist ? 'Remove from Watchlist' : 'Add to Watchlist' ?>">
                        <i class="ti ti-heart<?= $inWishlist ? '-filled' : '' ?>"></i>
                    </button>
                    
                    <div class="card-body">
                        <div class="card-category">
                            <i class="ti ti-tag"></i> <?= ucfirst(htmlspecialchars($asset['asset_status'] ?? 'Active')) ?>
                        </div>
                        <div class="card-title"><?= htmlspecialchars($asset['title'] ?? 'Land Property') ?></div>
                        <div class="card-brand">
                            <?php if (!empty($asset['survey_number'])): ?>
                                Survey #: <?= htmlspecialchars($asset['survey_number']) ?> · 
                            <?php endif; ?>
                            <?= htmlspecialchars($asset['asset_code'] ?? '') ?>
                        </div>
                        <?php if (!empty($asset['land_area'])): ?>
                            <div style="font-size:13px;color:var(--text-gray);">
                                <i class="ti ti-ruler"></i> Area: <?= htmlspecialchars($asset['land_area']) ?> 
                                (<?= htmlspecialchars($asset['land_type'] ?? '') ?>)
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($asset['village']) || !empty($asset['taluka']) || !empty($asset['district'])): ?>
                            <div style="font-size:12px;color:var(--text-muted);">
                                <?= htmlspecialchars($asset['village'] ?? '') ?>
                                <?= !empty($asset['village']) && !empty($asset['taluka']) ? ', ' : '' ?>
                                <?= htmlspecialchars($asset['taluka'] ?? '') ?>
                                <?= (!empty($asset['village']) || !empty($asset['taluka'])) && !empty($asset['district']) ? ', ' : '' ?>
                                <?= htmlspecialchars($asset['district'] ?? '') ?>
                            </div>
                        <?php endif; ?>
                        <div class="card-price" id="product-price-<?= $asset['asset_id'] ?>">
                            ₹<?= number_format($asset['current_price'] ?: $asset['starting_price'] ?? 100000, 2) ?>
                        </div>
                        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                            <span class="status-badge <?= strtolower($asset['asset_status'] ?? 'active') ?>">● <?= ucfirst(htmlspecialchars($asset['asset_status'] ?? 'Active')) ?></span>
                            <span class="card-condition"><?= htmlspecialchars($asset['city'] ?? '') ?>, <?= htmlspecialchars($asset['state'] ?? '') ?></span>
                            <span style="font-size:12px;color:var(--text-gray);">
                                <i class="ti ti-eye"></i> 0 views
                            </span>
                        </div>
                        
                        <!-- BID PROGRESS BAR -->
                        <div class="bid-progress">
                            <div class="progress-label">
                                <span>
                                    <i class="ti ti-gavel" style="font-size:12px;"></i>
                                    Your bids: <?= $bidCountOnLand ?> / <?= MAX_BIDS_PER_ASSET ?>
                                </span>
                                <span>
                                    <?php if ($bidCountOnLand >= MAX_BIDS_PER_ASSET): ?>
                                        <strong style="color:#EF476F;">🔒 FULL</strong>
                                    <?php elseif ($remainingBids > 0): ?>
                                        <span style="color:var(--text-muted);">
                                            <?= $remainingBids ?> remaining
                                        </span>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill <?= $progressClass ?>" style="width: <?= min($progressPercent, 100) ?>%;"></div>
                            </div>
                        </div>
                        
                        <?php if ($timeRemaining): ?>
                            <div class="timer-badge <?= $isEndingSoon ? 'urgent' : '' ?>">
                                <i class="ti ti-clock"></i> <?= $timeRemaining ?>
                            </div>
                        <?php endif; ?>
                        <div class="card-meta">
                            <span><i class="ti ti-calendar"></i> <?= date('M d, Y') ?></span>
                            <span><i class="ti ti-clock"></i> <?= date('h:i A') ?></span>
                        </div>
                        <div class="card-actions">
                            <?php if ($bidLimitReachedForLand): ?>
                                <button class="btn-view" disabled style="background: #EF476F; color: white; opacity: 0.7;">
                                    <i class="ti ti-lock"></i> Max Bids (<?= $bidCountOnLand ?>/<?= MAX_BIDS_PER_ASSET ?>)
                                </button>
                            <?php else: ?>
                                <button class="btn-view" onclick="openBidModal(<?= $asset['asset_id'] ?>, '<?= addslashes(htmlspecialchars($asset['title'] ?? 'Land')) ?>', <?= $asset['current_price'] ?: $asset['starting_price'] ?? 100000 ?>, <?= $bidCountOnLand ?>)">
                                    <i class="ti ti-arrow-up"></i> Place Bid
                                </button>
                            <?php endif; ?>
                            <button class="btn-bid" onclick="window.location.href='land_bids.php?listing_id=<?= $asset['asset_id'] ?? 0 ?>'">
                                <i class="ti ti-eye"></i> View
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="grid-column:1/-1;text-align:center;padding:60px 20px;background:var(--bg-card);border-radius:var(--radius);border:1px solid var(--border);">
                <i class="ti ti-home" style="font-size:64px;color:var(--text-muted);opacity:0.3;display:block;margin-bottom:16px;"></i>
                <h3 style="font-size:24px;font-weight:700;margin-bottom:8px;">No Lands Available</h3>
                <p style="color:var(--text-gray);">Try adjusting your filters or search criteria</p>
                <button class="btn btn-primary" style="margin-top:16px;" onclick="window.location.href='land_live_auction.php'">
                    <i class="ti ti-arrow-left"></i> View All
                </button>
            </div>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>&status=<?= urlencode($filterStatus) ?>&city=<?= urlencode($filterCity) ?>&search=<?= urlencode($searchQuery) ?>" class="btn">
                    <i class="ti ti-chevron-left"></i> Previous
                </a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?= $i ?>&status=<?= urlencode($filterStatus) ?>&city=<?= urlencode($filterCity) ?>&search=<?= urlencode($searchQuery) ?>" class="btn <?= $i === $page ? 'btn-primary' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
            <?php if ($page < $total_pages): ?>
                <a href="?page=<?= $page + 1 ?>&status=<?= urlencode($filterStatus) ?>&city=<?= urlencode($filterCity) ?>&search=<?= urlencode($searchQuery) ?>" class="btn">
                    Next <i class="ti ti-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- BID MODAL -->
<div class="bid-modal-overlay" id="bidModal">
    <div class="bid-modal">
        <button class="modal-close" onclick="closeBidModal()">&times;</button>
        <div class="modal-icon"><i class="ti ti-arrow-up-circle"></i></div>
        <h2 id="bidModalTitle">Place Your Bid</h2>
        <div class="product-name" id="bidProductName">Product Name</div>

        <div class="bid-message" id="bidMessageArea" style="display:none;"></div>

        <form id="bidForm" onsubmit="return false;">
            <input type="hidden" name="asset_id" id="bidAssetId" value="">
            <input type="hidden" name="action" value="place_bid">
            <div class="current-price">
                <span class="label">Current Price</span>
                <span class="value" id="bidCurrentPrice">₹0.00</span>
            </div>
            <div class="form-group">
                <label>Your Name</label>
                <input type="text" name="bidder_name" id="bidderName" placeholder="Enter your full name" value="<?= htmlspecialchars($username ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Your Bid Amount (₹)</label>
                <input type="number" name="bid_amount" id="bidAmount" placeholder="Enter bid amount" step="0.01" min="0" required>
            </div>
            <div style="padding:8px 12px; background:var(--bg-surface); border-radius:8px; margin-bottom:16px; font-size:13px; color:var(--text-gray);">
                <i class="ti ti-info-circle"></i> 
                You have placed <strong id="bidCountDisplay">0</strong> bids on this land. 
                Maximum allowed: <strong><?= MAX_BIDS_PER_ASSET ?></strong>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-primary" id="submitBidBtn"><i class="ti ti-send"></i> Submit Bid</button>
                <button type="button" class="btn btn-secondary" onclick="closeBidModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<a href="chat.php" class="chatbot-toggle"><i class="ti ti-message-2"></i><span class="notification-dot"></span></a>

<script>
    let currentBidProductName = '';
    let currentBidAssetId = 0;
    let currentBidCount = 0;

    function showToast(message) {
        const existing = document.querySelector('.toast-notification');
        if (existing) existing.remove();
        const toast = document.createElement('div');
        toast.className = 'toast-notification';
        toast.textContent = message;
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(20px)';
            toast.style.transition = 'all 0.3s ease';
            setTimeout(() => toast.remove(), 400);
        }, 4000);
    }

    function showBidMessage(message, type) {
        const el = document.getElementById('bidMessageArea');
        el.textContent = message;
        el.className = 'bid-message ' + (type || 'error');
        el.style.display = 'block';
    }

    function hideBidMessage() {
        const el = document.getElementById('bidMessageArea');
        el.style.display = 'none';
        el.textContent = '';
    }

    function openBidModal(assetId, productName, currentPrice, bidCount) {
        currentBidProductName = productName;
        currentBidAssetId = assetId;
        currentBidCount = bidCount || 0;
        
        document.getElementById('bidAssetId').value = assetId;
        document.getElementById('bidProductName').textContent = productName;
        document.getElementById('bidCurrentPrice').textContent = '₹' + parseFloat(currentPrice).toFixed(2);
        document.getElementById('bidAmount').value = (parseFloat(currentPrice) + 1).toFixed(2);
        document.getElementById('bidAmount').min = (parseFloat(currentPrice) + 1).toFixed(2);
        document.getElementById('bidCountDisplay').textContent = currentBidCount;
        
        document.getElementById('bidModalTitle').textContent = 'Place Your Bid';
        document.getElementById('submitBidBtn').innerHTML = '<i class="ti ti-send"></i> Submit Bid';
        document.getElementById('submitBidBtn').disabled = false;
        
        document.getElementById('bidModal').classList.add('active');
        document.body.style.overflow = 'hidden';
        hideBidMessage();
    }

    function closeBidModal() {
        document.getElementById('bidModal').classList.remove('active');
        document.body.style.overflow = '';
    }

    function toggleWatchlist(assetId, buttonElement) {
        const isActive = buttonElement.classList.contains('active');
        const icon = buttonElement.querySelector('i');
        
        buttonElement.classList.add('loading');
        buttonElement.disabled = true;
        
        const formData = new FormData();
        formData.append('toggle_watchlist', '1');
        formData.append('asset_id', assetId);

        fetch(window.location.href, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            buttonElement.classList.remove('loading');
            buttonElement.disabled = false;
            
            if (data.success) {
                if (data.action === 'removed') {
                    buttonElement.classList.remove('active');
                    icon.className = 'ti ti-heart';
                    buttonElement.title = 'Add to Watchlist';
                    showToast('💔 Removed from watchlist');
                } else {
                    buttonElement.classList.add('active');
                    icon.className = 'ti ti-heart-filled';
                    buttonElement.title = 'Remove from Watchlist';
                    showToast('❤️ Added to watchlist');
                }
                // Update badge
                const badge = document.getElementById('wishlistBadge');
                if (badge) {
                    let cnt = parseInt(badge.textContent) || 0;
                    if (data.action === 'removed') cnt--;
                    else cnt++;
                    badge.textContent = cnt;
                }
            } else {
                showToast('❌ Could not update watchlist');
            }
        })
        .catch(function() {
            buttonElement.classList.remove('loading');
            buttonElement.disabled = false;
            showToast('❌ Could not reach the server. Please try again.');
        });
    }

    function filterProducts() {
        const type = document.getElementById('typeFilter').value;
        const district = document.getElementById('districtFilter').value;
        const search = document.getElementById('searchInput').value.toLowerCase();
        const cards = document.querySelectorAll('.product-card');
        let visibleCount = 0;
        cards.forEach(card => {
            let show = true;
            const cardTitle = card.querySelector('.card-title')?.textContent.toLowerCase() || '';
            const cardCategory = card.querySelector('.card-category')?.textContent.toLowerCase() || '';
            const cardBrand = card.querySelector('.card-brand')?.textContent.toLowerCase() || '';
            if (type && !cardCategory.includes(type.toLowerCase())) show = false;
            if (search && !cardTitle.includes(search) && !cardBrand.includes(search)) show = false;
            card.style.display = show ? '' : 'none';
            if (show) visibleCount++;
        });
        const countSpan = document.querySelector('.top-bar-right .stats-badge .number');
        if (countSpan) countSpan.textContent = visibleCount;
    }

    function applyFilters() {
        const type = document.getElementById('typeFilter').value;
        const district = document.getElementById('districtFilter').value;
        const search = document.getElementById('searchInput').value;
        let url = 'land_live_auction.php?';
        if (type) url += 'status=' + encodeURIComponent(type) + '&';
        if (district) url += 'city=' + encodeURIComponent(district) + '&';
        if (search) url += 'search=' + encodeURIComponent(search) + '&';
        window.location.href = url;
    }

    function clearFilters() {
        window.location.href = 'land_live_auction.php';
    }

    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const toggleBtn = document.getElementById('sidebarToggle');

        function toggleSidebar() {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('open');
            document.body.style.overflow = sidebar.classList.contains('open') ? 'hidden' : '';
        }

        if (toggleBtn) toggleBtn.addEventListener('click', toggleSidebar);
        if (overlay) overlay.addEventListener('click', toggleSidebar);
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                if (sidebar.classList.contains('open')) toggleSidebar();
                if (document.getElementById('bidModal').classList.contains('active')) closeBidModal();
            }
        });
        document.getElementById('bidModal').addEventListener('click', function(e) {
            if (e.target === this) closeBidModal();
        });

        document.getElementById('searchInput').addEventListener('keyup', function(e) {
            if (e.key === 'Enter') applyFilters();
        });

        document.getElementById('submitBidBtn').addEventListener('click', function(e) {
            e.preventDefault();

            const submitBtn = document.getElementById('submitBidBtn');
            const amount = parseFloat(document.getElementById('bidAmount').value);
            const currentPrice = parseFloat(document.getElementById('bidCurrentPrice').textContent.replace('₹', ''));
            const assetId = document.getElementById('bidAssetId').value;
            const bidderName = document.getElementById('bidderName').value;

            if (!amount || isNaN(amount) || amount <= currentPrice) {
                showBidMessage('Bid amount must be higher than the current price of ₹' + currentPrice.toFixed(2), 'warning');
                return;
            }

            if (currentBidCount >= <?= MAX_BIDS_PER_ASSET ?>) {
                showBidMessage('❌ You have reached the maximum of <?= MAX_BIDS_PER_ASSET ?> bids on this land. Please cancel one first.', 'error');
                return;
            }

            hideBidMessage();
            submitBtn.disabled = true;
            const originalBtnHtml = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="ti ti-loader"></i> Processing...';

            const formData = new FormData();
            formData.append('action', 'place_bid');
            formData.append('asset_id', assetId);
            formData.append('bid_amount', amount);
            formData.append('bidder_name', bidderName);

            fetch(window.location.href, {
                method: 'POST',
                headers: { 
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(function(response) {
                if (!response.ok) throw new Error('HTTP error! Status: ' + response.status);
                return response.json();
            })
            .then(function(data) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnHtml;

                if (data.success) {
                    const priceEl = document.getElementById('product-price-' + assetId);
                    if (priceEl) {
                        priceEl.textContent = '₹' + parseFloat(amount).toFixed(2);
                    }
                    
                    const card = document.getElementById('product-card-' + assetId);
                    if (card) {
                        const progressLabel = card.querySelector('.progress-label');
                        if (progressLabel) {
                            const newCount = data.bid_count || 1;
                            progressLabel.innerHTML = `
                                <span>
                                    <i class="ti ti-gavel" style="font-size:12px;"></i>
                                    Your bids: ${newCount} / <?= MAX_BIDS_PER_ASSET ?>
                                </span>
                                <span>
                                    ${newCount >= <?= MAX_BIDS_PER_ASSET ?> ? '<strong style="color:#EF476F;">🔒 FULL</strong>' : '<span style="color:var(--text-muted);">' + (<?= MAX_BIDS_PER_ASSET ?> - newCount) + ' remaining</span>'}
                                </span>
                            `;
                        }
                        const progressFill = card.querySelector('.progress-fill');
                        if (progressFill) {
                            const newPercent = ((data.bid_count || 1) / <?= MAX_BIDS_PER_ASSET ?>) * 100;
                            progressFill.style.width = Math.min(newPercent, 100) + '%';
                            progressFill.className = 'progress-fill';
                            if (newPercent < 50) {
                                progressFill.classList.add('');
                            } else if (newPercent < 80) {
                                progressFill.classList.add('warning');
                            } else if (newPercent < 100) {
                                progressFill.classList.add('danger');
                            } else {
                                progressFill.classList.add('full');
                            }
                        }
                        const btn = card.querySelector('.btn-view');
                        if (btn) {
                            if (data.bid_count >= <?= MAX_BIDS_PER_ASSET ?>) {
                                btn.innerHTML = '<i class="ti ti-lock"></i> Max Bids (' + data.bid_count + '/<?= MAX_BIDS_PER_ASSET ?>)';
                                btn.disabled = true;
                                btn.style.background = '#EF476F';
                                btn.style.color = 'white';
                                btn.style.opacity = '0.7';
                                btn.onclick = null;
                            } else {
                                btn.innerHTML = '<i class="ti ti-arrow-up"></i> Increase Bid';
                                btn.disabled = false;
                                btn.style.background = '';
                                btn.style.color = '';
                                btn.style.opacity = '';
                                btn.onclick = function() {
                                    openBidModal(parseInt(assetId), currentBidProductName, amount, data.bid_count);
                                };
                            }
                        }
                    }

                    const badge = document.getElementById('myBidsBadge');
                    if (badge) {
                        const currentCount = parseInt(badge.textContent) || 0;
                        badge.textContent = currentCount + 1;
                    }

                    closeBidModal();
                    showToast('✅ Bid placed successfully! (' + data.bid_count + '/<?= MAX_BIDS_PER_ASSET ?>)');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    showBidMessage(data.message || 'Something went wrong. Please try again.', 'error');
                    if (data.bid_count && data.bid_count >= <?= MAX_BIDS_PER_ASSET ?>) {
                        const card = document.getElementById('product-card-' + assetId);
                        if (card) {
                            const btn = card.querySelector('.btn-view');
                            if (btn) {
                                btn.innerHTML = '<i class="ti ti-lock"></i> Max Bids (' + data.bid_count + '/<?= MAX_BIDS_PER_ASSET ?>)';
                                btn.disabled = true;
                                btn.style.background = '#EF476F';
                                btn.style.color = 'white';
                                btn.style.opacity = '0.7';
                                btn.onclick = null;
                            }
                        }
                    }
                }
            })
            .catch(function(err) {
                console.error('Fetch error:', err);
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnHtml;
                showBidMessage('Could not reach the server. Error: ' + err.message, 'error');
            });
        });
    });
</script>
</body>
</html>