<?php
session_start();
include('connection.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('You need to log in first.');</script>";
    echo "<script>window.location.href = 'index.php';</script>";
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

        .greeting {
            margin-left: 15px;
            font-size: 1.1rem;
            color: #f0e6d2;
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

        .chat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }

        .status-container {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .status-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: #4CAF50;
        }

        .status-indicator.offline {
            background-color: #f44336;
        }

        .status-text {
            color: #fff;
            font-size: 0.9em;
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
            margin-right: auto;
            align-items: flex-start;
        }

        .message.admin {
            margin-left: auto;
            align-items: flex-end;
        }

        .message-content {
            padding: 12px 16px;
            border-radius: 15px;
            display: inline-block;
            max-width: 100%;
            word-wrap: break-word;
        }

        .user .message-content {
            background-color: #444;
            color: #fff;
            border-bottom-left-radius: 5px;
        }

        .admin .message-content {
            background-color: #d4b895;
            color: #2f2f2f;
            border-bottom-right-radius: 5px;
        }

        .message-time {
            font-size: 0.8em;
            color: #888;
            margin-top: 5px;
            padding: 0 5px;
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

        .clear-chat-btn {
            padding: 8px 16px;
            background-color: #ff4444;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9em;
            transition: background-color 0.3s;
        }

        .clear-chat-btn:hover {
            background-color: #ff0000;
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
            <a href="logout.php">Log Out</a>
        </div>
    </nav>

    <div class="chat-container">
        <div class="chat-box">
            <div class="chat-header">
                <div class="status-container">
                    <div class="status-indicator"></div>
                    <span id="adminStatus" class="status-text">Checking admin status...</span>
                </div>
                <button class="clear-chat-btn" onclick="clearChat()">Clear Chat</button>
            </div>
            <div class="chat-messages" id="chatMessages">
                <!-- Messages will be loaded here -->
            </div>
            <div class="chat-input">
                <input type="text" id="messageInput" placeholder="Type your message..." autocomplete="off">
                <button onclick="sendMessage()">Send</button>
            </div>
        </div>
    </div>

    <script>
        let lastMessageId = 0;
        const userId = <?php echo $user_id; ?>;
        const userName = "<?php echo $user_name; ?>";

        function updateAdminStatus() {
            fetch('check_admin_status.php')
                .then(response => response.json())
                .then(data => {
                    const statusElement = document.getElementById('adminStatus');
                    if (data.online) {
                        statusElement.textContent = 'Admin is online';
                        statusElement.classList.add('online');
                    } else {
                        statusElement.textContent = 'Admin is offline';
                        statusElement.classList.remove('online');
                    }
                });
        }

        function loadMessages() {
            fetch(`get_messages.php?last_id=${lastMessageId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.messages.length > 0) {
                        data.messages.forEach(message => {
                            if (message.id > lastMessageId) {
                                addMessageToChat(message);
                                lastMessageId = message.id;
                            }
                        });
                    }
                });
        }

        function addMessageToChat(message) {
            const chatMessages = document.getElementById('chatMessages');
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${message.is_admin ? 'admin' : 'user'}`;
            
            const time = new Date(message.created_at).toLocaleTimeString();
            
            messageDiv.innerHTML = `
                <div class="message-content">${message.message}</div>
                <div class="message-time">${time}</div>
            `;
            
            chatMessages.appendChild(messageDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function sendMessage() {
            const input = document.getElementById('messageInput');
            const message = input.value.trim();
            
            if (message) {
                fetch('send_message.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `message=${encodeURIComponent(message)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        input.value = '';
                        // Update lastMessageId to prevent duplicate loading
                        lastMessageId = data.message_id;
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

        function clearChat() {
            if (confirm('Are you sure you want to clear this chat? This will delete all messages.')) {
                fetch('clear_chat.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `user_id=${userId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Clear the chat view
                        document.getElementById('chatMessages').innerHTML = '';
                        // Reset lastMessageId
                        lastMessageId = 0;
                        // Reload messages
                        loadMessages();
                    }
                });
            }
        }

        // Initial load
        loadMessages();
        updateAdminStatus();

        // Poll for new messages and admin status
        setInterval(loadMessages, 3000);
        setInterval(updateAdminStatus, 10000);
    </script>
</body>
</html> 