<?php
include('connection.php');
if (isset($_POST['name']) && isset($_POST['email'])&& isset($_POST['contact']) && isset($_POST['password']) && isset($_POST['confirm_password'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $contact= $_POST['contact'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Hashing the password
    if ($password === $confirm_password) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // SQL to insert data into the database
        $sql = "INSERT INTO registertb (userid, name, username, contact, password, checkpass) VALUES ('', '$name', '$email','$contact', '$hashed_password', '$confirm_password')";

        if ($conn->query($sql) === TRUE) {
            echo "<script>
            alert('Registration successful'); 
            window.location='index.php';
            </script>";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
        echo "<script>
        alert('Passwords do not match!');
        window.history.back();
        </script>";
    }
}
$conn->close();  // Close the correct database connection
?>