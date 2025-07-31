<?php
include 'db_connect.php';

// Ambil daftar staff (type=2) untuk filter dropdown
$staff_list = $conn->query("SELECT id, name FROM users WHERE type = 2 ORDER BY name ASC");

// Tentukan periode tanggal default (bulan ini)
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');
$staff_id = isset($_GET['staff_id']) ? $_GET['staff_id'] : 'all';

// Query utama untuk mengambil data presensi yang sudah diproses
// Menggabungkan data 'in' dan 'out' menjadi satu baris per hari per user
$sql = "SELECT 
            DATE(p.waktu) as tanggal,
            p.user_id,
            u.name as nama_staff,
            MIN(CASE WHEN p.type = 'in' THEN TIME(p.waktu) END) as waktu_masuk,
            MAX(CASE WHEN p.type = 'out' THEN TIME(p.waktu) END) as waktu_pulang
        FROM 
            presensi p
        JOIN 
            users u ON u.id = p.user_id
        WHERE 
            DATE(p.waktu) BETWEEN ? AND ? ";

$params = [$start_date, $end_date];
$types = "ss";

if ($staff_id != 'all' && is_numeric($staff_id)) {
    $sql .= " AND p.user_id = ? ";
    $params[] = $staff_id;
    $types .= "i";
}

$sql .= " GROUP BY tanggal, p.user_id ORDER BY tanggal DESC, nama_staff ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<style>
    /* CSS khusus untuk menyembunyikan elemen saat dicetak */
    @media print {
        body * {
            visibility: hidden;
        }
        #printableArea, #printableArea * {
            visibility: visible;
        }
        #printableArea {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
        .no-print {
            display: none !important;
        }
    }
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header no-print">
                    <h4><i class="fa fa-calendar-check"></i> Laporan Presensi Staff</h4>
                </div>
                <div class="card-body">
                    <form method="GET" class="row g-3 align-items-end no-print">
                        <input type="hidden" name="page" value="reports_presensi">
                        <div class="col-md-3">
                            <label for="start_date" class="form-label">Dari Tanggal</label>
                            <input type="date" class="form-control" name="start_date" id="start_date" value="<?php echo $start_date; ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="end_date" class="form-label">Sampai Tanggal</label>
                            <input type="date" class="form-control" name="end_date" id="end_date" value="<?php echo $end_date; ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="staff_id" class="form-label">Pilih Staff</label>
                            <select name="staff_id" id="staff_id" class="form-select">
                                <option value="all">Semua Staff</option>
                                <?php while($row = $staff_list->fetch_assoc()): ?>
                                    <option value="<?php echo $row['id'] ?>" <?php echo ($staff_id == $row['id']) ? 'selected' : '' ?>>
                                        <?php echo htmlspecialchars($row['name']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-primary"><i class="fa fa-filter"></i> Tampilkan</button>
                            <button type="button" class="btn btn-success ms-2" onclick="window.print()"><i class="fa fa-print"></i> Cetak</button>
                        </div>
                    </form>
                    <hr class="no-print">
                    
                    <div id="printableArea">
                        <h3 class="text-center mb-4">Laporan Presensi</h3>
                        <p class="text-center">Periode: <?php echo date('d M Y', strtotime($start_date)) . ' - ' . date('d M Y', strtotime($end_date)); ?></p>
                        
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center">#</th>
                                        <th>Tanggal</th>
                                        <th>Nama Staff</th>
                                        <th class="text-center">Waktu Masuk</th>
                                        <th class="text-center">Waktu Pulang</th>
                                        <th class="text-center">Total Jam Kerja</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $i = 1;
                                    if ($result->num_rows > 0):
                                        while($row = $result->fetch_assoc()): 
                                            $durasi = ' - ';
                                            if (!empty($row['waktu_masuk']) && !empty($row['waktu_pulang'])) {
                                                $masuk = new DateTime($row['waktu_masuk']);
                                                $pulang = new DateTime($row['waktu_pulang']);
                                                $interval = $masuk->diff($pulang);
                                                $durasi = $interval->format('%h jam %i menit');
                                            }
                                    ?>
                                    <tr>
                                        <td class="text-center"><?php echo $i++ ?></td>
                                        <td><?php echo date('d M Y', strtotime($row['tanggal'])) ?></td>
                                        <td><?php echo htmlspecialchars($row['nama_staff']) ?></td>
                                        <td class="text-center"><?php echo $row['waktu_masuk'] ? $row['waktu_masuk'] : '<span class="badge bg-warning text-dark">Belum Absen</span>' ?></td>
                                        <td class="text-center"><?php echo $row['waktu_pulang'] ? $row['waktu_pulang'] : '<span class="badge bg-danger">Belum Pulang</span>' ?></td>
                                        <td class="text-center"><?php echo $durasi ?></td>
                                    </tr>
                                    <?php 
                                        endwhile; 
                                    else: 
                                    ?>
                                    <tr>
                                        <td colspan="6" class="text-center">Tidak ada data untuk periode yang dipilih.</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>