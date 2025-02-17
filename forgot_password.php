<?php
session_start();
include 'db_connection.php';
require 'vendor/autoload.php'; // Ensure PHPMailer is installed via Composer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    if (!empty($email)) {
        $sql = "SELECT id FROM users WHERE email = ? LIMIT 1";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows == 1) {
                $stmt->bind_result($user_id);
                $stmt->fetch();

                $token = bin2hex(random_bytes(50));
                $expiry_time = date('Y-m-d H:i:s', strtotime('+1 hour')); 

                $update_token_sql = "UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?";
                if ($update_stmt = $conn->prepare($update_token_sql)) {
                    $update_stmt->bind_param("ssi", $token, $expiry_time, $user_id);
                    $update_stmt->execute();

                    $reset_link = "http://localhost:3000/reset_password.php?token=" . urlencode($token);
                    $mail = new PHPMailer(true);
                    try {
                        $mail->isSMTP();
                        $mail->Host = 'smtp.gmail.com';
                        $mail->SMTPAuth = true;
                        $mail->Username = 'curepath.acc@gmail.com';
                        $mail->Password = 'euge pljp uecc lfnb';
                        $mail->SMTPSecure = 'tls';
                        $mail->Port = 587;

                        $mail->setFrom('curepath.acc@gmail.com', 'Curepath no-reply');
                        $mail->addAddress($email);
                        $mail->Subject = 'Password Reset Request';
                        $mail->Body = "Click the link below to reset your password:\n\n$reset_link\n\nThis link will expire in 1 hour.";

                        $mail->send();
                        $_SESSION['message'] = "A password reset link has been sent to your email.";
                        header("Location: login_form.php");
                        exit();
                    } catch (Exception $e) {
                        $_SESSION['error'] = "Email could not be sent. Mailer Error: " . $mail->ErrorInfo;
                    }
                }
            } else {
                $_SESSION['error'] = "No account found with that email address.";
            }
            $stmt->close();
        }
    } else {
        $_SESSION['error'] = "Please enter a valid email address.";
    }
    $conn->close();
    header("Location: forgot_password_form.php");
    exit();
}
?>