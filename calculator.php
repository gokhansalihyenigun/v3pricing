<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
include 'config.php';
include 'tcpdf/tcpdf.php';
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

function get_products($conn) {
    $sql = "SELECT * FROM products";
    $result = $conn->query($sql);
    $products = array();
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
    return $products;
}

$products = get_products($conn);


    

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['calculate'])) {
    $customer_name = $_POST['customer_name'];
    $total_price = $_POST['total_price'];
    $prepared_by = $_POST['prepared_by'];



    // Ürünleri $items değişkenine atayın
    $items = array();
    foreach ($products as $product) {
        $quantity = intval($_POST["quantity_" . $product['id']]);
        if ($quantity > 0) {
            $items[] = array(
                "name" => $product["product_name"],
                "price" => $product["unit_price"],
                "quantity" => $quantity,
                "total" => $product["unit_price"] * $quantity
            );
        }
    }

 

}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calculator</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
	
	
    <div class="container">
        <h1>Calculator</h1>
        <form method="post">
        
           <div class="form-group">
    <label for="customer_name">Müşteri İsmi:</label>
    <input type="text" name="customer_name" id="customer_name" class="form-control" required>
</div>
<div class="form-group">
    <label for="prepared_by">Hazırlayan:</label>
    <select name="prepared_by" id="prepared_by" class="form-control" required>
        <option value="">Seçiniz</option>
        <option value="Bora ARAT">Bora ARAT</option>
        <option value="Aşkın KILIÇ">Aşkın KILIÇ</option>
        <option value="Yunus BIYIKOĞLU">Yunus BIYIKOĞLU</option>
		<option value="Gökhan Salih YENİGÜN">Gökhan Salih YENİGÜN</option>
    </select>
</div>
<table class="table">
    <thead>
                    <tr>
                        <th>Ürün</th>
                        <th>Birim Fiyat</th>
                        <th>Adet</th>
						<th>Toplam</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product) { ?>
                        <tr>
                            <td><?php echo $product['product_name']; ?></td>
                            <td> $ <?php echo $product['unit_price']; ?></td>
                            <td>
                                <input type="number" placeholder="0" class="product-quantity" id="test_x" name="quantity_<?php echo $product['id']; ?>" data-price="<?php echo $product['unit_price']; ?>" min="0">
                            </td> 
							<td>  <span  id="quantity_<?php echo $product['id']; ?>">	$ 0,00</span>     </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        
			
					<div class="container mt-1">
  <div class="row">
    <div class="col-6">
    </div>
    <div class="col-2">     
		<span class="badge pill bg-light" for="total_price">Toplam Fiyat</span>
    </div>
    <div class="col-4">
      <div class="form-group">
     
             
				
				<div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text">$</span>
  </div>

					
  <input  type="number" step="0.01" name="total_price" id="total_price" class="form-control"  disabled onselectstart="return false;" onmousedown="return false;">
 
</div>
				
            </div>
    </div>
  </div>
</div>			
			
 

			<div class="form-group">
    <label>Export Format:</label>
    <div class="form-check">
        <input class="form-check-input" type="radio" name="export_format" id="pdf" value="pdf" checked>
        <label class="form-check-label" for="pdf">PDF</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="radio" name="export_format" id="xlsx" value="xlsx">
        <label class="form-check-label" for="xlsx">Excel (XLSX)</label>
    </div>
</div>
            <button type="submit" name="calculate" class="btn btn-primary">Hesapla ve İndir</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function () {
            // Ürün adetlerinin herhangi biri değiştiğinde toplam fiyatı güncelle
		
            $(".product-quantity").on("input", function (e) {
                let total_price = 0;	
				$("#"+$(this).attr("name")).text( "$"+($(this).val()*parseFloat($(this).data("price"))).toFixed(2));
                $(".product-quantity").each(function (e) {
                    let quantity = parseInt($(this).val());
                    let price = parseFloat($(this).data("price"));
                    if (!isNaN(quantity) && !isNaN(price)) {
                        total_price += quantity * price;
                    }
						
                });
                $("#total_price").val(total_price.toFixed(2));
            });
        });
		
		 
    </script>

