<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login & Registration</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(to bottom right,#323232, #d4b895);
            color: #fff;
        }

        /* Navbar */
        .navbar {
            background-color: #111;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 30px;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .navbar .logo {
            font-size: 1.5em;
            font-weight: bold;
            color: white;
            text-decoration: none;
            margin-right:900px;
        }

        .navbar ul {
            list-style: none;
            display: flex;
            gap: 20px;
            margin: 0;
            padding: 0;
        }

        .navbar ul li a {
            color: white;
            text-decoration: none;
            font-weight: 500;
           
        }

        .navbar ul li a:hover {
            color: #e0f2f1;
        }
        .logo-img {
    width: 60px;
    height: 60px;
    border-radius: 50%; /* makes it a circle */
    object-fit: cover;  /* ensures image doesn't stretch */
    border: 2px solid white; /* optional border */
    box-shadow: 0 0 5px rgba(0,0,0,0.2); /* optional shadow */
}

        /* Container */
        .auth-container {
            max-width: 400px;
            margin: 80px auto;
            background: #222;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 0 15px rgba(0,0,0,0.5);
        }

        .tab-buttons {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .tab-buttons button {
            flex: 1;
            background-color: #333;
            border: none;
            padding: 12px;
            cursor: pointer;
            color: #d4b895;
            font-weight: bold;
        }

        .tab-buttons button.active {
            background-color: #d4b895;
            color: #000;
        }

        form {
            display: none;
        }

        form.active {
            display: block;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-top: 12px;
            border: none;
            border-radius: 6px;
            background: #333;
            color: #fff;
        }

        input[type="submit"] {
            width: 100%;
            background-color: #d4b895;
            color: #000;
            border: none;
            padding: 12px;
            margin-top: 20px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: white;
        }

        label {
            margin-top: 10px;
            display: block;
        }

        .note {
            text-align: center;
            margin-top: 15px;
            font-size: 0.9em;
            color: #ccc;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
<img src="logo.jfif" alt="Logo" class="logo-img">
    <a href="#" class="logo">RJ & A Catering Services</a>
    <ul>
        <li><a href="home.php">Home</a></li>
    </ul>
</nav>

<!-- Login/Registration Container -->
<div class="auth-container">
    <div class="tab-buttons">
        <button id="loginTab" class="active" onclick="showForm('login')">Login</button>
        <button id="registerTab" onclick="showForm('register')">Register</button>
    </div>

    <!-- Login Form -->
    <form id="loginForm" class="active" method="post" action="login_process.php">
        <label>Email:</label>
        <input type="email" name="email" required>

        <label>Password:</label>
        <input type="password" name="password" required>

        <input type="submit" value="Login">
    </form>

    <!-- Registration Form -->
    <form id="registerForm" method="post" action="register_process.php">
        <label>Full Name:</label>
        <input type="text" id="name" name="name" required>

        <label>Email:</label>
        <input type="email" id="email" name="email" required>

        <label>Contact Number</label>
        <input type="text"id="contact" name="contact" required>

        <label>Password:</label>
        <input type="password" id="password" name="password" required>

        <label>Confirm Password:</label>
        <input type="password"id="confirm_password" name="confirm_password" required>

        

        <input type="submit" value="Register">
    </form>

    <div class="note">Your info is secure with us 💚</div>
</div>

<!-- Toggle Script -->
<script>
    function showForm(type) {
        const loginBtn = document.getElementById('loginTab');
        const registerBtn = document.getElementById('registerTab');
        const loginForm = document.getElementById('loginForm');
        const registerForm = document.getElementById('registerForm');

        if (type === 'login') {
            loginForm.classList.add('active');
            registerForm.classList.remove('active');
            loginBtn.classList.add('active');
            registerBtn.classList.remove('active');
        } else {
            loginForm.classList.remove('active');
            registerForm.classList.add('active');
            loginBtn.classList.remove('active');
            registerBtn.classList.add('active');
        }
    }
</script>

</body>
</html>
