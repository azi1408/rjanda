<?php
session_start();
include('connection.php');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

// Check if required data is provided
if (!isset($_POST['user_id']) || !isset($_POST['budget']) || !isset($_POST['event_type']) || !isset($_POST['guest_count'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false, 
        'error' => 'Missing required fields',
        'debug' => [
            'user_id' => isset($_POST['user_id']) ? $_POST['user_id'] : 'missing',
            'budget' => isset($_POST['budget']) ? $_POST['budget'] : 'missing',
            'event_type' => isset($_POST['event_type']) ? $_POST['event_type'] : 'missing',
            'guest_count' => isset($_POST['guest_count']) ? $_POST['guest_count'] : 'missing'
        ]
    ]);
    exit();
}

$user_id = $_POST['user_id'];
$budget = floatval($_POST['budget']);
$event_type = $_POST['event_type'];
$guest_count = intval($_POST['guest_count']);
$additional_notes = isset($_POST['additional_notes']) ? $_POST['additional_notes'] : '';

// Calculate price per guest based on budget
$price_per_guest = $budget / $guest_count;

try {
    // Get user's name
    $stmt = $conn->prepare("SELECT name FROM registertb WHERE userid = ?");
    if (!$stmt) {
        throw new Exception("Error preparing user query: " . $conn->error);
    }
    
    $stmt->bind_param("i", $user_id);
    if (!$stmt->execute()) {
        throw new Exception("Error executing user query: " . $stmt->error);
    }
    
    $stmt->bind_result($customer_name);
    if (!$stmt->fetch()) {
        throw new Exception("User not found with ID: " . $user_id);
    }
    $stmt->close();

    // Insert into orders table with explicit column names
    $stmt = $conn->prepare("
        INSERT INTO orders (
            customer_name, 
            event_type, 
            guest_count, 
            total_price, 
            user_id, 
            status, 
            payment_method, 
            is_custom
        ) VALUES (?, ?, ?, ?, ?, 'pending payment', '', 1)
    ");
    
    if (!$stmt) {
        throw new Exception("Error preparing order insert: " . $conn->error);
    }
    
    $stmt->bind_param("ssiii", $customer_name, $event_type, $guest_count, $budget, $user_id);
    if (!$stmt->execute()) {
        throw new Exception("Error executing order insert: " . $stmt->error);
    }
    
    $order_id = $stmt->insert_id;
    $stmt->close();
    
    // Send notification message to user
    $message = "A custom order has been created for your event:\n\n" .
               "Event Type: $event_type\n" .
               "Number of Guests: $guest_count\n" .
               "Budget: ₱" . number_format($budget, 2) . "\n" .
               "Price per Guest: ₱" . number_format($price_per_guest, 2) . "\n\n" .
               "Additional Notes: " . ($additional_notes ?: 'None') . "\n\n" .
               "Please check your orders page to view and confirm this custom order.";
    
    $stmt = $conn->prepare("INSERT INTO chat_messages (user_id, message, sender_type, created_at) VALUES (?, ?, 'admin', NOW())");
    if (!$stmt) {
        throw new Exception("Error preparing message insert: " . $conn->error);
    }
    
    $stmt->bind_param("is", $user_id, $message);
    if (!$stmt->execute()) {
        throw new Exception("Error executing message insert: " . $stmt->error);
    }
    
    echo json_encode(['success' => true, 'order_id' => $order_id]);

} catch (Exception $e) {
    error_log("Custom Order Creation Error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'error' => 'Failed to create custom order: ' . $e->getMessage(),
        'debug' => [
            'user_id' => $user_id,
            'budget' => $budget,
            'event_type' => $event_type,
            'guest_count' => $guest_count
        ]
    ]);
}

$conn->close();
?> 