<?php
session_start();
require_once('connection.php');

$newconnection = new Connection();
$pdo = $newconnection->openConnection();

// Handle login functionality
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_submit'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare the query to validate the user
    // Use hashed password verification for security
    $users = $pdo->prepare("SELECT * FROM user WHERE username = :username");
    $users->execute(['username' => $username]);
    $result = $users->fetch();

    // Check if the user exists and verify password
    if ($result && password_verify($password, $result->password)) { // Ensure the password is hashed
        $_SESSION['name'] = $result->name;
        header('Location: index.php');  // Reload the page after login to show products
        exit();
    } else {
        $login_error = "Invalid username or password!";
    }
}

// Check if the user is logged in
if (!isset($_SESSION['name'])) {
    // Show the login form if not logged in
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login</title>
    </head>
    <body>
        <h2>Login</h2>
        <?php if (isset($login_error)) { echo "<p style='color:red;'>$login_error</p>"; } ?>
        <form action="index.php" method="post">
            <label for="username">Username:</label>
            <input type="text" name="username" id="username" required><br><br>
            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required><br><br>
            <button type="submit" name="login_submit">Login</button>
        </form>
    </body>
    </html>
    <?php
    exit();
}

// Handle product operations if the user is logged in
$query = "SELECT * FROM product_table";
$params = [];

// Handle form submissions for products
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['login_submit'])) {
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

    // Update product handling logic here
    if (isset($_POST['update_product'])) {
        $newconnection->updateProduct();
    }
}

// Prepare and execute the filtered query
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
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: darkgray;
        }
    </style>
</head>

