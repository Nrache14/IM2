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
            $id = $_POST['delete_product'];  // This should be the ID of the product to delete.
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
            $catname = $_POST['catname'];

            try {
                $connection = $this->openConnection();
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


    public function getCategories()
    {
        try {
            $connection = $this->openConnection();
            $query = "SELECT * FROM categories";
            $stmnt = $connection->prepare($query);
            $stmnt->execute();
            return $stmnt->fetchAll();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
        return [];
    }
}