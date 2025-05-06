<?php
session_start();  // Start the session to store session variables
include ('connection.php');

if (isset($_POST['email']) && isset($_POST['password'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Query to fetch user data by email including the role
    $sql = "SELECT * FROM registertb WHERE LOWER(username) = LOWER('$email')";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Fetch the user data
        $row = $result->fetch_assoc();
        $stored_hashed_password = $row['password'];  // This is the hashed password stored in the DB
        $user_role = $row['role'];  // Fetch the role (admin or user)

        // Verify if the entered password matches the hashed password
        if (password_verify($password, $stored_hashed_password)) {
            // Successful login, set session variables
            $_SESSION['user_id'] = $row['userid'];  // Store user ID in the session
            $_SESSION['user_email'] = $row['email'];  // Store email in session
            $_SESSION['user_role'] = $user_role;  // Store user role in session (admin or user)

            // If user is an admin, redirect to admin page, else to home page
            if ($user_role == 'admin') {
                header('Location: admin_home.php');  // Admin dashboard or order management
            } else {
                header('Location: home.php');  // Regular user dashboard
            }
            exit();
        } else {
            echo "<script>
            alert('Incorrect password!');
            window.history.back();
            </script>";
        }
    } else {
        echo "<script>
        alert('No user found with that email!');
        window.history.back();
        </script>";
    }
}

$conn->close();  // Close the database connection
?>
