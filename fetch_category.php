<?php
// Assuming you have a database connection
include 'layouts/config.php';

// Get the product ID from the query string
$product_id = $_GET['product_id'];

// Fetch the category ID based on the product's parent ID (iParentid)
$stmt = $link->prepare("SELECT iParentid FROM tblproduct WHERE iProductid = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product_result = $stmt->get_result();
$product = $product_result->fetch_assoc();

if ($product) {
    $parent_id = $product['iParentid'];

    // Fetch categories based on the parent ID
    $stmt = $link->prepare("SELECT * FROM tblcategoryname WHERE id = ? ORDER BY sCategoryname");
    $stmt->bind_param("i", $parent_id);
    $stmt->execute();
    $category_result = $stmt->get_result();

    if ($category_result->num_rows > 0) {
        // Generate category options (based on parent)
        while ($category = $category_result->fetch_assoc()) {
            echo "<option value='" . $category['id'] . "'>" . $category['sCategoryname'] . "</option>";
        }
    } else {
        // If no matching category found, fetch all categories
        $all_stmt = $link->query("SELECT * FROM tblcategoryname ORDER BY sCategoryname");

        if ($all_stmt->num_rows > 0) {
            while ($category = $all_stmt->fetch_assoc()) {
                echo "<option value='" . $category['id'] . "'>" . $category['sCategoryname'] . "</option>";
            }
        } else {
            echo "<option value=''>No Categories Available</option>";
        }
    }
} else {
    echo "<option value=''>Invalid Product</option>";
}
?>
