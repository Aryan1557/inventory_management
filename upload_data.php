<?php
include 'db_connection';
include 'sidebar.php';

// $conn = mysqli_connect("localhost", "root", "", "inventory_management");

//MANUAL UPLOAD PHP
if (isset($_POST['submit'])) {
    $agency_name = $_POST['agency_name'];
    $customer_name = $_POST['customer_name'];
    $mob_number = $_POST['mob_number'];
    $alt_number = $_POST['alt_number'];
    $mail_id = $_POST['mail_id'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $address = $_POST['address'];
    $feedback = $_POST['feedback'];

    $sql = "INSERT INTO agency
    (agency_name, customer_name, mob_number, alt_number,
    mail_id, city, state, address, feedback)
    VALUES
    ('$agency_name','$customer_name','$mob_number',
    '$alt_number','$mail_id','$city','$state',
    '$address','$feedback')";

    mysqli_query($conn, $sql);

    echo "<script>alert('Agency Added Successfully');</script>";
}

// CSV UPLOAD PHP
if (isset($_POST['upload'])) {
    $file = $_FILES['csv_file']['tmp_name'];

    if ($_FILES['csv_file']['size'] > 0) {
        $handle = fopen($file, "r");

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $agency_name   = mysqli_real_escape_string($conn, $data[0]);
            $customer_name = mysqli_real_escape_string($conn, $data[1]);
            $mob_number    = mysqli_real_escape_string($conn, $data[2]);
            $alt_number    = mysqli_real_escape_string($conn, $data[3]);
            $mail_id       = mysqli_real_escape_string($conn, $data[4]);
            $city          = mysqli_real_escape_string($conn, $data[5]);
            $state         = mysqli_real_escape_string($conn, $data[6]);
            $address       = mysqli_real_escape_string($conn, $data[7]);
            $feedback      = mysqli_real_escape_string($conn, $data[8]);

            $sql = "INSERT INTO agency
            (agency_name, customer_name, mob_number, alt_number, mail_id, city, state, address, feedback)
            VALUES
            ('$agency_name','$customer_name','$mob_number','$alt_number',
            '$mail_id','$city','$state','$address','$feedback')";

            mysqli_query($conn, $sql);
        }

        fclose($handle);

        echo "<script>alert('CSV Data Uploaded Successfully');</script>";
    }
}

?>

<!DOCTYPE html>
<html>

