<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login_form.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT fullname, email, gender, dob, location, created_at FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
} else {
    $_SESSION['error'] = "User details not found.";
    header("Location: login_form.php");
    exit();
}

$sql_hospitals = "SELECT h.hospital_id FROM hospitals h 
                  INNER JOIN user_insurance_hospitals uih ON h.hospital_id = uih.hospital_id 
                  WHERE uih.user_id = ?";
$stmt_hospitals = $conn->prepare($sql_hospitals);
$stmt_hospitals->bind_param("i", $user_id);
$stmt_hospitals->execute();
$result_hospitals = $stmt_hospitals->get_result();

$insurance_hospitals = [];
while ($row = $result_hospitals->fetch_assoc()) {
    $insurance_hospitals[] = $row['hospital_id'];
}

$stmt_hospitals->close();

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
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
        .navbar .auth-buttons .logoutBtn, 
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

        .navbar .auth-buttons .logoutBtn, 
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

        .logoutBtn-btn {
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

        .logoutBtn-btn:hover {
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

        .logoutBtn-link {
            font-size: 0.9rem;
            margin-top: 15px;
        }

        .logoutBtn-link a {
            color: #095d7e;
            text-decoration: none;
            font-weight: bold;
        }

        .logoutBtn-link a:hover {
            text-decoration: underline;
        }

        .form-group {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: center;
        }

        .checkbox-container {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 6px;
            background-color: #fff;
            padding: 6px 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
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
                <?php
                if (isset($_SESSION['user_id'])) {
                    // Fetch user data
                    include 'db_connection.php';
                    $user_id = $_SESSION['user_id'];
                    $query = "SELECT fullname, email, gender, dob, location, created_at FROM users WHERE id = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $user = $result->fetch_assoc();
                ?>
                    <a href="logout.php" class="logout">Logout</a>
                <?php
                } else {
                ?>
                    <a href="login_form.php" class="signup">Login</a>
                    <a href="sign_up.html" class="signup">Sign Up</a>
                <?php
                }
                ?>
            </div>
        </div>

        <div class="container">
            <p class="features-subheading">
                <span class="hero-heading">Personalised Patient Portal</span>
                
                <?php if (isset($_SESSION['user_id'])) { ?>
                    <div class="form-group">
                        <label for="name">Name:</label>
                        <input type="text" id="name" value="<?php echo htmlspecialchars($user['fullname']); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label for="age">Age:</label>
                        <input type="number" id="age" value="<?php echo date_diff(date_create($user['dob']), date_create('today'))->y; ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label for="gender">Gender:</label>
                        <input type="text" id="gender" value="<?php echo htmlspecialchars($user['gender']); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="text" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label for="joined">Joined On:</label>
                        <input type="text" id="joined" value="<?php echo date("d M Y", strtotime($user['created_at'])); ?>" readonly>
                    </div>
                    <form method="POST" action="update_profile.php">
                        <div class="form-group">
                            <label for="location">Location:</label>
                            <input type="text" name="location" value="<?php echo htmlspecialchars($user['location']); ?>">
                        </div>

                        <div class="form-group">
                            <label for="insurance_hospitals" style="display: block;">Insurance Hospitals:</label>
                            <div class="checkbox-container">
                                <?php
                                $hospitalQuery = "SELECT hospital_id, name FROM hospitals";
                                $hospitalResult = $conn->query($hospitalQuery);

                                while ($hospital = $hospitalResult->fetch_assoc()) {
                                    $checked = in_array($hospital['hospital_id'], $insurance_hospitals) ? 'checked' : '';
                                    echo "<label class='checkbox-item'><input type='checkbox' name='insurance_hospitals[]' value='{$hospital['hospital_id']}' $checked> {$hospital['name']}</label>";
                                }
                                ?>
                            </div>
                        </div>

                        <button type="submit" class="logoutBtn-btn">Update Profile</button>
                    </form>
                <?php } else { ?>
                    <p>Please <a href="login_form.php">log in</a> to view your details.</p>
                <?php } ?>
                
            </p><br>
        </div>

    </div>
</body>

</html>
