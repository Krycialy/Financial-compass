<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Composer autoload for PHPMailer
include("conne.php");
session_start();

function sendResetCode($email, $code) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; // Your SMTP server
        $mail->SMTPAuth   = true;
        $mail->Username   = 'kenryanloyola9@gmail.com'; // Your email
        $mail->Password   = 'daio iyii wtum vgab'; // App password, not regular password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('your_email@gmail.com', 'Financial Compass');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Code';
        $mail->Body    = "
            <html>
            <body>
                <h2>Password Reset Code</h2>
                <p>Your reset code is: <strong>$code</strong></p>
                <p>This code will expire in 10 minutes.</p>
                <p>If you did not request a password reset, please ignore this email.</p>
            </body>
            </html>
        ";
        $mail->AltBody = "Your password reset code is: $code";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

if (isset($_POST['submit'])) {
    $email = mysqli_real_escape_string($db, $_POST['email']);
    $query = mysqli_query($db, "SELECT * FROM user WHERE email = '$email'");
    
    if (mysqli_num_rows($query) > 0) {
        $code = rand(100000, 999999);
        $_SESSION['reset_code'] = $code;
        $_SESSION['reset_email'] = $email;
        $_SESSION['code_timestamp'] = time(); // Add timestamp for code expiration

        // Send email with reset code
        if (sendResetCode($email, $code)) {
            echo "<script>
                alert('Reset code sent to your email!');
                window.location.href='verify-code.php';
                </script>";
        } else {
            echo "<script>
                alert('Failed to send reset code. Please try again.');
                window.location.href='forgot-password.php';
                </script>";
        }
    } else {
        echo "<script>
            alert('Email not found!');
            window.location.href='forgot-password.php';
            </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="forgot-pass.css?v=2">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="icon" href="SAD.jpg" type="image/x-icon"/>
    <title>Financial Compass: Forgot Password</title>
</head>
<body>
    <nav class="navbar">
        <div class="logo">
            <i class="fa fa-compass"></i>
            <span>FINANCIAL COMPASS</span>
        </div>
    </nav>
    
    <div class="container">
        <div class="forgot-password-box">
            <h2>FORGOT PASSWORD</h2>
            <form action="forgot-password.php" method="POST">
                <div class="input-field">
                    <input type="email" class="field" name="email" placeholder="ENTER YOUR EMAIL" required>
                </div>
                <div class="links">
                    <a href="login.php">Back to Login</a>
                </div>
                <button type="submit" class="submit-btn" name="submit">SEND CODE</button>
            </form>
        </div>
    </div>
</body>
</html>