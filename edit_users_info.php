<?php
// Connect to the database
$pdo = new PDO("mysql:host=localhost;dbname=school_portal", "root", "");

// Get the user ID from the form
if (isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];

    // Fetch the user's data from the database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

// If the form is submitted, update the user information
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    // Get updated data
    $fullname = $_POST['fullname'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];
    $mobile = $_POST['mobile'];
    $course = $_POST['course'];
    
    // Optional: handle profile image update
    $profile_image = $user['profile_image']; // Keep the old image by default
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $imagePath = 'uploads/' . $_FILES['profile_image']['name'];
        move_uploaded_file($_FILES['profile_image']['tmp_name'], $imagePath);
        $profile_image = $imagePath;
    }

    // Update the user information
    $stmt = $pdo->prepare("UPDATE users SET fullname = ?, age = ?, gender = ?, mobile = ?, course = ?, profile_image = ? WHERE id = ?");
    $stmt->execute([$fullname, $age, $gender, $mobile, $course, $profile_image, $user_id]);

    echo "User information updated successfully!";
    header('Location: admin_dashboard.php'); // Redirect back to the admin dashboard
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link href="https://fonts.googleapis.com/css?family=Poppins:400,500,600,700&display=swap" rel="stylesheet">
    <style>
        /* Include the same styling from your profile edit page */
        @import url("https://fonts.googleapis.com/css?family=Poppins:400,500,600,700&display=swap");

        * {
            margin: 0;
            padding: 0;
            outline: none;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
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
            background: -webkit-linear-gradient(right, #56d8e4, #9f01ea);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .input-data {
            width: 100%;
            height: 50px;
            margin: 20px 0;
            position: relative;
        }

        .input-data input,
        .back,
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

        .back{
            text-align: center;

        }

        .submit-btn .input-data input[type="submit"],
        .submit-btn .input-data a {
            background: -webkit-linear-gradient(right, #56d8e4, #9f01ea);
            border: none;
            color: #fff;
            font-size: 17px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-decoration: none;
            cursor: pointer;
            position: relative;
            z-index: 2;
            transition: all 0.4s;
        }

        .submit-btn .input-data input[type="submit"]:hover{
            background: linear-gradient(115deg, #9f01ea, #56d8e4);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="text">Edit User Information</div>

        <!-- Display the profile picture if it exists -->
        <?php if (!empty($user['profile_image'])): ?>
            <div style="text-align: center;">
                <img src="<?= htmlspecialchars($user['profile_image']) ?>" alt="Profile Picture" style="width: 150px; height: 150px; border-radius: 50%; margin-bottom: 15px;">
            </div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['id']) ?>">

            <div class="input-data">
                <input type="text" name="fullname" value="<?= htmlspecialchars($user['fullname']) ?>" required>
                <label for="fullname">Full Name:</label>
            </div>

            <div class="input-data">
                <input type="number" name="age" value="<?= htmlspecialchars($user['age']) ?>">
                <label for="age">Age:</label>
            </div>

            <div class="input-data">
                <select name="gender" required>
                    <option value="Male" <?= $user['gender'] == 'Male' ? 'selected' : '' ?>>Male</option>
                    <option value="Female" <?= $user['gender'] == 'Female' ? 'selected' : '' ?>>Female</option>
                    <option value="Other" <?= $user['gender'] == 'Other' ? 'selected' : '' ?>>Other</option>
                </select>
                <label for="gender">Gender:</label>
            </div>

            <div class="input-data">
                <input type="text" name="mobile" value="<?= htmlspecialchars($user['mobile']) ?>">
                <label for="mobile">Mobile:</label>
            </div>

            <div class="input-data">
                <input type="text" name="course" value="<?= htmlspecialchars($user['course']) ?>">
                <label for="course">Course:</label>
            </div>

            <div class="input-data">
                <input type="file" name="profile_image" accept="image/*">
                <label for="profile_image">Profile Picture:</label>
            </div>

            <div class="submit-btn">
                <div class="input-data">
                    <input type="submit" name="update_user" value="Save Changes">
                </div>
            </div>

            <div class="submit-btn">
                <div class="input-data">
                    <a href="admin_dashboard.php" class="back">Back</a>

                </div>
            </div>
        </form>
    </div>
</body>
</html>