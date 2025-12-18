<?php
    include 'connection.php';
    include_once 'friend_management.php';

    if (!isset($_SESSION['user_id'])) {
        http_response_code(403); // Forbidden
        header("Location: login.html");
        exit();
    } else {
        $userId = $_SESSION['user_id'];
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['friend_id'])) {
        http_response_code(400); // Bad Request
        echo "Invalid request.";
        exit();
    }

    $friendId = intval($_POST['friend_id']);
    if (!areFriends($conn, $userId, $friendId)) {
        http_response_code(403); // Forbidden
        echo "You are not friends with this user.";
        exit();
    }
    $sql = "DELETE FROM friends WHERE (user1 = ? AND user2 = ?) OR (user1 = ? AND user2 = ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiii", $userId, $friendId, $friendId, $userId);
    $stmt->execute();
    $stmt->close();
    echo "Friend removed successfully.";