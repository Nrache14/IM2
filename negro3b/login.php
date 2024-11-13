<?php
session_start();
require_once('connection.php');

$newconnection = new Connection();
$pdo = $newconnection->openConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_submit'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $users = $pdo->prepare("SELECT * FROM users WHERE username = :username");
    $users->execute(['username' => $username]);
    $result = $users->fetch(PDO::FETCH_ASSOC);  // Fetch as associative array

    if ($result) {
        if (password_verify($password, $result['password'])) {
            $_SESSION['name'] = $result['first_name']; 
            header('Location: home.php'); 
            exit();
        } else {
            $login_error = "Invalid username or password!";
        }
    } else {
        $login_error = "Invalid username or password!";
    }
}

if (isset($_SESSION['name'])) {
    header('Location: home.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="d-flex justify-content-center mt-5">
        <div class="col-md-6 form-container">
            <h3>Login</h3>
            <!-- Show login error message if it exists -->
            <?php if (isset($login_error)) { echo "<p class='text-danger'>$login_error</p>"; } ?>
            <form action="login.php" method="post">
                <div class="mb-3">
                    <label for="username" class="form-label">Username:</label>
                    <input type="text" name="username" id="username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password:</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                </div>
                <button type="submit" name="login_submit" class="custom-btn">Login</button>
            </form>
            <p class="mt-3">Don't have an account? <a href="register.php" class="custom-link">Register here</a></p>
        </div>
    </div>

    <?php include 'style.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>