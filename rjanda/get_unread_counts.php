<?php
session_start();
include('connection.php');

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode([]);
    exit();
}

// Get unread message counts for all users
$query = "SELECT user_id, COUNT(*) as unread_count 
          FROM chat_messages 
          WHERE sender_type = 'user' AND is_read = 0 
          GROUP BY user_id";

$result = mysqli_query($conn, $query);
$unread_counts = [];

while ($row = mysqli_fetch_assoc($result)) {
    $unread_counts[] = [
        'user_id' => $row['user_id'],
        'unread_count' => (int)$row['unread_count']
    ];
}

// Return unread counts
header('Content-Type: application/json');
echo json_encode($unread_counts);
?> 