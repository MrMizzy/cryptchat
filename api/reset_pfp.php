<?php
    session_start();
    if (!isset($_SESSION['user_id'])){
        http_response_code(403); // Forbidden
        header("Location: login.html");
        exit();
    } else {
        $username = $_SESSION['username'];
        $pfp_path = $_SESSION['pfp_path'];
    }
    // Ensure the request method is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405); // Method Not Allowed
        echo json_encode(['error' => 'Method not allowed. Please use POST.']);
        exit();
    }
    include 'connection.php';
    // Check whether to delete old profile picture
    if ($_SESSION['pfp_path'] !== '../uploads/default.jpg') {
        $old_pfp_path = "." . $_SESSION['pfp_path'];
        if (file_exists($old_pfp_path)) {
            unlink($old_pfp_path);
        }
    }
    // Reset profile picture to default
    $default_pfp = './uploads/default.jpg';
    $stmt = $conn->prepare("UPDATE users SET pfp = ? WHERE uid = ?");
    $stmt->bind_param("si", $default_pfp, $_SESSION['user_id']);
    if ($stmt->execute()) {
        $_SESSION['pfp_path'] = $default_pfp;
        echo json_encode(['pfp_path' => $default_pfp]);
    } else {
        echo json_encode(['error' => 'Failed to reset profile picture.']);
    }
?>