<?php
session_start();
require_once('connection.php');

$newconnection = new Connection();
$pdo = $newconnection->openConnection();

// Handle user registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_submit'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $address = trim($_POST['address']);
    $birthdate = trim($_POST['birthdate']);
    $gender = trim($_POST['gender']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $role = 'user';  // Default role for new users
    $date_created = date('Y-m-d H:i:s'); // Current date and time

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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Button to trigger the register modal -->
    <div class="d-flex justify-content-center mt-5">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#registerModal">Register</button>
    </div>

    <!-- Register Modal -->
    <div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="registerModalLabel">Register</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if (isset($register_error)) { echo "<p class='text-danger'>$register_error</p>"; } ?>
                    <form action="register.php" method="post">
                        <div class="mb-3">
                            <label for="first_name" class="form-label">First Name:</label>
                            <input type="text" name="first_name" id="first_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="last_name" class="form-label">Last Name:</label>
                            <input type="text" name="last_name" id="last_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Address:</label>
                            <input type="text" name="address" id="address" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="birthdate" class="form-label">Birthdate:</label>
                            <input type="date" name="birthdate" id="birthdate" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="gender" class="form-label">Gender:</label>
                            <select name="gender" id="gender" class="form-select" required>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Non-binary</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="username" class="form-label">Username:</label>
                            <input type="text" name="username" id="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password:</label>
                            <input type="password" name="password" id="password" class="form-control" required>
                        </div>
                        <button type="submit" name="register_submit" class="btn btn-primary">Register</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <p>Already have an account? <a href="login.php">Login here</a></p>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
