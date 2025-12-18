<?php
    include_once 'connection.php';
    if (!isset($_SESSION['user_id'])){
        http_response_code(403); // Forbidden
        header("Location: ../login.html");
        exit();
    } else {
        $username = $_SESSION['username'];
        $pfp_path = $_SESSION['pfp_path'];
    }

    function sendMessage($conn, $senderId, $receiverId, $message) : bool {
        $sql = "INSERT INTO messages (sender_id, receiver_id, message, created_at) VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iis", $senderId, $receiverId, $message);
        $success = $stmt->execute();
        $stmt->close();
        return $success;        
    }

    function getMessages($conn, $userId1, $userId2) : array {
        $sql = "SELECT sender_id, receiver_id, message, created_at 
                FROM messages 
                WHERE (sender_id = ? AND receiver_id = ?) 
                   OR (sender_id = ? AND receiver_id = ?)
                ORDER BY created_at ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiii", $userId1, $userId2, $userId2, $userId1);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $result;
    }

    function markAsRead($conn, $receiverId, $senderId) : bool {
        $sql = "UPDATE messages SET is_read = 1 WHERE receiver_id = ? OR sender_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $receiverId, $senderId);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    function loadMessages($conn, $userId1, $userId2) : void {
        $messages = getMessages($conn, $userId1, $userId2);
        foreach ($messages as $msg) {
            $is_sender = $msg['sender_id'] == $userId1;
            $msg_class = $is_sender ? 'sent' : 'received';
            echo "<div class='d-flex justify-content-between'>";
            echo    "<p class='small mb-1 text-muted'>". date('h:i', strtotime($msg['created_at'])) ."</p>";
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
    }

?>