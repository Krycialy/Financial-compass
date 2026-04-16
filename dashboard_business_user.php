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
$profit = 0;
$revenue = 0;
$variable_cost = 0; // Initialize variable cost

if(isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    $user_id = mysqli_real_escape_string($db, $user_id);

    $financial_db = mysqli_select_db($db, 'u627256117_fincompass'); 
    
    if(!$financial_db){
        echo "Error selecting financial database: " . mysqli_error($db);
        exit;
    }

    // Query to get user and business data
    $sql = "
        SELECT u.username, u.profile_picture, b.quantity, b.price, b.cost, b.fixed_cost, b.variable_cost, b.variable_costperunit
        FROM user u
        LEFT JOIN business b ON u.user_id = b.user_id
        WHERE u.user_id = '$user_id'
    ";
    $result = mysqli_query($db, $sql);

    if($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $username = $row['username'];
        if (!empty($row['profile_picture'])) {
            $profile_picture = $row['profile_picture'];
        }
        
        $quantity = $row['quantity'];
        $price = $row['price'];
        $cost = $row['cost'];
        $fixed_cost = isset($row['fixed_cost']) ? $row['fixed_cost'] : 0; 
        $variable_cost = isset($row['variable_cost']) ? $row['variable_cost'] : 0; 
        $variable_costperunit = isset($row['variable_costperunit']) ? $row['variable_costperunit'] : 0;

        // Correct calculation of variable cost based on quantity and cost per unit
        if ($variable_costperunit > 0 && $quantity > 0) {
            $variable_cost = $quantity * $variable_costperunit;
        }

        $revenue = $quantity * $price;
        $profit =  $revenue - $variable_cost - $fixed_cost; 
    } else {
        $username = "Unknown User";
    }
} 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financial Compass: Business Dashboard</title>
    <link rel="icon" href="SAD.jpg" type="image/x-icon"/>
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="dashboard_business_user.css?v=4">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0&family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@48,400,1,0" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
</head>
<body>
<!-- main-->
    <div class="wrapper">
    <button class="toggle-sidebar">
        <i class="fa fa-bars"></i>
        </button>
        
        <div class="sidebar">
            <h2>Sidebar</h2>
            <ul>
                <li><a href="dashboard_business_user.php" title="Dashboard"><i class="fa fa-bar-chart"></i> Dashboard</a></li>
                <li><a href="tracker_business.php" title="Tracker"><i class="fa fa-table"></i> Tracker</a></li>
                <li><a href="add_business.php" title="Add"><i class="fa fa-plus"></i> Add</a></li>
                <li><a href="settings_busi.php" title="Profile"><i class="fa fa-address-card"></i> Profile</a></li>
                <li><a href="login.php" title="Logout" onclick="return confirmLogout()"><i class="fa fa-sign-out"></i> Logout</a></li>
            </ul>
        
        </div>
        <div class="main">
            <div class="header">
                <img src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture" class="profile-pic">
                <span class="welcome-text">Welcome <span style="color: Red;"> <?php echo htmlspecialchars($username);?></span>!</span>
                
            </div>
            <div class="info">
                <div class="budgetcard">
                    <p>Profit:</p>
                    <div class="val">
                        <label for="">₱</label>
                        <label for="Profit"><?php echo number_format($profit, 2); ?></label>
                    </div>
                </div>
                <div class="totalcard">
                    <p>Revenue:</p>
                    <div class="val">
                        <label for="">₱</label>
                        <label for="Revenue"><?php echo number_format($revenue, 2); ?></label>
                    </div>
                </div>
                <div class="variable_cost">
                    <p>Variable Cost:</p>
                    <div class="val">
                        <label for="">₱</label>
                        <label for="variable_cost"><?php echo number_format($variable_cost, 2); ?></label>
                    </div>
                </div>
            </div>
            
            <div class="user-chart">
                <h3>Profit and Revenue Visualization</h3>
                <canvas id="profitRevenueChart"></canvas>
                <script>
                    const ctx = document.getElementById('profitRevenueChart').getContext('2d');
                    new Chart(ctx, {
                        type: 'bar',  
                        data: {
                            labels: ['Current'],  
                            datasets: [
                                {
                                    label: 'Profit',
                                    data: [<?php echo $profit; ?>],  
                                    backgroundColor: 'rgba(76, 175, 80, 0.5)', 
                                    borderColor: '#4caf50',
                                    borderWidth: 1
                                },
                                {
                                    label: 'Revenue',
                                    data: [<?php echo $revenue; ?>],  
                                    backgroundColor: 'rgba(244, 67, 54, 0.5)', 
                                    borderColor: '#f44336',
                                    borderWidth: 1
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    title: {
                                        display: true,
                                        text: 'Amount (₱)'
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'top'
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(tooltipItem) {
                                            return tooltipItem.dataset.label + ': ₱' + tooltipItem.raw.toFixed(2);
                                        }
                                    }
                                }
                            }
                        }
                    });
                </script>
             <!-- Chatbot Toggler -->
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