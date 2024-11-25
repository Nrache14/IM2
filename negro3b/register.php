<?php

session_start();
require_once('connection.php');

if (isset($_POST['register_submit'])) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $address = $_POST['address'];
    $birthdate = $_POST['birthdate'];
    $gender = $_POST['gender'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role = 'customer'; // Default role is customer

    try {
        $connection = new Connection();
        $pdo = $connection->openConnection();
        $query = "INSERT INTO users (first_name, last_name, address, birthdate, gender, username, password, role) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$first_name, $last_name, $address, $birthdate, $gender, $username, $password, $role]);

        $_SESSION['message'] = "Registration successful! You can now login.";
        header("Location: login.php");
        exit();
    } catch (PDOException $e) {
        $register_error = "Error: " . $e->getMessage();
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
    <div class="d-flex justify-content-center mt-5">
        <div class="col-md-6 form-container">
            <h3>Register</h3>
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
                <button type="submit" name="register_submit" class="custom-btn">Register</button>
            </form>
            <p class="mt-3">Already have an account? <a href="login.php" class="custom-link">Login here</a></p>
        </div>
    </div>

    <?php include 'style.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>