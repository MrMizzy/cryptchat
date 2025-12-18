<?php
    session_start();
    include './api/connection.php';
    if (!isset($_SESSION['user_id'])){
        http_response_code(403); // Forbidden
        header("Location: login.html");
        exit();
    } else {
        $username = $_SESSION['username'];
        $pfp_path = $_SESSION['pfp_path'];
    }
    // Get profile bio
    $bio_stmt = $conn->prepare("SELECT bio FROM users WHERE uid = ?");
    $bio_stmt->bind_param("i", $_SESSION['user_id']);
    $bio_stmt->execute();
    $bio_result = $bio_stmt->get_result();
    if ($bio_result->num_rows > 0) {
        $bio_row = $bio_result->fetch_assoc();
        $bio = $bio_row['bio'];
    } else {
        $bio = "This user has no bio.";
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
            <section class="flex-grow-1">
                <div class="container p-5">
                    <!-- Profile Details -->
                    <h1>Your Profile</h1>
                    <p>Username: <?php echo $username ?></p>
                    <!-- Profile Picture -->
                    <div class="container-fluid mb-3 d-flex align-items-center gap-3 bg-dark p-3 rounded">
                        <p><img src="<?php echo $pfp_path ?>" alt="Profile Picture" style="width: 100px" class="rounded-pill"></p>
                        <form action="./api/upload_pfp.php" method="POST" enctype="multipart/form-data">
                            <input type="file" name="pfp" accept="image/*" required>
                            <button class="btn btn-primary mt-2" type="submit">Upload New Profile Picture</button>
                            <p class="small text-warning">Max file size: 2MB</p>
                        </form>
                        <button class="btn btn-secondary mt-2" onclick="resetPFP()">Reset to Default</button>
                    </div>
                    <p>Email: <?php echo $_SESSION['email'] ?></p>
                    <p>Bio:</p>
                    <textarea id="bio" class="form-control" rows="5"><?php echo htmlspecialchars($bio); ?></textarea>
                    <button class="btn btn-primary mt-2" onclick="saveBio()">Save Changes</button>
                    <br><br>
                    <p>Number of Friends: <?php
                        $friend_stmt = $conn->prepare("SELECT COUNT(*) AS friend_count FROM friends WHERE user1 = ? OR user2 = ?");
                        $friend_stmt->bind_param("ii", $_SESSION['user_id'], $_SESSION['user_id']);
                        $friend_stmt->execute();
                        $friend_result = $friend_stmt->get_result();
                        $friend_row = $friend_result->fetch_assoc();
                        echo $friend_row['friend_count'];
                    ?></p>
                </div>
                <div class="container-fluid justify-content-center d-flex mb-5">
                    <button class="btn btn-danger mx-8" onclick="deleteAccount()">Delete Account</button>
                </div>
            </section>
        </div>
    </main>
</body>
<script>
    function resetPFP() {
        fetch('./api/reset_pfp.php', {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.pfp_path) {
                alert('Profile picture reset to default.');
                location.reload();
            } else if (data.error) {
                alert('Error: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    function saveBio() {
        const bio = document.querySelector('textarea').value;
        fetch('./api/update_bio.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                'bio': bio
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Bio updated successfully.');
            } else if (data.error) {
                alert('Error: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    function deleteAccount() {
        if (confirm('Are you sure you want to delete your account? This action cannot be undone.')) {
            fetch('./api/delete_account.php', {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Account deleted successfully.');
                    window.location.href = 'login.html';
                } else if (data.error) {
                    alert('Error: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
    }
</script>
</html>