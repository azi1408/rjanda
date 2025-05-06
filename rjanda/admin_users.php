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
            display: flex;
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
    </style>
</head>
<body>

<nav class="navbar">
    <div style="display: flex; align-items: center;">
        <img src="logo.jfif" alt="Logo" class="logo-img">
        <span class="title">RJ & A Catering Services - Admin Panel</span>
    </div>
    <ul>
        <li><a href="admin_dashboard.php">Dashboard</a></li>
        <li><a href="admin_home.php">Orders</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</nav>

<div class="container">
    <h2>👥 Registered Users</h2>

    <?php if ($result->num_rows > 0): ?>
    <table>
        <tr>
            <th>User ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['userid']) ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td>
                    <?= $row['role'] === 'admin' ? '<span class="status-admin">Admin</span>' : '<span class="status-user">User</span>' ?>
                </td>
                <td>
                    <?php if ($row['role'] !== 'admin'): ?>
                        <!-- Only allow deletion for non-admin users -->
                        <form method="POST" action="admin_users.php" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                            <input type="hidden" name="userid" value="<?= $row['userid'] ?>">
                            <button type="submit" name="delete_user">Delete User</button>
                        </form>

                        <!-- Promote to Admin button -->
                        <form method="POST" action="admin_users.php" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to promote this user to admin?');">
                            <input type="hidden" name="userid" value="<?= $row['userid'] ?>">
                            <button type="submit" name="promote_user">Promote to Admin</button>
                        </form>
                    <?php else: ?>
                        <!-- Disable delete and promote buttons for admin users -->
                        <button disabled>Admin - Cannot Delete</button>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
<?php else: ?>
    <p>No users found.</p>
<?php endif; ?>

</div>

</body>
</html>

<?php $conn->close(); ?>
