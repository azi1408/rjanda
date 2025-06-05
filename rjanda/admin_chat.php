<?php
session_start();
include('connection.php');

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch all unique users who have sent messages
$query = "SELECT DISTINCT r.userid, r.name, 
          (SELECT COUNT(*) FROM chat_messages 
           WHERE user_id = r.userid AND sender_type = 'user' AND is_read = 0) as unread_count
          FROM registertb r
          INNER JOIN chat_messages cm ON r.userid = cm.user_id
          ORDER BY r.name ASC";
$result = mysqli_query($conn, $query);
$users = mysqli_fetch_all($result, MYSQLI_ASSOC);
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
            padding: 8px 0;
            box-shadow: 0px 4px 8px rgba(0,0,0,0.5);
            z-index: 1000;
        }

        .nav-links.show {
            display: flex;
        }

        .nav-links li {
            padding: 8px 15px;
        }

        .nav-links a {
            color: beige;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .nav-links a:hover {
            color: #d4b895;
        }

        @media screen and (max-width: 768px) {
            .container {
                padding: 10px;
            }
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

        .chat-input button.hidden {
            display: none;
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

        /* Image Modal Styles */
        .image-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.9);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            max-width: 90%;
            max-height: 90%;
            object-fit: contain;
        }

        .close-modal {
            position: absolute;
            top: 15px;
            right: 35px;
            color: #f1f1f1;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
        }

        .close-modal:hover {
            color: #bbb;
        }

        /* Custom Order Modal Styles */
        .custom-order-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .custom-order-content {
            background-color: #2f2f2f;
            padding: 30px;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            position: relative;
        }

        .custom-order-content h2 {
            color: #d4b895;
            margin-bottom: 20px;
            font-family: 'Playfair Display', serif;
        }

        .custom-order-content label {
            display: block;
            color: #fff;
            margin-bottom: 8px;
            font-weight: bold;
        }

        .custom-order-content input,
        .custom-order-content textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #444;
            border-radius: 5px;
            background-color: rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        .custom-order-content textarea {
            height: 100px;
            resize: vertical;
        }

        .custom-order-content button {
            background-color: #d4b895;
            color: #2f2f2f;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            width: 100%;
            margin-top: 10px;
        }

        .custom-order-content button:hover {
            background-color: #c4a985;
        }

        .close-custom-order {
            position: absolute;
            top: 15px;
            right: 15px;
            color: #d4b895;
            font-size: 24px;
            cursor: pointer;
        }

        .close-custom-order:hover {
            color: #c4a985;
        }
    </style>
</head>
<body>
    <div class="background-slider"></div>

    <!-- Custom Order Modal -->
    <div id="customOrderModal" class="custom-order-modal">
        <div class="custom-order-content">
            <span class="close-custom-order" onclick="closeCustomOrderModal()">&times;</span>
            <h2>Create Custom Order</h2>
            <form id="customOrderForm" onsubmit="submitCustomOrder(event)">
                <input type="hidden" id="customOrderUserId" name="user_id">
                <label for="eventType">Event Type:</label>
                <input type="text" id="eventType" name="event_type" required>

                <label for="guestCount">Number of Guests:</label>
                <input type="number" id="guestCount" name="guest_count" min="1" required>

                <label for="budget">Total Budget (₱):</label>
                <input type="number" id="budget" name="budget" min="1" step="0.01" required>

                <label for="additionalNotes">Additional Notes:</label>
                <textarea id="additionalNotes" name="additional_notes"></textarea>

                <button type="submit">Create Order</button>
            </form>
        </div>
    </div>

    <!-- Image Modal -->
    <div id="imageModal" class="image-modal">
        <span class="close-modal">&times;</span>
        <img class="modal-content" id="modalImage">
    </div>

    <nav class="navbar">
        <div class="navbar-left">
            <img src="logo.jfif" alt="Logo" class="logo-img">
            <?php if (!empty($user_name)) {
                echo "<span class='greeting'>Hello, <strong>$user_name</strong>!</span>";
            } ?>
        </div>
        <button class="menu-toggle" onclick="toggleMenu()">☰</button>
        <ul id="navLinks" class="nav-links">
            <li><a href="admin_dashboard.php">Dashboard</a></li>
            <li><a href="admin_home.php">Orders</a></li>
            <li><a href="admin_users.php">Users</a></li>
            <li><a href="admin_reviews.php">Reviews</a></li>
            <li><a href="admin_chat.php">💬 Chat</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>

    <div class="chat-container">
        <div class="users-list">
            <h3 style="margin-bottom: 20px; color: #d4b895;">Users</h3>
            <?php foreach ($users as $user): ?>
            <div class="user-item" data-user-id="<?php echo $user['userid']; ?>">
                <?php echo htmlspecialchars($user['name']); ?>
                <?php if ($user['unread_count'] > 0): ?>
                <span class="unread-badge"><?php echo $user['unread_count']; ?></span>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="chat-box">
            <div id="chatMessages" class="chat-messages">
                <div class="no-chat-selected">Select a user to start chatting</div>
            </div>
            <div class="chat-input">
                <input type="text" id="messageInput" placeholder="Type your message..." autocomplete="off">
                <input type="file" id="imageInput" accept="image/*" style="display: none;">
                <button onclick="document.getElementById('imageInput').click()" style="background-color: #666;">📷</button>
                <button onclick="openCustomOrderModal()" style="background-color: #4CAF50;" class="hidden" id="customOrderBtn">Create Custom Order</button>
                <button onclick="sendMessage()">Send</button>
            </div>
        </div>
    </div>

    <script>
        let currentUserId = null;
        let lastMessageId = 0;

        // Get modal elements
        const modal = document.getElementById('imageModal');
        const modalImg = document.getElementById('modalImage');
        const closeBtn = document.getElementsByClassName('close-modal')[0];

        // Close modal when clicking the X
        closeBtn.onclick = function() {
            modal.style.display = "none";
        }

        // Close modal when clicking outside the image
        modal.onclick = function(event) {
            if (event.target === modal) {
                modal.style.display = "none";
            }
        }

        // Function to handle image upload
        document.getElementById('imageInput').addEventListener('change', function(e) {
            if (!currentUserId) {
                alert('Please select a user first');
                return;
            }

            const file = e.target.files[0];
            if (file) {
                const formData = new FormData();
                formData.append('image', file);
                formData.append('sender_type', 'admin');
                formData.append('user_id', currentUserId);

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

        // Function to load messages
        function loadMessages() {
            if (!currentUserId) {
                // Hide custom order button when no user is selected
                document.getElementById('customOrderBtn').classList.add('hidden');
                return;
            }

            fetch(`get_messages.php?user_id=${currentUserId}`)
                .then(response => response.json())
                .then(data => {
                    const chatMessages = document.getElementById('chatMessages');
                    chatMessages.innerHTML = '';
                    
                    if (data.length === 0) {
                        chatMessages.innerHTML = '<div class="no-chat-selected">No messages yet</div>';
                        return;
                    }
                    
                    data.forEach(message => {
                        const messageDiv = document.createElement('div');
                        const isAdminMessage = message.sender_type === 'admin';
                        messageDiv.className = `message ${isAdminMessage ? 'admin' : 'user'}`;
                        
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
                            img.style.cursor = 'pointer'; // Add pointer cursor
                            
                            // Add click event to show full image
                            img.onclick = function() {
                                modal.style.display = "flex";
                                modalImg.src = this.src;
                            }
                            
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
                    markMessagesAsRead(currentUserId);
                });
        }

        // Function to mark messages as read
        function markMessagesAsRead(userId) {
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
                    // Remove unread badge for this user
                    const userItem = document.querySelector(`.user-item[data-user-id="${userId}"]`);
                    const unreadBadge = userItem.querySelector('.unread-badge');
                    if (unreadBadge) {
                        unreadBadge.remove();
                    }
                }
            })
            .catch(error => console.error('Error marking messages as read:', error));
        }

        // Function to update unread counts
        function updateUnreadCounts() {
            fetch('get_unread_counts.php')
                .then(response => response.json())
                .then(data => {
                    data.forEach(item => {
                        const userItem = document.querySelector(`.user-item[data-user-id="${item.user_id}"]`);
                        if (userItem) {
                            let unreadBadge = userItem.querySelector('.unread-badge');
                            
                            if (item.unread_count > 0) {
                                if (!unreadBadge) {
                                    unreadBadge = document.createElement('span');
                                    unreadBadge.className = 'unread-badge';
                                    userItem.appendChild(unreadBadge);
                                }
                                unreadBadge.textContent = item.unread_count;
                            } else if (unreadBadge) {
                                unreadBadge.remove();
                            }
                        }
                    });
                })
                .catch(error => console.error('Error updating unread counts:', error));
        }

        // Function to send message
        function sendMessage() {
            if (!currentUserId) {
                alert('Please select a user first');
                return;
            }

            const input = document.getElementById('messageInput');
            const message = input.value.trim();
            
            if (message) {
                const formData = new FormData();
                formData.append('user_id', currentUserId);
                formData.append('message', message);
                formData.append('sender_type', 'admin');

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
                })
                .catch(error => {
                    console.error('Error sending message:', error);
                    alert('Error sending message. Please try again.');
                });
            }
        }

        // Event Listeners
        document.querySelectorAll('.user-item').forEach(item => {
            item.addEventListener('click', function() {
                // Remove active class from all users
                document.querySelectorAll('.user-item').forEach(i => i.classList.remove('active'));
                // Add active class to clicked user
                this.classList.add('active');
                // Set current user ID
                currentUserId = this.dataset.userId;
                // Show custom order button
                document.getElementById('customOrderBtn').classList.remove('hidden');
                // Load messages for selected user
                loadMessages();
                // Clear input field
                document.getElementById('messageInput').value = '';
            });
        });

        // Handle Enter key
        document.getElementById('messageInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });

        // Auto refresh messages and unread counts
        setInterval(loadMessages, 3000);
        setInterval(updateUnreadCounts, 3000);

        // Initial load
        updateUnreadCounts();

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

        function openCustomOrderModal() {
            if (!currentUserId) {
                alert('Please select a user first');
                return;
            }
            document.getElementById('customOrderUserId').value = currentUserId;
            document.getElementById('customOrderModal').style.display = 'flex';
        }

        function closeCustomOrderModal() {
            document.getElementById('customOrderModal').style.display = 'none';
            document.getElementById('customOrderForm').reset();
        }

        function submitCustomOrder(event) {
            event.preventDefault();
            
            const formData = new FormData(document.getElementById('customOrderForm'));
            
            // Show loading state
            const submitButton = document.querySelector('#customOrderForm button[type="submit"]');
            const originalText = submitButton.textContent;
            submitButton.textContent = 'Creating Order...';
            submitButton.disabled = true;
            
            fetch('create_custom_order.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Custom order created successfully!');
                    closeCustomOrderModal();
                    loadMessages();
                } else {
                    // Show detailed error message
                    let errorMessage = 'Error creating custom order:\n\n';
                    if (data.error) {
                        errorMessage += data.error + '\n\n';
                    }
                    if (data.debug) {
                        errorMessage += 'Debug Information:\n';
                        for (const [key, value] of Object.entries(data.debug)) {
                            errorMessage += `${key}: ${value}\n`;
                        }
                    }
                    alert(errorMessage);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error creating custom order. Please try again. If the problem persists, please contact support.');
            })
            .finally(() => {
                // Reset button state
                submitButton.textContent = originalText;
                submitButton.disabled = false;
            });
        }
    </script>
</body>
</html> 