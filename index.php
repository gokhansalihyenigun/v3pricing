<?php
session_start();

if (!isset($_SESSION['user_id'])) {
   header("Location: login.php");
    exit;
}
include 'config.php';

// Kullanıcı ekleme işlemi
if (isset($_POST['add_user'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $display_name = $_POST['display_name'];

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (username, password, display_name) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $hashed_password, $display_name);
    $stmt->execute();
}
// Kullanıcı silme işlemi
if (isset($_GET['delete_user'])) {
    $user_id = $_GET['delete_user'];

    $sql = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
}
// Kullanıcıları veritabanından alın
$sql = "SELECT * FROM users";
$result = $conn->query($sql);
$users = $result->fetch_all(MYSQLI_ASSOC);




// Ürün ekleme işlemi
if (isset($_POST['add_product'])) {
    $product_name = $_POST['product_name'];
    $product_price = $_POST['unit_price'];

    $sql = "INSERT INTO products (product_name, unit_price) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sd", $product_name, $unit_price);
    $stmt->execute();
}

// Ürün düzenleme işlemi
//if (isset($_POST['edit_product'])) {
 //   $product_id = $_POST['product_id'];
//    $product_name = $_POST['product_name'];
//    $product_price = $_POST['unit_price'];

//    $sql = "UPDATE products SET product_name = ?, unit_price = ? WHERE id = ?";
//    $stmt = $conn->prepare($sql);
////    $stmt->bind_param("sdi", $product_name, $unit_price, $product_id);
//    $stmt->execute();
//}

// Ürün silme işlemi
if (isset($_GET['delete_product'])) {
    $product_id = $_GET['delete_product'];

    $sql = "DELETE FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
}

// Ürünleri veritabanından alın
$sql = "SELECT * FROM products";
$result = $conn->query($sql);
$products = $result->fetch_all(MYSQLI_ASSOC);

