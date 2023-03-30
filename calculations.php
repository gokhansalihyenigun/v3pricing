<?php
include 'config.php';

$sql = "SELECT * FROM calculations";
$result = $conn->query($sql);
$calculations = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calculations</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
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
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mt-4 mb-4">Calculations</h1>
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer Name</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Prepared By</th>
                    <th>PDF/XLSX Download</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($calculations as $calculation) { ?>
                    <tr>
                        <td><?php echo $calculation['id']; ?></td>
                        <td><?php echo $calculation['customer_name']; ?></td>
                        <td><?php echo date('Y-m-d', strtotime($calculation['created_at'])); ?></td>
                        <td><?php echo date('H:i:s', strtotime($calculation['created_at'])); ?></td>
                        <td><?php echo $calculation['prepared_by']; ?></td>
                        <td>
                            <a href="exports/<?php echo $calculation['pdf_filename']; ?>" class="btn btn-primary" download>Download</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</body>
</html>