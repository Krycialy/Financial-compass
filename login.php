<?php
session_start();
include("conne.php");

if (isset($_POST["submit"])) {
    $email = mysqli_real_escape_string($db, $_POST['email']);
    $password = mysqli_real_escape_string($db, $_POST['password']); 

    $myquery = mysqli_query($db, "SELECT * FROM user WHERE email = '$email'");
    $row = mysqli_fetch_assoc($myquery);

    // Password comparison
    if ($row && $password === $row['password']) {  
        $_SESSION['user_id'] = $row['user_id'];
        $_SESSION['username'] = $row['username'];
        $_SESSION['role'] = $row['role'];
        $_SESSION['options'] = $row['options'];


        if ($row['role'] === 'admin') {
            header("Location: admin.php");
            exit();
        } else {
            if ($row['options'] === 'Small Business') {
                header("Location: dashboard_business_user.php");
            } else {
                header("Location: dashboard.php");
            }
            exit();
        }
    } else {
        echo "<script>
            alert('Wrong Email or Password!');
            window.location.href='login.php';
            </script>";
    } 
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="login.css?v=10">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
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
        <div class="title-description"><h1>Chart Your Financial Future</h1>
        <p>Discover the tools you need to master budgeting and achieve your goals. Start building your financial confidence now!</p>

        </div>
        <div class="login-box">
            <h2>LOGIN</h2>
            <form action="login.php" method="POST">
                <div class="input-field">
                    <input type="text" class="field" name="email" placeholder="EMAIL" required>
                </div>
                <div class="input-field">
                    <input type="password" class="field" id="password" name="password" placeholder="PASSWORD" required>
                    <span class="show-password" onclick="togglePassword('password', this)">
                        <i class="fa fa-eye"></i>
                    </span>
                </div>
                <div class="links">
                    <a href="forgot-password.php">Forgot Password?</a>
                    <a href="signup.php" class="register">Register here</a>
                </div>
                <button type="submit" class="login-btn" name="submit">LOGIN</button>
            </form>
        </div>
        
    </div>

    <script>
        function togglePassword(fieldId, iconContainer) {
            const field = document.getElementById(fieldId);
            const icon = iconContainer.querySelector("i");
            field.type = field.type === "password" ? "text" : "password";
            icon.className = field.type === "password" ? "fa fa-eye" : "fa fa-eye-slash";
        }
    </script>
</body>
</html>
