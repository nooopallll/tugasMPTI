<?php include 'db_connect.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-lg-12">
            <h2 class="fw-bold">Daftar Pesanan Laundry</h2>
            <p class="text-muted">Kelola semua pesanan laundry yang masuk, sedang diproses, dan selesai.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="card-title mb-0">Semua Pesanan</h5>
                    <button class="btn btn-primary btn-sm" type="button" id="new_laundry"><i class="fa fa-plus"></i> Tambah Pesanan</button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="laundry-list" style="width:100%">
                            <thead>
                                <tr>
                                    <th data-priority="2">Tanggal</th>
                                    <th data-priority="3">Antrian</th>
                                    <th data-priority="1">Nama Pelanggan</th>
                                    <th data-priority="2">Status</th>
                                    <th data-priority="1" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $list = $conn->query("SELECT * FROM laundry_list ORDER BY status ASC, FIELD(status, 0, 1, 2, 3), id DESC");
                                if ($list->num_rows > 0) :
                                    while ($row = $list->fetch_assoc()) :
                                ?>
                                        <tr>
                                            <td><?php echo date("d M, Y", strtotime($row['date_created'])) ?></td>
                                            <td class="text-center"><?php echo $row['queue'] ?></td>
                                            <td><?php echo ucwords($row['customer_name']) ?></td>
                                            <td class="text-center">
                                                <?php
                                                // [DIUBAH] Menggunakan kelas badge Bootstrap 5 (bg-*)
                                                switch ($row['status']) {
                                                    case 0:
                                                        echo '<span class="badge bg-secondary">Pending</span>';
                                                        break;
                                                    case 1:
                                                        echo '<span class="badge bg-primary">Diproses</span>';
                                                        break;
                                                    case 2:
                                                        echo '<span class="badge bg-info text-dark">Siap Diambil</span>';
                                                        break;
                                                    case 3:
                                                        echo '<span class="badge bg-success">Sudah Diambil</span>';
                                                        break;
                                                }
                                                ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-outline-primary btn-sm edit_laundry" data-id="<?php echo $row['id'] ?>" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-info btn-sm print_laundry" data-id="<?php echo $row['id'] ?>" title="Cetak Nota">
                                                        <i class="fas fa-print"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger btn-sm delete_laundry" data-id="<?php echo $row['id'] ?>" title="Hapus">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                <?php
                                    endwhile;
                                else :
                                ?>
                                    <tr>
                                        <td colspan="5" class="text-center">Belum ada data laundry. Silakan tambahkan data baru.</td>
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

<script>
$(document).ready(function() {
    // [DIUBAH] Inisialisasi DataTable dengan opsi responsive
    $('#laundry-list').dataTable({
        responsive: true // Cukup tambahkan baris ini!
    });

    // Event handlers (tidak ada perubahan di sini)
    $('#new_laundry').click(function() {
        uni_modal('Tambah Pesanan Baru', 'manage_laundry.php', 'mid-large');
    });

    $('#laundry-list').on('click', '.edit_laundry', function() {
        uni_modal('Edit Pesanan', 'manage_laundry.php?id=' + $(this).attr('data-id'), 'mid-large');
    });

    $('#laundry-list').on('click', '.print_laundry', function() {
        var id = $(this).attr('data-id');
        var nw = window.open("print_laundry.php?id=" + id, "_blank", "width=900,height=600");
        setTimeout(function() {
            nw.print();
            setTimeout(function() {
                nw.close();
            }, 500);
        }, 500);
    });

    $('#laundry-list').on('click', '.delete_laundry', function() {
        _conf("Anda yakin ingin menghapus data ini?", "delete_laundry", [$(this).attr('data-id')]);
    });
});

function delete_laundry($id) {
    start_load();
    $.ajax({
        url: 'ajax.php?action=delete_laundry',
        method: 'POST',
        data: { id: $id },
        success: function(resp) {
            if (resp == 1) {
                alert_toast("Data berhasil dihapus", 'success');
                setTimeout(function() {
                    location.reload();
                }, 1500);
            }
        },
        error: function(err) {
            console.log(err);
            alert_toast("Terjadi kesalahan", 'error');
            end_load();
        }
    });
}
</script>