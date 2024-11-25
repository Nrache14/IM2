<?php
session_start();
require_once('connection.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header('Location: login.php');
    exit();
}

// Ensure the cart exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [
        'product_id' => [
            'prod_name' => 'Product Name',
            'cat' => 'Category',
            'quantity' => 2,
        ],
    ];
}

// Handle removing an item from the cart
if (isset($_POST['removeFromCart'])) {
    $product_id = $_POST['product_id'];
    unset($_SESSION['cart'][$product_id]);
}

// Handle checkout
if (isset($_POST['checkout'])) {
    $connection = new Connection();
    $pdo = $connection->openConnection();
    $user_id = $_SESSION['user_id'];

    // Check which items are selected for checkout
    $selectedItems = isset($_POST['selected_items']) ? $_POST['selected_items'] : [];

    // Process selected items
    foreach ($selectedItems as $product_id) {
        if (isset($_SESSION['cart'][$product_id])) {
            $item = $_SESSION['cart'][$product_id];
            $quantity = $item['quantity'];

            // Fetch product to check stock
            $stmt = $pdo->prepare("SELECT Quantity FROM product_table WHERE id = ?");  // Assuming 'id' is the correct column name
            $stmt->execute([$product_id]);
            $product = $stmt->fetch(PDO::FETCH_OBJ);


            if ($product && $product->Quantity >= $quantity) {
                // Update product stock
                $newStock = $product->Quantity - $quantity;
                $updateStmt = $pdo->prepare("UPDATE product_table SET Quantity = ? WHERE id = ?");
                $updateStmt->execute([$newStock, $product_id]);

                // Insert into orders table (assume an orders table exists)
                // $orderStmt = $pdo->prepare("INSERT INTO orders (user_id, id, quantity, order_date) VALUES (?, ?, ?, NOW())");
                // $orderStmt->execute([$user_id, $product_id, $quantity]);

                // Remove item from cart after successful checkout
                unset($_SESSION['cart'][$product_id]);
            } else {
                echo "<script>alert('Insufficient stock for product ID: $product_id');</script>";
            }
        }
    }

    // If all items were removed from the cart, empty the cart
    if (empty($_SESSION['cart'])) {
        echo "<script>alert('All selected items have been checked out!'); window.location = 'products.php';</script>";
    } else {
        echo "<script>alert('Checkout successful! Remaining items in the cart.'); window.location = 'cart.php';</script>";
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
                        <th><input type="checkbox" id="selectAll" onclick="toggleSelectAll(this)"></th>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Quantity</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($cartItems as $product_id => $item): ?>
                        <tr>
                            <td><input type="checkbox" name="selected_items[]" value="<?= $product_id; ?>"></td>
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
                <button type="submit" class="btn btn-success" name="checkout">Proceed to Checkout</button>
            </div>
        </form>
    <?php else: ?>
        <p class="text-center">Your cart is empty. <a href="products.php">Browse products</a></p>
    <?php endif; ?>
</div>

<?php include 'style.php'; ?>


<script>
    // Function to toggle the select all checkbox
    function toggleSelectAll(source) {
        const checkboxes = document.querySelectorAll('input[name="selected_items[]"]');
        checkboxes.forEach(checkbox => {
            checkbox.checked = source.checked;
        });
    }
</script>
</body>
</html>
