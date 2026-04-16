<?php 
session_start();
include("conne.php");

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Ensure database connection
if (!$db) {
    die("Database connection failed: " . mysqli_connect_error());
}

$user_id = $_SESSION['user_id'];

// Handle AJAX update request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_business'])) {
    try {
        $product_name = mysqli_real_escape_string($db, $_POST['product_name']);
        $original_product_name = mysqli_real_escape_string($db, $_POST['original_product_name']);
        $date = mysqli_real_escape_string($db, $_POST['date']);
        $quantity = mysqli_real_escape_string($db, $_POST['quantity']);
        $price = mysqli_real_escape_string($db, $_POST['price']);
        $cost = mysqli_real_escape_string($db, $_POST['cost']);
        $fixed_cost = mysqli_real_escape_string($db, $_POST['fixed_cost']);
        $variable_cost = mysqli_real_escape_string($db, $_POST['variable_costperunit']);

        $update_sql = "UPDATE business 
              SET product_name = '$product_name',
                  date = '$date',
                  quantity = '$quantity',
                  price = '$price',
                  cost = '$cost',
                  fixed_cost = '$fixed_cost',
                  variable_costperunit = '$variable_cost'
              WHERE user_id = $user_id 
              AND product_name = '$original_product_name'";

        if (mysqli_query($db, $update_sql)) {
            echo json_encode(['status' => 'success', 'message' => 'Business details updated successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update business details']);
        }
        exit;
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit;
    }
}

// Handle AJAX fetch request
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['fetch_details'])) {
    $product_name = mysqli_real_escape_string($db, $_GET['product_name']);
    $sql = "SELECT * FROM business WHERE user_id = $user_id AND product_name = '$product_name'";
    $result = mysqli_query($db, $sql);
    
    if ($row = mysqli_fetch_assoc($result)) {
        echo json_encode($row);
    } else {
        echo json_encode(['error' => 'Product not found']);
    }
    exit;
}

// Fetch username
$sql_username = "SELECT username FROM user WHERE user_id = $user_id";
$result_username = mysqli_query($db, $sql_username);
$username = (mysqli_num_rows($result_username) > 0) 
    ? mysqli_fetch_assoc($result_username)['username'] 
    : "Unknown User";

// Fetch business tracker data
$sql_business = "SELECT date, product_name, price, quantity, cost, fixed_cost, variable_costperunit
                 FROM business 
                 WHERE user_id = $user_id 
                 ORDER BY date DESC";
$result_business = mysqli_query($db, $sql_business);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="track_business.css?v=4">
    <link rel="stylesheet" href="modal.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="icon" href="SAD.jpg" type="image/x-icon"/> 
    <title>Business Tracker | Financial Compass</title>
