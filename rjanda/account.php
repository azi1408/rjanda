<?php
session_start();
include('connection.php');

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please log in.'); window.location.href='index.php';</script>";
    exit;
}

$user_id = $_SESSION['user_id'];

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check column and table exist
$sql = "SELECT name, username, contact FROM registertb WHERE userid = ?";
$query = $conn->prepare($sql);

if (!$query) {
    die("Prepare failed: " . $conn->error . " | SQL: $sql");
}

$query->bind_param("i", $user_id);
$query->execute();
$query->bind_result($name, $email, $phone);

if (!$query->fetch()) {
    // This just means no result found; not a fatal error
    $name = $email = $phone = '';
}
$query->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Account Settings</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(to bottom right,#323232, #d4b895);
            color: #fff;
        }

        /* Navbar */
        .navbar {
            background-color: #111;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.4);
        }

        .navbar a {
            color: white;
            text-decoration: none;
            margin: 0 15px;
            font-weight: 500;
        }

        .navbar a:hover {
            color: #d4b895;
        }

        .logo-img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid white;
            box-shadow: 0 0 4px rgba(255, 255, 255, 0.2);
        }

        /* Hamburger Menu Styles */
        .menu-toggle {
            font-size: 28px;
            background: none;
            border: none;
            color: beige;
            cursor: pointer;
            display: block;
        }

        .nav-links {
            display: none;
            flex-direction: column;
            position: absolute;
            top: 70px;
            right: 30px;
            background-color: #222;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0px 4px 8px rgba(0,0,0,0.5);
            z-index: 1000;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            margin: 5px 0;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }

        .nav-links a:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .nav-links.show {
            display: flex;
        }

        /* Container */
        .container {
            max-width: 600px;
            margin: 60px auto;
            background-color: #222;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.5);
        }

        h2 {
            text-align: center;
            color: #d4b895;
            margin-bottom: 30px;
        }

        label {
            display: block;
            margin: 15px 0 5px;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="tel"] {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 6px;
            background-color: #333;
            color: #fff;
        }

        input[type="submit"] {
            margin-top: 25px;
            width: 100%;
            background-color: #d4b895;
            color: #000;
            border: none;
            padding: 12px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: white;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
    <!-- Left side: Logo and title -->
    <div style="display: flex; align-items: center; gap: 15px;">
        <img src="logo.jfif" alt="Logo" class="logo-img">
        <span style="color: white; font-size: 1.2em; font-weight: bold;">RJ & A Catering Services</span>
    </div>

    <button class="menu-toggle" onclick="toggleMenu()">☰</button>
    <!-- Right side: Nav links -->
    <ul id="navLinks" class="nav-links">
        <?php
        if (isset($_SESSION['user_id'])) {
            echo '<a href="home.php">Home</a>';
        }

        if (isset($_SESSION['user_id'])) {
            echo '<a href="orders.php">Packages</a>';
        }

        if (isset($_SESSION['user_id'])) {
            echo '<a href="order_details.php">Payment Methods</a>';
        }

        if (isset($_SESSION['user_id'])) {
            echo '<a href="show_reviews.php">Reviews</a>';
        }

        if (isset($_SESSION['user_id'])) {
            echo '<a href="chat.php">Chat with Admin</a>';
        }

        if (isset($_SESSION['user_id'])) {
            echo '<a href="logout.php">Log Out</a>';
        } else {
            echo '<a href="index.php">Log In</a>';
        }
        ?>
    </ul>
</nav>

<!-- Account Settings Form -->
<div class="container">
    <h2>Edit Account Details</h2>
    <form action="update_account.php" method="POST">
        <label>Full Name:</label>
        <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" required>

        <label>Email:</label>
        <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required>

        <label>Contact Number:</label>
        <input type="tel" name="phone" value="<?= htmlspecialchars($phone) ?>" required>

        <label>New Password:</label>
        <input type="password" name="new_password">

        <label>Confirm Password:</label>
        <input type="password" name="confirm_password">

        <input type="submit" value="Save Changes">
    </form>
</div>

<script>
    function toggleMenu() {
        document.getElementById("navLinks").classList.toggle("show");
    }

    // Close menu if clicked outside
    document.addEventListener("click", function(event) {
        const menu = document.getElementById("navLinks");
        const button = document.querySelector(".menu-toggle");
        if (!menu.contains(event.target) && !button.contains(event.target)) {
            menu.classList.remove("show");
        }
    });
</script>
</body>
</html>
