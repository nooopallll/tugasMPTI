<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Laundry Management System</title>

    <?php
        session_start();
        if(!isset($_SESSION['login_id']))
            header('location:login.php');
        include('./header.php');
    ?>

    <style>
        /* =================================
            CSS Grid Layout - Desktop First
            ================================= */
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif; /* Rekomendasi font yang lebih modern */
        }

        #main-container {
            height: 100vh;
            display: grid;
            grid-template-columns: 250px 1fr;
            grid-template-rows: 80px 1fr;
            grid-template-areas:
                "topbar topbar"
                "sidebar main";
            transition: grid-template-columns 0.3s ease-in-out; /* Animasi transisi */
        }

        #topbar, #sidebar, main#view-panel {
            margin: 0 !important;
            padding: 0 !important;
            top: unset !important;
            left: unset !important;
            position: static !important;
        }

        /* === TOPBAR === */
        #topbar {
            grid-area: topbar;
            position: relative !important;
            z-index: 1001;
            background-color: #2c3e50;
            height: 80px;
            color: #ffffff;
            box-shadow: 0 2px 4px rgba(0,0,0,.08);
            display: flex;
            align-items: center;
            padding: 0 1.5rem !important;
        }
        #topbar .navbar-brand,
        #topbar .nav-link {
            color: #ffffff !important;
        }

        /* === MAIN CONTENT === */
        main#view-panel {
            grid-area: main;
            position: relative !important;
            overflow-y: auto;
            padding: 2rem !important;
            background-color: #f4f6f9; /* Warna latar yang lebih lembut */
            color: #333; /* Warna teks default */
        }

        main#view-panel h2, main#view-panel h3,
        main#view-panel h4, main#view-panel h5 {
            color: #2c4964;
        }

        /* === SIDEBAR === */
        #sidebar {
            grid-area: sidebar;
            position: relative !important;
            z-index: 1002;
            background: #2c3e50;
            color: white;
            padding: 20px 0 !important;
            transition: transform 0.3s ease-in-out;
        }

        #sidebar .sidebar-list {
            padding: 0;
            margin: 0;
            list-style: none;
        }

        #sidebar .sidebar-list a {
            padding: 15px 20px !important;
            font-size: 1.1em;
            display: flex;
            align-items: center;
            color: #bdc3c7;
            text-decoration: none;
            border-left: 4px solid transparent;
            transition: all 0.2s ease-in-out;
        }
        #sidebar .sidebar-list a .icon-field {
            min-width: 40px;
            font-size: 1.2em;
            text-align: center;
        }
        #sidebar .sidebar-list a:hover {
            background: #34495e;
            color: #ffffff;
            border-left-color: #3498db;
        }
        #sidebar .sidebar-list a.active {
            background: rgba(52, 152, 219, 0.2);
            color: #ffffff;
            font-weight: 500;
            border-left: 4px solid #3498db;
        }

        /* [BARU] Tombol menu toggle untuk mobile */
        .mobile-menu-toggle {
            display: none; /* Sembunyikan di desktop */
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            margin-right: 15px;
            cursor: pointer;
        }

        /* =================================
            CSS RESPONSIVE - Untuk Mobile
            ================================= */
        @media (max-width: 768px) {
            .mobile-menu-toggle {
                display: block; /* Tampilkan di mobile */
            }

            #main-container {
                grid-template-columns: 1fr; /* Hanya 1 kolom */
                grid-template-areas:
                    "topbar"
                    "main";
            }

            #sidebar {
                position: fixed !important; /* Buat jadi overlay */
                top: 0 !important;
                left: 0 !important;
                height: 100vh;
                width: 250px;
                transform: translateX(-100%); /* Sembunyikan di luar layar */
                box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            }

            /* [BARU] Class untuk menampilkan sidebar */
            #sidebar.visible {
                transform: translateX(0);
            }
            
            /* [BARU] Overlay gelap di belakang sidebar saat aktif */
            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.5);
                z-index: 1001;
            }
            
            .sidebar-overlay.visible {
                display: block;
            }

            main#view-panel {
            padding: 1rem !important; /* Mengurangi jarak samping agar konten lebih penuh */
        }
        }

        .card {
        border: none;
        border-radius: 0.75rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }
    </style>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap5.min.css">
</head>

