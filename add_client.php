<?php
// TEMP DEBUG: force PHP to show errors instead of a blank white page.
// REMOVE these 3 lines once the issue is found and fixed - never leave
// display_errors on in production, it can leak sensitive info.
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

include "db_connection.php";
include "sidebar.php";
// include 'session_check.php';

// ---------------- DETECT SILENT POST FAILURE (post_max_size exceeded) ----------------
// When the total POST body (including uploaded files) exceeds php.ini's post_max_size,
// PHP wipes $_POST and $_FILES entirely and gives no warning/error - the page just
// reloads with nothing happening. This block catches that specific situation.
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && empty($_POST)
    && empty($_FILES)
    && isset($_SERVER['CONTENT_LENGTH'])
    && (int)$_SERVER['CONTENT_LENGTH'] > 0
) {
    $postMax = ini_get('post_max_size');
    echo "<script>alert(" . json_encode(
        "Upload failed silently: your request (" . round($_SERVER['CONTENT_LENGTH'] / 1024 / 1024, 2) .
        " MB) exceeded PHP's post_max_size setting (currently: $postMax). " .
        "PHP drops the entire form + files when this happens, with no error. " .
        "Ask your host/server admin to raise post_max_size and upload_max_filesize in php.ini (e.g. to 20M), then restart PHP/web server."
    ) . "); window.history.back();</script>";
    exit;
}

// ---------------- CONFIG: PAYMENT PHOTO UPLOADS ----------------
define('PAYMENT_PHOTOS_DIR', __DIR__ . '/uploads/payment_photos/');   // physical path
define('PAYMENT_PHOTOS_REL', 'uploads/payment_photos/');              // path stored in DB / used in <img src="">
define('PAYMENT_PHOTOS_MAX_SIZE', 5 * 1024 * 1024); // 5MB per file
define('PAYMENT_PHOTOS_ALLOWED_EXT', ['jpg', 'jpeg', 'png', 'webp']);
define('PAYMENT_PHOTOS_ALLOWED_MIME', ['image/jpeg', 'image/png', 'image/webp']);

/**
 * Handles upload of one or more files from $_FILES['payment_photos']
 * Returns a JSON-encoded array of relative paths (or NULL if nothing valid was uploaded)
 *
 * DEBUG MODE: pass $debugLog by reference to get a step-by-step trace of what
 * happened to every file. Remove/ignore this once uploads are confirmed working.
 */
