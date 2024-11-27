<?php
session_start();
require_once('connection.php');

// Ensure the user is logged in and is a customer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header('Location: login.php');
    exit();
}

$newconnection = new Connection();
$pdo = $newconnection->openConnection(); 

// Ensure the cart exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle removing an item from the cart
if (isset($_POST['removeFromCart'])) {
    $product_id = $_POST['product_id'];
    unset($_SESSION['cart'][$product_id]);

    // Remove the product from the database cart table
    $removeFromDb = $pdo->prepare("DELETE FROM cart WHERE user_id = :user_id AND product_id = :product_id");
    $removeFromDb->execute([':user_id' => $_SESSION['user_id'], ':product_id' => $product_id]);

    $successMessage = "Product removed from cart!";
    echo "<script>alert('$successMessage'); window.location = 'cart.php';</script>";
}

// Handle checkout
if (isset($_POST['checkout'])) {
    $user_id = $_SESSION['user_id'];
    $cart = $_SESSION['cart'];

    if (!empty($cart)) {
        try {
            // Loop through all items in the cart and insert them into the orders table
            foreach ($cart as $product_id => $cartItem) {
                $quantity = $cartItem['quantity'];

                // Insert into orders table
                $stmt = $pdo->prepare("INSERT INTO orders (user_id, product_id, quantity) VALUES (:user_id, :product_id, :quantity)");
                $stmt->execute([':user_id' => $user_id, ':product_id' => $product_id, ':quantity' => $quantity]);

                // Update stock in product_table
                $updateStock = $pdo->prepare("UPDATE product_table SET Quantity = Quantity - :quantity WHERE id = :product_id");
                $updateStock->execute([':quantity' => $quantity, ':product_id' => $product_id]);

                // Remove the product from the session cart
                unset($_SESSION['cart'][$product_id]);

                // Remove the product from the database cart table
                $removeFromDb = $pdo->prepare("DELETE FROM cart WHERE user_id = :user_id AND product_id = :product_id");
                $removeFromDb->execute([':user_id' => $user_id, ':product_id' => $product_id]);
            }

            // Clear the cart after successful checkout
            $_SESSION['cart'] = [];

            $successMessage = "Checkout completed successfully!";
            echo "<script>alert('$successMessage'); window.location = 'home.php';</script>"; // Redirect to home.php where orders will be displayed
        } catch (PDOException $e) {
            $errorMessage = "Error during checkout: " . $e->getMessage();
            echo "<script>alert('$errorMessage'); window.location = 'cart.php';</script>";
        }
    } else {
        $errorMessage = "Your cart is empty. Please add items before checkout!";
        echo "<script>alert('$errorMessage'); window.location = 'cart.php';</script>";
    }
}

// Display cart items
$cartItems = $_SESSION['cart'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2 class="text-center">My Cart</h2>
    <a href="products.php" class="btn btn-primary mb-3">Back to Products</a>
    <?php if (!empty($cartItems)): ?>
        <form method="POST">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Quantity</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($cartItems as $product_id => $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['name']); ?></td>
                            <td><?= htmlspecialchars($item['category']); ?></td>
                            <td><?= htmlspecialchars($item['quantity']); ?></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="product_id" value="<?= $product_id; ?>">
                                    <button type="submit" class="btn btn-danger" name="removeFromCart">Remove</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="text-end">
                <button type="submit" class="btn btn-success" name="checkout">Checkout</button>
            </div>
        </form>
    <?php else: ?>
        <p class="text-center">Your cart is empty. <a href="products.php">Browse products</a></p>
    <?php endif; ?>
</div>

<?php include 'style.php'; ?>
</body>
</html>
