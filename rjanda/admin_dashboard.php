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

// Get available months for selection
$months_sql = "SELECT DISTINCT DATE_FORMAT(created_at, '%Y-%m') as month_value,
    DATE_FORMAT(created_at, '%M %Y') as month_label
FROM orders
WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
ORDER BY month_value DESC";

$months_result = $conn->query($months_sql);
$available_months = [];
while ($row = $months_result->fetch_assoc()) {
    $available_months[] = $row;
}

// Get weekly sales data
$selected_month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
$weekly_sql = "SELECT 
    DATE_FORMAT(created_at, '%Y-%m-%d') as week_start,
    COUNT(*) as total_orders,
    SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) as completed_orders,
    SUM(total_price) as total_sales
FROM orders
WHERE DATE_FORMAT(created_at, '%Y-%m') = ?
GROUP BY YEARWEEK(created_at)
ORDER BY week_start ASC";

$stmt = $conn->prepare($weekly_sql);
$stmt->bind_param("s", $selected_month);
$stmt->execute();
$weekly_result = $stmt->get_result();
$weekly_data = [];
while ($row = $weekly_result->fetch_assoc()) {
    $weekly_data[] = $row;
}
$stmt->close();

// Get monthly sales data
$monthly_sql = "SELECT 
    DATE_FORMAT(created_at, '%Y-%m') as month,
    COUNT(*) as total_orders,
    SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) as completed_orders,
    SUM(total_price) as total_sales
FROM orders
WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
GROUP BY DATE_FORMAT(created_at, '%Y-%m')
ORDER BY month DESC
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
    COUNT(*) as total_orders,
    SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) as completed_orders,
    SUM(total_price) as total_sales
FROM orders 
WHERE DATE(created_at) = CURDATE()";

$today_result = $conn->query($today_sql);
if (!$today_result) {
    die("Today's stats query failed: " . $conn->error);
}
$today_stats = $today_result->fetch_assoc();

// Get top selling packages
$top_packages_sql = "SELECT 
    COALESCE(p.package_name, 'Custom Orders') as package_name,
    COUNT(o.id) as order_count,
    SUM(o.total_price) as total_sales
FROM orders o
LEFT JOIN packages p ON o.package_id = p.id
WHERE o.status = 'done'
GROUP BY COALESCE(p.package_name, 'Custom Orders')
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

// Get recent orders (last 7 days)
$recent_orders_sql = "SELECT 
    o.id,
    o.created_at,
    o.total_price,
    o.status,
    r.name as customer_name,
    p.package_name,
    o.is_custom
FROM orders o
JOIN registertb r ON o.user_id = r.userid
LEFT JOIN packages p ON o.package_id = p.id
WHERE o.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
ORDER BY o.created_at DESC";

