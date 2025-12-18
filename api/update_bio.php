<?php
    session_start();
    include 'connection.php';
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

    // Get the new bio from POST data
    if (!isset($_POST['bio'])) {
        http_response_code(400); // Bad Request
        echo json_encode(['error' => 'Bio is required.']);
        echo json_encode(['bio' => var_dump($_POST)]);
        exit();
    }
    $new_bio = trim($_POST['bio']);
    // Update the bio in the database
    $stmt = $conn->prepare("UPDATE users SET bio = ? WHERE uid = ?");
    $stmt->bind_param("si", $new_bio, $_SESSION['user_id']);
    if ($stmt->execute()) {
        echo json_encode(['success' => 'Bio updated successfully.']);
    } else {
        echo json_encode(['error' => 'Failed to update bio.']);
    }
    $stmt->close();
    $conn->close();
?>