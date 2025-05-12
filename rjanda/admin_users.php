<?php
session_start();
include('connection.php');

if (!isset($_SESSION['user_id'])) {
    echo "<!DOCTYPE html>
    <html>
    <head>
        <script>
            alert('You need to log in first.');
        </script>";
    echo "<script>window.location.href = 'index.php';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = "";

// Prepare the SQL statement
$query = "SELECT name, role FROM registertb WHERE userid = ?";
$stmt = $conn->prepare($query);

if (!$stmt) {
    // Output error and stop script
    die("SQL prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($name, $roles);

if ($stmt->fetch()) {
    $user_name = $name;

    // Redirect if not admin
    if ($roles !== 'admin') {
        header("Location: forbidden.php");
        exit();
    }
} 

$stmt->close();

// Fetch all users
$sql = "SELECT userid, name, username, role FROM registertb ORDER BY userid ASC";
$result = $conn->query($sql);

if (!$result) {
    die("Query failed: " . $conn->error);
}

// Handle deletion if the admin clicks the delete button
if (isset($_POST['delete_user'])) {
    $userid = $_POST['userid'];
    $delete_sql = "DELETE FROM registertb WHERE userid = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $userid);

    if ($stmt->execute()) {
        echo "<script>alert('User deleted successfully.'); window.location.href = 'admin_users.php';</script>";
    } else {
        echo "<script>alert('Failed to delete user.'); window.location.href = 'admin_users.php';</script>";
    }
    $stmt->close();
}

// Handle promoting a user to admin
if (isset($_POST['promote_user'])) {
    $userid = $_POST['userid'];
    $promote_sql = "UPDATE registertb SET role = 'admin' WHERE userid = ?";
    $stmt = $conn->prepare($promote_sql);
    $stmt->bind_param("i", $userid);

    if ($stmt->execute()) {
        echo "<script>alert('User promoted to Admin.'); window.location.href = 'admin_users.php';</script>";
    } else {
        echo "<script>alert('Failed to promote user.'); window.location.href = 'admin_users.php';</script>";
    }
    $stmt->close();
}

// Handle role update
if (isset($_POST['update_role'])) {
    $userid = $_POST['userid'];
    $new_role = $_POST['new_role'];
    
    // Prevent changing the last admin's role
    if ($new_role !== 'admin') {
        $check_admin_sql = "SELECT COUNT(*) as admin_count FROM registertb WHERE role = 'admin'";
        $admin_result = $conn->query($check_admin_sql);
        $admin_count = $admin_result->fetch_assoc()['admin_count'];
        
        if ($admin_count <= 1) {
            echo "<script>alert('Cannot change role: At least one admin must remain.'); window.location.href = 'admin_users.php';</script>";
            exit();
        }
    }
    
    $update_sql = "UPDATE registertb SET role = ? WHERE userid = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("si", $new_role, $userid);

    if ($stmt->execute()) {
        echo "<script>alert('User role updated successfully.'); window.location.href = 'admin_users.php';</script>";
    } else {
        echo "<script>alert('Failed to update user role.'); window.location.href = 'admin_users.php';</script>";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - User Management</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: #2e2e2e;
            color: #f1f1f1;
        }

        .navbar {
            background-color: #111;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .navbar .logo-img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid white;
            box-shadow: 0 0 8px rgba(0,0,0,0.3);
        }

        .navbar .title {
            font-size: 1.8em;
            color: #f1f1f1;
            margin-left: 20px;
        }

        .navbar ul {
            list-style: none;
            gap: 20px;
            margin: 0;
            padding: 0;
        }

        .navbar ul li a {
            color: beige;
            text-decoration: none;
            font-weight: 500;
        }

        .navbar ul li a:hover {
            color: #d4b895;
        }

        .container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
            background-color: #3e3e3e;
            border-radius: 12px;
        }

        h2 {
            color: #d4b895;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #2a2a2a;
        }

        th, td {
            padding: 15px;
            border-bottom: 1px solid #555;
            text-align: left;
        }

        th {
            background-color: #444;
            color: beige;
        }

        tr:hover {
            background-color: #444;
        }

        button {
            background-color: #d4b895;
            color: #222;
            border: none;
            padding: 8px 12px;
            font-weight: bold;
            border-radius: 6px;
            cursor: pointer;
        }

        button:hover {
            background-color: #caa97a;
        }

        .status-admin {
            color: #4caf50;
            font-weight: bold;
        }

        .status-user {
            color: #ff9800;
            font-weight: bold;
        }

        .status-moderator {
            color: #2196f3;
            font-weight: bold;
        }

        .role-select {
            padding: 6px 10px;
            border-radius: 4px;
            border: 1px solid #555;
            background-color: #2a2a2a;
            color: #fff;
            cursor: pointer;
        }

        .role-select:focus {
            outline: none;
            border-color: #d4b895;
        }

        .update-role-btn {
            background-color: #2196f3;
            color: white;
            border: none;
            padding: 8px 12px;
            font-weight: bold;
            border-radius: 6px;
            cursor: pointer;
            margin-left: 10px;
        }

        .update-role-btn:hover {
            background-color: #1976d2;
        }

        .delete-btn {
            background-color: #f44336;
            color: white;
        }

        .delete-btn:hover {
            background-color: #d32f2f;
        }

        .menu-toggle {
            font-size: 28px;
            background: none;
            border: none;
            color: beige;
            cursor: pointer;
            display: block; /* Always show */
        }

        .nav-links {
            display: none;
            flex-direction: column;
            position: absolute;
            top: 70px;
            right: 30px;
            background-color: #222;
            border-radius: 8px;
            padding: 10px 0;
            box-shadow: 0px 4px 8px rgba(0,0,0,0.5);
            z-index: 1000;
        }

        .nav-links.show {
            display: flex;
        }

        .menu-toggle {
            display: block;
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="navbar-left">
        <img src="logo.jfif" alt="Logo" class="logo-img">
        <?php if (!empty($user_name)) {
            echo "<span class='greeting'>Hello, <strong>$user_name</strong>!</span>";
        } ?>
    </div>

    <!-- Hamburger Icon -->
    <button class="menu-toggle" onclick="toggleMenu()">☰</button>

    <!-- Nav Links -->
    <ul id="navLinks" class="nav-links">
        <li><a href="admin_dashboard.php">Dashboard</a></li>
        <li><a href="admin_home.php">Orders</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</nav>

<div class="container">
    <h2>👥 Registered Users</h2>

    <?php if ($result->num_rows > 0): ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Username</th>
                <th>Current Role</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['userid']; ?></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['username']); ?></td>
                <td>
                    <span class="status-<?php echo strtolower($row['role']); ?>">
                        <?php echo ucfirst($row['role']); ?>
                    </span>
                </td>
                <td>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="userid" value="<?php echo $row['userid']; ?>">
                        <select name="new_role" class="role-select">
                            <option value="user" <?php echo $row['role'] === 'user' ? 'selected' : ''; ?>>User</option>
                            <option value="moderator" <?php echo $row['role'] === 'moderator' ? 'selected' : ''; ?>>Moderator</option>
                            <option value="admin" <?php echo $row['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                        </select>
                        <button type="submit" name="update_role" class="update-role-btn">Update Role</button>
                    </form>
                    <?php if ($row['role'] !== 'admin' || $result->num_rows > 1): ?>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="userid" value="<?php echo $row['userid']; ?>">
                        <button type="submit" name="delete_user" class="delete-btn" onclick="return confirm('Are you sure you want to delete this user?')">Delete</button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No users found.</p>
<?php endif; ?>

</div>
<script>
    function toggleMenu() {
        document.getElementById("navLinks").classList.toggle("show");
    }

    // Close menu if clicked outside
    document.addEventListener("click", function(event) {
        const menu = document.getElementById("navLinks");
        const button = document.querySelector(".menu-toggle");
        if (!menu.contains(event.target) && !button.contains(event.target)) {
            menu.classList.remove("show");
        }
    });
</script>
</body>
</html>

<?php $conn->close(); ?>
