<?php

class Connection
{
    private $server = "mysql:host=localhost;dbname=negro3b";
    private $dbuser = "root";
    private $dbpass = "";
    private $options = array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
    );
    protected $con;

    public function openConnection()
    {
        try {
            $this->con = new PDO($this->server, $this->dbuser, $this->dbpass, $this->options);
            return $this->con;
        } catch (PDOException $err) {
            echo "Database connection problem: " . $err->getMessage();
            return null;
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
                // $_SESSION['message'] = "Student added successfully"; // Set a success message
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
                $stmt->execute([':id' => $id]); // Ensure the binding is correct

                // Optional: set a success message to display to the user
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

                $_SESSION['message'] = "Product updated successfully"; // Set a success message
            } catch (PDOException $th) {
                echo "Error: " . $th->getMessage();
            }
        }
    }
}