<head>
    <title>Upload Agency Data</title>
    <!-- Added viewport meta tag for mobile responsiveness -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.5, user-scalable=yes">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'Segoe UI', sans-serif;
            transition: .35s;
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

        :root {
            /* Light Theme - Vibrant Orange */
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

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 35px;
            transition: all .4s ease;
            width: auto;
            max-width: 100%;
        }

        .main-content.expanded {
            margin-left: var(--sidebar-collapsed);
        }

        /* Top Buttons */
        .top-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .switch-btn {
            padding: 12px 22px;
            border: none;
            border-radius: 50px;
            min-width: 200px;
            background: var(--card);
            color: var(--secondary);
            cursor: pointer;
            font-weight: 600;
            border: 2px solid var(--card-border);
            box-shadow: 0 8px 20px var(--orange-shadow);
            transition: all .35s ease;
            position: relative;
            overflow: hidden;
            font-size: 16px;
            white-space: nowrap;
        }

        .switch-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--orange-gradient-start), var(--orange-primary));
            opacity: 0;
            transition: opacity 0.35s ease;
        }

        .switch-btn:hover {
            transform: translateY(-3px);
            border-color: var(--orange-primary);
            box-shadow: 0 12px 30px var(--orange-shadow);
        }

        .switch-btn.active {
            background: linear-gradient(135deg, var(--orange-gradient-start), var(--orange-primary));
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 12px 25px var(--orange-shadow);
            border-color: var(--orange-primary);
        }

        .switch-btn.active::before {
            opacity: 0;
        }

        .switch-btn span {
            position: relative;
            z-index: 1;
        }

        /* Upload Card */
        .section-box {
            max-width: 1000px;
            margin: auto;
            background: var(--card);
            border: 2px solid var(--card-border);
            border-radius: 28px;
            padding: 40px;
            box-shadow: 0 10px 25px var(--orange-shadow), 0 20px 50px rgba(255, 152, 0, 0.08);
            transition: all .35s ease;
            width: 100%;
        }

        .section-box:hover {
            box-shadow: 0 15px 35px var(--orange-shadow), 0 25px 60px rgba(255, 152, 0, 0.1);
        }

        .section-box h2 {
            text-align: center;
            margin-bottom: 30px;
            color: var(--orange-primary);
            font-size: 28px;
            font-weight: 700;
            position: relative;
            word-wrap: break-word;
        }

        .section-box h2::after {
            content: '';
            display: block;
            width: 60px;
            height: 4px;
            background: linear-gradient(90deg, var(--orange-gradient-start), var(--orange-primary));
            margin: 10px auto 0;
            border-radius: 10px;
        }

        /* Form */
        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            color: var(--text);
            font-weight: 600;
            display: block;
            margin-bottom: 6px;
            font-size: 14px;
            letter-spacing: 0.3px;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 14px 18px;
            border-radius: 15px;
            background: var(--input-bg);
            border: 2px solid var(--input-border);
            color: var(--text);
            font-size: 15px;
            transition: all .35s ease;
            font-family: 'Segoe UI', sans-serif;
            -webkit-appearance: none;
            appearance: none;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--orange-primary);
            box-shadow: 0 0 0 4px var(--orange-subtle);
            background: var(--card);
        }

        .form-group input::placeholder,
        .form-group textarea::placeholder {
            color: var(--secondary);
            opacity: 0.6;
        }

        textarea {
            resize: none;
        }

        /* Submit Button */
        .btn {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 15px;
            background: linear-gradient(135deg, var(--orange-gradient-start), var(--orange-primary));
            color: white;
            font-weight: 700;
            cursor: pointer;
            transition: all .35s ease;
            margin-top: 20px;
            height: 55px;
            font-size: 17px;
            box-shadow: 0 10px 25px var(--orange-shadow);
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
            transform: translateY(-4px);
            box-shadow: 0 15px 35px var(--orange-shadow);
            background: linear-gradient(135deg, var(--orange-primary), var(--orange-dark));
        }

        .btn:active {
            transform: translateY(0px);
        }

        /* Page Title */
        .page-title {
            color: var(--text);
        }

        .upload-area {
            border: 3px dashed var(--orange-primary);
            border-radius: 24px;
            padding: 50px 20px;
            text-align: center;
            cursor: pointer;
            background: var(--orange-subtle);
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
            transform: translateY(-4px);
            box-shadow: 0 15px 30px var(--orange-shadow);
            border-color: var(--orange-dark);
        }

        .upload-area.dragover {
            border-color: var(--orange-dark);
            background: rgba(255, 152, 0, 0.15);
            transform: scale(1.02);
            box-shadow: 0 0 30px var(--orange-shadow);
        }

        .upload-icon {
            font-size: 55px;
            margin-bottom: 10px;
            display: block;
            position: relative;
            z-index: 1;
        }

        .upload-area h3 {
            margin-bottom: 10px;
            color: var(--text);
            font-weight: 600;
            position: relative;
            z-index: 1;
            font-size: 20px;
        }

        .upload-area p {
            color: var(--secondary);
            position: relative;
            z-index: 1;
            font-size: 16px;
        }

        #fileName {
            display: block;
            margin-top: 15px;
            font-weight: 600;
            color: var(--orange-primary);
            position: relative;
            z-index: 1;
            word-break: break-all;
        }

        /* Dark mode specific styles for upload area */
        body.dark .upload-area {
            background: rgba(255, 152, 0, 0.06);
        }

        body.dark .upload-area.dragover {
            background: rgba(255, 152, 0, 0.15);
        }

        /* Success/Error message styles */
        .message {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-weight: 600;
            display: none;
        }

        .message.success {
            display: block;
            background: rgba(76, 175, 80, 0.1);
            border: 1px solid #4CAF50;
            color: #4CAF50;
        }

        .message.error {
            display: block;
            background: rgba(244, 67, 54, 0.1);
            border: 1px solid #f44336;
            color: #f44336;
        }

        /* Smooth scrollbar */
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

        /* Large tablets and small laptops */
        @media (max-width: 1024px) {
            .main-content {
                padding: 30px 25px;
            }
            
            .switch-btn {
                min-width: 170px;
                padding: 11px 18px;
                font-size: 15px;
            }
            
            .section-box {
                padding: 35px 30px;
            }
        }

        /* Tablets and small devices */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 85px;
                padding: 20px 15px;
            }

            .top-buttons {
                justify-content: center;
                flex-wrap: wrap;
                gap: 12px;
            }
            
            .switch-btn {
                min-width: 140px;
                padding: 10px 16px;
                font-size: 14px;
                white-space: normal;
                word-break: break-word;
            }
            
            .section-box {
                padding: 25px 20px;
                border-radius: 20px;
            }
            
            .section-box h2 {
                font-size: 24px;
            }
            
            .upload-area {
                padding: 40px 15px;
            }
            
            .upload-icon {
                font-size: 45px;
            }
            
            .upload-area h3 {
                font-size: 18px;
            }
            
            .upload-area p {
                font-size: 14px;
            }
            
            .btn {
                height: 50px;
                font-size: 16px;
                padding: 12px;
            }
            
            .form-group input,
            .form-group textarea,
            .form-group select {
                padding: 12px 15px;
                font-size: 14px;
            }
        }

        /* Mobile phones */
        @media (max-width: 480px) {
            .main-content {
                margin-left: 0;
                padding: 15px 12px;
                width: 100%;
            }

            .main-content.expanded {
                margin-left: 0;
            }

            .top-buttons {
                flex-direction: column;
                align-items: stretch;
                gap: 10px;
                margin-bottom: 20px;
            }
            
            .switch-btn {
                min-width: unset;
                width: 100%;
                padding: 12px 16px;
                font-size: 14px;
                justify-content: center;
                white-space: normal;
                word-break: break-word;
            }
            
            .section-box {
                padding: 20px 15px;
                border-radius: 16px;
                border-width: 1.5px;
            }
            
            .section-box h2 {
                font-size: 20px;
                margin-bottom: 20px;
            }
            
            .section-box h2::after {
                width: 40px;
                height: 3px;
            }
            
            .upload-area {
                padding: 30px 12px;
                border-radius: 18px;
                border-width: 2px;
            }
            
            .upload-icon {
                font-size: 38px;
                margin-bottom: 8px;
            }
            
            .upload-area h3 {
                font-size: 16px;
                margin-bottom: 6px;
            }
            
            .upload-area p {
                font-size: 13px;
            }
            
            #fileName {
                font-size: 13px;
                margin-top: 10px;
            }
            
            .form-group {
                margin-bottom: 14px;
            }
            
            .form-group label {
                font-size: 13px;
                margin-bottom: 4px;
            }
            
            .form-group input,
            .form-group textarea,
            .form-group select {
                padding: 10px 14px;
                font-size: 14px;
                border-radius: 12px;
                border-width: 1.5px;
            }
            
            textarea {
                min-height: 60px;
            }
            
            .btn {
                height: 48px;
                font-size: 15px;
                padding: 10px;
                border-radius: 12px;
                margin-top: 16px;
            }
            
            .btn::before {
                display: none; /* Disable shine effect on mobile for performance */
            }
            
            .upload-area:hover {
                transform: none; /* Disable hover transform on mobile */
            }
            
            .switch-btn:hover {
                transform: none; /* Disable hover transform on mobile */
            }
            
            .switch-btn.active {
                transform: none; /* Disable transform on mobile */
            }
        }

        /* Very small phones */
        @media (max-width: 380px) {
            .main-content {
                padding: 10px 8px;
            }
            
            .section-box {
                padding: 15px 12px;
                border-radius: 14px;
            }
            
            .section-box h2 {
                font-size: 18px;
            }
            
            .upload-area {
                padding: 25px 10px;
            }
            
            .upload-icon {
                font-size: 32px;
            }
            
            .upload-area h3 {
                font-size: 14px;
            }
            
            .upload-area p {
                font-size: 12px;
            }
            
            .form-group input,
            .form-group textarea,
            .form-group select {
                padding: 8px 12px;
                font-size: 13px;
                border-radius: 10px;
            }
            
            .btn {
                height: 44px;
                font-size: 14px;
                padding: 8px;
                border-radius: 10px;
            }
        }

        /* Fix for sidebar overlap on mobile */
        @media (max-width: 768px) {
            body {
                overflow-x: hidden;
            }
            
            .main-content {
                overflow-x: hidden;
            }
            
            .section-box {
                overflow-x: hidden;
            }
        }

        /* Improve touch targets on mobile */
        @media (max-width: 480px) {
            .switch-btn,
            .btn,
            .upload-area {
                touch-action: manipulation;
                -webkit-tap-highlight-color: transparent;
            }
            
            .form-group input,
            .form-group textarea,
            .form-group select {
                font-size: 16px !important; /* Prevents iOS zoom on focus */
            }
        }

        /* Fix for landscape mode on phones */
        @media (max-height: 500px) and (orientation: landscape) {
            .main-content {
                padding: 12px 15px;
            }
            
            .section-box {
                padding: 15px 20px;
            }
            
            .section-box h2 {
                font-size: 20px;
                margin-bottom: 15px;
            }
            
            .upload-area {
                padding: 20px 15px;
            }
            
            .upload-icon {
                font-size: 30px;
            }
            
            .form-group {
                margin-bottom: 10px;
            }
            
            .btn {
                height: 40px;
                font-size: 14px;
                margin-top: 12px;
            }
        }
    </style>
