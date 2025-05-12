<?php
session_start();
include('connection.php');

// Check if user is logged in and is a moderator
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'moderator') {
    header('Location: index.php');
    exit;
}

// Fetch all reviews with user and order information
$sql = "SELECT r.*, rt.name as user_name, o.package_name, o.event_date 
        FROM reviews r 
        JOIN registertb rt ON r.user_id = rt.userid 
        JOIN orders o ON r.order_id = o.id 
        ORDER BY r.created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Reviews - Moderator View</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(to bottom right, #323232, #d4b895);
            color: #fff;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        h1 {
            color: #d4b895;
            text-align: center;
            margin-bottom: 30px;
        }

        .reviews-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            padding: 20px;
        }

        .review-card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 20px;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .reviewer-name {
            font-weight: bold;
            color: #d4b895;
        }

        .review-date {
            color: #888;
            font-size: 0.9em;
        }

        .rating {
            color: #ffd700;
            font-size: 1.2em;
            margin: 10px 0;
        }

        .review-content {
            color: #fff;
            line-height: 1.5;
            margin: 10px 0;
        }

        .package-info {
            color: #d4b895;
            font-size: 0.9em;
            margin-top: 10px;
        }

        .event-date {
            color: #888;
            font-size: 0.9em;
        }

        .stats {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            text-align: center;
        }

        .stats h2 {
            color: #d4b895;
            margin-bottom: 15px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .stat-item {
            background: rgba(255, 255, 255, 0.05);
            padding: 15px;
            border-radius: 8px;
        }

        .stat-value {
            font-size: 2em;
            color: #d4b895;
            margin: 10px 0;
        }

        .stat-label {
            color: #888;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Customer Reviews</h1>

        <?php
        // Calculate review statistics
        $total_reviews = $result->num_rows;
        $avg_rating = 0;
        $rating_counts = array_fill(1, 5, 0);
        
        if ($total_reviews > 0) {
            $result->data_seek(0);
            $total_rating = 0;
            while ($row = $result->fetch_assoc()) {
                $total_rating += $row['rating'];
                $rating_counts[$row['rating']]++;
            }
            $avg_rating = round($total_rating / $total_reviews, 1);
        }
        ?>

        <div class="stats">
            <h2>Review Statistics</h2>
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-value"><?php echo $total_reviews; ?></div>
                    <div class="stat-label">Total Reviews</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?php echo $avg_rating; ?></div>
                    <div class="stat-label">Average Rating</div>
                </div>
                <?php for ($i = 5; $i >= 1; $i--): ?>
                <div class="stat-item">
                    <div class="stat-value"><?php echo $rating_counts[$i]; ?></div>
                    <div class="stat-label"><?php echo $i; ?> Star Reviews</div>
                </div>
                <?php endfor; ?>
            </div>
        </div>

        <div class="reviews-grid">
            <?php
            $result->data_seek(0);
            while ($row = $result->fetch_assoc()):
            ?>
            <div class="review-card">
                <div class="review-header">
                    <span class="reviewer-name"><?php echo htmlspecialchars($row['user_name']); ?></span>
                    <span class="review-date"><?php echo date('M j, Y', strtotime($row['created_at'])); ?></span>
                </div>
                <div class="rating">
                    <?php for ($i = 0; $i < $row['rating']; $i++) echo "★"; ?>
                    <?php for ($i = $row['rating']; $i < 5; $i++) echo "☆"; ?>
                </div>
                <div class="review-content">
                    <?php echo htmlspecialchars($row['comment']); ?>
                </div>
                <div class="package-info">
                    Package: <?php echo htmlspecialchars($row['package_name']); ?>
                </div>
                <div class="event-date">
                    Event Date: <?php echo date('F j, Y', strtotime($row['event_date'])); ?>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html> 