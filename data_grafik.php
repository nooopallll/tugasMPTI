<?php
header('Content-Type: application/json');
include 'db_connect.php';

if ($conn->connect_error) {
    echo json_encode(['error' => 'Koneksi database gagal']);
    exit;
}

$data_pendapatan = [];
$label_tanggal = [];

for ($i = 6; $i >= 0; $i--) {
    $tanggal = date('Y-m-d', strtotime("-$i days"));
    $label_tanggal[] = date('d M', strtotime($tanggal));

    $query = $conn->query("SELECT SUM(total_amount) as amount FROM laundry_list WHERE pay_status = 1 AND DATE(date_created) = '$tanggal'");
    if (!$query) {
        echo json_encode(['error' => 'Query gagal']);
        exit;
    }

    $row = $query->fetch_assoc();
    $data_pendapatan[] = $row['amount'] ? floatval($row['amount']) : 0;
}

$response = [
    'labels' => $label_tanggal,
    'data' => $data_pendapatan,
];

$conn->close();
echo json_encode($response);
?>
