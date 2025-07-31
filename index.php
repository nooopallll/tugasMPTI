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
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap5.min.css">

    <style>
        /* Gaya CSS Anda tetap sama, bisa juga dipindah ke file .css eksternal */
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background-color: #f4f6f9;
        }

        #main-container {
            height: 100vh;
            display: grid;
            grid-template-columns: 250px 1fr;
            grid-template-rows: 80px 1fr;
            grid-template-areas:
                "topbar topbar"
                "sidebar main";
            transition: grid-template-columns 0.3s ease-in-out;
        }

        #topbar, #sidebar, main#view-panel {
            margin: 0 !important;
            padding: 0 !important;
            top: unset !important;
            left: unset !important;
            position: static !important;
        }

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
        
        main#view-panel {
            grid-area: main;
            position: relative !important;
            overflow-y: auto;
            padding: 2rem !important;
            color: #333;
        }
        main#view-panel h2, main#view-panel h3,
        main#view-panel h4, main#view-panel h5 {
            color: #2c4964;
        }
        
        #sidebar {
            grid-area: sidebar;
            position: relative !important;
            z-index: 1002;
            background: #2c3e50;
            color: white;
            padding: 20px 0 !important;
            transition: transform 0.3s ease-in-out;
        }
        #sidebar .sidebar-list { padding: 0; margin: 0; list-style: none; }
        #sidebar .sidebar-list a { padding: 15px 20px !important; font-size: 1.1em; display: flex; align-items: center; color: #bdc3c7; text-decoration: none; border-left: 4px solid transparent; transition: all 0.2s ease-in-out; }
        #sidebar .sidebar-list a .icon-field { min-width: 40px; font-size: 1.2em; text-align: center; }
        #sidebar .sidebar-list a:hover { background: #34495e; color: #ffffff; border-left-color: #3498db; }
        #sidebar .sidebar-list a.active { background: rgba(52, 152, 219, 0.2); color: #ffffff; font-weight: 500; border-left: 4px solid #3498db; }

        .mobile-menu-toggle { display: none; background: none; border: none; color: white; font-size: 24px; margin-right: 15px; cursor: pointer; }

        @media (max-width: 768px) {
            .mobile-menu-toggle { display: block; }
            #main-container { grid-template-columns: 1fr; grid-template-areas: "topbar" "main"; }
            #sidebar { position: fixed !important; top: 0 !important; left: 0 !important; height: 100vh; width: 250px; transform: translateX(-100%); box-shadow: 2px 0 5px rgba(0,0,0,0.1); }
            #sidebar.visible { transform: translateX(0); }
            .sidebar-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1001; }
            .sidebar-overlay.visible { display: block; }
            main#view-panel { padding: 1rem !important; }
        }
        
        .card { border: none; border-radius: 0.75rem; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
    </style>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
</head>
<body>
    <div class="sidebar-overlay" id="sidebar-overlay"></div>

    <div id="main-container">
        <?php include 'topbar.php' ?>
        <?php include 'navbar.php' // Ini adalah sidebar Anda ?>

        <main id="view-panel">
            <?php $page = isset($_GET['page']) ? $_GET['page'] : 'home'; ?>
            <?php include $page . '.php' ?>
        </main>
    </div>

    <div class="toast" id="alert_toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-body text-white"></div>
    </div>

    <div class="modal fade" id="uni_modal" role='dialog'>
        <div class="modal-dialog modal-dialog-centered modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id='submit' onclick="$('#uni_modal form').submit()">Simpan</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="confirm_modal" role='dialog'>
        <div class="modal-dialog modal-dialog-centered modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Konfirmasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id='confirm' onclick="">Lanjutkan</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                </div>
            </div>
        </div>
    </div>

    <div id="preloader"></div>
    <a href="#" class="back-to-top"><i class="icofont-simple-up"></i></a>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap5.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    // Global Functions (Struktur tidak diubah)
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
                end_load();
            },
            success: function(resp) {
                if (resp) {
                    $('#uni_modal .modal-title').html($title);
                    $('#uni_modal .modal-body').html(resp);
                    var modalDialog = $('#uni_modal .modal-dialog');
                    modalDialog.removeClass('modal-sm modal-lg modal-xl');
                    if ($size) {
                       modalDialog.addClass($size.replace('-large','-lg'));
                    }
                    var myModal = new bootstrap.Modal(document.getElementById('uni_modal'));
                    myModal.show();
                    end_load();
                }
            }
        });
    }
    window._conf = function($msg = '', $func = '', $params = []){
        var stringParams = $params.map(p => typeof p === 'string' ? `'${p}'` : p);
        $('#confirm_modal #confirm').attr('onclick', $func + "(" + stringParams.join(',') + ")");
        $('#confirm_modal .modal-body').html($msg);
        var myModal = new bootstrap.Modal(document.getElementById('confirm_modal'));
        myModal.show();
    }
    window.alert_toast = function($msg = 'TEST', $bg = 'success'){
    const toastEl = document.getElementById('alert_toast');
    if (!toastEl) {
        console.error('Elemen #alert_toast tidak ditemukan.');
        return;
    }

    // Mengatur warna background
    toastEl.classList.remove('bg-success', 'bg-danger', 'bg-info', 'bg-warning');
    if($bg == 'danger') {
        toastEl.classList.add('bg-danger');
    } else {
        toastEl.classList.add('bg-' + $bg);
    }
    
    // Mengisi pesan
    toastEl.querySelector('.toast-body').innerHTML = $msg;
    
    // Menggunakan metode Bootstrap 5 yang lebih aman untuk menampilkan toast
    const toast = bootstrap.Toast.getOrCreateInstance(toastEl);
    toast.show();
}
    
    // Document Ready
    $(document).ready(function(){
        // Preloader
        $('#preloader').fadeOut('fast', function() {
            $(this).remove();
        });

        // [TAMBAHAN] Handler untuk form pengaturan, diletakkan di sini
        $('#manage-settings').submit(function(e){
            e.preventDefault(); // Mencegah halaman berpindah
            start_load();
            
            $.ajax({
                url: 'ajax.php?action=save_attendance_settings', // Action untuk menyimpan pengaturan presensi
                method: 'POST',
                data: $(this).serialize(),
                success: function(resp){
                    if(resp == 1){
                        alert_toast("Pengaturan berhasil disimpan.", 'success');
                    } else {
                        alert_toast("Terjadi kesalahan: " + resp, 'danger');
                    }
                },
                complete: function(){
                    end_load();
                }
            });
        });

        // Handler untuk form di dalam modal
        $('#uni_modal').on('submit', 'form', function(e) {
            e.preventDefault();
            start_load();
            $.ajax({
                url: $(this).attr('action'),
                data: new FormData($(this)[0]),
                cache: false,
                contentType: false,
                processData: false,
                method: 'POST',
                success: function(resp) {
                    if (resp == 1) {
                        alert_toast("Data berhasil disimpan", 'success');
                        setTimeout(function() { location.reload(); }, 1000);
                    } 
                },
                complete: function() {
                    end_load();
                }
            });
        });

        // Handler untuk menu mobile
        const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebar-overlay');
        if(mobileMenuToggle && sidebar && sidebarOverlay) {
             mobileMenuToggle.addEventListener('click', function() {
                sidebar.classList.toggle('visible');
                sidebarOverlay.classList.toggle('visible');
            });
       
             sidebarOverlay.addEventListener('click', function() {
                sidebar.classList.remove('visible');
                this.classList.remove('visible');
            });
        }
    });
</script>

</body> </html>