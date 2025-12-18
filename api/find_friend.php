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
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['friend_username'])) {
        http_response_code(400); // Bad Request
        echo json_encode(['error' => 'Invalid request']);
        exit();
    }

    $friendUsername = $_POST['friend_username'];
    $userId = $_SESSION['user_id'];


    // Perform wildcard search for users
    $sql = "SELECT uid, username, pfp FROM users WHERE username LIKE ?";
    $stmt = $conn->prepare($sql);
    $likeUsername = "%" . $friendUsername . "%";
    $stmt->bind_param("s", $likeUsername);
    $stmt->execute();
    $result = $stmt->get_result();
    $friends = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if (empty($friends)) {
        http_response_code(404); // Not Found
        echo json_encode(['error' => 'No users found']);
        exit();
    }

    echo json_encode($friends);
    exit();
?>