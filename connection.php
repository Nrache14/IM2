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
            $Product_Name = $_POST['productname'];
            $Category = $_POST['category'];
            $Quantiy = $_POST['quantity'];
            $Date_Purchase = $_POST['datepurchase'];

            try {
                $connection = $this->openConnection();
                $query = "INSERT INTO product_table (`Product_Name`,`Category`,`Quantity`,`Date_Purchase`) 
                VALUES ( ?, ?, ?, ?)";
                $stmt = $connection->prepare($query);
                $stmt->execute([$Product_Name, $Category, $Quantiy, $Date_Purchase]);
            } catch (PDOException $th) {
                echo "Error: " . $th->getMessage();
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
            // Retrieve the values from the form
            $id = $_POST['edit_id'];
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
}