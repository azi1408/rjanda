<?php
session_start();
// Initialize variables for form data and errors
$bank_account = $bank_name = $account_holder = "";
$bank_account_err = $bank_name_err = $account_holder_err = "";



// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate Bank Account Number
    if (empty($_POST["bank-account"])) {
        $bank_account_err = "Bank account number is required.";
    } else {
        $bank_account = test_input($_POST["bank-account"]);
    }

    // Validate Bank Name
    if (empty($_POST["bank-name"])) {
        $bank_name_err = "Bank name is required.";
    } else {
        $bank_name = test_input($_POST["bank-name"]);
    }

    // Validate Account Holder's Name
    if (empty($_POST["account-holder"])) {
        $account_holder_err = "Account holder's name is required.";
    } else {
        $account_holder = test_input($_POST["account-holder"]);
    }

    // If no errors, process the form (e.g., store to a database or send email)
    if (empty($bank_account_err) && empty($bank_name_err) && empty($account_holder_err)) {
        // Here you can process the payment data (e.g., store in DB, send to email, etc.)
        echo "<script>alert('Payment information submitted successfully!');</script>";
    }
}

// Function to sanitize user input
function test_input($data) {
    $data = trim($data); // Remove whitespace from both ends
    $data = stripslashes($data); // Remove backslashes
    $data = htmlspecialchars($data); // Convert special characters to HTML entities
    return $data;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catering Business - Payment Methods</title>
    <link rel="stylesheet" href="styles.css">
    <style>
/* General Styles */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Arial', sans-serif;
    background: linear-gradient(to bottom right,#323232, #d4b895);
    color: #f0e6d2; /* Beige */
}

/* Navbar */
.navbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #333; /* Darker Grey */
    padding: 10px 30px;
}

.logo-container {
    display: flex;
    align-items: center;
}

.logo {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    margin-right: 15px;
}

.title {
    font-size: 24px;
    font-weight: bold;
    color: #f0e6d2; /* Beige */
}

.nav-links {
    display: flex;
    list-style: none;
}

.nav-links li {
    margin: 0 20px;
}

.nav-links a {
    text-decoration: none;
    color: #f0e6d2; /* Beige */
    font-size: 18px;
}

.nav-links a:hover {
    color: #008CBA; /* Blue color on hover */
}

/* Payment Methods Section */
.payment-methods {
    padding: 40px 30px;
    text-align: center;
    background-color: #444; /* Darker Grey */
    border-radius: 8px;
    margin: 20px auto;
    max-width: 600px;
}

.payment-methods h2 {
    font-size: 28px;
    margin-bottom: 20px;
}

.payment-methods p {
    font-size: 18px;
    margin-bottom: 30px;
}

.payment-methods form {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.payment-methods label {
    text-align: left;
    font-size: 16px;
}

.payment-methods input {
    padding: 10px;
    font-size: 16px;
    background-color: #555; /* Slightly lighter grey */
    color: #f0e6d2; /* Beige */
    border: 1px solid #888; /* Light Grey Border */
    border-radius: 5px;
}

.payment-methods input:focus {
    outline: none;
    border-color: #008CBA; /* Blue Border on Focus */
}

.submit-button {
    background-color: #d4b895; /* Blue Button */
    color: #fff;
    border: none;
    padding: 12px 20px;
    font-size: 18px;
    cursor: pointer;
    border-radius: 5px;
    transition: background-color 0.3s;
}

.submit-button:hover {
    background-color: #006c8e; /* Darker Blue on Hover */
}
nav {
            background-color: rgba(0, 0, 0, 0.6);
            display: flex;
            justify-content: flex-end;
            padding: 1rem 2rem;
        }

        nav a {
            color: white;
            text-decoration: none;
            margin-left: 2rem;
            font-size: 1rem;
            
        }

        nav a:hover {
            color: #fff;
        }
        .logo-img {
    width: 60px;
    height: 60px;
    border-radius: 50%; /* makes it a circle */
    object-fit: cover;  /* ensures image doesn't stretch */
    border: 2px solid white; /* optional border */
    box-shadow: 0 0 5px rgba(0,0,0,0.2); /* optional shadow */
}
.navbar .logo {
            font-size: 1.5em;
            font-weight: bold;
            color: white;
            text-decoration: none;
            margin-right:900px;
        }
.logotext{
    margin-right:700px;
}

    </style>
</head>
<body>

    <!-- Navbar -->
    <nav>
    <div class="logotext" style="display: flex; align-items: center; gap: 15px;">
        <img src="logo.jfif" alt="Logo" class="logo-img">
        <span style="color: white; font-size: 1.2em; font-weight: bold;">RJ & A Catering Services</span>
    </div>
        <a href="home.php">Home</a>
        <a href="account.php">Account Settings</a>
        <a href="orders.php">Packages</a>
        <?php
        if (isset($_SESSION['user_id'])) {
    // If user is logged in, show the Log Out button
    echo '<a href="logout.php">Log Out</a>';
} else {
    // If user is not logged in, show the Log In button
    echo '<a href="index.php">Log In</a>';
}
?>
    </nav>
    
    <!-- Payment Section -->
    <section class="payment-methods">
        <h2>Payment Methods</h2>
        <p>Please enter your bank account details below:</p>

        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
            <!-- Bank Account Number -->
            <label for="bank-account">Bank Account Number:</label>
            <input type="text" id="bank-account" name="bank-account" placeholder="Enter your bank account number" value="<?php echo $bank_account;?>">
            <span class="error"><?php echo $bank_account_err;?></span>

            <!-- Bank Name -->
            <label for="bank-name">Bank Name:</label>
            <input type="text" id="bank-name" name="bank-name" placeholder="Enter your bank name" value="<?php echo $bank_name;?>">
            <span class="error"><?php echo $bank_name_err;?></span>

            <!-- Account Holder's Name -->
            <label for="account-holder">Account Holder's Name:</label>
            <input type="text" id="account-holder" name="account-holder" placeholder="Enter account holder's name" value="<?php echo $account_holder;?>">
            <span class="error"><?php echo $account_holder_err;?></span>

            <!-- Submit Button -->
            <button type="submit" class="submit-button">Submit</button>
        </form>

        <!-- Display Submitted Data -->
        <?php
        if (!empty($bank_account) && !empty($bank_name) && !empty($account_holder)) {
            echo "<h3>Submitted Payment Details:</h3>";
            echo "<p><strong>Bank Account Number:</strong> " . $bank_account . "</p>";
            echo "<p><strong>Bank Name:</strong> " . $bank_name . "</p>";
            echo "<p><strong>Account Holder's Name:</strong> " . $account_holder . "</p>";
        }
        ?>
    </section>

</body>
</html>