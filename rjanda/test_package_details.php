<?php
session_start();
include('connection.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('You need to log in first.'); window.location.href = 'index.php';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch package details based on ID
if (isset($_GET['id'])) {
    $package_id = $_GET['id'];
    $sql = "SELECT * FROM packages WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $package_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $package = $result->fetch_assoc();
    $stmt->close();
} else {
    header("Location: view_packages.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Package Details</title>
    <style>
        /* Add the same CSS as in the view packages page */
    </style>
</head>
<body>

<nav class="navbar">
    <!-- Navbar code here (same as view packages page) -->
</nav>

<div class="container">
    <h2>🍽️ Package Details: <?= htmlspecialchars($package['package_name']) ?></h2>

    <p><strong>Price:</strong> ₱<?= number_format($package['price'], 2) ?></p>
    <p><strong>Dishes:</strong> <?= htmlspecialchars($package['dishes']) ?></p>
    <p><strong>Desserts:</strong> <?= htmlspecialchars($package['desserts']) ?></p>
    <p><strong>Description:</strong> <?= htmlspecialchars($package['description']) ?></p>
    <p><strong>Max Dishes:</strong> <?= $package['max_dishes'] ?></p>
    <p><strong>Max Desserts:</strong> <?= $package['max_desserts'] ?></p>
    <p><strong>Status:</strong> <?= ucfirst($package['status']) ?></p>

    <a href="view_packages.php" class="view-btn">Back to Packages</a>
</div>

</body>
</html>

<?php $conn->close(); ?>
