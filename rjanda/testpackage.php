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
        background-color: #000;
        padding: 10px 20px;
    }

    nav .logo {
        display: flex;
        align-items: center;
    }

    nav .logo img {
        height: 40px;
        width: 40px;
        border-radius: 50%;
        margin-right: 10px;
    }

    nav .logo span {
        font-size: 1.2em;
        font-weight: bold;
        color: white;
    }

    nav .nav-links a {
        color: white;
        text-decoration: none;
        margin-left: 20px;
        font-weight: bold;
    }

    nav .nav-links a:hover {
        color: #76ff03;
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
    </style>
</head>
<body>
<nav>
    <div class="logo">
        <img src="logo.jfif" alt="Logo">
        <span>RJ & A Catering Services</span>
    </div>
    <div class="nav-links">
        <a href="order_details.php">My Orders</a>
        <a href="home.php">Home</a>
        <a href="show_reviews.php">Reviews</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<div class="container">
    <?php if (count($packages) > 0): ?>
        <?php foreach ($packages as $row): ?>
            <div class="package">
                <h3><?= htmlspecialchars($row['package_name']) ?></h3>
                <p><strong>Dishes:</strong> <?= nl2br(htmlspecialchars($row['dishes'])) ?></p>
                <p><strong>Desserts:</strong> <?= nl2br(htmlspecialchars($row['desserts'])) ?></p>
                <p><strong>Price:</strong> ₱<?= number_format($row['price'], 2) ?></p>
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
        <form action="submit_order.php" method="POST" onsubmit="return validateOrderForm()">
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

            <div style="background-color: #f8f8f8; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #4CAF50;">
                <h4 style="margin: 0 0 10px 0; color: #333;">📅 Date Selection Rules:</h4>
                <ul style="margin: 0; padding-left: 20px; color: #555;">
                    <li>Please select a date at least 7 days before your event</li>
                    <li>Red dates indicate unavailable dates</li>
                    <li>Click on an available date to select it</li>
                </ul>
            </div>

            <div id="calendar" class="calendar"></div>
            <div id="selected-date-display" style="background-color: #f8f8f8; padding: 10px; border-radius: 8px; margin-top: 10px; text-align: center; color: #333; font-weight: bold;">
                Selected Date: <span id="selected-date-text">No date selected</span>
            </div>
            <input type="hidden" name="order_date" id="order_date" required>

            <button type="submit">Submit Order</button>
        </form>
       
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
    $('#calendar').fullCalendar({
        header: { 
            left: 'prev',  
            center: 'title',
            right: 'next'  
        },
        defaultView: 'month',
        events: unavailableDates.map(date => ({
            title: '',
            start: date, 
            color: '#ff4d4d',
        })),
        dayRender: function(date, cell) {
            if (unavailableDates.includes(date.format('YYYY-MM-DD'))) {
                cell.css("background-color", "#ff4d4d");
                cell.css("cursor", "not-allowed");
            } else {
                cell.css("cursor", "pointer");
            }
        },
        dayClick: function(date, jsEvent, view) {
            // Check if the clicked date is not in unavailable dates
            if (!unavailableDates.includes(date.format('YYYY-MM-DD'))) {
                // Remove any existing selected date
                $('.fc-day').removeClass('selected-date');
                
                // Set the selected date in the input field
                document.getElementById('order_date').value = date.format('YYYY-MM-DD');
                
                // Update the selected date display
                document.getElementById('selected-date-text').textContent = date.format('MMMM D, YYYY');
                
                // Add selected class to the clicked date
                $(jsEvent.target).closest('.fc-day').addClass('selected-date');
            }
        },
        height: 300,
        contentHeight: 300,
        aspectRatio: 1.2
    });

    // Add some CSS for the selected date and submit button
    const style = document.createElement('style');
    style.textContent = `
        .selected-date {
            background-color: #4CAF50 !important;
            color: white !important;
        }
        .fc-day:hover:not(.fc-other-month) {
            background-color: #e8f5e9 !important;
        }
        .fc-day.selected-date:hover {
            background-color: #4CAF50 !important;
        }
        .fc-day {
            min-height: 30px !important;
        }
        .fc-day-number {
            font-size: 0.9em;
        }
        .fc-header-title h2 {
            font-size: 1.2em;
        }
        .fc-day {
            position: relative;
        }
        .fc-day > div {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 1;
        }
        .fc-day-number {
            position: relative;
            z-index: 2;
        }
        .fc-button {
            background: #4CAF50 !important;
            border: none !important;
            padding: 8px 12px !important;
            border-radius: 4px !important;
            color: white !important;
            font-weight: bold !important;
            transition: all 0.3s ease !important;
        }
        .fc-button:hover {
            background: #45a049 !important;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .fc-button:active {
            transform: translateY(0);
        }
        .fc-button.fc-state-active {
            background: #388e3c !important;
        }
        .fc-header {
            margin-bottom: 10px !important;
        }
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

    // Add legend after calendar initialization
    
    $('#calendar').after(legendHtml);
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
    if (!dishChecked || !dessertChecked) return alert("Select required dishes and desserts."), false;
    return true;
}

function initMap() {
    const defaultLocation = { lat: 14.5995, lng: 120.9842 };
    map = new google.maps.Map(document.getElementById("map"), { center: defaultLocation, zoom: 12 });
    map.addListener("click", e => {
        if (marker) marker.setMap(null);
        marker = new google.maps.Marker({ position: e.latLng, map: map });
        document.getElementById('lat').value = e.latLng.lat();
        document.getElementById('lng').value = e.latLng.lng();
    });
}

$(function() {
    // Remove the calendar initialization from here since it's now in openModal
    });
</script>

</body>
</html>
