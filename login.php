<?php
    // Logika sesi PHP Anda sudah benar
    session_start();
    if(isset($_SESSION['login_id'])) {
        header("location:index.php?page=home");
        exit();
    }
    include('./header.php');
    include('./db_connect.php');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Login | Sistem Manajemen Laundry</title>
    
    <style>
        /* === Layout & Font Utama === */
        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background-color: #f4f6f9;
        }

        main#main {
            display: flex;
            height: 100%;
            width: 100%;
            flex-direction: column; /* Default: tumpuk vertikal untuk mobile */
        }

        /* === Bagian Branding/Logo === */
        #login-left {
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #6ab1d7, #33d9de);
            padding: 40px 20px;
        }
        
        .logo { text-align: center; }

        .logo img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: white;
            padding: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease-in-out; /* Animasi halus */
        }

        .logo h1 {
            color: white;
            font-weight: 700;
            margin-top: 15px;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.2);
            transition: all 0.3s ease-in-out; /* Animasi halus */
        }

        /* === Bagian Form Login === */
        #login-right {
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            padding: 40px 20px;
            flex: 1; /* Ambil sisa ruang vertikal di mobile */
        }

        #login-right .card {
            width: 100%;
            max-width: 400px;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border-radius: 10px;
        }

        .card-body { padding: 2.5rem; }
        .form-group { margin-bottom: 1.5rem; }
        .btn-primary {
            background-color: #0d6efd; border: none; padding: 12px;
            font-weight: 600; transition: background-color 0.2s;
        }
        .btn-primary:hover { background-color: #0b5ed7; }

        /* =================================
           PERBAIKAN UNTUK MOBILE & DESKTOP
           ================================= */

        /* === Tampilan Desktop (Layar 768px ke atas) === */
        @media (min-width: 768px) {
            main#main {
                flex-direction: row; /* Tampilan berdampingan di desktop */
            }

            #login-left {
                flex-basis: 55%; /* Lebar 55% di desktop */
            }

            #login-right {
                flex-basis: 45%; /* Lebar 45% di desktop */
                flex-grow: 0; /* Jangan memanjang lebih dari basisnya */
            }
        }

        /* === [INI PERBAIKANNYA] Tampilan Mobile (Layar di bawah 768px) === */
        @media (max-width: 767.98px) {
    #login-left {
        /* Perkecil area biru di atas */
        flex-basis: 200px;
        flex-grow: 0;
        padding: 20px;
    }

    .logo img {
        /* Perkecil logo di mobile */
        width: 80px;
        height: 80px;
        padding: 10px;
    }

    .logo h1 {
        /* Perkecil teks logo di mobile */
        font-size: 1.5rem;
        margin-top: 10px;
    }
    
    #login-right {
        flex: 1;
        /* [DIUBAH] Posisikan form sedikit lebih ke atas */
        align-items: flex-start; /* Alih-alih 'center', ratakan ke atas */
        padding-top: 3rem;      /* Beri jarak dari area biru */
    }
}
    </style>
</head>

<body>
    <main id="main">
        <div id="login-left">
            <div class="logo">
                <img src="assets/img/logoaes.png" alt="Logo Laundry">
                <h1>Manajemen Laundry</h1>
            </div>
        </div>

        <div id="login-right">
            <div class="card shadow-lg">
                <div class="card-body">
                    <h4 class="card-title text-center mb-4">Login</h4>
                    <form id="login-form">
                        <div class="form-group">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" id="username" name="username" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" id="password" name="password" class="form-control" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Login</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Skrip AJAX Anda sudah bagus dan tidak perlu diubah
        $('#login-form').submit(function(e) {
            e.preventDefault();
            const $btn = $(this).find('button[type="submit"]');
            
            $btn.attr('disabled', true).html('Memproses...');

            if ($(this).find('.alert-danger').length > 0) {
                $(this).find('.alert-danger').remove();
            }

            $.ajax({
                url: 'ajax.php?action=login',
                method: 'POST',
                data: $(this).serialize(),
                error: err => {
                    console.log(err);
                    $btn.removeAttr('disabled').html('Login');
                },
                success: function(resp) {
                    if (resp == 1) {
                        location.href = 'index.php?page=home';
                    } else {
                        $('#login-form').prepend('<div class="alert alert-danger mt-3">Username atau password salah.</div>');
                        $btn.removeAttr('disabled').html('Login');
                    }
                }
            });
        });
    </script>
</body>
</html>