<?php
session_start();
include('connection.php');

// Simple admin check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user name from database
$query = "SELECT name FROM registertb WHERE userid = ?";
$stmt = $conn->prepare($query);

if (!$stmt) {
    die("SQL prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user) {
    $user_name = $user['name'];
} else {
    $user_name = "Admin";
}

$stmt->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Order Management</title>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCbjR1u-y0-MTmnemgPip8GRyv2msVZDC0"></script>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: #2e2e2e;
            color: #f1f1f1;
            position: relative;
        }

        .navbar {
            background-color: #111;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }

        .navbar-left {
            display: flex;
            align-items: center;
        }

        .greeting {
            font-size: 1.2rem;
            color: #f7f2e9;
            margin-left: 15px;
        }

        .logo-img {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
            border: 2px solid beige;
        }

        .container {
            max-width: 1200px;
            margin: 80px auto 20px;
            padding: 15px;
            position: relative;
            overflow-x: auto;
        }

        h2 {
            color: #d4b895;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #3e3e3e;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 20px;
            min-width: 1200px; /* Ensure table doesn't shrink below this width */
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #3e3e3e;
        }

        th {
            background-color: #1a1a1a;
            color: #d4b895;
            font-weight: 500;
            position: sticky;
            top: 0;
            white-space: nowrap;
        }

        tr:hover {
            background-color: #323232;
        }

        /* Specific column widths */
        th:nth-child(1), td:nth-child(1) { min-width: 120px; } /* Order ID + Location button */
        th:nth-child(2), td:nth-child(2) { min-width: 120px; } /* Customer Name */
        th:nth-child(3), td:nth-child(3) { min-width: 100px; } /* Event Date */
        th:nth-child(4), td:nth-child(4) { min-width: 80px; } /* Guests */
        th:nth-child(5), td:nth-child(5) { min-width: 150px; } /* Address */
        th:nth-child(6), td:nth-child(6) { min-width: 100px; } /* Event Type */
        th:nth-child(7), td:nth-child(7) { min-width: 150px; } /* Selected Dishes */
        th:nth-child(8), td:nth-child(8) { min-width: 150px; } /* Selected Desserts */
        th:nth-child(9), td:nth-child(9) { min-width: 120px; } /* Status */
        th:nth-child(10), td:nth-child(10) { min-width: 100px; } /* Payment Method */
        th:nth-child(11), td:nth-child(11) { min-width: 120px; } /* Action */

        /* Action buttons styling */
        td form {
            display: inline-block;
            margin: 2px;
        }

        td button {
            background-color: #d4b895;
            color: #222;
            border: none;
            padding: 6px 12px;
            font-size: 0.9em;
            font-weight: 500;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        td button:hover {
            background-color: #caa97a;
        }

        /* Status styling */
        .status-cell {
            padding: 8px 12px;
            border-radius: 6px;
            font-weight: 500;
            font-size: 0.9em;
        }

        .status-cell.paid {
            background-color: rgba(76, 175, 80, 0.2);
            color: #4caf50;
        }

        .status-cell.pending {
            background-color: rgba(255, 152, 0, 0.2);
            color: #ff9800;
        }

        .status-cell.done {
            background-color: rgba(76, 175, 80, 0.3);
            color: #4caf50;
        }

        /* Location button styles */
        .location-btn {
            background-color: #2196F3;
            color: white;
            padding: 4px 8px;
            font-size: 0.8em;
            margin-left: 8px;
            border-radius: 4px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: background-color 0.3s ease;
        }

        .location-btn:hover {
            background-color: #1976D2;
        }

        /* Location modal styles */
        .location-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .location-modal-content {
            background: #3e3e3e;
            padding: 20px;
            border-radius: 10px;
            width: 80%;
            max-width: 800px;
            position: relative;
        }

        .location-modal-close {
            position: absolute;
            right: 20px;
            top: 20px;
            color: #fff;
            font-size: 24px;
            cursor: pointer;
            z-index: 1;
        }

        #locationMap {
            width: 100%;
            height: 400px;
            border-radius: 8px;
            margin-top: 10px;
        }

        .location-info {
            color: #fff;
            margin-top: 10px;
            padding: 10px;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 4px;
        }

        /* Navigation menu styles */
        .menu-toggle {
            font-size: 24px;
            background: none;
            border: none;
            color: beige;
            cursor: pointer;
            display: block;
        }

        .nav-links {
            display: none;
            flex-direction: column;
            position: fixed;
            top: 60px;
            right: 20px;
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

        /* Review modal styles */
        #reviewModal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        #reviewModal > div {
            background: #3e3e3e;
            padding: 20px;
            border-radius: 10px;
            width: 400px;
            max-width: 90%;
        }

        #reviewModal h3 {
            color: #d4b895;
            margin-top: 0;
        }

        #reviewModal p {
            color: #f1f1f1;
            white-space: pre-wrap;
        }

        #reviewModal button {
            background: #d4b895;
            color: #000;
            border: none;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
            margin-top: 15px;
        }

        @media screen and (max-width: 768px) {
            .container {
                padding: 10px;
            }

            table {
                font-size: 0.9em;
            }

            td button {
                padding: 4px 8px;
                font-size: 0.8em;
            }

            .location-btn {
                padding: 3px 6px;
                font-size: 0.7em;
            }
        }
    </style>
