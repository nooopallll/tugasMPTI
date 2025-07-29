<?php include 'db_connect.php'; ?>

<!-- [DIHAPUS] Tag <head> dan <style> dipindahkan ke file layout utama. -->
<style>
    /* Tambahkan style ini ke file CSS utama Anda jika belum ada */
    .card.users-card {
        border: none;
        border-radius: 0.75rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }
    .card-header.page-header {
        background-color: #fff;
        border-bottom: 1px solid #e3e6f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .table thead th {
        background-color: #f8f9fc;
    }
</style>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-lg-12">
            <h2 class="fw-bold">Manajemen Pengguna</h2>
            <p class="text-muted">Tambah, lihat, atau ubah data pengguna sistem.</p>
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-12">
            <div class="card users-card">
                <div class="card-header page-header py-3">
                    <h5 class="m-0 fw-bold text-primary">Daftar Pengguna</h5>
                    <button class="btn btn-primary btn-sm" type="button" id="new_user"><i class="fa fa-plus"></i> Tambah Pengguna</button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <!-- [DIUBAH] Menambahkan ID dan style width untuk DataTables -->
                        <table class="table table-hover" id="user-list" style="width:100%">
                            <thead>
                                <tr>
                                    <!-- [BARU] Atribut data-priority untuk DataTables Responsive -->
                                    <th data-priority="3" class="text-center">#</th>
                                    <th data-priority="1">Nama</th>
                                    <th data-priority="2">Username</th>
                                    <th data-priority="1" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $i = 1;
                                    $users = $conn->query("SELECT * FROM users ORDER BY name ASC");
                                    while($row = $users->fetch_assoc()):
                                ?>
                                <tr>
                                    <td class="text-center"><?php echo $i++ ?></td>
                                    <td><?php echo ucwords($row['name']) ?></td>
                                    <td><?php echo $row['username'] ?></td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <!-- [DIUBAH] Tombol aksi menjadi ikon saja untuk tampilan lebih bersih -->
                                            <button class="btn btn-sm btn-outline-primary edit_user" type="button" data-id="<?php echo $row['id'] ?>" title="Edit Pengguna">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger delete_user" type="button" data-id="<?php echo $row['id'] ?>" title="Hapus Pengguna">
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
    // [DIUBAH] Inisialisasi DataTable dengan opsi responsive
    var table = $('#user-list').DataTable({
        responsive: true
    });

    $('#new_user').click(function() {
        uni_modal('Tambah Pengguna Baru', 'manage_user.php');
    });

    // [DIUBAH] Menggunakan event delegation agar tombol berfungsi di semua halaman tabel
    $('#user-list tbody').on('click', '.edit_user', function() {
        uni_modal('Edit Pengguna', 'manage_user.php?id=' + $(this).data('id'));
    });

    $('#user-list tbody').on('click', '.delete_user', function() {
        _conf("Anda yakin ingin menghapus pengguna ini?", "delete_user", [$(this).data('id')]);
    });
});

// Fungsi delete_user tetap sama
function delete_user($id) {
    start_load();
    $.ajax({
        url: 'ajax.php?action=delete_user',
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