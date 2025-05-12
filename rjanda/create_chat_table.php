<?php
include('connection.php');

// Create messages table
$sql = "CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    is_admin BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES registertb(userid)
)";

if ($conn->query($sql) === TRUE) {
    echo "Messages table created successfully";
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
?> 