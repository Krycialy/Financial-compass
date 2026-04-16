<?php
session_start();
include("conne.php");

$username = "Guest";
$user_id = null;

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $user_id = mysqli_real_escape_string($db, $user_id);

    $sql = "SELECT * FROM user WHERE user_id = '$user_id'";
    $result = mysqli_query($db, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $username = $row['username'];
    } else {
        $username = "Unknown User";
    }
}

// Assuming the correct column is 'options', adjust accordingly if it's different
$sql_all_users = "SELECT user_id, username, options FROM user WHERE role != 'admin' AND options != 'Small Business'";
$result_all_users = mysqli_query($db, $sql_all_users);

$allUsersData = array();

if ($result_all_users && mysqli_num_rows($result_all_users) > 0) {
    while ($user = mysqli_fetch_assoc($result_all_users)) {
        $user_id = $user['user_id'];
        $username = $user['username'];
        
        $dataPointsBudgetLeft = array();
        $dataPointsExpenses = array();

        $sql_track = "
            SELECT track.date, SUM(track.amount) as total_expenses, SUM(track.budget) as total_budget 
            FROM track 
            WHERE track.user_id = '$user_id' 
            GROUP BY YEAR(track.date), MONTH(track.date)
            ORDER BY track.date
        ";

        $result_track = mysqli_query($db, $sql_track);

        if ($result_track && mysqli_num_rows($result_track) > 0) {
            $total_price = 0;
            $total_budget = 0;
            while ($st = mysqli_fetch_assoc($result_track)) {
                $date = strtotime($st["date"]) * 1000; 
                $total_price += $st["total_expenses"];
                $total_budget += $st["total_budget"];
                $budget_left = $total_budget - $total_price;

                $dataPointsBudgetLeft[] = array("x" => $date, "y" => $budget_left);
                $dataPointsExpenses[] = array("x" => $date, "y" => $st["total_expenses"]);
            }
        }

        $allUsersData[] = array(
            "username" => $username,
            "budgetLeft" => $dataPointsBudgetLeft,
            "expenses" => $dataPointsExpenses
        );
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
    <link rel="stylesheet" href="personaldashboard.css?v=2">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
</head>
<body>
    <div class="navbar">
        <h1>Admin Dashboard</h1>
        <nav>
            <a href="Admin.php">User Management</a>
            <a href="Personal_Dashboard.php">Personal Dashboard</a>
            <a href="SmallBusiness.php">Small Business Dashboard</a>
            <a href="login.php" onclick="return confirmLogout()">Logout</a>
        </nav>
    </div>

    <h1>All Users Budget and Expenses Graphs</h1>
    <div class="container">
        <?php foreach ($allUsersData as $index => $userData): ?>
            <div class="user-chart">
                <h3><?php echo htmlspecialchars($userData['username']); ?></h3>
                <canvas id="chart-<?php echo $index; ?>"></canvas>
                <script>
                    const ctx<?php echo $index; ?> = document.getElementById('chart-<?php echo $index; ?>').getContext('2d');
                    new Chart(ctx<?php echo $index; ?>, {
                        type: 'bar',
                        data: {
                            datasets: [
                                {
                                    label: 'Budget Left',
                                    data: <?php echo json_encode($userData['budgetLeft']); ?>,
                                    borderColor: '#4caf50',
                                    backgroundColor: 'rgba(76, 175, 80, 0.5)',
                                    fill: true,
                                    tension: 0.3
                                },
                                {
                                    label: 'Expenses',
                                    data: <?php echo json_encode($userData['expenses'], JSON_NUMERIC_CHECK); ?>,
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
                </script>
            </div>
        <?php endforeach; ?>
    </div>

    <script>
        function confirmLogout() {
            return confirm("Are you sure you want to log out?");
        }
    </script>
</body>
</html>
<script>
    function confirmLogout() {
        return confirm("Are you sure you want to log out?");
    }
</script>
