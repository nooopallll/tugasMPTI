<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Gaya CSS utama Anda sudah bagus */
        body { background-color: #f8f9fa; }
        .main-container { padding: 1.5rem; }
        .stat-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
            transition: all 0.3s ease-in-out;
            color: white;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.1);
        }
        .stat-card .card-body {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .stat-icon { font-size: 3rem; opacity: 0.5; }
        .main-chart-card, .transactions-card {
            border-radius: 10px;
            border: none;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
        }
        .table thead th {
            background-color: #e9ecef;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
        }

        /* [BARU] Style untuk mengubah tabel menjadi daftar kartu di mobile */
        .transaction-list-mobile {
            display: none; /* Sembunyikan di desktop */
        }
        .transaction-card {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
        }
        .transaction-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 600;
            margin-bottom: 10px;
        }
        .transaction-card-body .detail-item {
            display: flex;
            justify-content: space-between;
            font-size: 0.9rem;
            padding: 5px 0;
            border-bottom: 1px dashed #f1f1f1;
        }
        .transaction-card-body .detail-item:last-child {
            border-bottom: none;
        }
        .detail-item .label {
            color: #6c757d;
        }

        /* [BARU] Media Query untuk tampilan mobile */
        @media (max-width: 767.98px) {
            .main-title h2 {
                font-size: 1.5rem; /* Perkecil font judul utama di mobile */
            }
            .table-responsive {
                display: none; /* Sembunyikan tabel di mobile */
            }
            .transaction-list-mobile {
                display: block; /* Tampilkan daftar kartu di mobile */
            }
        }
    </style>
</head>
<body>

