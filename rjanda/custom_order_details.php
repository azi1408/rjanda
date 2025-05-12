<?php
session_start();
include('connection.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = "";

// Get user's name
$stmt = $conn->prepare("SELECT name FROM registertb WHERE userid = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($name);
if ($stmt->fetch()) {
    $user_name = $name;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Custom Order Details - RJ & A Catering Services</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display&family=Open+Sans&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body, html { height: 100%; font-family: 'Open Sans', sans-serif; }
        body { 
            background: linear-gradient(to right, #2f2f2f, #e0d6c3); 
            color: #fff;
            overflow-x: hidden;
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
            justify-content: space-between;
            padding: 1rem 2rem;
            align-items: center;
        }
        
        nav a { 
            color: white; 
            text-decoration: none; 
            margin-left: 2rem; 
            font-size: 1rem;
            transition: color 0.3s ease;
        }
        
        nav a:hover { 
            color: #d4b895; 
        }

        .nav-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo-img { 
            width: 60px; 
            height: 60px; 
            border-radius: 50%; 
            object-fit: cover; 
            border: 2px solid white; 
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.2); 
        }

        .order-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            position: relative;
            z-index: 1;
        }

        .order-box {
            background-color: rgba(68, 68, 68, 0.9);
            border-radius: 12px;
            padding: 20px;
            backdrop-filter: blur(5px);
        }

        .order-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .order-header h1 {
            color: #d4b895;
            font-family: 'Playfair Display', serif;
            margin-bottom: 10px;
        }

        .order-details {
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .detail-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .detail-label {
            color: #d4b895;
            font-weight: bold;
        }

        .detail-value {
            color: #fff;
        }

        .price {
            font-size: 1.2em;
            color: #4CAF50;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }

        .confirm-btn, .decline-btn {
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .confirm-btn {
            background-color: #4CAF50;
            color: white;
        }

        .confirm-btn:hover {
            background-color: #45a049;
        }

        .decline-btn {
            background-color: #f44336;
            color: white;
        }

        .decline-btn:hover {
            background-color: #da190b;
        }

        .message {
            text-align: center;
            margin-top: 20px;
            padding: 10px;
            border-radius: 5px;
            display: none;
        }

        .success {
            background-color: rgba(76, 175, 80, 0.2);
            color: #4CAF50;
        }

        .error {
            background-color: rgba(244, 67, 54, 0.2);
            color: #f44336;
        }
    </style>
</head>
<body>
    <div class="background-slider"></div>

    <nav>
        <div class="nav-left">
            <img src="logo.jfif" alt="Logo" class="logo-img">
            <?php if (!empty($user_name)) {
                echo "<span class='greeting'>Hello, <strong>$user_name</strong>!</span>";
            } ?>
        </div>
        <div>
            <a href="home.php">Home</a>
            <a href="account.php">Account Settings</a>
            <a href="orders.php">Packages</a>
            <a href="show_reviews.php">Reviews</a>
            <a href="chat.php">Chat</a>
            <a href="logout.php">Log Out</a>
        </div>
    </nav>

    <div class="order-container">
        <div class="order-box">
            <div class="order-header">
                <h1>Custom Order Details</h1>
                <p>Please review the order details below</p>
            </div>

            <div class="order-details">
                <div class="detail-row">
                    <span class="detail-label">Package Name:</span>
                    <span class="detail-value" id="packageName"></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Description:</span>
                    <span class="detail-value" id="packageDescription"></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Price:</span>
                    <span class="detail-value price" id="packagePrice"></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Additional Details:</span>
                    <span class="detail-value" id="packageDetails"></span>
                </div>
            </div>

            <div class="action-buttons">
                <button class="confirm-btn" onclick="confirmOrder()">Confirm Order</button>
                <button class="decline-btn" onclick="declineOrder()">Decline Order</button>
            </div>

            <div id="message" class="message"></div>
        </div>
    </div>

    <script>
        // Parse URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        const orderData = JSON.parse(decodeURIComponent(urlParams.get('order')));

        // Display order details
        document.getElementById('packageName').textContent = orderData.package_name;
        document.getElementById('packageDescription').textContent = orderData.description;
        document.getElementById('packagePrice').textContent = `₱${orderData.price}`;
        document.getElementById('packageDetails').textContent = orderData.details || 'None';

        function showMessage(message, type) {
            const messageDiv = document.getElementById('message');
            messageDiv.textContent = message;
            messageDiv.className = `message ${type}`;
            messageDiv.style.display = 'block';
        }

        function confirmOrder() {
            // Send confirmation to admin via chat
            const message = `I confirm the custom order:\nPackage: ${orderData.package_name}\nPrice: ₱${orderData.price}`;
            
            fetch('send_message.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `message=${encodeURIComponent(message)}&is_admin=0`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage('Order confirmed! The admin will contact you shortly.', 'success');
                    // Disable buttons after confirmation
                    document.querySelector('.confirm-btn').disabled = true;
                    document.querySelector('.decline-btn').disabled = true;
                } else {
                    showMessage('Failed to confirm order. Please try again.', 'error');
                }
            });
        }

        function declineOrder() {
            if (confirm('Are you sure you want to decline this order?')) {
                const message = `I decline the custom order:\nPackage: ${orderData.package_name}\nPrice: ₱${orderData.price}`;
                
                fetch('send_message.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `message=${encodeURIComponent(message)}&is_admin=0`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showMessage('Order declined. The admin will be notified.', 'success');
                        // Disable buttons after declining
                        document.querySelector('.confirm-btn').disabled = true;
                        document.querySelector('.decline-btn').disabled = true;
                    } else {
                        showMessage('Failed to decline order. Please try again.', 'error');
                    }
                });
            }
        }
    </script>
</body>
</html> 