</head>
<body>

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


<div class="container">
    <h2>📋 Order Management</h2>
    
    <!-- Add Delete Options Button -->
    <div style="margin-bottom: 20px;">
        <button onclick="openDeleteOptionsModal()" style="background-color: #f44336; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: 500;">
            🗑️ Delete Orders by Period
        </button>
    </div>

    <!-- Delete Options Modal -->
    <div id="deleteOptionsModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.7); z-index: 1000; justify-content: center; align-items: center;">
        <div style="background: #3e3e3e; padding: 30px; border-radius: 10px; width: 400px; max-width: 90%;">
            <h3 style="color: #d4b895; margin-top: 0;">Delete Orders by Period</h3>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; color: #fff; margin-bottom: 8px;">Select Period Type:</label>
                <select id="periodType" style="width: 100%; padding: 8px; background: #2a2a2a; color: #fff; border: 1px solid #444; border-radius: 4px;">
                    <option value="month">Last Month</option>
                    <option value="year">Last Year</option>
                </select>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button onclick="closeDeleteOptionsModal()" style="background: #666; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer;">
                    Cancel
                </button>
                <button onclick="deleteOrdersByPeriod()" style="background: #f44336; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer;">
                    Delete Orders
                </button>
            </div>
        </div>
    </div>

<?php
$order_query = "SELECT * FROM orders ORDER BY created_at DESC, order_date DESC"; // Sort by created_at first, then order_date
$result = $conn->query($order_query);

if (!$result) {
    die("Failed to fetch orders: " . $conn->error);
}

?>
    <table>
        <tr>
            <th>Order ID</th>
            <th>Customer Name</th>
            <th>Event Date</th>
            <th>Guests</th>
            <th>Address</th>    
            <th>Event Type</th>
            <th>Selected Dishes</th>
            <th>Selected Desserts</th>
            <th>Status</th>
            <th>Payment Method</th>
            <th>Action</th>
        </tr>

        <?php while ($row = $result->fetch_assoc()): ?>
            <tr class="<?php 
                if ($row['status'] === 'done') echo 'status-done';
                elseif ($row['status'] === 'paid') echo 'status-paid';
                else echo 'status-pending';
            ?>">
                <td>
                    <?= htmlspecialchars($row['id']) ?>
                    <button class="location-btn" onclick="showLocation(<?= $row['lat'] ?>, <?= $row['lng'] ?>, '<?= htmlspecialchars($row['address']) ?>')">
                        📍 View Location
                    </button>
                </td>
                <td><?= htmlspecialchars($row['customer_name']) ?></td>
                <td><?= htmlspecialchars($row['order_date']) ?></td>
                <td><?= htmlspecialchars($row['guest_count']) ?></td>
                <td><?= htmlspecialchars($row['address']) ?></td>
                <td><?= htmlspecialchars($row['event_type']) ?></td>
                <td><?= htmlspecialchars($row['selected_dishes']) ?></td>    
                <td><?= htmlspecialchars($row['selected_desserts']) ?></td>

                <!-- STATUS -->
                <td class="status-cell <?php 
                    if ($row['status'] === 'done') echo 'done';
                    elseif ($row['status'] === 'paid') echo 'paid';
                    else echo 'pending';
                ?>">
                    <?php if ($row['status'] === 'done'): ?>
                        <span>✅ Order Completed</span>
                    <?php elseif ($row['status'] === 'paid'): ?>
                        <span>✔ Paid</span>
                    <?php elseif ($row['status'] === 'pending payment' && $row['payment_method'] === 'GCash'): ?>
                        <span>⏳ Pending GCash Payment</span>
                    <?php elseif (!empty($row['payment_method'])): ?>
                        <span>⏳ Awaiting Confirmation</span>
                    <?php else: ?>
                        <span>⏳ Pending</span>
                    <?php endif; ?>
                </td>

                <!-- PAYMENT METHOD -->
                <td><?= htmlspecialchars($row['payment_method']) ?: 'N/A' ?></td>

                <!-- ACTIONS -->
                <td>
                    <?php if ($row['status'] === 'pending payment' && $row['payment_method'] === 'GCash'): ?>
                        <form method="POST" action="admin_payment.php" style="display:inline-block;">
                            <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
                            <button type="submit" name="mark_paid">Verify Payment</button>
                        </form>
                    <?php elseif (empty($row['payment_method'])): ?>
                        <form method="POST" action="admin_payment.php" style="display:inline-block;">
                            <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
                            <button type="submit" name="mark_paid">Mark as Paid</button>
                        </form>
                    <?php else: ?>
                        <form method="POST" action="admin_payment.php" style="display:inline-block;">
                            <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
                            <button type="submit" name="mark_unpaid">Mark as Unpaid</button>
                        </form>

                        <?php if ($row['status'] !== 'done'): ?>
                            <form method="POST" action="admin_payment.php" style="display:inline-block;">
                                <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
                                <button type="submit" name="mark_done">Mark as Done</button>
                            </form>
                        <?php else: ?>
                            <form method="POST" action="admin_payment.php" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this completed order?');">
                                <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
                                <button type="submit" name="delete_order">Delete</button>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>

