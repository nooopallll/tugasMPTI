<?php include 'db_connect.php'; ?>

<style>
    /* Style yang sudah ada */
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

    /* [BARU] Style untuk memposisikan pagination di tengah */
    .dataTables_wrapper .dataTables_paginate {
        justify-content: center !important;
        padding-top: 1em; /* Memberi sedikit jarak dari tabel */
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
                        <table class="table table-hover" id="user-list" style="width:100%">
                            <thead>
                                <tr>
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
    // [DIUBAH] Inisialisasi DataTable dengan opsi pagination baru
    var table = $('#user-list').DataTable({
        responsive: true,
        language: {
            paginate: {
                previous: '<span aria-hidden="true">&laquo;</span>', // Simbol untuk "Previous"
                next:     '<span aria-hidden="true">&raquo;</span>'  // Simbol untuk "Next"
            }
        }
    });

    $('#new_user').click(function() {
        uni_modal('Tambah Pengguna Baru', 'manage_user.php');
    });

    // Menggunakan event delegation agar tombol berfungsi di semua halaman tabel
    $('#user-list tbody').on('click', '.edit_user', function() {
        uni_modal('Edit Pengguna', 'manage_user.php?id=' + $(this).data('id'));
    });

    $('#user-list tbody').on('click', '.delete_user', function() {
        _conf("Anda yakin ingin menghapus pengguna ini?", "delete_user", [$(this).data('id')]);
    });
});

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