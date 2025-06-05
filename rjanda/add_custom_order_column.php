<?php
include('connection.php');

// Add is_custom column to orders table
$sql = "ALTER TABLE orders ADD COLUMN is_custom BOOLEAN DEFAULT FALSE";

if ($conn->query($sql) === TRUE) {
    echo "Successfully added is_custom column to orders table";
} else {
    echo "Error adding column: " . $conn->error;
}

$conn->close();
?> 