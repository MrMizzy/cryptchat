<?php
    session_start();
    // check if current pfp is default
    if ($_SESSION['pfp_path'] === './uploads/default.jpg') {
        // If default, do not delete previous file
        $keep_old_file = true;
    } else {
        $keep_old_file = false;
        $old_file_path = "." . $_SESSION['pfp_path'];
    }

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
    // Check if file was uploaded without errors
    if (isset($_FILES['pfp']) && $_FILES['pfp']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['pfp']['tmp_name'];
        $fileName = $_FILES['pfp']['name'];
        $fileSize = $_FILES['pfp']['size'];
        $fileType = $_FILES['pfp']['type'];
        $fileNameParts = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameParts));

        // Sanitize file name
        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;

        // Check if file has one of the following extensions
        $allowedfileExtensions = ['jpg', 'gif', 'png', 'jpeg'];
        if (in_array($fileExtension, $allowedfileExtensions)) {
            // Directory in which the uploaded file will be moved
            $uploadFileDir = '../uploads/';
            $dest_path = $uploadFileDir . $newFileName;

            if(move_uploaded_file($fileTmpPath, $dest_path)) {
                // Update user's profile picture path in the database
                $dest_path_db = './uploads/' . $newFileName; // Path to store in DB
                include 'connection.php';
                $stmt = $conn->prepare("UPDATE users SET pfp = ? WHERE uid = ?");
                $stmt->bind_param("si", $dest_path_db, $_SESSION['user_id']);
                if ($stmt->execute()) {
                    // Update session variable
                    $_SESSION['pfp_path'] = $dest_path_db;
                    echo json_encode(['message' => 'Profile picture updated successfully.', 'pfp_path' => $dest_path_db]);
                } else {
                    http_response_code(500); // Internal Server Error
                    echo json_encode(['error' => 'Database update failed.']);
                }
                $stmt->close();
                $conn->close();
            } else {
                http_response_code(500); // Internal Server Error
                echo json_encode(['error' => 'There was an error moving the uploaded file.']);
            }
        } else {
            http_response_code(400); // Bad Request
            echo json_encode(['error' => 'Upload failed. Allowed file types: ' . implode(',', $allowedfileExtensions)]);
        }
    } else {
        http_response_code(400); // Bad Request
        echo json_encode(['error' => 'No file uploaded or there was an upload error.']);
    }

    // Delete old profile picture file if not default
    if (!$keep_old_file && file_exists($old_file_path)) {
        unlink($old_file_path);
    }

    // Redirect back to profile page
    header("Location: ../profile.php");
    exit();
?>
  