<?php
session_start();
require "config.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: login.php");
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $role_id = (int)$_POST["role_id"];

    if (empty($name) || empty($email) || empty($password)) {
        $message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters.";
    } else {

        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {

            $message = "Email already exists.";

        } else {

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare(
                "INSERT INTO users (name, email, password, role_id)
                 VALUES (?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "sssi",
                $name,
                $email,
                $hashedPassword,
                $role_id
            );

            if ($stmt->execute()) {
                $message = "User added successfully!";
            } else {
                $message = "Failed to add user.";
            }

            $stmt->close();
        }

        $check->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add User</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="container">

    <h1>Add New User</h1>

    <?php if ($message != ""): ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <form method="POST">

        <input
            type="text"
            name="name"
            placeholder="Enter Name"
            required
        >

        <input
            type="email"
            name="email"
            placeholder="Enter Email"
            required
        >

        <input
            type="password"
            name="password"
            placeholder="Enter Password"
            required
        >

        <select name="role_id" required>
            <option value="1">User</option>
            <option value="2">Admin</option>
        </select>

        <button type="submit">Add User</button>

    </form>

    <br>

    <a href="users.php" class="btn">Back to Users</a>

</div>

</body>
</html>
