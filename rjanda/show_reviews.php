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

// Fetch reviews from the catering_orders table
$reviews_query = "SELECT c.review, c.user_id, u.name FROM catering_orders c INNER JOIN registertb u ON c.user_id = u.userid WHERE c.review IS NOT NULL";
$reviews_result = $conn->query($reviews_query);

// Check if query was successful
if (!$reviews_result) {
    die("Error executing query: " . $conn->error);
}
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
        body { background: linear-gradient(to right, #2f2f2f, #e0d6c3); color: #fff; }
        nav { background-color: rgba(0, 0, 0, 0.6); display: flex; justify-content: flex-end; padding: 1rem 2rem; }
        nav a { color: white; text-decoration: none; margin-left: 2rem; font-size: 1rem; }
        nav a:hover { color: #fff; }
        .container { margin: 20px auto; padding: 20px; max-width: 1000px; background-color: #3e3e3e; border-radius: 10px; }
        .review-box { background-color: #444; padding: 20px; border-radius: 10px; margin-bottom: 15px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3); }
        .review-box .reviewer-info { font-size: 1.2rem; font-weight: bold; color: #d4b895; margin-bottom: 10px; }
        .review-box .review-text { color: #f1f1f1; font-style: italic; }
        h2 { color: #d4b895; margin-bottom: 20px; }
        .logo-img { width: 60px; height: 60px; border-radius: 50%; object-fit: cover; border: 2px solid white; box-shadow: 0 0 5px rgba(0, 0, 0, 0.2); }
        nav span.greeting { margin-left: 15px; font-size: 1.1rem; color: #f0e6d2; }
    </style>
</head>
<body>

<nav>
    <div style="display: flex; align-items: center; gap: 15px; justify-content: flex-start; width: 100%;">
        <!-- Left Side: Logo and Greeting -->
        <img src="logo.jfif" alt="Logo" class="logo-img">
        <?php if (!empty($user_name)) {
            echo "<span class='greeting'>Hello, <strong>$user_name</strong>!</span>";
        } ?>
    </div>

    <!-- Right Side: Navigation Links -->
    <div style="display: flex; justify-content: flex-end; width: 100%;">
        <a href="home.php">Home</a>
        <a href="account.php">Account Settings</a>
        <a href="orders.php">Packages</a>
        <a href="logout.php">Log Out</a>
    </div>
</nav>

<div class="container">
    <h2>Customer Reviews</h2>

    <?php
    if ($reviews_result->num_rows > 0) {
        while ($row = $reviews_result->fetch_assoc()) {
            $review_text = htmlspecialchars($row['review']);
            $reviewer_name = htmlspecialchars($row['name']);
            echo "<div class='review-box'>
                    <div class='reviewer-info'>$reviewer_name</div>
                    <div class='review-text'>$review_text</div>
                </div>";
        }
    } else {
        echo "<p>No reviews available.</p>";
    }
    ?>
</div>

</body>
</html>

<?php
$conn->close();
?>
