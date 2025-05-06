<?php
include ('connection.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>403 Forbidden</title>
    <style>
        body {
            font-family: sans-serif;
            background-color: #1c1c1c;
            color: #f1f1f1;
            text-align: center;
            padding: 100px;
        }
        h1 {
            font-size: 4em;
            color: #e74c3c;
        }
        p {
            font-size: 1.5em;
        }
        a {
            color: #d4b895;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h1>403</h1>
    <p>Access Denied. Admins only.</p>
    <a href="home.php">Return to Homepage</a>
</body>
</html>
