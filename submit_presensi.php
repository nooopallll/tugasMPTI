<?php
// Selalu mulai dengan include dan pengaturan dasar
include 'db_connect.php'; 
date_default_timezone_set('Asia/Jakarta');
session_start();

// 1. Validasi Sesi Pengguna
if (!isset($_SESSION['login_id'])) {
    http_response_code(403); // Forbidden
    echo "Akses ditolak. Anda belum login atau sesi Anda telah berakhir.";
    exit;
}

// 2. [PENTING] Ambil Pengaturan Presensi dari Database
$settings_qry = $conn->query("SELECT office_latitude, office_longitude, attendance_radius FROM system_settings WHERE id = 1 LIMIT 1");
if ($settings_qry->num_rows > 0) {
    $settings = $settings_qry->fetch_assoc();
    $office_lat = floatval($settings['office_latitude']);
    $office_lon = floatval($settings['office_longitude']);
    $batas_jarak = intval($settings['attendance_radius']);
} else {
    // Beri pesan error jika admin belum mengatur lokasi di halaman Pengaturan
    http_response_code(500); // Internal Server Error
    echo "Pengaturan presensi belum di-set oleh admin.";
    exit;
}

// 3. Validasi Input dari Frontend
if (!isset($_POST['lat']) || !isset($_POST['lon']) || !isset($_POST['type'])) {
    http_response_code(400); // Bad Request
    echo "Data tidak lengkap (lokasi atau tipe presensi).";
    exit;
}

// Ambil data dari POST
$user_id = $_SESSION['login_id'];
$lat = floatval($_POST['lat']);
$lon = floatval($_POST['lon']);
$type = $_POST['type']; // 'in' atau 'out'
$today = date("Y-m-d");

// 4. Fungsi Perhitungan Jarak (Haversine Formula)
function distance($lat1, $lon1, $lat2, $lon2) {
    $earth_radius = 6371; 
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    return $earth_radius * $c * 1000; // Hasil dalam meter
}

// 5. Validasi Jarak Pengguna dari Kantor
$jarak = distance($lat, $lon, $office_lat, $office_lon);
if ($jarak > $batas_jarak) {
    http_response_code(400); 
    echo "Gagal presensi. Jarak Anda dari kantor sekitar " . round($jarak) . " meter, melebihi batas " . $batas_jarak . " meter.";
    exit;
}

// 6. Logika Mencegah Presensi Ganda
$stmt_check = $conn->prepare("SELECT COUNT(*) as count FROM presensi WHERE user_id = ? AND DATE(waktu) = ? AND type = ?");
$stmt_check->bind_param("iss", $user_id, $today, $type);
$stmt_check->execute();
$has_attended = $stmt_check->get_result()->fetch_assoc()['count'] > 0;
$stmt_check->close();

if ($type == 'in' && $has_attended) {
    http_response_code(400);
    echo "Anda sudah melakukan Check In hari ini.";
    exit;
}
if ($type == 'out' && $has_attended) {
    http_response_code(400);
    echo "Anda sudah melakukan Check Out hari ini.";
    exit;
}
// Tambahan: Pastikan pengguna sudah check-in sebelum bisa check-out
if ($type == 'out') {
    $stmt_check_in = $conn->prepare("SELECT COUNT(*) as count FROM presensi WHERE user_id = ? AND DATE(waktu) = ? AND type = 'in'");
    $stmt_check_in->bind_param("is", $user_id, $today);
    $stmt_check_in->execute();
    $has_checked_in = $stmt_check_in->get_result()->fetch_assoc()['count'] > 0;
    $stmt_check_in->close();

    if (!$has_checked_in) {
        http_response_code(400);
        echo "Anda tidak bisa Check Out karena belum melakukan Check In hari ini.";
        exit;
    }
}


// 7. Jika semua validasi lolos, masukkan data ke database
$waktu = date("Y-m-d H:i:s");
$insert_stmt = $conn->prepare("INSERT INTO presensi (user_id, waktu, latitude, longitude, type) VALUES (?, ?, ?, ?, ?)");
$insert_stmt->bind_param("isdds", $user_id, $waktu, $lat, $lon, $type);

if ($insert_stmt->execute()) {
    $action_text = ($type == 'in') ? 'Check In' : 'Check Out';
    echo "{$action_text} berhasil dicatat pada pukul " . date("H:i:s") . ".";
} else {
    http_response_code(500); 
    echo "Gagal menyimpan data presensi. Error: " . $insert_stmt->error;
}
$insert_stmt->close();
$conn->close();
?>