<?php
session_start();
require_once('connection.php');

$newconnection = new Connection();
$pdo = $newconnection->openConnection();

if (isset($_POST['login_submit'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        $query = "SELECT * FROM users WHERE username = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
        
            // Role-based redirection
            if ($user['role'] === 'admin') {
                header("Location: home.php"); // Redirect admin
            } else if ($user['role'] === 'customer') {
                header("Location: products.php"); // Redirect customer
            } else {
                $login_error = "User role is not recognized.";
            }
            exit();
        } else {
            $login_error = "Invalid username or password.";
        }
        
    } catch (PDOException $e) {
        $login_error = "Error: " . $e->getMessage();
    }
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