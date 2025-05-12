<?php
session_start();
include('connection.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('You need to log in first.');</script>";
    echo "<script>window.location.href = 'index.php';</script>";
    exit();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $_POST['order_id'];
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];
    $user_id = $_SESSION['user_id'];

    // First, verify that the order belongs to the user
    $verify_stmt = $conn->prepare("SELECT id FROM orders WHERE id = ? AND user_id = ?");
    if ($verify_stmt === false) {
        die('Error preparing verification query: ' . $conn->error);
    }
    
    $verify_stmt->bind_param("ii", $order_id, $user_id);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();

    if ($verify_result->num_rows === 0) {
        echo "<script>alert('Invalid order or unauthorized access.');</script>";
        echo "<script>window.location.href = 'orders.php';</script>";
        exit();
    }

    // Update the review in the orders table
    $update_stmt = $conn->prepare("UPDATE orders SET reviews = ?, rating = ? WHERE id = ?");
    if ($update_stmt === false) {
        die('Error preparing update query: ' . $conn->error);
    }
    
    $update_stmt->bind_param("sii", $comment, $rating, $order_id);
    if ($update_stmt->execute()) {
        echo "<script>alert('Review submitted successfully!');</script>";
    } else {
        echo "<script>alert('Error submitting review: " . $update_stmt->error . "');</script>";
    }

    // Redirect back to orders page
    echo "<script>window.location.href = 'orders.php';</script>";
    exit();
} else {
    // If not POST request, redirect to orders page
    echo "<script>window.location.href = 'orders.php';</script>";
    exit();
}
?>
