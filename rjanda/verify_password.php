<?php
session_start();
include('connection.php');

if (!isset($_SESSION['user_id']) || !isset($_POST['confirm_password'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$password_input = $_POST['confirm_password'];

$query = "SELECT password FROM registertb WHERE userid = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    if (password_verify($password_input, $user['password'])) {
        header("Location: account.php");
        exit();
    } else {
        echo "<script>alert('Incorrect password.'); window.history.back();</script>";
    }
} else {
    echo "<script>alert('User not found.'); window.history.back();</script>";
}

$stmt->close();
$conn->close();
?>
