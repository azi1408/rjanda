<?php
session_start();
include('connection.php');
$user_name = "";

// Check if the user is logged in and fetch their name
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT name FROM registertb WHERE userid = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($name);
    if ($stmt->fetch()) {
        $user_name = $name;
    }
    $stmt->close();
}

// Fetch reviews with user information
$stmt = $conn->prepare("
    SELECT o.id, o.reviews, o.rating, o.created_at, r.name as customer_name 
    FROM orders o 
    JOIN registertb r ON o.user_id = r.userid 
    WHERE o.reviews IS NOT NULL 
    ORDER BY o.created_at DESC
");

if ($stmt === false) {
    die('Error preparing query: ' . $conn->error);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Reviews - RJ & A Catering Services</title>
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
        
        nav { 
            background-color: rgba(0, 0, 0, 0.6);
            display: flex; 
            justify-content: space-between;
            padding: 1rem 2rem;
            align-items: center;
            position: relative;
            z-index: 1000;
        }
        
        nav a { 
            color: white; 
            text-decoration: none; 
            margin-left: 2rem;
            font-size: 1rem;
            transition: color 0.3s ease;
        }
        
        nav a:hover { 
            color: #fff; 
        }

        .nav-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo-img { 
            width: 60px; 
            height: 60px; 
            border-radius: 50%; 
            object-fit: cover;
            border: 2px solid white;
            box-shadow: 0 0 5px rgba(0,0,0,0.2);
        }

        .greeting {
            margin-left: 15px;
            font-size: 1.1rem;
            color: #f0e6d2;
        }

        .nav-links {
            display: flex;
            align-items: center;
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

        .customer-name {
            font-size: 1.2rem;
            font-weight: bold;
            color: #d4b895;
        }

        .review-date {
            color: #888;
            font-size: 0.9em;
        }

        .star-rating {
            color: #ffd700;
            font-size: 1.2em;
            margin: 10px 0;
            letter-spacing: 2px;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
        }

        .review-text {
            color: #f1f1f1;
            line-height: 1.6;
            font-style: italic;
            margin-top: 10px;
        }

        .no-reviews {
            text-align: center;
            color: #888;
            font-size: 1.2em;
            margin-top: 50px;
            background-color: rgba(68, 68, 68, 0.9);
            padding: 20px;
            border-radius: 10px;
            backdrop-filter: blur(5px);
        }
    </style>
</head>
<body>
    <div class="background-slider"></div>

    <nav>
        <div class="nav-left">
            <img src="logo.jfif" alt="Logo" class="logo-img">
            <span>RJ & A Catering Services</span>
            <?php if (!empty($user_name)) {
                echo "<span class='greeting'>Hello, <strong>$user_name</strong>!</span>";
            } ?>
        </div>
        <div class="nav-links">
            <a href="home.php">Home</a>
            <a href="account.php">Account Settings</a>
            <a href="orders.php">Packages</a>
            <a href="show_reviews.php">Reviews</a>
            <a href="chat.php">Chat with Admin</a>
            <a href="logout.php">Log Out</a>
        </div>
    </nav>

    <div class="container">
        <h2>📝 Customer Reviews</h2>
        
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="review-card">
                    <div class="review-header">
                        <span class="customer-name"><?= htmlspecialchars($row['customer_name']) ?></span>
                        <span class="review-date"><?= date('F j, Y', strtotime($row['created_at'])) ?></span>
                    </div>
                    <div class="star-rating">
                        <?php
                        $rating = $row['rating'];
                        for ($i = 1; $i <= 5; $i++) {
                            if ($i <= $rating) {
                                echo "★";
                            } else {
                                echo "☆";
                            }
                        }
                        ?>
                    </div>
                    <div class="review-text">
                        <?= nl2br(htmlspecialchars($row['reviews'])) ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-reviews">
                <p>No reviews yet.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
