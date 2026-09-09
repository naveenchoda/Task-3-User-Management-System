<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="container">

    <h1>Welcome, <?php echo htmlspecialchars($_SESSION["name"]); ?>!</h1>

    <p><strong>Email:</strong>
        <?php echo htmlspecialchars($_SESSION["email"]); ?>
    </p>

    <p><strong>Role:</strong>
        <?php echo htmlspecialchars($_SESSION["role"]); ?>
    </p>

    <a href="profile.php" class="btn">My Profile</a>

    <?php if ($_SESSION["role"] == "admin"): ?>

        <a href="users.php" class="btn">Manage Users</a>
        <a href="add_user.php" class="btn">Add User</a>

    <?php endif; ?>

    <br>

    <a href="logout.php" class="btn">Logout</a>

</div>

</body>
</html>
