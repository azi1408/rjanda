<?php
session_start();
include('connection.php');

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['error' => 'Not authorized']);
    exit();
}

// Get unread message counts for each user
$stmt = $conn->prepare("
    SELECT user_id, COUNT(*) as unread_count 
    FROM chat_messages 
    WHERE sender_type = 'user' 
    AND is_read = FALSE 
    GROUP BY user_id
");

$stmt->execute();
$result = $stmt->get_result();

$unreadCounts = [];
while ($row = $result->fetch_assoc()) {
    $unreadCounts[$row['user_id']] = (int)$row['unread_count'];
}

echo json_encode($unreadCounts);

$stmt->close();
$conn->close();
?> 