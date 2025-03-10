<?php
session_start();
session_regenerate_id(true); // Regenerate session ID after login

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

include "includes/db_connection.php";

$currentUserId = $_SESSION['user_id']; // Get the current user's ID

// Fetch the user's full name from the database
$stmt = $pdo->prepare("SELECT fullname FROM users WHERE id = ?");
$stmt->execute([$currentUserId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Archive requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'remove') {
    $request_id = filter_input(INPUT_POST, 'request_id', FILTER_VALIDATE_INT);

    // Check if request_id is valid
    if ($request_id !== false) {
        // Archive the request by setting `is_visible` to 0
        $stmt = $pdo->prepare("UPDATE requests SET is_visible = 0 WHERE id = ?");
        if ($stmt->execute([$request_id])) {
            echo "<script>alert('Request archived successfully.');</script>";
        } else {
            echo "<script>alert('Error archiving the request.');</script>";
        }
    } else {
        echo "<script>alert('Invalid request ID.');</script>";
    }
}

// Fetch requests for the current user
$stmt = $pdo->prepare("SELECT * FROM requests WHERE user_id = ? AND is_visible = 1 AND status = 'approved' ");
$stmt->execute([$currentUserId]);
$userRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch approved requests for notification
$stmt = $pdo->prepare("SELECT id FROM requests WHERE status = 'approved' AND user_id = ? AND is_visible = 1");
$stmt->execute([$currentUserId]);
$approvedRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Home</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
        }

        .table-container {
            display: none; /* Initially hidden */
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            max-width: auto;
            margin: 20px 0;
        }

        h3 {
            text-align: center;
            color: #333;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .table caption {
            margin: 10px 0;
            font-size: 1.2em;
            font-weight: bold;
        }

        .table th, .table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        .table th {
            background-color: #007bff;
            color: white;
        }

        .table tr:hover {
            background-color: #f1f1f1;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        .sidenav {
            width: 250px;
            position: fixed;
            height: 100%;
            background-color: #f1f1f1;
            padding-top: 20px;
        }

        .sidenav a {
            padding: 10px;
            text-decoration: none;
            display: block;
            color: black;
        }

        .sidenav a:hover {
            background-color: grey;
            color: #ddd;
        }

        .content {
            margin-left: 270px;
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #28a745; /* Green color for success */
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            display: none; /* Initially hidden */
        }

        .table .btn {
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9em;
        }

        .table .btn-danger {
            background-color: #dc3545;
            color: white;
        }
    </style>

    <script>
        // Show notification on page load if there are approved requests
        window.onload = function() {
            const approvedRequests = <?php echo json_encode($approvedRequests); ?>;
            const notification = document.getElementById('notification');
            if (approvedRequests.length > 0) {
                notification.innerText = 'Your request has been approved!';
                notification.style.display = 'block';
                setTimeout(() => {
                    notification.style.display = 'none';
                }, 5000); // Hide after 5 seconds
            }
        };

        function showTable(tableId) {
            document.querySelectorAll('.table-container').forEach(function(table) {
                table.style.display = 'none';
            });
            document.getElementById(tableId).style.display = 'block';
        }
    </script>
</head>
<body>
    <div class="sidenav">
        <div class="welcome-message">
            <?php echo "<h2>Welcome, " . htmlspecialchars($user['fullname']) . "</h2>"; ?>
        </div>
        <a href="profile.php">Edit Profile</a>
        <a href="request_form.php">Submit Request</a>
        <a href="#" onclick="showTable('requestTable')">View My Requests</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="content">
        <div id="notification" class="notification"></div> <!-- Notification element -->

        <div id="requestTable" class="table-container">
            <h3>Your Requests</h3>
            <div class="request-list">
                <?php if (count($userRequests) > 0): ?>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Timestamp</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($userRequests as $request): ?>
                                <tr>
                                    <td><?= htmlspecialchars($request['id']) ?></td>
                                    <td><?= htmlspecialchars($request['request_type']) ?></td>
                                    <td><?= htmlspecialchars($request['status']) ?></td>
                                    <td><?= htmlspecialchars($request['timestamp']) ?></td>
                                    <td>
                                        <form method="POST">
                                            <input type="hidden" name="request_id" value="<?= htmlspecialchars($request['id']) ?>">
                                            <button type="submit" name="action" value="remove" class="btn btn-danger">Archive</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No requests found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>