<?php
    session_start();
    if (!isset($_SESSION['user_id'])){
        http_response_code(403); // Forbidden
        header("Location: login.html");
        exit();
    } else {
        $username = $_SESSION['username'];
        $pfp_path = $_SESSION['pfp_path'];
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
        .no-spinners::-webkit-outer-spin-button,
        .no-spinners::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0; /* Important to remove the default margin added by some browsers */
        }

        .no-spinners[type=number] {
            -moz-appearance: textfield; /* Firefox specific */
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
            <section class="d-flex flex-column w-100">
                <section class="container row w-100 m-0 p-0 vh-50">
                    <!-- Decryption Input -->
                    <div class="container p-5 border w-50">
                        <h1>Decrypt Your Messages</h1>
                        <form id="decryptForm">
                            <div class="form-floating mb-3">
                                <textarea class="form-control" id="message" placeholder="Enter your message here..." required></textarea>
                                <label for="message">Message</label>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Decrypt</button>
                        </form>
                    </div>
                    <!-- Decryption Output -->
                    <div class="container p-5 border w-50">
                        <h1>Decrypted Message</h1>
                        <div class="form-floating mb-3">
                            <textarea class="form-control" id="decryptedMessage" placeholder="Your decrypted message will appear here..." style="min-height: 130px" readonly></textarea>
                            <label for="decryptedMessage">Decrypted Message</label>
                        </div>
                    </div>
                </section>
                <!-- Decryption Key Selection -->
               <div class="container p-5 border">
                    <h2>Input Your Public Key</h2>
                    <!-- Decryption Details -->
                    <div class="d-flex justify-content-around">
                        <div class="container"> 
                            <div class="w-50 form-floating">
                                <input type="number" class="form-control text-center no-spinners" id="nInput" placeholder="Enter N" min="1">
                                <label for="nInput">N</label>
                            </div>
                        </div>
                        <div class="container">
                            <div class="w-50 form-floating">
                                <input type="number" class="form-control text-center no-spinners" id="eInput" placeholder="Enter E" min="1">
                                <label for="eInput">E</label>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>
</body>
<script>
    const nInput = document.getElementById("nInput");
    const eInput = document.getElementById("eInput");
    const decryptForm = document.getElementById("decryptForm");
    const decryptedMessageTextarea = document.getElementById("decryptedMessage");
    decryptForm.addEventListener('submit', function(event){
        event.preventDefault();
        const message = document.getElementById("message").value;
        const n = parseInt(nInput.value);
        const e = parseInt(eInput.value);

        fetch('./api/decrypt_public.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                message: message,
                n: n,
                e: e
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.decrypted_message) {
                decryptedMessageTextarea.value = data.decrypted_message;
            } else if (data.error) {
                decryptedMessageTextarea.value = "Error: " + data.error;
            }
        })
        .catch(error => {
            decryptedMessageTextarea.value = "Error: " + error.message;
        });
    });
</script>
</html>