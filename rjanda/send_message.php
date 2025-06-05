<?php
session_start();
include('connection.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

// Get message data
$message = isset($_POST['message']) ? trim($_POST['message']) : '';
$sender_type = isset($_POST['sender_type']) ? $_POST['sender_type'] : '';
$user_id = $_SESSION['user_id'];

// Validate message
if (empty($message)) {
    echo json_encode(['success' => false, 'error' => 'Message cannot be empty']);
    exit();
}

// Validate sender type
if (!in_array($sender_type, ['user', 'admin'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid sender type']);
    exit();
}

// If admin is sending message, get the target user_id from POST
if ($sender_type === 'admin' && isset($_POST['user_id'])) {
    $target_user_id = (int)$_POST['user_id'];
} else {
    $target_user_id = $user_id;
}

// Insert message into database
$stmt = $conn->prepare("
    INSERT INTO chat_messages (user_id, message, sender_type, created_at) 
    VALUES (?, ?, ?, NOW())
");
$stmt->bind_param("iss", $target_user_id, $message, $sender_type);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message_id' => $stmt->insert_id
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Failed to send message'
    ]);
}

$stmt->close();
$conn->close();
?> 