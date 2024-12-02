<?php
session_start();
require_once('connection.php');

// Ensure the user is logged in and is a customer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header('Location: login.php');
    exit();
}

$connection = new Connection();
$pdo = $connection->openConnection();

// Fetch products
$query = "SELECT * FROM product_table";
$stmt = $pdo->prepare($query);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_OBJ);

// Initialize cart if not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle Add to Cart
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];

    if (isset($_POST['addToCart'])) {
        $productId = $_POST['product_id'];
        $quantity = $_POST['quantity'];

        $product = $pdo->prepare("SELECT * FROM product_table WHERE id = ?");
        $product->execute([$productId]);
        $productData = $product->fetch(PDO::FETCH_OBJ);

        if ($productData && $quantity > 0 && $quantity <= $productData->Quantity) {
            // Check if product is already in the cart
            $checkCart = $pdo->prepare("SELECT * FROM cart WHERE user_id = :user_id AND product_id = :product_id");
            $checkCart->execute([':user_id' => $userId, ':product_id' => $productId]);
            $existingCartItem = $checkCart->fetch(PDO::FETCH_OBJ);

            if ($existingCartItem) {
                // Update quantity in both session and database
                $updateCart = $pdo->prepare("UPDATE cart SET quantity = quantity + :quantity WHERE user_id = :user_id AND product_id = :product_id");
                $updateCart->execute([':quantity' => $quantity, ':user_id' => $userId, ':product_id' => $productId]);
                $_SESSION['cart'][$productId]['quantity'] += $quantity;
            } else {
                // Add new item to cart
                $addToCart = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (:user_id, :product_id, :quantity)");
                $addToCart->execute([':user_id' => $userId, ':product_id' => $productId, ':quantity' => $quantity]);
                $_SESSION['cart'][$productId] = [
                    'name' => $productData->Product_Name,
                    'category' => $productData->Category,
                    'quantity' => $quantity,
                ];
            }
            $successMessage = "Product added to cart!";
        } else {
            $errorMessage = "Invalid quantity or insufficient stock!";
        }
    }

    // Handle Checkout
    if (isset($_POST['checkout'])) {
        $cart = $_SESSION['cart'];
        if (!empty($cart)) {
            try {
                // Start a transaction to ensure all operations complete successfully
                $pdo->beginTransaction();

                foreach ($cart as $productId => $cartItem) {
                    $quantity = $cartItem['quantity'];

                    // Insert into orders table
                    $stmt = $pdo->prepare("INSERT INTO orders (user_id, product_id, quantity) VALUES (:user_id, :product_id, :quantity)");
                    $stmt->execute([':user_id' => $userId, ':product_id' => $productId, ':quantity' => $quantity]);

                    // Update stock in product_table
                    $updateStock = $pdo->prepare("UPDATE product_table SET Quantity = Quantity - :quantity WHERE id = :product_id");
                    $updateStock->execute([':quantity' => $quantity, ':product_id' => $productId]);

                    // Delete the cart item from the database after checkout
                    $deleteCartItem = $pdo->prepare("DELETE FROM cart WHERE user_id = :user_id AND product_id = :product_id");
                    $deleteCartItem->execute([':user_id' => $userId, ':product_id' => $productId]);
                }

                // Commit the transaction
                $pdo->commit();

                // Clear cart from the session after checkout
                $_SESSION['cart'] = [];
                $successMessage = "Checkout completed successfully! Your cart has been cleared.";
            } catch (PDOException $e) {
                // Rollback in case of error
                $pdo->rollBack();
                $errorMessage = "Error during checkout: " . $e->getMessage();
            }
        } else {
            $errorMessage = "Your cart is empty!";
        }
    }

    // Handle Remove from Cart
    if (isset($_POST['removeFromCart'])) {
        $productId = $_POST['product_id'];
        unset($_SESSION['cart'][$productId]);

        $removeCart = $pdo->prepare("DELETE FROM cart WHERE user_id = :user_id AND product_id = :product_id");
        $removeCart->execute([':user_id' => $userId, ':product_id' => $productId]);

        $successMessage = "Product removed from cart!";
    }
}

// Get cart items
$cartItems = $_SESSION['cart'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products & Cart</title>
    <!-- Link to your custom styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-4">
        <a href="logout.php" class="btn btn-danger logout-btn">Logout</a>

        <?php if (isset($successMessage)): ?>
            <div class="alert alert-success"><?= $successMessage; ?></div>
        <?php endif; ?>
        <?php if (isset($errorMessage)): ?>
            <div class="alert alert-danger"><?= $errorMessage; ?></div>
        <?php endif; ?>

        <!-- Products Section -->
        <h3>Available Products</h3>
        <div class="row gy-4">
            <?php foreach ($products as $product): ?>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($product->Product_Name); ?></h5>
                            <p>Category: <?= htmlspecialchars($product->Category); ?></p>
                            <p>Stock: <?= htmlspecialchars($product->Quantity); ?></p>
                            <form method="POST">
                                <input type="number" name="quantity" class="form-control mb-2" min="1" max="<?= $product->Quantity; ?>" required>
                                <input type="hidden" name="product_id" value="<?= $product->id; ?>">
                                <button type="submit" name="addToCart" class="btn btn-success w-100">Add to Cart</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Cart Section -->
        <h3 class="mt-5">My Cart</h3>
        <?php if (!empty($cartItems)): ?>
            <form method="POST">
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
                        <?php foreach ($cartItems as $productId => $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['name']); ?></td>
                                <td><?= htmlspecialchars($item['category']); ?></td>
                                <td><?= htmlspecialchars($item['quantity']); ?></td>
                                <td>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="product_id" value="<?= $productId; ?>">
                                        <button type="submit" class="btn btn-danger" name="removeFromCart">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="submit" name="checkout" class="btn btn-primary checkout-btn">Checkout</button>
            </form>
        <?php else: ?>
            <p>Your cart is empty.</p>
        <?php endif; ?>
    </div>

    <?php include 'style.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>