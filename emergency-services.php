<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="logo.png">
    <title>Emergency Services - CurePath</title>
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
        .navbar .auth-buttons .signup,
        .navbar .auth-buttons .profile, 
        .navbar .auth-buttons .logout {
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

        .navbar .auth-buttons .signup,
        .navbar .auth-buttons .profile, 
        .navbar .auth-buttons .logout {
            background-color: #095d7e;
            color: white;
        }

        @media (max-width: 768px) {
            .navbar nav {
                display: none; /* Hide menu items */
            }
        }

        .container {
            padding: 40px 80px;
            background-color: #F5FAFF;
            border-radius: 20px;
            max-width: 1200px;
            margin: 20px auto;
            text-align: center;
            border: 2px solid #cce7f5;
        }
        
        .hero-heading {
            color: #095d7e; 
            font-weight: bold;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 35px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.4);
        }

        h1 {
            text-align: center;
            color: #095d7e;
        }

        p {
            line-height: 1.6;
            color: #555;
            margin: 15px 0;
        }

        .features {
            text-align: left;
            margin-top: 30px;
        }

        .features h2 {
            color: #333;
        }

        .feature-item {
            margin: 10px 0;
            padding: 15px;
            background: #f9f9f9;
            border-left: 5px solid #095d7e;
            border-radius: 5px;
            text-align: left;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .feature-item h3 {
            margin-bottom: 10px;
            color: #0c5a83;
        }

        ul {
            padding-left: 20px;
        }

        li {
            margin-bottom: 10px;
        }

        @media (max-width: 768px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }

        /* Footer Styles */
        .footer {
            color: #095d7e;
            padding: 40px 20px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: bold;
            text-align: center;
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .footer-logo {
            margin-bottom: 0px;
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
                    <a href="contactus.html">Contact Us</a>
                </nav>
                <div class="auth-buttons" id="auth-buttons">
                    <a href="login_form.php" class="login">Login</a>
                    <a href="sign_up.html" class="signup">Sign Up</a>
                </div>
        </div>

        <div class="container">
            <h1 class="hero-heading">Emergency Services - CurePath</h1>
            <p>
                In case of a medical emergency, immediate access to reliable healthcare can make all the difference.
                CurePath has compiled a list of major hospitals across Pakistan with their 24/7 emergency contact numbers to ensure you get timely assistance when it matters most.
            </p>

            <?php
                include 'db_connection.php';
                
                $mysqli = new mysqli($host, $username, $password, $dbname);
                // Query hospitals grouped by parent hospital
                $query = "SELECT h1.hospital_id, h1.name, h1.contact, h2.name AS parent_name
                        FROM hospitals h1
                        LEFT JOIN hospitals h2 ON h1.affiliated_hospital_id = h2.hospital_id
                        ORDER BY h2.name, h1.name";

                $result = $mysqli->query($query);

                $groupedHospitals = [];

                while ($row = $result->fetch_assoc()) {
                    $parentName = $row['parent_name'] ?? $row['name'];
                    $groupedHospitals[$parentName][] = [
                        'name' => $row['name'],
                        'contact' => $row['contact']
                    ];
                }
                ?>

                <?php foreach ($groupedHospitals as $group => $hospitals): ?>
                    <div class="feature-item">
                        <h3><?= htmlspecialchars($group) ?></h3>
                        <ul>
                            <?php foreach ($hospitals as $hospital): ?>
                                <li><strong><?= htmlspecialchars($hospital['name']) ?></strong><br>
                                    Contact: <?= htmlspecialchars($hospital['contact']) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>

                <p><strong>Note:</strong> The numbers listed are for hospital emergency departments and are based on official sources as of the latest update.</p>

        </div>
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
                    <a href="contactus.html" class="footer-link">Contact Us</a>
                </nav>

                <!-- Contact and Copyright -->
                <div class="footer-contact">
                    <p>© 2024 CurePath. All Rights Reserved.</p>
                </div>
            </div>
        </footer>

        <script>
            document.addEventListener("DOMContentLoaded", function () {
                fetch("auth_status.php")
                    .then(response => response.json())
                    .then(data => {
                        let authButtons = document.getElementById("auth-buttons");
                        if (data.logged_in) {
                           authButtons.innerHTML = `
                                    <a href="dashboard.php" class="profile">Profile</a>
                                    <a href="logout.php" class="logout">Logout</a>
                                `;
                        }
                    })
                    .catch(error => console.error("Error fetching auth status:", error));
            });
            </script>
</body>
</html>
