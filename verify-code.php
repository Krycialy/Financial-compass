<?php
session_start();
include("conne.php");

if (!isset($_SESSION['reset_code']) || !isset($_SESSION['reset_email'])) {
    header("Location: forgot-password.php");
    exit();
}

if (isset($_POST['submit'])) {
    $entered_code = $_POST['code'];

    if ($entered_code == $_SESSION['reset_code']) {
        $_SESSION['code_verified'] = true;
        header("Location: reset-pass.php");
        exit();
    } else {
        echo "<script>
            alert('Invalid code!');
            window.location.href='verify-code.php';
            </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="verify-code.css?v=2">
    <link rel="icon" href="SAD.jpg" type="image/x-icon"/> 
    <title>Verify Code</title>
</head>
<body>
  <nav class="navbar">
        <div class="logo">
            <i class="fa fa-compass"></i>
            <span>FINANCIAL COMPASS</span>
        </div>
    </nav>
    <div class="container">
        <form action="verify-code.php" method="POST"> 
            <div class="login-box">
                <div class="headertext">
                    <p>Enter Verification Code</p>
                </div>
                <div class="input">
                    <input type="text" class="field" name="code" placeholder="Enter 6-digit code" required>
                </div>
                <div class="input">
                    <button type="submit" class="input-submit" name="submit">Verify Code</button>
                </div>
                <div class="forget-pass">
                    <a href="forgot-password.php">Resend Code</a> <!-- Ensure this points to the correct page -->
                </div>
            </div>
        </form>
    </div>
</body>
</html>
