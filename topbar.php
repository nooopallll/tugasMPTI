<style>
    /* Style ini bisa dipindahkan ke file CSS utama Anda */
    .logo-container {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #ffffff;
    }

    .logo-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* CSS Tambahan untuk jarak logo (solusi dari sebelumnya) */
    .navbar-brand .logo-container {
        margin-right: 1rem !important;
    }

    /* Memastikan dropdown berada di posisi yang benar */
    #topbar .nav-item.dropdown {
        position: relative;
    }

    #topbar .dropdown-menu {
        position: absolute;
        z-index: 1050;
    }
</style>

<nav id="topbar" class="navbar navbar-expand-lg">
    <div class="container-fluid">

        <button class="mobile-menu-toggle" id="mobile-menu-toggle" aria-label="Toggle Menu">
            &#9776; </button>

        <a class="navbar-brand d-flex align-items-center" href="index.php?page=home">
            <div class="logo-container">
                <img src="assets/img/logoaes.png" alt="Logo">
            </div>
            <b class="d-none d-sm-block" style="color: white;">Sistem Manajemen Laundry</b>
        </a>

        <div class="ms-auto">
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa fa-user-circle"></i>
                        <span class="d-none d-sm-inline ms-1"><?php echo $_SESSION['login_name'] ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <li>
                            <a class="dropdown-item" href="ajax.php?action=logout">
                                <i class="fa fa-power-off"></i>
                                <span class="d-none d-sm-inline">Logout</span>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>

    </div>
</nav>