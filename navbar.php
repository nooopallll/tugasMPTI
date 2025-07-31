<nav id="sidebar">

    <ul class="sidebar-list">
        <li>
            <a href="index.php?page=home" class="nav-item nav-home">
                <span class='icon-field'><i class="fa fa-home"></i></span> Home
            </a>
        </li>
        <li>
            <a href="index.php?page=laundry" class="nav-item nav-laundry">
                <span class='icon-field'><i class="fa fa-water"></i></span> Daftar Pesanan
            </a>
        </li>
        <li>
            <a href="index.php?page=categories" class="nav-item nav-categories">
                <span class='icon-field'><i class="fa fa-list"></i></span> Kategori & Harga
            </a>
        </li>
        <li>
            <a href="index.php?page=supply" class="nav-item nav-supply">
                <span class='icon-field'><i class="fa fa-boxes"></i></span> Daftar Stok
            </a>
        </li>
        <li>
            <a href="index.php?page=inventory" class="nav-item nav-inventory">
                <span class='icon-field'><i class="fa fa-list-alt"></i></span> Inventaris
            </a>
        </li>

        <?php if($_SESSION['login_type'] == 2): ?>
        <li>
            <a href="index.php?page=presensi" class="nav-item nav-presensi">
                <span class='icon-field'><i class="fa fa-map-marker-alt"></i></span> Presensi
            </a>
        </li>
        <?php endif; ?>

        <li>
            <a href="index.php?page=reports" class="nav-item nav-reports">
                <span class='icon-field'><i class="fa fa-th-list"></i></span> Laporan
            </a>
        </li>

        <?php if(isset($_SESSION['login_type']) && $_SESSION['login_type'] == 1): ?>
        <li>
            <a href="index.php?page=reports_presensi" class="nav-item nav-reports_presensi">
                <span class='icon-field'><i class="fa fa-calendar-check"></i></span> Laporan Presensi
            </a>
        </li>
        <li>
            <a href="index.php?page=settings" class="nav-item nav-settings">
                <span class='icon-field'><i class="fa fa-cog"></i></span> Pengaturan
            </a>
        </li>
        <li>
            <a href="index.php?page=users" class="nav-item nav-users">
                <span class='icon-field'><i class="fa fa-users"></i></span> Akun
            </a>
        </li>
        <?php endif; ?>
    </ul>

</nav>

<script>
    $('.nav-<?php echo isset($_GET['page']) ? $_GET['page'] : '' ?>').addClass('active');
</script>