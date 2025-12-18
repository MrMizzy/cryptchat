<?php
    session_start();
    include './connection.php';
    if (!isset($_SESSION['user_id'])){
        http_response_code(403); // Forbidden
        exit();
    }
    // Delete user account
    $delete_stmt = $conn->prepare("DELETE FROM users WHERE uid = ?");
    $delete_stmt->bind_param("i", $_SESSION['user_id']);
    if ($delete_stmt->execute()) {
        // Account deleted successfully
        session_unset();
        session_destroy();
        setcookie(session_name(), '', time() - 3600, '/');
        session_regenerate_id(true);
        echo json_encode(['success' => true]);
    } else {
        // Error deleting account
        echo json_encode(['error' => 'Failed to delete account.']);
    }
    $delete_stmt->close();
    $conn->close();
?>