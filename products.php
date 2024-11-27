<?php
session_start();
require_once('connection.php');

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
    if (isset($_POST['addToCart'])) {
        $productId = $_POST['product_id'];
        $quantity = $_POST['quantity'];
        $userId = $_SESSION['user_id'];

        $product = $pdo->prepare("SELECT * FROM product_table WHERE id = ?");
        $product->execute([$productId]);
        $productData = $product->fetch(PDO::FETCH_OBJ);

        if ($productData && $quantity > 0 && $quantity <= $productData->Quantity) {
            // Check if the product is already in the cart (both session and database)
            $checkCart = $pdo->prepare("SELECT * FROM cart WHERE user_id = :user_id AND product_id = :product_id");
            $checkCart->execute([':user_id' => $userId, ':product_id' => $productId]);
            $existingCartItem = $checkCart->fetch(PDO::FETCH_OBJ);

            if ($existingCartItem) {
                // Update quantity in both session and database
                $updateCart = $pdo->prepare("UPDATE cart SET quantity = quantity + :quantity WHERE user_id = :user_id AND product_id = :product_id");
                $updateCart->execute([':quantity' => $quantity, ':user_id' => $userId, ':product_id' => $productId]);

                // Update session cart
                $_SESSION['cart'][$productId]['quantity'] += $quantity;
            } else {
                // Add new item to cart in database
                $addToCart = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (:user_id, :product_id, :quantity)");
                $addToCart->execute([':user_id' => $userId, ':product_id' => $productId, ':quantity' => $quantity]);

                // Add item to session cart
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
// }
    // Handle Checkout
    if (isset($_POST['checkout'])) {
        $userId = $_SESSION['user_id'];
        $cart = $_SESSION['cart'];

        if (!empty($cart)) {
            try {
                foreach ($cart as $productId => $cartItem) {
                    $quantity = $cartItem['quantity'];

                    // Insert into orders table
                    $stmt = $pdo->prepare("INSERT INTO orders (user_id, product_id, quantity, order_date) VALUES (:user_id, :product_id, :quantity, NOW())");
                    $stmt->execute([
                        ':user_id' => $userId,
                        ':product_id' => $productId,
                        ':quantity' => $quantity,
                    ]);

                    // Update stock in product_table
                    $updateStock = $pdo->prepare("UPDATE product_table SET Quantity = Quantity - :quantity WHERE id = :product_id");
                    $updateStock->execute([
                        ':quantity' => $quantity,
                        ':product_id' => $productId,
                    ]);
                }

                // Clear cart after checkout
                $_SESSION['cart'] = [];
                $successMessage = "Checkout completed successfully!";
            } catch (PDOException $e) {
                $errorMessage = "Error during checkout: " . $e->getMessage();
            }
        } else {
            $errorMessage = "Your cart is empty!";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Welcome, <?= htmlspecialchars($_SESSION['role']); ?></a>
        <div class="ms-auto">
            <a href="cart.php" class="btn btn-warning me-2">View Cart</a>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h2 class="text-center">Available Products</h2>

    <?php if (isset($successMessage)): ?>
        <div class="alert alert-success"><?= $successMessage; ?></div>
    <?php endif; ?>
    <?php if (isset($errorMessage)): ?>
        <div class="alert alert-danger"><?= $errorMessage; ?></div>
    <?php endif; ?>
    
    <div class="row">
        <?php foreach ($products as $product): ?>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h2 class="card-title"><?= htmlspecialchars($product->Product_Name); ?></h2>
                        <p>Category: <?= htmlspecialchars($product->Category); ?></p>
                        <p>Stock: 
                            <span style="color: <?= $product->Quantity > 50 ? 'green' : ($product->Quantity > 10 ? 'orange' : 'red'); ?>">
                                <?= htmlspecialchars($product->Quantity); ?>
                            </span>
                        </p>
                        <form method="POST">
                            <div class="input-group mb-2">
                                <input type="number" name="quantity" class="form-control" min="1" max="<?= $product->Quantity; ?>" required>
                                <input type="hidden" name="product_id" value="<?= $product->id; ?>">
                            </div>
                            <button type="submit" name="addToCart" class="btn btn-success w-100">Add to Cart</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include 'style.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
