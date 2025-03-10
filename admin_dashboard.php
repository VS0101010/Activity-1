<?php
session_start();

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
    header('Location: login.php');
    exit;
}

// Database connection
include 'includes/db_connection.php';

// Fetch user counts and requests
$user_count = $pdo->query("SELECT COUNT(*) FROM users WHERE is_archived = 0 AND role != 'admin'")->fetchColumn();
$request_count = $pdo->query("SELECT COUNT(*) FROM requests WHERE status = 'pending'")->fetchColumn();
$validate_count = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'pending' AND role != 'admin'")->fetchColumn();
$approved_count = $pdo->query("SELECT COUNT(*) FROM requests WHERE status = 'approved'")->fetchColumn();


// Fetch active and archived users
$stmt = $pdo->query("SELECT * FROM users WHERE is_archived = 0 AND status IN ('approved', 'pending') AND role != 'admin'");
$activeUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query("SELECT * FROM users WHERE is_archived = 1 AND role != 'admin'");
$archivedUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query("SELECT * FROM users WHERE status IN ('pending', 'disapproved') AND role != 'admin'");
$validate = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Check the action
if (isset($_POST['action']) && $_POST['action'] === 'remove') {
    $request_id = $_POST['request_id'];
    
    // Prepare and execute the delete statement
    $stmt = $pdo->prepare("UPDATE requests SET is_visible = 0 WHERE id = ?");
    if ($stmt->execute([$request_id])) {
        echo "<script>alert('Notification removed successfully.');</script>";
    } else {
        echo "<script>alert('Error removing notification.');</script>";
    }
}

