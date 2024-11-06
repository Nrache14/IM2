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
    <!-- Button to trigger the login modal -->
    <div class="d-flex justify-content-center mt-5">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#loginModal">Login</button>
    </div>

    <!-- Login Modal -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="loginModalLabel">Login</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
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
                        <button type="submit" name="login_submit" class="btn btn-primary">Login</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <p>Don't have an account? <a href="register.php">Register here</a></p>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
