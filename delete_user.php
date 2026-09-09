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

/* Prevent admin from deleting their own account */
if ($id == $_SESSION["user_id"]) {
    die("You cannot delete your own account.");
}

$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: users.php");
    exit();
} else {
    echo "Failed to delete user.";
}

$stmt->close();
?>
