<?php
session_start();
include('connection.php');

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please log in to view packages.'); window.location.href='index.php';</script>";
    exit;
}

$raw_result = mysqli_query($conn, "SELECT * FROM packages ORDER BY created_at DESC");
$packages = [];
while ($row = mysqli_fetch_assoc($raw_result)) {
    $packages[] = $row;
}

$unavailable_dates = [];
$availability_sql = "SELECT date FROM date_availability WHERE status = 'unavailable'";
$availability_stmt = $conn->prepare($availability_sql);
$availability_stmt->execute();
$availability_result = $availability_stmt->get_result();
while ($row = $availability_result->fetch_assoc()) {
    $unavailable_dates[] = $row['date'];
}
$availability_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Available Packages</title>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCbjR1u-y0-MTmnemgPip8GRyv2msVZDC0&v=weekly"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.2.0/fullcalendar.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.2.0/fullcalendar.min.js"></script>
    <style>
        body {
        font-family: 'Segoe UI', sans-serif;
        background: linear-gradient(to bottom right, #323232, #d4b895);
        color: #fff;
        margin: 0;
        padding: 0;
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
        padding: 40px 20px;
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
    }

    .package {
        background: white;
        border: 1px solid black;
        color: black;
        border-radius: 15px;
        box-shadow: 0 0 15px black;
        padding: 20px;
        width: 300px;
        margin: 20px;
        position: relative;
    }

    .package h3 {
        margin-top: 0;
    }

    .package p {
        margin: 5px 0;
    }

    .order-btn {
        background-color: #4caf50;
        color: white;
        padding: 10px 15px;
        text-decoration: none;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: bold;
        margin-top: 10px;
        display: block;
    }

    .order-btn:hover {
        background-color: #388e3c;
    }

    .no-packages {
        background-color: #333;
        color: #fff;
        padding: 20px;
        border-radius: 10px;
        width: 90%;
        text-align: center;
        font-size: 1.1em;
        margin: 20px auto;
    }

    footer {
        background-color: #222;
        color: #a5d6a7;
        text-align: center;
        padding: 15px 0;
        position: fixed;
        width: 100%;
        bottom: 0;
    }

    /* ✅ Fix: Modal styling should be here, not in nested <style> */
    .modal {
        position: fixed;
        z-index: 999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0,0,0,0.6);
    }

    .modal-content {
        background-color: #fefefe;
        color: black;
        margin: 10% auto;
        padding: 20px;
        border-radius: 15px;
        width: 400px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.5);
        position: relative;
    }
    .modal-content form label {
    display: block;
    margin-top: 12px;
    font-weight: 600;
    font-size: 14px;
}

.modal-content form input[type="text"],
.modal-content form input[type="number"],
.modal-content form textarea {
    width: 100%;
    padding: 10px 12px;
    margin-top: 5px;
    border: 1px solid #ccc;
    border-radius: 10px;
    box-sizing: border-box;
    font-size: 14px;
    font-family: 'Segoe UI', sans-serif;
    background-color: #f8f8f8;
    color: #333;
    transition: border 0.3s ease, box-shadow 0.3s ease;
}

.modal-content form input[type="text"]:focus,
.modal-content form input[type="number"]:focus,
.modal-content form textarea:focus {
    border-color: #4caf50;
    box-shadow: 0 0 5px rgba(76, 175, 80, 0.4);
    outline: none;
}

.modal-content form textarea {
    resize: vertical;
    min-height: 80px;
}
.checkbox-wrapper {
    display: flex;
    align-items: center;
    margin-bottom: 8px;
    font-size: 15px;
    font-family: 'Segoe UI', sans-serif;
}

.checkbox-wrapper input[type="checkbox"] {
    margin-right: 10px;
    transform: scale(1.2);
    cursor: pointer;
}

