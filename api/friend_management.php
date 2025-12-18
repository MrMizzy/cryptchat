<?php
    session_start();
    include_once 'connection.php';
    if (!isset($_SESSION['user_id'])){
        http_response_code(403); // Forbidden
        header("Location: ../login.html");
        exit();
    } else {
        $username = $_SESSION['username'];
        $pfp_path = $_SESSION['pfp_path'];
    }

    // Get friends and pending requests
    function getFriends($conn, $userId) : array {
        $sql = "SELECT u.uid, u.username, u.pfp, f.n, f.e
                FROM users u
                JOIN friends f ON u.uid = f.user1
                WHERE f.user2 = ? OR f.user1 = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $userId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $friends = [];
        while ($row = $result->fetch_assoc()) {
            if ($row['uid'] == $userId) {
                continue; // Skip self
            }
            $friends[] = $row;
        }
        $stmt->close();
        $sql = "SELECT u.uid, u.username, u.pfp, f.n, f.e
                FROM users u
                JOIN friends f ON u.uid = f.user2
                WHERE f.user2 = ? OR f.user1 = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $userId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            if ($row['uid'] == $userId) {
                continue; // Skip self
            }
            $friends[] = $row;
        }
        return $friends;
    }

    function getPendingFriendRequests($conn, $userId) : array {
        $sql = "SELECT u.uid, u.username, u.pfp
                FROM users u
                JOIN requests r ON u.uid = r.sender_id
                WHERE r.receiver_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $requests = [];
        while ($row = $result->fetch_assoc()) {
            $requests[] = $row;
        }
        return $requests;
    }

    // Check if two users are friends
    function areFriends($conn, $userId1, $userId2) : bool {
        $sql = "SELECT COUNT(*) as count 
                FROM friends 
                WHERE (user1 = ? AND user2 = ?) OR (user1 = ? AND user2 = ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiii", $userId1, $userId2, $userId2, $userId1);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result['count'] > 0;
    }

    // Get a user by ID
    function getUserById($conn, $userId) : array {
        $sql = "SELECT uid, username, pfp FROM users WHERE uid = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result;
    }

    function isPendingRequest($conn, $senderId, $receiverId) : bool {
        $sql = "SELECT COUNT(*) as count 
                FROM requests 
                WHERE sender_id = ? AND receiver_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $senderId, $receiverId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result['count'] > 0;
    }

    function acceptFriendRequest($conn, $senderId, $receiverId) : bool {
        // Start transaction
        $conn->begin_transaction();
        try {
            // Delete the request
            $sqlDelete = "DELETE FROM requests WHERE sender_id = ? AND receiver_id = ?";
            $stmtDelete = $conn->prepare($sqlDelete);
            $stmtDelete->bind_param("ii", $senderId, $receiverId);
            $stmtDelete->execute();
            $stmtDelete->close();

            // generate keys for friendship
            $execution_string = "python3 ../encryption_tools/rsa_keygen.py";
            $key = shell_exec($execution_string);
            list($n, $e) = explode(",", trim($key));

            // Add to friends
            $sqlInsert = "INSERT INTO friends (user1, user2, n, e) VALUES (?, ?, ?, ?)";
            $stmtInsert = $conn->prepare($sqlInsert);
            $stmtInsert->bind_param("iiii", $senderId, $receiverId, $n, $e);
            $stmtInsert->execute();
            $stmtInsert->close();

            // Commit transaction
            $conn->commit();
            return true;
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            return false;
        }
    }

    function declineFriendRequest($conn, $senderId, $receiverId) : bool {
        $sql = "DELETE FROM requests WHERE sender_id = ? AND receiver_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $senderId, $receiverId);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    function sendFriendRequest($conn, $senderId, $receiverId) : bool {
        $sql = "INSERT INTO requests (sender_id, receiver_id) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $senderId, $receiverId);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }