<?php
session_start();
include('connection.php');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

// Check if image was uploaded
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode([
        'success' => false, 
        'error' => 'No image uploaded or upload error',
        'debug' => isset($_FILES['image']) ? $_FILES['image']['error'] : 'No file'
    ]);
    exit();
}

// Validate file type
$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$file_type = $_FILES['image']['type'];
if (!in_array($file_type, $allowed_types)) {
    echo json_encode([
        'success' => false, 
        'error' => 'Invalid file type. Only JPG, PNG, GIF, and WEBP images are allowed.',
        'debug' => 'File type: ' . $file_type
    ]);
    exit();
}

// Validate file size (5MB max)
$max_size = 5 * 1024 * 1024; // 5MB in bytes
if ($_FILES['image']['size'] > $max_size) {
    echo json_encode([
        'success' => false, 
        'error' => 'File too large. Maximum size is 5MB.',
        'debug' => 'File size: ' . $_FILES['image']['size']
    ]);
    exit();
}

// Create upload directory if it doesn't exist
$upload_dir = 'chat_images';
if (!file_exists($upload_dir)) {
    if (!mkdir($upload_dir, 0777, true)) {
        echo json_encode([
            'success' => false, 
            'error' => 'Failed to create upload directory',
            'debug' => 'Directory: ' . $upload_dir
        ]);
        exit();
    }
}

// Generate unique filename
$file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
$filename = uniqid() . '_' . time() . '.' . $file_extension;
$filepath = $upload_dir . '/' . $filename;

// Move uploaded file
if (!move_uploaded_file($_FILES['image']['tmp_name'], $filepath)) {
    echo json_encode([
        'success' => false, 
        'error' => 'Failed to save image',
        'debug' => 'Upload error: ' . error_get_last()['message']
    ]);
    exit();
}

// Get sender type and user ID
$sender_type = isset($_POST['sender_type']) ? $_POST['sender_type'] : 'user';
$user_id = $_SESSION['user_id'];

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

if (!$stmt) {
    echo json_encode([
        'success' => false, 
        'error' => 'Database error',
        'debug' => 'Prepare error: ' . $conn->error
    ]);
    exit();
}

$stmt->bind_param("iss", $target_user_id, $filename, $sender_type);

if (!$stmt->execute()) {
    echo json_encode([
        'success' => false, 
        'error' => 'Failed to save message',
        'debug' => 'Execute error: ' . $stmt->error
    ]);
    exit();
}

echo json_encode([
    'success' => true,
    'message_id' => $stmt->insert_id,
    'filename' => $filename
]);

$stmt->close();
$conn->close();
?> 