<script>
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

    function openReviewModal(orderId, reviewText) {
        document.getElementById('reviewContent').textContent = reviewText;
        document.getElementById('reviewModal').style.display = 'flex';
    }

    function closeReviewModal() {
        document.getElementById('reviewModal').style.display = 'none';
    }

    let locationMap;
    let locationMarker;

    function showLocation(lat, lng, address) {
        const modal = document.getElementById('locationModal');
        const mapDiv = document.getElementById('locationMap');
        const addressSpan = document.getElementById('locationAddress');
        const coordinatesSpan = document.getElementById('locationCoordinates');

        // Show modal
        modal.style.display = 'flex';
        
        // Update address and coordinates
        addressSpan.textContent = address;
        coordinatesSpan.textContent = `${lat.toFixed(7)}, ${lng.toFixed(7)}`;

        // Initialize map
        const location = { lat: parseFloat(lat), lng: parseFloat(lng) };
        
        if (!locationMap) {
            locationMap = new google.maps.Map(mapDiv, {
                center: location,
                zoom: 15,
                mapTypeControl: true,
                streetViewControl: true,
                fullscreenControl: true
            });
        } else {
            locationMap.setCenter(location);
        }

        // Add or update marker
        if (locationMarker) {
            locationMarker.setPosition(location);
        } else {
            locationMarker = new google.maps.Marker({
                position: location,
                map: locationMap,
                animation: google.maps.Animation.DROP
            });
        }

        // Add info window
        const infoWindow = new google.maps.InfoWindow({
            content: `<strong>Order Location</strong><br>${address}`
        });
        infoWindow.open(locationMap, locationMarker);
    }

    function closeLocationModal() {
        document.getElementById('locationModal').style.display = 'none';
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('locationModal');
        if (event.target === modal) {
            closeLocationModal();
        }
    }

    function openDeleteOptionsModal() {
        document.getElementById('deleteOptionsModal').style.display = 'flex';
    }

    function closeDeleteOptionsModal() {
        document.getElementById('deleteOptionsModal').style.display = 'none';
    }

    function deleteOrdersByPeriod() {
        if (confirm('Are you sure you want to delete all orders from the selected period? This action cannot be undone.')) {
            const periodType = document.getElementById('periodType').value;
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="delete_orders_by_period" value="1">
                <input type="hidden" name="period_type" value="${periodType}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('deleteOptionsModal');
        if (event.target === modal) {
            closeDeleteOptionsModal();
        }
    }
</script>


<!-- Review Modal -->
<div id="reviewModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); justify-content:center; align-items:center;">
    <div style="background-color:#3e3e3e; padding:20px; border-radius:10px; width:400px; max-width:90%;">
        <h3 style="color:#d4b895;">📝 Customer Review</h3>
        <p id="reviewContent" style="color:#f1f1f1; white-space:pre-wrap;"></p>
        <div style="margin-top:15px; text-align:right;">
            <button onclick="closeReviewModal()" style="background:#d4b895; color:#000; border:none; padding:8px 12px; border-radius:6px;">Close</button>
        </div>
    </div>
</div>

<!-- Add the location modal at the end of the body -->
<div id="locationModal" class="location-modal">
    <div class="location-modal-content">
        <span class="location-modal-close" onclick="closeLocationModal()">&times;</span>
        <h3 style="color: #d4b895; margin-top: 0;">📍 Order Location</h3>
        <div id="locationMap"></div>
        <div class="location-info">
            <p><strong>Address:</strong> <span id="locationAddress"></span></p>
            <p><strong>Coordinates:</strong> <span id="locationCoordinates"></span></p>
        </div>
    </div>
</div>

</body>
</html>

<?php $conn->close(); ?>
