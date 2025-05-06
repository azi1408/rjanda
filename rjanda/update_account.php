<?php
session_start();
include('connection.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get form data
$name = trim($_POST['name']);
$email = trim($_POST['email']);
$phone = trim($_POST['phone']);
$new_password = $_POST['new_password'];
$confirm_password = $_POST['confirm_password'];

// Basic validation
if (empty($name) || empty($email) || empty($phone)) {
    echo "Please fill in all required fields.";
    exit();
}

if (!empty($new_password) || !empty($confirm_password)) {
    if ($new_password !== $confirm_password) {
        echo "Passwords do not match.";
        exit();
    }

    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $query = "UPDATE registertb SET name = ?, username = ?, contact = ?, password = ? WHERE userid = ?";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("ssssi", $name, $email, $phone, $hashed_password, $user_id);
} else {
    $query = "UPDATE registertb SET name = ?, username = ?, contact = ? WHERE userid = ?";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("sssi", $name, $email, $phone, $user_id);
}

if ($stmt->execute()) {
    echo "<script>alert('Account updated successfully.'); window.location.href='home.php';</script>";
} else {
    echo "Error updating account: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
