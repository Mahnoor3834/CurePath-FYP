<?php
$symptomParam = isset($_GET['symptoms']) ? $_GET['symptoms'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="logo.png">
    <title>Symptom Checker - CurePath</title>
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
            font-family: 'Plus Jakarta Sans', sans-serif;
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

        .diseases-list {
            margin: 10px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .disease-item {
            width: calc(33.33% - 10px); /* Adjust the width to fit 3 items per row */
            display: flex;
            align-items: center;
            gap: 10px; 
            margin-bottom: 10px;
        }

        button {
            display: block;
            width: 200px;
            padding: 10px;
            background-color: #095d7e;
            color: white;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #14967F;
        }

        .hero-heading {
            color: #095d7e; 
            font-weight: bold;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 35px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.4);
        }

        .results {
            margin-top: 20px;
        }

        .disease-info {
            margin: 10px 0;
            padding: 10px;
            background-color: #f9f9f9;
            border-left: 4px solid #095d7e;
        }

        .search-row {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }

        .search-input-box {
            padding: 10px;
            flex: 3;
            min-width: 200px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .search-clear-btn {
            padding: 10px;
            flex: 1;
            background-color: #095d7e;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
    <h1 class="hero-heading">Cost Estimation</h1>
    <p>Select the disease you have:</p>

    <div class="search-row">
        <input 
            type="text" 
            id="diseaseSearch" 
            placeholder="Search for diseases..." 
            oninput="filterDiseases()" 
            class="search-input-box">

        <button type="button" onclick="clearDiseases()" class="search-clear-btn">
            Clear List
        </button>
    </div>                

    <form id="symptomForm">
       <div class="diseases-list" id="diseasesContainer"></div>
        <button class="disease-button" type="button" onclick="submitDiseases()">Get Estimate</button>
    </form>

    <div class="results" id="results"></div>
</div>

<script>
    const symptomsFromQuery = "<?php echo $symptomParam; ?>".split(',');

    function filterDiseases() {
        const searchValue = document.getElementById('diseaseSearch').value.toLowerCase();
        const symptoms = document.querySelectorAll('.disease-item');
        symptoms.forEach(item => {
            const label = item.querySelector('label').innerText.toLowerCase();
            item.style.display = label.includes(searchValue) ? 'flex' : 'none';
        });
    }

    function clearDiseases() {
        const checkboxes = document.querySelectorAll('input[name="diseases"]');
        checkboxes.forEach(cb => cb.checked = false);
        localStorage.removeItem("selectedDiseases");
        document.querySelectorAll('.disease-item').forEach(item => item.style.display = 'flex');
    }

    function submitDiseases() {
        const selectedDiseases = Array.from(document.querySelectorAll('input[name="diseases"]:checked'))
                                      .map(cb => cb.value);

        if (selectedDiseases.length === 0) {
            Swal.fire({
                icon: 'info',
                title: 'Invalid Selection',
                text: 'Please select at least one disease.',
                confirmButtonText: 'OK'
            });
            return;
        }

        localStorage.setItem("selectedDiseases", JSON.stringify(selectedDiseases));
        const queryParams = new URLSearchParams({ diseases: selectedDiseases.join(',') });
        window.location.href = `cost-details.html?${queryParams.toString()}`;
    }

    document.addEventListener("DOMContentLoaded", function () {
        fetch("fetch_diseases.php")
            .then(response => response.text())
            .then(data => {
                document.getElementById("diseasesContainer").innerHTML = data;

                if (symptomsFromQuery.length > 0 && symptomsFromQuery[0] !== "") {
                    fetch("get_diseases_by_symptoms.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({ symptoms: symptomsFromQuery })
                    })
                    .then(response => response.json())
                    .then(diseases => {
                        diseases.forEach(disease => {
                            const checkbox = document.querySelector(`input[value="${disease}"]`);
                            if (checkbox) {
                                checkbox.checked = true;
                                checkbox.closest('.disease-item').style.display = 'flex';
                            }
                        });

                        localStorage.setItem("selectedDiseases", JSON.stringify(diseases));
                    })
                    .catch(err => console.error("Error fetching diseases:", err));
                }
            });
    });
</script>

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
</body>
</html>
