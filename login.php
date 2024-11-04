<?php
session_start();
require_once('connection.php');

$newconnection = new Connection();
$pdo = $newconnection->openConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_submit'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Prepare the query to validate the user
    $users = $pdo->prepare("SELECT * FROM users WHERE username = :username");
    $users->execute(['username' => $username]);
    $result = $users->fetch(PDO::FETCH_ASSOC);  // Fetch as associative array

    // Check if the user exists and verify password
    if ($result) {
        // Debugging output to check retrieved data
        // var_dump($result['password']); // Uncomment for debugging

        if (password_verify($password, $result['password'])) {
            // Password is correct, set session and redirect
            $_SESSION['name'] = $result['first_name']; // Set session variable for user name
            header('Location: home.php');  // Redirect to home.php after login
            exit();
        } else {
            $login_error = "Invalid username or password!";
        }
    } else {
        $login_error = "Invalid username or password!";
    }
}

// Check if the user is already logged in
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
</head>
<body>
    <h2>Login</h2>
    <!-- Show login error message if it exists -->
    <?php if (isset($login_error)) { echo "<p style='color:red;'>$login_error</p>"; } ?>
    <form action="login.php" method="post">
        <label for="username">Username:</label>
        <input type="text" name="username" id="username" required><br><br>
        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required><br><br>
        <button type="submit" name="login_submit">Login</button>
    </form>
    <p>Don't have an account? <a href="register.php">Register here</a></p>
</body>
</html>