<body>
    <div class="sidebar-overlay" id="sidebar-overlay"></div>

    <div id="main-container">
        <?php
        // Pastikan Anda menambahkan tombol toggle di dalam file 'topbar.php'
        // Contoh penambahan di 'topbar.php':
        /*
        <nav class="navbar navbar-expand-lg navbar-dark">
            <button class="mobile-menu-toggle" id="mobile-menu-toggle">
                <i class="fas fa-bars"></i> </button>
            <a class="navbar-brand" href="#">Laundry System</a>
            ... sisa kode topbar ...
        </nav>
        */
        ?>
        <?php include 'topbar.php' ?>
        <?php include 'navbar.php' // ini adalah sidebar Anda ?>

        <main id="view-panel">
            <?php $page = isset($_GET['page']) ? $_GET['page'] :'home'; ?>
            <?php include $page.'.php' ?>
        </main>
    </div>

    <div class="toast" id="alert_toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-body text-white"></div>
    </div>
    <div id="preloader"></div>
    <a href="#" class="back-to-top"><i class="icofont-simple-up"></i></a>

    <div class="modal fade" id="uni_modal" role='dialog'>
    <div class="modal-dialog modal-dialog-centered modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id='submit' onclick="$('#uni_modal form').submit()">Simpan</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            </div>
        </div>
    </div>
</div>
    <div class="modal fade" id="uni_modal" role='dialog'>
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title"></h5></div>
                <div class="modal-body"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id='submit' onclick="$('#uni_modal form').submit()">Simpan</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap5.min.js"></script>
    </body>

<script>
    // Script lainnya tetap sama
    window.start_load = function(){
        $('body').prepend('<div id="preloader2"></div>');
    }
    window.end_load = function(){
        $('#preloader2').fadeOut('fast', function() {
            $(this).remove();
        });
    }
    window.uni_modal = function($title = '', $url = '', $size = "") {
    start_load();
    $.ajax({
        url: $url,
        error: err => {
            console.log(err);
            alert("Terjadi sebuah kesalahan");
        },
        success: function(resp) {
            if (resp) {
                $('#uni_modal .modal-title').html($title);
                $('#uni_modal .modal-body').html(resp);

                // [DIUBAH] Cara yang lebih aman untuk mengubah ukuran modal
                // Ini tidak akan menghapus kelas penting seperti 'modal-dialog-centered'
                var modalDialog = $('#uni_modal .modal-dialog');
                modalDialog.removeClass('modal-sm modal-lg modal-xl'); // Hapus hanya kelas ukuran

                // Tambahkan kelas ukuran baru jika ada
                if ($size === 'large' || $size === 'mid-large') {
                    modalDialog.addClass('modal-lg');
                } else if ($size === 'small') {
                    modalDialog.addClass('modal-sm');
                } else if ($size === 'xlarge') {
                    modalDialog.addClass('modal-xl');
                }
                // Jika tidak ada size, akan menggunakan ukuran default (modal-md) dari HTML

                var myModal = new bootstrap.Modal(document.getElementById('uni_modal'));
                myModal.show();
                end_load();
            }
        }
    });
  }
  
    window._conf = function($msg = '', $func = '', $params = []){
        $('#confirm_modal #confirm').attr('onclick', $func + "(" + $params.join(',') + ")");
        $('#confirm_modal .modal-body').html($msg);
        var myModal = new bootstrap.Modal(document.getElementById('confirm_modal'));
        myModal.show();
    }
    window.alert_toast= function($msg = 'TEST', $bg = 'success'){
        $('#alert_toast').removeClass('bg-success bg-danger bg-info bg-warning');
        if($bg == 'success') $('#alert_toast').addClass('bg-success');
        if($bg == 'danger') $('#alert_toast').addClass('bg-danger');
        if($bg == 'info') $('#alert_toast').addClass('bg-info');
        if($bg == 'warning') $('#alert_toast').addClass('bg-warning');
        $('#alert_toast .toast-body').html($msg);
        var myToast = new bootstrap.Toast(document.getElementById('alert_toast'));
        myToast.show();
    }
    $(document).ready(function(){
        $('#preloader').fadeOut('fast', function() {
            $(this).remove();
        });

        // [BARU] Script untuk fungsionalitas menu mobile
        const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebar-overlay');

        if(mobileMenuToggle) {
             mobileMenuToggle.addEventListener('click', function() {
                sidebar.classList.toggle('visible');
                sidebarOverlay.classList.toggle('visible');
            });
        }
       
        if(sidebarOverlay) {
             sidebarOverlay.addEventListener('click', function() {
                sidebar.classList.remove('visible');
                this.classList.remove('visible');
            });
        }
    });
</script>
</html>