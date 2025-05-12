<?php
session_start();
include('connection.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    $is_admin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    
    if (!empty($message)) {
        if ($is_admin) {
            // Admin sending message to a specific user
            if (!isset($_POST['user_id'])) {
                echo json_encode(['success' => false, 'error' => 'User ID required for admin messages']);
                exit();
            }
            $user_id = (int)$_POST['user_id'];
            $sender_type = 'admin';
        } else {
            // User sending message
            $user_id = $_SESSION['user_id'];
            $sender_type = 'user';
        }
        
        $stmt = $conn->prepare("INSERT INTO chat_messages (user_id, message, sender_type, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iss", $user_id, $message, $sender_type);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message_id' => $conn->insert_id
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to send message']);
        }
        
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'Message cannot be empty']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}

$conn->close();
?> 