<?php
session_start();
include('connection.php');

// Simple admin check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user name from database
$query = "SELECT name FROM registertb WHERE userid = ?";
$stmt = $conn->prepare($query);

if (!$stmt) {
    die("SQL prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user) {
    $user_name = $user['name'];
} else {
    $user_name = "Admin";
}

$stmt->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Order Management</title>
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
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .navbar ul li a:hover {
            color: #d4b895;
        }

        .chat-icon {
            font-size: 1.2em;
        }

        .container {
            max-width: 1100px;
            margin: 30px auto;
            padding: 20px;
            background-color: #3e3e3e;
            border-radius: 10px;
        }

        h2 {
            color: #d4b895;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #2a2a2a;
            border-radius: 8px;
            overflow: hidden;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #555;
        }

        th {
            background-color: #444;
            color: beige;
        }

        tr:hover {
            background-color: #383838;
        }

        .status-paid {
            color: #4caf50;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .status-pending {
            color: #ffc107;
            font-weight: bold;
        }

        form {
            margin: 0;
        }

        button {
            background-color: #d4b895;
            color: #222;
            border: none;
            padding: 8px 12px;
            font-weight: bold;
            border-radius: 6px;
            cursor: pointer;
        }

        button:hover {
            background-color: #caa97a;
        }
        .menu-toggle {
            display: none;
            font-size: 28px;
            background: none;
            border: none;
            color: beige;
            cursor: pointer;
        }

        .nav-links {
            display: flex;
            gap: 20px;
        }

        @media screen and (max-width: 768px) {
            .menu-toggle {
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

            .navbar {
                padding: 15px 20px;
            }

            .greeting {
                display: none;
            }
        }

    </style>
</head>
<body>

<nav class="navbar">
    <div class="navbar-left">
        <img src="logo.jfif" alt="Logo" class="logo-img">
        <span class="business-title">RJ & A Catering Services</span>
        <span class="greeting">Welcome, <?php echo htmlspecialchars($user_name); ?>!</span>
    </div>

    <!-- Hamburger Icon -->
    <button class="menu-toggle" onclick="toggleMenu()">☰</button>

    <!-- Nav Links -->
    <ul id="navLinks" class="nav-links">
        <li><a href="admin_home.php">Dashboard</a></li>
        <li><a href="admin_users.php">Users</a></li>
        <li><a href="admin_packages.php">Packages</a></li>
        <li><a href="admin_orders.php">Orders</a></li>
        <li><a href="admin_chat.php"><span class="chat-icon">💬</span> Chat</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</nav>


<div class="container">
    <h2>📋 Order Management</h2>
<?php
$order_query = "SELECT * FROM orders ORDER BY order_date DESC"; // Changed from catering_orders to orders
$result = $conn->query($order_query);

if (!$result) {
    die("Failed to fetch orders: " . $conn->error);
}

?>
    <table>
        <tr>
            <th>Order ID</th>
            <th>Customer Name</th>
            <th>Event Date</th>
            <th>Guests</th>
            <th>Address</th>    
            <th>Event Type</th>
            <th>Selected Dishes</th>
            <th>Selected Desserts</th>
            <th>Status</th>
            <th>Payment Method</th>
            <th>Review</th>
            <th>Action</th>
        </tr>

        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['id']) ?></td>
                <td><?= htmlspecialchars($row['customer_name']) ?></td>
                <td><?= htmlspecialchars($row['order_date']) ?></td>
                <td><?= htmlspecialchars($row['guest_count']) ?></td>
                <td><?= htmlspecialchars($row['address']) ?></td>
                <td><?= htmlspecialchars($row['event_type']) ?></td>
                <td><?= htmlspecialchars($row['selected_dishes']) ?></td>    
                <td><?= htmlspecialchars($row['selected_desserts']) ?></td>

                <!-- STATUS -->
                <td>
                    <?php if ($row['status'] === 'done'): ?>
                        <span class="status-paid">✅ Order Completed</span>
                    <?php elseif ($row['status'] === 'paid'): ?>
                        <span class="status-paid"><span style='color:#4caf50;font-weight:bold;'>✔ Paid</span></span>
                    <?php elseif ($row['status'] === 'pending payment' && $row['payment_method'] === 'GCash'): ?>
                        <span class="status-pending">⏳ Pending GCash Payment</span>
                    <?php elseif (!empty($row['payment_method'])): ?>
                        <span class="status-pending">⏳ Awaiting Confirmation</span>
                    <?php else: ?>
                        <span class="status-pending">Pending</span>
                    <?php endif; ?>
                </td>

                <!-- PAYMENT METHOD -->
                <td><?= htmlspecialchars($row['payment_method']) ?: 'N/A' ?></td>
                <td>
                    <?php if (!empty($row['review'])): ?>
                        <button onclick="openReviewModal(<?= $row['id'] ?>, '<?= addslashes($row['review']) ?>')">View Review</button>
                    <?php else: ?>
                        <span style="color: #aaa;">No Review</span>
                    <?php endif; ?>
                </td>

                <!-- ACTIONS -->
                <td>
                    <?php if ($row['status'] === 'pending payment' && $row['payment_method'] === 'GCash'): ?>
                        <form method="POST" action="admin_payment.php" style="display:inline-block;">
                            <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
                            <button type="submit" name="mark_paid">Verify Payment</button>
                        </form>
                    <?php elseif (empty($row['payment_method'])): ?>
                        <form method="POST" action="admin_payment.php" style="display:inline-block;">
                            <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
                            <button type="submit" name="mark_paid">Mark as Paid</button>
                        </form>
                    <?php else: ?>
                        <form method="POST" action="admin_payment.php" style="display:inline-block;">
                            <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
                            <button type="submit" name="mark_unpaid">Mark as Unpaid</button>
                        </form>

                        <?php if ($row['status'] !== 'done'): ?>
                            <form method="POST" action="admin_payment.php" style="display:inline-block;">
                                <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
                                <button type="submit" name="mark_done">Mark as Done</button>
                            </form>
                        <?php else: ?>
                            <form method="POST" action="admin_payment.php" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this completed order?');">
                                <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
                                <button type="submit" name="delete_order">Delete</button>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>

<script>
    function toggleMenu() {
        document.getElementById("navLinks").classList.toggle("show");
    }

    // Close menu if clicked outside
    document.addEventListener("click", function(event) {
        const menu = document.getElementById("navLinks");
        const button = document.querySelector(".menu-toggle");
        if (!menu.contains(event.target) && !button.contains(event.target)) {
            menu.classList.remove("show");
        }
    });

    function openReviewModal(orderId, reviewText) {
        document.getElementById('reviewContent').textContent = reviewText;
        document.getElementById('reviewModal').style.display = 'flex';
    }

    function closeReviewModal() {
        document.getElementById('reviewModal').style.display = 'none';
    }
</script>


<!-- Review Modal -->
<div id="reviewModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); justify-content:center; align-items:center;">
    <div style="background-color:#3e3e3e; padding:20px; border-radius:10px; width:400px; max-width:90%;">
        <h3 style="color:#d4b895;">📝 Customer Review</h3>
        <p id="reviewContent" style="color:#f1f1f1; white-space:pre-wrap;"></p>
        <div style="margin-top:15px; text-align:right;">
            <button onclick="closeReviewModal()" style="background:#d4b895; color:#000; border:none; padding:8px 12px; border-radius:6px;">Close</button>
        </div>
    </div>
</div>

</body>
</html>

<?php $conn->close(); ?>
