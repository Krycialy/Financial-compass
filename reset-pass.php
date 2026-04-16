<?php
session_start();
include("conne.php");

if (isset($_POST['submit'])) {
    $email = $_SESSION['reset_email'];
    $new_password = mysqli_real_escape_string($db, $_POST['new_password']);
    $confirm_password = mysqli_real_escape_string($db, $_POST['confirm_password']);

    if ($new_password === $confirm_password) {
        
        $update_query = mysqli_query($db, "UPDATE user SET password = '$new_password' WHERE email = '$email'");

        if ($update_query) {
            unset($_SESSION['reset_email']);
            unset($_SESSION['code_verified']);

            echo "<script>
                alert('Password updated successfully!');
                window.location.href='login.php';
                </script>";
        } else {
            echo "<script>alert('Error updating password: " . mysqli_error($db) . "');</script>";
        }
    } else {
        echo "<script>alert('Passwords do not match!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="reset-pass.css?v=2">
    <link rel="icon" href="SAD.jpg" type="image/x-icon"/> 
    <title>Financial Compass: Navigating your Budgeting Journey with an Interactive Website</title>
</head>
<body>
<nav class="navbar">
        <div class="logo">
            <i class="fa fa-compass"></i>
            <span>FINANCIAL COMPASS</span>
        </div>
    </nav>
    <div class="container">
        <form action="reset-pass.php" method="POST">
            <div class="login-box">
                <div class="headertext">
                    <p>Reset Password</p>
                </div>
                <div class="input">
                    <input type="password" class="field" name="new_password" placeholder="New Password" required>
                </div>
                <div class="input">
                    <input type="password" class="field" name="confirm_password" placeholder="Confirm Password" required>
                </div>
                <div class="input">
                    <button type="submit" class="input-submit" name="submit">Reset Password</button>
                </div>
            </div>
        </form>
    </div>
</body>
</html>
