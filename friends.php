<?php
    include './api/connection.php';
    include_once './api/friend_management.php';
    if (!isset($_SESSION['user_id'])){
        http_response_code(403); // Forbidden
        header("Location: login.html");
        exit();
    } else {
        $username = $_SESSION['username'];
        $pfp_path = $_SESSION['pfp_path'];
    }

    $friends = getFriends($conn, $_SESSION['user_id']);
    $pendingRequests = getPendingFriendRequests($conn, $_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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
    <main>
        <div class="d-flex">
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
            <!-- Main Content Area -->
            <section class="d-flex flex-column flex-grow-1">
                <section class="flex-grow-1 p-4">
                    <h1>Your Friends</h1>
                    <div class="list-group">
                        <?php foreach ($friends as $friend): ?>
                            <a href="#" class="list-group-item d-flex align-items-center">
                                <img src="<?php echo htmlspecialchars($friend['pfp']); ?>" alt="Profile Picture" style="width: 40px" class="rounded-pill me-3">
                                <div>
                                    <h5 class="mb-1"><?php echo htmlspecialchars($friend['username']); ?></h5>
                                    <small>Key: <?php echo htmlspecialchars($friend['n']); ?>, <?php echo htmlspecialchars($friend['e']); ?></small>
                                </div>
                                <button class="btn btn-danger btn-sm ms-auto" onclick="removeFriend(<?php echo htmlspecialchars($friend['uid']); ?>)">Remove</button>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </section>
                <section class="flex-grow-1 p-4">
                    <h1>Pending Friend Requests</h1>
                    <div class="list-group">
                        <?php foreach ($pendingRequests as $row) : ?>
                            <a href="#" class="list-group-item d-flex align-items-center">
                                <img src="<?php echo htmlspecialchars($row['pfp']); ?>" alt="Profile Picture" style="width: 40px" class="rounded-pill me-3">
                                <div>
                                    <h5 class="mb-1"><?php echo htmlspecialchars($row['username']); ?></h5>
                                    <button class="btn btn-success btn-sm me-2" onclick="acceptRequest(<?php echo htmlspecialchars($row['uid']); ?>)">Accept</button>
                                    <button class="btn btn-danger btn-sm" onclick="declineRequest(<?php echo htmlspecialchars($row['uid']); ?>)">Decline</button>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </section>
                <section class="flex-grow-1 p-4">
                    <h1>Send Friend Request</h1>
                    <form id="findFriendForm" method="POST">
                        <div class="mb-3">
                            <label for="friend_username" class="form-label">Find a Friend!</label>
                            <input type="text" class="form-control" id="friend_username" name="friend_username" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Find</button>
                    </form>
                    <div id="searchResults" class="mt-3"></div>
                </section>
            </section>
        </div>
    </main>
</body>
<script>
    function removeFriend(friendId) {
        const formData = new FormData();
        formData.append('friend_id', friendId);
        fetch('./api/remove_friend.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            alert(data);
            location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    function acceptRequest(senderId) {
        const formData = new FormData();
        formData.append('sender_id', senderId);
        fetch('./api/accept_request.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            alert(data);
            location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    function declineRequest(senderId) {
        const formData = new FormData();
        formData.append('sender_id', senderId);
        fetch('./api/decline_request.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            alert(data);
            location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    document.getElementById('findFriendForm').addEventListener('submit', function(event) {
        event.preventDefault();
        const formData = new FormData(this);
        fetch('./api/find_friend.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            const resultsDiv = document.getElementById('searchResults');
            resultsDiv.innerHTML = '';
            if (data.error) {
                resultsDiv.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
            } else {
                data.forEach(user => {
                    const userDiv = document.createElement('div');
                    userDiv.classList.add('list-group-item', 'd-flex', 'align-items-center');
                    userDiv.innerHTML = `
                        <img src="${user.pfp}" alt="Profile Picture" style="width: 40px" class="rounded-pill me-3">
                        <div>
                            <h5 class="mb-1">${user.username}</h5>
                            <form class="request-form" method="POST">
                                <input type="hidden" name="receiver_id" value="${user.uid}">
                                <button type="submit" class="btn btn-primary btn-sm">Send Friend Request</button>
                            </form>
                        </div>
                    `;
                    resultsDiv.appendChild(userDiv);
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    });

    document.getElementById('searchResults').addEventListener('submit', function(event) {
        if (event.target && event.target.classList.contains('request-form')) {
            event.preventDefault();
            const formData = new FormData(event.target);
            fetch('./api/send_request.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.success);
                } else if (data.error) {
                    alert(data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
    });
</script>
</html>