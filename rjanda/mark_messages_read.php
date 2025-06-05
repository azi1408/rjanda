<?php
session_start();
include('connection.php');

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

// Check if user_id is provided
if (!isset($_POST['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'User ID not provided']);
    exit();
}

$user_id = $_POST['user_id'];

// Update messages as read
$query = "UPDATE chat_messages 
          SET is_read = 1 
          WHERE user_id = ? AND sender_type = 'user' AND is_read = 0";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$success = $stmt->execute();
$stmt->close();

// Return response
header('Content-Type: application/json');
echo json_encode(['success' => $success]);
?> 