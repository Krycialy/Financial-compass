<?php 
session_start(); 
include("conne.php");

if(!$db){
    echo "Disconnected: " . mysqli_connect_error();
    exit;
}

$username = "Guest";
$profile_picture = "uploads/default_profile.png";
$user_id = null;

if(isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    $user_id = mysqli_real_escape_string($db, $user_id);

    $sql = "SELECT username, profile_picture FROM user WHERE user_id = '$user_id'";
    $result = mysqli_query($db, $sql);

    if($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $username = $row['username'];
        if (!empty($row['profile_picture'])) {
            $profile_picture = $row['profile_picture'];
        }
    } else {
        $username = "Unknown User";
    }
} 

$total_price = 0;
$total_budget = 0;
$budget_left = 0;
$budget = 0; // Initialize the current budget
$dataPointsBudgetLeft = array();
$dataPointsExpenses = array();
$dataPointsBudget = array(); // New array to track individual budgets

if ($user_id) {
    // Use a prepared statement to prevent SQL injection
    $sql_track = "
        SELECT 
            track.date, 
            SUM(track.budget) AS budget,
            SUM(track.amount) AS total_expenses, 
            SUM(track.budget) AS total_budget 
        FROM track 
        WHERE track.user_id = ? 
        GROUP BY YEAR(track.date), MONTH(track.date)
        ORDER BY track.date ASC
    ";

    if ($stmt = mysqli_prepare($db, $sql_track)) {
        mysqli_stmt_bind_param($stmt, "i", $user_id); // Bind user_id as an integer
        mysqli_stmt_execute($stmt);
        $result_track = mysqli_stmt_get_result($stmt);

        if ($result_track && mysqli_num_rows($result_track) > 0) {
            while ($st = mysqli_fetch_assoc($result_track)) {
                $date = strtotime($st["date"]) * 1000; // Convert date to milliseconds
                $budget = $st["budget"]; // Get the current budget value
                $total_price += $st["total_expenses"];
                $total_budget += $st["total_budget"];
                $budget_left = $total_budget - $total_price;

                // Add data points for budget, expenses, and budget left
                $dataPointsBudgetLeft[] = array("x" => $date, "y" => $budget_left);
                $dataPointsExpenses[] = array("x" => $date, "y" => $st["total_expenses"]);
                $dataPointsBudget[] = array("x" => $date, "y" => $budget);
            }
        } else {
            // Handle case where no data is returned
            error_log("No data found for user_id: $user_id");
        }

        mysqli_stmt_close($stmt); // Close the prepared statement
    } else {
        // Handle SQL preparation errors
        error_log("Failed to prepare SQL query: " . mysqli_error($db));
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financial Compass: Navigating your Budgeting Journey with an Interactive Website</title>
    <link rel="icon" href="SAD.jpg" type="image/x-icon"/>
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="dashboard.css?v=17">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"> <!--icon link-->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0&family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@48,400,1,0" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
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
            <div class="header">
                <img src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture" class="profile-pic">
                <span class="welcome-text">Welcome <span style="color: Red;"> <?php echo htmlspecialchars($username);?></span>!</span>
            </div>
            <div class="info">
                <div class="totalcard">
                    <p>Budget:</p>
                    <div class="val">
                        <label for="">P</label>
                        <label for="totalexpenses"><?php echo number_format($budget, 2); ?></label>
                    </div>
                </div>
                <div class="totalcard">
                    <p>Total Expenses:</p>
                    <div class="val">
                        <label for="">P</label>
                        <label for="totalexpenses"><?php echo number_format($total_price, 2); ?></label>
                    </div>
                </div>
                <div class="budgetcard">
                    <p>Budget Left:</p>
                    <div class="val">
                        <label for="">P</label>
                        <label for="totalexpenses"><?php echo number_format($budget_left, 2); ?></label>
                    </div>
                </div>
            </div>
            <div class="user-chart-card">
                <h3>Budget Left vs Total Expenses</h3>
                <canvas id="dashboardChart"></canvas>
                <script>
                    const ctx = document.getElementById('dashboardChart').getContext('2d');
                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            datasets: [
                                {
                                    label: 'Budget Left',
                                    data: <?php echo json_encode($dataPointsBudgetLeft); ?>,
                                    borderColor: '#4caf50',
                                    backgroundColor: 'rgba(76, 175, 80, 0.5)',
                                    fill: true,
                                    tension: 0.3
                                },
                                {
                                    label: 'Total Expenses',
                                    data: <?php echo json_encode($dataPointsExpenses); ?>,
                                    borderColor: '#f44336',
                                    backgroundColor: 'rgba(244, 67, 54, 0.5)',
                                    fill: true,
                                    tension: 0.3
                                }
                            ]
                        },
                        options: {
                            scales: {
                                x: {
                                    type: 'time',
                                    time: {
                                    unit: 'month'
                                    },
                                    title: {
                                        display: true,
                                        text: 'Date'
                                    }
                                },
                                y: {
                                    title: {
                                        display: true,
                                        text: 'Amount (P)'
                                    },
                                    beginAtZero: true
                                }
                            },
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'top'
                                }
                            }
                        }
                    });

                    function confirmLogout() {
                        return confirm("Are you sure you want to log out?");
                    }
                </script>
            <button id="chatbot-toggler">
      <span class="material-symbols-rounded">mode_comment</span>
      <span class="material-symbols-rounded">close</span>
    </button>

    <div class="chatbot-popup">
      <!-- Chatbot Header -->
      <div class="chat-header">
        <div class="header-info">
          <svg class="chatbot-logo" xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 1024 1024">
            <path
              d="M738.3 287.6H285.7c-59 0-106.8 47.8-106.8 106.8v303.1c0 59 47.8 106.8 106.8 106.8h81.5v111.1c0 .7.8 1.1 1.4.7l166.9-110.6 41.8-.8h117.4l43.6-.4c59 0 106.8-47.8 106.8-106.8V394.5c0-59-47.8-106.9-106.8-106.9zM351.7 448.2c0-29.5 23.9-53.5 53.5-53.5s53.5 23.9 53.5 53.5-23.9 53.5-53.5 53.5-53.5-23.9-53.5-53.5zm157.9 267.1c-67.8 0-123.8-47.5-132.3-109h264.6c-8.6 61.5-64.5 109-132.3 109zm110-213.7c-29.5 0-53.5-23.9-53.5-53.5s23.9-53.5 53.5-53.5 53.5 23.9 53.5 53.5-23.9 53.5-53.5 53.5zM867.2 644.5V453.1h26.5c19.4 0 35.1 15.7 35.1 35.1v121.1c0 19.4-15.7 35.1-35.1 35.1h-26.5zM95.2 609.4V488.2c0-19.4 15.7-35.1 35.1-35.1h26.5v191.3h-26.5c-19.4 0-35.1-15.7-35.1-35.1zM561.5 149.6c0 23.4-15.6 43.3-36.9 49.7v44.9h-30v-44.9c-21.4-6.5-36.9-26.3-36.9-49.7 0-28.6 23.3-51.9 51.9-51.9s51.9 23.3 51.9 51.9z"
            />
          </svg>
          <h2 class="logo-text">FinBot</h2>
        </div>
        <button id="close-chatbot" class="material-symbols-rounded">keyboard_arrow_down</button>
      </div>

      <!-- Chatbot Body -->
      <div class="chat-body">
        <div class="message bot-message">
          <svg class="bot-avatar" xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 1024 1024">
            <path
              d="M738.3 287.6H285.7c-59 0-106.8 47.8-106.8 106.8v303.1c0 59 47.8 106.8 106.8 106.8h81.5v111.1c0 .7.8 1.1 1.4.7l166.9-110.6 41.8-.8h117.4l43.6-.4c59 0 106.8-47.8 106.8-106.8V394.5c0-59-47.8-106.9-106.8-106.9zM351.7 448.2c0-29.5 23.9-53.5 53.5-53.5s53.5 23.9 53.5 53.5-23.9 53.5-53.5 53.5-53.5-23.9-53.5-53.5zm157.9 267.1c-67.8 0-123.8-47.5-132.3-109h264.6c-8.6 61.5-64.5 109-132.3 109zm110-213.7c-29.5 0-53.5-23.9-53.5-53.5s23.9-53.5 53.5-53.5 53.5 23.9 53.5 53.5-23.9 53.5-53.5 53.5zM867.2 644.5V453.1h26.5c19.4 0 35.1 15.7 35.1 35.1v121.1c0 19.4-15.7 35.1-35.1 35.1h-26.5zM95.2 609.4V488.2c0-19.4 15.7-35.1 35.1-35.1h26.5v191.3h-26.5c-19.4 0-35.1-15.7-35.1-35.1zM561.5 149.6c0 23.4-15.6 43.3-36.9 49.7v44.9h-30v-44.9c-21.4-6.5-36.9-26.3-36.9-49.7 0-28.6 23.3-51.9 51.9-51.9s51.9 23.3 51.9 51.9z"
            />
          </svg>
          <!-- prettier-ignore -->
          <div class="message-text"> Hey there  <br /> How can I help you today? </div>
        </div>
      </div>

      <!-- Chatbot Footer -->
      <div class="chat-footer">
        <form action="#" class="chat-form">
          <textarea placeholder="Message..." class="message-input" required></textarea>
          <div class="chat-controls">
            <button type="button" id="emoji-picker" class="material-symbols-outlined">sentiment_satisfied</button>
            <div class="file-upload-wrapper">
              <input type="file" accept="image/*" id="file-input" hidden />
              <img src="#" />
              <button type="button" id="file-upload" class="material-symbols-rounded">attach_file</button>
              <button type="button" id="file-cancel" class="material-symbols-rounded">close</button>
            </div>
            <button type="submit" id="send-message" class="material-symbols-rounded">arrow_upward</button>
          </div>
        </form>
      </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/emoji-mart@latest/dist/browser.js"></script>
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
            <script src="chatbot.js"></script>
        </div>
    </div>
</body>
</html>