.checkbox-wrapper label {
    cursor: pointer;
}


    .close {
        color: #aaa;
        float: right;
        font-size: 26px;
        font-weight: bold;
        cursor: pointer;
    }
    .container {
            margin: 20px;
            font-family: Arial, sans-serif;
        }

        .calendar {
            margin-top: 20px;
        }

        .success-message {
            color: green;
            margin-top: 20px;
        }

        .error-message {
            color: red;
            margin-top: 20px;
        }

        .summary-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            gap: 10px;
        }

        .cancel-btn, .confirm-btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            flex: 1;
            transition: all 0.3s ease;
        }

        .cancel-btn {
            background: #f44336;
            color: white;
        }

        .confirm-btn {
            background: #4CAF50;
            color: white;
        }

        .cancel-btn:hover {
            background: #d32f2f;
        }

        .confirm-btn:hover {
            background: #388e3c;
        }

        #orderSummary {
            background: #f8f8f8;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }

        #orderSummary p {
            margin: 8px 0;
            color: #333;
        }

        #orderSummary .total-price {
            font-size: 1.2em;
            font-weight: bold;
            color: #4CAF50;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
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

    <div class="container">
        <?php if (count($packages) > 0): ?>
            <?php foreach ($packages as $row): ?>
                <div class="package">
                    <h3><?= htmlspecialchars($row['package_name']) ?></h3>
                    <p><strong>Dishes:</strong> <?= nl2br(htmlspecialchars($row['dishes'])) ?></p>
                    <p><strong>Desserts:</strong> <?= nl2br(htmlspecialchars($row['desserts'])) ?></p>
                    <p><strong>Price per Guest:</strong> ₱<?= number_format($row['price'], 2) ?></p>
                    <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($row['description'])) ?></p>
                    <p><strong>Max Dishes:</strong> <?= $row['max_dishes'] ?> | <strong>Max Desserts:</strong> <?= $row['max_desserts'] ?></p>
                    <button type="button" class="order-btn" onclick="openModal(<?= $row['id'] ?>)">Make an Order</button>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-packages">No catering packages are currently available. Please check back later!</div>
        <?php endif; ?>
    </div>


    <!-- Modal -->
    <div id="orderModal" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <form id="orderForm" onsubmit="return showOrderSummary(event)">
                <input type="hidden" name="package_id" id="package_id">
                <h2>Submit Order</h2>
                <label>Name:</label>
                <input type="text" name="customer_name" required>

                <label>Address:</label>
                <textarea name="address" required></textarea>

                <label>Type of Event:</label>
                <input type="text" name="event_type" required>

                <label>Number of Guests:</label>
                <input type="number" name="guest_count" required>

                <label>Event Location:</label>
                <div id="map" style="height: 300px; width: 100%; border-radius: 10px;"></div>
                <input type="hidden" id="lat" name="lat">
                <input type="hidden" id="lng" name="lng">

                <label>Select Dishes (<span id="dishLimit">0</span> max):</label>
                <div id="dishOptions"></div>

                <label>Select Desserts (<span id="dessertLimit">0</span> max):</label>
                <div id="dessertOptions"></div>
                
                <div style="background-color: #fff3cd; color: #856404; padding: 10px; border-radius: 8px; margin: 15px 0; border: 1px solid #ffeeba;">
                    <strong>Note:</strong> Please select a date at least 7 days in advance from today's date.
                </div>
                
                <div id="calendar" class="calendar"></div>
                <div style="margin-top: 15px; padding: 10px; background-color: #f8f8f8; border-radius: 8px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Selected Date:</label>
                    <div id="selectedDateDisplay" style="color: #4CAF50; font-size: 16px; font-weight: bold;">No date selected</div>
                </div>
                <input type="hidden" name="order_date" id="order_date" required>

                <button type="submit">Review Order</button>
            </form>
        </div>
    </div>

    <!-- Summary Modal -->
    <div id="summaryModal" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="close" onclick="closeSummaryModal()">&times;</span>
            <h2>Order Summary</h2>
            <div id="orderSummary"></div>
            <div class="summary-actions">
                <button onclick="closeSummaryModal()" class="cancel-btn">Edit Order</button>
                <button onclick="submitOrder()" class="confirm-btn">Confirm Order</button>
            </div>
        </div>
    </div>

    <script>
        let map;
        let marker;
        const packages = <?= json_encode(array_map(function($row) {
            $row['dishes'] = array_map('trim', explode(',', $row['dishes']));
            $row['desserts'] = array_map('trim', explode(',', $row['desserts']));
            return $row;
        }, $packages)) ?>;

        function openModal(packageId) {
            const pack = packages.find(p => p.id == packageId);
            document.getElementById('orderModal').style.display = 'block';
            document.getElementById('package_id').value = packageId;
            dishLimit.innerText = pack.max_dishes;
            dessertLimit.innerText = pack.max_desserts;

            document.getElementById('dishOptions').innerHTML = pack.dishes.map((dish, i) =>
                `<div class='checkbox-wrapper'><input type='checkbox' name='selected_dishes[]' value='${dish}' id='dish_${i}' onclick='enforceLimit("dishOptions", ${pack.max_dishes})'><label for='dish_${i}'>${dish}</label></div>`
            ).join('');

            document.getElementById('dessertOptions').innerHTML = pack.desserts.map((dessert, i) =>
                `<div class='checkbox-wrapper'><input type='checkbox' name='selected_desserts[]' value='${dessert}' id='dessert_${i}' onclick='enforceLimit("dessertOptions", ${pack.max_desserts})'><label for='dessert_${i}'>${dessert}</label></div>`
            ).join('');

            initMap();
            
            // Initialize calendar when modal opens
            const unavailableDates = <?= json_encode($unavailable_dates) ?>;
            const today = new Date();
            const minDate = new Date(today);
            minDate.setDate(today.getDate() + 7); // Set minimum date to 7 days from today
            
            // Update the calendar initialization
            $('#calendar').fullCalendar({
                header: { 
                    left: '',  
                    center: 'title',
                    right: ''  
                },
                defaultView: 'month',
                height: 400,
                contentHeight: 400,
                aspectRatio: 1.2,
                dayRender: function(date, cell) {
                    const currentDate = date.toDate();
                    const dateStr = date.format('YYYY-MM-DD');
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);
                    
                    // Remove any existing classes
                    cell.removeClass('unavailable too-soon selected-date');
                    
                    // Check if date is today
                    if (currentDate.getTime() === today.getTime()) {
                        cell.addClass('today');
                        // Add today indicator
                        cell.append('<div class="today-indicator"></div>');
                    }
                    // Check if date is too soon (less than 7 days from today)
                    else if (currentDate < minDate) {
                        cell.addClass('too-soon');
                    }
                    // Check if date is unavailable
                    else if (unavailableDates.includes(dateStr)) {
                        cell.addClass('unavailable');
                    }
                },
                dayClick: function(date, jsEvent, view) {
                    const currentDate = date.toDate();
                    const dateStr = date.format('YYYY-MM-DD');
                    
                    // Only allow selection if date is valid
                    if (currentDate >= minDate && !unavailableDates.includes(dateStr)) {
                        // Remove selected class from all days
                        $('.fc-day').removeClass('selected-date');
                        
                        // Add selected class to clicked day
                        $(jsEvent.target).closest('.fc-day').addClass('selected-date');
                        
                        // Update the selected date in the input field
                        document.getElementById('order_date').value = dateStr;
                        
                        // Update the selected date display
                        document.getElementById('selectedDateDisplay').textContent = date.format('MMMM D, YYYY');
                    }
                }
            });

            // Add some CSS for the selected date and submit button
            const style = document.createElement('style');
            style.textContent = `
                /* Calendar Container */
                .fc-view-container {
                    background: white;
                    border-radius: 8px;
                    overflow: hidden;
                }

                /* Calendar Header */
                .fc-header {
                    padding: 10px;
                    background: #f8f8f8;
                }

                /* Calendar Body */
                .fc-body {
                    background: white;
                }

                /* Calendar Grid */
                .fc-grid {
                    border: none !important;
                }

                /* Calendar Day Cells */
                .fc-day {
                    border: 1px solid #e0e0e0 !important;
                    min-height: 40px !important;
                    position: relative;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    padding: 0 !important;
                }

                .fc-day:hover:not(.disabled):not(.fc-other-month) {
                    background-color: #e8f5e9 !important;
                }

                /* Day Number */
                .fc-day-number {
                    position: relative;
                    font-size: 14px;
                    color: #333;
                    padding: 8px !important;
                    text-align: center;
                    width: 100%;
                    height: 100%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    z-index: 2;
                }

                /* Selected Date */
                .selected-date {
                    background-color: #4CAF50 !important;
                }

                .selected-date .fc-day-number {
                    color: white !important;
                    font-weight: bold;
                }

                /* Unavailable Dates */
                .fc-day.unavailable {
                    background-color: #ff4d4d !important;
                    cursor: not-allowed;
                }

                .fc-day.unavailable .fc-day-number {
                    color: white !important;
                }

                /* Too Soon Dates */
                .fc-day.too-soon {
                    background-color: #e0e0e0 !important;
                    opacity: 0.5;
                    cursor: not-allowed;
                }

                /* Today's Date */
                .fc-day.today {
                    background-color: transparent !important;
                }

                .fc-day.today .fc-day-number {
                    color: white !important;
                    font-weight: bold !important;
                    text-shadow: 0 1px 2px rgba(0,0,0,0.2) !important;
                }

                .today-indicator {
                    position: absolute;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    width: 32px !important;
                    height: 32px !important;
                    background: linear-gradient(135deg, #4CAF50, #45a049) !important;
                    border-radius: 50%;
                    z-index: 1;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.2) !important;
                    border: 2px solid #fff !important;
                }

                .fc-day.today .fc-day-number {
                    position: relative;
                    z-index: 2;
                }

                /* Other Month Dates */
                .fc-other-month {
                    background-color: #f8f8f8 !important;
                    cursor: default;
                }

                .fc-other-month .fc-day-number {
                    color: #999 !important;
                }

                /* Remove default FullCalendar styles */
                .fc-day.fc-today.fc-state-highlight {
                    background: none !important;
                }

                .fc-day.fc-today.fc-state-highlight > div {
                    background: none !important;
                }

                /* Ensure the entire cell is clickable */
                .fc-day > div {
                    height: 100% !important;
                    width: 100% !important;
                    display: flex !important;
                    align-items: center !important;
                    justify-content: center !important;
                }

                /* Calendar Header Title */
                .fc-header-title h2 {
                    font-size: 1.2em;
                    font-weight: bold;
                    color: #333;
                }

                /* Submit Button Styles */
                .modal-content form button[type="submit"] {
                    background: linear-gradient(to right, #4CAF50, #45a049);
                    color: white;
                    padding: 12px 24px;
                    border: none;
                    border-radius: 8px;
                    font-size: 16px;
                    font-weight: bold;
                    cursor: pointer;
                    width: 100%;
                    margin-top: 20px;
                    transition: all 0.3s ease;
                    text-transform: uppercase;
                    letter-spacing: 1px;
                    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                }

                .modal-content form button[type="submit"]:hover {
                    background: linear-gradient(to right, #45a049, #3d8b40);
                    transform: translateY(-2px);
                    box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
                }

                .modal-content form button[type="submit"]:active {
                    transform: translateY(0);
                    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                }
            `;
            document.head.appendChild(style);
        }

        function enforceLimit(containerId, limit) {
            const checkboxes = document.querySelectorAll(`#${containerId} input[type=checkbox]`);
            const checked = Array.from(checkboxes).filter(cb => cb.checked);
            checkboxes.forEach(cb => cb.disabled = checked.length >= limit && !cb.checked);
        }

        function closeModal() {
            document.getElementById('orderModal').style.display = 'none';
        }

        function validateOrderForm() {
            const dishChecked = document.querySelectorAll('input[name="selected_dishes[]"]:checked').length;
            const dessertChecked = document.querySelectorAll('input[name="selected_desserts[]"]:checked').length;
            const lat = document.getElementById('lat').value;
            const lng = document.getElementById('lng').value;
            
            if (!dishChecked || !dessertChecked) {
                alert("Please select required dishes and desserts.");
                return false;
            }
            
            if (!lat || !lng) {
                alert("Please select a location on the map.");
                return false;
            }
            
            return true;
        }

        function initMap() {
            const defaultLocation = { lat: 14.5995, lng: 120.9842 };
            map = new google.maps.Map(document.getElementById("map"), { 
                center: defaultLocation, 
                zoom: 12,
                mapTypeControl: true,
                streetViewControl: true,
                fullscreenControl: true
            });

            // Set initial coordinates
            document.getElementById('lat').value = defaultLocation.lat.toFixed(7);
            document.getElementById('lng').value = defaultLocation.lng.toFixed(7);

            // Add click listener to map
            map.addListener("click", function(e) {
                // Remove existing marker if any
                if (marker) {
                    marker.setMap(null);
                }
                
                // Create new marker at clicked location
                marker = new google.maps.Marker({
                    position: e.latLng,
                    map: map,
                    animation: google.maps.Animation.DROP
                });

                // Update hidden input fields with lat/lng values
                document.getElementById('lat').value = e.latLng.lat().toFixed(7);
                document.getElementById('lng').value = e.latLng.lng().toFixed(7);

                // Optional: Add info window to show coordinates
                const infoWindow = new google.maps.InfoWindow({
                    content: `Location: ${e.latLng.lat().toFixed(7)}, ${e.latLng.lng().toFixed(7)}`
                });
                infoWindow.open(map, marker);
            });

            // Add geolocation support
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const pos = {
                            lat: position.coords.latitude,
                            lng: position.coords.longitude
                        };
                        map.setCenter(pos);
                        
                        // Add marker at current location
                        if (marker) {
                            marker.setMap(null);
                        }
                        marker = new google.maps.Marker({
                            position: pos,
                            map: map,
                            animation: google.maps.Animation.DROP
                        });

                        // Update hidden input fields
                        document.getElementById('lat').value = pos.lat.toFixed(7);
                        document.getElementById('lng').value = pos.lng.toFixed(7);
                    },
                    () => {
                        console.log('Error: The Geolocation service failed.');
                        // Keep default coordinates if geolocation fails
                    }
                );
            }
        }

        $(function() {
            // Remove the calendar initialization from here since it's now in openModal
        });

        function showOrderSummary(event) {
            event.preventDefault();
            
            if (!validateOrderForm()) {
                return false;
            }
            
            const form = document.getElementById('orderForm');
            const formData = new FormData(form);
            const packageId = formData.get('package_id');
            const package = packages.find(p => p.id == packageId);
            
            // Calculate total price
            const guestCount = parseInt(formData.get('guest_count'));
            // Remove any commas and ensure proper parsing of the price
            const basePrice = parseFloat(package.price.toString().replace(/,/g, ''));
            const totalPrice = basePrice * guestCount;
            
            // Get selected dishes and desserts
            const selectedDishes = Array.from(formData.getAll('selected_dishes[]'));
            const selectedDesserts = Array.from(formData.getAll('selected_desserts[]'));
            
            // Create summary HTML
            const summaryHTML = `
                <p><strong>Package:</strong> ${package.package_name}</p>
                <p><strong>Customer Name:</strong> ${formData.get('customer_name')}</p>
                <p><strong>Event Type:</strong> ${formData.get('event_type')}</p>
                <p><strong>Number of Guests:</strong> ${guestCount}</p>
                <p><strong>Event Date:</strong> ${formData.get('order_date')}</p>
                <p><strong>Event Location:</strong> ${formData.get('address')}</p>
                <p><strong>Coordinates:</strong> ${formData.get('lat')}, ${formData.get('lng')}</p>
                <p><strong>Selected Dishes:</strong></p>
                <ul>${selectedDishes.map(dish => `<li>${dish}</li>`).join('')}</ul>
                <p><strong>Selected Desserts:</strong></p>
                <ul>${selectedDesserts.map(dessert => `<li>${dessert}</li>`).join('')}</ul>
                <p><strong>Price per Guest:</strong> ₱${basePrice.toFixed(2)}</p>
                <p class="total-price">Total Price: ₱${totalPrice.toFixed(2)}</p>
            `;
            
            document.getElementById('orderSummary').innerHTML = summaryHTML;
            document.getElementById('orderModal').style.display = 'none';
            document.getElementById('summaryModal').style.display = 'block';
            
            return false;
        }

        function closeSummaryModal() {
            document.getElementById('summaryModal').style.display = 'none';
            document.getElementById('orderModal').style.display = 'block';
        }

        function submitOrder() {
            const form = document.getElementById('orderForm');
            const formData = new FormData(form);
            const packageId = formData.get('package_id');
            const package = packages.find(p => p.id == packageId);
            
            // Calculate total price
            const guestCount = parseInt(formData.get('guest_count'));
            const basePrice = parseFloat(package.price);
            const totalPrice = basePrice * guestCount;
            
            // Add total price to form data
            formData.append('total_price', totalPrice);

            // Show loading state
            const submitButton = document.querySelector('.confirm-btn');
            const originalText = submitButton.textContent;
            submitButton.textContent = 'Submitting...';
            submitButton.disabled = true;
            
            // Submit the form to submit_order.php
            fetch('submit_order.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert('Order submitted successfully!');
                    window.location.href = 'order_details.php';
                } else {
                    alert('Error submitting order: ' + (data.message || 'Unknown error occurred'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error submitting order. Please try again. If the problem persists, please contact support.');
            })
            .finally(() => {
                // Reset button state
                submitButton.textContent = originalText;
                submitButton.disabled = false;
            });
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
    </script>

</body>
</html>
