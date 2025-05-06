<?php
session_start();
// Assuming you've already connected to the database
include('connection.php');

// Get the order ID from the URL parameter
$order_id = $_GET['order_id'];
$status = $_GET['status'];

// Fetch the order details
$order_query = "SELECT * FROM catering_orders WHERE id = ?";
$stmt = $conn->prepare($order_query);
$stmt->bind_param('i', $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Confirmation</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #323232;
            color: #fff;
            margin: 0;
            padding: 0;
        }
        .container {
            text-align: center;
            padding: 50px;
        }
        .success {
            color: green;
        }
        .failure {
            color: red;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php
        if ($status == 'success') {
            echo "<h1 class='success'>Payment Successful!</h1>";
            echo "<p>Thank you for your order. Your order ID is " . $order['id'] . ".</p>";
            echo "<p>Your payment status is now: Paid</p>";
        } elseif ($status == 'failed') {
            echo "<h1 class='failure'>Payment Update Failed</h1>";
            echo "<p>We were unable to update your payment status. Please try again later.</p>";
        } else {
            echo "<h1 class='failure'>Error</h1>";
            echo "<p>There was an error processing your payment. Please check the details and try again.</p>";
        }
        ?>
    </div>
</body>
</html>
