<?php
session_start();
include('connection.php');

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('You need to log in first.'); window.location.href = 'index.php';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = "";

// Check if user is admin or moderator
$query = "SELECT name, role FROM registertb WHERE userid = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($name, $role);
$stmt->fetch();
$stmt->close();

// Allow only admin and moderator roles
if ($role !== 'admin' && $role !== 'moderator') {
    header("Location: forbidden.php");
    exit();
}

if (isset($_GET['id'])) {
    $package_id = intval($_GET['id']);
    $query = "SELECT * FROM packages WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $package_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $package = $result->fetch_assoc();
    $stmt->close();

    // Fetch dishes and desserts as arrays
    $dishes = explode(',', $package['dishes']);
    $desserts = explode(',', $package['desserts']);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['form_type'])) {
        if ($_POST['form_type'] === 'package') {
            // Handle package update
            $package_name = $_POST['package_name'];
            $price = $_POST['price'];
            $dishes = $_POST['dishes'];
            $desserts = $_POST['desserts'];
            $status = $_POST['status'];
            $description = $_POST['description'];
            $max_dishes = $_POST['max_dishes'];
            $max_desserts = $_POST['max_desserts'];

            $sql = "UPDATE packages SET package_name = ?, price = ?, dishes = ?, desserts = ?, status = ?, description = ?, max_dishes = ?, max_desserts = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sdssssiii", $package_name, $price, $dishes, $desserts, $status, $description, $max_dishes, $max_desserts, $package_id);

            if ($stmt->execute()) {
                $success = "Package updated successfully!";
            } else {
                $error = "Error: " . $conn->error;
            }

            $stmt->close();
        } elseif ($_POST['form_type'] === 'availability') {
            // Handle date availability
            if (isset($_POST['selected_date']) && isset($_POST['availability_status'])) {
                $selected_date = $_POST['selected_date'];
                $availability_status = $_POST['availability_status'];

                // Check if date already exists in the availability table
                $check_query = "SELECT * FROM date_availability WHERE date = ?";
                $check_stmt = $conn->prepare($check_query);
                $check_stmt->bind_param("s", $selected_date);
                $check_stmt->execute();
                $check_stmt->store_result();

                if ($check_stmt->num_rows > 0) {
                    // Update the availability status for the selected date
                    $update_query = "UPDATE date_availability SET status = ? WHERE date = ?";
                    $update_stmt = $conn->prepare($update_query);
                    $update_stmt->bind_param("ss", $availability_status, $selected_date);
                    $update_stmt->execute();
                    $update_stmt->close();
                } else {
                    // Insert a new availability record for the selected date
                    $insert_query = "INSERT INTO date_availability (date, status) VALUES (?, ?)";
                    $insert_stmt = $conn->prepare($insert_query);
                    $insert_stmt->bind_param("ss", $selected_date, $availability_status);
                    $insert_stmt->execute();
                    $insert_stmt->close();
                }

                $check_stmt->close();
                $success = "Date availability updated successfully!";
                
                // Redirect to refresh the page
                header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $package_id . "&success=1");
                exit();
            }
        }
    }
}

