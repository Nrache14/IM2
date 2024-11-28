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

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Handle removing an item from the cart
    if (isset($_POST['removeFromCart'])) {
        $product_id = $_POST['product_id'];
        $user_id = $_SESSION['user_id'];

        $result = $newconnection->removeFromCart($user_id, $product_id);
        if ($result === true) {
            unset($_SESSION['cart'][$product_id]);
            $_SESSION['message'] = "Product removed from cart successfully";
        } else {
            $_SESSION['message'] = $result;
        }

        header('Location: cart.php');
        exit();
    }

    // Handle checkout
    if (isset($_POST['checkout'])) {
        var_dump($_POST);
        $user_id = $_SESSION['user_id'];
        $cart = $_SESSION['cart'];

        if (!empty($cart)) {
            $result = $newconnection->processCheckout($user_id, $cart);

            if ($result === true) {
                $_SESSION['cart'] = [];
                $_SESSION['message'] = "Checkout completed successfully!";
            } else {
                $_SESSION['message'] = $result;
            }
            header("Location: cart.php");
            exit();
        } else {
            $_SESSION['message'] = "Your cart is empty. Please add items before checkout!";
            header("Location: cart.php");
            exit();
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
    <div class="text-end">
        <a href="products.php" class="btn btn-primary mb-3">Back to Products</a>
    </div>

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
