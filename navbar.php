<nav id="sidebar">

    <ul class="sidebar-list">
        <li>
            <a href="index.php?page=home" class="nav-item nav-home"><span class='icon-field'><i class="fa fa-home"></i></span> Home</a>
        </li>
        <li>
            <a href="index.php?page=laundry" class="nav-item nav-laundry"><span class='icon-field'><i class="fa fa-water"></i></span> Laundry List</a>
        </li>
        <li>
            <a href="index.php?page=categories" class="nav-item nav-categories"><span class='icon-field'><i class="fa fa-list"></i></span> Laundry Category</a>
        </li>
        <li>
            <a href="index.php?page=supply" class="nav-item nav-supply"><span class='icon-field'><i class="fa fa-boxes"></i></span> Supply List</a>
        </li>
        <li>
            <a href="index.php?page=inventory" class="nav-item nav-inventory"><span class='icon-field'><i class="fa fa-list-alt"></i></span> Inventory</a>
        </li>
        <li>
            <a href="index.php?page=reports" class="nav-item nav-reports"><span class='icon-field'><i class="fa fa-th-list"></i></span> Reports</a>
        </li>
        
        <?php if(isset($_SESSION['login_type']) && $_SESSION['login_type'] == 1): ?>
        <li>
            <a href="index.php?page=users" class="nav-item nav-users"><span class='icon-field'><i class="fa fa-users"></i></span> Users</a>
        </li>
        <?php endif; ?>
    </ul>

</nav>

<script>
    $('.nav-<?php echo isset($_GET['page']) ? $_GET['page'] : '' ?>').addClass('active');
</script>