<?php
session_start();
include "includes/db_connection.php";

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please log in to update your profile.'); window.location.href = 'login.php';</script>";
    exit;
}

$currentUserId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $fullname = $_POST['fullname'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];
    $mobile = $_POST['mobile'];
    $course = $_POST['course'];
    $address = $_POST['address'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $profile_picture = $_FILES['profile_picture'];

    $updates = [];
    $params = [];

    // Check if passwords match
    if ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match!');</script>";
    } else {
        // Update fields
        $fields = ['fullname', 'age', 'gender', 'mobile', 'course', 'address'];
        foreach ($fields as $field) {
            if (!empty($$field)) {
                $updates[] = "$field = ?";
                $params[] = $$field;
            }
        }

        // Update password if provided
        if (!empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $updates[] = "password = ?";
            $params[] = $hashedPassword;
        }

        // Update profile picture if uploaded
        if ($profile_picture['error'] === UPLOAD_ERR_OK) {
            $imagePath = 'uploads/' . basename($profile_picture['name']);
            if (move_uploaded_file($profile_picture['tmp_name'], $imagePath)) {
                $updates[] = "profile_image = ?";
                $params[] = $imagePath;
            }
        }

        // Build and execute the SQL query
        if ($updates) {
            $sql = "UPDATE users SET " . implode(", ", $updates) . " WHERE id = ?";
            $params[] = $currentUserId;

            $stmt = $pdo->prepare($sql);
            if ($stmt->execute($params)) {
                echo "<script>alert('Profile updated successfully!');</script>";
                header('Location: user_home.php');
            } else {
                echo "<script>alert('Error updating profile.');</script>";
                header('Location: user_home.php');
            }
        }
    }
}

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please log in to update your profile.'); window.location.href = 'login.php';</script>";
    exit;
}

// Fetch user details to display in the form
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$currentUserId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link href="https://fonts.googleapis.com/css?family=Poppins:400,500,600,700&display=swap" rel="stylesheet">
    <style>
        /* CSS Styling */
        @import url("https://fonts.googleapis.com/css?family=Poppins:400,500,600,700&display=swap");

        * {
            margin: 0;
            padding: 0;
            outline: none;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }


        .back_btn {
            position: absolute;
            top: 10px;
            left: 10px;
        }

        .back_btn ul {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .back_btn ul a {
            display: inline-block;
            padding: 5px 10px;
            color: #fff;
            font-size: 17px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
            outline: none;
            border: none;
            cursor: pointer;
            background: linear-gradient(115deg, #56d8e4, #9f01ea);
            border-radius: 5px;
            transition: all 0.4s;
            text-decoration: none; /* Add this line to remove underline */

        }

        .back_btn ul a:hover {
            background: linear-gradient(115deg, #9f01ea, #56d8e4);
        }

        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 10px;
            background: linear-gradient(115deg, #56d8e4 10%, #9f01ea 90%);
        }

        .container {
            position: relative;
            max-width: 800px;
            background: #fff;
            width: 100%;
            padding: 25px 40px 10px 40px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }

        .container .text {
            text-align: center;
            font-size: 41px;
            font-weight: 600;
            background: -webkit-linear-gradient(right, #56d8e4, #9f01ea, #56d8e4, #9f01ea);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        form .input-data {
            width: 100%;
            height: 50px;
            margin: 20px 0;
            position: relative;
        }

        .input-data input,
        .input-data select {
            display: block;
            width: 100%;
            height: 100%;
            padding: 10px 0;
            border: none;
            border-bottom: 2px solid rgba(0, 0, 0, 0.12);
            font-size: 17px;
        }

        .input-data label {
            position: absolute;
            left: 0;
            bottom: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
            color: #777;
        }

        .input-data input:focus ~ label,
        .input-data input:not(:placeholder-shown) ~ label,
        .input-data select:focus ~ label,
        .input-data select:not(:placeholder-shown) ~ label {
            bottom: 35px;
            font-size: 14px;
            color: #9f01ea;
        }

        .submit-btn .input-data input[type="submit"] {
            background: none;
            border: none;
            color: #fff;
            font-size: 17px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            position: relative;
            z-index: 2;
            background: -webkit-linear-gradient(right, #56d8e4, #9f01ea, #56d8e4, #9f01ea);
            transition: all 0.4s;
        }

        .submit-btn .input-data input[type="submit"]:hover {
            background: linear-gradient(115deg, #9f01ea, #56d8e4);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="text">Edit Profile</div>
        <div class="back_btn">
            <ul>
                <a href="user_home.php">Back Home</a>
            </ul>
        </div>

        <!-- Display the profile picture if it exists -->
        <?php if (!empty($user['profile_image'])): ?>
            <div style="text-align: center;">
                <img src="<?= htmlspecialchars($user['profile_image']) ?>" alt="Profile Picture" style="width: 150px; height: 150px; border-radius: 50%; margin-bottom: 15px;">
            </div>
        <?php endif; ?>

        <form action="profile.php" method="POST" enctype="multipart/form-data">
            <div class="input-data">
                <input type="text" name="fullname" id="fullname" value="<?= htmlspecialchars($user['fullname']) ?>" required>
                <label for="fullname">Full Name:</label>
            </div>

            <div class="input-data">
                <input type="number" name="age" id="age" value="<?= htmlspecialchars($user['age']) ?>">
                <label for="age">Age:</label>
            </div>

            <div class="input-data">
                <select name="gender" id="gender" required>
                    <option value="Male" <?= $user['gender'] == 'Male' ? 'selected' : '' ?>>Male</option>
                    <option value="Female" <?= $user['gender'] == 'Female' ? 'selected' : '' ?>>Female</option>
                    <option value="Other" <?= $user['gender'] == 'Other' ? 'selected' : '' ?>>Other</option>
                </select>
                <label for="gender">Gender:</label>
            </div>

            <div class="input-data">
                <input type="text" name="mobile" id="mobile" value="<?= htmlspecialchars($user['mobile']) ?>">
                <label for="mobile">Mobile:</label>
            </div>

            <div class="input-data">
                <input type="text" name="course" id="course" value="<?= htmlspecialchars($user['course']) ?>">
                <label for="course">Course:</label>
            </div>

            <div class="input-data">
                <input type="text" name="address" id="address" value="<?= htmlspecialchars($user['address']) ?>">
                <label for="address">Address:</label>
            </div>

            <div class="input-data">
                <input type="password" name="password" id="password">
                <label for="password">New Password:</label>
            </div>

            <div class="input-data">
                <input type="password" name="confirm_password" id="confirm_password">
                <label for="confirm_password">Confirm Password:</label>
            </div>

            <div class="input-data">
                <input type="file" name="profile_picture" id="profile_picture" accept="image/*">
                <label for="profile_picture">Profile Picture:</label>
            </div>

            <div class="submit-btn">
                <div class="input-data">
                    <input type="submit" name="update_profile" value="Save Changes">
                </div>
            </div>
        </form>
    </div>
</body>
</html>
