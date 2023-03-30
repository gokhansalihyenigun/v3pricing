<?php
include 'config.php';

// Ürün ekleme işlemi
if (isset($_POST['add_product'])) {
    $product_name = $_POST['product_name'];
    $product_price = $_POST['unit_price'];

    $sql = "INSERT INTO products (name, price) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sd", $product_name, $product_price);
    $stmt->execute();

    // Ürün eklendikten sonra ana sayfaya yönlendirme yapılıyor.
    header('Location: product_operations.php');
}

// Ürün düzenleme işlemi
if (isset($_POST['edit_product'])) {
    $product_id = $_POST['product_id'];
    $product_name = $_POST['product_name'];
    $product_price = $_POST['unit_price'];

    $sql = "UPDATE products SET name = ?, price = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdi", $product_name, $product_price, $product_id);
    $stmt->execute();

    // Ürün düzenlendikten sonra ana sayfaya yönlendirme yapılıyor.
    header('Location: product_operations.php');
}

// Ürün silme işlemi
if (isset($_GET['delete_product'])) {
    $product_id = $_GET['delete_product'];

    $sql = "DELETE FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();

    // Ürün silindikten sonra ana sayfaya yönlendirme yapılıyor.
    header('Location: product_operations.php');
}
?>