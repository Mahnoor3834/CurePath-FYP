<?php
    session_start();
    $userId = $_SESSION['user_id'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="logo.png">
    <title>Insurance Compatibility</title>
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
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }

        h2 {
            text-align: center;
            font-size: 1.8rem;
            margin-bottom: 20px;
        }

        .hero-heading {
            color: #095d7e; 
            font-weight: bold;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 30px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.4);
        }

        /* Filter Section */
        .filter-section {
            background-color: #E0F6FF;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .filter-section h3 {
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: #14967F;
        }

        .filter-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }

        .filter-form label {
            font-size: 1rem;
            font-weight: bold;
        }

        .filter-form select,
        .filter-form input {
            width: 100%;
            padding: 10px;
            font-size: 1rem;
            border: 1px solid #14967F;
            border-radius: 10px;
            background-color: #F8F9FA;
        }

        .filter-form button {
            margin-top: 25px;
            padding: 10px 20px;
            font-size: 1rem;
            background-color: #095d7e;
            color: #FFF;
            border: none;
            border-radius: 10px;
            cursor: pointer;
        }

        .filter-form button:hover {
            background-color: #14967F;
        }

        /* Cost Estimation Section 
        .estimation-section {
            background-color: #F0FFF4;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .estimation-section h3 {
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: #095d7e;
        }

        .estimation-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .estimation-form textarea {
            width: 100%;
            height: 100px;
            padding: 10px;
            font-size: 1rem;
            border: 1px solid #14967F;
            border-radius: 10px;
            background-color: #F8F9FA;
        }

        .estimation-form button {
            align-self: flex-start;
            padding: 10px 20px;
            font-size: 1rem;
            background-color: #095d7e;
            color: #FFF;
            border: none;
            border-radius: 10px;
            cursor: pointer;
        }

        .estimation-form button:hover {
            background-color: #14967F;
        }*/

        /*Results Section*/
        .results-section {
            margin-top: 20px;
        }

        .results-section h3 {
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: #095d7e;
        }

        .results-section .doctor-card {
            background-color: #F9F9F9;
            padding: 15px;
            border-left: 4px solid #14967F;
            margin-bottom: 15px;
            border-radius: 10px;
        }

        .doctor-card strong {
            color: #095d7e;
        }
        .detail-btn {
            align-self: flex-start;
            margin-top: 20px;
            margin-right: 40px;
            background-color: #095d7e;
            color: white;
            font-size: 1rem;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 200px;
        }

        .detail-btn:hover {
            background-color: #14967F;
        }

        .doctor-card .info { flex: 1; }
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap; /* Optional: for responsiveness */
            gap: 6px;
            margin-top: 20px;
        }

        .pagination button {
            padding: 6px 10px;
            min-width: 32px;
            width: 32px;
            background-color: #095d7e;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .pagination button:hover:not(.disabled) {
            background-color: #0c7ba2;
        }

        .pagination button.disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }

        .profile-pic {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin-right: 15px;
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
        <h2 class="hero-heading">Insurance Compatibility</h2>

        <!-- Filter Section -->
        <div class="filter-section">
            <h3>Hospital For Insurance</h3>
            <form class="filter-form" id="insuranceForm">
                <div>
                    <label for="insurance">Insurance Compatibility:</label>
                    <select id="insurance">
                        <option value="">Loading...</option>
                    </select>
                </div>
                <div>
                    <button type="button" onclick="searchDoctors()">Search</button>
                </div>
                <div>
                    <label for="doctorSearch">Search Doctor by Name:</label>
                    <input type="text" id="doctorSearch" placeholder="Enter doctor name...">
                </div>
            </form>            
        </div>

        <!-- Cost Estimation Section 
        <div class="estimation-section">
            <h3>Estimate Treatment Cost</h3>
            <form class="estimation-form">
                <textarea placeholder="Describe your symptoms or disease"></textarea>
                <button type="button">Get Estimate</button>
            </form>
        </div>-->

        <!--Results Section-->
        <div class="results-section" id="resultsSection">
            <div id="doctorResults">
                <!-- Filtered doctors will appear here -->
            </div>
        </div> 
        <div class="pagination" id="pagination"></div>
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
            const userId = <?php echo json_encode($userId); ?>;
            document.addEventListener("DOMContentLoaded", function () {
                const params = new URLSearchParams(window.location.search);
                const doctorId = params.get("doctor_id");
                const hospitalId = params.get("hospital_id");
                const selectedSpecialization = params.get("specialization"); // specialization will not be used anymore

                fetch("fetch_insurance.php")
                .then(response => response.json())
                .then(data => {
                    const insuranceDropdown = document.getElementById("insurance");
                    insuranceDropdown.innerHTML = '<option value="">Select Hospital</option>';
                    data.forEach(hospital => {
                        insuranceDropdown.innerHTML += `<option value="${hospital.hospital_id}">${hospital.name}</option>`;
                    });
                })
                .catch(error => console.error("Error fetching insurance providers:", error));
            });

            let allDoctors = [];
            let currentPage = 1;
            const resultsPerPage = 15;

            function searchDoctors() {
                const insuranceId = document.getElementById("insurance").value;

                if (!insuranceId) {
                        Swal.fire({
                        icon: 'warning',
                        title: 'No Hospital Selected',
                        text: 'Please select a hospital.'
                        });
                    return;
                }

                fetch("fetch_ins_doctors.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ hospital_id: insuranceId }),
                })
                    .then(response => response.json())
                    .then(data => {
                        const recommendedSpecialityIds = JSON.parse(localStorage.getItem("recommendedSpecialityIds") || "[]");
                    allDoctors = recommendedSpecialityIds.length > 0
                        ? data.filter(doc => recommendedSpecialityIds.includes(doc.speciality_id))
                        : data;
                                currentPage = 1;
                                displayDoctorsPage(currentPage);
                            })
                            .catch(error => {
                                console.error("Error fetching doctors:", error);
                            });
            }

            function displayDoctorsPage(page) {
                const resultsDiv = document.getElementById("doctorResults");
                const paginationDiv = document.getElementById("pagination");

                resultsDiv.innerHTML = "";
                paginationDiv.innerHTML = "";

                if (allDoctors.length === 0) {
                    resultsDiv.innerHTML = "<p>No doctors found for the selected hospital.</p>";
                    return;
                }

                const start = (page - 1) * resultsPerPage;
                const end = start + resultsPerPage;
                const doctorsToShow = allDoctors.slice(start, end);

                doctorsToShow.forEach(doctor => {
                    const availabilityText = doctor.availability.length > 0
                        ? doctor.availability.join(", ")
                        : "Not available";

                    resultsDiv.innerHTML += `
                        <div class="doctor-card">
                            <strong>Doctor:</strong> ${doctor.name}<br>
                            <strong>Speciality:</strong> ${doctor.speciality}<br>
                            <button class="detail-btn" onclick="viewDoctorDetails('${doctor.doctor_id}')">Check Details</button>
                        </div>
                    `;
                });                

                // Render pagination
                const totalPages = Math.ceil(allDoctors.length / resultsPerPage);
                for (let i = 1; i <= totalPages; i++) {
                    const btn = document.createElement("button");
                    btn.innerText = i;
                    btn.className = (i === page) ? "active" : "";
                    btn.onclick = () => {
                        currentPage = i;
                        displayDoctorsPage(currentPage);
                    };
                    paginationDiv.appendChild(btn);
                }
            }

            document.getElementById("doctorSearch").addEventListener("input", function () {
                const query = this.value.trim().toLowerCase();
                const filteredDoctors = allDoctors.filter(doc =>
                    doc.name.toLowerCase().includes(query)
                );
                displayFilteredDoctors(filteredDoctors);
            });

            function displayFilteredDoctors(filteredList) {
                const resultsDiv = document.getElementById("doctorResults");
                const paginationDiv = document.getElementById("pagination");

                resultsDiv.innerHTML = "";
                paginationDiv.innerHTML = "";

                if (filteredList.length === 0) {
                    resultsDiv.innerHTML = "<p>No matching doctors found.</p>";
                    return;
                }

                filteredList.forEach(doctor => {
                    const availabilityText = doctor.availability.length > 0
                        ? doctor.availability.join(", ")
                        : "Not available";

                    resultsDiv.innerHTML += `
                        <div class="doctor-card">
                            <strong>Doctor:</strong> ${doctor.name}<br>
                            <strong>Speciality:</strong> ${doctor.speciality}<br>
                            <button class="detail-btn" onclick="viewDoctorDetails('${doctor.doctor_id}')">Check Details</button>
                        </div>
                    `;
                });
            }

            function viewDoctorDetails(doctorId) {
                window.location.href = `doctor-details.html?doctor_id=${doctorId}`;
            }

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
