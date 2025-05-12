<?php
session_start();
include('connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $_POST['order_id'];
    $payment_method = $_POST['payment_method'];

    // Set status based on payment method
    if ($payment_method === 'GCash') {
        $status = 'pending payment';
    } else {
        $status = 'Awaiting Payment Verification';
    }

    // Update the order
    $stmt = $conn->prepare("UPDATE orders SET payment_method = ?, status = ? WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("ssi", $payment_method, $status, $order_id);
        $stmt->execute();
        $stmt->close();

        if ($payment_method === 'GCash') {
            echo "
            <script>
                alert('Your GCash payment has been submitted. Please wait for admin verification.');
                window.location.href = 'orders.php';
            </script>
            ";
        } else {
            echo "
            <script>
                alert('Your payment method \"{$payment_method}\" has been submitted. Status set to: Awaiting Payment Verification.');
                window.location.href = 'orders.php';
            </script>
            ";
        }
        exit();
    } else {
        echo "Error: Failed to update payment details.";
    }
}
?>
