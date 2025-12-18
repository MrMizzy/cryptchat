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
    // get message, slope, intercept from POST data
    $jsonData = file_get_contents('php://input');
    $data = json_decode($jsonData, true);
    if (!isset($data['message']) || !isset($data['slope']) || !isset($data['intercept'])) {
        http_response_code(400); // Bad Request
        exit();
    }

    $message = $data['message'];
    $slope = (int) $data['slope'];
    $intercept = (int) $data['intercept'];

    $message = escapeshellarg($message);
    $slope = escapeshellarg($slope);
    $intercept = escapeshellarg($intercept);
    $execution_string = "python ../encryption_tools/private.py d $message $slope $intercept";

    $decrypted_message = shell_exec($execution_string);
    echo json_encode(['decrypted_message' => $decrypted_message]);
    exit();
?>