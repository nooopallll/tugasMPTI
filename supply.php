<?php include('db_connect.php'); ?>

<!-- [DIHAPUS] Tag <head> yang tidak valid dihapus. Style sebaiknya ada di file CSS utama. -->
<style>
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
            <h2 class="fw-bold">Manajemen Stok & Kebutuhan</h2>
            <p class="text-muted">Kelola daftar kebutuhan dan stok untuk operasional laundry.</p>
        </div>
    </div>
    
    <!-- [DIUBAH] Menggunakan gy-4 untuk memberi jarak vertikal saat kolom bertumpuk di mobile -->
    <div class="row gy-4">
        <!-- Kolom Form -->
        <div class="col-lg-4">
            <form action="" id="manage-supply">
                <div class="card h-100">
                    <div class="card-header form-header">
                        <i class="fas fa-plus-circle me-2"></i>
                        <span id="form-title">Form Tambah Stok</span>
                    </div>
                    <div class="card-body">
                        <input type="hidden" name="id">
                        <div class="form-group">
                            <label class="form-label" for="supply-name">Nama Stok/Kebutuhan</label>
                            <textarea name="name" id="supply-name" cols="30" rows="3" class="form-control" required></textarea>
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
        
        <!-- Kolom Tabel -->
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header table-header">
                    <i class="fas fa-boxes me-2"></i> Daftar Stok
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <!-- [DIUBAH] Menambahkan ID untuk inisialisasi DataTables -->
                        <table class="table table-hover" id="supply-list-table" style="width:100%">
                            <thead>
                                <tr>
                                    <!-- [BARU] Atribut data-priority untuk DataTables Responsive -->
                                    <th data-priority="3" class="text-center">#</th>
                                    <th data-priority="1">Nama Stok</th>
                                    <th data-priority="1" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $i = 1;
                                $cats = $conn->query("SELECT * FROM supply_list ORDER BY name ASC");
                                while($row = $cats->fetch_assoc()):
                                ?>
                                <tr>
                                    <td class="text-center"><?php echo $i++ ?></td>
                                    <td><b><?php echo ucwords($row['name']) ?></b></td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <!-- [DIUBAH] Tombol aksi menjadi ikon saja -->
                                            <button class="btn btn-sm btn-outline-primary edit_supply" type="button" data-id="<?php echo $row['id'] ?>" data-name="<?php echo htmlspecialchars($row['name']) ?>" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger delete_supply" type="button" data-id="<?php echo $row['id'] ?>" title="Hapus">
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
    var table = $('#supply-list-table').DataTable({
        responsive: true
    });

    // Fungsi untuk mereset form
    function resetForm() {
        $('#manage-supply').get(0).reset();
        $('#manage-supply [name="id"]').val('');
        $('#form-title').text('Form Tambah Stok');
    }

    $('#cancel-edit').click(function() {
        resetForm();
    });

    $('#manage-supply').submit(function(e) {
        e.preventDefault();
        start_load();
        $.ajax({
            url: 'ajax.php?action=save_supply',
            data: new FormData($(this)[0]),
            cache: false,
            contentType: false,
            processData: false,
            method: 'POST',
            success: function(resp) {
                if (resp == 1) {
                    alert_toast("Data berhasil ditambahkan", 'success');
                } else if (resp == 2) {
                    alert_toast("Data berhasil diperbarui", 'success');
                }
                setTimeout(function() {
                    location.reload();
                }, 1500);
            }
        });
    });

    // [DIUBAH] Menggunakan event delegation untuk tombol yang dinamis
    $('#supply-list-table tbody').on('click', '.edit_supply', function() {
        var form = $('#manage-supply');
        form.find("[name='id']").val($(this).data('id'));
        form.find("[name='name']").val($(this).data('name'));
        $('#form-title').text('Form Edit Stok');
        // Scroll ke atas agar form terlihat
        $('html, body').animate({ scrollTop: 0 }, 'fast');
    });

    $('#supply-list-table tbody').on('click', '.delete_supply', function() {
        _conf("Anda yakin ingin menghapus data ini?", "delete_supply", [$(this).data('id')]);
    });
});

function delete_supply($id) {
    start_load();
    $.ajax({
        url: 'ajax.php?action=delete_supply',
        method: 'POST',
        data: {id: $id},
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