// Fetch pending requests
$stmt = $pdo->query("SELECT requests.id, requests.user_id, requests.request_type, requests.status, requests.timestamp, users.fullname
                     FROM requests 
                     JOIN users ON requests.user_id = users.id 
                     WHERE requests.status = 'pending'");
$pendingRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Function to approve or disapprove requests
// Function to approve, disapprove, or remove requests
function handleRequest($pdo, $request_id, $action) {
    if ($action == 'approve') {
        $query = "UPDATE requests SET status = 'approved', is_visible = 1 WHERE id = ?";
    } elseif ($action == 'disapprove') {
        $query = "UPDATE requests SET status = 'disapproved' WHERE id = ?";
    } elseif ($action == 'remove') {
        $query = "DELETE FROM requests WHERE id = ?";
    } else {
        return; // If the action is not recognized, exit the function
    }

    // Prepare and execute the query
    $stmt = $pdo->prepare($query);
    $stmt->execute([$request_id]);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $request_id = $_POST['request_id'];
    $action = $_POST['action'];
    
    // Call the handleRequest function to approve or disapprove
    handleRequest($pdo, $request_id, $action);
    
    // Refresh the page to reflect changes
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
        }

        .table-container {
            display: none;
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
        img {
            width: 50px;  
            height: 50px;
            object-fit: cover; 
        }

        .table .btn {
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9em;
        }

        .table .btn-success {
            background-color: #28a745;
            color: white;
        }

        .table .btn-danger {
            background-color: #dc3545;
            color: white;
        }

        .table .btn-edit{
            background-color: #dcd935;
            color: white;
        }

        .table .btn:hover {
            opacity: 0.8;
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
        .sidenav a:hover{
            background-color: grey;
            color: #ddd;
        }
        .content {
            margin-left: 270px;
        }

        .user-counts {
            display: inline-block;
            margin: 20px 0;
            text-align: center;
            width: 69%; /* Ensures that the block stretches horizontally */
        }

        .card {
            display: inline-block; /* Ensures that the cards are laid out horizontally */
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 200px;
            margin: 10px; /* Adds spacing between the cards */
        }

        /* .notifications {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            width: 300px;
        }
        .notification-item {
            margin-bottom: 10px;
        }
        .notification-item p {
            margin: 0;
        }
        .notification-item button {
            margin-top: 5px;
        } */

        .notifications {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            width: 300px;
            max-width: 400px;
            margin: auto;
        }

        .notification-list {
            max-height: 150px; /* Adjust height for the two-item view */
            overflow-y: auto;  /* Enable scrolling */
        }

        .notification-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #e0e0e0;
        }

        .notification-item:last-child {
            border-bottom: none; /* Remove border for the last item */
        }

        .notification-item p {
            margin: 0;
            flex-grow: 1; /* Allow text to grow */
        }

        .remove-button {
            background: transparent;
            border: none;
            color: #dc3545;
            font-size: 1.2em;
            cursor: pointer;
            transition: color 0.3s;
        }

        .remove-button:hover {
            color: #a71d2d; /* Darker red on hover */
        }

        @media (max-width: 600px) {
            .notifications {
                width: 90%; /* Responsive width */
            }
        }
    </style>
</head>
<body>
    <div class="sidenav">
        <h2>Admin Dashboard</h2>
        <a href="#" onclick="showTable('activeUsers')">Update/Archive Users</a>
        <a href="#" onclick="showTable('archivedUsers')">Archived Users</a>
        <a href="#" onclick="showTable('requestTable')">Approve/Disapprove Requests</a>
        <a href="#" onclick="showTable('Validate')">Approve/Disapprove User</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="content">
        <div class="user-counts">
            <div class="card">
                <h3>Total Users</h3>
                <p>Total Users: <?= $user_count ?></p>
                <h3>Pending Users</h3>
                <p>Total Pending: <?= $validate_count ?></p>
            </div>
            <div class="card">
                <h3>Pending Requests</h3>
                <p>Total Requests: <?= $request_count ?></p>
                <h3>Approved Request</h3>
                <p> Approved Requests: <?= $approved_count ?></p>

            </div>
        </div>
        
        <div class="notifications">
            <h3>Pending Requests</h3>
            <div class="notification-list">
                <?php foreach ($pendingRequests as $request): ?>
                    <div class="notification-item">
                        <p>
                            <strong><?= htmlspecialchars($request['fullname']) ?></strong> requested: <?= htmlspecialchars($request['request_type']) ?>
                        </p>
                        <form method="POST" class="remove-form">
                            <input type="hidden" name="request_id" value="<?= htmlspecialchars($request['id']) ?>">
                            <button type="submit" name="action" value="remove" class="remove-button">✖</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div id="activeUsers" class="table-container">
            <h3>Active Users</h3>
            <table class="table table-striped">
                <thead>
                    <caption>Users Information</caption>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Full Name</th>
                        <th>Age</th>
                        <th>Gender</th>
                        <th>Mobile</th>
                        <th>Course</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($activeUsers as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['id']) ?></td>
                            <td>
                                <?php if (!empty($user['profile_image'])): ?>
                                    <img src="<?= htmlspecialchars($user['profile_image']) ?>" alt="Profile Image">
                                <?php else: ?>
                                    No Image
                                <?php endif; ?>
                            </td>                           
                             <td><?= htmlspecialchars($user['fullname']) ?></td>
                            <td><?= htmlspecialchars($user['age']) ?></td>
                            <td><?= htmlspecialchars($user['gender']) ?></td>
                            <td><?= htmlspecialchars($user['mobile']) ?></td>
                            <td><?= htmlspecialchars($user['course']) ?></td>
                            <td><?= htmlspecialchars($user['status']) ?></td>
                            <td>
                                <!-- Edit Button -->
                                <form method="POST" action="edit_users_info.php">
                                    <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['id']) ?>">
                                    <button type="submit" name="action" value="edit" class="btn btn-edit">Edit</button>
                                </form>
                                <!-- Archive Button -->
                                <form method="POST" action="archive_user.php">
                                    <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['id']) ?>">
                                    <?php if ($user['is_archived'] == 0): ?>
                                        <button type="submit" name="action" value="archive" class="btn btn-success">Archive</button>
                                    <?php endif; ?>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div id="archivedUsers" class="table-container">
            <h3>Archived Users</h3>
            <table class="table table-striped">
                <thead>
                    <caption>Archived Users</caption>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Full Name</th>
                        <th>Address</th>
                        <th>Gender</th>
                        <th>Mobile</th>
                        <th>Course</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($archivedUsers as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['id']) ?></td>
                            <td>
                                <?php if (!empty($user['profile_image'])): ?>
                                    <img src="<?= htmlspecialchars($user['profile_image']) ?>" alt="Profile Image">
                                <?php else: ?>
                                    No Image
                                <?php endif; ?>
                            </td>  
                            <td><?= htmlspecialchars($user['fullname']) ?></td>
                            <td><?= htmlspecialchars($user['address']) ?></td>
                            <td><?= htmlspecialchars($user['gender']) ?></td>
                            <td><?= htmlspecialchars($user['mobile']) ?></td>
                            <td><?= htmlspecialchars($user['course']) ?></td>
                            <td><?= htmlspecialchars($user['status']) ?></td>
                            <td>
                                <form method="POST" action="archive_user.php">
                                    <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['id']) ?>">
                                    <button type="submit" name="action" value="unarchive" class="btn btn-danger">Unarchive</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div id="requestTable" class="table-container">
            <h3>Request Forms</h3>
            <table class="table table-striped">
                <thead>
                    <caption>Student Requests</caption>
                    <tr>
                        <th>User Name</th>
                        <th>Request Type</th>
                        <th>Status</th>
                        <th>Timestamp</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pendingRequests as $request): ?>
                        <tr>
                            <td><?= htmlspecialchars($request['fullname']) ?></td>
                            <td><?= htmlspecialchars($request['request_type']) ?></td>
                            <td><?= htmlspecialchars($request['status']) ?></td>
                            <td><?= htmlspecialchars($request['timestamp']) ?></td>
                            <td>
                                <form method="POST" action="">
                                    <input type="hidden" name="request_id" value="<?= htmlspecialchars($request['id']) ?>">
                                    <button type="submit" name="action" value="approve" class="btn btn-success">Approve</button>
                                    <button type="submit" name="action" value="disapprove" class="btn btn-danger">Disapprove</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div id="Validate" class="table-container">
            <h3>Validate Users</h3>
            <table class="table table-striped">
                <thead>
                    <caption>Validating Users</caption>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Full Name</th>
                        <th>Address</th>
                        <th>Gender</th>
                        <th>Mobile</th>
                        <th>Course</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($validate as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['id']) ?></td>
                            <td>
                                <?php if (!empty($user['profile_image'])): ?>
                                    <img src="<?= htmlspecialchars($user['profile_image']) ?>" alt="Profile Image">
                                <?php else: ?>
                                    No Image
                                <?php endif; ?>
                            </td>  
                            <td><?= htmlspecialchars($user['fullname']) ?></td>
                            <td><?= htmlspecialchars($user['address']) ?></td>
                            <td><?= htmlspecialchars($user['gender']) ?></td>
                            <td><?= htmlspecialchars($user['mobile']) ?></td>
                            <td><?= htmlspecialchars($user['course']) ?></td>
                            <td><?= htmlspecialchars($user['status']) ?></td>
                            <td>
                                <form method="POST" action="validate_user.php">
                                    <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['id']) ?>">
                                    <button type="submit" name="action" value="approved" class="btn btn-success">Approved</button>
                                </form>
                                <form method="POST" action="validate_user.php">
                                    <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['id']) ?>">
                                    <button type="submit" name="action" value="disapproved" class="btn btn-danger">Dispproved</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function showTable(tableId) {
            document.querySelectorAll('.table-container').forEach(function(table) {
                table.style.display = 'none';
            });
            document.getElementById(tableId).style.display = 'block';
        }
    </script>
</body>
</html>
