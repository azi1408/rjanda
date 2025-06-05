<?php
session_start();
include('connection.php');

// Check if user is logged in and is a moderator
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('You need to log in first.'); window.location.href = 'index.php';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = "";

// Check if user is admin or moderator
$query = "SELECT name, role FROM registertb WHERE userid = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($name, $role);
$stmt->fetch();
$stmt->close();

// Allow only admin and moderator roles
if ($role !== 'admin' && $role !== 'moderator') {
    header("Location: forbidden.php");
    exit();
}

// Fetch reviews for completed orders
$sql = "SELECT o.id, o.order_date, o.customer_name, o.event_type, o.selected_dishes, o.selected_desserts, o.status, o.reviews, o.rating 
        FROM orders o 
        WHERE o.status = 'done' AND o.reviews IS NOT NULL AND o.reviews != '' 
        ORDER BY o.order_date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Moderator - User Reviews</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #2e2e2e;
            color: #f1f1f1;
            margin: 0;
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
        .hamburger {
            display: none;
            cursor: pointer;
            padding: 10px;
        }
        .hamburger-line {
            width: 25px;
            height: 3px;
            background-color: beige;
            margin: 5px 0;
            transition: all 0.3s ease;
        }
        .nav-links {
            display: flex;
            gap: 20px;
        }
        .nav-links a {
            color: beige;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        .nav-links a:hover {
            color: #d4b895;
        }
        .container {
            max-width: 900px;
            margin: 40px auto;
            background-color: #3e3e3e;
            padding: 30px;
            border-radius: 12px;
        }
        h2 {
            color: #d4b895;
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
        .review-box {
            background: #232323;
            color: #f1f1f1;
            border-radius: 8px;
            padding: 12px 16px;
            margin: 0;
            font-size: 1.05em;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .star-rating {
            color: #ffd700;
            font-size: 1.2em;
            margin: 5px 0;
        }
        @media screen and (max-width: 768px) {
            .hamburger {
                display: block;
            }
            .nav-links {
                display: none;
                position: absolute;
                top: 100%;
                right: 0;
                background-color: #111;
                width: 200px;
                padding: 20px;
                flex-direction: column;
                gap: 15px;
                box-shadow: 0 4px 10px rgba(0,0,0,0.3);
                z-index: 1000;
            }
            .nav-links.active {
                display: flex;
            }
            .hamburger.active .hamburger-line:nth-child(1) {
                transform: rotate(-45deg) translate(-5px, 6px);
            }
            .hamburger.active .hamburger-line:nth-child(2) {
                opacity: 0;
            }
            .hamburger.active .hamburger-line:nth-child(3) {
                transform: rotate(45deg) translate(-5px, -6px);
            }
        }
    </style>
</head>
<body>
<nav class="navbar">
    <div class="navbar-left">
        <img src="logo.jfif" alt="Logo" class="logo-img">
        <span class="greeting">Hello, <strong><?= htmlspecialchars($name) ?></strong>!</span>
    </div>
    <div class="hamburger" onclick="toggleMenu()">
        <div class="hamburger-line"></div>
        <div class="hamburger-line"></div>
        <div class="hamburger-line"></div>
    </div>
    <div class="nav-links">
        <a href="mod_packages.php">Manage Packages</a>
        <a href="mod_add_packages.php">Add Package</a>
        <a href="mod_review_page.php">User Reviews</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<div class="container">
    <h2>📝 User Reviews for Completed Orders</h2>
    <table>
        <tr>
            <th>Order ID</th>
            <th>Date</th>
            <th>Customer</th>
            <th>Event Type</th>
            <th>Dishes</th>
            <th>Desserts</th>
            <th>Rating</th>
            <th>Review</th>
        </tr>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['id']) ?></td>
                    <td><?= htmlspecialchars($row['order_date']) ?></td>
                    <td><?= htmlspecialchars($row['customer_name']) ?></td>
                    <td><?= htmlspecialchars($row['event_type']) ?></td>
                    <td><?= nl2br(htmlspecialchars($row['selected_dishes'])) ?></td>
                    <td><?= nl2br(htmlspecialchars($row['selected_desserts'])) ?></td>
                    <td>
                        <div class="star-rating">
                            <?php for ($i = 0; $i < $row['rating']; $i++) echo "★"; ?>
                            <?php for ($i = $row['rating']; $i < 5; $i++) echo "☆"; ?>
                        </div>
                    </td>
                    <td><div class="review-box">"<?= nl2br(htmlspecialchars($row['reviews'])) ?>"</div></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="8" style="text-align:center; color:#ccc;">No reviews found.</td></tr>
        <?php endif; ?>
    </table>
</div>

<script>
function toggleMenu() {
    const navLinks = document.querySelector('.nav-links');
    const hamburger = document.querySelector('.hamburger');
    navLinks.classList.toggle('active');
    hamburger.classList.toggle('active');
}

// Close menu when clicking outside
document.addEventListener('click', function(event) {
    const navLinks = document.querySelector('.nav-links');
    const hamburger = document.querySelector('.hamburger');
    if (!event.target.closest('.nav-links') && !event.target.closest('.hamburger')) {
        navLinks.classList.remove('active');
        hamburger.classList.remove('active');
    }
});
</script>
</body>
</html>

<?php $conn->close(); ?> 