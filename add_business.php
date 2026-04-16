<?php
session_start();
include("conne.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_POST['submit'])) {
    $user_id = $_SESSION['user_id']; 
    $date = $_POST["date"];
    $product = isset($_POST["product"]) ? $_POST["product"] : ""; 
    $price = $_POST["Price"];
    $cost = $_POST["cost"];
    $quantity = $_POST["Quantity"];
    $fixed_cost = $_POST["fixed_cost"];
    $variable_costperunit = $_POST["variable_costperunit"];

    // Check if record already exists
    $check_query = "SELECT * FROM business WHERE user_id = $user_id AND date = '$date' AND product_name = '$product'";
    $check_result = mysqli_query($db, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        echo "<script>
            alert('A record with the same date and product already exists.');
            window.location.href='add_business.php';  
        </script>";
    } else {
        // Insert record into database including cost
        $insert_query = "INSERT INTO business(date, product_name, price, user_id, quantity, cost, fixed_cost, variable_costperunit) 
                         VALUES ('$date', '$product', '$price', '$user_id', '$quantity', '$cost', '$fixed_cost', '$variable_costperunit')";
        mysqli_query($db, $insert_query) or die(mysqli_error($db));
        echo "<script>
            alert('Inserted Successfully');
            window.location.href='tracker_business.php'; 
        </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="add_business.css">
    <link rel="icon" href="SAD.jpg" type="image/x-icon"/> 
    <title>Financial Compass: Navigating your Budgeting Journey with an Interactive Website</title>
</head>
<body>
    <div class="container">
        <form method="POST">
            <div class="login-box">
                <div class="headertext">
                    <p>Add to Tracker</p>
                </div>
                <div class="input">
                    <input type="date" class="field" name="date" required>
                </div>
                <div class="input">
                    <input type="number" class="field" name="Quantity" placeholder="Quantity" required min="1">
                </div>
                <div class="input">
                    <input type="text" class="field" name="product" placeholder="Product Name" required>
                </div>
                <div class="input">
                    <input type="number" class="field" name="Price" placeholder="Price" required min="0">
                </div>
                <div class="input">
                    <input type="number" class="field" name="cost" placeholder="Cost" required min="0">
                </div>
                <div class="input">
                    <input type="number" class="field" name="fixed_cost" placeholder="Fixed Cost" required min="0">
                </div>
                <div class="input">
                     <input type="number" class="field" name="variable_costperunit" placeholder="Variable Cost Per Unit" required min="0">
                </div>
                <div class="input">
                    <button type="submit" name="submit" class="input-submit">Add</button>
                    <a href="tracker_business.php" class="input-submit" style="background-color: red; color: white; text-decoration: none; display: inline-block; padding: 10px; text-align: center;">Cancel</a>
                </div>
            </div>
        </form>
    </div>


    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get all number input fields
        var numberInputs = [
            'Quantity', 
            'Price', 
            'cost', 
            'fixed_cost', 
            'variable_costperunit'
        ];

        numberInputs.forEach(function(inputName) {
            var input = document.querySelector('input[name="' + inputName + '"]');
            
            // Prevent non-numeric characters
            input.addEventListener('input', function() {
                // Remove any non-numeric characters except decimal point
                this.value = this.value.replace(/[^0-9.]/g, '');
                
                // Ensure only one decimal point
                var parts = this.value.split('.');
                if (parts.length > 2) {
                    this.value = parts[0] + '.' + parts.slice(1).join('');
                }
            });

            // Prevent paste of non-numeric content
            input.addEventListener('paste', function(e) {
                e.preventDefault();
                var pastedText = e.clipboardData.getData('text/plain');
                var numericText = pastedText.replace(/[^0-9.]/g, '');
                this.value = numericText;
            });
        });
    });
    </script>
</body>
</html>