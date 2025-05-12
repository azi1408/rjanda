<?php
session_start();  // Start the session to store session variables
include ('connection.php');

if (isset($_POST['email']) && isset($_POST['password'])) {
    $username = trim($_POST['email']); // We're using the email field for username
    $password = $_POST['password'];

    // Query to fetch user data by username including the role
    $sql = "SELECT * FROM registertb WHERE LOWER(username) = LOWER(?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Fetch the user data
        $row = $result->fetch_assoc();
        $stored_hashed_password = $row['password'];  // This is the hashed password stored in the DB
        $user_role = $row['role'];  // Fetch the role (admin or user)

        // Verify if the entered password matches the hashed password
        if (password_verify($password, $stored_hashed_password)) {
            // Successful login, set only necessary session variables
            $_SESSION['user_id'] = $row['userid'];  // Store user ID in the session
            $_SESSION['user_role'] = $user_role;  // Store user role in session (admin or user)

            // Redirect based on user role
            if ($user_role == 'admin') {
                header('Location: admin_home.php');  // Admin dashboard
            } else if ($user_role == 'moderator') {
                header('Location: mod_packages.php');  // Moderator dashboard
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
        alert('No user found with that username!');
        window.history.back();
        </script>";
    }
}

$conn->close();  // Close the database connection
?>
