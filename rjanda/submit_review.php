<?php
session_start();
include('connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['review'])) {
    $user_id = $_SESSION['user_id'];
    $order_id = intval($_POST['order_id']);
    $review = trim($_POST['review']);

    // Update the review column for the current user's order
    $stmt = $conn->prepare("UPDATE catering_orders SET review = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("sii", $review, $order_id, $user_id);

    if ($stmt->execute()) {
        echo "<script>alert('Thank you for your review!'); window.location.href='order_details.php';</script>";
    } else {
        echo "<script>alert('Error submitting review.'); window.history.back();</script>";
    }
}
?>
