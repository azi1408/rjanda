<?php
session_start();
include('connection.php');

// Check if admin is logged in
$is_admin = isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';

// Return admin status
header('Content-Type: application/json');
echo json_encode(['online' => $is_admin]);
?> 