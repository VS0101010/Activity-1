<?php
session_start();

error_reporting(E_ALL);
ini_set ('display_errors',1);

try{
    $pdo =  new PDO('mysql:host=localhost;dbname=school_portal', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

}
catch (PDOException $e){
    echo 'ERROR: ' . $e->getMessage();
}


if($_SERVER['REQUEST_METHOD'] === 'POST'){
    if(isset($_POST['user_id']) && isset($_POST['action'])){
        $user_id = $_POST['user_id'];
        $action = $_POST['action'];

        error_log("UserID: $user_id Action: $action");

        if($action == 'approved'){
            $query = "UPDATE users SET status = 'approved' WHERE id = :id";
            $message = "User is now Active";
        }
        elseif($action == 'disapproved'){
            $query = "UPDATE users SET status = 'disapproved' WHERE id = :id";
            $message = "User is  now Inactive";
        }
        else{
            echo "Invalid actions";
            exit;
        }
    }
    else{
        echo "Invalid request";
    }

    try{
        $stmt = $pdo->prepare($query);
        $stmt->execute(['id' => $user_id]);

        header('Location: admin_dashboard.php?message=' . urlencode($message));
        exit;
    }
    catch (PDOException $e){
        echo 'Error'. htmlspecialchars($e->$message());
        exit;
    }
}













