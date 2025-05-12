<?php
session_start();
include('connection.php');

// Check if admin is logged in
$admin_online = isset($_SESSION['admin_id']);

echo json_encode(['online' => $admin_online]);
?> 