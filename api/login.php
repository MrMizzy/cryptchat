<?php
    session_start();
    include 'connection.php';
    // Ensure the request method is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405); // Method Not Allowed
        echo json_encode(['error' => 'Method not allowed. Please use POST.']);
        exit();
    }

    // Get the POST data
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Basic validation
    if (empty($email) || empty($password)) {
        http_response_code(400); // Bad Request
        echo json_encode(['error' => 'All fields are required.']);
        exit();
    }

    // Prepare and bind
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        http_response_code(401); // Unauthorized
        echo json_encode(['error' => 'Invalid email or password.']);
        exit();
    }
    $user = $result->fetch_assoc();
    // Verify password
    if (!password_verify($password, $user['password_hash'])) {
        http_response_code(401); // Unauthorized
        echo json_encode(['error' => 'Invalid email or password.']);
        exit();
    }
    // Set session variables
    $_SESSION['user_id'] = $user['uid'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['pfp_path'] = $user['pfp'];
    echo json_encode(['message' => 'Login successful.']);
    $stmt->close();
    $conn->close();
?>