<?php
session_start();
include 'db_connection.php';
// include 'session_check.php';
include 'sidebar.php';

if (isset($_POST['add_user'])) {
    $name = $_POST['name'];
    $address = $_POST['address'];
    $employee_id = $_POST['employee_id'];
    $email_id = $_POST['email_id'];
    $contact_no = $_POST['contact_no'];
    $username = $_POST['username'];
    $password = md5($_POST['password']);
    $designation = $_POST['designation'];
    $role = $_POST['role'];
    $city = $_POST['city'];
    $status = $_POST['status'];

    $profile_picture = "";

    // Handle profile picture upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "uploads/profiles/";

        // Create directory if it doesn't exist
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        // Generate unique filename
        $file_name = time() . "_" . preg_replace('/[^a-zA-Z0-9._-]/', '', $_FILES['profile_picture']['name']);
        $target_file = $target_dir . $file_name;

        // Move uploaded file
        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
            // Store ONLY the filename (not the full path)
            $profile_picture = $file_name;
        } else {
            // Only show error if move failed
            echo "<script>alert('Failed to move uploaded file. Please check folder permissions.');</script>";
        }
    } elseif (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] !== UPLOAD_ERR_NO_FILE) {
        // Show error only if there was an actual upload error (not just no file)
        $upload_errors = [
            UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form',
            UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
        ];
        $error_message = $upload_errors[$_FILES['profile_picture']['error']] ?? 'Unknown upload error';
        echo "<script>alert('Upload error: " . $error_message . "');</script>";
    }

    $sql = "INSERT INTO users
    (
        name,
        address,
        employee_id,
        email_id,
        contact_no,
        username,
        password_hash,
        designation,
        role,
        city,
        profile_picture,
        status
    )
    VALUES
    (
        '$name',
        '$address',
        '$employee_id',
        '$email_id',
        '$contact_no',
        '$username',
        '$password',
        '$designation',
        '$role',
        '$city',
        '$profile_picture',
        '$status'
    )";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('User Added Successfully');</script>";
    } else {
        echo mysqli_error($conn);
    }
}

?>

<!DOCTYPE html>
<html>

