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
    echo "<script>window.location.href = 'index.php';</script>";
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

// Fetch all reviews with user and order information
$reviews_query = "SELECT o.id as order_id, o.customer_name, o.reviews, o.rating, o.created_at, rt.name as user_name 
                 FROM orders o 
                 JOIN registertb rt ON o.user_id = rt.userid 
                 WHERE o.reviews IS NOT NULL AND o.reviews != ''
                 ORDER BY o.created_at DESC";
$reviews_result = $conn->query($reviews_query);

if (!$reviews_result) {
    die("Failed to fetch reviews: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Reviews Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display&family=Open+Sans&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body, html { height: 100%; font-family: 'Open Sans', sans-serif; }
        body { 
            background: linear-gradient(to right, #2f2f2f, #e0d6c3); 
            color: #fff;
            overflow-x: hidden;
        }

        .background-slider {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            animation: slide 30s infinite;
            background-size: cover;
            background-position: center;
        }

        @keyframes slide {
            0%, 100% { background-image: url('bg-1.jpg'); }
            33% { background-image: url('bg-2.jpg'); }
            66% { background-image: url('bg-3.jpg'); }
        }

        .navbar {
            background-color: rgba(0, 0, 0, 0.6);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 2rem;
            position: relative;
            z-index: 1000;
        }

        .navbar-left {
            display: flex;
            align-items: center;
            gap: 15px;
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
            margin: 20px auto;
            padding: 20px;
            max-width: 1000px;
            position: relative;
            z-index: 1;
        }

        h2 {
            color: #d4b895;
            margin-bottom: 20px;
            font-family: 'Playfair Display', serif;
            text-align: center;
            font-size: 2em;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }

        .review-card {
            background-color: rgba(68, 68, 68, 0.9);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s ease;
            backdrop-filter: blur(5px);
        }

        .review-card:hover {
            transform: translateY(-5px);
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .reviewer-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .reviewer-name {
            font-size: 1.2rem;
            font-weight: bold;
            color: #d4b895;
        }

        .review-date {
            color: #888;
            font-size: 0.9em;
        }

        .review-rating {
            color: #ffd700;
            font-size: 1.2em;
            margin: 10px 0;
            letter-spacing: 2px;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
        }

        .review-content {
            color: #f1f1f1;
            line-height: 1.6;
            font-style: italic;
            margin-top: 10px;
        }

        .delete-btn {
            background-color: #f44336;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9em;
            transition: background-color 0.3s ease;
        }

        .delete-btn:hover {
            background-color: #d32f2f;
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
            background-color: rgba(34, 34, 34, 0.9);
            border-radius: 8px;
            padding: 8px 0;
            box-shadow: 0px 4px 8px rgba(0,0,0,0.5);
            z-index: 1000;
            backdrop-filter: blur(5px);
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
            transition: color 0.3s ease;
        }

        .nav-links a:hover {
            color: #d4b895;
        }

        @media screen and (max-width: 768px) {
            .container {
                padding: 10px;
            }

            .review-card {
                padding: 15px;
            }

            .review-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="background-slider"></div>

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
        <h2>📝 Customer Reviews</h2>

        <?php if ($reviews_result->num_rows > 0): ?>
            <?php while ($review = $reviews_result->fetch_assoc()): ?>
                <div class="review-card">
                    <div class="review-header">
                        <div class="reviewer-info">
                            <span class="reviewer-name"><?php echo htmlspecialchars($review['customer_name']); ?></span>
                            <span class="review-date"><?php echo date('M d, Y H:i', strtotime($review['created_at'])); ?></span>
                        </div>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="order_id" value="<?php echo $review['order_id']; ?>">
                            <button type="submit" name="delete_review" class="delete-btn" onclick="return confirm('Are you sure you want to delete this review?')">Delete</button>
                        </form>
                    </div>
                    <div class="review-rating">
                        <?php for ($i = 0; $i < $review['rating']; $i++): ?>
                            ★
                        <?php endfor; ?>
                        <?php for ($i = $review['rating']; $i < 5; $i++): ?>
                            ☆
                        <?php endfor; ?>
                    </div>
                    <div class="review-content">
                        <?php echo nl2br(htmlspecialchars($review['reviews'])); ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No reviews found.</p>
        <?php endif; ?>
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
    </script>
</body>
</html>

<?php
// Handle review deletion
if (isset($_POST['delete_review'])) {
    $order_id = $_POST['order_id'];
    $update_query = "UPDATE orders SET reviews = NULL, rating = NULL WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("i", $order_id);
    
    if ($stmt->execute()) {
        echo "<script>alert('Review deleted successfully.'); window.location.href = 'admin_reviews.php';</script>";
    } else {
        echo "<script>alert('Failed to delete review.'); window.location.href = 'admin_reviews.php';</script>";
    }
    $stmt->close();
}

$conn->close();
?> 