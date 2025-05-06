<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Catering Business</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display&family=Open+Sans&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body, html {
            height: 100%;
            font-family: 'Open Sans', sans-serif;
            overflow: hidden;
        }

        body {
            background: linear-gradient(to right, #2f2f2f, #e0d6c3);
            color: #fff;
        }

        .background-slider {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            animation: slide 30s infinite;
            background-size: cover;
            background-position: center;
        }

        @keyframes slide {
            0%, 100% { background-image: url('bg-1.jpg'); }
            33% { background-image: url('bg-2.jpg'); }
            66% { background-image: url('bg-3.jpg'); }
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

        .content {
            text-align: center;
            margin-top: 15%;
            
        }

        h1 {
            font-family: 'Playfair Display', serif;
            font-size: 3.5rem;
            color: white;
            border-color:black 1px;
        }

        p.tagline {
            font-size: 1.5rem;
            font-style: italic;
            color: white;
            margin-top: 1rem;
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
    </style>
</head>
<body>

    <div class="background-slider"></div>

    <nav>

        <a href="account.php">Account Settings</a>
        <a href="orders.php">Packages</a>
        <a href="#">Payment Methods</a>
        <a href="index.php">Log Out</a>
    </nav>

    <div class="content">
        <h1>RJ & A Catering Services</h1>
        <p class="tagline">From Ordinary to Extraordinary, We Cater with Ingenuity.</p>
    </div>

</body>
</html>
