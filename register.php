<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Connect to the database
        $pdo = new PDO("mysql:host=localhost;dbname=school_portal", "root", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Get form data
        $fullname = $_POST['fullname'];
        $age = $_POST['age'];
        $gender = $_POST['gender'];
        $mobile = $_POST['mobile'];
        $course = $_POST['course'];
        $address = $_POST['address'];
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        // Check if passwords match
        if ($password !== $confirm_password) {
            echo "<script>alert('Passwords do not match!');</script>";
        } else {
            // Check if fullname already exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE fullname = ?");
            $stmt->execute([$fullname]);
            $nameExists = $stmt->fetchColumn();

            if ($nameExists) {
                echo "<script>alert('This name is already registered. Please use a different name.');</script>";
            } else {
                // Hash the password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // Handle the image upload
                $imagePath = NULL; // Default to NULL if no image is uploaded
                if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                    $imageTmpName = $_FILES['profile_image']['tmp_name'];
                    $imageName = $_FILES['profile_image']['name'];
                    $imageSize = $_FILES['profile_image']['size'];
                    $imageType = $_FILES['profile_image']['type'];

                    // Validate the image file
                    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                    if (in_array($imageType, $allowedTypes) && $imageSize <= 5000000) {
                        $uploadDir = 'uploads/';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0777, true);
                        }
                        $imagePath = $uploadDir . basename($imageName);

                        if (!move_uploaded_file($imageTmpName, $imagePath)) {
                            echo "<script>alert('Error uploading the image.');</script>";
                            $imagePath = NULL;
                        }
                    } else {
                        echo "<script>alert('Invalid image type or size.');</script>";
                    }
                }

                // Insert user data into the database
                $stmt = $pdo->prepare("INSERT INTO users (fullname, age, gender, mobile, course, address, password, profile_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$fullname, $age, $gender, $mobile, $course, $address, $hashedPassword, $imagePath]);

                echo "<script>alert('Registration successful!');</script>";
                header('Location: login.php');
                exit();
            }
        }
    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage();
    }
}
?>
<?php
// Your PHP code remains the same
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 10px;
            background: linear-gradient(135deg, #71b7e6, #9b59b6);
        }

        .container {
            max-width: 700px;
            width: 100%;
            background-color: #fff;
            padding: 25px 30px;
            border-radius: 5px;
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.15);
        }

        .container h2 {
            width: 100%;
            font-size: 25px;
            font-weight: 500;
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        .user-details {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .input-box {
            width: calc(50% - 10px);
        }

        .input-box span.details {
            display: block;
            font-weight: 500;
            margin-bottom: 5px;
            color: #333;
        }

        .input-box input,
        .input-box select {
            height: 45px;
            width: 100%;
            outline: none;
            font-size: 16px;
            border-radius: 5px;
            padding: 0 15px;
            border: 1px solid #ccc;
            transition: all 0.3s ease;
        }

        .input-box input:focus,
        .input-box select:focus {
            border-color: #9b59b6;
        }

        .button-container {
            width: 100%;
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
        }

        .login{
            padding: 10px;
        }

        .button-container a,
        .button-container input[type="submit"] {
            width: 48%;
            height: 45px;
            border-radius: 5px;
            border: none;
            color: #fff;
            font-size: 18px;
            font-weight: 500;
            text-align: center;
            text-decoration: none;
            background: linear-gradient(135deg, #71b7e6, #9b59b6);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .button-container a:hover,
        .button-container input[type="submit"]:hover {
            background: linear-gradient(-135deg, #71b7e6, #9b59b6);
        }

        @media (max-width: 584px) {
            .container {
                max-width: 100%;
            }

            .input-box {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Register</h2>
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="user-details">
                <div class="input-box">
                    <span class="details">Full Name</span>
                    <input type="text" name="fullname" required>
                </div>
                <div class="input-box">
                    <span class="details">Age</span>
                    <input type="number" name="age" required>
                </div>
                <div class="input-box">
                    <span class="details">Gender</span>
                    <select name="gender" required>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="input-box">
                    <span class="details">Mobile #</span>
                    <input type="text" name="mobile" required>
                </div>
                <div class="input-box">
                    <span class="details">Course</span>
                    <input type="text" name="course" required>
                </div>
                <div class="input-box">
                    <span class="details">Address</span>
                    <input type="text" name="address" required>
                </div>
                <div class="input-box">
                    <span class="details">Password</span>
                    <input type="password" name="password" required>
                </div>
                <div class="input-box">
                    <span class="details">Confirm Password</span>
                    <input type="password" name="confirm_password" required>
                </div>
                <div class="input-box">
                    <span class="details">Profile Image</span>
                    <input type="file" name="profile_image" accept="image/*">
                </div>
            </div>
            <div class="button-container">
                <input type="submit" value="Register">
                <a href="login.php" class="login">Login</a>
            </div>
        </form>
    </div>
</body>
</html>
