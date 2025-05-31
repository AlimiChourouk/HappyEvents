<?php
session_start();
require('db.php');

// If user is already logged in, redirect to index
if (isset($_SESSION['userId'])) {
    header("Location: index.php");
    exit();
}

// Get the referring URL if it exists
$redirect_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';
// Store the URL in session for use after login
$_SESSION['redirect_url'] = $redirect_url;

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['mail']);
    $motPasse = trim($_POST['motPasse']);

    if (empty($email) || empty($motPasse)) {
        $message = "All fields are required.";
    } else {
        $sql = "SELECT idUser, motPasse FROM user WHERE mailUser = :email";
        $stm = $pdo->prepare($sql);
        $stm->execute([':email' => $email]);
        $user = $stm->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Direct password comparison (not hashed)
            if ($motPasse === $user['motPasse']) {
                $_SESSION['userId'] = $user['idUser'];
                // Redirect to the original page or index.php by default
                $destination = isset($_SESSION['redirect_url']) ? $_SESSION['redirect_url'] : 'index.php';
                unset($_SESSION['redirect_url']); // Clean up session
                header("Location: $destination");
                exit();
            } else {
                $message = "Incorrect email or password.";
            }
        } else {
            $message = "Incorrect email or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <link rel="stylesheet" href="login.css"?v=<?=time();?>>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <title>Login - HappyEvents</title>
</head>
<body>
    <div class="container">
        <h1>Login</h1>
        <?php if ($message) : ?>
            <div class="message">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="mail">Email:</label>
                <input type="email" id="mail" name="mail" value="<?= isset($_POST['mail']) ? htmlspecialchars($_POST['mail']) : '' ?>" required>
            </div>
            <div class="form-group">
                <label for="motPasse">Password:</label>
                <input type="password" id="motPasse" name="motPasse" required>
            </div>
            <button type="submit">Log In</button>
        </form>
        <div class="register-link">
            <p>No account? <a href="register.php">Sign Up</a></p>
        </div>
    </div>
</body>
</html>