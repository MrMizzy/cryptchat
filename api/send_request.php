<?php
    include 'connection.php';
    include_once 'friend_management.php';
    if (!isset($_SESSION['user_id'])){
        http_response_code(403); // Forbidden
        header("Location: ../login.html");
        exit();
    } else {
        $username = $_SESSION['username'];
        $pfp_path = $_SESSION['pfp_path'];
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['receiver_id'])) {
        http_response_code(400); // Bad Request
        echo json_encode(['error' => 'Invalid request']);
        exit();
    }

    $friend_id = intval($_POST['receiver_id']);
    $userId = $_SESSION['user_id'];

    // Ensure they are not already friends
    if (areFriends($conn, $userId, $friend_id)) {
        http_response_code(400);
        echo json_encode(['error' => 'You are already friends']);
        exit();
    }

    // Ensure not in pending requests
    if (isPendingRequest($conn, $userId, $friend_id)) {
        http_response_code(400);
        echo json_encode(['error' => 'Friend request already pending']);
        exit();
    }

    if (sendFriendRequest($conn, $userId, $friend_id)) {
        echo json_encode(['success' => 'Friend request sent']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to send friend request']);
    }

    exit();
?>