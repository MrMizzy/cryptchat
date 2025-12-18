<?php
    include 'connection.php';
    // Ensure the request method is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405); // Method Not Allowed
        echo json_encode(['error' => 'Method not allowed. Please use POST.']);
        exit();
    }

    // Get the POST data
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Basic validation
    if (empty($username) || empty($email) || empty($password)) {
        http_response_code(400); // Bad Request
        echo json_encode(['error' => 'All fields are required.']);
        exit();
    }

    // Check if username or email already exists
    $check_stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $check_stmt->bind_param("ss", $username, $email);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        http_response_code(409); // Conflict
        echo json_encode(['error' => 'Username or email already exists.']);
        exit();
    }
    $check_stmt->close();

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $hashed_password);

    // Execute the statement
    try {
        $result  = $stmt->execute();
        if ($result) {
            http_response_code(201); // Created
            echo json_encode(['message' => 'User registered successfully.']);
        } else {
            http_response_code(500); // Internal Server Error
            echo json_encode(['error' => 'Failed to register user.']);
        }
    } catch (Exception $e) {
        http_response_code(500); // Internal Server Error
        echo json_encode(['error' => 'Failed to register user. Error: ' . $e->getMessage()]);
    }
    $stmt->close();
    $conn->close();
?>