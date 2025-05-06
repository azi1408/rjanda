<?php
  session_start();
  
include('connection.php');

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please log in.'); window.location.href='index.php';</script>";
    exit;
}

$user_id = $_SESSION['user_id'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Catering Services - Order Now</title>
  <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCbjR1u-y0-MTmnemgPip8GRyv2msVZDC0&v=weekly"></script>
  <style>
    /* [Same styles you provided] */

    /* Add small map styling */
    .map-container {
      height: 300px;
      width: 100%;
      margin: 10px 0;
      border: 1px solid #ccc;
      border-radius: 10px;
    }
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

nav .nav-links a {
  color: white;
  text-decoration: none;
  margin-left: 20px;
  font-weight: bold;
}

nav .nav-links a:hover {
  text-decoration: underline;
}

header {
  background: linear-gradient(to right, #76ff03, #64dd17);
  color: black;
  padding: 20px 0;
  text-align: center;
  font-size: 2em;
}

.container {
  display: flex;
  justify-content: center;
  flex-wrap: wrap;
  padding: 40px 20px;
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
  transition: transform 0.2s ease-in-out;
  position: relative;
}

.package:hover {
  transform: scale(1.05);
}

.price {
  font-size: 1.5em;
  color: black;
  margin: 10px 0;
}

.inclusions {
  list-style: none;
  padding: 0;
}

.inclusions li {
  padding: 5px 0;
  border-bottom: 1px solid #333;
}

.details-btn {
  background: linear-gradient(to bottom right, #d4b895, #d4b895);
  border: none;
  color: black;
  padding: 10px 15px;
  margin-top: 15px;
  font-weight: bold;
  border-radius: 8px;
  cursor: pointer;
}

.details-btn:hover {
  background: linear-gradient(to bottom right, #bfa27f, #bfa27f);
  color: #fff;
}

footer {
  background-color: #222;
  color: #a5d6a7;
  text-align: center;
  padding: 15px 0;
}

.modal {
  display: none;
  position: fixed;
  z-index: 999;
  left: 0; top: 0;
  width: 100%; height: 100%;
  overflow: auto;
  background-color: rgba(0,0,0,0.8);
}

.modal-content {
  background-color: beige;
  margin: 5% auto;
  padding: 20px;
  border: 1px solid black;
  width: 80%;
  max-width: 600px;
  color: black;
  border-radius: 12px;
  position: relative;
}

.close {
  color: #aaa;
  position: absolute;
  top: 10px;
  right: 15px;
  font-size: 24px;
  cursor: pointer;
}

.close:hover {
  color: white;
}

label {
  display: block;
  margin-bottom: 10px;
}

/* Google Map container styling */
.map-container {
  height: 300px;
  width: 100%;
  margin: 10px 0;
  border: 1px solid #ccc;
  border-radius: 10px;
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

nav .nav-links {
  display: flex;
  align-items: center;
}

nav .nav-links a {
  color: white;
  text-decoration: none;
  margin-left: 20px;
  font-weight: bold;
  transition: color 0.3s;
}

nav .nav-links a:hover {
  color: #76ff03;
  text-decoration: underline;
}

nav .nav-links a.logout {
  background-color: #ff5733;
  padding: 5px 15px;
  border-radius: 5px;
  transition: background-color 0.3s;
}

nav .nav-links a.logout:hover {
  background-color: #d9534f;
}

nav .nav-links a.logout:active {
  background-color: #c9302c;
}

  </style>
</head>
<body>
  <nav>
    <div class="logo">
      <img src="logo.jfif" alt="Logo">
      <span style="color: white; font-weight: bold; font-size: 1.2em;">RJ & A Catering Services</span>
    </div>
    <div class="nav-links">
      <a href="home.php">Home</a>
      <a href="account.php">Account Settings</a>
      <a href="order_details.php">Payment Methods</a>
      <?php if (isset($_SESSION['user'])) echo '<a href="logout.php">Logout</a>'; ?>
    </div>
  </nav>

  <div class="container">
    <?php
    $packages = [
      ['name' => 'Basic Package', 'dishes' => 3, 'desserts' => 1],
      ['name' => 'Premium Package', 'dishes' => 5, 'desserts' => 2],
      ['name' => 'Deluxe Package', 'dishes' => 7, 'desserts' => 3]
    ];

    $dishOptions = ['Chicken Adobo', 'Beef Caldereta', 'Pancit Canton', 'Kare-Kare', 'Grilled Liempo', 'Sinigang na Baboy', 'Sweet and Sour Fish', 'Fried Chicken', 'Menudo', 'Lumpiang Shanghai'];
    $dessertOptions = ['Leche Flan', 'Buko Pandan', 'Fruit Salad', 'Mango Float', 'Ube Halaya'];

    foreach ($packages as $index => $pkg) {
      echo "<div class='package'>";
      echo "<h2>{$pkg['name']}</h2>";
      echo "<div class='price'>Starting at ₱" . ($pkg['dishes'] * 1000 + $pkg['desserts'] * 500) . "+</div>";
      echo "<ul class='inclusions'>";
      echo "<li>{$pkg['dishes']} Dishes</li><li>{$pkg['desserts']} Dessert(s)</li><li>Drinks, Setup & Cleanup Included</li>";
      echo "</ul>";
      echo "<button class='details-btn' onclick='openForm($index)'>Order Now</button>";
      echo "</div>";

      echo "<div id='modal$index' class='modal'>";
      echo "<div class='modal-content'>";
      echo "<span class='close' onclick='closeForm($index)'>&times;</span>";
      echo "<h2>{$pkg['name']} Order Form</h2>";
      echo "<form method='post' action='submit_order.php' onsubmit='return validateSelection(this, {$pkg['dishes']}, {$pkg['desserts']})'>";
      echo '<label>Name:<br><input type="text" name="name" required style="width: 100%; padding: 8px; margin-bottom: 10px;"></label><br>';
        echo '<label>Event Date:<br><input type="date" name="event_date" required style="width: 100%; padding: 8px; margin-bottom: 10px;"></label><br>';
        echo '<label>Number of Guests:<br><input type="number" name="guests" min="1" required style="width: 100%; padding: 8px; margin-bottom: 10px;"></label><br>';
        echo '<label>Address:<br><input type="text" id="addressInput' . $index . '" name="address" required style="width: 100%; padding: 8px; margin-bottom: 10px;"></label><br>';

      echo "<label>Select {$pkg['dishes']} Dishes:</label>";
      foreach ($dishOptions as $dish) {
        echo "<label><input type='checkbox' name='dishes[]' value='$dish' onchange='limitSelection(this.form, {$pkg['dishes']}, \"dishes[]\")'> $dish</label>";
      }

      echo "<label>Select {$pkg['desserts']} Desserts:</label>";
      foreach ($dessertOptions as $dessert) {
        echo "<label><input type='checkbox' name='desserts[]' value='$dessert' onchange='limitSelection(this.form, {$pkg['desserts']}, \"desserts[]\")'> $dessert</label>";
      }
      echo "<input type='hidden' name='package_name' value='" . $pkg['name'] . "'>";
      // Hidden fields for lat/lng
      echo "<input type='hidden' name='lat' id='lat$index'>";
      echo "<input type='hidden' name='lng' id='lng$index'>";

      echo "<label>Pin your event location on the map:</label>";
      echo "<div id='map$index' class='map-container'></div>";

      echo "<button type='submit' class='details-btn'>Submit Order</button>";
      echo "</form></div></div>";
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = $_POST['name'];
        $event_date = $_POST['event_date'];
        $guests = $_POST['guests'];
        $address = $_POST['address'];
        $requests = $_POST['requests'];
        $lat = $_POST['lat'];
        $lng = $_POST['lng'];}
    ?>
  </div>
  <footer>&copy; 2025 RJ & A Catering Services</footer>

  <script>
    const maps = [];

    function initMap() {
      <?php for ($i = 0; $i < count($packages); $i++) : ?>
        const map<?= $i ?> = new google.maps.Map(document.getElementById("map<?= $i ?>"), {
          center: { lat: 14.5995, lng: 120.9842 }, // Default Manila
          zoom: 12
        });

        let marker<?= $i ?> = null;

        map<?= $i ?>.addListener("click", function(e) {
    const lat = e.latLng.lat();
    const lng = e.latLng.lng();

    console.log("Latitude: " + lat + ", Longitude: " + lng);  // Debugging line

    document.getElementById("lat<?= $i ?>").value = lat;
    document.getElementById("lng<?= $i ?>").value = lng;

    if (marker<?= $i ?>) {
        marker<?= $i ?>.setMap(null);
    }

    marker<?= $i ?> = new google.maps.Marker({
        position: { lat: lat, lng: lng },
        map: map<?= $i ?>
    });
});

        maps.push(map<?= $i ?>);
      <?php endfor; ?>
    }

    window.onload = initMap;

    function openForm(index) {
      document.getElementById('modal' + index).style.display = 'block';
    }

    function closeForm(index) {
      document.getElementById('modal' + index).style.display = 'none';
    }

    window.onclick = function(event) {
      <?php for ($i = 0; $i < count($packages); $i++) {
        echo "if (event.target === document.getElementById('modal$i')) document.getElementById('modal$i').style.display = 'none';";
      } ?>
    }

    function validateSelection(form, maxDishes, maxDesserts) {
      const selectedDishes = form.querySelectorAll("input[name='dishes[]']:checked").length;
      const selectedDesserts = form.querySelectorAll("input[name='desserts[]']:checked").length;

      if (selectedDishes !== maxDishes) {
        alert(`Please select exactly ${maxDishes} dish(es).`);
        return false;
      }
      if (selectedDesserts !== maxDesserts) {
        alert(`Please select exactly ${maxDesserts} dessert(s).`);
        return false;
      }

      // Ensure map location is selected
      if (!form.lat.value || !form.lng.value) {
        alert("Please pin your event location on the map.");
        return false;
      }

      return true;
    }

    function limitSelection(form, limit, groupName) {
      const checkboxes = form.querySelectorAll(`input[name='${groupName}']`);
      const checked = Array.from(checkboxes).filter(checkbox => checkbox.checked);
      if (checked.length > limit) {
        alert(`You can only select up to ${limit} option(s).`);
        event.target.checked = false;
      }
    }
    
  </script>
</body>
</html>