</head>

<script>
    function showSection(sectionId, btn) {
        document.getElementById("fileSection").style.display = "none";
        document.getElementById("manualSection").style.display = "none";

        document.getElementById(sectionId).style.display = "block";

        document.querySelectorAll(".switch-btn")
            .forEach(button => button.classList.remove("active"));

        btn.classList.add("active");
    }
</script>

<body>
    <div class="main-content" id="mainContent">
        <div class="top-buttons">
            <button class="switch-btn active" onclick="showSection('fileSection', this)">
                <span>📤 Upload File</span>
            </button>

            <button class="switch-btn" onclick="showSection('manualSection', this)">
                <span>➕ Upload Manually</span>
            </button>
        </div>

        <div id="fileSection" class="section-box">

            <h2>Upload CSV File</h2>

            <form method="post" enctype="multipart/form-data">

                <div class="upload-area" id="uploadArea">

                    <input
                        type="file"
                        id="csv_file"
                        name="csv_file"
                        accept=".csv"
                        hidden
                        required>

                    <div class="upload-icon">📁</div>

                    <h3>Drop CSV file here</h3>

                    <p>or click to browse</p>

                    <span id="fileName">
                        No file selected
                    </span>

                </div>

                <br><br>

                <button type="submit" name="upload" class="btn">
                    📤 Upload CSV
                </button>

            </form>

        </div>

        <div id="manualSection" class="section-box" style="display:none;">

            <h2>Add Agency</h2>

            <form method="post">

                <div class="form-group">
                    <label>Agency Name</label>
                    <input type="text" name="agency_name" placeholder="Enter agency name">
                </div>

                <div class="form-group">
                    <label>Customer Name</label>
                    <input type="text" name="customer_name" placeholder="Enter customer name">
                </div>

                <div class="form-group">
                    <label>Mobile Number</label>
                    <input type="text" name="mob_number" placeholder="Enter mobile number">
                </div>

                <div class="form-group">
                    <label>Alternate Number</label>
                    <input type="text" name="alt_number" placeholder="Enter alternate number">
                </div>

                <div class="form-group">
                    <label>Email ID</label>
                    <input type="email" name="mail_id" placeholder="Enter email address">
                </div>

                <div class="form-group">
                    <label>City</label>
                    <input type="text" name="city" placeholder="Enter city">
                </div>

                <div class="form-group">
                    <label>State</label>
                    <input type="text" name="state" placeholder="Enter state">
                </div>

                <div class="form-group">
                    <label>Address</label>
                    <textarea name="address" rows="3" placeholder="Enter full address"></textarea>
                </div>

                <div class="form-group">
                    <label>Feedback</label>
                    <textarea name="feedback" rows="3" placeholder="Enter feedback"></textarea>
                </div>

                <button type="submit" name="submit" class="btn">
                    ➕ Add Agency
                </button>

            </form>

        </div>
    </div>

    <script>
        const uploadArea = document.getElementById("uploadArea");
        const fileInput = document.getElementById("csv_file");
        const fileName = document.getElementById("fileName");

        uploadArea.addEventListener("click", () => {
            fileInput.click();
        });

        fileInput.addEventListener("change", () => {

            if (fileInput.files.length) {
                fileName.innerText =
                    fileInput.files[0].name;
            }
        });

        uploadArea.addEventListener("dragover", (e) => {

            e.preventDefault();
            uploadArea.classList.add("dragover");
        });

        uploadArea.addEventListener("dragleave", () => {

            uploadArea.classList.remove("dragover");
        });

        uploadArea.addEventListener("drop", (e) => {

            e.preventDefault();

            uploadArea.classList.remove("dragover");

            fileInput.files = e.dataTransfer.files;

            if (e.dataTransfer.files.length) {

                fileName.innerText =
                    e.dataTransfer.files[0].name;
            }
        });
    </script>
</body>

</html>