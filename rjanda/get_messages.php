<?php
session_start();
include('connection.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode([]);
    exit();
}

$user_id = $_SESSION['user_id'];
$is_admin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';

// If admin is requesting messages, get messages for a specific user
if ($is_admin && isset($_GET['user_id'])) {
    $target_user_id = $_GET['user_id'];
    $query = "SELECT id, message, sender_type, created_at 
              FROM chat_messages 
              WHERE user_id = ? 
              ORDER BY created_at ASC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $target_user_id);
} else {
    // If regular user is requesting messages, get their own messages
    $query = "SELECT id, message, sender_type, created_at 
              FROM chat_messages 
              WHERE user_id = ? 
              ORDER BY created_at ASC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
}

$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

$stmt->close();

// Return messages
header('Content-Type: application/json');
echo json_encode($messages);
?> 