// Fetch unavailable dates for the calendar
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
    <title>Edit Package - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.2.0/fullcalendar.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.2.0/fullcalendar.min.js"></script>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #2e2e2e;
            color: #f1f1f1;
            margin: 0;
        }

        .navbar {
            background-color: #111;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
            position: relative;
        }

        .navbar-left {
            display: flex;
            align-items: center;
        }

        .greeting {
            font-size: 1.5rem;
            color: #f7f2e9;
            margin-left: 25px;
        }

        .logo-img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 15px;
            border: 2px solid beige;
        }

        .hamburger {
            display: none;
            cursor: pointer;
            padding: 10px;
        }

        .hamburger-line {
            width: 25px;
            height: 3px;
            background-color: beige;
            margin: 5px 0;
            transition: all 0.3s ease;
        }

        .nav-links {
            display: flex;
            gap: 20px;
        }

        .nav-links a {
            color: beige;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .nav-links a:hover {
            color: #d4b895;
        }

        @media screen and (max-width: 768px) {
            .hamburger {
                display: block;
            }

            .nav-links {
                display: none;
                position: absolute;
                top: 100%;
                right: 0;
                background-color: #111;
                width: 200px;
                padding: 20px;
                flex-direction: column;
                gap: 15px;
                box-shadow: 0 4px 10px rgba(0,0,0,0.3);
                z-index: 1000;
            }

            .nav-links.active {
                display: flex;
            }

            .hamburger.active .hamburger-line:nth-child(1) {
                transform: rotate(-45deg) translate(-5px, 6px);
            }

            .hamburger.active .hamburger-line:nth-child(2) {
                opacity: 0;
            }

            .hamburger.active .hamburger-line:nth-child(3) {
                transform: rotate(45deg) translate(-5px, -6px);
            }
        }

        .container {
            max-width: 800px;
            margin: 40px auto;
            background-color: #3e3e3e;
            padding: 30px;
            border-radius: 12px;
        }

        h2 {
            color: #d4b895;
        }

        input, textarea, select {
            width: 100%;
            padding: 10px;
            margin: 10px 0 20px;
            border: none;
            border-radius: 8px;
            background-color: #2a2a2a;
            color: white;
        }

        label {
            font-weight: bold;
            color: beige;
        }

        button {
            background-color: #d4b895;
            color: #2a2a2a;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
        }

        button:hover {
            background-color: #c3a77c;
        }

        .message {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 8px;
            color: #fff;
        }

        .success { background-color: #4CAF50; }
        .error { background-color: #f44336; }

        /* Calendar styles */
        .calendar-container {
            margin-top: 30px;
            background-color: #3e3e3e;
            padding: 20px;
            border-radius: 12px;
        }

        .fc-toolbar h2 {
            color: #d4b895;
            font-size: 1.5em;
        }

        .fc-button {
            background-color: #d4b895 !important;
            border-color: #d4b895 !important;
            color: #2a2a2a !important;
        }

        .fc-button:hover {
            background-color: #c3a77c !important;
            border-color: #c3a77c !important;
        }

        .fc-today {
            background-color: #4a4a4a !important;
        }

        .fc-day-grid-event {
            margin: 0 !important;
            padding: 0 !important;
            height: 100% !important;
            background-color: transparent !important;
            border: none !important;
        }

        .fc-day-grid-event .fc-content {
            display: none !important;
        }

        .fc-day.fc-past {
            background-color: #2a2a2a !important;
        }

        .fc-day.fc-future {
            background-color: #2a2a2a !important;
        }

        .fc-day.fc-today {
            background-color: #3a3a3a !important;
        }

        .fc-day.fc-other-month {
            background-color: #2a2a2a !important;
        }

        .fc-day-grid-event {
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
        }

        .fc-day-grid-event.fc-event {
            background-color: transparent !important;
            border: none !important;
        }

        .fc-day-grid-event.fc-event[style*="background-color: rgb(255, 77, 77)"] {
            background-color: #ff4d4d !important;
        }

        .fc-day-grid-event.fc-event[style*="background-color: rgb(255, 77, 77)"] .fc-content {
            display: none !important;
        }

        .fc-day.fc-unavailable {
            background-color: #ff4d4d !important;
        }

        .fc-day.fc-unavailable .fc-day-number {
            color: white !important;
        }

        .selected-range {
            background-color: #4CAF50 !important;
            color: white !important;
        }
        
        .fc-day.selected-range {
            background-color: #4CAF50 !important;
        }
        
        .fc-day.selected-range .fc-day-number {
            color: white !important;
        }
        
        .calendar-container form {
            background-color: #2a2a2a;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        .calendar-container form label {
            display: block;
            margin-bottom: 8px;
            color: beige;
        }
        
        .calendar-container form input[type="date"],
        .calendar-container form select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: none;
            border-radius: 8px;
            background-color: #3e3e3e;
            color: white;
        }
        
        .calendar-container form button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(to right, #4CAF50, #45a049);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        .calendar-container form button:hover {
            background: linear-gradient(to right, #45a049, #388e3c);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .update-btn {
            background: linear-gradient(to right, #d4b895, #c3a77c);
            color: #2a2a2a;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            width: 100%;
            margin-top: 20px;
            transition: all 0.3s ease;
        }

        .update-btn:hover {
            background: linear-gradient(to right, #c3a77c, #b39a6b);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .availability-btn {
            background: linear-gradient(to right, #4CAF50, #45a049);
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            width: 100%;
            margin-top: 15px;
            transition: all 0.3s ease;
        }

        .availability-btn:hover {
            background: linear-gradient(to right, #45a049, #388e3c);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="navbar-left">
        <img src="logo.jfif" alt="Logo" class="logo-img">
        <span class="greeting">Hello, <strong><?= htmlspecialchars($name) ?></strong>!</span>
    </div>
    <div class="hamburger" onclick="toggleMenu()">
        <div class="hamburger-line"></div>
        <div class="hamburger-line"></div>
        <div class="hamburger-line"></div>
    </div>
    <div class="nav-links">
        <a href="mod_packages.php">Manage Packages</a>
        <a href="mod_add_packages.php">Add Package</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<div class="container">
    <h2>✏️ Edit Catering Package</h2>

    <?php if (!empty($success)): ?>
        <div class="message success"><?= $success ?></div>
    <?php elseif (!empty($error)): ?>
        <div class="message error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" id="packageForm">
        <input type="hidden" name="form_type" value="package">
        <label for="package_name">Package Name:</label>
        <input type="text" name="package_name" id="package_name" value="<?= htmlspecialchars($package['package_name'] ?? '') ?>" required>

        <label for="description">Description:</label>
        <textarea name="description" id="description" rows="3" required><?= htmlspecialchars($package['description'] ?? '') ?></textarea>

        <label for="price">Price (₱):</label>
        <input type="number" step="0.01" name="price" id="price" value="<?= htmlspecialchars($package['price'] ?? '') ?>" required>

        <label for="dishes">Dishes (comma-separated):</label>
        <textarea name="dishes" id="dishes" rows="3" required><?= htmlspecialchars($package['dishes'] ?? '') ?></textarea>

        <label for="desserts">Desserts (comma-separated):</label>
        <textarea name="desserts" id="desserts" rows="2" required><?= htmlspecialchars($package['desserts'] ?? '') ?></textarea>

        <label for="status">Status:</label>
        <select name="status" id="status">
            <option value="available" <?= ($package['status'] ?? '') === 'available' ? 'selected' : '' ?>>Available</option>
            <option value="unavailable" <?= ($package['status'] ?? '') === 'unavailable' ? 'selected' : '' ?>>Unavailable</option>
        </select>

        <label for="max_dishes">Max Dishes Allowed:</label>
        <input type="number" name="max_dishes" id="max_dishes" value="<?= htmlspecialchars($package['max_dishes'] ?? '') ?>" min="1" required>

        <label for="max_desserts">Max Desserts Allowed:</label>
        <input type="number" name="max_desserts" id="max_desserts" value="<?= htmlspecialchars($package['max_desserts'] ?? '') ?>" min="1" required>

        <button type="submit" class="update-btn">Update Package</button>
    </form>

    <!-- Date Availability Section -->
    <div class="calendar-container">
        <h3>🗓️ Set Date Availability</h3>
        <div id="calendar"></div>
        <form method="POST" id="availabilityForm">
            <input type="hidden" name="form_type" value="availability">
            <input type="hidden" name="selected_date" id="selected_date">
            
            <label for="availability_status">Toggle Availability:</label>
            <select name="availability_status" id="availability_status">
                <option value="available">Make Available</option>
                <option value="unavailable">Make Unavailable</option>
            </select>

            <button type="submit" class="availability-btn">Update Availability</button>
        </form>
    </div>
</div>

<script>
function toggleMenu() {
    const navLinks = document.querySelector('.nav-links');
    const hamburger = document.querySelector('.hamburger');
    navLinks.classList.toggle('active');
    hamburger.classList.toggle('active');
}

// Close menu when clicking outside
document.addEventListener('click', function(event) {
    const navLinks = document.querySelector('.nav-links');
    const hamburger = document.querySelector('.hamburger');
    if (!event.target.closest('.nav-links') && !event.target.closest('.hamburger')) {
        navLinks.classList.remove('active');
        hamburger.classList.remove('active');
    }
});

$(function() {
    const unavailableDates = <?= json_encode($unavailable_dates) ?>;
    
    $('#calendar').fullCalendar({
        header: { 
            left: 'prev',  
            center: 'title',
            right: 'next'  
        },
        defaultView: 'month',
        selectable: false,
        dayRender: function(date, cell) {
            const dateStr = date.format('YYYY-MM-DD');
            if (unavailableDates.includes(dateStr)) {
                cell.addClass('fc-unavailable');
            }
            cell.css("cursor", "pointer");
        },
        dayClick: function(date, jsEvent, view) {
            // Single date selection
            const selectedDate = date.format('YYYY-MM-DD');
            document.getElementById('selected_date').value = selectedDate;
            
            // Highlight the selected date
            $('.fc-day').removeClass('selected-range');
            $(jsEvent.target).closest('.fc-day').addClass('selected-range');
        },
        height: 400,
        contentHeight: 400,
        aspectRatio: 1.2
    });
});

// Add form submission handling
document.getElementById('packageForm').addEventListener('submit', function(e) {
    e.preventDefault();
    this.submit();
});

document.getElementById('availabilityForm').addEventListener('submit', function(e) {
    e.preventDefault();
    this.submit();
});
</script>

</body>
</html>

<?php $conn->close(); ?>
