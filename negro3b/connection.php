<?php

class Connection {
    private $host = 'localhost';
    private $db = 'negro3b'; 
    private $user = 'root';
    private $pass = ''; 

    public function openConnection() {
        try {
            $pdo = new PDO("mysql:host=$this->host;dbname=$this->db", $this->user, $this->pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            die("Could not connect to the database $this->db :" . $e->getMessage());
        }
    }

    //ADD PRODUCT
    public function addProduct()
    {
        if (isset($_POST['addproduct'])) {
            $product_name = $_POST['product_name'];
            $category_id = $_POST['category_id']; // Category is selected from a dropdown
            $quantity = $_POST['quantity'];
            $date_purchase = $_POST['date_purchase'];

            try {
                $connection = $this->openConnection();
                $query = "INSERT INTO product_table (Product_Name, Category, Quantity, Date_Purchase) VALUES (?, ?, ?, ?)";
                $stmnt = $connection->prepare($query);
                $stmnt->execute([$product_name, $category_id, $quantity, $date_purchase]);

                $_SESSION['message'] = "Product added successfully!";
                header("Location: home.php");
                exit;
            } catch (PDOException $e) {
                echo "Error: " . $e->getMessage();
            }
        }
    }


    // DELETE PRODUCT
    public function deleteProduct()
    {
        if (isset($_POST['delete_product'])) {
            $id = $_POST['delete_product'];  
            try {
                $connection = $this->openConnection();
                $query = "DELETE FROM product_table WHERE id = :id";
                $stmt = $connection->prepare($query);
                $stmt->execute([':id' => $id]);
                $_SESSION['message'] = "Product deleted successfully";
            } catch (PDOException $th) {
                echo "Error: " . $th->getMessage();
            }
        }
    }


    // UPDATE PRODUCT
    public function updateProduct()
    {
        if (isset($_POST['update_product'])) {
            $id = $_POST['product_id'];  // This is the product ID
            $Product_Name = $_POST['productname'];
            $Category = $_POST['category'];
            $Quantity = $_POST['quantity'];
            $Date_Purchase = $_POST['datepurchase'];

            try {
                $connection = $this->openConnection();
                $query = "UPDATE product_table SET Product_Name = ?, Category = ?, Quantity = ?, Date_Purchase = ? WHERE id = ?";
                $stmt = $connection->prepare($query);
                $stmt->execute([$Product_Name, $Category, $Quantity, $Date_Purchase, $id]);

                $_SESSION['message'] = "Product updated successfully";
            } catch (PDOException $th) {
                echo "Error: " . $th->getMessage();
            }
        }
    }


    //ADD CATEGORIES
    public function addCategory()
    {
        if (isset($_POST['addcategory'])) {
            $catname = $_POST['catname']; // Category name input from form

            try {
                $connection = $this->openConnection();

                // Check if category already exists
                $checkQuery = "SELECT COUNT(*) FROM categories WHERE catname = :catname";
                $stmt = $connection->prepare($checkQuery);
                $stmt->execute([':catname' => $catname]);
                $categoryExists = $stmt->fetchColumn();

                if ($categoryExists > 0) {
                    // Category already exists
                    $_SESSION['message'] = "Category already exists!";
                    header("Location: home.php");
                    exit;
                }

                // Insert new category if it doesn't exist
                $query = "INSERT INTO categories (catname) VALUES (:catname)";
                $stmnt = $connection->prepare($query);
                $stmnt->execute([':catname' => $catname]);

                $_SESSION['message'] = "Category added successfully!";
                header("Location: home.php");
                exit;
            } catch (PDOException $e) {
                echo "Error: " . $e->getMessage();
            }
        }
    }


    // Function to remove product from the cart
    public function removeFromCart($user_id, $product_id) {
        try {
            $connection = $this->openConnection();
            $removeFromDb = $connection->prepare("DELETE FROM cart WHERE user_id = :user_id AND product_id = :product_id");
            $removeFromDb->execute([':user_id' => $user_id, ':product_id' => $product_id]);
            return true;
        } catch (PDOException $e) {
            return "Error removing product: " . $e->getMessage();
        }
    }

    // Function to insert order and update stock during checkout
public function processCheckout($user_id, $cart) {
    try {
        $connection = $this->openConnection();
        $connection->beginTransaction();

        foreach ($cart as $product_id => $cartItem) {
            $quantity = $cartItem['quantity'];

            $stmt = $connection->prepare("INSERT INTO orders (user_id, product_id, quantity) VALUES (:user_id, :product_id, :quantity)");
            $stmt->execute([':user_id' => $user_id, ':product_id' => $product_id, ':quantity' => $quantity]);

            $updateStock = $connection->prepare("UPDATE product_table SET Quantity = Quantity - :quantity WHERE id = :product_id");
            $updateStock->execute([':quantity' => $quantity, ':product_id' => $product_id]);

            $removeFromDb = $connection->prepare("DELETE FROM cart WHERE user_id = :user_id AND product_id = :product_id");
            $removeFromDb->execute([':user_id' => $user_id, ':product_id' => $product_id]);

            if ($removeFromDb->rowCount() > 0) {
                echo "Item removed from the cart table for product ID: $product_id <br>";
            } else {
                echo "Error removing item from the cart table for product ID: $product_id <br>";
            }
        }

        $connection->commit();
        return true;
    } catch (PDOException $e) {
        $connection->rollBack();
        return "Error during checkout: " . $e->getMessage();
    }
}


    
}