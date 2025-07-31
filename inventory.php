<?php include 'db_connect.php'; ?>

<style>
    /* Style yang sudah ada */
    .low-stock {
        color: #dc3545;
        font-weight: 600;
        background-color: rgba(220, 53, 69, 0.1);
    }
    .card-header.page-header {
        background-color: #fff;
        border-bottom: 1px solid #e3e6f0;
        font-weight: 600;
        color: #0d6efd;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .card {
        border: none;
        border-radius: 0.75rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }
    
    /* [BARU] Tambahkan style ini untuk memposisikan pagination di tengah */
    .dataTables_wrapper .dataTables_paginate {
        justify-content: center !important;
        padding-top: 1em; /* Memberi sedikit jarak dari tabel */
    }
</style>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-lg-12">
            <h2 class="fw-bold">Manajemen Inventaris</h2>
            <p class="text-muted">Pantau ringkasan stok dan riwayat keluar masuk barang.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-5 mb-4">
            <div class="card h-100">
                <div class="card-header page-header">
                    <span><i class="fas fa-boxes"></i> Ringkasan Stok</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th class="text-center">#</th>
                                    <th>Nama Stok</th>
                                    <th class="text-center">Tersedia</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $i = 1;
                                $sup_arr = array();
                                $supply_query = $conn->query("
                                    SELECT
                                        s.id, s.name,
                                        COALESCE(SUM(CASE WHEN i.stock_type = 1 THEN i.qty ELSE 0 END), 0) as total_in,
                                        COALESCE(SUM(CASE WHEN i.stock_type = 2 THEN i.qty ELSE 0 END), 0) as total_out
                                    FROM
                                        supply_list s
                                    LEFT JOIN
                                        inventory i ON s.id = i.supply_id
                                    GROUP BY
                                        s.id, s.name
                                    ORDER BY
                                        s.name ASC
                                ");
                                while ($row = $supply_query->fetch_assoc()) :
                                    $sup_arr[$row['id']] = $row['name'];
                                    $available = $row['total_in'] - $row['total_out'];
                                ?>
                                    <tr>
                                        <td class="text-center"><?php echo $i++ ?></td>
                                        <td><?php echo $row['name'] ?></td>
                                        <td class="text-center <?php if ($available <= 10) echo 'low-stock'; ?>">
                                            <?php echo $available ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-7 mb-4">
            <div class="card h-100">
                <div class="card-header page-header">
                    <span><i class="fas fa-history"></i> Riwayat Stok</span>
                    <button class="btn btn-primary btn-sm" type="button" id="manage-supply"><i class="fas fa-plus"></i> Kelola Stok</button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="inventory-history" style="width:100%">
                            <thead>
                                <tr>
                                    <th data-priority="2">Tanggal</th>
                                    <th data-priority="1">Nama Stok</th>
                                    <th data-priority="3" class="text-center">Jumlah</th>
                                    <th data-priority="2" class="text-center">Tipe</th>
                                    <th data-priority="1" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $inventory = $conn->query("SELECT * FROM inventory ORDER BY id DESC");
                                while ($row = $inventory->fetch_assoc()) :
                                ?>
                                    <tr>
                                        <td><?php echo date("d M Y", strtotime($row['date_created'])) ?></td>
                                        <td><?php echo isset($sup_arr[$row['supply_id']]) ? $sup_arr[$row['supply_id']] : 'N/A' ?></td>
                                        <td class="text-center"><?php echo $row['qty'] ?></td>
                                        <td class="text-center">
                                            <?php
                                            if ($row['stock_type'] == 1) :
                                                echo '<span class="badge bg-success">Masuk</span>';
                                            else :
                                                echo '<span class="badge bg-warning text-dark">Keluar</span>';
                                            endif;
                                            ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-primary edit_stock" data-id="<?php echo $row['id'] ?>"><i class="fa fa-edit"></i></button>
                                                <button type="button" class="btn btn-sm btn-outline-danger delete_stock" data-id="<?php echo $row['id'] ?>"><i class="fa fa-trash"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Inisialisasi DataTable dengan opsi baru
    $('#inventory-history').dataTable({
        responsive: true,
        "order": [], // Menonaktifkan pengurutan default
        language: {
            paginate: {
                previous: '<span aria-hidden="true">&laquo;</span>', // Simbol untuk "Previous"
                next:     '<span aria-hidden="true">&raquo;</span>'  // Simbol untuk "Next"
            }
        }
    });

    // Event handlers (tidak ada perubahan)
    $('#manage-supply').click(function() {
        uni_modal("Kelola Stok", "manage_inv.php");
    });

    $('#inventory-history').on('click', '.edit_stock', function() {
        uni_modal("Kelola Stok", "manage_inv.php?id=" + $(this).attr('data-id'));
    });

    $('#inventory-history').on('click', '.delete_stock', function() {
        _conf("Anda yakin ingin menghapus data ini?", "delete_stock", [$(this).attr('data-id')]);
    });
});

function delete_stock($id) {
    start_load();
    $.ajax({
        url: 'ajax.php?action=delete_inv',
        method: 'POST',
        data: { id: $id },
        success: function(resp) {
            if (resp == 1) {
                alert_toast("Data berhasil dihapus", 'success');
                setTimeout(function() {
                    location.reload();
                }, 1500);
            }
        }
    });
}
</script>