</head>
<body>
    <div class="wrapper">
    <button class="toggle-sidebar">
        <i class="fa fa-bars"></i>
        </button>
        <div class="sidebar">
            <h2>Menu</h2>
            <ul>
                <li><a href="dashboard_business_user.php"><i class="fa fa-bar-chart"></i> Dashboard</a></li>
                <li><a href="tracker_business.php"><i class="fa fa-table"></i> Tracker</a></li>
                <li><a href="add_business.php"><i class="fa fa-plus"></i> Add</a></li>
                <li><a href="settings.php"><i class="fa fa-address-card"></i> Profile</a></li>
                <li><a href="login.php" onclick="return confirm('Are you sure you want to logout?')"><i class="fa fa-sign-out"></i> Logout</a></li>
            </ul>
        </div>
        <div class="main">
            <div class="header">
                Business Tracker
                <button onclick="printBusinessTracker()" class="print-btn">Print</button>
            </div>
            <div class="info">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Quantity</th>
                            <th>Product</th> 
                            <th>Price</th>
                            <th>Cost</th>
                            <th>Fixed Cost</th>
                            <th>Variable Cost Per Unit</th>
                            <th>Edit</th>
                            <th>Delete</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if (mysqli_num_rows($result_business) > 0) {
                            while ($st = mysqli_fetch_assoc($result_business)) {
                                echo "<tr>";
                                echo "<td>".htmlspecialchars($st["date"])."</td>";
                                echo "<td>".htmlspecialchars($st["quantity"])."</td>";
                                echo "<td>".htmlspecialchars($st["product_name"])."</td>";
                                echo "<td>".htmlspecialchars($st["price"])."</td>";
                                echo "<td>".htmlspecialchars($st["cost"])."</td>";
                                echo "<td>".htmlspecialchars($st["fixed_cost"])."</td>";
                                echo "<td>".htmlspecialchars($st["variable_costperunit"])."</td>";
                                echo "<td><button type='button' class='edit-btn' onclick='openEditModal(\"".htmlspecialchars($st["product_name"])."\")'>Edit</button></td>";
                                echo "<td><a href='del.php?product_name=".urlencode($st["product_name"])."&date=".urlencode($st["date"])."' onclick='return confirm(\"Are you sure you want to delete this item?\")'><button type='button' class='button remove-btn'>
                                        <svg class='w-6 h-6 text-gray-800 dark:text-white' aria-hidden='true' xmlns='http://www.w3.org/2000/svg' width='24' height='24' fill='currentColor' viewBox='0 0 24 24'>
                                            <path fill-rule='evenodd' d='M2 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10S2 17.523 2 12Zm5.757-1a1 1 0 1 0 0 2h8.486a1 1 0 1 0 0-2H7.757Z' clip-rule='evenodd'/>
                                        </svg>
                                    </button></a></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6' class='text-center'>No Business Data Found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<!-- Modal for Edit -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Edit Small Business Tracker</h2>
        <form id="editForm">
            <input type="hidden" id="original_product_name" name="original_product_name">
            
            <div class="form-group">
                <label for="edit_date">Date:</label>
                <input type="date" id="edit_date" name="date" required>
            </div>
            
            <div class="form-group">
                <label for="edit_quantity">Quantity:</label>
                <input type="number" id="edit_quantity" name="quantity" required min="1">
            </div>
            
            <div class="form-group">
                <label for="edit_product_name">Product Name:</label>
                <input type="text" id="edit_product_name" name="product_name" required>
            </div>
            
            <div class="form-group">
                <label for="edit_price">Price per Product:</label>
                <input type="number" step="0.01" id="edit_price" name="price" required min="0">
            </div>
            
            <div class="form-group">
                <label for="edit_cost">Cost per Product:</label>
                <input type="number" step="0.01" id="edit_cost" name="cost" required min="0">
            </div>
            
            <div class="form-group">
                <label for="edit_fixed_cost">Fixed Cost:</label>
                <input type="number" step="0.01" id="edit_fixed_cost" name="fixed_cost" required min="0">
            </div>

            <div class="form-group">
                <label for="edit_variable_cost">Variable Cost Per Unit:</label>
                <input type="number" step="0.01" id="edit_variable_costperunit" name="variable_costperunit" required min="0">
            </div>
            <button type="submit" class="update-btn">Update</button>
        </form>
    </div>
</div>
    <script>    
        // Toggle sidebar visibility
document.querySelector('.toggle-sidebar').addEventListener('click', function () {
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main');
    
    // Toggle sidebar open class
    sidebar.classList.toggle('open');
    
    // Toggle main content shift class
    mainContent.classList.toggle('shift');
});
        
        // Function to print the business tracker
        function printBusinessTracker() {
            const businessTable = document.querySelector('table');
            const newWindow = window.open('', '_blank', 'width=800,height=650');

            const clonedTable = businessTable.cloneNode(true);

            // Remove edit and delete buttons from the print version
            const headerCells = clonedTable.querySelectorAll('th');
            if (headerCells.length > 0) {
                headerCells[7].remove(); // Remove Edit button
                headerCells[8].remove(); // Remove Delete button
            }

            const rows = clonedTable.querySelectorAll('tr');
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                if (cells.length > 0) {
                    if (cells.length > 7) cells[7].remove(); // Remove Edit button
                    if (cells.length > 8) cells[8].remove(); // Remove Delete button
                }
            });

            newWindow.document.write('<html><head><title>Print Business Tracker</title>');
            newWindow.document.write('<style>');
            newWindow.document.write('body { font-family: Arial, sans-serif; text-align: center; margin: 20px; }');
            newWindow.document.write('h1 { margin-bottom: 20px; }');
            newWindow.document.write('table { margin: 0 auto; border-collapse: collapse; width: 80%; }');
            newWindow.document.write('th, td { padding: 8px 12px; text-align: left; border: 1px solid #ddd; }');
            newWindow.document.write('th { background-color: #f4f4f4; }');
            newWindow.document.write('</style>');
            newWindow.document.write('</head><body>');
            newWindow.document.write('<h1>Business Tracker - Financial Compass</h1>');
            newWindow.document.write(clonedTable.outerHTML);
            newWindow.document.write('</body></html>');
            newWindow.document.close();
            newWindow.print();
        }
</script>
    <script src="modal.js"></script>
</body>
</html>
