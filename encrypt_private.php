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
                    <!-- Encryption Input -->
                    <div class="container p-5 border w-50">
                        <h1>Encrypt Your Messages</h1>
                        <form id="encryptForm">
                            <div class="form-floating mb-3">
                                <textarea class="form-control" id="message" placeholder="Enter your message here..." required></textarea>
                                <label for="message">Message</label>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Encrypt</button>
                        </form>
                    </div>
                    <!-- Encryption Output -->
                    <div class="container p-5 border w-50">
                        <h1>Encrypted Message</h1>
                        <div class="form-floating mb-3">
                            <textarea class="form-control" id="encryptedMessage" placeholder="Your encrypted message will appear here..." style="min-height: 130px" readonly></textarea>
                            <label for="encryptedMessage">Encrypted Message</label>
                        </div>
                    </div>
                </section>
                <!-- Encryption Key Selection -->
               <div class="container p-5 border">
                    <h2>Select Affine Shift Key</h2>
                    <!-- Encryption Details -->
                    <div class="row">
                        <div class="container w-50 border">
                            <small class="text-muted">SLOPE / A</small>
                            <div class="input-group">
                                <button class="btn btn-light w-25 text-center" id="slope-minus"><i class="fa fa-minus" aria-hidden="true"></i></button>
                                <div class="w-50">
                                    <input type="number" class="form-control border-0 text-center no-spinners" id="slope" value="5" min="1" readonly>
                                </div>
                                <button class="btn btn-light w-25 text-center" id="slope-plus"><i class="fa fa-plus" aria-hidden="true"></i></button>
                            </div>
                        </div>
                        <div class="container w-50 border">
                            <small class="text-muted">INTERCEPT / B</small>
                            <div class="input-group">
                                <button class="btn btn-light w-25 text-center" id="intercept-minus"><i class="fa fa-minus" aria-hidden="true"></i></button>
                                <div class="w-50">
                                    <input type="number" class="form-control border-0 text-center no-spinners" id="intercept" value="5" min="1" readonly>
                                </div>
                                <button class="btn btn-light w-25 text-center" id="intercept-plus"><i class="fa fa-plus" aria-hidden="true"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>
</body>
<script>
    // Add functionality for increment and decrement buttons
    const slopeInput = document.getElementById("slope");
    const slopeDecrement = document.getElementById("slope-minus");
    const slopeIncrement = document.getElementById("slope-plus");

    slopeDecrement.addEventListener('click', function(event){
        let currentValue = parseInt(slopeInput.value);
        let newValue = currentValue - 1;
         while (newValue % 2 === 0 || newValue % 13 === 0 ) {
            newValue = newValue - 1
        }
        if (newValue <= 1) {
            newValue = 1;
        }
        slopeInput.value = newValue;
    });
    slopeIncrement.addEventListener('click', function(event){
        let currentValue = parseInt(slopeInput.value);
        let newValue = currentValue + 1;
        while (newValue % 2 === 0 || newValue % 13 === 0 ) {
            newValue = newValue + 1
        }
        slopeInput.value = newValue;
    });
    
    const interceptInput = document.getElementById("intercept");
    const interceptDecrement = document.getElementById("intercept-minus")
    const interceptIncrement = document.getElementById("intercept-plus")

    interceptDecrement.addEventListener('click', function(event){
        let currentValue = parseInt(interceptInput.value)
        let newValue = currentValue - 1;
        if (newValue <= 1) {
            newValue = 1;
        }
        interceptInput.value = newValue;
    });
    interceptIncrement.addEventListener('click', function(event){
        let currentValue = parseInt(interceptInput.value)
        let newValue = currentValue + 1;
        if (newValue >= 26) {
            newValue = 26;
        }
        interceptInput.value = newValue;
    });

    const encryptForm = document.getElementById("encryptForm");
    const encryptedMessageTextarea = document.getElementById("encryptedMessage");
    encryptForm.addEventListener('submit', function(event){
        event.preventDefault();
        const message = document.getElementById("message").value;
        const slope = parseInt(slopeInput.value);
        const intercept = parseInt(interceptInput.value);

        fetch('./api/encrypt_private.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                message: message,
                slope: slope,
                intercept: intercept
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.encrypted_message) {
                encryptedMessageTextarea.value = data.encrypted_message;
            } else if (data.error) {
                encryptedMessageTextarea.value = "Error: " + data.error;
            }
        })
        .catch(error => {
            encryptedMessageTextarea.value = "Error: " + error.message;
        });
    });
</script>
</html>