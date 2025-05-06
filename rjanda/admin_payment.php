<?php
session_start();
include('connection.php');



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the order ID from the POST request
    $order_id = $_POST['order_id'];

    if (isset($_POST['mark_paid'])) {
        // Mark the order as paid
        $stmt = $conn->prepare("UPDATE catering_orders SET payment_method = 'Paid' WHERE id = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        header("Location: admin_home.php");
        exit();
    }

    if (isset($_POST['mark_unpaid'])) {
        // Mark the order as unpaid
        $stmt = $conn->prepare("UPDATE catering_orders SET payment_method = NULL WHERE id = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        header("Location: admin_home.php");
        exit();
    }

    if (isset($_POST['mark_done'])) {
        // Mark the order as "Order Completed"
        $stmt = $conn->prepare("UPDATE catering_orders SET status = 'done' WHERE id = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        header("Location: admin_home.php");  // Redirect to orders page after the update
        exit();
    }

    if (isset($_POST['delete_order'])) {
        // Delete the completed order
        $stmt = $conn->prepare("DELETE FROM catering_orders WHERE id = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        header("Location: admin_home.php");
        exit();
    }
}
?>
