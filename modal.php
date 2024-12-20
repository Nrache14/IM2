<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="" method="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addProductModalLabel">Add Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="product_name" class="form-label">Product Name</label>
                        <input type="text" class="form-control" id="product_name" name="product_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Category</label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <option value="" disabled selected>Select a Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category->id; ?>"><?= $category->catname; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label for="date_purchase" class="form-label">Date of Purchase</label>
                        <input type="date" class="form-control" id="date_purchase" name="date_purchase" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="addproduct" class="btn btn-primary">Add Product</button>
                </div>
            </div>
        </form>
    </div>
</div>


<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="" method="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCategoryModalLabel">Add Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="catname" class="form-label">Category Name</label>
                        <input type="text" class="form-control" id="catname" name="catname" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="addcategory" class="btn btn-primary">Add Category</button>
                </div>
            </div>
        </form>
    </div>
</div>



<!-- Filter Modal -->
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
                        <label for="filter_category" class="form-label">Filter by Category</label>
                        <!-- <input type="text" name="filter_category" class="form-control" id="filter_category"> -->
                        <select class="form-select" name="selectedCategory" required>
                            <option selected disabled>Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category->Cat_id; ?>"><?php echo $category->catname; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="filter_stock" class="form-label">Filter by Stock</label>
                        <select name="filter_stock" class="form-select" id="filter_stock">
                            <option selected disabled>Select Stock Status</option>
                            <option value="in_stock">In Stock</option>
                            <option value="out_of_stock">Out of Stock</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="filter_date_from" class="form-label">Date from</label>
                        <input type="date" name="filter_date_from" class="form-control" id="filter_date_from">
                    </div>
                    <div class="mb-3">
                        <label for="filter_date_to" class="form-label">Date to</label>
                        <input type="date" name="filter_date_to" class="form-control" id="filter_date_to">
                    </div>
                    <button type="submit" name="filter_products" class="btn btn-primary">Apply Filter</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProductModalLabel">Edit Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST">
                    <input type="hidden" name="product_id" id="edit_id">  <!-- Hidden field for product id -->
                    <div class="mb-3">
                        <label for="productname" class="form-label">Product Name</label>
                        <input type="text" name="productname" class="form-control" id="edit_product_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="category" class="form-label">Category</label>
                        <input type="text" name="category" class="form-control" id="edit_category" required>
                    </div>
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="number" name="quantity" class="form-control" id="edit_quantity" required>
                    </div>
                    <div class="mb-3">
                        <label for="datepurchase" class="form-label">Date Purchase</label>
                        <input type="date" name="datepurchase" class="form-control" id="edit_date_purchase" required>
                    </div>
                    <button type="submit" name="update_product" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>
    
