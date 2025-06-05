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

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: rgba(0, 0, 0, 0.6);
            padding: 1rem 2rem;
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

        /* Hamburger Menu Styles */
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
            background-color: #4CAF50 !important;
            color: white !important;
            padding: 12px 30px !important;
            font-size: 16px !important;
            border: none !important;
            border-radius: 8px !important;
            cursor: pointer !important;
            transition: background-color 0.3s !important;
            margin: 20px auto !important;
            display: block !important;
        }

        .pay-btn:hover {
            background-color: #45a049 !important;
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

.pay-section {
    position: relative;
    padding-bottom: 60px;
}

    </style>
</head>
<body>

<nav>
    <div class="nav-left">
        <img src="logo.jfif" alt="Logo" class="logo-img">
        <span style="color: white; font-size: 1.2em; font-weight: bold;">RJ & A Catering Services</span>
    </div>
    <button class="menu-toggle" onclick="toggleMenu()">☰</button>
    <ul id="navLinks" class="nav-links">
        <?php
        if (isset($_SESSION['user_id'])) {
            echo '<a href="home.php">Home</a>';
        }

        if (isset($_SESSION['user_id'])) {
            echo '<a href="orders.php">Packages</a>';
        }

        if (isset($_SESSION['user_id'])) {
            echo '<a href="order_details.php">Payment Methods</a>';
        }

        if (isset($_SESSION['user_id'])) {
            echo '<a href="show_reviews.php">Reviews</a>';
        }

        if (isset($_SESSION['user_id'])) {
            echo '<a href="chat.php">Chat with Admin</a>';
        }

        if (isset($_SESSION['user_id'])) {
            echo '<a href="logout.php">Log Out</a>';
        } else {
            echo '<a href="index.php">Log In</a>';
        }
        ?>
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

                                    <label style="margin-top: 10px;">Payment Method:</label>
                                    <select name="payment_method" id="payment-method-<?= $row['id'] ?>" onchange="handlePaymentMethodChange(<?= $row['id'] ?>)">
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
                                        <img src="maya.webp" alt="Maya QR Code" style="max-width: 200px; border: 2px solid #333; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.3); margin: 10px auto;">
                                        <p>Maya Number: <strong>098888888888</strong></p>
                                    </div>

                                    <div id="pay-button-<?= $row['id'] ?>" style="text-align: center; margin-top: 20px; display: none;">
                                        <button type="submit" class="pay-btn" style="padding: 12px 30px; font-size: 16px; background-color: #4CAF50; color: white; border: none; border-radius: 8px; cursor: pointer;">
                                            Pay Now
                                        </button>
                                    </div>
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
    // Add this at the beginning of your script section
    document.addEventListener('DOMContentLoaded', function() {
        // Add form submission handlers to all payment forms
        const paymentForms = document.querySelectorAll('form[action="submit_payment.php"]');
        paymentForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault(); // Prevent default submission
                
                // Get form data
                const formData = new FormData(this);
                const paymentMethod = formData.get('payment_method');
                const proofOfPayment = formData.get('proof_of_payment');
                
                // Validate payment method
                if (!paymentMethod) {
                    alert('Please select a payment method');
                    return false;
                }
                
                // Validate proof of payment
                if (!proofOfPayment || !proofOfPayment.name) {
                    alert('Please upload proof of payment');
                    return false;
                }
                
                // If all validations pass, submit the form
                this.submit();
            });
        });
    });

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
    const payButton = document.getElementById('pay-button-' + orderId);

    // Hide all elements first
    gcashNote.style.display = 'none';
    mayaNote.style.display = 'none';
    payButton.style.display = 'none';

    // Show relevant elements based on selection
    if (method === 'GCash') {
        gcashNote.style.display = 'block';
        payButton.style.display = 'block';
    } else if (method === 'Maya') {
        mayaNote.style.display = 'block';
        payButton.style.display = 'block';
    }
}

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

// Add this new function for image preview
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    const previewImg = preview.querySelector('img');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.style.display = 'block';
        }
        
        reader.readAsDataURL(input.files[0]);
    }
}

// Update the form submission function
function submitPaymentForm(form) {
    const formData = new FormData(form);
    const paymentMethod = formData.get('payment_method');
    const proofOfPayment = formData.get('proof_of_payment');

    if (!paymentMethod) {
        alert('Please select a payment method');
        return false;
    }

    if (!proofOfPayment || !proofOfPayment.name) {
        alert('Please select a proof of payment image');
        return false;
    }

    // If all validations pass, submit the form
    form.submit();
    return true;
}

// Add form submission handler
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form[action="submit_payment.php"]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const file = formData.get('proof_of_payment');
            
            console.log('Form data:', {
                order_id: formData.get('order_id'),
                payment_method: formData.get('payment_method'),
                file: file
            });
            
            // Submit the form
            this.submit();
        });
    });
});

</script>

</body>
</html>

<?php $conn->close(); ?>
