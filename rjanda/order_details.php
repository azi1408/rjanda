<?php
session_start();
include('connection.php');

// Redirect if user is not logged in
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('You need to log in first.');</script>";
    echo "<script>window.location.href = 'index.php';</script>";
    exit(); // Always stop script execution after redirect
}

$user_id = $_SESSION['user_id'];  // Safe to access now
// Fetch orders that belong to the current user by joining orders and registertb
$stmt = $conn->prepare("
    SELECT orders.*, registertb.name 
    FROM orders 
    JOIN registertb ON orders.user_id = registertb.userid  -- Correct column name
    WHERE registertb.userid = ? 
    ORDER BY orders.created_at DESC
");

if ($stmt === false) {
    die('Error preparing the query: ' . $conn->error);
}

$stmt->bind_param("i", $user_id);  // Bind the user's ID to the query
$stmt->execute();
$result = $stmt->get_result();


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order & Payment List</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: #2e2e2e;
            color: #f1f1f1;
        }

        .navbar {
            background-color: #111;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .navbar .logo-img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid white;
            box-shadow: 0 0 8px rgba(0,0,0,0.3);
        }

        .navbar .title {
            font-size: 1.8em;
            color: #f1f1f1;
            margin-left: 20px;
        }

        .navbar ul {
            list-style: none;
            display: flex;
            gap: 20px;
            margin: 0;
            padding: 0;
        }

        .navbar ul li a {
            color: beige;
            text-decoration: none;
            font-weight: 500;
        }

        .navbar ul li a:hover {
            color: #d4b895;
        }

        .container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
            background-color: #3e3e3e;
            border-radius: 12px;
        }

        h2 {
            color: #d4b895;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #2a2a2a;
        }

        th, td {
            padding: 15px;
            border-bottom: 1px solid #555;
            text-align: left;
        }

        th {
            background-color: #444;
            color: beige;
        }

        .pay-section {
            margin-top: 10px;
            background-color: #444;
            padding: 15px;
            border-radius: 10px;
        }

        .pay-section input,
        .pay-section select {
            width: 100%;
            padding: 8px;
            margin: 10px 0;
            border-radius: 6px;
            border: 1px solid #ccc; 
        }

        .pay-btn {
            background-color: #d4b895;
            color: #222;
            border: none;
            padding: 10px 15px;
            font-weight: bold;
            border-radius: 6px;
            cursor: pointer;
        }

        .pay-btn:hover {
            background-color: #caa97a;
        }

        .status-paid {
            color: #4caf50;
            font-weight: bold;
        }

        .status-pending {
            color: #ffc107;
            font-weight: bold;
        }

        .gcash-note {
            background: #eee;
            padding: 10px;
            border-radius: 6px;
            color: #333;
            margin-top: 10px;
            font-size: 0.95em;
        }

        .pay-btn.cancel {
            background-color: #ff6b6b;
        }
        .pay-btn.cancel:hover {
            background-color: #ff4d4d;
        }
       /* Password Modal Styling */
#passwordModal {
    display: none; /* Hidden by default */
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.6);
    z-index: 9999;
    justify-content: center;
    align-items: center;
}

#passwordModal.show {
    display: flex;
}

.modal-box {
    background: #fff;
    color: #333;
    padding: 30px;
    border-radius: 12px;
    max-width: 400px;
    width: 90%;
    box-shadow: 0 10px 30px rgba(0,0,0,0.4);
    text-align: center;
}

.modal-box input[type="password"] {
    width: 100%;
    padding: 10px;
    margin-top: 15px;
    border: 1px solid #ccc;
    border-radius: 6px;
}

.button-group {
    margin-top: 20px;
    display: flex;
    justify-content: space-around;
}

.button-group button {
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    font-weight: bold;
    cursor: pointer;
}

.button-group button[type="submit"] {
    background-color: #4caf50;
    color: white;
}

.button-group button[type="button"] {
    background-color: #f44336;
    color: white;
}


    </style>
</head>
<body>

<nav class="navbar">
    <div style="display: flex; align-items: center;">
        <img src="logo.jfif" alt="Logo" class="logo-img">
        <span class="title">RJ & A Catering Services</span>
    </div>
    <ul>
        <li><a href="home.php">Home</a></li>
        <li><a href="account.php" class="account-link">Account Settings</a></li>
        <li><a href="orders.php">Packages</a></li>
    </ul>
</nav>
<div id="passwordModal" class="modal-overlay">
    <div class="modal-box">
        <h3>Confirm Your Password</h3>
        <form method="POST" action="verify_password.php">
            <input type="hidden" name="redirect_to" value="account.php">
            
            <div style="position: relative;">
                <input type="password" name="confirm_password" id="confirm_password" placeholder="Enter your password" required style="padding-right: 40px;">
                
                <!-- Show/Hide Button -->
                <button type="button" onclick="togglePasswordVisibility()" style="
                    position: absolute;
                    right: 10px;
                    top: 50%;
                    transform: translateY(-50%);
                    background: none;
                    border: none;
                    cursor: pointer;
                    font-size: 0.9em;
                    color: #333;
                ">👁</button>
            </div>
            
            <div class="button-group">
                <button type="submit">Confirm</button>
                <button type="button" onclick="closePasswordPopup()">Cancel</button>
            </div>
        </form>
    </div>
</div>


