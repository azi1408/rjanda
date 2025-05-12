<?php
session_start();
include('connection.php');

// Check if user or admin is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

$last_id = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;
$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;

// If admin is requesting messages for a specific user
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin' && $user_id !== null) {
    $stmt = $conn->prepare("
        SELECT m.id, m.message, m.sender_type, m.created_at, r.name as user_name 
        FROM chat_messages m 
        LEFT JOIN registertb r ON m.user_id = r.userid 
        WHERE m.id > ? AND m.user_id = ?
        ORDER BY m.created_at ASC
    ");
    $stmt->bind_param("ii", $last_id, $user_id);
} else {
    // User requesting their own messages
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("
        SELECT m.id, m.message, m.sender_type, m.created_at, r.name as user_name 
        FROM chat_messages m 
        LEFT JOIN registertb r ON m.user_id = r.userid 
        WHERE m.id > ? AND m.user_id = ?
        ORDER BY m.created_at ASC
    ");
    $stmt->bind_param("ii", $last_id, $user_id);
}

$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = [
        'id' => $row['id'],
        'message' => htmlspecialchars($row['message']),
        'is_admin' => $row['sender_type'] === 'admin',
        'created_at' => $row['created_at'],
        'user_name' => $row['user_name']
    ];
}

echo json_encode(['success' => true, 'messages' => $messages]);

$stmt->close();
$conn->close();
?> 