function handlePaymentPhotosUpload($fieldName = 'payment_photos', array &$debugLog = [])
{
    $debugLog[] = "php_ini upload_max_filesize=" . ini_get('upload_max_filesize')
        . " post_max_size=" . ini_get('post_max_size')
        . " file_uploads=" . (ini_get('file_uploads') ? 'On' : 'Off');

    if (!isset($_FILES[$fieldName])) {
        $debugLog[] = "\$_FILES['$fieldName'] is not set at all. " .
            "This usually means the form is missing enctype=multipart/form-data, " .
            "or post_max_size was exceeded (check post_max_size vs total request size).";
        return null;
    }

    $debugLog[] = "\$_FILES['$fieldName'] raw dump: " . print_r($_FILES[$fieldName], true);

    if (empty($_FILES[$fieldName]['name'][0])) {
        $debugLog[] = "No file was actually selected in the input (name[0] is empty). Field is optional, so this is fine if intentional.";
        return null;
    }

    if (!is_dir(PAYMENT_PHOTOS_DIR)) {
        $made = mkdir(PAYMENT_PHOTOS_DIR, 0755, true);
        $debugLog[] = "Directory " . PAYMENT_PHOTOS_DIR . " did not exist. mkdir() returned: " . var_export($made, true);
    }

    if (!is_dir(PAYMENT_PHOTOS_DIR)) {
        $debugLog[] = "STILL not a directory after mkdir attempt. Check parent folder permissions.";
    } elseif (!is_writable(PAYMENT_PHOTOS_DIR)) {
        $debugLog[] = "Directory exists but is NOT WRITABLE by the web server user. Run: chmod 755 " . PAYMENT_PHOTOS_DIR . " (or chown to the web server user, e.g. www-data).";
    } else {
        $debugLog[] = "Directory " . PAYMENT_PHOTOS_DIR . " exists and is writable. Good.";
    }

    $savedPaths = [];
    $fileCount = count($_FILES[$fieldName]['name']);
    $debugLog[] = "File count detected: $fileCount";

    for ($i = 0; $i < $fileCount; $i++) {

        $origName = $_FILES[$fieldName]['name'][$i];

        if ($_FILES[$fieldName]['error'][$i] !== UPLOAD_ERR_OK) {
            $debugLog[] = "File #$i ($origName) skipped - upload error code: " . $_FILES[$fieldName]['error'][$i] . " (see PHP UPLOAD_ERR_* constants).";
            continue;
        }

        $tmpPath   = $_FILES[$fieldName]['tmp_name'][$i];
        $size      = $_FILES[$fieldName]['size'][$i];
        $ext       = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

        $debugLog[] = "File #$i ($origName): tmp_name=$tmpPath size=$size ext=$ext";

        // Validate extension
        if (!in_array($ext, PAYMENT_PHOTOS_ALLOWED_EXT, true)) {
            $debugLog[] = "File #$i REJECTED - extension '$ext' not in allowed list (" . implode(',', PAYMENT_PHOTOS_ALLOWED_EXT) . ")";
            continue;
        }

        // Validate size
        if ($size > PAYMENT_PHOTOS_MAX_SIZE) {
            $debugLog[] = "File #$i REJECTED - size $size exceeds max " . PAYMENT_PHOTOS_MAX_SIZE;
            continue;
        }

        if (!is_uploaded_file($tmpPath)) {
            $debugLog[] = "File #$i REJECTED - is_uploaded_file() returned false for tmp_name=$tmpPath (not a genuine upload / tmp file missing).";
            continue;
        }

        // Validate real MIME type (not just trusting the extension)
        if (!function_exists('finfo_open')) {
            $debugLog[] = "PHP 'fileinfo' extension is not enabled on this server (finfo_open() missing). " .
                "Falling back to extension-only validation for File #$i (less secure, but won't crash). " .
                "Ask your host to enable php-fileinfo / php_fileinfo.dll.";
            $mime = null; // skip MIME check below
        } else {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime  = finfo_file($finfo, $tmpPath);
            finfo_close($finfo);
            $debugLog[] = "File #$i detected MIME: $mime";
        }

        if ($mime !== null && !in_array($mime, PAYMENT_PHOTOS_ALLOWED_MIME, true)) {
            $debugLog[] = "File #$i REJECTED - MIME '$mime' not in allowed list (" . implode(',', PAYMENT_PHOTOS_ALLOWED_MIME) . ")";
            continue;
        }

        // Build a safe, unique filename -> uploads/payment_photos/<unique>_<originalname>.<ext>
        $safeBase = preg_replace('/[^A-Za-z0-9_\-]/', '_', pathinfo($origName, PATHINFO_FILENAME));
        $newName  = uniqid('pay_', true) . '_' . $safeBase . '.' . $ext;
        $destPath = PAYMENT_PHOTOS_DIR . $newName;

        if (move_uploaded_file($tmpPath, $destPath)) {
            $debugLog[] = "File #$i SAVED to $destPath";
            $savedPaths[] = PAYMENT_PHOTOS_REL . $newName;
        } else {
            $err = error_get_last();
            $debugLog[] = "File #$i move_uploaded_file() FAILED. Last PHP error: " . ($err ? $err['message'] : 'none');
        }
    }

    $debugLog[] = "Final saved paths: " . json_encode($savedPaths);

    return !empty($savedPaths) ? json_encode($savedPaths) : null;
}

