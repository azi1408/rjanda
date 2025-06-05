<?php
session_start();
include('connection.php');

// Check if user is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_id'])) {
    $review_id = $_POST['review_id'];
    
    // Prepare and execute the delete query
    $query = "DELETE FROM reviews WHERE id = ?";
    $stmt = $conn->prepare($query);
    
    if ($stmt) {
        $stmt->bind_param("i", $review_id);
        
        if ($stmt->execute()) {
            // Successfully deleted
            header('Location: admin_reviews.php?message=deleted');
        } else {
            // Error in deletion
            header('Location: admin_reviews.php?error=delete_failed');
        }
        
        $stmt->close();
    } else {
        // Error in preparing statement
        header('Location: admin_reviews.php?error=prepare_failed');
    }
} else {
    // Invalid request
    header('Location: admin_reviews.php?error=invalid_request');
}

$conn->close();
?> 