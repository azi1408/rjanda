<?php
session_start();
include('connection.php');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to submit an order.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $package_id = $_POST['package_id'];
    $customer_name = $_POST['customer_name'];
    $address = $_POST['address'];
    $event_type = $_POST['event_type'];
    $guest_count = $_POST['guest_count'];
    $lat = $_POST['lat'];
    $lng = $_POST['lng'];
    $order_date = $_POST['order_date'];
    $total_price = $_POST['total_price'];
    $selected_dishes = isset($_POST['selected_dishes']) ? implode(', ', $_POST['selected_dishes']) : '';
    $selected_desserts = isset($_POST['selected_desserts']) ? implode(', ', $_POST['selected_desserts']) : '';

    // Insert into catering_orders table
    $stmt = $conn->prepare("INSERT INTO catering_orders (user_id, name, event_date, guests, address, latitude, longitude, special_requests, payment_method, status, total_price, selected_dishes, selected_desserts) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?, ?)");
    
    $special_requests = $event_type; // Using event_type as special_requests
    $payment_method = 'Pending'; // Default payment method
    
    $stmt->bind_param("issdsdssdss", 
        $user_id,
        $customer_name,
        $order_date,
        $guest_count,
        $address,
        $lat,
        $lng,
        $special_requests,
        $payment_method,
        $total_price,
        $selected_dishes,
        $selected_desserts
    );

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Order submitted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error submitting order: ' . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>
