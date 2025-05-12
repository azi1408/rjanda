<?php

session_start();
include('connection.php');

if (!isset($_SESSION['user_id'])) {
    echo "<!DOCTYPE html>
    <html>
    <head>
        <script>
            alert('You need to log in first.');
        </script>";
    echo "<script>window.location.href = 'index.php'</script>";
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = "";

// Prepare the SQL statement
$query = "SELECT name, role FROM registertb WHERE userid = ?";
$stmt = $conn->prepare($query);

if (!$stmt) {
    die("SQL prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($name, $roles);

if ($stmt->fetch()) {
    $user_name = $name;
    if ($roles !== 'admin') {
        header("Location: forbidden.php");
        exit();
    }
} 

$stmt->close();

// Get weekly sales data
$weekly_sql = "SELECT 
    period_value as week,
    total_orders,
    completed_orders,
    total_sales
FROM dashboard
WHERE period_type = 'weekly'
ORDER BY date_recorded DESC
LIMIT 12";

$weekly_result = $conn->query($weekly_sql);
if (!$weekly_result) {
    die("Weekly query failed: " . $conn->error);
}
$weekly_data = [];
while ($row = $weekly_result->fetch_assoc()) {
    $weekly_data[] = $row;
}

// Get monthly sales data
$monthly_sql = "SELECT 
    period_value as month,
    total_orders,
    completed_orders,
    total_sales
FROM dashboard
WHERE period_type = 'monthly'
ORDER BY date_recorded DESC
LIMIT 12";

$monthly_result = $conn->query($monthly_sql);
if (!$monthly_result) {
    die("Monthly query failed: " . $conn->error);
}
$monthly_data = [];
while ($row = $monthly_result->fetch_assoc()) {
    $monthly_data[] = $row;
}

// Get today's statistics
$today_sql = "SELECT 
    total_orders,
    completed_orders,
    total_sales
FROM dashboard
WHERE period_type = 'daily' 
AND date_recorded = CURDATE()";

$today_result = $conn->query($today_sql);
if (!$today_result) {
    die("Today's stats query failed: " . $conn->error);
}
$today_stats = $today_result->fetch_assoc();

// Get top selling packages
$top_packages_sql = "SELECT 
    p.name as package_name,
    COUNT(o.id) as order_count,
    SUM(o.amount) as total_sales
FROM orders o
JOIN packages p ON o.package_id = p.id
WHERE o.status = 'completed'
GROUP BY p.id, p.name
ORDER BY total_sales DESC
LIMIT 5";

$top_packages_result = $conn->query($top_packages_sql);
if (!$top_packages_result) {
    die("Top packages query failed: " . $conn->error);
}
$top_packages = [];
while ($row = $top_packages_result->fetch_assoc()) {
    $top_packages[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Sales Analytics</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: #2e2e2e;
            color: #f1f1f1;
        }
        .navbar {
            background-color: #111;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .navbar-left {
            display: flex;
            align-items: center;
        }
        .greeting {
            font-size: 1.5rem;
            color: #f7f2e9;
            margin-left: 25px;
        }
        .logo-img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 15px;
            border: 2px solid beige;
        }
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background-color: #3e3e3e;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }
        .stat-card h3 {
            color: #d4b895;
            margin: 0 0 10px 0;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #fff;
        }
        .chart-container {
            background-color: #3e3e3e;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .chart-title {
            color: #d4b895;
            margin-bottom: 20px;
        }
        .menu-toggle {
            font-size: 28px;
            background: none;
            border: none;
            color: beige;
            cursor: pointer;
            display: block;
        }
        .nav-links {
            display: none;
            flex-direction: column;
            position: absolute;
            top: 70px;
            right: 30px;
            background-color: #222;
            border-radius: 8px;
            padding: 10px 0;
            box-shadow: 0px 4px 8px rgba(0,0,0,0.5);
            z-index: 1000;
        }
        .nav-links.show {
            display: flex;
        }
        .nav-links li {
            padding: 10px 20px;
        }
        .nav-links a {
            color: beige;
            text-decoration: none;
            font-weight: 500;
        }
        .nav-links a:hover {
            color: #d4b895;
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="navbar-left">
        <img src="logo.jfif" alt="Logo" class="logo-img">
        <?php if (!empty($user_name)) {
            echo "<span class='greeting'>Hello, <strong>$user_name</strong>!</span>";
        } ?>
    </div>
    <button class="menu-toggle" onclick="toggleMenu()">☰</button>
    <ul id="navLinks" class="nav-links">
        <li><a href="admin_home.php">Orders</a></li>
        <li><a href="admin_users.php">Users</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</nav>

<div class="container">
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Today's Orders</h3>
            <div class="stat-value"><?php echo $today_stats['total_orders'] ?? 0; ?></div>
        </div>
        <div class="stat-card">
            <h3>Today's Sales</h3>
            <div class="stat-value">₱<?php echo number_format($today_stats['total_sales'] ?? 0, 2); ?></div>
        </div>
        <div class="stat-card">
            <h3>Completed Today</h3>
            <div class="stat-value"><?php echo $today_stats['completed_orders'] ?? 0; ?></div>
        </div>
    </div>

    <div class="chart-container">
        <h2 class="chart-title">Weekly Sales Overview</h2>
        <canvas id="weeklyChart"></canvas>
    </div>

    <div class="chart-container">
        <h2 class="chart-title">Monthly Sales Overview</h2>
        <canvas id="monthlyChart"></canvas>
    </div>

    <div class="chart-container">
        <h2 class="chart-title">Top Selling Packages</h2>
        <canvas id="packagesChart"></canvas>
    </div>
</div>

<script>
    function toggleMenu() {
        document.getElementById("navLinks").classList.toggle("show");
    }

    document.addEventListener("click", function(event) {
        const menu = document.getElementById("navLinks");
        const button = document.querySelector(".menu-toggle");
        if (!menu.contains(event.target) && !button.contains(event.target)) {
            menu.classList.remove("show");
        }
    });

    // Weekly Chart
    const weeklyData = <?php echo json_encode(array_reverse($weekly_data)); ?>;
    new Chart(document.getElementById('weeklyChart'), {
        type: 'line',
        data: {
            labels: weeklyData.map(item => 'Week ' + item.week.split('-')[1]),
            datasets: [{
                label: 'Total Sales',
                data: weeklyData.map(item => item.total_sales),
                borderColor: '#d4b895',
                backgroundColor: 'rgba(212, 184, 149, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    labels: {
                        color: '#f1f1f1'
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: '#f1f1f1',
                        callback: function(value) {
                            return '₱' + value.toLocaleString();
                        }
                    },
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)'
                    }
                },
                x: {
                    ticks: {
                        color: '#f1f1f1'
                    },
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)'
                    }
                }
            }
        }
    });

    // Monthly Chart
    const monthlyData = <?php echo json_encode(array_reverse($monthly_data)); ?>;
    new Chart(document.getElementById('monthlyChart'), {
        type: 'bar',
        data: {
            labels: monthlyData.map(item => {
                const [year, month] = item.month.split('-');
                return new Date(year, month - 1).toLocaleString('default', { month: 'short' }) + ' ' + year;
            }),
            datasets: [{
                label: 'Total Sales',
                data: monthlyData.map(item => item.total_sales),
                backgroundColor: '#d4b895',
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    labels: {
                        color: '#f1f1f1'
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: '#f1f1f1',
                        callback: function(value) {
                            return '₱' + value.toLocaleString();
                        }
                    },
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)'
                    }
                },
                x: {
                    ticks: {
                        color: '#f1f1f1'
                    },
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)'
                    }
                }
            }
        }
    });

    // Top Packages Chart
    const packagesData = <?php echo json_encode($top_packages); ?>;
    new Chart(document.getElementById('packagesChart'), {
        type: 'doughnut',
        data: {
            labels: packagesData.map(item => item.package_name),
            datasets: [{
                data: packagesData.map(item => item.total_sales),
                backgroundColor: [
                    '#d4b895',
                    '#c4a785',
                    '#b49775',
                    '#a48765',
                    '#947755'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        color: '#f1f1f1',
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = Math.round((value / total) * 100);
                            return `${label}: ₱${value.toLocaleString()} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
</script>
</body>
</html>

<?php $conn->close(); ?>
