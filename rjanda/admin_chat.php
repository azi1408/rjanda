<?php
session_start();
include('connection.php');

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

// Get all unique users who have sent messages
$stmt = $conn->prepare("
    SELECT DISTINCT r.userid, r.name 
    FROM chat_messages m 
    JOIN registertb r ON m.user_id = r.userid 
    ORDER BY r.name
");
$stmt->execute();
$users = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Chat - RJ & A Catering Services</title>
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

        .chat-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            display: flex;
            gap: 20px;
            position: relative;
            z-index: 1;
            height: calc(100vh - 100px);
        }

        .users-list {
            width: 300px;
            background-color: rgba(68, 68, 68, 0.9);
            border-radius: 12px;
            padding: 20px;
            backdrop-filter: blur(5px);
            height: 100%;
            overflow-y: auto;
        }

        .user-item {
            padding: 12px;
            margin-bottom: 10px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            position: relative;
        }

        .unread-badge {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background-color: #ff4444;
            color: white;
            border-radius: 50%;
            min-width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8em;
            font-weight: bold;
            padding: 0 6px;
        }

        .user-item:hover {
            background-color: rgba(255, 255, 255, 0.2);
            transform: translateX(5px);
        }

        .user-item.active {
            background-color: #d4b895;
            color: #2f2f2f;
        }

        .chat-box {
            flex-grow: 1;
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

        .header-buttons {
            display: flex;
            gap: 10px;
        }

        .custom-order-btn {
            padding: 8px 16px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9em;
            transition: background-color 0.3s;
        }

        .custom-order-btn:hover {
            background-color: #45a049;
        }

        .close-chat-btn {
            padding: 8px 16px;
            background-color: #f44336;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9em;
            transition: background-color 0.3s;
        }

        .close-chat-btn:hover {
            background-color: #da190b;
        }

        .order-form-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: 1000;
        }

        .order-form-container {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .order-form-container h2 {
            color: #333;
            margin-bottom: 20px;
        }

        .order-form-container label {
            display: block;
            margin-bottom: 5px;
            color: #333;
        }

        .order-form-container input,
        .order-form-container textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .order-form-container button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .order-form-container button:hover {
            background-color: #45a049;
        }

        .close-modal {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 24px;
            cursor: pointer;
            color: #666;
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

        .no-chat-selected {
            text-align: center;
            color: #888;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            font-size: 1.2em;
        }
    </style>
</head>
<body>
    <div class="background-slider"></div>

    <nav>
        <div class="nav-left">
            <img src="logo.jfif" alt="Logo" class="logo-img">
            <span style="color: white; font-size: 1.2em;">Admin Panel</span>
        </div>
        <div>
            <a href="admin_dashboard.php">Dashboard</a>
            <a href="admin_users.php">Users</a>
            <a href="admin_packages.php">Packages</a>
            <a href="admin_orders.php">Orders</a>
            <a href="admin_chat.php">Chat</a>
            <a href="logout.php">Log Out</a>
        </div>
    </nav>

    <div class="chat-container">
        <div class="users-list">
            <h3 style="margin-bottom: 20px; color: #d4b895;">Users</h3>
            <?php while ($user = $users->fetch_assoc()): ?>
                <div class="user-item" data-user-id="<?= $user['userid'] ?>" onclick="selectUser(<?= $user['userid'] ?>, '<?= htmlspecialchars($user['name']) ?>')">
                    <?= htmlspecialchars($user['name']) ?>
                </div>
            <?php endwhile; ?>
        </div>

        <div class="chat-box">
            <div class="chat-header">
                <div class="status-container">
                    <div class="status-indicator"></div>
                    <span id="adminStatus" class="status-text">Checking admin status...</span>
                </div>
                <div class="header-buttons">
                    <button class="custom-order-btn" onclick="showOrderForm()">Create Custom Order</button>
                    <button class="close-chat-btn" onclick="closeChat()">Close Chat</button>
                </div>
            </div>
            <div id="chatMessages" class="chat-messages">
                <div class="no-chat-selected">Select a user to start chatting</div>
            </div>
            <div class="chat-input">
                <input type="text" id="messageInput" placeholder="Type your message..." autocomplete="off">
                <button onclick="sendMessage()">Send</button>
            </div>
        </div>
    </div>

    <!-- Order Form Modal -->
    <div id="orderFormModal" class="order-form-modal">
        <div class="order-form-container">
            <span class="close-modal" onclick="closeOrderForm()">&times;</span>
            <h2>Create Custom Order</h2>
            <form id="customOrderForm">
                <input type="hidden" id="selectedUserId" name="user_id">
                <div>
                    <label for="packageName">Package Name:</label>
                    <input type="text" id="packageName" name="package_name" required>
                </div>
                <div>
                    <label for="packageDescription">Description:</label>
                    <textarea id="packageDescription" name="description" required></textarea>
                </div>
                <div>
                    <label for="packagePrice">Price:</label>
                    <input type="number" id="packagePrice" name="price" step="0.01" required>
                </div>
                <div>
                    <label for="packageDetails">Additional Details:</label>
                    <textarea id="packageDetails" name="details"></textarea>
                </div>
                <button type="submit">Send Order to User</button>
            </form>
        </div>
    </div>

    <script>
        let selectedUserId = null;
        let lastMessageId = 0;
        let unreadCounts = {};
        const closeChatBtn = document.getElementById('closeChatBtn');
        const selectedUserName = document.getElementById('selectedUserName');

        function updateUnreadCounts() {
            fetch('get_unread_counts.php')
                .then(response => response.json())
                .then(data => {
                    unreadCounts = data;
                    updateUnreadBadges();
                });
        }

        function updateUnreadBadges() {
            document.querySelectorAll('.user-item').forEach(item => {
                const userId = item.getAttribute('data-user-id');
                const badge = item.querySelector('.unread-badge');
                
                if (unreadCounts[userId] > 0) {
                    if (!badge) {
                        const newBadge = document.createElement('div');
                        newBadge.className = 'unread-badge';
                        newBadge.textContent = unreadCounts[userId];
                        item.appendChild(newBadge);
                    } else {
                        badge.textContent = unreadCounts[userId];
                    }
                } else if (badge) {
                    badge.remove();
                }
            });
        }

        function closeChat() {
            if (!selectedUserId) return;

            if (confirm('Are you sure you want to close this chat?')) {
                // Clear the chat view
                document.getElementById('chatMessages').innerHTML = '<div class="no-chat-selected">Select a user to start chatting</div>';
                // Reset selection
                selectedUserId = null;
                selectedUserName.textContent = '';
                closeChatBtn.disabled = true;
                // Remove active class from user item
                document.querySelectorAll('.user-item').forEach(item => {
                    item.classList.remove('active');
                });
            }
        }

        function selectUser(userId, userName) {
            selectedUserId = userId;
            selectedUserName.textContent = userName;
            closeChatBtn.disabled = false;
            
            document.querySelectorAll('.user-item').forEach(item => {
                item.classList.remove('active');
            });
            event.target.classList.add('active');
            document.getElementById('chatMessages').innerHTML = '';
            lastMessageId = 0;
            loadMessages();
            
            // Mark messages as read when selecting a user
            fetch('mark_messages_read.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `user_id=${userId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    unreadCounts[userId] = 0;
                    updateUnreadBadges();
                }
            });
        }

        function loadMessages() {
            if (!selectedUserId) return;

            fetch(`get_messages.php?last_id=${lastMessageId}&user_id=${selectedUserId}`)
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
            if (!selectedUserId) {
                alert('Please select a user first');
                return;
            }

            const input = document.getElementById('messageInput');
            const message = input.value.trim();
            
            if (message) {
                fetch('send_message.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `message=${encodeURIComponent(message)}&user_id=${selectedUserId}&is_admin=1`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        input.value = '';
                        addMessageToChat({
                            id: data.message_id,
                            message: message,
                            is_admin: true,
                            created_at: new Date().toISOString()
                        });
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

        // Poll for new messages and unread counts
        setInterval(loadMessages, 3000);
        setInterval(updateUnreadCounts, 5000);

        // Initial load
        updateUnreadCounts();

        function showOrderForm() {
            if (!selectedUserId) {
                alert('Please select a user first');
                return;
            }
            document.getElementById('selectedUserId').value = selectedUserId;
            document.getElementById('orderFormModal').style.display = 'block';
        }

        function closeOrderForm() {
            document.getElementById('orderFormModal').style.display = 'none';
        }

        document.getElementById('customOrderForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const orderData = {
                user_id: formData.get('user_id'),
                package_name: formData.get('package_name'),
                description: formData.get('description'),
                price: formData.get('price'),
                details: formData.get('details')
            };

            // Send order to user via chat
            const message = `New Custom Order:\nPackage: ${orderData.package_name}\nDescription: ${orderData.description}\nPrice: ₱${orderData.price}\nDetails: ${orderData.details}\n\nClick here to view and confirm: <a href="custom_order_details.php?order=${encodeURIComponent(JSON.stringify(orderData))}" onclick="window.open(this.href, '_blank')">View Order Details</a>`;
            
            fetch('send_message.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `message=${encodeURIComponent(message)}&user_id=${orderData.user_id}&is_admin=1`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeOrderForm();
                    this.reset();
                    // Add the message to the chat
                    addMessageToChat({
                        message: message,
                        is_admin: true,
                        created_at: new Date().toISOString()
                    });
                }
            });
        });

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('orderFormModal');
            if (event.target == modal) {
                closeOrderForm();
            }
        }
    </script>
</body>
</html> 