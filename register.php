<?php
session_start();
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $gender = trim($_POST['gender']);
    $dob = trim($_POST['dob']);
    $location = trim($_POST['location']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm-password'];
    $insurance_hospitals = isset($_POST['insurance_hospitals']) ? $_POST['insurance_hospitals'] : [];

    if ($password !== $confirm_password) {
        echo '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Error</title>
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        </head>
        <body>
            <script>
                Swal.fire({
                    icon: "error",
                    title: "Oops!",
                    text: "Passwords do not match!"
                }).then(() => {
                    window.history.back();
                });
            </script>
        </body>
        </html>';
        exit;
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $check_email = "SELECT id FROM users WHERE email = ?";
    $stmt = $conn->prepare($check_email);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <title>Redirecting...</title>
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            </head>
            <body>
                <script>
                    Swal.fire({
                        icon: "warning",
                        title: "Email Already Registered",
                        text: "Please use a different email."
                    }).then(() => {
                        window.history.back();
                    });
                </script>
            </body>
            </html>';
        exit;
    }
    $stmt->close();

    $query = "INSERT INTO users (fullname, email, gender, dob, location, password) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssss", $fullname, $email, $gender, $dob, $location, $hashed_password);

    if ($stmt->execute()) {
        $user_id = $stmt->insert_id; 
        if (!empty($insurance_hospitals)) {
            $insurance_query = "INSERT INTO user_insurance_hospitals (user_id, hospital_id) VALUES (?, ?)";
            $insurance_stmt = $conn->prepare($insurance_query);

            foreach ($insurance_hospitals as $hospital_id) {
                $insurance_stmt->bind_param("ii", $user_id, $hospital_id);
                $insurance_stmt->execute();
            }
            $insurance_stmt->close();
        }

        echo '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <title>Redirecting...</title>
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            </head>
            <body>
                <script>
                    Swal.fire({
                            icon: "success",
                            title: "Registration Successful",
                            text: "You can now log in."
                        }).then(() => {
                            window.location.href = "login_form.php";
                        });
                </script>
            </body>
            </html>';
    } else {
        echo '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <title>Redirecting...</title>
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            </head>
            <body>
                <script>
                    Swal.fire({
                            icon: "error",
                            title: "Registration Failed",
                            text: "Something went wrong. Please try again."
                        }).then(() => {
                            window.history.back();
                        });
                </script>
            </body>
            </html>';
    }

    $stmt->close();
    $conn->close();
}
?>
