<?php
include('connection.php');

// Create reviews table
$sql = "CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES registertb(userid),
    FOREIGN KEY (order_id) REFERENCES orders(id)
)";

if ($conn->query($sql) === TRUE) {
    echo "Reviews table created successfully";
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
?> 