<body>
    <!-- Navigation Bar with Logout and Search -->
    <nav class="navbar" style="background-color: #e3f2fd;">
        <div class="container-fluid">
            <a class="navbar-brand" style="font-family: 'Arial', sans-serif; font-weight: bold; font-size: 30px; margin-left: 100px;">ABC Store</a>
            <form class="d-flex" action="" method="POST" role="search">
                <!-- Filter Button -->
                <button type="button" class="btn btn-outline-success me-3" data-bs-toggle="modal" data-bs-target="#filterModal">Filter</button>
                <!-- Reload Button -->
                <a href="index.php" class="btn btn-outline-secondary me-5">Reload</a>
                <!-- Search Input -->
                <input class="form-control me-2" type="search" name="search_term" placeholder="Search" aria-label="Search" style="width: 400px;">
                <button class="btn btn-outline-success" type="submit" name="search">Search</button>
            </form>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Welcome, <?= $_SESSION['name']; ?></h2> <!-- Display the logged-in user's name -->

        <!-- Add Product Button that triggers the modal -->
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
            Add Product
        </button>

        <?php if (isset($_SESSION['message'])): ?>
            <h5 class="alert alert-success"> <?= $_SESSION['message']; ?> </h5>
            <?php unset($_SESSION['message']);  ?>
        <?php endif; ?>

        <!-- Product List Table -->
        <div class="info-table container mt-4">
            <table class="table table-dark table-striped">
                <thead>
                    <tr>
                        <th scope="col">Id</th>
                        <th scope="col">Product Name</th>
                        <th scope="col">Category</th>
                        <th scope="col">Quantity</th>
                        <th scope="col">Date Purchase</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result) {
                        foreach ($result as $row) {
                    ?>
                            <tr>
                                <td><?php echo $row->id; ?></td> <!-- Changed from $row['id'] to $row->id -->
                                <td><?php echo $row->Product_Name; ?></td> <!-- Changed from $row['Product_Name'] to $row->Product_Name -->
                                <td><?php echo $row->Category; ?></td> <!-- Changed from $row['Category'] to $row->Category -->
                                <td><?php echo $row->Quantity; ?></td> <!-- Changed from $row['Quantity'] to $row->Quantity -->
                                <td><?php echo $row->Date_Purchase; ?></td> <!-- Changed from $row['Date_Purchase'] to $row->Date_Purchase -->
                                <td>
                                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editProductModal"
                                        onclick="populateEditModal(<?= $row->id; ?>)">Edit</button> <!-- Changed from $row['id'] to $row->id -->
                                    <form action="" method="post" class="d-inline">
                                        <input type="hidden" name="delete_product" value="<?php echo $row->id; ?>"> <!-- Changed from $row['id'] to $row->id -->
                                        <button type="submit" class="btn btn-danger" name="delete_product">Delete</button>
                                    </form>
                                </td>
                            </tr>
                    <?php
                        }
                    } else {
                        echo "<tr><td colspan='6' class='text-center'>No products found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal for Adding Product -->
    <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addProductModalLabel">Add Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="" method="POST">
                        <div class="mb-3">
                            <label for="product_name" class="form-label">Product Name</label>
                            <input type="text" name="productname" class="form-control" id="product_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="category" class="form-label">Category</label>
                            <select id="category" name="category" class="form-select" required>
                                <option selected>Food</option>
                                <option>Drinks</option>
                                <option>Shoes</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="quantity" class="form-label">Quantity</label>
                            <input type="text" name="quantity" class="form-control" id="quantity" required>
                        </div>
                        <div class="mb-3">
                            <label for="datepurchase" class="form-label">Date Purchase</label>
                            <input type="date" name="datepurchase" class="form-control" id="datepurchase" required>
                        </div>
                        <button type="submit" class="btn btn-primary" name="addproduct">Add Product</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Filtering Products -->
    <div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="filterModalLabel">Filter Products</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="" method="POST">
                        <div class="mb-3">
                            <label for="filter_category" class="form-label">By Category</label>
                            <select id="filter_category" name="filter_category" class="form-select">
                                <option value="">All Categories</option>
                                <option value="Food">Food</option>
                                <option value="Drinks">Drinks</option>
                                <option value="Shoes">Shoes</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="filter_stock" class="form-label">By Stock Status</label>
                            <select id="filter_stock" name="filter_stock" class="form-select">
                                <option value="">All</option>
                                <option value="in_stock">In Stock</option>
                                <option value="out_of_stock">Out of Stock</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="filter_date_from" class="form-label">Purchased From</label>
                            <input type="date" name="filter_date_from" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="filter_date_to" class="form-label">Purchased To</label>
                            <input type="date" name="filter_date_to" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-primary" name="filter_products">Filter</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Editing Product -->
    <div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editProductModalLabel">Edit Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="" method="POST">
                        <input type="hidden" name="edit_id" id="edit_id">
                        <div class="mb-3">
                            <label for="edit_product_name" class="form-label">Product Name</label>
                            <input type="text" name="productname" class="form-control" id="edit_product_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_category" class="form-label">Category</label>
                            <select id="edit_category" name="category" class="form-select" required>
                                <option>Food</option>
                                <option>Drinks</option>
                                <option>Shoes</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_quantity" class="form-label">Quantity</label>
                            <input type="text" name="quantity" class="form-control" id="edit_quantity" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_datepurchase" class="form-label">Date Purchase</label>
                            <input type="date" name="datepurchase" class="form-control" id="edit_datepurchase" required>
                        </div>
                        <button type="submit" class="btn btn-primary" name="update_product">Update Product</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- <a href="logout.php">logout</a> -->



    <script>
        function populateEditModal(id) {
            // Assuming you have a way to retrieve the product details
            const products = <?= json_encode($result); ?>; // Encode PHP variable to JavaScript

            const product = products.find(p => p.id === id);
            if (product) {
                document.getElementById('edit_id').value = product.id;
                document.getElementById('edit_product_name').value = product.Product_Name;
                document.getElementById('edit_category').value = product.Category;
                document.getElementById('edit_quantity').value = product.Quantity;
                document.getElementById('edit_datepurchase').value = product.Date_Purchase;
            }
        }
    </script>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>
