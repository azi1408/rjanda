<?php
session_start();
include('connection.php');

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('You need to log in first.'); window.location.href = 'index.php';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = "";

// Check if user is admin or moderator
$query = "SELECT name, role FROM registertb WHERE userid = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($name, $role);
$stmt->fetch();
$stmt->close();

// Allow only admin and moderator roles
if ($role !== 'admin' && $role !== 'moderator') {
    header("Location: forbidden.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $package_name = $_POST['package_name'];
    $price = $_POST['price'];
    $dishes = $_POST['dishes'];
    $desserts = $_POST['desserts'];
    $status = $_POST['status'];
    $description = $_POST['description'];
    $max_dishes = intval($_POST['max_dishes']);
    $max_desserts = intval($_POST['max_desserts']);

    $sql = "INSERT INTO packages (package_name, price, dishes, desserts, status, max_dishes, max_desserts, description)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdsssiss", $package_name, $price, $dishes, $desserts, $status, $max_dishes, $max_desserts, $description);

    if ($stmt->execute()) {
        $success = "Package added successfully!";
        echo "<script>alert('$success'); window.location.href = 'mod_packages.php';</script>";
        exit();
    } else {
        $error = "Error: " . $conn->error;
    }

    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Package - Admin</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #2e2e2e;
            color: #f1f1f1;
            margin: 0;
        }

        .navbar {
            background-color: #111;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
            position: relative;
        }

        .navbar-left {
            display: flex;
            align-items: center;
        }

        .greeting {
            font-size: 1.5rem;
            color: #f7f2e9;
            margin-left: 25px;
        }

        .logo-img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 15px;
            border: 2px solid beige;
        }

        .hamburger {
            display: none;
            cursor: pointer;
            padding: 10px;
        }

        .hamburger-line {
            width: 25px;
            height: 3px;
            background-color: beige;
            margin: 5px 0;
            transition: all 0.3s ease;
        }

        .nav-links {
            display: flex;
            gap: 20px;
        }

        .nav-links a {
            color: beige;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .nav-links a:hover {
            color: #d4b895;
        }

        @media screen and (max-width: 768px) {
            .hamburger {
                display: block;
            }

            .nav-links {
                display: none;
                position: absolute;
                top: 100%;
                right: 0;
                background-color: #111;
                width: 200px;
                padding: 20px;
                flex-direction: column;
                gap: 15px;
                box-shadow: 0 4px 10px rgba(0,0,0,0.3);
                z-index: 1000;
            }

            .nav-links.active {
                display: flex;
            }

            .hamburger.active .hamburger-line:nth-child(1) {
                transform: rotate(-45deg) translate(-5px, 6px);
            }

            .hamburger.active .hamburger-line:nth-child(2) {
                opacity: 0;
            }

            .hamburger.active .hamburger-line:nth-child(3) {
                transform: rotate(45deg) translate(-5px, -6px);
            }
        }

        .container {
            max-width: 800px;
            margin: 40px auto;
            background-color: #3e3e3e;
            padding: 30px;
            border-radius: 12px;
        }

        h2 {
            color: #d4b895;
        }

        input, textarea, select {
            width: 100%;
            padding: 10px;
            margin: 10px 0 20px;
            border: none;
            border-radius: 8px;
            background-color: #2a2a2a;
            color: white;
        }

        label {
            font-weight: bold;
            color: beige;
        }

        button {
            background-color: #d4b895;
            color: #2a2a2a;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
        }

        button:hover {
            background-color: #c3a77c;
        }

        .message {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 8px;
            color: #fff;
        }

        .success { background-color: #4CAF50; }
        .error { background-color: #f44336; }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="navbar-left">
        <img src="logo.jfif" alt="Logo" class="logo-img">
        <span class="greeting">Hello, <strong><?= htmlspecialchars($name) ?></strong>!</span>
    </div>
    <div class="hamburger" onclick="toggleMenu()">
        <div class="hamburger-line"></div>
        <div class="hamburger-line"></div>
        <div class="hamburger-line"></div>
    </div>
    <div class="nav-links">
        <a href="mod_packages.php">Manage Packages</a>
        <a href="mod_add_packages.php">Add Package</a>
        <a href="mod_review_page.php">User Reviews</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<div class="container">
    <h2>➕ Add New Catering Package</h2>

    <?php if (!empty($success)): ?>
        <div class="message success"><?= $success ?></div>
    <?php elseif (!empty($error)): ?>
        <div class="message error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <label for="package_name">Package Name:</label>
        <input type="text" name="package_name" id="package_name" required>

        <label for="price">Price (₱):</label>
        <input type="number" step="0.01" name="price" id="price" required>

        <label for="dishes">Dishes (comma-separated):</label>
        <textarea name="dishes" id="dishes" rows="3" required></textarea>

        <label for="desserts">Desserts (comma-separated):</label>
        <textarea name="desserts" id="desserts" rows="2" required></textarea>

        <label for="max_dishes">Max Dishes Users Can Select:</label>
        <input type="number" name="max_dishes" id="max_dishes" min="1" required>

        <label for="max_desserts">Max Desserts Users Can Select:</label>
        <input type="number" name="max_desserts" id="max_desserts" min="0" required>

        <label for="description">Package Description:</label>
        <textarea name="description" id="description" rows="3" required></textarea>

        <label for="status">Status:</label>
        <select name="status" id="status">
            <option value="available">Available</option>
            <option value="unavailable">Unavailable</option>
        </select>

        <button type="submit">Add Package</button>
    </form>
</div>

<script>
function toggleMenu() {
    const navLinks = document.querySelector('.nav-links');
    const hamburger = document.querySelector('.hamburger');
    navLinks.classList.toggle('active');
    hamburger.classList.toggle('active');
}

// Close menu when clicking outside
document.addEventListener('click', function(event) {
    const navLinks = document.querySelector('.nav-links');
    const hamburger = document.querySelector('.hamburger');
    if (!event.target.closest('.nav-links') && !event.target.closest('.hamburger')) {
        navLinks.classList.remove('active');
        hamburger.classList.remove('active');
    }
});
</script>

</body>
</html>

<?php $conn->close(); ?>
