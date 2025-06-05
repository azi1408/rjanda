<?php
session_start();
include('connection.php');

// Set header to return JSON
header('Content-Type: application/json');

// Function to send JSON response
function sendJsonResponse($success, $message) {
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    sendJsonResponse(false, 'Please log in to submit an order.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(false, 'Invalid request method');
}

try {
    // Validate required fields
    $required_fields = ['package_id', 'customer_name', 'address', 'event_type', 'guest_count', 'lat', 'lng', 'order_date', 'total_price'];
    $missing_fields = [];
    
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            $missing_fields[] = $field;
        }
    }
    
    if (!empty($missing_fields)) {
        sendJsonResponse(false, 'Missing required fields: ' . implode(', ', $missing_fields));
    }

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

    // Insert into orders table
    $stmt = $conn->prepare("INSERT INTO orders (package_id, customer_name, address, event_type, guest_count, selected_dishes, selected_desserts, lat, lng, order_date, user_id, status, payment_method, total_price) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending payment', ?, ?)");
    
    if (!$stmt) {
        sendJsonResponse(false, 'Database error: ' . $conn->error);
    }

    $payment_method = ''; // Empty payment method initially
    
    $stmt->bind_param("isssisssssssi", 
        $package_id,
        $customer_name,
        $address,
        $event_type,
        $guest_count,
        $selected_dishes,
        $selected_desserts,
        $lat,
        $lng,
        $order_date,
        $user_id,
        $payment_method,
        $total_price
    );

    if (!$stmt->execute()) {
        sendJsonResponse(false, 'Error executing query: ' . $stmt->error);
    }

    $stmt->close();
    $conn->close();

    sendJsonResponse(true, 'Order submitted successfully');

} catch (Exception $e) {
    sendJsonResponse(false, 'An error occurred: ' . $e->getMessage());
}
?>
