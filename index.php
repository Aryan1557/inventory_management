
<!DOCTYPE html>
<html>

<head>
    <title>Inventory Management System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            scroll-behavior: smooth;
            background: #0a0a0a;
        }

        :root {
            --bg: #0a0a0a;
            --text: #f8fafc;
            --card: #1a0a0a;
            --border: #3b0a0a;
            --secondary: #cbd5e1;
            --orange-primary: #f57c00;
            --orange-light: #ffb74d;
            --orange-dark: #e65100;
            --orange-gradient-start: #ff9800;
            --orange-gradient-end: #f57c00;
            --orange-shadow: rgba(245, 124, 0, 0.15);
        }

        /* Splash Screen */
        #splash {
            position: fixed;
            width: 100%;
            height: 100vh;
            background: linear-gradient(135deg, #e65100, #f57c00);
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            z-index: 9999;
            animation: fadeOut 1s ease 1.5s forwards;
        }

        #splash img {
            width: 120px;
            padding: 15px;
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 152, 0, 0.4);
            border-radius: 50%;
            box-shadow: 0 8px 32px rgba(255, 152, 0, 0.3);
            animation: zoom 2s infinite;
        }

        #splash h1 {
            margin-top: 30px;
            padding: 15px 30px;
            color: white;
            font-size: 32px;
            font-weight: bold;
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 152, 0, 0.4);
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(255, 152, 0, 0.3);
            text-shadow: 2px 2px 10px rgba(0, 0, 0, 0.5);
        }

        @keyframes zoom {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.2);
            }
        }

        @keyframes fadeOut {
            to {
                opacity: 0;
                visibility: hidden;
            }
        }

        /* Navigation Bar */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 80px;
            padding: 0 60px;
            background: rgba(10, 0, 0, 0.95);
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 3px 12px var(--orange-shadow);
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--orange-primary);
            font-size: 24px;
            font-weight: bold;
        }

        .logo img {
            width: 62px;
            height: 62px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid rgba(255, 152, 0, 0.6);
            transition: 0.3s ease;
            cursor: pointer;
        }

        .logo img:hover {
            transform: scale(1.08);
            box-shadow: 0 0 15px rgba(255, 152, 0, 0.5);
        }

        nav ul {
            list-style: none;
            display: flex;
            gap: 10px;
            align-items: center;
        }

        nav ul li a {
            color: var(--secondary);
            text-decoration: none;
            font-size: 16px;
            font-weight: 500;
            padding: 10px 20px;
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        nav ul li a:hover {
            background: rgba(255, 152, 0, 0.15);
            color: var(--orange-primary);
        }

        .btn-nav {
            background: linear-gradient(135deg, var(--orange-gradient-start), var(--orange-primary)) !important;
            color: #fff !important;
            padding: 11px 22px !important;
            border-radius: 30px !important;
            font-weight: 600 !important;
            transition: .3s;
            box-shadow: 0 4px 15px var(--orange-shadow);
        }

        .btn-nav:hover {
            background: linear-gradient(135deg, var(--orange-primary), var(--orange-dark)) !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(245, 124, 0, 0.6);
        }

        .menu-toggle {
            display: none;
            font-size: 30px;
            color: #f8fafc;
            cursor: pointer;
        }

        @media(max-width:768px) {
            nav {
                padding: 0 20px;
            }

            .logo img {
                width: 55px;
                height: 55px;
            }

            .menu-toggle {
                display: block;
            }

            .nav-links {
                position: absolute;
                top: 80px;
                left: 0;
                width: 100%;
                background: #1a0a0a;
                flex-direction: column;
                align-items: center;
                gap: 20px;
                padding: 25px 0;
                display: none;
                box-shadow: 0 8px 15px var(--orange-shadow);
            }

            .nav-links.active {
                display: flex;
            }

            .nav-links a {
                color: #f8fafc;
            }

            .nav-links a:hover {
                color: var(--orange-primary);
            }
        }

        /* Hero Section */
        .hero {
            height: 100vh;
            background: linear-gradient(135deg, #1a0a0a 0%, #0a0a0a 50%, #2d0a0a 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            width: 600px;
            height: 600px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 152, 0, 0.25), transparent);
            top: -150px;
            right: -150px;
        }

        .hero::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(245, 124, 0, 0.2), transparent);
            bottom: -100px;
            left: -100px;
        }

        .particles {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        .particle {
            position: absolute;
            width: 6px;
            height: 6px;
            background: rgba(255, 152, 0, 0.9);
            border-radius: 50%;
            animation: floatUp 6s infinite;
            box-shadow: 0 0 10px rgba(255, 152, 0, 0.9), 0 0 20px rgba(245, 124, 0, 0.6), 0 0 40px rgba(230, 81, 0, 0.4);
        }

        .particle:nth-child(1) {
            left: 10%;
            animation-delay: 0s;
        }
        .particle:nth-child(2) {
            left: 20%;
            animation-delay: 1s;
            width: 4px;
            height: 4px;
        }
        .particle:nth-child(3) {
            left: 30%;
            animation-delay: 2s;
            width: 8px;
            height: 8px;
        }
        .particle:nth-child(4) {
            left: 40%;
            animation-delay: 0.5s;
        }
        .particle:nth-child(5) {
            left: 50%;
            animation-delay: 1.5s;
            width: 5px;
            height: 5px;
        }
        .particle:nth-child(6) {
            left: 60%;
            animation-delay: 2.5s;
        }
        .particle:nth-child(7) {
            left: 70%;
            animation-delay: 0.8s;
            width: 7px;
            height: 7px;
        }
        .particle:nth-child(8) {
            left: 80%;
            animation-delay: 1.8s;
        }
        .particle:nth-child(9) {
            left: 90%;
            animation-delay: 2.2s;
            width: 3px;
            height: 3px;
        }
        .particle:nth-child(10) {
            left: 15%;
            animation-delay: 3s;
        }
        .particle:nth-child(11) {
            left: 25%;
            animation-delay: 0.3s;
            width: 5px;
            height: 5px;
        }
        .particle:nth-child(12) {
            left: 35%;
            animation-delay: 1.3s;
        }
        .particle:nth-child(13) {
            left: 45%;
            animation-delay: 2.8s;
            width: 6px;
            height: 6px;
        }
        .particle:nth-child(14) {
            left: 55%;
            animation-delay: 0.7s;
        }
        .particle:nth-child(15) {
            left: 65%;
            animation-delay: 1.9s;
            width: 4px;
            height: 4px;
        }
        .particle:nth-child(16) {
            left: 75%;
            animation-delay: 2.3s;
        }
        .particle:nth-child(17) {
            left: 85%;
            animation-delay: 1.1s;
            width: 7px;
            height: 7px;
        }
        .particle:nth-child(18) {
            left: 5%;
            animation-delay: 2.7s;
        }
        .particle:nth-child(19) {
            left: 95%;
            animation-delay: 0.4s;
            width: 5px;
            height: 5px;
        }
        .particle:nth-child(20) {
            left: 48%;
            animation-delay: 3.2s;
        }

        @keyframes floatUp {
            0% {
                transform: translateY(100vh) scale(0) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            50% {
                transform: translateY(50vh) scale(1.5) rotate(180deg);
            }
            90% {
                opacity: 1;
            }
            100% {
                transform: translateY(-100px) scale(0.5) rotate(360deg);
                opacity: 0;
            }
        }

        .content {
            width: min(90%, 800px);
            padding: 50px;
            background: rgba(26, 10, 10, 0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 152, 0, 0.3);
            border-radius: 24px;
            color: white;
            text-align: center;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6), 0 0 80px rgba(255, 152, 0, 0.2);
            position: relative;
            z-index: 1;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        .content h1 {
            font-size: 48px;
            margin-bottom: 20px;
            background: linear-gradient(135deg, var(--orange-light), var(--orange-primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .content p {
            font-size: 20px;
            margin-bottom: 30px;
            opacity: 0.9;
            line-height: 1.6;
            color: #cbd5e1;
        }

        .btn {
            background: linear-gradient(135deg, var(--orange-gradient-start), var(--orange-primary));
            color: white;
            text-decoration: none;
            padding: 15px 35px;
            border-radius: 15px;
            font-size: 18px;
            font-weight: 600;
            display: inline-block;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px var(--orange-shadow);
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(245, 124, 0, 0.6);
            background: linear-gradient(135deg, var(--orange-primary), var(--orange-dark));
        }

        /* Parallax Sections */
        .parallax {
            position: relative;
            min-height: 100vh;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .parallax::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(rgba(10, 0, 0, 0.92), rgba(10, 0, 0, 0.88));
        }

        .parallax1 {
            background-image: url('https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?q=80&w=2070');
        }

        .parallax2 {
            background-image: url('https://images.unsplash.com/photo-1553413077-190dd305871c?q=80&w=2070');
        }

        .parallax3 {
            background-image: url('https://images.unsplash.com/photo-1517048676732-d65bc937f952?q=80&w=2070');
        }

        .parallax-content {
            position: relative;
            z-index: 2;
            padding: 60px;
            background: rgba(26, 10, 10, 0.75);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 24px;
            width: min(90%, 800px);
            border: 1px solid rgba(255, 152, 0, 0.25);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6), 0 0 60px rgba(255, 152, 0, 0.15);
            color: white;
            opacity: 0;
            transform: translateY(50px);
            transition: 1s ease;
            text-align: center;
        }

        .parallax-content.show {
            opacity: 1;
            transform: translateY(0);
        }

        .parallax-icon {
            font-size: 80px;
            margin-bottom: 20px;
            display: block;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }
        }

        .parallax-content h1 {
            font-size: 42px;
            margin-bottom: 20px;
            background: linear-gradient(135deg, var(--orange-light), var(--orange-primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .parallax-content p {
            font-size: 18px;
            line-height: 1.8;
            color: #cbd5e1;
        }

        /* Info Sections */
        .info-section {
            background: var(--bg);
            color: var(--text);
            padding: 100px 50px;
            text-align: center;
        }

        .info-section h2 {
            font-size: 40px;
            margin-bottom: 20px;
            background: linear-gradient(135deg, var(--orange-light), var(--orange-primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .info-section p {
            max-width: 800px;
            margin: auto;
            line-height: 1.8;
            font-size: 18px;
            color: var(--secondary);
        }

        /* Features Section */
        .features-section {
            background: var(--bg);
            padding: 80px 50px;
        }

        .features-title {
            text-align: center;
            color: var(--text);
            font-size: 42px;
            margin-bottom: 15px;
            background: linear-gradient(135deg, var(--orange-light), var(--orange-primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .features-subtitle {
            text-align: center;
            color: var(--secondary);
            font-size: 18px;
            margin-bottom: 50px;
        }

        .cards-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 30px;
            color: var(--text);
            box-shadow: 0 4px 10px var(--orange-shadow), 0 10px 25px rgba(0, 0, 0, 0.5);
            transition: all 0.35s ease;
            position: relative;
            overflow: hidden;
            opacity: 0;
            transform: translateY(40px);
            transition: all 0.6s ease;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: linear-gradient(90deg, var(--orange-gradient-start), var(--orange-primary), var(--orange-dark));
        }

        .card.show {
            opacity: 1;
            transform: translateY(0);
        }

        .card:hover {
            transform: translateY(-10px) scale(1.03);
            box-shadow: 0 15px 35px var(--orange-shadow), 0 25px 60px rgba(0, 0, 0, 0.6);
            border-color: var(--orange-primary);
        }

        .card h3 {
            margin-bottom: 15px;
            color: var(--orange-primary);
            font-size: 22px;
        }

        .card p {
            line-height: 1.6;
            font-size: 15px;
            color: var(--secondary);
        }

        /* Contact Section */
        #contact-section {
            background: var(--bg);
            padding: 80px 50px;
        }

        .contact-title {
            text-align: center;
            color: var(--text);
            font-size: 42px;
            margin-bottom: 50px;
            background: linear-gradient(135deg, var(--orange-light), var(--orange-primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .contact-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .contact-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 30px;
            color: var(--text);
            box-shadow: 0 4px 10px var(--orange-shadow), 0 10px 25px rgba(0, 0, 0, 0.5);
            transition: all 0.35s ease;
            position: relative;
            overflow: hidden;
            opacity: 0;
            transform: translateY(40px);
            transition: all 0.6s ease;
        }

        .contact-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: linear-gradient(90deg, var(--orange-gradient-start), var(--orange-primary));
        }

        .contact-card.show {
            opacity: 1;
            transform: translateY(0);
        }

        .contact-card:hover {
            transform: translateY(-10px) scale(1.03);
            box-shadow: 0 15px 35px var(--orange-shadow), 0 25px 60px rgba(0, 0, 0, 0.6);
            border-color: var(--orange-primary);
        }

        .contact-card h3 {
            color: var(--orange-primary);
            margin-bottom: 15px;
            font-size: 24px;
        }

        .contact-card p {
            line-height: 1.8;
            font-size: 16px;
            color: var(--secondary);
        }

        /* Footer */
        .footer {
            background: var(--card);
            border-top: 1px solid var(--border);
            padding: 30px;
            text-align: center;
            color: var(--secondary);
        }

        @media(max-width:768px) {
            .parallax {
                background-attachment: scroll;
            }

            .parallax-content {
                padding: 30px;
            }

            .parallax-content h1 {
                font-size: 28px;
            }

            .parallax-content p {
                font-size: 16px;
            }

            .parallax-icon {
                font-size: 60px;
            }

            .content h1 {
                font-size: 32px;
            }

            .features-title,
            .contact-title {
                font-size: 30px;
            }

            .cards-container,
            .contact-container {
                padding: 20px;
            }
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--bg);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--orange-primary);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--orange-dark);
        }
    </style>
</head>

<body>
    <!-- Splash Screen -->
    <div id="splash">
        <img src="images/logo.jpeg" alt="Logo">
        <h1>Inventory Management System</h1>
    </div>

    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="logo">
            <a href="#">
                <img src="images/logo.jpeg" alt="Logo">
            </a>
        </div>
        <div class="menu-toggle">☰</div>
        <ul class="nav-links">
            <li><a href="#home">Home</a></li>
            <li><a href="#features-section">Features</a></li>
            <li><a href="#contact-section">Contact</a></li>
            <li><a href="login.php" class="btn-nav">Login</a></li>
        </ul>
    </nav>

    <!-- Hero Section -->
    <div class="hero" id="home">
        <div class="particles">
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
        </div>
        <div class="content">
            <h1>Smart Inventory Management</h1>
            <p>
                Manage products, stock levels, suppliers, orders, and reports
                with our modern, efficient inventory solution.
            </p>
            <a href="login.php" class="btn">Get Started →</a>
        </div>
    </div>

    <!-- Parallax Section 1 -->
    <section class="parallax parallax1">
        <div class="parallax-content">
            <span class="parallax-icon">📊</span>
            <h1>Smart Inventory Control</h1>
            <p>
                Track products, monitor stock levels and manage your business
                operations with a modern inventory solution designed for efficiency
                and growth.
            </p>
        </div>
    </section>

    <!-- Info Section 1 -->
    <section class="info-section">
        <h2>Manage Everything Efficiently</h2>
        <p>
            Keep complete control over your inventory with real-time updates,
            automated tracking and detailed reporting tools that help you make
            informed business decisions.
        </p>
    </section>

    <!-- Parallax Section 2 -->
    <section class="parallax parallax2">
        <div class="parallax-content">
            <span class="parallax-icon">📈</span>
            <h1>Real-Time Analytics</h1>
            <p>
                Gain valuable insights into inventory performance and sales trends
                with powerful analytics tools that drive business growth.
            </p>
        </div>
    </section>

    <!-- Info Section 2 -->
    <section class="info-section">
        <h2>Data Driven Decisions</h2>
        <p>
            Visualize business growth through advanced reports and performance
            analytics that help improve productivity and streamline operations
            across your organization.
        </p>
    </section>

    <!-- Parallax Section 3 -->
    <section class="parallax parallax3">
        <div class="parallax-content">
            <span class="parallax-icon">🔒</span>
            <h1>Secure & Reliable</h1>
            <p>
                Protect your business data with authentication and role-based
                access control ensuring only authorized personnel can access
                sensitive information.
            </p>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section" id="features-section">
        <h1 class="features-title">Powerful Features</h1>
        <p class="features-subtitle">Everything you need to manage inventory efficiently</p>
        <div class="cards-container">
            <div class="card">
                <h3>📦 Product Management</h3>
                <p>Add, update, delete, and organize products with detailed information such as name, category, price, and quantity.</p>
            </div>
            <div class="card">
                <h3>📊 Inventory Tracking</h3>
                <p>Monitor stock levels in real time and maintain accurate inventory records across all products.</p>
            </div>
            <div class="card">
                <h3>⚠️ Low Stock Alerts</h3>
                <p>Receive notifications when product quantities fall below predefined limits to avoid stock shortages.</p>
            </div>
            <div class="card">
                <h3>🛒 Order Management</h3>
                <p>Create, process, and track customer orders efficiently while maintaining inventory accuracy.</p>
            </div>
            <div class="card">
                <h3>🏢 Supplier Management</h3>
                <p>Store supplier information, manage purchases, and track product deliveries from vendors.</p>
            </div>
            <div class="card">
                <h3>📈 Reports & Analytics</h3>
                <p>Generate sales reports, stock reports, and performance analytics for better decision-making.</p>
            </div>
            <div class="card">
                <h3>🔍 Search & Filter</h3>
                <p>Quickly find products using advanced search and filtering options based on categories and stock status.</p>
            </div>
            <div class="card">
                <h3>🔒 Secure User Access</h3>
                <p>Protect inventory data with user authentication and role-based access control.</p>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact-section">
        <h1 class="contact-title">Contact Us</h1>
        <div class="contact-container">
            <div class="contact-card">
                <h3>📞 Phone</h3>
                <p>+91 92255 24747</p>
                <p>+91 77559 79797</p>
            </div>
            <div class="contact-card">
                <h3>📧 Email</h3>
                <p>support@ebiztech.in</p>
                <p>devloper@ebiztech.in</p>
            </div>
            <div class="contact-card">
                <h3>📍 Address</h3>
                <p>Ebiztech<br>KK.Market, 4th Floor Office no:-89 D-wing, Dhankawadi, pune - 411043 </p>
            </div>
            <div class="contact-card">
                <h3>🕒 Working Hours</h3>
                <p>Monday - Friday</p>
                <p>9:00 AM - 6:00 PM</p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <div class="footer">
        <p>&copy; <?php echo date('Y'); ?> Inventory Management System. All rights reserved.</p>
    </div>

    <script>
        const menuToggle = document.querySelector(".menu-toggle");
        const navLinks = document.querySelector(".nav-links");
        menuToggle.addEventListener("click", () => {
            navLinks.classList.toggle("active");
        });

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add("show");
                }
            });
        }, {
            threshold: 0.2
        });

        document.querySelectorAll(".card, .contact-card, .parallax-content")
            .forEach(el => observer.observe(el));

        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
                navLinks.classList.remove('active');
            });
        });

        window.addEventListener('scroll', () => {
            const nav = document.querySelector('nav');
            if (window.scrollY > 100) {
                nav.style.background = 'rgba(10, 0, 0, 0.98)';
                nav.style.boxShadow = '0 4px 30px rgba(255, 152, 0, 0.3)';
            } else {
                nav.style.background = 'rgba(10, 0, 0, 0.95)';
                nav.style.boxShadow = '0 4px 20px rgba(255, 152, 0, 0.15)';
            }
        });
    </script>
</body>

</html>