<?php
if (isset($_POST['calculate'])) {
    $customer_name = $_POST['customer_name'];
    
    $items = array();
    $total_price = 0;
    foreach ($products as $product) {
        $quantity_input_name = 'quantity_' . $product['id'];
        if (isset($_POST[$quantity_input_name]) && intval($_POST[$quantity_input_name]) > 0) {
            $items[] = array(
                'name' => $product['product_name'],
                'price' => $product['unit_price'],
                'quantity' => intval($_POST[$quantity_input_name]),
                'total' => $product['unit_price'] * intval($_POST[$quantity_input_name])
            );
            $total_price += $product['unit_price'] * intval($_POST[$quantity_input_name]);
		 
			
        }
    }
	
	$export_format = $_POST['export_format'];
if ($export_format === 'pdf') {

    // PDF oluşturma işlemi burada yapılacak

    // TCPDF ile PDF oluşturma ve kaydetme işlemini burada gerçekleştirin
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // PDF başlığını ve yazarını ayarlayın
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor($prepared_by);
    $pdf->SetTitle('Cloudspark Teklifi');

    // PDF ayarlarını yapın
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
	$pdf->SetFont('dejavusans', '', 10, '', true);

    // İlk sayfayı ekle
    $pdf->AddPage();
// Tarihi al
$current_date = date("d-m-Y");

// İçeriği oluşturun
$html = '<div style="width: 100%; margin-bottom: 20px; display: flex; align-items: center;">
            <div><img src="logo.png" alt="Logo" width="300"></div>
            <div style="font-weight: bold; white-space: nowrap; text-align: right; flex: 1; align-self: flex-start;">Tarih: ' . $current_date . '</div>
         </div>';

$html .= '<h1 style="text-align: center; margin-bottom: 20px; color: #1a73e8;">Cloud Hizmeti</h1>';
$html .= '<div style="margin-bottom: 20px; border-top: 2px solid #1a73e8; padding-top: 10px;">
            <div style="margin-bottom: 10px;"><strong>Müşteri Adı:</strong> ' . $customer_name . '</div>
            <div style="margin-bottom: 10px;"><strong>Hazırlayan:</strong> ' . $prepared_by . '</div>
          </div>';

$html .= '<table border="1" cellpadding="5" cellspacing="0" style="width: 100%; margin-bottom: 20px;">
    <thead>
        <tr>
            <th style="background-color: #1a73e8; font-weight: bold; color: #ffffff;">Ürün Adı</th>
            <th style="background-color: #1a73e8; font-weight: bold; color: #ffffff;">Fiyat</th>
            <th style="background-color: #1a73e8; font-weight: bold; color: #ffffff;">Adet</th>
            <th style="background-color: #1a73e8; font-weight: bold; color: #ffffff;">Toplam</th>
        </tr>
    </thead>
    <tbody>';


    foreach ($items as $item) {
        $html .= '<tr>
            <td>' . $item['name'] . '</td>
            <td>' . $item['price'] . '</td>
            <td>' . $item['quantity'] . '</td>
            <td>' . $item['total'] . '</td>
        </tr>';
    }

$html .= '</tbody>
    </table>';
$html .= '<div style="text-align: right; border-top: 2px solid #1a73e8; padding-top: 10px;"><h3 style="color: #1a73e8;"> Toplam Fiyat:' . $total_price . ' </h3></div>';

    // İçeriği PDF'ye yazdırın
        $pdf->writeHTML($html, true, false, true, false, '');
	
	function clean_tr_characters($string) {
    $tr_chars = array(
        'ç' => 'c', 'Ç' => 'C',
        'ğ' => 'g', 'Ğ' => 'G',
        'ı' => 'i', 'İ' => 'I',
        'ö' => 'o', 'Ö' => 'O',
        'ş' => 's', 'Ş' => 'S',
        'ü' => 'u', 'Ü' => 'U',
    );

    return strtr($string, $tr_chars);
}

    // Müşteri adında özel karakterleri ve boşlukları "_" ile değiştirin
    $customer_name_clean = clean_tr_characters($customer_name);
	$customer_name_clean = preg_replace('/[^A-Za-z0-9\-]/', '_', $customer_name_clean);

	
	
 //...
// PDF dosyasını sunucuda kaydedin
$pdf_file = time() . '_' . $customer_name_clean . '.pdf';
$pdf_full_path = __DIR__ . '/exports/' . $pdf_file;
$pdf->Output($pdf_full_path, 'F');



// PDF dosyasının yolu ve diğer bilgileri veritabanına kaydedin
$sql = "INSERT INTO calculations (customer_name, total_price, pdf_filename, prepared_by) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);



$stmt->bind_param("sdss", $customer_name, $total_price, $pdf_file, $prepared_by);
$stmt->execute();



//...
	
    // İşlem tamamlandığında kullanıcıyı bilgilendirin
    echo "<div class='alert alert-success'>Teklif PDF olarak kaydedildi ve veritabanına eklendi. <a href='exports/" . $pdf_file . "' download>PDF'yi indir</a></div>";
}
	else if ($export_format === 'xlsx') {
    // XLSX oluşturma ve kaydetme işlemini burada gerçekleştirin
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
		// Başlık satırını yazdır
    $sheet->setCellValue('A1', 'Ürün Adı');
    $sheet->setCellValue('B1', 'Fiyat');
    $sheet->setCellValue('C1', 'Adet');
    $sheet->setCellValue('D1', 'Toplam');

    // Ürünleri yazdır
    $row = 2;
    foreach ($items as $item) {
        $sheet->setCellValue('A' . $row, $item['name']);
        $sheet->setCellValue('B' . $row, $item['price']);
        $sheet->setCellValue('C' . $row, $item['quantity']);
        $sheet->setCellValue('D' . $row, $item['total']);
        $row++;
    }
		function clean_tr_characters($string) {
    $tr_chars = array(
        'ç' => 'c', 'Ç' => 'C',
        'ğ' => 'g', 'Ğ' => 'G',
        'ı' => 'i', 'İ' => 'I',
        'ö' => 'o', 'Ö' => 'O',
        'ş' => 's', 'Ş' => 'S',
        'ü' => 'u', 'Ü' => 'U',
    );

    return strtr($string, $tr_chars);
}

    // Müşteri adında özel karakterleri ve boşlukları "_" ile değiştirin
    $customer_name_clean = clean_tr_characters($customer_name);
	$customer_name_clean = preg_replace('/[^A-Za-z0-9\-]/', '_', $customer_name_clean);

    // Excel dosyasını sunucuda kaydedin
    $xlsx_file = time() . '_' . $customer_name_clean . '.xlsx';
    $xlsx_full_path = __DIR__ . '/exports/' . $xlsx_file;
    $writer = new Xlsx($spreadsheet);
    $writer->save($xlsx_full_path);

    // XLSX dosyasının yolu ve diğer bilgileri veritabanına kaydedin
    $sql = "INSERT INTO calculations (customer_name, total_price, pdf_filename, prepared_by) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdss", $customer_name, $total_price, $xlsx_file, $prepared_by);
    $stmt->execute();

    // İşlem tamamlandığında kullanıcıyı bilgilendirin
    echo "<div class='alert alert-success'>Teklif XLSX olarak kaydedildi ve veritabanına eklendi. <a href='exports/" . $xlsx_file . "' download>XLSX'yi indir</a></div>";
}
	}
?>

</body>
</html>