<head>
    <title>Add User</title>
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
            max-width: 1000px;
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

        h2 {
            text-align: center;
            margin-bottom: 25px;
            color: var(--orange-primary);
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
        textarea,
        select {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid var(--input-border);
            border-radius: 14px;
            background: var(--input-bg);
            color: var(--text);
            transition: all .35s ease;
            font-size: 14px;
            font-family: 'Segoe UI', sans-serif;
        }

        input:focus,
        textarea:focus,
        select:focus {
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

        select option {
            background: var(--card);
            color: var(--text);
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }

        textarea {
            resize: vertical;
            min-height: 60px;
        }

        .btn {
            width: 100%;
            padding: 16px;
            border: none;
            border-radius: 16px;
            background: linear-gradient(135deg, var(--orange-gradient-start), var(--orange-primary));
            color: white;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all .35s ease;
            box-shadow: 0 8px 20px var(--orange-shadow);
            letter-spacing: 0.5px;
            position: relative;
            overflow: hidden;
            margin-top: 10px;
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
            box-shadow: 0 15px 30px var(--orange-shadow);
            background: linear-gradient(135deg, var(--orange-primary), var(--orange-dark));
        }

        .btn:active {
            transform: translateY(0px);
        }

        .upload-area {
            border: 3px dashed var(--orange-primary);
            border-radius: 20px;
            padding: 35px;
            text-align: center;
            background: var(--orange-subtle);
            cursor: pointer;
            transition: all .35s ease;
            position: relative;
            overflow: hidden;
        }

        .upload-area::before {
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

        .upload-area:hover::before {
            opacity: 1;
        }

        .upload-area:hover {
            background: rgba(255, 152, 0, 0.08);
            transform: translateY(-3px);
            box-shadow: 0 10px 25px var(--orange-shadow);
        }

        .upload-area.dragover {
            background: rgba(255, 152, 0, 0.15);
            border-color: var(--orange-dark);
            transform: scale(1.02);
            box-shadow: 0 0 30px var(--orange-shadow);
        }

        .upload-icon {
            font-size: 50px;
            margin-bottom: 10px;
            display: block;
            position: relative;
            z-index: 1;
        }

        .upload-content h4 {
            color: var(--text);
            font-weight: 600;
            position: relative;
            z-index: 1;
        }

        .upload-content p {
            color: var(--secondary);
            position: relative;
            z-index: 1;
        }

        .browse-btn {
            margin-top: 10px;
            padding: 12px 24px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--orange-gradient-start), var(--orange-primary));
            color: white;
            cursor: pointer;
            box-shadow: 0 4px 12px var(--orange-shadow);
            transition: all .35s ease;
            font-weight: 600;
            position: relative;
            z-index: 1;
        }

        .browse-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px var(--orange-shadow);
            background: linear-gradient(135deg, var(--orange-primary), var(--orange-dark));
        }

        #fileName {
            display: block;
            margin-top: 12px;
            font-size: 14px;
            color: var(--orange-primary);
            font-weight: 600;
            position: relative;
            z-index: 1;
            word-break: break-all;
        }

        /* Dark mode specific styles */
        body.dark .upload-area {
            background: rgba(255, 152, 0, 0.06);
        }

        body.dark .upload-area.dragover {
            background: rgba(255, 152, 0, 0.15);
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
            
            .form-row {
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

            .main-content.expanded {
                margin-left: 85px;
            }

            .container {
                padding: 25px 20px;
                border-radius: 18px;
                border-width: 2px;
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

            .form-row {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .form-group {
                margin-bottom: 12px;
            }

            label {
                font-size: 13px;
                margin-bottom: 4px;
            }

            input,
            textarea,
            select {
                padding: 12px 14px;
                font-size: 16px;
                border-radius: 12px;
            }

            textarea {
                min-height: 50px;
            }

            .btn {
                padding: 14px;
                font-size: 15px;
                border-radius: 14px;
            }

            .btn:hover {
                transform: none;
            }

            .upload-area {
                padding: 30px 20px;
                border-radius: 16px;
            }

            .upload-area:hover {
                transform: none;
            }

            .upload-icon {
                font-size: 40px;
            }

            .upload-content h4 {
                font-size: 16px;
            }

            .upload-content p {
                font-size: 14px;
            }

            .browse-btn {
                padding: 10px 20px;
                font-size: 14px;
                border-radius: 10px;
            }

            .browse-btn:hover {
                transform: none;
            }

            #fileName {
                font-size: 13px;
                margin-top: 10px;
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

            .form-row {
                gap: 10px;
            }

            .form-group {
                margin-bottom: 10px;
            }

            label {
                font-size: 12px;
            }

            input,
            textarea,
            select {
                padding: 10px 12px;
                font-size: 16px;
                border-radius: 10px;
                border-width: 1.5px;
            }

            textarea {
                min-height: 40px;
            }

            .btn {
                padding: 12px;
                font-size: 14px;
                border-radius: 12px;
                margin-top: 6px;
            }

            .btn::before {
                display: none;
            }

            .btn:active {
                transform: scale(0.97);
            }

            .upload-area {
                padding: 22px 15px;
                border-radius: 14px;
                border-width: 2px;
            }

            .upload-area:hover {
                transform: none;
                box-shadow: none;
            }

            .upload-area.dragover {
                transform: none;
            }

            .upload-icon {
                font-size: 32px;
                margin-bottom: 6px;
            }

            .upload-content h4 {
                font-size: 14px;
            }

            .upload-content p {
                font-size: 12px;
                margin-bottom: 6px;
            }

            .browse-btn {
                padding: 8px 16px;
                font-size: 13px;
                border-radius: 8px;
            }

            .browse-btn:hover {
                transform: none;
            }

            .browse-btn:active {
                transform: scale(0.97);
            }

            #fileName {
                font-size: 12px;
                margin-top: 8px;
            }

            /* Disable hover effects on mobile for performance */
            .container:hover,
            .welcome-card:hover,
            .upload-area:hover,
            .browse-btn:hover,
            .btn:hover {
                transform: none;
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
                border-radius: 12px;
            }

            .welcome-card h1 {
                font-size: 16px;
            }

            .welcome-card p {
                font-size: 12px;
            }

            input,
            textarea,
            select {
                font-size: 16px;
                padding: 8px 10px;
                border-radius: 8px;
            }

            .btn {
                font-size: 13px;
                padding: 10px;
                border-radius: 10px;
            }

            .upload-area {
                padding: 18px 12px;
                border-radius: 12px;
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

            #fileName {
                font-size: 11px;
            }
        }

        /* Landscape mode on phones */
        @media (max-height: 500px) and (orientation: landscape) {
            .main-content {
                padding: 10px 15px;
            }

            .container {
                padding: 20px 20px;
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

            .form-row {
                grid-template-columns: 1fr 1fr;
                gap: 10px;
            }

            .form-group {
                margin-bottom: 8px;
            }

            input,
            textarea,
            select {
                padding: 8px 12px;
                font-size: 14px;
                min-height: 32px;
            }

            textarea {
                min-height: 35px;
            }

            .btn {
                padding: 10px;
                font-size: 13px;
            }

            .upload-area {
                padding: 20px 15px;
            }

            .upload-icon {
                font-size: 32px;
            }
        }

        /* Touch-friendly improvements for mobile */
        @media (pointer: coarse) {
            input,
            textarea,
            select,
            .btn,
            .browse-btn,
            .upload-area {
                touch-action: manipulation;
                -webkit-tap-highlight-color: transparent;
            }

            input,
            textarea,
            select {
                font-size: 16px !important;
            }

            .btn,
            .browse-btn {
                min-height: 44px;
            }

            .upload-area {
                min-height: 100px;
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
            
            .form-row {
                overflow-x: hidden;
            }
        }
    </style>

</head>

<body>
    <div class="main-content" id="mainContent">
        <div class="container">

            <div class="welcome-card">
                <h1>👤 Add User</h1>
                <p>Create employee accounts and assign roles.</p>
            </div>
            
            <form method="post" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="name" placeholder="Enter full name" required>
                    </div>

                    <div class="form-group">
                        <label>Employee ID</label>
                        <input type="text" name="employee_id" placeholder="Enter employee ID" required>
                    </div>

                    <div class="form-group">
                        <label>Email ID</label>
                        <input type="email" name="email_id" placeholder="Enter email address" required>
                    </div>

                    <div class="form-group">
                        <label>Contact Number</label>
                        <input type="text" name="contact_no" placeholder="Enter contact number" required>
                    </div>

                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" placeholder="Choose username" required>
                    </div>

                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" placeholder="Enter password" required>
                    </div>

                    <div class="form-group">
                        <label>Designation</label>
                        <input type="text" name="designation" placeholder="Enter designation">
                    </div>

                    <div class="form-group">
                        <label>Role</label>
                        <select name="role" required>
                            <option value="Admin">Admin</option>
                            <option value="User">User</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>City</label>
                        <input type="text" name="city" placeholder="Enter city">
                    </div>

                    <div class="form-group">
                        <label>Address</label>
                        <textarea name="address" rows="3" placeholder="Enter address"></textarea>
                    </div>

                    <div class="form-group">
                        <label>Profile Picture</label>
                        <div class="upload-area" id="dropZone">
                            <input type="file"
                                id="profile_picture"
                                name="profile_picture"
                                hidden
                                accept="image/*">

                            <div class="upload-content">
                                <div class="upload-icon">🖼️</div>

                                <h4>Drag & Drop Profile Picture</h4>

                                <p>or click to browse</p>

                                <button type="button"
                                    class="browse-btn"
                                    onclick="document.getElementById('profile_picture').click()">
                                    📎 Choose Image
                                </button>

                                <span id="fileName">
                                    No file selected
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Status</label>
                        <select name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                
                <button type="submit" name="add_user" class="btn">
                    👤 Create User
                </button>

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

        // File upload handling
        const dropZone = document.getElementById("dropZone");
        const fileInput = document.getElementById("profile_picture");
        const fileName = document.getElementById("fileName");

        dropZone.addEventListener("click", () => {
            fileInput.click();
        });

        fileInput.addEventListener("change", () => {
            if (fileInput.files.length) {
                fileName.textContent = "📄 " + fileInput.files[0].name;
            }
        });

        dropZone.addEventListener("dragover", (e) => {
            e.preventDefault();
            dropZone.classList.add("dragover");
        });

        dropZone.addEventListener("dragleave", () => {
            dropZone.classList.remove("dragover");
        }); 

        dropZone.addEventListener("drop", (e) => {
            e.preventDefault();

            dropZone.classList.remove("dragover");

            const files = e.dataTransfer.files;

            if (files.length) {
                fileInput.files = files;
                fileName.textContent = "📄 " + files[0].name;
            }
        });
    </script>
</body>

</html>