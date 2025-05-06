<?php
session_start();
include('connection.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please log in to place an order.'); window.location.href='login.php';</script>";
    exit;
}

$user_id = $_SESSION['user_id']; // Get the logged-in user's ID

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $packageName = $_POST['package_name'];
    $name =($_POST['name']);
    $event_date =$_POST['event_date'];  // Getting the event date from the form


    $guests = (int) $_POST['guests'];
    $address = $conn->real_escape_string($_POST['address']);

    // Dishes and desserts (from checkbox arrays)
    $dishes = isset($_POST['dishes']) ? implode(', ', $_POST['dishes']) : '';
    $desserts = isset($_POST['desserts']) ? implode(', ', $_POST['desserts']) : '';

    // Get latitude and longitude
    $latitude = isset($_POST['lat']) ? $_POST['lat'] : '';
    $longitude = isset($_POST['lng']) ? $_POST['lng '] : '';

    // Save the order to the database
    $stmt = $conn->prepare("INSERT INTO catering_orders(package_name, name, events_date, guests, address, dishes, desserts, latitude, longitude, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssisssiii",$packageName, $name, $event_date, $guests, $address, $dishes, $desserts, $latitude, $longitude,$user_id);

    if ($stmt->execute()) {
        echo "<script>alert('Order submitted successfully!'); window.location.href='orders.php';</script>";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
