<?php include('db_connect.php'); ?>

<style>
    /* Style ini bisa dipindahkan ke file CSS utama Anda */
    .card {
        border: none;
        border-radius: 0.75rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }
    .card-header.form-header, .card-header.table-header {
        background-color: #fff;
        border-bottom: 1px solid #e3e6f0;
        font-weight: 600;
        color: #0d6efd;
    }
    .table thead th {
        background-color: #f8f9fc;
    }
</style>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-lg-12">
            <h2 class="fw-bold">Manajemen Kategori Laundry</h2>
            <p class="text-muted">Tambah, lihat, atau ubah kategori dan harga layanan laundry.</p>
        </div>
    </div>
    
    <div class="row gy-4">
        <div class="col-lg-4">
            <form action="" id="manage-category">
                <div class="card h-100">
                    <div class="card-header form-header">
                        <i class="fas fa-plus-circle me-2"></i>
                        <span id="form-title">Form Tambah Kategori</span>
                    </div>
                    <div class="card-body">
                        <input type="hidden" name="id">
                        <div class="mb-3">
                            <label for="category-name" class="form-label">Nama Kategori</label>
                            <input type="text" id="category-name" name="name" class="form-control" required>
                        </div>
                        <div>
                            <label for="category-price" class="form-label">Harga per Kg</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" id="category-price" class="form-control text-end" min="0" step="any" name="price" required>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-white">
                        <div class="d-flex justify-content-end">
                            <button class="btn btn-primary" type="submit"><i class="fas fa-save me-1"></i> Simpan</button>
                            <button class="btn btn-secondary ms-2" type="button" id="cancel-edit"><i class="fas fa-times me-1"></i> Batal</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header table-header">
                   <i class="fas fa-list me-2"></i> Daftar Kategori
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="category-list-table" style="width:100%">
                            <thead>
                                <tr>
                                    <th data-priority="3" class="text-center">#</th>
                                    <th data-priority="1">Nama Kategori</th>
                                    <th data-priority="2" class="text-end">Harga per Kg</th>
                                    <th data-priority="1" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $i = 1;
                                $cats = $conn->query("SELECT * FROM laundry_categories order by name asc");
                                while($row = $cats->fetch_assoc()):
                                ?>
                                <tr>
                                    <td class="text-center"><?php echo $i++ ?></td>
                                    <td><b><?php echo ucwords($row['name']) ?></b></td>
                                    <td class="text-end"><b><?php echo 'Rp ' . number_format($row['price'], 0, ',', '.') ?></b></td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-outline-primary edit_cat" type="button" data-id="<?php echo $row['id'] ?>" data-name="<?php echo htmlspecialchars($row['name']) ?>" data-price="<?php echo $row['price'] ?>" title="Edit Kategori">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger delete_cat" type="button" data-id="<?php echo $row['id'] ?>" title="Hapus Kategori">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
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
    // [BARU] Inisialisasi DataTable dengan opsi responsive
    $('#category-list-table').DataTable({
        responsive: true,
        "order": [],
        "language": {
            "search": "Cari:",
            "lengthMenu": "Tampilkan _MENU_ data",
            "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            "zeroRecords": "Tidak ada data yang cocok",
            "paginate": { "next": "Berikutnya", "previous": "Sebelumnya" }
        }
    });

    // Fungsi untuk mereset form ke keadaan awal
    function resetForm() {
        $('#manage-category').get(0).reset();
        $('#manage-category [name="id"]').val('');
        $('#form-title').html('<i class="fas fa-plus-circle me-2"></i> Form Tambah Kategori');
    }

    // Event handler untuk tombol Batal
    $('#cancel-edit').click(function() {
        resetForm();
    });

    // Pengiriman form
    $('#manage-category').submit(function(e) {
        e.preventDefault();
        start_load();
        $.ajax({
            url: 'ajax.php?action=save_category',
            data: new FormData($(this)[0]),
            cache: false, contentType: false, processData: false, method: 'POST',
            success: function(resp) {
                if (resp == 1) {
                    alert_toast("Data berhasil ditambahkan", 'success');
                } else if (resp == 2) {
                    alert_toast("Data berhasil diperbarui", 'success');
                }
                setTimeout(() => location.reload(), 1500);
            }
        });
    });

    // [DIUBAH] Menggunakan event delegation untuk tombol yang dinamis di dalam tabel
    $('#category-list-table tbody').on('click', '.edit_cat', function() {
        var cat = $('#manage-category');
        cat.find("[name='id']").val($(this).data('id'));
        cat.find("[name='name']").val($(this).data('name'));
        cat.find("[name='price']").val($(this).data('price'));
        $('#form-title').html('<i class="fas fa-edit me-2"></i> Form Edit Kategori');
        $('html, body').animate({ scrollTop: 0 }, 'fast');
    });

    $('#category-list-table tbody').on('click', '.delete_cat', function() {
        _conf("Anda yakin ingin menghapus kategori ini?", "delete_cat", [$(this).data('id')]);
    });
});

// Fungsi hapus (tidak ada perubahan)
function delete_cat($id) {
    start_load();
    $.ajax({
        url: 'ajax.php?action=delete_category',
        method: 'POST', data: {id: $id},
        success: function(resp) {
            if (resp == 1) {
                alert_toast("Data berhasil dihapus", 'success');
                setTimeout(() => location.reload(), 1500);
            }
        }
    });
}
</script>