// Hesaplamaları veritabanından alın
$sql = "SELECT * FROM calculations";
$result = $conn->query($sql);
$calculations = $result->fetch_all(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<button onclick="location.href='https://pricing.cloudspark.com.tr/phppricecalculator/calculator.php';" class="btn btn-primary">Pricing Calculator</button>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .form-group {
            margin-bottom: 1rem;
        }
        table {
            width: 100%;
            margin-bottom: 1rem;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #dee2e6;
        }
        th, td {
            padding: 0.75rem;
            text-align: left;
        }
        .sort-btn {
            cursor: pointer;
            padding-left: 5px;
            padding-right: 5px;
        }
    </style>
    <script>
        
        const calculations = <?php echo json_encode($calculations); ?>;
        const sortDirection = {
            'customer_name': 'asc',
            'created_at': 'asc',
            'prepared_by': 'asc',
			'total_price': 'asc',
        };

        function renderSortedCalculations(column, direction) {
            sortDirection[column] = direction === 'asc' ? 'desc' : 'asc';
            const sortedCalculations = [...calculations].sort((a, b) => {
                if (a[column] < b[column]) {
                    return direction === 'asc' ? -1 : 1;
                } else if (a[column] > b[column]) {
                    return direction === 'asc' ? 1 : -1;
                } else {
                    return 0;
                }
            });

            const tableBody = document.querySelector('#calculations-table');
            tableBody.innerHTML = '';

            for (const calculation of sortedCalculations) {
                const row = document.createElement('tr');

                row.innerHTML = `
                    <td>${calculation.id}</td>
                    <td>${calculation.customer_name}</td>
                    <td>${new Date(calculation.created_at).toLocaleDateString()}</td>
                    <td>${new Date(calculation.created_at).toLocaleTimeString()}</td>
                    <td>${calculation.prepared_by}</td>
					<td>${calculation.total_price}</td>
                    <td>
                        <a href="exports/${calculation.pdf_filename}" class="btn btn-primary" download>İndir</a>
                    </td>
                `;

                tableBody.appendChild(row);
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            renderSortedCalculations('customer_name', sortDirection['customer_name']);
        });
    
    </script>
</head>
<body>
    <div class="container">
        <h1 class="mt-4 mb-4">Admin Panel</h1>
        <!-- Ürün ekleme formu -->
        <h2>Ürün Ekle</h2>
        <form method="post">
            <div class="form-group">
                <label for="product_name">Ürün Adı</label>
                <input type="text" class="form-control" id="product_name" name="product_name" placeholder="Ürün Adı" required>
            </div>
            <div class="form-group">
                <label for="product_price">Ürün Fiyatı</label>
                <input type="number" class="form-control" id="unit_price" step="0.01" name="unit_price" placeholder="Ürün Fiyatı" required>
            </div>
            <button type="submit" class="btn btn-primary" name="add_product">Ürün Ekle</button>
        </form>
		
		<!-- Kullanıcı ekleme formu -->
<h2 class="mt-4 mb-3">Kullanıcı Ekle</h2>
<form method="post">
    <div class="form-group">
        <label for="username">Kullanıcı Adı</label>
        <input type="text" class="form-control" id="username" name="username" placeholder="Kullanıcı Adı" required>
    </div>
    <div class="form-group">
        <label for="password">Şifre</label>
        <input type="password" class="form-control" id="password" name="password" placeholder="Şifre" required>
    </div>
    <div class="form-group">
        <label for="display_name">Görünen İsim</label>
        <input type="text" class="form-control" id="display_name" name="display_name" placeholder="Görünen İsim" required>
    </div>
    <button type="submit" class="btn btn-primary" name="add_user">Kullanıcı Ekle</button>
</form>




        <!-- Ürün listesi ve düzenleme/silme butonları -->
        <h2 class="mt-4 mb-3">Ürünler</h2>
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Ürün Adı</th>
                    <th>Fiyat</th>
                    <th>Düzenle</th>
                    <th>Sil</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product) { ?>
                    <tr>
                        <td><?php echo $product['id']; ?></td>
                        <td><?php echo $product['product_name']; ?></td>
                        <td><?php echo $product['unit_price']; ?></td>
                        <td>
                            <form method="post">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <input type="text" class="form-control" name="product_name" value="<?php echo $product['product_name']; ?>" required>
                                <input type="number" class="form-control" step="0.01" name="unit_price" value="<?php echo $product['unit_price']; ?>" required>
                                
                            </form>
                        </td>
                        <td>
                            <a href="?delete_product=<?php echo $product['id']; ?>" class="btn btn-danger">Sil</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <!-- Hesaplamaları görüntülemek ve pdf'leri indirmek için tablo ve bağlantıları -->
        <h2 class="mt-4 mb-3">Hesaplamalar</h2>
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                                        <th>Müşteri İsmi <span class="sort-btn" onclick="renderSortedCalculations('customer_name', sortDirection['customer_name'])">&#x21D5;</span></th>

                    <th>Tarih <span class="sort-btn" onclick="renderSortedCalculations('created_at', sortDirection['created_at'])">&#x21D5;</span></th>
                    <th>Saat</th>
                    <th>Hazırlayan <span class="sort-btn" onclick="renderSortedCalculations('prepared_by', sortDirection['prepared_by'])">&#x21D5;</span></th>
					<th>Toplam Fiyat <span class="sort-btn" onclick="renderSortedCalculations('total_price', sortDirection['total_price'])">&#x21D5;</span></th>
                    <th>PDF/XLSX İndir</th>
                </tr>
            </thead>
            <tbody id="calculations-table">
                <?php foreach ($calculations as $calculation) { ?>
                    <tr>
                        <td><?php echo $calculation['id']; ?></td>
                        <td><?php echo $calculation['customer_name']; ?></td>
                        <td><?php echo date('Y-m-d', strtotime($calculation['created_at'])); ?></td>
                        <td><?php echo date('H:i:s', strtotime($calculation['created_at'])); ?></td>
                        <td><?php echo $calculation['prepared_by']; ?></td>
						<td>$<?php echo $calculation['total_price']; ?></td>
                        <td>
                            <a href="exports/<?php echo $calculation['pdf_filename']; ?>" class="btn btn-primary" download>İndir</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</body>
</html>