<?php
session_start();
include("conne.php");

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Query for Personal Users
$personal_query = "
    SELECT u.user_id, u.username, u.email, u.role, u.options, 
           SUM(t.budget) AS budget, 
           SUM(t.amount) AS amount 
    FROM user u 
    LEFT JOIN track t ON u.user_id = t.user_id 
    WHERE u.options = 'Personal Use'
    GROUP BY u.user_id, u.username, u.email, u.role, u.options";

$personal_result = mysqli_query($db, $personal_query);
if (!$personal_result) {
    die("Personal Query Failed: " . mysqli_error($db));
}

// Query for Business Users
$business_query = "
    SELECT u.user_id, u.username, u.email, u.role, u.options, 
           u.location, 
           b.quantity, b.price, b.cost, 
           b.fixed_cost, b.variable_costperunit
    FROM user u 
    LEFT JOIN business b ON u.user_id = b.user_id 
    WHERE u.options = 'Small Business'";

$business_result = mysqli_query($db, $business_query);
if (!$business_result) {
    die("Business Query Failed: " . mysqli_error($db));
}

// Handle role update
if (isset($_POST['update']) && isset($_POST['user_id']) && isset($_POST['role'])) {
    $role = mysqli_real_escape_string($db, $_POST['role']);
    $user_id = mysqli_real_escape_string($db, $_POST['user_id']);

    $update_query = "UPDATE user SET role=? WHERE user_id=?";
    $stmt = mysqli_prepare($db, $update_query);
    mysqli_stmt_bind_param($stmt, "si", $role, $user_id);
    mysqli_stmt_execute($stmt);

    if (mysqli_stmt_affected_rows($stmt) > 0) {
        echo "Role updated successfully.";
    } else {
        echo "Error updating role.";
    }

    mysqli_stmt_close($stmt);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="admin.css?v=3">
    <link rel="icon" href="SAD.jpg" type="image/x-icon"/> 
    <title>Financial Compass: User Management</title>
</head>
<body>
    <div class="navbar">
        <h1>Admin Dashboard</h1>
        <nav>
            <a href="admin.php">User Management</a>
            <a href="Personal_Dashboard.php">Personal Dashboard</a>
            <a href="SmallBusiness.php">Small Business Dashboard</a>
            <a href="login.php" onclick="return confirmLogout()">Logout</a>
        </nav>
    </div>
    
    <div class="content">
        <!-- Personal Users Section -->
        <h2>Personal Users</h2>
        <table>
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Options</th>
                    <th>Budget</th>
                    <th>Expenditure</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($personal_result)) { ?>
                    <tr>
                        <td><?php echo $row['user_id']; ?></td>
                        <td><?php echo $row['username']; ?></td>
                        <td><?php echo $row['email']; ?></td>
                        <td>
                            <select class="field" id="role_personal_<?php echo $row['user_id']; ?>">
                                <option value="user" <?php echo $row['role'] == 'user' ? 'selected' : ''; ?>>User</option>
                                <option value="admin" <?php echo $row['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                            </select>
                        </td>
                        <td><?php echo $row['options']; ?></td>
                        <td><?php echo isset($row['budget']) ? number_format($row['budget'], 2) : 'N/A'; ?></td>
                        <td><?php echo isset($row['amount']) ? number_format($row['amount'], 2) : 'N/A'; ?></td>
                        <td>
                            <button onclick="updateRole(<?php echo $row['user_id']; ?>, 'personal')">Update</button>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <h2>Business Users Management</h2>
        <table>
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Options</th>
                    <th>Location</th>
                    <th>Revenue</th>
                    <th>Profit</th>
                    <th>Variable Cost</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($business_result)) { 
                    $quantity = $row['quantity'];
                    $price = $row['price'];
                    $cost = $row['cost'];
                    $fixed_cost = isset($row['fixed_cost']) ? $row['fixed_cost'] : 0;
                    $variable_costperunit = isset($row['variable_costperunit']) ? $row['variable_costperunit'] : 0;

                    // Correct calculation of variable cost
                    $variable_cost = ($variable_costperunit > 0 && $quantity > 0) 
                        ? $quantity * $variable_costperunit 
                        : 0;

                    // Calculate revenue and profit
                    $revenue = $quantity * $price;
                    $profit = $revenue - $variable_cost - $fixed_cost;
                ?>
                <tr>
                    <td><?php echo $row['user_id']; ?></td>
                    <td><?php echo $row['username']; ?></td>
                    <td><?php echo $row['email']; ?></td>
                    <td>
                        <select id="role_business_<?php echo $row['user_id']; ?>">
                            <option value="user" <?php echo $row['role'] == 'user' ? 'selected' : ''; ?>>User</option>
                            <option value="admin" <?php echo $row['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                        </select>
                    </td>
                    <td><?php echo $row['options']; ?></td>
                    <td><?php echo $row['location']; ?></td>
                    <td><?php echo number_format($revenue, 2); ?></td>
                    <td><?php echo number_format($profit, 2); ?></td>
                    <td><?php echo number_format($variable_cost, 2); ?></td>
                    <td>
                        <button onclick="updateRole(<?php echo $row['user_id']; ?>, 'business')">Update</button>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <script>
    function confirmLogout() {
        return confirm("Are you sure you want to log out?");
    }

    function updateRole(userId, userType) {
        const role = document.getElementById(`role_${userType}_${userId}`).value;

        const xhr = new XMLHttpRequest();
        xhr.open("POST", "", true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4 && xhr.status == 200) {
                alert(xhr.responseText);
            }
        };

        xhr.send(`update=1&user_id=${userId}&role=${role}`);
    }
    </script>
</body>
</html>