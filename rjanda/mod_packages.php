<?php
session_start();
include('connection.php');

// Only allow admin users here, you can add your own admin check if needed
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please log in.'); window.location.href='index.php';</script>";
    exit;
}

// Handle deletion
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM packages WHERE id = $id");
    header("Location: mod_packages.php");
    exit;
}

// Fetch all packages
$result = mysqli_query($conn, "SELECT * FROM packages ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Manage Packages</title>
    <style>
        .logo-img {
    height: 30px;
    width: 30px;
    border-radius: 50%;
    margin-right: 10px;
    object-fit: cover;
}

        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(to bottom right, #323232, #d4b895);
            color: #fff;
            margin: 0;
            padding: 0;
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #000;
            padding: 10px 20px;
        }

        nav .logo {
            display: flex;
            align-items: center;
        }

        nav .logo img {
            height: 25px;
            width: 25px;
            border-radius: 50px;
            margin-right: 10px;
        }

        nav .logo span {
            font-size: 1.2em;
            font-weight: bold;
            color: white;
        }

        nav .nav-links a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
            font-weight: bold;
        }

        nav .nav-links a:hover {
            color: #76ff03;
        }

        .container {
            padding: 40px 20px;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
        }

        .package {
            background: white;
            border: 1px solid black;
            color: black;
            border-radius: 15px;
            box-shadow: 0 0 15px black;
            padding: 20px;
            width: 300px;
            margin: 20px;
            position: relative;
        }

        .package h3 {
            margin-top: 0;
        }

        .package p {
            margin: 5px 0;
        }

        .delete-btn {
            background-color: #ff4444;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            margin-top: 10px;
        }

        .delete-btn:hover {
            background-color: #cc0000;
        }

        footer {
            background-color: #222;
            color: #a5d6a7;
            text-align: center;
            padding: 15px 0;
            position: fixed;
            width: 100%;
            bottom: 0;
        }

        .edit-btn {
            background-color: #2196f3;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            margin-left: 10px;
            display: inline-block;
        }

        .edit-btn:hover {
            background-color: #0b7dda;
        }

        .no-packages {
            background-color: #333;  /* Dark-themed background */
            color: #fff;
            padding: 20px;
            border-radius: 10px;
            width: 90%;
            text-align: center;
            font-size: 1.1em; /* Reduced font size */
            margin: 20px auto;
        }

        .add-package-container {
            background: linear-gradient(to right, #323232, #d4b895);
            color: #fff;
            padding: 20px;
            border-radius: 15px;
            width: 90%;
            text-align: center;
            margin: 20px auto;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .add-package-btn {
            background: linear-gradient(to right, #4CAF50, #45a049);
            color: white;
            border: none;
            font-size: 1.1em;
            padding: 12px 30px;
            border-radius: 25px;
            cursor: pointer;
            margin-top: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .add-package-btn:hover {
            background: linear-gradient(to right, #45a049, #388e3c);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        }

        .add-package-btn:active {
            transform: translateY(0);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .add-package-btn::before {
            content: '+';
            font-size: 1.4em;
            font-weight: bold;
        }

        /* Add hamburger and nav-links styles for responsive menu */
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

    </style>
</head>
<body>
<nav class="navbar">
    <div class="navbar-left">
        <img src="logo.jfif" alt="Logo" class="logo-img">
        <span class="greeting">RJ & A Catering Services - Moderator</span>
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
    <?php if (mysqli_num_rows($result) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($result)) : ?>
            <div class="package">
                <h3><?= htmlspecialchars($row['package_name']) ?></h3>
                <p><strong>Dishes:</strong> <?= nl2br(htmlspecialchars($row['dishes'])) ?></p>
                <p><strong>Desserts:</strong> <?= nl2br(htmlspecialchars($row['desserts'])) ?></p>
                <p><strong>Price:</strong> ₱<?= number_format($row['price'], 2) ?></p>
                <p><strong>Created At:</strong> <?= date("F j, Y H:i", strtotime($row['created_at'])) ?></p>
                <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($row['description'])) ?></p>
                <p><strong>Max Dishes Allowed:</strong> <?= $row['max_dishes'] ?></p>
                <p><strong>Max Desserts Allowed:</strong> <?= $row['max_desserts'] ?></p>

                <form method="get" onsubmit="return confirm('Are you sure you want to delete this package?');" style="display:inline;">
                    <input type="hidden" name="delete" value="<?= $row['id'] ?>">
                    <button type="submit" class="delete-btn">Delete</button>
                </form>

                <a href="mod_edit_packages.php?id=<?= $row['id'] ?>" class="edit-btn">Edit</a>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="no-packages">
            No packages were added yet. Please add a new package to get started.
        </div>
    <?php endif; ?>

    <!-- Add package button -->
    <div class="add-package-container">
        <a href="mod_add_packages.php" style="text-decoration: none;">
            <button class="add-package-btn">Add New Package</button>
        </a>
    </div>
</div>

<footer>&copy; 2025 RJ & A Catering Services</footer>

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
