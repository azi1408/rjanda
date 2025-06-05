<?php
session_start();
include('connection.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('You need to log in first.');</script>";
    echo "<script>window.location.href = 'index.php';</script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $_POST['order_id'];
    $payment_method = $_POST['payment_method'];
    
    // Update order in database
    $stmt = $conn->prepare("UPDATE orders SET payment_method = ?, status = 'pending payment' WHERE id = ?");
    $stmt->bind_param("si", $payment_method, $order_id);
    
    if ($stmt->execute()) {
        echo "<script>
            alert('Payment method selected successfully! Please proceed with the payment and send your proof of payment through the Chat with Admin feature.');
            window.location.href = 'order_details.php';
        </script>";
    } else {
        echo "<script>alert('Error updating order: " . $stmt->error . "');</script>";
        echo "<script>window.location.href = 'order_details.php';</script>";
    }
} else {
    echo "<script>alert('Invalid request method.');</script>";
    echo "<script>window.location.href = 'order_details.php';</script>";
}
?>
