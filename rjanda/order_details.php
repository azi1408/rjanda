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
// Fetch only orders that belong to the current user
$stmt = $conn->prepare("SELECT * FROM catering_orders WHERE user_id = ? ORDER BY created_at DESC");
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
        <li><a href="account.php">Account Settings</a></li>
        <li><a href="orders.php">Packages</a></li>
    </ul>
</nav>

<div class="container">
    <h2>📋 Order List</h2>  

    <?php if ($result->num_rows > 0): ?>
        <table>
            <tr>
                <th>Customer</th>
                <th>Event Date</th>
                <th>Guests</th>
                <th>Address</th>    
                <th>Package</th>
                <th>Dishes</th>
                <th>Desserts</th>
                <th>Status</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['events_date']) ?></td>
                    <td><?= htmlspecialchars($row['guests']) ?></td>
                    <td><?= htmlspecialchars($row['address']) ?></td>
                    <td><?= htmlspecialchars($row['package_name']) ?></td>
                   
                    <td>
                        <?php 
                        // Debugging the value of dishes
                        echo nl2br(htmlspecialchars($row['dishes'])); 
                        ?>
                    </td>
                    <td>
                        <?php 
                        // Debugging the value of desserts
                        echo nl2br(htmlspecialchars($row['desserts'])); 
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

                                    <div class="card-section" id="card-section-<?= $row['id'] ?>" style="margin-top: 10px;">
                                        <label>Card Number:</label>
                                        <input type="text" name="card_number" id="card-input-<?= $row['id'] ?>" placeholder="xxxx-xxxx-xxxx-xxxx" required>
                                    </div>

                                    <label style="margin-top: 10px;">Payment Method:</label>
                                    <select name="payment_method" id="payment-method-<?= $row['id'] ?>" required onchange="handlePaymentMethodChange(<?= $row['id'] ?>)">
                                        <option value="">Select</option>
                                        <option value="GCash">GCash</option>
                                        <option value="Bank Transfer">Bank Transfer</option>
                                    </select>

                                    <div class="gcash-note" id="gcash-note-<?= $row['id'] ?>" style="display:none; text-align: center;">
                                        <p><strong>📱 PAY / SCAN THIS QR CODE TO PROCEED PAYMENT</strong></p>
                                        <img src="gcash.png" alt="GCash QR Code" style="max-width: 200px; border: 2px solid #333; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.3); margin: 10px auto;">
                                        <p>GCash Number: <strong>099999999999</strong></p>
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
<!-- Review Modal -->
<div id="reviewModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%;
    background:rgba(0,0,0,0.6); z-index:9999; justify-content:center; align-items:center;">
    <div style="background:#fff; color:#000; padding:30px; border-radius:12px; max-width:500px; width:90%;">
        <h3 style="margin-top:0;">📝 Write a Review</h3>
        <form method="post" action="submit_review.php">
            <input type="hidden" name="order_id" id="reviewOrderId">
            <textarea name="review" placeholder="Write your feedback..." rows="5" style="width:100%; border-radius:8px; padding:10px;" required></textarea>
            <br><br>
            <button type="submit" class="pay-btn">Submit Review</button>
            <button type="button" class="pay-btn cancel" onclick="closeReviewModal()">Cancel</button>
        </form>
    </div>
</div>

<script>
    function openReviewModal(orderId) {
    document.getElementById('reviewOrderId').value = orderId;
    document.getElementById('reviewModal').style.display = 'flex';
}

function closeReviewModal() {
    document.getElementById('reviewModal').style.display = 'none';
}


function handlePaymentMethodChange(orderId) {
    const method = document.getElementById('payment-method-' + orderId).value;
    const cardSection = document.getElementById('card-section-' + orderId);
    const cardInput = document.getElementById('card-input-' + orderId);
    const gcashNote = document.getElementById('gcash-note-' + orderId);

    if (method === 'GCash') {
        cardSection.style.display = 'none';
        cardInput.removeAttribute('required');
        gcashNote.style.display = 'block';
    } else if (method === 'Cash on Delivery') {
        cardSection.style.display = 'none';
        cardInput.removeAttribute('required');
        gcashNote.style.display = 'none';
    } else {
        cardSection.style.display = 'block';
        cardInput.setAttribute('required', 'required');
        gcashNote.style.display = 'none';
    }
}

</script>

</body>
</html>

<?php $conn->close(); ?>
