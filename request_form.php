<?php
session_start();
session_regenerate_id(true); // Regenerate session ID after login

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

include "includes/db_connection.php";

$currentUserId = $_SESSION['user_id']; // Get the current user's ID

$stmt = $pdo->prepare("SELECT fullname FROM users WHERE id = ?");
$stmt->execute([$currentUserId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    $fullName = htmlspecialchars($user['fullname']);
} else {
    $fullName = "User";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Insert request into the database
    $pdo = new PDO("mysql:host=localhost;dbname=school_portal", "root", "");
    $request_type = $_POST['request_type'];
    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("INSERT INTO requests (user_id, request_type) VALUES (?, ?)");
    $stmt->execute([$user_id, $request_type]);

    $message = "Request Submitted Successfully";
    header('Location: user_home.php?message='. urlencode($message));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Form</title>
    <style>
        /* Importing Google Fonts - Poppins */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: linear-gradient(135deg, #71b7e6, #9b59b6);
        }

        .container {
            width: 100%;
            max-width: 500px;
            background: #fff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h1 {
            font-size: 24px;
            color: #333;
            text-align: center;
            margin-bottom: 10px;
        }

        h2 {
            font-size: 20px;
            color: #555;
            text-align: center;
            margin-bottom: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        label {
            font-weight: 500;
            color: #333;
            font-size: 16px;
        }

        select, input[type="submit"] {
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
            outline: none;
            transition: all 0.3s ease;
        }

        select:focus, input[type="submit"]:focus {
            border-color: #9b59b6;
        }

        input[type="submit"] {
            cursor: pointer;
            color: #fff;
            background: linear-gradient(135deg, #71b7e6, #9b59b6);
            font-weight: 600;
        }

        input[type="submit"]:hover {
            background: linear-gradient(-135deg, #71b7e6, #9b59b6);
        }

        a {
            display: inline-block;
            text-align: center;
            text-decoration: none;
            color: #9b59b6;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        a:hover {
            color: #71b7e6;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome, <?php echo $fullName; ?></h1>
        <h2>Submit a Request</h2>
        <form method="POST" action="">
            <label for="request_type">Request Type:</label>
            <select name="request_type" id="request_type" required>
                <option value="TOR">TOR</option>
                <option value="COR">COR</option>
                <option value="COE">COE</option>
                <option value="COG">COG</option>
                <option value="Diploma">Diploma</option>
            </select>
            <input type="submit" value="Submit Request">
            <a href="user_home.php">Go Back</a>
        </form>
    </div>
</body>
</html>
