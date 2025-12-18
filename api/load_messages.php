<?php
    session_start();
    include 'connection.php';
    include_once 'messaging.php';

    
    if (!isset($_SESSION['user_id'])){
        http_response_code(403); // Forbidden
        // header("Location: ../login.html");
        echo var_dump($_SESSION);
        echo "Forbidden";
        exit();
    }
    
    if ($_SERVER['REQUEST_METHOD'] !== 'GET' || !isset($_GET['friend_id'])) {
        http_response_code(405); // Method Not Allowed
        echo json_encode(['error' => 'Method Not Allowed']);
        exit();
    }
    
    $userId = (int) $_SESSION['user_id'];
    $friendId = (int) $_GET['friend_id'];
    

    $messages = getMessages($conn, $userId, $friendId);
    foreach ($messages as $msg) {
        $is_sender = $msg['sender_id'] == $userId;
        $msg_class = $is_sender ? 'sent' : 'received';
        echo "<div class='d-flex justify-content-between'>";
        echo    "<p class='small mb-1 text-muted'>". date('d-m-Y  h:i', strtotime($msg['created_at'])) ."</p>";
        echo "</div>";
        if ($is_sender) {
            echo "<div class='d-flex flex-row justify-content-end'>";
        } else {
            echo "<div class='d-flex flex-row justify-content-start'>";
        }
        echo    "<div>";
        echo        "<p class='small p-2 ms-3 mb-3 rounded-3 bg-body-tertiary'>" . htmlspecialchars($msg['message']) . "</p>";
        echo    "</div>";
        echo "</div>";
        }
    
?>