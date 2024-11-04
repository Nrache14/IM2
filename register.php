<?php
session_start();
require_once('connection.php');

$newconnection = new Connection();
$pdo = $newconnection->openConnection();

// Handle user registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_submit'])) {
    // Collect user inputs
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $address = trim($_POST['address']);
    $birthdate = trim($_POST['birthdate']);
    $gender = trim($_POST['gender']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $role = 'user';  // Default role for new users
    $date_created = date('Y-m-d H:i:s'); // Current date and time

    // Hash the password before storing
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check if the username already exists
    $checkUser = $pdo->prepare("SELECT * FROM users WHERE username = :username");
    $checkUser->execute(['username' => $username]);
    
    if ($checkUser->rowCount() > 0) {
        $register_error = "Username already exists. Please choose another one.";
    } else {
        // Insert the new user into the database
        $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, address, birthdate, gender, username, password, role, date_created) 
                               VALUES (:first_name, :last_name, :address, :birthdate, :gender, :username, :password, :role, :date_created)");

        $success = $stmt->execute([
            'first_name' => $first_name,
            'last_name' => $last_name,
            'address' => $address,
            'birthdate' => $birthdate,
            'gender' => $gender,
            'username' => $username,
            'password' => $hashed_password,
            'role' => $role,
            'date_created' => $date_created
        ]);

        if ($success) {
            // Redirect to login page after successful registration
            $_SESSION['registration_success'] = "Registration successful! You can now log in.";
            header('Location: login.php');
            exit();
        } else {
            $register_error = "An error occurred during registration. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
</head>
<body>
    <h2>Register</h2>
    <?php if (isset($register_error)) { echo "<p style='color:red;'>$register_error</p>"; } ?>
    <form action="register.php" method="post">
        <label for="first_name">First Name:</label>
        <input type="text" name="first_name" id="first_name" required><br><br>

        <label for="last_name">Last Name:</label>
        <input type="text" name="last_name" id="last_name" required><br><br>

        <label for="address">Address:</label>
        <input type="text" name="address" id="address" required><br><br>

        <label for="birthdate">Birthdate:</label>
        <input type="date" name="birthdate" id="birthdate" required><br><br>

        <label for="gender">Gender:</label>
        <select name="gender" id="gender" required>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
            <option value="Other">Non-binary</option>
        </select><br><br>

        <label for="username">Username:</label>
        <input type="text" name="username" id="username" required><br><br>

        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required><br><br>

        <button type="submit" name="register_submit">Register</button>
    </form>
    <p>Already have an account? <a href="login.php">Login here</a></p>
</body>
</html>
