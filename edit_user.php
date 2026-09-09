<?php
session_start();
require "config.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: login.php");
    exit();
}

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: users.php");
    exit();
}

$id = (int)$_GET["id"];
$message = "";

/* Fetch user details */
$stmt = $conn->prepare(
    "SELECT id, name, email, role_id FROM users WHERE id = ?"
);
$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows != 1) {
    header("Location: users.php");
    exit();
}

$user = $result->fetch_assoc();
$stmt->close();

/* Update user */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $role_id = (int)$_POST["role_id"];

    if (empty($name) || empty($email)) {

        $message = "Name and email are required.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Enter a valid email address.";

    } else {

        /* Check whether email belongs to another user */
        $check = $conn->prepare(
            "SELECT id FROM users WHERE email = ? AND id != ?"
        );

        $check->bind_param("si", $email, $id);
        $check->execute();

        $checkResult = $check->get_result();

        if ($checkResult->num_rows > 0) {

            $message = "This email is already used by another user.";

        } else {

            $update = $conn->prepare(
                "UPDATE users
                 SET name = ?, email = ?, role_id = ?
                 WHERE id = ?"
            );

            $update->bind_param(
                "ssii",
                $name,
                $email,
                $role_id,
                $id
            );

            if ($update->execute()) {
                header("Location: users.php");
                exit();
            } else {
                $message = "Failed to update user.";
            }

            $update->close();
        }

        $check->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit User</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="container">

    <h1>Edit User</h1>

    <?php if ($message != ""): ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <form method="POST">

        <input
            type="text"
            name="name"
            value="<?php echo htmlspecialchars($user["name"]); ?>"
            placeholder="Enter Name"
            required
        >

        <input
            type="email"
            name="email"
            value="<?php echo htmlspecialchars($user["email"]); ?>"
            placeholder="Enter Email"
            required
        >

        <select name="role_id" required>

            <option value="1"
                <?php if ($user["role_id"] == 1) echo "selected"; ?>>
                User
            </option>

            <option value="2"
                <?php if ($user["role_id"] == 2) echo "selected"; ?>>
                Admin
            </option>

        </select>

        <button type="submit">Update User</button>

    </form>

    <br>

    <a href="users.php" class="btn">Back to Users</a>

</div>

</body>
</html>
