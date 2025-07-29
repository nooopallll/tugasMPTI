<?php
include 'db_connect.php';
$d1 = (isset($_GET['d1']) ? date("Y-m-d", strtotime($_GET['d1'])) : date("Y-m-d"));
$d2 = (isset($_GET['d2']) ? date("Y-m-d", strtotime($_GET['d2'])) : date("Y-m-d"));
$data_range_label = $d1 == $d2 ? date("d M Y", strtotime($d1)) : date("d M Y", strtotime($d1)) . ' - ' . date("d M Y", strtotime($d2));

$laundry_data = [];
$total_lunas = 0;

// Logika PHP sudah benar: mengambil SEMUA pesanan pada rentang tanggal
$stmt = $conn->prepare("SELECT * FROM laundry_list WHERE date(date_created) BETWEEN ? AND ? ORDER BY date_created DESC");
$stmt->bind_param("ss", $d1, $d2);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $laundry_data[] = $row;
    if ($row['pay_status'] == 1) {
        $total_lunas += $row['total_amount'];
    }
}
?>

<style>
    /* Style umum (sudah bagus) */
    .card.filter-card, .card.report-card {
        border: none; border-radius: 0.75rem; box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }
    .card-header.page-header {
        background-color: #fff; border-bottom: 1px solid #e3e6f0; font-weight: 600; color: #0d6efd;
    }
    .summary-stat {
        background-color: #eaf2ff; border-left: 5px solid #0d6efd; padding: 20px; border-radius: 8px;
    }

    /* [BARU] Style untuk mengubah tabel laporan menjadi daftar kartu di mobile */
    .report-list-mobile { display: none; }
    .report-card-item {
        background: #fff; border: 1px solid #e9ecef; border-radius: 8px; padding: 15px; margin-bottom: 10px;
    }
    .report-card-header {
        display: flex; justify-content: space-between; align-items: center; font-weight: 600; margin-bottom: 10px;
    }
    .report-card-body .detail-item {
        display: flex; justify-content: space-between; align-items: center; font-size: 0.9rem; padding: 8px 0; border-bottom: 1px dashed #f1f1f1;
    }
    .report-card-body .detail-item:last-child { border-bottom: none; }
    .detail-item .label { color: #6c757d; }

    /* Media Query untuk Tampilan Cetak dan Mobile */
    @media (max-width: 991.98px) { /* Titik henti diubah ke Large (lg) agar lebih cepat berubah */
        .table-responsive { display: none; }
        .report-list-mobile { display: block; }
    }
    @media print {
        body * { visibility: hidden; }
        #sidebar, #topbar, .filter-card, .main-title, .btn, .dataTables_wrapper { display: none !important; }
        #report-section, #report-section * { visibility: visible; }
        #report-section { position: absolute; left: 0; top: 0; width: 100%; }
        .table { width: 100% !important; border-collapse: collapse; }
        .table th, .table td { border: 1px solid #dee2e6 !important; padding: 8px; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .print-header { display: block !important; }
        .badge { border: 1px solid #ccc; color: #000; }
    }
</style>

<div class="container-fluid">
    <div class="row mb-4 main-title">
        <div class="col-lg-12">
            <h2 class="fw-bold">Laporan Pesanan</h2>
            <p class="text-muted">Lihat dan cetak semua pesanan berdasarkan rentang tanggal.</p>
        </div>
    </div>

    <div class="card filter-card mb-4">
        <div class="card-header page-header"><i class="fas fa-filter me-2"></i> Filter Laporan</div>
        <div class="card-body">
            <div class="row gy-3 align-items-end">
                <div class="col-md-6 col-lg-4">
                    <label for="d1" class="form-label">Dari Tanggal</label>
                    <input type="date" class="form-control" name="d1" id="d1" value="<?php echo $d1; ?>">
                </div>
                <div class="col-md-6 col-lg-4">
                    <label for="d2" class="form-label">Sampai Tanggal</label>
                    <input type="date" class="form-control" name="d2" id="d2" value="<?php echo $d2; ?>">
                </div>
                <div class="col-lg-4">
                    <div class="d-grid gap-2 d-md-flex">
                        <button class="btn btn-primary w-100" type="button" id="filter"><i class="fas fa-search me-1"></i> Tampilkan</button>
                        <button class="btn btn-success w-100" type="button" id="print"><i class="fa fa-print me-1"></i> Cetak</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="report-section">
        <div class="card report-card">
            <div class="card-body">
                <div class="print-header text-center mb-4" style="display:none;">
                    <h4>Laporan Pesanan Laundry</h4>
                    <p>Periode: <strong><?php echo $data_range_label; ?></strong></p><hr>
                </div>
                <div class="summary-stat mb-4">
                    <h5>Total Pendapatan (Hanya dari yang Lunas)</h5>
                    <h2 class="fw-bold">Rp <?php echo number_format($total_lunas, 0, ',', '.'); ?></h2>
                    <small class="text-muted">Untuk periode <?php echo $data_range_label; ?></small>
                </div>

                <div class="table-responsive">
                    <table class='table table-hover' id="report-table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Nama Pelanggan</th>
                                <th class="text-center">Status Bayar</th>
                                <th class="text-end">Total Tagihan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($laundry_data)): foreach ($laundry_data as $row): ?>
                                <tr>
                                    <td><?php echo date("d M, Y", strtotime($row['date_created'])) ?></td>
                                    <td><?php echo htmlspecialchars(ucwords($row['customer_name'])) ?></td>
                                    <td class="text-center">
                                        <?php echo $row['pay_status'] == 1 ? '<span class="badge bg-success">Lunas</span>' : '<span class="badge bg-warning text-dark">Belum Lunas</span>'; ?>
                                    </td>
                                    <td class='text-end'>Rp <?php echo number_format($row['total_amount'], 0, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="report-list-mobile">
                    <?php if (!empty($laundry_data)): foreach ($laundry_data as $row): ?>
                    <div class="report-card-item">
                        <div class="report-card-header">
                            <span><?php echo htmlspecialchars(ucwords($row['customer_name'])); ?></span>
                            <?php echo $row['pay_status'] == 1 ? '<span class="badge bg-success">Lunas</span>' : '<span class="badge bg-warning text-dark">Belum Lunas</span>'; ?>
                        </div>
                        <div class="report-card-body">
                            <div class="detail-item">
                                <span class="label">Tanggal:</span>
                                <span class="value"><?php echo date('d M Y', strtotime($row['date_created'])); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="label">Total Tagihan:</span>
                                <span class="value fw-bold">Rp <?php echo number_format($row['total_amount'], 0, ',', '.'); ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; else: ?>
                    <div class="text-center text-muted p-4">Tidak ada data untuk rentang tanggal yang dipilih.</div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Inisialisasi DataTables
    $('#report-table').DataTable({
        "order": [],
        "language": {
            "search": "Cari:",
            "lengthMenu": "Tampilkan _MENU_ data",
            "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            "infoEmpty": "Menampilkan 0 data",
            "infoFiltered": "(difilter dari _MAX_ total data)",
            "zeroRecords": "Tidak ada data yang cocok",
            "paginate": { "next": "Berikutnya", "previous": "Sebelumnya" }
        }
    });

    $('#filter').click(function() {
        var d1 = $('#d1').val();
        var d2 = $('#d2').val();
        if (d1 && d2) {
            location.href = 'index.php?page=reports&d1=' + d1 + '&d2=' + d2;
        } else {
            alert('Silakan pilih rentang tanggal terlebih dahulu.');
        }
    });

    $('#print').click(function() {
        window.print();
    });
});
</script>