$recent_orders_result = $conn->query($recent_orders_sql);
if (!$recent_orders_result) {
    die("Recent orders query failed: " . $conn->error);
}
$recent_orders = [];
while ($row = $recent_orders_result->fetch_assoc()) {
    $recent_orders[] = $row;
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
            padding: 10px 20px;
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
            font-size: 1.2rem;
            color: #f7f2e9;
            margin-left: 15px;
        }
        .logo-img {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
            border: 2px solid beige;
        }
        .container {
            max-width: 1000px;
            margin: 20px auto;
            padding: 15px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-card {
            background-color: #3e3e3e;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        .stat-card h3 {
            color: #d4b895;
            margin: 0 0 8px 0;
            font-size: 0.9rem;
        }
        .stat-value {
            font-size: 1.5rem;
            font-weight: bold;
            color: #fff;
        }
        .chart-container {
            background-color: #3e3e3e;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            min-height: 400px;
            position: relative;
            transition: all 0.3s ease-out;
        }

        .chart-container.minimized {
            min-height: auto;
            padding: 10px 20px;
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-top: 40px;
            position: relative;
        }

        .chart-container.minimized .chart-header {
            margin-bottom: 0;
            padding-top: 0;
        }

        .chart-title {
            color: #d4b895;
            margin: 0;
            padding-right: 40px; /* Add space for the arrow */
        }

        .toggle-section-btn {
            background: none;
            border: none;
            color: #d4b895;
            font-size: 24px;
            cursor: pointer;
            padding: 5px;
            transition: transform 0.3s ease;
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%) rotate(180deg);
        }

        .toggle-section-btn.rotated {
            transform: translateY(-50%) rotate(0deg);
        }

        .chart-controls {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-top: 10px;
        }

        .section-content {
            transition: all 0.3s ease-out;
            overflow: hidden;
        }

        .section-content.hidden {
            display: none;
        }

        .menu-toggle {
            font-size: 24px;
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
            top: 50px;
            right: 20px;
            background-color: #222;
            border-radius: 8px;
            padding: 8px 0;
            box-shadow: 0px 4px 8px rgba(0,0,0,0.5);
            z-index: 1000;
        }
        .nav-links.show {
            display: flex;
        }
        .nav-links li {
            padding: 8px 15px;
        }
        .nav-links a {
            color: beige;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
        }
        .nav-links a:hover {
            color: #d4b895;
        }
        .view-toggle {
            display: flex;
            gap: 10px;
            background: #2a2a2a;
            padding: 5px;
            border-radius: 20px;
        }
        .toggle-btn {
            background: none;
            border: none;
            color: #f1f1f1;
            padding: 5px 15px;
            border-radius: 15px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        .toggle-btn.active {
            background: #d4b895;
            color: #2e2e2e;
        }
        .toggle-btn:hover:not(.active) {
            background: #3e3e3e;
        }
        .month-selector {
            display: none;
        }
        .month-selector.show {
            display: block;
        }
        .month-selector select {
            background: #2a2a2a;
            color: #f1f1f1;
            border: 1px solid #3e3e3e;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.9rem;
            cursor: pointer;
        }
        .month-selector select:focus {
            outline: none;
            border-color: #d4b895;
        }
        .recent-orders-section {
            background-color: #3e3e3e;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            transition: all 0.3s ease-out;
        }

        .recent-orders-section.minimized {
            padding: 10px 20px;
        }

        .recent-orders-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            margin-bottom: 20px;
        }

        .recent-orders-section.minimized .recent-orders-header {
            margin-bottom: 0;
        }

        .recent-orders-title {
            color: #d4b895;
            margin: 0;
            padding-right: 40px;
        }

        .recent-orders-content {
            transition: all 0.3s ease-out;
            overflow: hidden;
        }

        .recent-orders-content.hidden {
            display: none;
        }
        .recent-orders-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #2a2a2a;
            border-radius: 8px;
            overflow: hidden;
        }
        .recent-orders-table th,
        .recent-orders-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #3e3e3e;
        }
        .recent-orders-table th {
            background-color: #1a1a1a;
            color: #d4b895;
            font-weight: 500;
        }
        .recent-orders-table tr:hover {
            background-color: #323232;
        }
        .order-type {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        .type-custom {
            background-color: #9C27B0;
            color: white;
        }
        .type-package {
            background-color: #2196F3;
            color: white;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        .status-done {
            background-color: #4CAF50;
            color: white;
        }
        .status-pending {
            background-color: #FFC107;
            color: #2e2e2e;
        }
        .status-cancelled {
            background-color: #f44336;
            color: white;
        }
        canvas {
            margin-top: 20px;
            max-height: 350px;
            width: 100% !important;
            height: 100% !important;
        }
        /* Add specific styles for the packages chart container */
        #packagesChart {
            position: relative;
            margin: 0 auto;
            max-width: 100%;
            max-height: 350px;
        }
        @media screen and (max-width: 768px) {
            .container {
                padding: 10px;
            }
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
        <li><a href="admin_dashboard.php">Dashboard</a></li>
        <li><a href="admin_home.php">Orders</a></li>
        <li><a href="admin_users.php">Users</a></li>
        <li><a href="admin_reviews.php">Reviews</a></li>
        <li><a href="admin_chat.php">💬 Chat</a></li>
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

    <!-- Recent Orders Section -->
    <div class="recent-orders-section" id="recentOrdersContainer">
        <div class="recent-orders-header">
            <h2 class="recent-orders-title">Recent Orders (Last 7 Days)</h2>
            <button class="toggle-section-btn" onclick="toggleSection('recentOrders')">▼</button>
        </div>
        <div id="recentOrders" class="recent-orders-content">
            <table class="recent-orders-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Type</th>
                        <th>Package/Details</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_orders as $order): ?>
                    <tr>
                        <td>#<?php echo $order['id']; ?></td>
                        <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                        <td>
                            <span class="order-type type-<?php echo $order['is_custom'] ? 'custom' : 'package'; ?>">
                                <?php echo $order['is_custom'] ? 'Custom' : 'Package'; ?>
                            </span>
                        </td>
                        <td><?php echo $order['is_custom'] ? 'Custom Order' : htmlspecialchars($order['package_name']); ?></td>
                        <td><?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></td>
                        <td>₱<?php echo number_format($order['total_price'], 2); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="chart-container" id="salesOverviewContainer">
        <div class="chart-header">
            <h2 class="chart-title">Sales Overview</h2>
            <button class="toggle-section-btn" onclick="toggleSection('salesOverview')">▼</button>
        </div>
        <div class="chart-controls">
            <div class="view-toggle">
                <button id="weeklyBtn" class="toggle-btn active">Weekly</button>
                <button id="monthlyBtn" class="toggle-btn">Monthly</button>
            </div>
            <div id="monthSelector" class="month-selector">
                <select id="monthSelect" onchange="updateChart()">
                    <?php foreach ($available_months as $month): ?>
                        <option value="<?php echo $month['month_value']; ?>" 
                                <?php echo $month['month_value'] === $selected_month ? 'selected' : ''; ?>>
                            <?php echo $month['month_label']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div id="salesOverview" class="section-content">
            <canvas id="salesChart"></canvas>
        </div>
    </div>

    <div class="chart-container" id="topPackagesContainer">
        <div class="chart-header">
            <h2 class="chart-title">Top Selling Packages</h2>
            <button class="toggle-section-btn" onclick="toggleSection('topPackages')">▼</button>
        </div>
        <div id="topPackages" class="section-content">
            <canvas id="packagesChart"></canvas>
        </div>
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

    // Sales Chart Data
    const weeklyData = <?php echo json_encode(array_reverse($weekly_data)); ?>;
    const monthlyData = <?php echo json_encode(array_reverse($monthly_data)); ?>;
    let salesChart;

    function createSalesChart(data, type) {
        const ctx = document.getElementById('salesChart').getContext('2d');
        
        if (salesChart) {
            salesChart.destroy();
        }

        salesChart = new Chart(ctx, {
            type: type === 'weekly' ? 'line' : 'bar',
            data: {
                labels: data.map(item => {
                    if (type === 'weekly') {
                        const date = new Date(item.week_start);
                        const month = date.toLocaleString('default', { month: 'short' });
                        const weekInMonth = Math.ceil(date.getDate() / 7);
                        return `${weekInMonth}${getOrdinalSuffix(weekInMonth)} week of ${month}`;
                    } else {
                        const [year, month] = item.month.split('-');
                        return new Date(year, month - 1).toLocaleString('default', { month: 'short' }) + ' ' + year;
                    }
                }),
                datasets: [{
                    label: 'Total Sales',
                    data: data.map(item => item.total_sales),
                    borderColor: '#d4b895',
                    backgroundColor: type === 'weekly' ? 'rgba(212, 184, 149, 0.1)' : '#d4b895',
                    fill: type === 'weekly',
                    tension: 0.4,
                    borderRadius: type === 'monthly' ? 5 : 0,
                    pointRadius: type === 'weekly' ? 8 : 0,
                    pointHoverRadius: type === 'weekly' ? 12 : 0,
                    pointBackgroundColor: '#d4b895',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointStyle: 'circle'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
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
                            color: '#f1f1f1',
                            maxRotation: 45,
                            minRotation: 45
                        },
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)'
                        }
                    }
                }
            }
        });
    }

    // Helper function to get ordinal suffix
    function getOrdinalSuffix(n) {
        const s = ['th', 'st', 'nd', 'rd'];
        const v = n % 100;
        return s[(v - 20) % 10] || s[v] || s[0];
    }

    // Show/hide month selector based on view type
    document.getElementById('weeklyBtn').addEventListener('click', function() {
        this.classList.add('active');
        document.getElementById('monthlyBtn').classList.remove('active');
        document.getElementById('monthSelector').classList.add('show');
        createSalesChart(weeklyData, 'weekly');
    });

    document.getElementById('monthlyBtn').addEventListener('click', function() {
        this.classList.add('active');
        document.getElementById('weeklyBtn').classList.remove('active');
        document.getElementById('monthSelector').classList.remove('show');
        createSalesChart(monthlyData, 'monthly');
    });

    // Function to update chart when month is changed
    function updateChart() {
        const selectedMonth = document.getElementById('monthSelect').value;
        window.location.href = `?month=${selectedMonth}`;
    }

    // Initialize with weekly view and show month selector
    document.getElementById('monthSelector').classList.add('show');
    createSalesChart(weeklyData, 'weekly');

    // Add this function at the beginning of your script section
    function generateRandomColors(count) {
        const colors = [];
        for (let i = 0; i < count; i++) {
            // Generate random RGB values
            const r = Math.floor(Math.random() * 200) + 55; // 55-255 to avoid too dark colors
            const g = Math.floor(Math.random() * 200) + 55;
            const b = Math.floor(Math.random() * 200) + 55;
            colors.push(`rgb(${r}, ${g}, ${b})`);
        }
        return colors;
    }

    // Update the packages chart initialization
    const packagesData = <?php echo json_encode($top_packages); ?>;
    
    // Calculate total sales from all packages
    const totalSales = packagesData.reduce((sum, item) => sum + parseFloat(item.total_sales), 0);
    
    new Chart(document.getElementById('packagesChart'), {
        type: 'pie',
        data: {
            labels: packagesData.map(item => item.package_name),
            datasets: [{
                data: packagesData.map(item => item.total_sales),
                backgroundColor: generateRandomColors(packagesData.length),
                borderWidth: 2,
                borderColor: '#2e2e2e'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        color: '#f1f1f1',
                        font: {
                            size: 12,
                            weight: 'bold'
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = parseFloat(context.raw) || 0;
                            const percentage = ((value / totalSales) * 100).toFixed(1);
                            return `${label}: ₱${value.toLocaleString()} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });

    // Update the toggle function
    function toggleSection(sectionId) {
        const section = document.getElementById(sectionId);
        const container = document.getElementById(sectionId + 'Container');
        const button = container.querySelector('.toggle-section-btn');
        const controls = container.querySelector('.chart-controls');
        
        section.classList.toggle('hidden');
        container.classList.toggle('minimized');
        button.classList.toggle('rotated');
        
        // Hide/show controls for Sales Overview section
        if (sectionId === 'salesOverview' && controls) {
            controls.style.display = section.classList.contains('hidden') ? 'none' : 'flex';
        }
        
        // If showing the section, update the chart
        if (!section.classList.contains('hidden')) {
            if (sectionId === 'salesOverview') {
                createSalesChart(weeklyData, 'weekly');
            } else if (sectionId === 'topPackages') {
                // Reinitialize the packages chart
                new Chart(document.getElementById('packagesChart'), {
                    type: 'pie',
                    data: {
                        labels: packagesData.map(item => item.package_name),
                        datasets: [{
                            data: packagesData.map(item => item.total_sales),
                            backgroundColor: generateRandomColors(packagesData.length),
                            borderWidth: 2,
                            borderColor: '#2e2e2e'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: {
                                    color: '#f1f1f1',
                                    font: {
                                        size: 12,
                                        weight: 'bold'
                                    }
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = parseFloat(context.raw) || 0;
                                        const percentage = ((value / totalSales) * 100).toFixed(1);
                                        return `${label}: ₱${value.toLocaleString()} (${percentage}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            }
        }
    }
</script>
</body>
</html>

<?php $conn->close(); ?>
