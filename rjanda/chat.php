<?php
session_start();
include('connection.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user's name
$stmt = $conn->prepare("SELECT name FROM registertb WHERE userid = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Chat with Admin - RJ & A Catering Services</title>
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

        .navbar {
            background-color: rgba(0, 0, 0, 0.6);
            display: flex;
            justify-content: space-between;
            padding: 1rem 2rem;
            align-items: center;
        }

        .navbar-left {
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

        .chat-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            position: relative;
            z-index: 1;
            height: calc(100vh - 100px);
        }

        .chat-box {
            background-color: rgba(68, 68, 68, 0.9);
            border-radius: 12px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            backdrop-filter: blur(5px);
            height: 100%;
        }

        .admin-status {
            padding: 15px;
            background-color: rgba(0, 0, 0, 0.3);
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .status-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: #ff4444;
        }

        .status-indicator.online {
            background-color: #28a745;
        }

        .chat-messages {
            flex-grow: 1;
            overflow-y: auto;
            padding: 20px;
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .message {
            margin-bottom: 10px;
            max-width: 70%;
            display: flex;
            flex-direction: column;
        }

        .message.user {
            margin-left: auto;
            align-items: flex-end;
        }

        .message.admin {
            margin-right: auto;
            align-items: flex-start;
        }

        .message-content {
            padding: 12px 16px;
            border-radius: 15px;
            display: inline-block;
            max-width: 100%;
            word-wrap: break-word;
        }

        .message-content img {
            max-width: 200px;
            max-height: 200px;
            border-radius: 8px;
            margin-top: 5px;
        }

        .user .message-content {
            background-color: #d4b895;
            color: #2f2f2f;
            border-bottom-right-radius: 5px;
        }

        .admin .message-content {
            background-color: #444;
            color: #fff;
            border-bottom-left-radius: 5px;
        }

        .message-time {
            font-size: 0.8em;
            color: #888;
            margin-top: 5px;
            padding: 0 5px;
        }

        .message-sender {
            font-size: 0.8em;
            color: #d4b895;
            margin-bottom: 3px;
            font-weight: bold;
        }

        .admin .message-sender {
            color: #d4b895;
        }

        .chat-input {
            display: flex;
            gap: 10px;
            padding: 15px;
            background-color: rgba(0, 0, 0, 0.3);
            border-radius: 8px;
            margin-top: auto;
        }

        .chat-input input {
            flex-grow: 1;
            padding: 12px;
            border: none;
            border-radius: 5px;
            background-color: rgba(255, 255, 255, 0.1);
            color: #fff;
            font-size: 1em;
        }

        .chat-input button {
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            background-color: #d4b895;
            color: #2f2f2f;
            cursor: pointer;
            transition: background-color 0.3s;
            font-weight: bold;
        }

        .chat-input button:hover {
            background-color: #c4a985;
        }
    </style>
</head>
<body>
    <div class="background-slider"></div>

    <nav class="navbar">
        <div class="navbar-left">
            <img src="logo.jfif" alt="Logo" class="logo-img">
        </div>
        <button class="menu-toggle" onclick="toggleMenu()">☰</button>
        <ul id="navLinks" class="nav-links">
            <a href="home.php">Home</a>
            <a href="orders.php">Packages</a>
            <a href="order_details.php">Payment Methods</a>
            <a href="logout.php">Logout</a>
        </ul>
    </nav>

    <div class="chat-container">
        <div class="chat-box">
            <div class="admin-status">
                <div class="status-indicator" id="adminStatus"></div>
                <span id="adminStatusText">Admin is offline</span>
            </div>
            <div id="chatMessages" class="chat-messages"></div>
            <div class="chat-input">
                <input type="text" id="messageInput" placeholder="Type your message..." autocomplete="off">
                <input type="file" id="imageInput" accept="image/*" style="display: none;">
                <button onclick="document.getElementById('imageInput').click()" style="background-color: #666;">📷</button>
                <button onclick="sendMessage()">Send</button>
            </div>
        </div>
    </div>

    <script>
        let lastMessageId = 0;

        // Function to handle image upload
        document.getElementById('imageInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const formData = new FormData();
                formData.append('image', file);
                formData.append('sender_type', 'user');

                fetch('upload_chat_image.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadMessages();
                    } else {
                        alert('Error uploading image: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error uploading image');
                });
            }
        });

        // Function to check admin status
        function checkAdminStatus() {
            fetch('check_admin_status.php')
                .then(response => response.json())
                .then(data => {
                    const statusIndicator = document.getElementById('adminStatus');
                    const statusText = document.getElementById('adminStatusText');
                    
                    if (data.online) {
                        statusIndicator.classList.add('online');
                        statusText.textContent = 'Admin is online';
                    } else {
                        statusIndicator.classList.remove('online');
                        statusText.textContent = 'Admin is offline';
                    }
                });
        }

        // Function to load messages
        function loadMessages() {
            fetch('get_messages.php')
                .then(response => response.json())
                .then(data => {
                    const chatMessages = document.getElementById('chatMessages');
                    chatMessages.innerHTML = '';
                    
                    data.forEach(message => {
                        const messageDiv = document.createElement('div');
                        const isUserMessage = message.sender_type === 'user';
                        messageDiv.className = `message ${isUserMessage ? 'user' : 'admin'}`;
                        
                        if (!isUserMessage) {
                            const senderDiv = document.createElement('div');
                            senderDiv.className = 'message-sender';
                            senderDiv.textContent = 'Admin';
                            messageDiv.appendChild(senderDiv);
                        }
                        
                        const contentDiv = document.createElement('div');
                        contentDiv.className = 'message-content';
                        
                        // Check if message is an image
                        if (message.message.match(/\.(jpg|jpeg|png|gif|webp)$/i)) {
                            const img = document.createElement('img');
                            img.src = 'chat_images/' + message.message;
                            img.alt = 'Chat image';
                            img.style.maxWidth = '200px';
                            img.style.maxHeight = '200px';
                            img.style.borderRadius = '8px';
                            img.style.marginTop = '5px';
                            img.style.display = 'block';
                            contentDiv.appendChild(img);
                        } else {
                            contentDiv.textContent = message.message;
                        }
                        
                        const timeDiv = document.createElement('div');
                        timeDiv.className = 'message-time';
                        timeDiv.textContent = new Date(message.created_at).toLocaleString();
                        
                        messageDiv.appendChild(contentDiv);
                        messageDiv.appendChild(timeDiv);
                        chatMessages.appendChild(messageDiv);
                        
                        if (message.id > lastMessageId) {
                            lastMessageId = message.id;
                        }
                    });
                    
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                });
        }

        // Function to send message
        function sendMessage() {
            const input = document.getElementById('messageInput');
            const message = input.value.trim();
            
            if (message) {
                const formData = new FormData();
                formData.append('message', message);
                formData.append('sender_type', 'user');

                fetch('send_message.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        input.value = '';
                        loadMessages();
                    }
                });
            }
        }

        // Handle Enter key
        document.getElementById('messageInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });

        // Auto refresh messages and admin status
        setInterval(loadMessages, 3000);
        setInterval(checkAdminStatus, 5000);

        // Initial load
        loadMessages();
        checkAdminStatus();

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