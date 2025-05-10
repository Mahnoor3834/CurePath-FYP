<?php
session_start();
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $gender = trim($_POST['gender']);
    $dob = trim($_POST['dob']);
    $location = trim($_POST['location']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm-password'];
    
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
    
    $query = "INSERT INTO users (fullname, email, phone, gender, dob, location, password) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssssss", $fullname, $email, $phone, $gender, $dob, $location, $hashed_password);
    
    if ($stmt->execute()) {
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
