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
    echo"<script>window.location.href = 'index.php'</script>
    ";
    }

$user_id = $_SESSION['user_id'];
$user_name = "";

// Prepare the SQL statement
$query = "SELECT name, role FROM registertb WHERE userid = ?";
$stmt = $conn->prepare($query);

if (!$stmt) {
    // Output error and stop script
    die("SQL prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($name, $roles);

// Check result
if ($stmt->fetch()) {
    $user_name = $name;

    // Redirect if not admin
    if ($roles !== 'admin') {
        header("Location: forbidden.php"); // Create this page with "Access Denied" message
        exit();
    }
} 

$stmt->close();

// Query orders grouped by date
$sql = "SELECT 
            DATE(created_at) as order_date,
            COUNT(*) as total_orders,
            SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) as completed_orders
        FROM catering_orders
        GROUP BY DATE(created_at)
        ORDER BY order_date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Orders Per Day</title>
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

        .business-title {
            font-size: 1.5em;
            font-weight: bold;
            color: beige;
        }

        .navbar ul {
            list-style: none;
            display: flex;
            gap: 20px;
            margin: 0;
            padding: 0;
        }

        .navbar ul li a {
            color: beige;
            text-decoration: none;
            font-weight: 500;
        }

        .navbar ul li a:hover {
            color: #d4b895;
        }

        .container {
            max-width: 1000px;
            margin: 30px auto;
            padding: 20px;
            background-color: #3e3e3e;
            border-radius: 10px;
        }
        h2 {
            color: #d4b895;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #2a2a2a;
        }
        th, td {
    padding: 12px;
    border-bottom: 1px solid #555;
    text-align: center; /* Horizontal centering */
    vertical-align: middle; /* Vertical centering */
}
        th {
            background-color: #444;
            color: beige;
        }
        tr:hover {
            background-color: #383838;
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
    <ul>
        <li><a href="admin_home.php">Orders</a></li>
        <li><a href="admin_users.php">Users</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</nav>

<div class="container">
    <h2>📊 Orders Placed Per Day</h2>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Total Orders</th>
                <th>Completed Orders</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['order_date']) ?></td>
                    <td><?= htmlspecialchars($row['total_orders']) ?></td>
                    <td><?= htmlspecialchars($row['completed_orders']) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>

<?php $conn->close(); ?>
