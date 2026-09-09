<?php
session_start();
require "config.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: login.php");
    exit();
}

$sql = "SELECT users.id, users.name, users.email, roles.role_name, users.created_at
        FROM users
        JOIN roles ON users.role_id = roles.id
        ORDER BY users.id DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Users</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="container">

    <h1>Manage Users</h1>

    <a href="dashboard.php" class="btn">Dashboard</a>
    <a href="add_user.php" class="btn">Add User</a>

    <br><br>

    <table border="1" width="100%" cellpadding="10">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Created At</th>
            <th>Actions</th>
        </tr>

        <?php while ($user = $result->fetch_assoc()): ?>

        <tr>
            <td><?php echo $user["id"]; ?></td>

            <td><?php echo htmlspecialchars($user["name"]); ?></td>

            <td><?php echo htmlspecialchars($user["email"]); ?></td>

            <td><?php echo htmlspecialchars($user["role_name"]); ?></td>

            <td><?php echo $user["created_at"]; ?></td>

            <td>
                <a href="edit_user.php?id=<?php echo $user["id"]; ?>">
                    Edit
                </a>

                |

                <a href="delete_user.php?id=<?php echo $user["id"]; ?>"
                   onclick="return confirm('Are you sure you want to delete this user?');">
                    Delete
                </a>
            </td>
        </tr>

        <?php endwhile; ?>

    </table>

</div>

</body>
</html>
