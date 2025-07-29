<?php
include 'db_connect.php';
// Cek apakah ID ada di URL
if(isset($_GET['id'])){
    // Ambil data utama laundry dari tabel laundry_list
    $qry = $conn->query("SELECT * FROM laundry_list where id = ".$_GET['id']);
    // Ubah hasil query menjadi array asosiatif
    foreach($qry->fetch_array() as $k => $v){
        $$k = $v;
    }
    // Ambil item-item laundry dari tabel laundry_items
    $items = $conn->query("SELECT i.*, c.name as cname FROM laundry_items i inner join laundry_categories c on c.id = i.laundry_category_id where i.laundry_id = ".$_GET['id']);
}
?>
<style>
    body {
        font-family: Arial, sans-serif;
        font-size: 13px;
    }
    .container {
        padding: 10px;
        border: 1px solid black;
        width: 400px; /* Lebar struk */
    }
    table {
        width: 100%;
        border-collapse: collapse;
    }
    th, td {
        padding: 5px;
        text-align: left;
    }
    .text-center {
        text-align: center;
    }
    .text-right {
        text-align: right;
    }
    h3, h4 {
        margin: 5px 0;
    }
    hr {
        border: 1px dashed black;
    }
</style>

<div class="container">
    <div class="text-center">
        <h3>Struk Laundry</h3>
        <h4>Nama Toko Laundry Anda</h4>
        <p>Alamat Toko Anda | No. Telp Anda</p>
    </div>
    <hr>
    <p>
        Tanggal: <?php echo date("d M, Y", strtotime($date_created)) ?><br>
        Nama Pelanggan: **<?php echo ucwords($customer_name) ?>**<br>
        No. Antrian: <?php echo $queue ?>
    </p>
    <hr>
    <table>
        <thead>
            <tr>
                <th>Kategori</th>
                <th class="text-right">Berat (kg)</th>
                <th class="text-right">Harga</th>
                <th class="text-right">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Loop untuk menampilkan setiap item laundry
            while($row=$items->fetch_assoc()):
            ?>
            <tr>
                <td><?php echo $row['cname'] ?></td>
                <td class="text-right"><?php echo $row['weight'] ?></td>
                <td class="text-right"><?php echo number_format($row['unit_price'],0) ?></td>
                <td class="text-right"><?php echo number_format($row['amount'],0) ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3">Total Bayar</th>
                <th class="text-right">Rp <?php echo number_format($total_amount,0) ?></th>
            </tr>
            <tr>
                <th colspan="3">Uang Tunai</th>
                <th class="text-right">Rp <?php echo number_format($amount_tendered,0) ?></th>
            </tr>
            <tr>
                <th colspan="3">Kembalian</th>
                <th class="text-right">Rp <?php echo number_format($amount_tendered - $total_amount,0) ?></th>
            </tr>
        </tfoot>
    </table>
    <hr>
    <div class="text-center">
        <p>Terima kasih telah menggunakan jasa kami.</p>
        <p>Status:
            <?php if($status == 0): ?>
                Pending
            <?php elseif($status == 1): ?>
                Processing
            <?php elseif($status == 2): ?>
                Siap Diambil
            <?php else: ?>
                Sudah Diambil
            <?php endif; ?>
        </p>
    </div>
</div>