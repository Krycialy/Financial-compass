<?php 
session_start(); 
include("conne.php");

if(!$db){
    echo "Disconnected: " . mysqli_connect_error();
    exit;
}

// Switch to the 'financial' database
$financial_db = mysqli_select_db($db, 'u627256117_fincompass');

if(!$financial_db){
    echo "Error selecting financial database: " . mysqli_error($db);
    exit;
}

// Fetch all users with 'Business' option
$sql_business_users = "
    SELECT u.user_id, u.username, u.profile_picture, 
           COALESCE(SUM(b.quantity * b.price), 0) as revenue, 
           COALESCE(SUM(b.cost + (b.quantity - b.price)), 0) as profit
    FROM user u
    LEFT JOIN business b ON u.user_id = b.user_id
    WHERE u.options = 'Small Business'
    GROUP BY u.user_id, u.username, u.profile_picture
";
$result_business_users = mysqli_query($db, $sql_business_users);

$business_users_data = array();

if ($result_business_users && mysqli_num_rows($result_business_users) > 0) {
    while ($user = mysqli_fetch_assoc($result_business_users)) {
        $business_users_data[] = $user;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Users Financial Overview</title>
    <link rel="icon" href="SAD.jpg" type="image/x-icon"/>
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="smallbusiness.css?v=2">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
    <style>
        .user-chart-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
        }
        .user-chart {
            width: 45%;
            margin: 10px;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>Admin Dashboard</h1>
        <nav>
            <a href="Admin.php">User Management</a>
            <a href="Personal_Dashboard.php"> Personal Dashboard</a>
            <a href="SmallBusiness.php"> Small Business Dashboard</a>
            <a href="login.php" onclick="return confirmLogout()">Logout</a>
        </nav>
    </div>
    <h1>All Users Profit and Revenue Graphs</h1>
    <div class="user-chart-container">
        <?php foreach ($business_users_data as $user): ?>
            <div class="user-chart">
                <h3><?php echo htmlspecialchars($user['username']); ?></h3>
                <canvas id="chart-<?php echo $user['user_id']; ?>"></canvas>
            </div>
        <?php endforeach; ?>
    </div>

    <script>
        <?php foreach ($business_users_data as $user): ?>
        (function() {
            const ctx = document.getElementById('chart-<?php echo $user['user_id']; ?>').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Current'],
                    datasets: [
                        {
                            label: 'Profit',
                            data: [<?php echo $user['profit']; ?>],
                            backgroundColor: 'rgba(76, 175, 80, 0.5)',
                            borderColor: '#4caf50',
                            borderWidth: 1
                        },
                        {
                            label: 'Revenue',
                            data: [<?php echo $user['revenue']; ?>],
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
        })();
        <?php endforeach; ?>

        function confirmLogout() {
            return confirm("Are you sure you want to log out?");
        }
    </script>
</body>
</html>