<div class="container-fluid main-container">
    <div class="row main-title">
        <div class="col-12">
            <h2 class="fw-bold">Selamat Datang, <?php echo $_SESSION['login_name']; ?>!</h2>
            <p class="text-muted">Berikut adalah ringkasan aktivitas laundry Anda.</p>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-lg-8 mb-4">
            <div class="card main-chart-card h-100">
                <div class="card-header bg-white border-0 py-3"><h5 class="card-title mb-0">Grafik Pendapatan 7 Hari Terakhir</h5></div>
                <div class="card-body"><canvas id="grafikPendapatan" style="min-height: 300px;"></canvas></div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="row">
                <div class="col-md-12 col-sm-6 mb-4">
                    <div class="card stat-card bg-success">
                        <div class="card-body">
                            <div>
                                <p class="mb-0"><strong>Total Profit Hari Ini</strong></p>
                                <h4 class="fw-bold mb-0">
                                    <?php
                                        include 'db_connect.php';
                                        $laundry_today = $conn->query("SELECT SUM(total_amount) as amount FROM laundry_list WHERE pay_status = 1 AND DATE(date_created) = CURDATE()");
                                        echo 'Rp ' . number_format($laundry_today->fetch_assoc()['amount'] ?? 0, 0, ',', '.');
                                    ?>
                                </h4>
                            </div>
                            <div class="stat-icon"><i class="fas fa-coins"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 col-sm-6 mb-4">
                    <div class="card stat-card bg-info">
                        <div class="card-body">
                            <div>
                                <p class="mb-0"><strong>Total Pelanggan Hari Ini</strong></p>
                                <h4 class="fw-bold mb-0">
                                    <?php
                                        $customers_today = $conn->query("SELECT COUNT(id) as `count` FROM laundry_list WHERE DATE(date_created) = CURDATE()");
                                        echo number_format($customers_today->fetch_assoc()['count'] ?? 0);
                                    ?>
                                </h4>
                            </div>
                            <div class="stat-icon"><i class="fas fa-users"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-12">
            <div class="card transactions-card">
                <div class="card-header bg-white border-0 py-3"><h5 class="card-title mb-0">10 Transaksi Terbaru</h5></div>
                <div class="card-body">
                    <?php
                        // Ambil data sekali dan simpan di array
                        $transactions = [];
                        $transaksi_query = $conn->query("SELECT * FROM laundry_list ORDER BY date_created DESC LIMIT 10");
                        if ($transaksi_query) {
                            while ($row = $transaksi_query->fetch_assoc()) {
                                $transactions[] = $row;
                            }
                        }
                    ?>
                    
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Pelanggan</th>
                                    <th>Total Bayar</th>
                                    <th>Status Bayar</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($transactions)): $no = 1; foreach ($transactions as $row): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                    <td>Rp <?php echo number_format($row['total_amount'], 0, ',', '.'); ?></td>
                                    <td><?php echo $row['pay_status'] == 1 ? '<span class="badge bg-success">Lunas</span>' : '<span class="badge bg-warning text-dark">Belum Lunas</span>'; ?></td>
                                    <td><?php echo date('d M Y, H:i', strtotime($row['date_created'])); ?></td>
                                </tr>
                                <?php endforeach; else: ?>
                                <tr><td colspan="5" class="text-center">Belum ada data transaksi.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="transaction-list-mobile">
                        <?php if (!empty($transactions)): foreach ($transactions as $row): ?>
                        <div class="transaction-card">
                            <div class="transaction-card-header">
                                <span><?php echo htmlspecialchars($row['customer_name']); ?></span>
                                <span><?php echo $row['pay_status'] == 1 ? '<span class="badge bg-success">Lunas</span>' : '<span class="badge bg-warning text-dark">Belum Lunas</span>'; ?></span>
                            </div>
                            <div class="transaction-card-body">
                                <div class="detail-item">
                                    <span class="label">Total Bayar:</span>
                                    <span class="value fw-bold">Rp <?php echo number_format($row['total_amount'], 0, ',', '.'); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="label">Tanggal:</span>
                                    <span class="value"><?php echo date('d M Y, H:i', strtotime($row['date_created'])); ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; else: ?>
                        <div class="text-center text-muted">Belum ada data transaksi.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('grafikPendapatan');
    if (!ctx) return;

    // [BARU] Fungsi untuk memformat angka menjadi format ringkas (juta/ribu)
    function formatRupiahSingkat(nilai) {
        if (nilai >= 1000000) {
            // Ubah ke format 'jt' dengan satu angka desimal
            return 'Rp ' + (nilai / 1000000).toLocaleString('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 1 }) + ' jt';
        }
        if (nilai >= 1000) {
            // Ubah ke format 'rb' tanpa desimal
            return 'Rp ' + (nilai / 1000).toLocaleString('id-ID') + ' rb';
        }
        return 'Rp ' + nilai.toLocaleString('id-ID');
    }

    fetch('data_grafik.php')
        .then(response => response.ok ? response.json() : Promise.reject('Gagal mengambil data'))
        .then(chartData => {
            const gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 300);
            gradient.addColorStop(0, 'rgba(54, 162, 235, 0.5)');
            gradient.addColorStop(1, 'rgba(54, 162, 235, 0)');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        label: 'Pendapatan',
                        data: chartData.data,
                        fill: true,
                        backgroundColor: gradient,
                        borderColor: 'rgb(54, 162, 235)',
                        pointBackgroundColor: 'rgb(54, 162, 235)',
                        pointBorderColor: '#fff',
                        pointHoverRadius: 7,
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: 'rgb(54, 162, 235)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                // [DIUBAH] Gunakan fungsi baru untuk format label sumbu Y
                                callback: function(value) {
                                    if (value === 0) return 'Rp 0'; // Jaga agar nilai 0 tetap 'Rp 0'
                                    return formatRupiahSingkat(value);
                                }
                            }
                        },
                        x: {
                            grid: { display: false }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#2c3e50',
                            titleFont: { size: 14, weight: 'bold' },
                            bodyFont: { size: 12 },
                            padding: 10,
                            cornerRadius: 5,
                            displayColors: false,
                            callbacks: {
                                // Tooltip tetap menampilkan nilai penuh dan presisi
                                label: function(context) {
                                    const value = context.parsed.y || 0;
                                    return 'Pendapatan: Rp ' + new Intl.NumberFormat('id-ID').format(value);
                                }
                            }
                        }
                    }
                }
            });
        })
        .catch(error => console.error('Error saat mengambil data grafik:', error));
});
</script>

</body>
</html>