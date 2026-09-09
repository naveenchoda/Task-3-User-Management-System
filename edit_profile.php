<?php
session_start();
require "config.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$message = "";

/* Get current profile */
$stmt = $conn->prepare(
    "SELECT name, email, profile_picture FROM users WHERE id = ?"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

$stmt->close();


/* Update profile */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);

    if (empty($name) || empty($email)) {

        $message = "Name and email are required.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Enter a valid email address.";

    } else {

        /* Check duplicate email */
        $check = $conn->prepare(
            "SELECT id FROM users WHERE email = ? AND id != ?"
        );

        $check->bind_param("si", $email, $user_id);
        $check->execute();

        $checkResult = $check->get_result();

        if ($checkResult->num_rows > 0) {

            $message = "This email is already used.";

        } else {

            $profile_picture = $user["profile_picture"];

            /* Profile picture upload */
            if (isset($_FILES["profile_picture"]) &&
                $_FILES["profile_picture"]["error"] == 0) {

                $maxSize = 2 * 1024 * 1024;

                if ($_FILES["profile_picture"]["size"] > $maxSize) {

                    $message = "Image must be less than 2MB.";

                } else {

                    $allowedTypes = [
                        "image/jpeg",
                        "image/png",
                        "image/gif"
                    ];

                    $fileType = mime_content_type(
                        $_FILES["profile_picture"]["tmp_name"]
                    );

                    if (!in_array($fileType, $allowedTypes)) {

                        $message = "Only JPG, PNG and GIF images are allowed.";

                    } else {

                        $extension = pathinfo(
                            $_FILES["profile_picture"]["name"],
                            PATHINFO_EXTENSION
                        );

                        $newFileName = uniqid("profile_", true) . "." . $extension;

                        $uploadPath = "uploads/" . $newFileName;

                        if (move_uploaded_file(
                            $_FILES["profile_picture"]["tmp_name"],
                            $uploadPath
                        )) {

                            $profile_picture = $newFileName;

                        } else {

                            $message = "Failed to upload image.";
                        }
                    }
                }
            }

            if ($message == "") {

                $update = $conn->prepare(
                    "UPDATE users
                     SET name = ?, email = ?, profile_picture = ?
                     WHERE id = ?"
                );

                $update->bind_param(
                    "sssi",
                    $name,
                    $email,
                    $profile_picture,
                    $user_id
                );

                if ($update->execute()) {

                    $_SESSION["name"] = $name;
                    $_SESSION["email"] = $email;

                    header("Location: profile.php");
                    exit();

                } else {

                    $message = "Failed to update profile.";
                }

                $update->close();
            }
        }

        $check->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Profile</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="container">

    <h1>Edit Profile</h1>

    <?php if ($message != ""): ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">

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

        <input
            type="file"
            name="profile_picture"
            accept=".jpg,.jpeg,.png,.gif"
        >

        <button type="submit">Update Profile</button>

    </form>

    <br>

    <a href="profile.php" class="btn">Back to Profile</a>

</div>

</body>
</html>
