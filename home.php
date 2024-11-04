<?php
session_start();
require_once('connection.php');

// Redirect to login if not logged in
if (!isset($_SESSION['name'])) {
    header('Location: login.php');
    exit();
}

$newconnection = new Connection();
$pdo = $newconnection->openConnection();

$query = "SELECT * FROM product_table";
$params = [];

// Handle product operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['addproduct'])) {
        $newconnection->addProduct();
    } elseif (isset($_POST['delete_product'])) {
        $newconnection->deleteProduct();
    } elseif (isset($_POST['filter_products'])) {
        // Filter by Category
        if (!empty($_POST['filter_category'])) {
            $query = "SELECT * FROM product_table WHERE Category = :category";
            $params[':category'] = $_POST['filter_category'];
        }
        // Filter by Stock Status
        elseif (!empty($_POST['filter_stock'])) {
            if ($_POST['filter_stock'] === 'in_stock') {
                $query = "SELECT * FROM product_table WHERE Quantity > 0"; // Products with quantity > 0 are in stock
            } elseif ($_POST['filter_stock'] === 'out_of_stock') {
                $query = "SELECT * FROM product_table WHERE Quantity = 0"; // Products with quantity = 0 are out of stock
            }
        }
        // Filter by Date Range
        elseif (!empty($_POST['filter_date_from']) && !empty($_POST['filter_date_to'])) {
            $query = "SELECT * FROM product_table WHERE Date_Purchase BETWEEN :date_from AND :date_to";
            $params[':date_from'] = $_POST['filter_date_from'];
            $params[':date_to'] = $_POST['filter_date_to'];
        }
    } elseif (isset($_POST['search'])) {
        // Capture the search term
        $search_term = $_POST['search_term'];

        // Modify query to filter by product name
        if (!empty($search_term)) {
            $query .= " WHERE Product_Name LIKE :search_term";
            $params[':search_term'] = "%" . $search_term . "%";  // Use % for partial match
        }
    }

    // **Insert the update_product handling logic here**
    if (isset($_POST['update_product'])) {
        $newconnection->updateProduct();
    }
}

// Prepare and execute the query
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
    <style>
        body {
            background-color: darkgray;
        }
    </style>
</head>
<body>
    <nav class="navbar" style="background-color: #e3f2fd;">
        <div class="container-fluid">
            <a class="navbar-brand" style="font-family: 'Arial', sans-serif; font-weight: bold; font-size: 30px; margin-left: 100px;">ABC Store</a>
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
        <h2>Welcome, <?= $_SESSION['name']; ?></h2>

        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">Add Product</button>

        <?php if (isset($_SESSION['message'])): ?>
            <h5 class="alert alert-success"> <?= $_SESSION['message']; ?> </h5>
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
                    <tr data-id="<?= $row->Product_Id; ?>">
                        <td><?= $row->Product_Id; ?></td>
                        <td><?= $row->Product_Name; ?></td>
                        <td><?= $row->Category; ?></td>
                        <td><?= $row->Quantity; ?></td>
                        <td><?= $row->Date_Purchase; ?></td>
                        <td>
                            <form action="" method="POST" class="d-inline">
                                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#editProductModal" onclick="populateEditModal(<?= $row->Product_Id; ?>)">Edit</button>
                                <input type="hidden" name="delete_id" value="<?= $row->Product_Id; ?>">
                                <button type="submit" name="delete_product" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php include 'modal.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Function to populate edit modal with existing product details
        function populateEditModal(productId) {
            const row = document.querySelector(`tr[data-id='${productId}']`);
            const productName = row.querySelector("td:nth-child(2)").textContent;
            const category = row.querySelector("td:nth-child(3)").textContent;
            const quantity = row.querySelector("td:nth-child(4)").textContent;
            const datePurchase = row.querySelector("td:nth-child(5)").textContent;

            document.getElementById("edit_product_id").value = productId;
            document.getElementById("edit_product_name").value = productName;
            document.getElementById("edit_category").value = category;
            document.getElementById("edit_quantity").value = quantity;
            document.getElementById("edit_date_purchase").value = datePurchase;
        }
    </script>
</body>
</html>
