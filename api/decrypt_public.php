<?php
    session_start();
    if (!isset($_SESSION['user_id'])) {
        http_response_code(403); // Forbidden
        exit();
    }
    // check if method is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405); // Method Not Allowed
        exit();
    }
    // get message, n, e from POST data
    $jsonData = file_get_contents('php://input');
    $data = json_decode($jsonData, true);
    if (!isset($data['message']) || !isset($data['n']) || !isset($data['e'])) {
        http_response_code(400); // Bad Request
        exit();
    }

    $message = trim($data['message']);
    $n = (int) $data['n'];
    $e = (int) $data['e'];

    $message = escapeshellarg($message);
    $n = escapeshellarg($n);
    $e = escapeshellarg($e);
    $execution_string = "python3 ../encryption_tools/public.py d $message $n $e";

    $decrypted_message = shell_exec($execution_string);
    echo json_encode(['decrypted_message' => $decrypted_message]);
    exit();
?>