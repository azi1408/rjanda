<?php
session_start();
include('connection.php');
$user_name = "";

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT name FROM registertb WHERE userid = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($name);
    if ($stmt->fetch()) {
        $user_name = $name;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Catering Business</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display&family=Open+Sans&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body, html {
            height: 100%;
            font-family: 'Open Sans', sans-serif;
            overflow: hidden;
        }

        body {
            background: linear-gradient(to right, #2f2f2f, #e0d6c3);
            color: #fff;
        }

        .background-slider {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            animation: slide 30s infinite;
            background-size: cover;
            background-position: center;
        }

        @keyframes slide {
            0%, 100% { background-image: url('bg-1.jpg'); }
            33% { background-image: url('bg-2.jpg'); }
            66% { background-image: url('bg-3.jpg'); }
        }

        nav {
            background-color: rgba(0, 0, 0, 0.6);
            display: flex;
            justify-content: flex-end;
            padding: 1rem 2rem;
        }

        nav a {
            color: white;
            text-decoration: none;
            margin-left: 2rem;
            font-size: 1rem;
            
        }

        nav a:hover {
            color: #fff;
        }

        .content {
            text-align: center;
            margin-top: 15%;
            font-size: 3rem;
            opacity: 0;
            transform: scale(0.8) translateY(20px);
            animation: popup 0.6s ease-out forwards;
            
        }
        @keyframes popup {
      to {
        opacity: 1;
        transform: scale(1) translateY(0);
      }
    }

        h1 {
            font-family: 'Playfair Display', serif;
            font-size: 3.5rem;
            color: white;
            border-color:black 1px;
        }

        p.tagline {
            font-size: 1.5rem;
            font-style: italic;
            color: white;
            margin-top: 1rem;
        }
        .logo-img {
    width: 60px;
    height: 60px;
    border-radius: 50%; /* makes it a circle */
    object-fit: cover;  /* ensures image doesn't stretch */
    border: 2px solid white; /* optional border */
    box-shadow: 0 0 5px rgba(0,0,0,0.2); /* optional shadow */
}
.navbar .logo {
            font-size: 1.5em;
            font-weight: bold;
            color: white;
            text-decoration: none;
            margin-right:900px;
        }
        .modal-overlay {
    display: none;
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.7);
    justify-content: center;
    align-items: center;
    z-index: 999;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.modal-overlay.show {
    display: flex;
    opacity: 1;
}

.modal-box {
    background: #fff;
    color: #000;
    padding: 30px;
    border-radius: 12px;
    max-width: 400px;
    width: 90%;
    animation: fadeInUp 0.3s ease;
}

.modal-box input[type="password"] {
    width: 100%;
    padding: 10px;
    margin-bottom: 20px;
}

.button-group {
    display: flex;
    justify-content: space-between;
}

.button-group button {
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.button-group button[type="submit"] {
    background: #2f2f2f;
    color: #fff;
}

.button-group button[type="button"] {
    background: #bbb;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}   
nav span.greeting {
    margin-left: 15px;
    font-size: 1.1rem;
    color: #f0e6d2;
}
    </style>

</head>
<body>

    <div class="background-slider"></div>

    <nav>
    <div style="display: flex; align-items: center; gap: 15px; justify-content: flex-start; width: 100%;">
        <!-- Left Side: Logo and Greeting -->
        <img src="logo.jfif" alt="Logo" class="logo-img">
        <?php if (!empty($user_name)) {
            echo "<span class='greeting'>Hello, <strong>$user_name</strong>!</span>";
        } ?>
    </div>

    <!-- Right Side: Navigation Links -->
    <div style="display: flex; justify-content: flex-end; width: 100%;">
        <?php
        if (isset($_SESSION['user_id'])) {
            // If user is logged in, show the Account Settings button
            echo '<a href="account.php" class="account-link">Account Settings</a>';
        }

        if (isset($_SESSION['user_id'])) {
            // If user is logged in, show the Packages button
            echo '<a href="orders.php">Packages</a>';
        }

        if (isset($_SESSION['user_id'])) {
            // If user is logged in, show the Payment Methods button
            echo '<a href="order_details.php">Payment Methods</a>';
        }
        if (isset($_SESSION['user_id'])) {
            echo '<a href="show_reviews.php">Reviews</a>';
        }

        if (isset($_SESSION['user_id'])) {
            // If user is logged in, show the Chat with Admin button
            echo '<a href="chat.php">Chat with Admin</a>';
        }

        if (isset($_SESSION['user_id'])) {
            // If user is logged in, show the Log Out button
            echo '<a href="logout.php">Log Out</a>';
        } else {
            // If user is not logged in, show the Log In button
            echo '<a href="index.php">Log In</a>';
        }
        ?>
    </div>
</nav>

    <div id="passwordModal" class="modal-overlay">
    <div class="modal-box">
        <h3>Confirm Your Password</h3>
        <form method="POST" action="verify_password.php">
            <input type="password" name="confirm_password" placeholder="Enter your password" required>
            <div class="button-group">
                <button type="submit">Confirm</button>
                <button type="button" onclick="closePasswordPopup()">Cancel</button>
            </div>
        </form>
    </div>
</div>

    <div class="content">
        <h1>RJ & A Catering Services</h1>
        <p class="tagline">From Ordinary to Extraordinary, We Cater with Ingenuity.</p>
    </div>
    <script>
    function openPasswordPopup() {
        const modal = document.getElementById('passwordModal');
        modal.classList.add('show');
    }

    function closePasswordPopup() {
        const modal = document.getElementById('passwordModal');
        modal.classList.remove('show');
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    }

    document.addEventListener('DOMContentLoaded', () => {
        const accountLink = document.querySelector('.account-link');
        if (accountLink) {
            accountLink.addEventListener('click', (e) => {
                e.preventDefault();
                openPasswordPopup();
            });
        }
    });
</script>
</body>
</html>
