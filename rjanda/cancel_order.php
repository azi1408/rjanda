<?php
session_start();
include('connection.php');

if (isset($_POST['cancel_order'])) {
    $order_id = $_POST['order_id'];
    $user_id = $_SESSION['user_id'];

    // Ensure the order belongs to the logged-in user
    $stmt = $conn->prepare("DELETE FROM catering_orders WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $stmt->close();

    header("Location: orders.php"); // Redirect back to orders page
    exit;
}
?>
