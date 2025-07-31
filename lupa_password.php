<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - Laundry Management System</title>
    
    <?php include 'header.php'; ?>

    <style>
        body {
            background-color: #f4f6f9;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        #reset-card {
            width: 100%;
            max-width: 400px;
        }
    </style>
</head>
<body>

<div id="reset-card" class="card shadow-lg">
    <div class="card-body">
        <h4 class="card-title text-center mb-4">Reset Password</h4>
        
        <form id="reset-password-form">
            <div class="alert alert-danger" style="display: none;" id="error-message"></div>
            
            <div class="form-group mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" id="username" name="username" class="form-control" required placeholder="Masukkan username Anda">
            </div>
            <div class="form-group mb-3">
                <label for="new_password" class="form-label">Password Baru</label>
                <input type="password" id="new_password" name="new_password" class="form-control" required placeholder="Masukkan password baru">
            </div>
            <div class="form-group mb-4">
                <label for="confirm_password" class="form-label">Konfirmasi Password Baru</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required placeholder="Ketik ulang password baru">
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Reset Password</button>
            </div>
        </form>

        <div class="text-center mt-3">
            <a href="login.php">Kembali ke Login</a>
        </div>
    </div>
</div>

<script src="assets/vendor/jquery/jquery.min.js"></script>
<script>
$(document).ready(function() {
    // Fungsi start_load dan end_load sederhana jika tidak ada di header
    window.start_load = function() {
        $('body').prepend('<div id="preloader2"></div>');
    }
    window.end_load = function() {
        $('#preloader2').fadeOut('fast', function() { $(this).remove(); });
    }

    // Tangani submit form dengan AJAX
    $('#reset-password-form').submit(function(e) {
        e.preventDefault();
        start_load();
        $('#error-message').hide();

        var new_pass = $('#new_password').val();
        var confirm_pass = $('#confirm_password').val();

        // Validasi sisi klien: pastikan password cocok
        if (new_pass !== confirm_pass) {
            $('#error-message').text('Password baru dan konfirmasi password tidak cocok.').show();
            end_load();
            return;
        }

        // Kirim data ke server
        $.ajax({
            url: 'ajax.php?action=reset_password',
            method: 'POST',
            data: $(this).serialize(),
            success: function(resp) {
                if (resp == 1) {
            
                    alert('Password berhasil diubah. Anda akan diarahkan ke halaman login.');
                    location.href = 'login.php';
                } 
                else if (resp == 2) {
            
                    $('#error-message').text('Username tidak ditemukan.').show();
                } 
        
                else if (resp == 3) {
                    $('#error-message').text('Gagal, hanya akun admin yang dapat mereset password.').show();
                } 
                else {
            
                    $('#error-message').text('Terjadi kesalahan: ' + resp).show();
                 }
                end_load();
            },
            error: function() {
                $('#error-message').text('Gagal terhubung ke server.').show();
                end_load();
            }
        });
    });
});
</script>

</body>
</html>