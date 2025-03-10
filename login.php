<?php
session_start();

include "includes/db_connection.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $fullName = htmlspecialchars(trim($_POST['fullname']));
        $password = $_POST['password'];

        // Check if the user exists by full name
        $stmt = $pdo->prepare("SELECT * FROM users WHERE fullname = ?");
        $stmt->execute([$fullName]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            if ($user['is_archived'] == 1) {
                echo "<script>alert('You are restricted!'); window.location.href='login.php';</script>";
                exit;
            } elseif ($user['status'] == 'approved') {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_name'] = $user['fullname'];

                // Redirect based on role
                header('Location: ' . ($user['role'] == 'admin' ? 'admin_dashboard.php' : 'user_home.php'));
                exit;
            } elseif ($user['status'] == 'pending') {
                echo "<script>alert('Your account is pending approval.'); window.location.href='login.php';</script>";
            } else {
                echo "<script>alert('You are restricted!'); window.location.href='login.php';</script>";
                exit;
            }
        } else {
            echo "<script>alert('Invalid credentials'); window.location.href='login.php';</script>";
        }
    } catch (PDOException $e) {
        echo "<script>alert('An error occurred, please try again later.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-wrapper {
            display: flex;
            width: 90%;
            max-width: 800px;
            height: 500px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }
        .login-image {
            width: 50%;
            /* background-image: url('img/background.jpeg');
            background-size: cover;
            background-position: center; */
            background: linear-gradient(-135deg, #71b7e6, #9b59b6);

        }
        .login-container {
            width: 50%;
            background-color: white;
            padding: 50px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            box-sizing: border-box;
        }
        .login-container h2 {
            margin-bottom: 20px;
            font-size: 24px;
            text-align: center;
            color: #333;
        }
        .login-container input[type="text"],
        .login-container input[type="password"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }
        .login-container input[type="text"]:focus,
        .login-container input[type="password"]:focus {
            outline: 2px solid #007bff;
        }
        .login-container input[type="submit"] {
            /* width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s ease; */
            width: 100%;
            border-radius: 5px;
            border: none;
            padding: 12px;
            color: #fff;
            font-size: 18px;
            font-weight: 500;
            text-align: center;
            text-decoration: none;
            background: linear-gradient(135deg, #71b7e6, #9b59b6);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .login-container input[type="submit"]:hover {
            /* background-color: #0056b3; */
            background: linear-gradient(-135deg, #71b7e6, #9b59b6);
        }
        .login-container .register-btn {
            margin-top: 20px;
            text-align: center;
        }
        .login-container .register-btn a {
            color: #007bff;
            text-decoration: none;
        }
        .login-container .register-btn a:hover {
            text-decoration: underline;
        }
        @media (max-width: 768px) {
            .login-wrapper {
                flex-direction: column;
                height: auto;
            }
            .login-image {
                width: 100%;
                height: 200px;
            }
            .login-container {
                width: 100%;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-image"></div>
        <div class="login-container">
            <h2>Login</h2>
            <form method="POST" action="">
                <input type="text" name="fullname" placeholder="Enter your full name" required>
                <input type="password" name="password" placeholder="Enter your password" required>
                <input type="submit" value="Login">
            </form>
            <div class="register-btn">
                <p>Don't have an account? <a href="register.php">Register</a></p>
            </div>
        </div>
    </div>
</body>
</html>
