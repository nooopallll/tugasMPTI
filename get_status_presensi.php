<?php
// Selalu set header ke JSON di paling atas
header('Content-Type: application/json');

// Mulai session dan atur zona waktu
session_start();
date_default_timezone_set('Asia/Jakarta');

// 1. Cek Apakah User Sudah Login
if (!isset($_SESSION['login_id'])) {
    http_response_code(403); // Forbidden
    // Kirim pesan error dalam format JSON dan hentikan script
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak. Sesi Anda telah berakhir, silakan login kembali.']);
    exit;
}

// Jika sudah login, lanjutkan proses
include 'db_connect.php'; 

// Cek koneksi database setelah include
if ($conn->connect_error) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['status' => 'error', 'message' => 'Gagal terhubung ke database: ' . $conn->connect_error]);
    exit;
}

$user_id = $_SESSION['login_id'];
$today = date("Y-m-d");

// 2. Gunakan Prepared Statement untuk Keamanan
$stmt = $conn->prepare("SELECT type, TIME(waktu) as waktu_presensi FROM presensi WHERE user_id = ? AND DATE(waktu) = ? ORDER BY waktu ASC");

// Cek jika prepare statement gagal
if ($stmt === false) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Query database gagal disiapkan: ' . $conn->error]);
    exit;
}

$stmt->bind_param("is", $user_id, $today);
$stmt->execute();
$result = $stmt->get_result();

$check_in_time = null;
$check_out_time = null;

while ($row = $result->fetch_assoc()) {
    if ($row['type'] == 'in') {
        $check_in_time = $row['waktu_presensi'];
    }
    if ($row['type'] == 'out') {
        $check_out_time = $row['waktu_presensi'];
    }
}
$stmt->close();
$conn->close();

// 3. Tentukan status dan kirim respons JSON yang valid
if ($check_in_time && $check_out_time) {
    echo json_encode([
        'status' => 'completed', 
        'waktu_masuk' => $check_in_time, 
        'waktu_pulang' => $check_out_time
    ]);
} elseif ($check_in_time) {
    echo json_encode([
        'status' => 'checked_in', 
        'waktu_masuk' => $check_in_time
    ]);
} else {
    echo json_encode([
        'status' => 'not_checked_in'
    ]);
}