<div class="container">
    <h2>📋 Order List</h2>  

    <?php if ($result->num_rows > 0): ?>
        <table>
            <tr>
                <th>Customer</th>
                <th>Event Date</th>
                <th>Guests No.</th>
                <th>Address</th>    
                <th>Event Type
                <th>Selected Dishes </th>
                <th>Selected Desserts</th>
                <th>Status</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['order_date']) ?></td>
                    <td><?= htmlspecialchars($row['guest_count']) ?></td>
                    <td><?= htmlspecialchars($row['address']) ?></td>
                    <td><?= htmlspecialchars($row['event_type']) ?></td>
                    
                   
                    <td>
                        <?php 
                        // Debugging the value of dishes
                        echo nl2br(htmlspecialchars($row['selected_dishes'])); 
                        ?>
                    </td>
                    <td>
                        <?php 
                        // Debugging the value of desserts
                        echo nl2br(htmlspecialchars($row['selected_desserts'])); 
                        ?>
                    </td>
                    <td>
                        
                            <?php if ($row['status'] === 'done'): ?>
    <span class="status-paid">✅ Order Completed</span><br>
    <?php if (empty($row['review'])): ?>
        <button class="pay-btn" onclick="openReviewModal(<?= $row['id'] ?>)">✍️ Write Review</button>
    <?php else: ?>
        <span style="color: #ccc; font-style: italic;">📝 Review submitted</span>
    <?php endif; ?>


                        <?php elseif ($row['status'] === 'pending payment' && $row['payment_method'] === 'GCash'): ?>
                            <span class="status-pending">⏳ Pending GCash Payment Verification</span>
                        <?php elseif (!empty($row['payment_method'])): ?>
                            <span class="status-paid">Paid (<?= htmlspecialchars($row['payment_method']) ?>)</span>
                        <?php else: ?>
                            <span class="status-pending">Pending</span>
                        <?php endif; ?>
                    </td>
                </tr>

                <?php if (empty($row['payment_method'])): ?>
                    <tr>
                        <td colspan="9">
                            <div class="pay-section">
                                <strong>💳 Payment Details:</strong>
                                <form method="post" action="submit_payment.php" id="payment-form-<?= $row['id'] ?>">
                                    <input type="hidden" name="order_id" value="<?= $row['id'] ?>">

                                    <div class="card-section" id="card-section-<?= $row['id'] ?>" style="margin-top: 10px; display:none;">
                                        <label>Card Number:</label>
                                        <input type="text" name="card_number" id="card-input-<?= $row['id'] ?>" placeholder="xxxx-xxxx-xxxx-xxxx">
                                    </div>

                                    <label style="margin-top: 10px;">Payment Method:</label>
                                    <select name="payment_method" id="payment-method-<?= $row['id'] ?>" required onchange="handlePaymentMethodChange(<?= $row['id'] ?>)">
                                        <option value="">Select</option>
                                        <option value="GCash">GCash</option>
                                        <option value="Maya">Maya</option>
                                    </select>

                                    <div class="gcash-note" id="gcash-note-<?= $row['id'] ?>" style="display:none; text-align: center;">
                                        <p><strong>📱 PAY / SCAN THIS QR CODE TO PROCEED PAYMENT (GCASH)</strong></p>
                                        <img src="gcash.png" alt="GCash QR Code" style="max-width: 200px; border: 2px solid #333; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.3); margin: 10px auto;">
                                        <p>GCash Number: <strong>099999999999</strong></p>
                                    </div>

                                    <div class="gcash-note" id="maya-note-<?= $row['id'] ?>" style="display:none; text-align: center;">
                                        <p><strong>💳 PAY / SCAN THIS QR CODE TO PROCEED PAYMENT (MAYA)</strong></p>
                                        <img src="maya_qr.png" alt="Maya QR Code" style="max-width: 200px; border: 2px solid #333; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.3); margin: 10px auto;">
                                        <p>Maya Number: <strong>098888888888</strong></p>
                                    </div>

                                    <button type="submit" class="pay-btn">Pay Now</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No orders found.</p>
    <?php endif; ?>
</div>

<?php include 'review_modal.php'; ?>

<script>
    function togglePasswordVisibility() {
    const input = document.getElementById('confirm_password');
    const type = input.getAttribute('type');
    input.setAttribute('type', type === 'password' ? 'text' : 'password');
}
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

    document.addEventListener('DOMContentLoaded', function () {
    const accountLink = document.querySelector('.account-link');
    if (accountLink) {
        accountLink.addEventListener('click', function (e) {
            e.preventDefault();
            document.getElementById('passwordModal').style.display = 'flex';
        });
    }
});

    function openReviewModal(orderId) {
    document.getElementById('reviewOrderId').value = orderId;
    document.getElementById('reviewModal').style.display = 'flex';
}

function closeReviewModal() {
    document.getElementById('reviewModal').style.display = 'none';
}


function handlePaymentMethodChange(orderId) {
    const method = document.getElementById('payment-method-' + orderId).value;
    const gcashNote = document.getElementById('gcash-note-' + orderId);
    const mayaNote = document.getElementById('maya-note-' + orderId);

    if (method === '') {
        gcashNote.style.display = 'none';
        mayaNote.style.display = 'none';
    } else if (method === 'GCash') {
        gcashNote.style.display = 'block';
        mayaNote.style.display = 'none';
    } else if (method === 'Maya') {
        gcashNote.style.display = 'none';
        mayaNote.style.display = 'block';
    }
}

</script>

</body>
</html>

<?php $conn->close(); ?>
