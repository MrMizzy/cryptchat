<?php
    include './api/connection.php';
    include_once './api/friend_management.php';
    include_once './api/messaging.php';
    if (!isset($_SESSION['user_id'])){
        http_response_code(403); // Forbidden
        header("Location: login.html");
        exit();
    } else {
        $username = $_SESSION['username'];
        $pfp_path = $_SESSION['pfp_path'];
    }

    $friends = getFriends($conn, $_SESSION['user_id']);

    // Handle sending a message
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['receiver_id']) && isset($_POST['message'])) {
        $receiver_id = (int) $_POST['receiver_id'];
        $message = trim($_POST['message']);

        // encrypt message before sending into database
        $sql = "SELECT f.n, f.e 
                FROM friends f 
                WHERE (user1 = ? AND user2 = ?) OR (user1 = ? AND user2 = ?);";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiii", $_SESSION['user_id'], $receiver_id, $receiver_id, $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $n = $result['n'];
        $e = $result['e'];


        $message = escapeshellarg($message);
        $n = escapeshellarg($n);
        $e = escapeshellarg($e);
        $encrypted_message = shell_exec("python3 ./encryption_tools/public.py e $message $n $e");

        if (areFriends($conn, $_SESSION['user_id'], $receiver_id) && !empty($message)) {
            if (sendMessage($conn, $_SESSION['user_id'], $receiver_id, $encrypted_message)) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['error' => 'Failed to send message.']);
            }
        } else {
            echo json_encode(['error' => 'You can only message your friends.']);
        }

        header('Location: messages.php?friend_id=' . $receiver_id);
        exit();
    }

    // Load messages with a specific friend
    $selected_friend = null;
    $messages = [];

    if (isset($_GET['friend_id'])) {
        $friend_id = (int) $_GET['friend_id'];
    
        if (areFriends($conn, $_SESSION['user_id'], $friend_id)) {
            $selected_friend = getUserById($conn, $friend_id);
            $messages = getMessages($conn, $_SESSION['user_id'], $friend_id);
            markAsRead($conn, $_SESSION['user_id'], $friend_id);
        } else {
            echo "User ID: {$_SESSION['user_id']} Friend ID: $friend_id";
            echo "You can only view messages with your friends.";
            exit();
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Encrypt Messages</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .chat-list-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            text-decoration: none;
            color: inherit;
            border-bottom: 1px solid #f2f2f2;
            transition: background 0.2s;
        }

        .chat-list-item:hover, .chat-list-item.active {
            background: #0d6efd;
        }

        .chat-list-item img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 1rem;
            object-fit: cover;
        }

        .chat-header {
            background: #0d6efd;
            color: white;
            padding: 1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .chat-header img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }

        .chat-messages {
            flex: 1;
            padding: 1rem;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .chat-input {
            display: flex;
            padding: 1rem;
            background: white;
            border-top: 1px solid #eee;
            gap: 0.5rem;
        }

        .chat-input input {
            flex: 1;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 25px;
            outline: none;
        }

        .chat-input button {
            width: 50px !important;
            height: 50px;
            border-radius: 50% !important;
            background: #0d6efd;
            color: white;
            border: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <!-- Top Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-primary text-bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand flex" href="#">
                <img src="<?php echo $pfp_path ?>" alt="Profile Picture" style="width: 40px" class="rounded-pill">
            </a>        
            <p class="navbar-text"><?php echo $username ?></p>
        </div>
    </nav>
    <main class="container-fluid p-0" style="overflow: hidden;">
        <div class="d-flex" style="height: 100%;">
            <!-- Side Navigation -->
            <nav class="nav flex-column bg-light p-3" style="width: 200px;">
                <a class="nav-link" href="./encrypt_private.php">Private Encryption</a>
                <a class="nav-link" href="./decrypt_private.php">Private Decryption</a>
                <a class="nav-link" href="./decrypt_public.php">Public Decryption</a>
                <a class="nav-link" href="./friends.php">Manage Friends</a>
                <a class="nav-link" href="./messages.php">Messages</a>
                <a class="nav-link" href="./profile.php">Profile</a>
                <a class="nav-link" href="./api/logout.php">Log out</a>
            </nav>
            <section class="flex-grow-1 py-3">
                <!-- Message List -->
                <div class="container-fluid d-flex p-2">
                    <!-- Show all chats here -->
                    <div id="chat-list" class="bg-dark-subtle p-6" style="width: 300px; overflow-y: auto; border-right: 1px solid #ddd;">
                        <?php if (empty($friends)): ?>
                            <div style="padding: 1rem; text-align: center; color: #666;">
                            <p>No Friends Yet</p>
                            <p><small>Accept friend requests to start messaging</small></p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($friends as $user): ?>
                            <a href="messages.php?friend_id=<?php echo $user['uid']; ?>" 
                                class="chat-list-item <?php echo ($selected_friend && $selected_friend['uid'] == $user['uid']) ? 'active' : ''; ?>">
                                <img src="<?php echo htmlspecialchars($user['pfp']); ?>" 
                                    alt="<?php echo htmlspecialchars($user['username']); ?>">
                                <div>
                                <h4><?php echo htmlspecialchars($user['username']); ?></h4>
                                <small>n: <?php echo htmlspecialchars($user['n']); ?></small>
                                <small>e: <?php echo htmlspecialchars($user['e']); ?></small>
                                </div>
                            </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <!-- Chat content -->
                    <div class="chat-area d-flex flex-column" style="width: calc(100% - 300px); height: 80vh;">
                        <?php if ($selected_friend): ?>
                            <div class="chat-header">
                                <img src="<?php echo htmlspecialchars($selected_friend['pfp']); ?>" 
                                    alt="<?php echo htmlspecialchars($selected_friend['username']); ?>">
                                <h3><?php echo htmlspecialchars($selected_friend['username']); ?></h3>
                            </div>
                            
                            <div class="chat-messages" id="chat-messages">
                                <?php loadMessages($conn, $_SESSION['user_id'], $selected_friend['uid']); ?>
                            </div>
                            
                            <form method="POST" class="chat-input">
                                <input type="hidden" name="receiver_id" value="<?php echo $selected_friend['uid']; ?>">
                                <input type="text" name="message" placeholder="Type a message..." required>
                                <button type="submit"><i class="fa fa-paper-plane"></i></button>
                            </form>
                        <?php else: ?>
                            <div class="no-chat-selected">
                                <i class="fa-regular fa-message" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                                <h3>Select a friend to start chatting</h3>
                                <p>Choose someone from your friends list to begin messaging</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        </div>
    </main>
</body>
<script>
    const chatMessages = document.getElementById('chat-messages');
    function scrollToBottom() {
        if (chatMessages) {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
    }

    <?php if ($selected_friend): ?>
        function refreshMessages() {
            fetch("./api/load_messages.php?friend_id=<?php echo $selected_friend['uid']; ?>")
                .then(result => result.text())
                .then(html => {
                    chatMessages.innerHTML = html;
                    scrollToBottom();
            });
      }

      scrollToBottom();

      // Refresh every 5 seconds
    setInterval(refreshMessages, 7000);
    // <?php endif; ?>
</script>
</html>