// ---------------- MANUAL INSERT ----------------
if (isset($_POST['submit_manual'])) {

    $stmt = $conn->prepare("
        INSERT INTO client (
            agency_name, owner_name, mobile_no, support_alt_no,
            address, alt_address, mail_id, purchase_rental, only_software,
            gateway_quantity, gateway_name, gateway_mac_id,
            server_quantity, server_name, server_mac_id,
            gateway_price, server_price, amc, amc_expiry,
            payment_status, total_outstanding,
            headphones_total_count, headphones_price,
            unpaid_headphones_price, gst_number, service, payment_photos
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    // Set default values for empty numeric fields
    $gateway_quantity = !empty($_POST['gateway_quantity']) ? (int)$_POST['gateway_quantity'] : 0;
    $server_quantity = !empty($_POST['server_quantity']) ? (int)$_POST['server_quantity'] : 0;
    $gateway_price = !empty($_POST['gateway_price']) ? (float)$_POST['gateway_price'] : 0.00;
    $server_price = !empty($_POST['server_price']) ? (float)$_POST['server_price'] : 0.00;
    $amc = !empty($_POST['amc']) ? (float)$_POST['amc'] : 0.00;
    $total_outstanding = !empty($_POST['total_outstanding']) ? (float)$_POST['total_outstanding'] : 0.00;
    $headphones_total_count = !empty($_POST['headphones_total_count']) ? (int)$_POST['headphones_total_count'] : 0;
    $headphones_price = !empty($_POST['headphones_price']) ? (float)$_POST['headphones_price'] : 0.00;
    $unpaid_headphones_price = !empty($_POST['unpaid_headphones_price']) ? (float)$_POST['unpaid_headphones_price'] : 0.00;

    // Important: AMC is DECIMAL(10,2) in database, not ENUM
    // service is ENUM('on', 'off') with NO NULL allowed
    $service = isset($_POST['service']) ? $_POST['service'] : 'off';

    // Handle payment photo(s) upload -> returns JSON string of paths, or NULL
    // TEMP DEBUG: $uploadDebug will print a trace of what happened to each file.
    // Remove the debug block below once uploads are confirmed working.
    $uploadDebug = [];
    $payment_photos = handlePaymentPhotosUpload('payment_photos', $uploadDebug);

    // 27 parameters: ssssssssssisisssddsssdiddsss
    $stmt->bind_param(
        "sssssssssisisssddsssdiddsss",
        $_POST['agency_name'],           // s - string
        $_POST['owner_name'],            // s - string
        $_POST['mobile_no'],             // s - string
        $_POST['support_alt_no'],        // s - string
        $_POST['address'],               // s - string
        $_POST['alt_address'],           // s - string
        $_POST['mail_id'],               // s - string
        $_POST['purchase_rental'],       // s - string
        $_POST['only_software'],         // s - string
        $gateway_quantity,               // i - integer
        $_POST['gateway_name'],          // s - string
        $_POST['gateway_mac_id'],        // s - string
        $server_quantity,                // i - integer
        $_POST['server_name'],           // s - string
        $_POST['server_mac_id'],         // s - string
        $gateway_price,                  // d - double
        $server_price,                   // d - double
        $amc,                            // d - double
        $_POST['amc_expiry'],            // s - string
        $_POST['payment_status'],        // s - string
        $total_outstanding,              // d - double
        $headphones_total_count,         // i - integer
        $headphones_price,               // d - double
        $unpaid_headphones_price,        // d - double
        $_POST['gst_number'],            // s - string
        $service,                        // s - string
        $payment_photos                  // s - string (JSON array or NULL)
    );

    $insertDebug = null; // will hold upload trace to render on-page if present

    if ($stmt->execute()) {
        // TEMP DEBUG: instead of redirecting immediately (which wipes the browser
        // console), we fall through and render the trace directly on the page below.
        // Remove this whole debug flow once payment_photos is confirmed working,
        // and put the original redirect-on-success script back.
        $insertDebug = $uploadDebug;
        $insertSuccess = true;
    } else {
        die("Execute failed: " . $stmt->error);
    }

    $stmt->close();
}

// ---------------- BULK CSV UPLOAD ----------------
if (isset($_POST['upload_csv'])) {

    if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
        die("File upload error. Error code: " . $_FILES['csv_file']['error']);
    }

    $file = $_FILES['csv_file']['tmp_name'];

    if (($handle = fopen($file, "r")) !== FALSE) {

        // Skip header row
        $header = fgetcsv($handle);

        if (!$header) {
            die("CSV file is empty or invalid.");
        }

        $success_count = 0;
        $error_count = 0;
        $errors = [];
        $row_number = 1;

        // Exact match to table columns (excluding id and created_at). payment_photos is
        // not sourced from CSV rows (no file data in a CSV cell), so it's always NULL here.
        $stmt = $conn->prepare("
            INSERT INTO client (
                agency_name, owner_name, mobile_no, support_alt_no,
                address, alt_address, mail_id, purchase_rental, only_software,
                gateway_quantity, gateway_name, gateway_mac_id,
                server_quantity, server_name, server_mac_id,
                gateway_price, server_price, amc, amc_expiry,
                payment_status, total_outstanding,
                headphones_total_count, headphones_price,
                unpaid_headphones_price, gst_number, service, payment_photos
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        if (!$stmt) {
            fclose($handle);
            die("CSV Prepare failed: " . $conn->error);
        }

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $row_number++;

            // Skip empty rows
            if (empty(array_filter($data))) {
                continue;
            }

            // Check column count (26 columns for insert)
            if (count($data) < 26) {
                $error_count++;
                $errors[] = "Row $row_number: Only " . count($data) . " columns found, 26 required";
                continue;
            }

            // Trim all data
            $data = array_map('trim', $data);

            // Convert numeric fields with defaults
            $gateway_quantity = (isset($data[9]) && $data[9] !== '') ? (int)$data[9] : 0;
            $server_quantity = (isset($data[12]) && $data[12] !== '') ? (int)$data[12] : 0;
            $gateway_price = (isset($data[15]) && $data[15] !== '') ? (float)$data[15] : 0.00;
            $server_price = (isset($data[16]) && $data[16] !== '') ? (float)$data[16] : 0.00;
            $amc = (isset($data[17]) && $data[17] !== '') ? (float)$data[17] : 0.00;
            $total_outstanding = (isset($data[20]) && $data[20] !== '') ? (float)$data[20] : 0.00;
            $headphones_total_count = (isset($data[21]) && $data[21] !== '') ? (int)$data[21] : 0;
            $headphones_price = (isset($data[22]) && $data[22] !== '') ? (float)$data[22] : 0.00;
            $unpaid_headphones_price = (isset($data[23]) && $data[23] !== '') ? (float)$data[23] : 0.00;

            // service is ENUM('on', 'off'), required (No NULL)
            $service = (isset($data[25]) && in_array(strtolower($data[25]), ['on', 'off']))
                ? strtolower($data[25])
                : 'off';

            // CSV rows never carry an actual file, so payment_photos is always NULL for bulk import
            $payment_photos = null;

            // 27 parameters matching 27 columns
            $stmt->bind_param(
                "sssssssssisisssddsssdiddsss",
                $data[0],   // agency_name
                $data[1],   // owner_name
                $data[2],   // mobile_no
                $data[3],   // support_alt_no
                $data[4],   // address
                $data[5],   // alt_address
                $data[6],   // mail_id
                $data[7],   // purchase_rental
                $data[8],   // only_software
                $gateway_quantity,  // index 9
                $data[10],  // gateway_name
                $data[11],  // gateway_mac_id
                $server_quantity,   // index 12
                $data[13],  // server_name
                $data[14],  // server_mac_id
                $gateway_price,     // index 15
                $server_price,      // index 16
                $amc,               // index 17 - DECIMAL(10,2)
                $data[18],  // amc_expiry
                $data[19],  // payment_status
                $total_outstanding, // index 20
                $headphones_total_count, // index 21
                $headphones_price,  // index 22
                $unpaid_headphones_price, // index 23
                $data[24],  // gst_number
                $service,   // index 25 - ENUM('on','off')
                $payment_photos // always NULL for CSV import
            );

            if ($stmt->execute()) {
                $success_count++;
            } else {
                $error_count++;
                $errors[] = "Row $row_number Error: " . $stmt->error;
            }
        }

        fclose($handle);
        $stmt->close();

        // Display results
        $message = "CSV Upload Completed!\n";
        $message .= "Successfully inserted: $success_count records";
        if ($error_count > 0) {
            $message .= "\nFailed: $error_count records\n\nErrors:\n" .
                implode("\n", array_slice($errors, 0, 5));
        }

        echo "<script>alert(" . json_encode($message) . "); window.location.href='add_client.php';</script>";
    } else {
        die("Failed to open CSV file.");
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Client Management</title>
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
            --orange-hover: #e65100;
            --input-bg: #f8f6f4;
            --input-border: #e8e0d8;
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
            --orange-hover: #ffb74d;
            
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

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 5px;
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
            box-shadow: 0 20px 50px var(--orange-shadow);
            position: relative;
            overflow: hidden;
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
        }

        .welcome-card p {
            color: #ffe0b0;
            position: relative;
            z-index: 1;
            opacity: .9;
            margin-top: 8px;
        }

        .section-card {
            background: var(--card);
            border-radius: 22px;
            padding: 30px;
            margin-bottom: 25px;
            border: 2px solid var(--card-border);
            box-shadow: 0 10px 25px var(--orange-shadow);
            transition: all .35s ease;
        }

        .section-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px var(--orange-shadow);
        }

        .section-title {
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 22px;
            color: var(--orange-primary);
            font-weight: 700;
            position: relative;
            padding-left: 15px;
        }

        .section-title::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 5px;
            height: 25px;
            background: linear-gradient(180deg, var(--orange-gradient-start), var(--orange-primary));
            border-radius: 10px;
        }

        .row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            font-size: 14px;
            color: var(--text);
            letter-spacing: 0.3px;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--input-border);
            border-radius: 14px;
            background: var(--input-bg);
            color: var(--text);
            transition: all .35s ease;
            font-size: 14px;
            font-family: 'Segoe UI', sans-serif;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: var(--orange-primary);
            box-shadow: 0 0 0 4px var(--orange-subtle);
            outline: none;
            background: var(--card);
        }

        input::placeholder,
        textarea::placeholder {
            color: var(--secondary);
            opacity: 0.6;
        }

        select option {
            background: var(--card);
            color: var(--text);
        }

        textarea {
            resize: vertical;
            min-height: 60px;
        }

        hr {
            border: none;
            height: 2px;
            background: linear-gradient(90deg, var(--orange-subtle), var(--orange-primary), var(--orange-subtle));
            margin: 30px 0;
            border-radius: 10px;
        }

        .btn {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--orange-gradient-start), var(--orange-primary));
            color: white;
            font-weight: 700;
            cursor: pointer;
            transition: all .35s ease;
            font-size: 16px;
            box-shadow: 0 8px 20px var(--orange-shadow);
            letter-spacing: 0.5px;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.6s ease;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px var(--orange-shadow);
            background: linear-gradient(135deg, var(--orange-primary), var(--orange-dark));
        }

        .btn:active {
            transform: translateY(0px);
        }

        .upload-box {
            margin-top: 40px;
            padding: 30px;
            border-radius: 22px;
            background: var(--card);
            border: 2px solid var(--card-border);
            box-shadow: 0 10px 25px var(--orange-shadow);
            transition: all .35s ease;
        }

        .upload-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px var(--orange-shadow);
        }

        .upload-box h3 {
            color: var(--orange-primary);
            margin-bottom: 20px;
            font-size: 22px;
            font-weight: 700;
        }

        .file-upload-area {
            border: 3px dashed var(--orange-primary);
            border-radius: 20px;
            padding: 40px 20px;
            text-align: center;
            cursor: pointer;
            background: var(--orange-subtle);
            transition: all .35s ease;
            position: relative;
            overflow: hidden;
        }

        .file-upload-area::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, var(--orange-subtle), transparent 70%);
            opacity: 0;
            transition: opacity 0.5s ease;
            pointer-events: none;
        }

        .file-upload-area:hover::before {
            opacity: 1;
        }

        .file-upload-area:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px var(--orange-shadow);
            border-color: var(--orange-dark);
        }

        .file-upload-area.dragover {
            border-color: var(--orange-dark);
            background: rgba(255, 152, 0, 0.15);
            transform: scale(1.02);
            box-shadow: 0 0 30px var(--orange-shadow);
        }

        .upload-icon {
            font-size: 50px;
            margin-bottom: 15px;
            display: block;
            position: relative;
            z-index: 1;
        }

        .upload-content h4 {
            margin-bottom: 8px;
            color: var(--text);
            font-weight: 600;
            position: relative;
            z-index: 1;
        }

        .upload-content p {
            color: var(--secondary);
            margin-bottom: 18px;
            position: relative;
            z-index: 1;
        }

        .browse-btn {
            border: none;
            padding: 12px 24px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--orange-gradient-start), var(--orange-primary));
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: all .35s ease;
            box-shadow: 0 4px 12px var(--orange-shadow);
            position: relative;
            z-index: 1;
        }

        .browse-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px var(--orange-shadow);
            background: linear-gradient(135deg, var(--orange-primary), var(--orange-dark));
        }

        #fileName,
        #paymentPhotoNames {
            display: block;
            margin-top: 18px;
            font-size: 14px;
            color: var(--orange-primary);
            font-weight: 600;
            word-break: break-word;
            position: relative;
            z-index: 1;
        }

        /* Dark mode specific styles */
        body.dark .file-upload-area {
            background: rgba(255, 152, 0, 0.06);
        }

        body.dark .file-upload-area.dragover {
            background: rgba(255, 152, 0, 0.15);
        }

        body.dark .upload-box {
            background: var(--card);
        }

        /* Payment photo preview thumbnails */
        .photo-preview-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
            position: relative;
            z-index: 1;
            justify-content: center;
        }

        .photo-preview-grid img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 10px;
            border: 2px solid var(--orange-primary);
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
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
            
            .welcome-card {
                padding: 30px 25px;
            }
            
            .welcome-card h1 {
                font-size: 24px;
            }
            
            .section-card {
                padding: 25px;
            }
            
            .row {
                grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
                gap: 15px;
            }
        }

        /* iPads and tablets */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 85px;
                padding: 20px 15px;
            }

            .container {
                padding: 0;
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

            .section-card {
                padding: 20px 15px;
                border-radius: 16px;
                margin-bottom: 18px;
            }

            .section-card:hover {
                transform: none;
            }

            .section-title {
                font-size: 18px;
                padding-left: 12px;
                margin-bottom: 15px;
            }

            .section-title::before {
                height: 20px;
                width: 4px;
            }

            .row {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .form-group {
                margin-bottom: 12px;
            }

            label {
                font-size: 13px;
            }

            input,
            select,
            textarea {
                padding: 10px 14px;
                font-size: 16px;
                border-radius: 12px;
            }

            textarea {
                min-height: 50px;
            }

            hr {
                margin: 20px 0;
            }

            .btn {
                padding: 12px;
                font-size: 15px;
                border-radius: 12px;
            }

            .upload-box {
                padding: 20px 15px;
                margin-top: 25px;
                border-radius: 16px;
            }

            .upload-box h3 {
                font-size: 18px;
                margin-bottom: 15px;
            }

            .upload-box:hover {
                transform: none;
            }

            .file-upload-area {
                padding: 30px 15px;
                border-radius: 16px;
                border-width: 2px;
            }

            .file-upload-area:hover {
                transform: none;
            }

            .upload-icon {
                font-size: 40px;
                margin-bottom: 10px;
            }

            .upload-content h4 {
                font-size: 16px;
            }

            .upload-content p {
                font-size: 14px;
                margin-bottom: 14px;
            }

            .browse-btn {
                padding: 10px 20px;
                font-size: 14px;
                border-radius: 10px;
            }

            #fileName,
            #paymentPhotoNames {
                font-size: 13px;
                margin-top: 14px;
            }

            .photo-preview-grid img {
                width: 65px;
                height: 65px;
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
                padding: 0;
            }

            .welcome-card {
                padding: 18px 16px;
                border-radius: 14px;
                margin-bottom: 15px;
            }

            .welcome-card h1 {
                font-size: 18px;
            }

            .welcome-card p {
                font-size: 13px;
                margin-top: 4px;
            }

            .section-card {
                padding: 15px 12px;
                border-radius: 14px;
                margin-bottom: 14px;
                border-width: 1.5px;
            }

            .section-card:hover {
                transform: none;
                box-shadow: 0 10px 25px var(--orange-shadow);
            }

            .section-title {
                font-size: 16px;
                padding-left: 10px;
                margin-bottom: 12px;
            }

            .section-title::before {
                height: 18px;
                width: 3px;
            }

            .row {
                gap: 10px;
            }

            .form-group {
                margin-bottom: 10px;
            }

            label {
                font-size: 12px;
                margin-bottom: 4px;
            }

            input,
            select,
            textarea {
                padding: 8px 12px;
                font-size: 16px;
                border-radius: 10px;
                border-width: 1.5px;
            }

            textarea {
                min-height: 40px;
            }

            hr {
                margin: 16px 0;
                height: 1.5px;
            }

            .btn {
                padding: 10px;
                font-size: 14px;
                border-radius: 10px;
                margin-top: 0;
            }

            .btn::before {
                display: none;
            }

            .btn:hover {
                transform: none;
            }

            .upload-box {
                padding: 15px 12px;
                margin-top: 20px;
                border-radius: 14px;
                border-width: 1.5px;
            }

            .upload-box:hover {
                transform: none;
                box-shadow: 0 10px 25px var(--orange-shadow);
            }

            .upload-box h3 {
                font-size: 16px;
                margin-bottom: 12px;
            }

            .file-upload-area {
                padding: 25px 12px;
                border-radius: 14px;
                border-width: 2px;
            }

            .file-upload-area:hover {
                transform: none;
            }

            .file-upload-area.dragover {
                transform: none;
            }

            .upload-icon {
                font-size: 32px;
                margin-bottom: 8px;
            }

            .upload-content h4 {
                font-size: 14px;
                margin-bottom: 4px;
            }

            .upload-content p {
                font-size: 12px;
                margin-bottom: 12px;
            }

            .browse-btn {
                padding: 8px 16px;
                font-size: 13px;
                border-radius: 8px;
            }

            .browse-btn:hover {
                transform: none;
            }

            #fileName,
            #paymentPhotoNames {
                font-size: 12px;
                margin-top: 12px;
            }

            .photo-preview-grid {
                gap: 8px;
                margin-top: 12px;
            }

            .photo-preview-grid img {
                width: 55px;
                height: 55px;
                border-radius: 8px;
                border-width: 1.5px;
            }

            /* Disable hover effects on mobile for performance */
            .section-card:hover,
            .upload-box:hover,
            .file-upload-area:hover,
            .browse-btn:hover,
            .btn:hover {
                transform: none;
            }

            .btn:active {
                transform: scale(0.97);
            }

            .browse-btn:active {
                transform: scale(0.97);
            }
        }

        /* Very small phones */
        @media (max-width: 380px) {
            .main-content {
                padding: 8px 6px;
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

            .section-card {
                padding: 12px 10px;
                border-radius: 12px;
            }

            .section-title {
                font-size: 14px;
                padding-left: 8px;
            }

            .section-title::before {
                height: 14px;
                width: 3px;
            }

            input,
            select,
            textarea {
                font-size: 16px;
                padding: 6px 10px;
                border-radius: 8px;
            }

            .btn {
                font-size: 13px;
                padding: 8px;
            }

            .upload-box {
                padding: 12px 10px;
            }

            .upload-box h3 {
                font-size: 14px;
            }

            .file-upload-area {
                padding: 20px 10px;
            }

            .upload-icon {
                font-size: 28px;
            }

            .upload-content h4 {
                font-size: 13px;
            }

            .upload-content p {
                font-size: 11px;
            }

            .browse-btn {
                font-size: 12px;
                padding: 6px 14px;
            }

            .photo-preview-grid img {
                width: 45px;
                height: 45px;
            }
        }

        /* Landscape mode on phones */
        @media (max-height: 500px) and (orientation: landscape) {
            .main-content {
                padding: 10px 15px;
            }

            .welcome-card {
                padding: 15px 20px;
                margin-bottom: 15px;
            }

            .welcome-card h1 {
                font-size: 18px;
            }

            .section-card {
                padding: 15px 18px;
                margin-bottom: 12px;
            }

            .row {
                grid-template-columns: 1fr 1fr;
                gap: 10px;
            }

            .form-group {
                margin-bottom: 8px;
            }

            input,
            select,
            textarea {
                padding: 6px 10px;
                font-size: 14px;
                min-height: 32px;
            }

            textarea {
                min-height: 35px;
            }

            .btn {
                padding: 8px;
                font-size: 13px;
            }

            .upload-box {
                padding: 15px 18px;
                margin-top: 15px;
            }

            .file-upload-area {
                padding: 20px 15px;
            }

            .upload-icon {
                font-size: 30px;
            }
        }

        /* Touch-friendly improvements for mobile */
        @media (pointer: coarse) {
            input,
            select,
            textarea,
            .btn,
            .browse-btn,
            .file-upload-area {
                touch-action: manipulation;
                -webkit-tap-highlight-color: transparent;
            }

            input,
            select,
            textarea {
                font-size: 16px !important;
            }

            .btn,
            .browse-btn {
                min-height: 44px;
            }

            .file-upload-area {
                min-height: 120px;
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
            
            .section-card {
                overflow-x: hidden;
            }
            
            .upload-box {
                overflow-x: hidden;
            }
        }
    </style>
</head>

<body>
    <div class="main-content" id="mainContent">

        <div class="container">

            <div class="welcome-card">
                <h1>🏢 Client Management</h1>
                <p>
                    Add new clients manually or upload them using CSV files.
                </p>
            </div>

            <?php if (!empty($insertSuccess)): ?>
            <div class="section-card" style="border-color:#2e7d32;">
                <div class="section-title" style="color:#2e7d32;">✅ Client Added Successfully</div>
                <?php if (!empty($insertDebug)): ?>
                <p style="font-weight:600; margin-bottom:10px; font-size:14px;">Payment photo upload trace (temporary debug — remove once confirmed working):</p>
                <pre style="white-space:pre-wrap; word-break:break-word; background:var(--input-bg); border:1px solid var(--input-border); border-radius:12px; padding:15px; font-size:13px; max-height:400px; overflow:auto;"><?php echo htmlspecialchars(implode("\n", $insertDebug)); ?></pre>
                <?php endif; ?>
                <p style="margin-top:15px;"><a href="add_client.php" style="color:var(--orange-primary); font-weight:600;">← Add another client</a></p>
            </div>
            <?php endif; ?>


            <form method="POST" enctype="multipart/form-data">
                <div class="section-card">

                    <div class="section-title">
                        📋 Client Information
                    </div>

                    <div class="row">

                        <div class="form-group">
                            <label>Agency Name</label>
                            <input type="text" name="agency_name" placeholder="Enter agency name" required>
                        </div>

                        <div class="form-group">
                            <label>Owner Name</label>
                            <input type="text" name="owner_name" placeholder="Enter owner name" required>
                        </div>

                        <div class="form-group">
                            <label>Mobile Number</label>
                            <input type="text" name="mobile_no" placeholder="Enter mobile number" required>
                        </div>

                        <div class="form-group">
                            <label>Support Alternate Number</label>
                            <input type="text" name="support_alt_no" placeholder="Enter alternate number">
                        </div>

                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="mail_id" placeholder="Enter email address">
                        </div>

                        <div class="form-group">
                            <label>Address</label>
                            <textarea name="address" rows="2" placeholder="Enter address"></textarea>
                        </div>

                        <div class="form-group">
                            <label>Alternative Address</label>
                            <textarea name="alt_address" rows="2" placeholder="Enter alternative address"></textarea>
                        </div>

                        <div class="form-group">
                            <label>Purchase / Rental</label>
                            <select name="purchase_rental">
                                <option value="Purchase">Purchase</option>
                                <option value="Rental">Rental</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Only Software</label>
                            <select name="only_software">
                                <option value="No">No</option>
                                <option value="Yes">Yes</option>
                            </select>
                        </div>

                    </div>

                    <hr>

                    <div class="section-title">
                        🌐 Gateway Details
                    </div>

                    <div class="row">

                        <div class="form-group">
                            <label>Gateway Quantity</label>
                            <input type="number" name="gateway_quantity" value="0" min="0">
                        </div>

                        <div class="form-group">
                            <label>Gateway Name</label>
                            <select name="gateway_name">
                                <option value="OpenVox">OpenVox</option>
                                <option value="Dinstar">Dinstar</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Gateway MAC ID</label>
                            <input type="text" name="gateway_mac_id" placeholder="Enter gateway MAC ID">
                        </div>

                        <div class="form-group">
                            <label>Gateway Price</label>
                            <input type="number" step="0.01" name="gateway_price" value="0" min="0">
                        </div>

                    </div>

                    <hr>

                    <div class="section-title">
                        🖥️ Server Details
                    </div>

                    <div class="row">

                        <div class="form-group">
                            <label>Server Quantity</label>
                            <input type="number" name="server_quantity" value="0" min="0">
                        </div>

                        <div class="form-group">
                            <label>Server Name</label>
                            <input type="text" name="server_name" placeholder="Enter server name">
                        </div>

                        <div class="form-group">
                            <label>Server MAC ID</label>
                            <input type="text" name="server_mac_id" placeholder="Enter server MAC ID">
                        </div>

                        <div class="form-group">
                            <label>Server Price</label>
                            <input type="number" step="0.01" name="server_price" value="0" min="0">
                        </div>

                    </div>

                </div>

                <hr>

                <div class="section-card">

                    <div class="section-title">
                        💰 Payment Details
                    </div>

                    <div class="row">

                        <div class="form-group">
                            <label>AMC Amount</label>
                            <input type="number" step="0.01" name="amc" value="0.00" min="0">
                        </div>

                        <div class="form-group">
                            <label>AMC Expiry</label>
                            <input type="datetime-local" name="amc_expiry">
                        </div>

                        <div class="form-group">
                            <label>Payment Status</label>
                            <select name="payment_status">
                                <option value="Unpaid">Unpaid</option>
                                <option value="Paid">Paid</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Total Outstanding</label>
                            <input type="number" step="0.01" name="total_outstanding" value="0" min="0">
                        </div>

                        <div class="form-group">
                            <label>Headphones Count</label>
                            <input type="number" name="headphones_total_count" value="0" min="0">
                        </div>

                        <div class="form-group">
                            <label>Headphones Price</label>
                            <input type="number" step="0.01" name="headphones_price" value="0" min="0">
                        </div>

                        <div class="form-group">
                            <label>Unpaid Headphones Price</label>
                            <input type="number" step="0.01" name="unpaid_headphones_price" value="0" min="0">
                        </div>

                        <div class="form-group">
                            <label>GST Number</label>
                            <input type="text" name="gst_number" placeholder="Enter GST number">
                        </div>

                        <div class="form-group">
                            <label>Service</label>
                            <select name="service">
                                <option value="off">Off</option>
                                <option value="on">On</option>
                            </select>
                        </div>

                    </div>

                    <hr>

                    <div class="section-title">
                        📸 Payment Photo(s)
                    </div>

                    <div class="form-group">
                        <div class="file-upload-area" id="paymentPhotoDropZone">
                            <input type="file" name="payment_photos[]" id="payment_photos"
                                accept=".jpg,.jpeg,.png,.webp" multiple hidden>

                            <div class="upload-content">
                                <div class="upload-icon">📸</div>
                                <h4>Drag & Drop Payment Screenshot(s) Here</h4>
                                <p>or click to browse — JPG, PNG, WEBP (max 5MB each)</p>

                                <button type="button" class="browse-btn"
                                    onclick="document.getElementById('payment_photos').click()">
                                    📎 Choose File(s)
                                </button>

                                <span id="paymentPhotoNames">No files selected</span>
                                <div class="photo-preview-grid" id="paymentPhotoPreview"></div>
                            </div>
                        </div>
                    </div>

                    <button class="btn" type="submit" name="submit_manual">
                        💾 Save Client
                    </button>

                </div>

            </form>

            <div class="upload-box">

                <h3>📂 Bulk Upload CSV</h3>

                <form method="POST" enctype="multipart/form-data">

                    <div class="form-group">
                        <label>Select CSV File</label>

                        <div class="file-upload-area" id="dropZone">
                            <input type="file" name="csv_file" id="csv_file"
                                accept=".csv" required hidden>

                            <div class="upload-content">
                                <div class="upload-icon">📂</div>
                                <h4>Drag & Drop CSV File Here</h4>
                                <p>or click to browse</p>

                                <button type="button" class="browse-btn"
                                    onclick="document.getElementById('csv_file').click()">
                                    📎 Choose File
                                </button>

                                <span id="fileName">No file selected</span>
                            </div>
                        </div>
                    </div>
                    <button class="btn" type="submit" name="upload_csv">
                        📤 Upload CSV
                    </button>

                </form>

            </div>

        </div>
    </div>
    
    <script>
        // Theme toggle handling
        if (localStorage.getItem('theme') === 'dark') {
            document.body.classList.add('dark');
        }

        const dropZone = document.getElementById("dropZone");
        const fileInput = document.getElementById("csv_file");
        const fileName = document.getElementById("fileName");

        dropZone.addEventListener("click", () => {
            fileInput.click();
        });

        fileInput.addEventListener("change", function() {
            if (this.files.length) {
                fileName.textContent = "📄 " + this.files[0].name;
            }
        });

        dropZone.addEventListener("dragover", function(e) {
            e.preventDefault();
            dropZone.classList.add("dragover");
        });

        dropZone.addEventListener("dragleave", function() {
            dropZone.classList.remove("dragover");
        });

        dropZone.addEventListener("drop", function(e) {
            e.preventDefault();
            dropZone.classList.remove("dragover");
            const files = e.dataTransfer.files;
            if (files.length) {
                fileInput.files = files;
                fileName.textContent = "📄 " + files[0].name;
            }
        });

        // ---------------- Payment Photo(s) drag & drop + preview ----------------
        const payDropZone = document.getElementById("paymentPhotoDropZone");
        const payFileInput = document.getElementById("payment_photos");
        const payFileNames = document.getElementById("paymentPhotoNames");
        const payPreviewGrid = document.getElementById("paymentPhotoPreview");

        function renderPaymentPhotoPreview(files) {
            payPreviewGrid.innerHTML = "";
            if (!files.length) {
                payFileNames.textContent = "No files selected";
                return;
            }
            payFileNames.textContent = "📸 " + Array.from(files).map(f => f.name).join(", ");
            Array.from(files).forEach(file => {
                if (!file.type.startsWith("image/")) return;
                const img = document.createElement("img");
                img.src = URL.createObjectURL(file);
                payPreviewGrid.appendChild(img);
            });
        }

        payDropZone.addEventListener("click", () => {
            payFileInput.click();
        });

        payFileInput.addEventListener("change", function() {
            renderPaymentPhotoPreview(this.files);
        });

        payDropZone.addEventListener("dragover", function(e) {
            e.preventDefault();
            payDropZone.classList.add("dragover");
        });

        payDropZone.addEventListener("dragleave", function() {
            payDropZone.classList.remove("dragover");
        });

        payDropZone.addEventListener("drop", function(e) {
            e.preventDefault();
            payDropZone.classList.remove("dragover");
            const files = e.dataTransfer.files;
            if (files.length) {
                payFileInput.files = files;
                renderPaymentPhotoPreview(files);
            }
        });
    </script>
</body>

</html>