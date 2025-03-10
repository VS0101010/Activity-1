<?php
session_start();

// Error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ensure only admins can access this page


include 'includes/db_connection.php'; // Ensure this sets up your PDO connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['user_id']) && isset($_POST['action'])) {
        $user_id = $_POST['user_id'];
        $action = $_POST['action'];
        
        // Debugging output
        error_log("User ID: $user_id, Action: $action");

        // Prepare the SQL statement based on the action
        if ($action === 'archive') {
            // Archive the user
            $query = "UPDATE users SET is_archived = 1 WHERE id = :id";
            $message = 'User archived successfully';
        } elseif ($action === 'unarchive') {
            // Unarchive the user
            $query = "UPDATE users SET is_archived = 0 WHERE id = :id";
            $message = 'User unarchived successfully';
        } else {
            echo "Invalid action.";
            exit();
        }

        // Execute the query
        try {
            $stmt = $pdo->prepare($query);
            $stmt->execute(['id' => $user_id]);

            // Redirect back to the admin page with a success message
            header('Location: admin_dashboard.php?message=' . urlencode($message));
            exit();
        } catch (PDOException $e) {
            echo "Error updating user: " . htmlspecialchars($e->getMessage());
            exit();
        }
    } else {
        echo "User ID or action not provided.";
    }
} else {
    echo "Invalid request method.";
}
