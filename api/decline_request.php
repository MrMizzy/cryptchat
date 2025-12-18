<?php
    include 'connection.php';
    include_once 'friend_management.php';
    if (!isset($_SESSION['user_id'])){
        http_response_code(403); // Forbidden
        header("Location: login.html");
        exit();
    } else {
        $username = $_SESSION['username'];
        $pfp_path = $_SESSION['pfp_path'];
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['sender_id'])) {
        http_response_code(400); // Bad Request
        echo "Invalid request.";
        exit();
    }

    $sender_id = intval($_POST['sender_id']);
    $logged_in_user_id = $_SESSION['user_id'];

    if (!isPendingRequest($conn, $sender_id, $logged_in_user_id)) {
        http_response_code(403); // Forbidden
        echo "No pending request found.";
        exit();
    }

    if (declineFriendRequest($conn, $sender_id, $logged_in_user_id)) {
        http_response_code(200);
        echo "Friend request declined.";
    } else {
        http_response_code(500);
        echo "Error declining friend request.";
    }
?>