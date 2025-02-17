<?php
session_start();
include 'db_connection.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Validate the token from the database
    $sql = "SELECT id, reset_token_expiry FROM users WHERE reset_token = ? LIMIT 1";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            $stmt->bind_result($user_id, $expiry_time);
            $stmt->fetch();

            // Check if the token is expired
            if (strtotime($expiry_time) > time()) {
                // If the form is submitted
                if ($_SERVER["REQUEST_METHOD"] == "POST") {
                    $new_password = trim($_POST['password']);
                    $confirm_password = trim($_POST['confirm_password']);

                    // Check if passwords match
                    if ($new_password === $confirm_password) {
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                        // Update the user's password and clear the token
                        $update_sql = "UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?";
                        if ($update_stmt = $conn->prepare($update_sql)) {
                            $update_stmt->bind_param("si", $hashed_password, $user_id);
                            $update_stmt->execute();

                            $_SESSION['message'] = "Your password has been successfully reset.";
                            header("Location: login_form.php"); // Redirect to login
                            exit();
                        }
                    } else {
                        $_SESSION['error'] = "Passwords do not match.";
                    }
                }
            } else {
                $_SESSION['error'] = "This reset link has expired.";
            }
        } else {
            $_SESSION['error'] = "Invalid reset link.";
        }
        $stmt->close();
    }
} else {
    $_SESSION['error'] = "No reset token provided.";
}
?>

<!-- reset_password.html -->
<div class="container">
    <h1 class="hero-heading">Reset Password</h1>
    <?php if (isset($_SESSION['error'])): ?>
        <p style="color: red;"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
    <?php endif; ?>
    <form action="reset_password.php?token=<?php echo urlencode($_GET['token']); ?>" method="POST">
        <div class="form-group">
            <label for="password">New Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        <div class="form-group">
            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>
        <button type="submit" class="signup-btn">Reset Password</button>
    </form>
</div>
