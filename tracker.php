<?php 
session_start();
include("conne.php");

if (!$db) {
    echo "Disconnected" . mysqli_connect_error();
} else {
    // Check if user is logged in and set user_id
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];

        // Fetch user details
        $sql_username = "SELECT * FROM user WHERE user_id = $user_id";
        $result_username = mysqli_query($db, $sql_username);
        if (mysqli_num_rows($result_username) > 0) {
            $row_username = mysqli_fetch_assoc($result_username);
            $username = $row_username['username'];
        } else {
            $username = "Unknown User";
        }

        // Fetch tracker data for the logged-in user
        $sql_track = "SELECT track.date, track.note, track.amount, track.budget 
                      FROM track 
                      JOIN user ON track.user_id = user.user_id 
                      WHERE track.user_id = $user_id";
        $result_track = mysqli_query($db, $sql_track);

        // Update entry if form is submitted
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Get updated data from form
            $originalNote = mysqli_real_escape_string($db, $_POST['original_note']);
            $originalDate = mysqli_real_escape_string($db, $_POST['original_date']);
            $newDate = mysqli_real_escape_string($db, $_POST['new_date']);
            $newNote = mysqli_real_escape_string($db, $_POST['new_note']);
            $newBudget = mysqli_real_escape_string($db, $_POST['new_budget']);
            $newAmount = mysqli_real_escape_string($db, $_POST['new_amount']);
            
            // Update query
            $update_sql = "UPDATE track 
                           SET date = '$newDate', note = '$newNote', budget = '$newBudget', amount = '$newAmount' 
                           WHERE user_id = $user_id AND note = '$originalNote' AND date = '$originalDate'";

            if (mysqli_query($db, $update_sql)) {
                echo "<script>alert('Entry updated successfully!'); window.location.href = 'tracker.php';</script>";
            } else {
                echo "<script>alert('Error updating entry: " . mysqli_error($db) . "');</script>";
            }
        }

    } else {
        $username = "Guest";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="track.css?v=4">
    <link rel="stylesheet" href="modal.css?v=2">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="icon" href="SAD.jpg" type="image/x-icon"/> 
    <title>Financial Compass: Navigating your Budgeting Journey with an Interactive Website</title>
</head>
<body>
    <div class="wrapper">
    <button class="toggle-sidebar">
        <i class="fa fa-bars"></i>
        </button>
        <div class="sidebar">
            <!-- Sidebar content -->
            <h2>Sidebar</h2>
            <ul>
                <li><a href="dashboard.php" title="Dashboard"><i class="fa fa-bar-chart"></i> Dashboard</a></li>
                <li><a href="tracker.php" title="Tracker"><i class="fa fa-table"></i> Tracker</a></li>
                <li><a href="add.php" title="Add"><i class="fa fa-plus"></i> Add</a></li>
                <li><a href="settings.php" title="Profile"><i class="fa fa-address-card"></i> Profile</a></li>
                <li><a href="login.php" title="Logout" onclick="return confirmLogout()"><i class="fa fa-sign-out"></i> Logout</a></li>
            </ul>
        </div>
        <div class="main">
            <div class="header">Tracker</div>
            <div class="info">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Note</th>
                            <th>Budget</th>
                            <th>Expenditure</th>
                            <th>Edit</th>
                            <th>Remove</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if(isset($result_track)) {
                            if(mysqli_num_rows($result_track) > 0) {
                                while($st = mysqli_fetch_assoc($result_track)) {
                                    echo "<tr>";
                                    echo "<td>".$st["date"]."</td>";
                                    echo "<td>".$st["note"]."</td>";
                                    echo "<td>".$st["budget"]."</td>";
                                    echo "<td>".$st["amount"]."</td>";
                                    echo "<td><button type='button' class='edit-btn' onclick='openEditModal(\"".htmlspecialchars($st["note"])."\", \"".htmlspecialchars($st["date"])."\", ".$st["budget"].", ".$st["amount"].")'>Edit</button></td>";
                                    echo "<td><a href='del.php?note=".urlencode($st["note"])."&date=".urlencode($st["date"])."' onclick='return confirm(\"Are you sure you want to delete this item?\")'><button type='button' class='button remove-btn'>
                                    <svg class='w-6 h-6 text-gray-800 dark:text-white' aria-hidden='true' xmlns='http://www.w3.org/2000/svg' width='24' height='24' fill='currentColor' viewBox='0 0 24 24'>
                                        <path fill-rule='evenodd' d='M2 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10S2 17.523 2 12Zm5.757-1a1 1 0 1 0 0 2h8.486a1 1 0 1 0 0-2H7.757Z' clip-rule='evenodd'/>
                                    </svg>
                                </button></a></td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6'><center>NO DATA EXIST</center></td></tr>";
                            }
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <?php
                            $sql_track = "SELECT track.date, track.note, track.amount, track.budget FROM track JOIN user ON track.user_id = user.user_id WHERE track.user_id = $user_id";
                            $result_track = mysqli_query($db, $sql_track);
                            $total_price = 0;
                            if(isset($result_track) && mysqli_num_rows($result_track) > 0) {
                                while($st = mysqli_fetch_assoc($result_track)) {
                                    $total_price += $st["amount"];
                                }
                            }
                        ?>
                        <tr>
                            <td colspan="3">Total</td>
                            <td colspan="3"><?php echo $total_price; ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
        <h2>Edit Personal Tracker</h2>
            <span class="close-btn" onclick="closeEditModal()">&times;</span>
            <form id="editModalForm" class="modal-form" action="tracker.php" method="POST">
                <input type="hidden" id="originalNote" name="original_note">
                <input type="hidden" id="originalDate" name="original_date">
                
                <label for="newDate">Date:</label>
                <input type="date" id="newDate" name="new_date" required>
                
                <label for="newNote">Note:</label>
                <input type="text" id="newNote" name="new_note" required>
                
                <label for="newBudget">Budget:</label>
                <input type="number" step="0.01" id="newBudget" name="new_budget" required>
                
                <label for="newAmount">Expenditure:</label>
                <input type="number" step="0.01" id="newAmount" name="new_amount" required>
                
                <button type="submit" class="submit-btn">Update Entry</button>
            </form>
        </div>
    </div>

    <script src="logout.js"></script>
    <script src="modal2.js"></script>
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
    </script>
</body>
</html>
