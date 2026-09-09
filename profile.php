<?php
session_start();
require "config.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

$stmt = $conn->prepare(
    "SELECT users.name, users.email, users.profile_picture, roles.role_name
     FROM users
     JOIN roles ON users.role_id = roles.id
     WHERE users.id = ?"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Profile</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="container">

    <h1>My Profile</h1>

    <?php if (!empty($user["profile_picture"])): ?>

        <img
            src="uploads/<?php echo htmlspecialchars($user["profile_picture"]); ?>"
            width="150"
            height="150"
            style="object-fit: cover; border-radius: 50%;"
        >

    <?php endif; ?>

    <p>
        <strong>Name:</strong>
        <?php echo htmlspecialchars($user["name"]); ?>
    </p>

    <p>
        <strong>Email:</strong>
        <?php echo htmlspecialchars($user["email"]); ?>
    </p>

    <p>
        <strong>Role:</strong>
        <?php echo htmlspecialchars($user["role_name"]); ?>
    </p>

    <a href="edit_profile.php" class="btn">Edit Profile</a>

    <a href="dashboard.php" class="btn">Dashboard</a>

</div>

</body>
</html>
