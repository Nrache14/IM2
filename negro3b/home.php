<?php
session_start();
require_once('connection.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$newconnection = new Connection();
$pdo = $newconnection->openConnection();

// Fetch categories dynamically
$categoryQuery = "SELECT * FROM categories";
$categoryStmt = $pdo->prepare($categoryQuery);
$categoryStmt->execute();
$categories = $categoryStmt->fetchAll(PDO::FETCH_OBJ); // Fetch as objects

$Orderquery = "SELECT orders.order_id, orders.user_id, users.username, orders.product_id, product_table.prod_name, orders.quantity, orders.order_date
    FROM orders orders
    JOIN users users ON orders.user_id = users  .user_id
    JOIN product_table product_table ON orders.product_id = product_table.Product_Id ORDER BY orders.order_date DESC";
$Orderstmt = $pdo->prepare($Orderquery);
$Orderstmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$query = "SELECT * FROM product_table";
$params = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['addproduct'])) {
        $newconnection->addProduct();
    } elseif (isset($_POST['delete_product'])) {
        $newconnection->deleteProduct($_POST['product_id']);
    } elseif (isset($_POST['addcategory'])) {
        $newconnection->addCategory();
    } elseif (isset($_POST['filter_products'])) {
        if (!empty($_POST['filter_category'])) {
            $query = "SELECT * FROM product_table WHERE Category = :category";
            $params[':category'] = $_POST['filter_category'];
        }
        elseif (!empty($_POST['filter_stock'])) {
            if ($_POST['filter_stock'] === 'in_stock') {
                $query = "SELECT * FROM product_table WHERE Quantity > 0";
            } elseif ($_POST['filter_stock'] === 'out_of_stock') {
                $query = "SELECT * FROM product_table WHERE Quantity = 0"; 
            }
        }
        elseif (!empty($_POST['filter_date_from']) && !empty($_POST['filter_date_to'])) {
            $query = "SELECT * FROM product_table WHERE Date_Purchase BETWEEN :date_from AND :date_to";
            $params[':date_from'] = $_POST['filter_date_from'];
            $params[':date_to'] = $_POST['filter_date_to'];
        }
    } elseif (isset($_POST['search'])) {
        $search_term = $_POST['search_term'];
        if (!empty($search_term)) {
            $query .= " WHERE Product_Name LIKE :search_term";
            $params[':search_term'] = "%" . $search_term . "%";
        }
    }

    if (isset($_POST['update_product'])) {
        $newconnection->updateProduct();
    }
}

// $query = "SELECT product_table.*, categories.Category_name 
//     FROM product_table 
//     LEFT JOIN categories ON product_table.Category = categories.Category_id";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$result = $stmt->fetchAll(PDO::FETCH_OBJ); // Fetch as objects

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD FOR PRODUCTS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar">
        <div class="container-fluid">
            <a class="navbar-brand">ABC Store</a>
            <form class="d-flex" action="" method="POST" role="search">
                <button type="button" class="btn btn-outline-success me-3" data-bs-toggle="modal" data-bs-target="#filterModal">Filter</button>
                <a href="home.php" class="btn btn-outline-secondary me-5">Reload</a>
                <input class="form-control me-2" type="search" name="search_term" placeholder="Search" aria-label="Search" style="width: 400px;">
                <button class="btn btn-outline-success" type="submit" name="search">Search</button>
            </form>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- <h2 style="color: white;">Welcome, <?= $_SESSION['first_name']; ?></h2> -->

        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">Add Product</button>
        <button type="button" class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#addCategoryModal">Add Category</button>
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success mt-3">
                <?= $_SESSION['message']; ?>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>


        <table class="table table-hover table-bordered table-striped mt-3">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>Quantity</th>
                    <th>Date Purchase</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result as $row): ?>
                    <tr data-id="<?= $row->id; ?>">
                        <td><?= $row->id; ?></td>
                        <td><?= $row->Product_Name; ?></td>
                        <td><?= $row->Category; ?></td>
                        <td><?= $row->Quantity; ?></td>
                        <td><?= $row->Date_Purchase; ?></td>
                        <td>
                        <form action="" method="POST" class="d-inline">
                            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#editProductModal" onclick="populateEditModal(<?= $row->id; ?>)">Edit</button>

                            <input type="hidden" name="product_id" value="<?= $row->id; ?>"> 
                            <button type="submit" name="delete_product" class="btn btn-danger btn-sm">Delete</button>
                        </form>

                        </td>

                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="container mt-4">
    <h2 class="text-center">Order Management</h2>

    <?php if (!empty($orders)): ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer ID</th>
                        <th>Customer Name</th>
                        <th>Product Name</th>
                        <th>Quantity</th>
                        <th>Order Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= htmlspecialchars($order['order_id']); ?></td>
                            <td><?= htmlspecialchars($order['user_id']); ?></td>
                            <td><?= htmlspecialchars($order['username']); ?></td>
                            <td><?= htmlspecialchars($order['prod_name']); ?></td>
                            <td><?= htmlspecialchars($order['quantity']); ?></td>
                            <td><?= htmlspecialchars($order['order_date']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-center">No orders have been placed yet.</p>
    <?php endif; ?>
</div>

    <?php include 'modal.php'; ?>
    <?php include 'style.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    function populateEditModal(productId) {
        const row = document.querySelector(`tr[data-id='${productId}']`);
        const productName = row.querySelector("td:nth-child(2)").textContent;
        const category = row.querySelector("td:nth-child(3)").textContent;
        const quantity = row.querySelector("td:nth-child(4)").textContent;
        const datePurchase = row.querySelector("td:nth-child(5)").textContent;

        document.getElementById("edit_id").value = productId;
        document.getElementById("edit_product_name").value = productName;
        document.getElementById("edit_category").value = category;
        document.getElementById("edit_quantity").value = quantity;
        document.getElementById("edit_date_purchase").value = datePurchase;
    }
    </script>

</body>
</html>