<?php 
session_start(); 
include("conne.php");  

if (isset($_POST["submit"])) {     
    $email = mysqli_real_escape_string($db, $_POST['email']);     
    $username = mysqli_real_escape_string($db, $_POST['username']);     
    $password = mysqli_real_escape_string($db, $_POST['password']);
    $options = mysqli_real_escape_string($db, $_POST['options']);

    $check_email = mysqli_query($db, "SELECT * FROM user WHERE email = '$email'");     
    if (mysqli_num_rows($check_email) > 0) {         
        echo "<script>alert('Email is already registered.'); window.location.href='signup.php';</script>";     
    } else {         
        $location = isset($_POST['location']) ? mysqli_real_escape_string($db, $_POST['location']) : null;
        $query = "INSERT INTO user (email, username, password, role, options, location) VALUES ('$email', '$username', '$password', 'user', '$options', '$location')";         
        if (mysqli_query($db, $query)) {             
            echo "<script>alert('Registration successful.');</script>";
            // Redirect based on the chosen option
            if ($options == 'Personal Use') {
                echo "<script>window.location.href='login.php';</script>";
            } else if ($options == 'Small Business') {
                echo "<script>window.location.href='login.php';</script>";
            }
        } else {             
            echo "<script>alert('Registration failed.'); window.location.href='signup.php';</script>";         
        }     
    } 
} 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="signup.css?v=8">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="icon" href="SAD.jpg" type="image/x-icon"/>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
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
        <div class="signup-box">
            <h2>SIGN UP</h2>
            <form method="POST">
                <div class="input-field">
                    <input type="text" class="field" name="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" placeholder="USERNAME" required>
                </div>
                <div class="input-field">
                    <input type="email" class="field" name="email" placeholder="EMAIL" required>
                </div>
                <div class="input-field">
                    <input type="password" class="field" id="password" name="password" placeholder="PASSWORD" required>
                    <span class="show-password" onclick="togglePassword('password', this)">
                        <i class="fa fa-eye"></i>
                    </span>
                </div>
                <div class="input-field">
                    <input type="password" class="field" id="confirm_password" placeholder="CONFIRM PASSWORD" required>
                    <span class="show-password" onclick="togglePassword('confirm_password', this)">
                        <i class="fa fa-eye"></i>
                    </span>
                </div>
                <p id="password_warning">Passwords do not match.</p>
                <div class="input-field">
                <select class="field" id="options_dropdown" name="options" required>
                    <option value="" disabled selected>Select an Option</option>
                    <?php
                    $xml = simplexml_load_file('options.xml');
                    foreach ($xml->options as $option) {
                        echo '<option value="' . htmlspecialchars($option) . '">' . htmlspecialchars($option) . '</option>';
                    }
                    ?>
                </select>
            </div>
                <div class="input-field" id="location_field" style="display: none;">
                    <input type="text" class="field" name="location" id="location_input" placeholder="LOCATION">
                    <span class="map-icon" onclick="openMapModal()">
                        <i class="fa fa-map"></i>
                    </span>
                </div>
                <div id="map_modal" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 80%; height: 60%; z-index: 1000; background: white; border: 1px solid #ccc;">
                    <div id="map" style="width: 100%; height: 100%;"></div>
                    <button onclick="closeMapModal(event)" style="position: absolute; top: 10px; right: 10px; z-index: 1001; background-color: #f0f0f0; border: 1px solid #ccc; padding: 5px 10px; border-radius: 4px; cursor: pointer;">Close</button>
                </div>


                <div class="links">
                    <a href="login.php">Already have an account?</a>
                </div>
                <button type="submit" name="submit" class="signup-btn">SIGN UP</button>
            </form>
        </div>
        <div class="title-description">
            <h1>Navigating Finance Made Easy</h1>
            <p>From budgeting basics to investment strategies, we’ve got you covered.</p>
        </div>
    </div>

    <script>
        function togglePassword(fieldId, iconContainer) {
            const field = document.getElementById(fieldId);
            const icon = iconContainer.querySelector("i");
            field.type = field.type === "password" ? "text" : "password";
            icon.className = field.type === "password" ? "fa fa-eye" : "fa fa-eye-slash";
        }

        const passwordField = document.getElementById("password");
        const confirmPasswordField = document.getElementById("confirm_password");
        const warning = document.getElementById("password_warning");

        confirmPasswordField.addEventListener("input", () => {
            warning.style.display = passwordField.value !== confirmPasswordField.value ? "block" : "none";
        });
        const optionsDropdown = document.getElementById("options_dropdown");
        const locationField = document.getElementById("location_field");

        optionsDropdown.addEventListener("change", () => {
            locationField.style.display = optionsDropdown.value === "Small Business" ? "block" : "none";
        });

        const locationInput = document.getElementById("location_input");
        const mapModal = document.getElementById("map_modal");
        let map, marker;

        function openMapModal() {
            mapModal.style.display = "block";
            if (!map) {
                map = L.map('map').setView([14.5995, 120.9842], 13); // Default to Manila

                // Add OpenStreetMap tiles
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                }).addTo(map);

                // Add marker on click
                map.on('click', function(e) {
                    if (marker) {
                        map.removeLayer(marker);
                    }
                    marker = L.marker(e.latlng).addTo(map);
                    locationInput.value = `${e.latlng.lat}, ${e.latlng.lng}`; // Set coordinates to the input field
                });
            }
        }

        function closeMapModal(event) {
    event.preventDefault();  // Prevent form submission or reload
    mapModal.style.display = "none";
}
    </script>
</body>
</html>
