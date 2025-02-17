<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
} 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CurePath</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            overflow-x: hidden; 
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            line-height: 1.6;
            background-color: #F8F9FA;
        }

        .firstportion {
            margin: 10px;
            background: url('bg.png') no-repeat center center/cover;
            min-height: 100vh;
        }

        /* Navbar Styling */
        .navbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 20px;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .navbar a:hover {
            color: #14967F;
            text-decoration: underline;
        }

        .navbar .logo img {
            height: 70px;
            margin-left: 30px;
        }

        .navbar nav {
            display: flex;
            justify-content: center;
            gap: 30px;
            flex: 1;
        }

        .navbar nav a {
            text-decoration: none;
            color: #095d7e;
            font-weight: bold;
            font-size: 0.9rem;
        }

        .navbar .auth-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: nowrap;
        }

        .navbar .auth-buttons .login, 
        .navbar .auth-buttons .signup {
            padding: 8px 20px;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: bold;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
        }

        .login {
            color: #095d7e;
            background-color: transparent;
        }

        .navbar .auth-buttons .signup {
            background-color: #095d7e;
            color: white;
        }

        @media (max-width: 768px) {
            .navbar nav {
                display: none; /* Hide menu items */
            }
        }

        .container {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 600;
            padding: 40px 80px;
            background-color: #F5FAFF;
            border-radius: 20px;
            max-width: 1200px;
            margin: 20px auto;
            border: 2px solid #cce7f5;
        }

        h1 {
            text-align: center;
            color: #333333;
        }

        .form-group {
            font-family: 'Plus Jakarta Sans', sans-serif;
            margin-bottom: 15px;  
            font-weight: 450;
        }

        label {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 450;
            display: block;
            color: #333;
        }

        input, select {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .signup-btn {
            justify-content: center;
            display: block;
            width: 200px;
            background-color: #095d7e;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }

        .signup-btn:hover {
            background-color: #14967F;
        }

        .features-subheading {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 30px;
            font-weight: 600;
            text-align: center; 
        }

        .highlight1 {
            color: #14967F;
        }

        .highlight2 {
            color: #095d7e;
        }
                
        .hero-heading {
            color: #095d7e; 
            font-weight: bold;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 35px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.4);
        }

        .sign-button {
            justify-content: center;
        }

        .forgot-password {
            font-size: 0.9rem;
            color: #555;
            margin-top: 10px;
        }

        .forgot-password a {
            color: #095d7e;
            text-decoration: none;
        }

        .forgot-password a:hover {
            text-decoration: underline;
        }

        .signup-link {
            font-size: 0.9rem;
            margin-top: 15px;
        }

        .signup-link a {
            color: #095d7e;
            text-decoration: none;
            font-weight: bold;
        }

        .signup-link a:hover {
            text-decoration: underline;
        }

        /* Footer Styles */
        .footer {
            color: #095d7e;
            padding: 40px 20px;
            font-weight: bold;
            font-family: 'Plus Jakarta Sans', sans-serif;
            text-align: center;
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .footer-logo {
            margin-bottom: 30px;
        }

        .footer-logo-img {
            height: 50px;
            margin-bottom: 15px;
        }

        .footer-description {
            font-size: 0.9rem;
            color: #095d7e;
        }

        .footer-nav {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 20px;
        }

        .footer-link {
            color: #095d7e;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .footer-link:hover {
            color: #14967F;
            text-decoration: underline;
        }

        .footer-social {
            margin-bottom: 20px;
        }

        .footer-social-link {
            margin: 0 10px;
            text-decoration: none;
        }

        .footer-social-icon {
            height: 30px;
        }

        .footer-contact {
            font-size: 0.9rem;
            color: #095d7e;
        }

        .footer-contact p {
            color: #095d7e;
        }

        .footer-email {
            color: #095d7e;
            text-decoration: none;
        }

        .footer-email:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="firstportion">
        <!-- Navbar -->
            <div class="navbar">
                <div class="logo">
                    <img src="logo.png" alt="CurePath Logo">
                </div>
                <nav class="navclass">
                    <a href="index.html">Home</a>
                    <a href="about.html">About</a>
                    <a href="features.html">Features</a>
                    <a href="#">Pricing Plans</a>
                    <a href="#">Contact Us</a>
                </nav>
                <div class="auth-buttons">
                    <a href="sign_up.html" class="signup">Sign up</a>
                </div>
            </div>

            <div class="container">
                <p class="features-subheading">
                    <span class="hero-heading">Login To Your Account</span>
                </p><br>

                <?php 
                if (isset($_SESSION['error'])): 
                    echo "<p style='color: red;'>" . $_SESSION['error'] . "</p>"; 
                    unset($_SESSION['error']);
                endif; 
                ?>

                <form action="login.php" method="POST">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="signup-btn">Login</button>
            </form>
            <p class="forgot-password"><a href="forgot_password_form.php">Forgot Password?</a></p>
            <p class="signup-link">Don't have an account? <a href="sign_up.html">Sign up here</a></p>
        </div>

        <!-- Footer Section -->
    <footer class="footer">
        <div class="footer-container">
            <!-- Logo and Description -->
            <div class="footer-logo">
                <img src="logo.png" alt="CurePath Logo" class="footer-logo-img">
                <p class="footer-description">
                    Empowering healthcare with smarter solutions. Discover the best doctors, tailored for your health needs.
                </p>
            </div>

            <!-- Navigation Links -->
            <nav class="footer-nav">
                <a href="index.html" class="footer-link">Home</a>
                <a href="about.html" class="footer-link">About</a>
                <a href="features.html" class="footer-link">Features</a>
                <a href="#" class="footer-link">Pricing Plans</a>
                <a href="#" class="footer-link">Contact Us</a>
            </nav>

            <!-- Contact and Copyright -->
            <div class="footer-contact">
                <p>© 2024 CurePath. All Rights Reserved.</p>
            </div>
        </div>
    </footer>
</div>
</body>
</html>
