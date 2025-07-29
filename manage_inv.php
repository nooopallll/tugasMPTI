<?php 
include 'db_connect.php'; 
if(isset($_GET['id'])){
    // Menggunakan prepared statement untuk keamanan
    $stmt = $conn->prepare("SELECT * FROM inventory WHERE id = ?");
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows > 0){
        $data = $result->fetch_assoc();
        foreach($data as $k => $v){
            $$k = $v;
        }
    }
}
?>

<div class="container-fluid">
    <form action="" id="manage-inv">
        <input type="hidden" name="id" value="<?php echo isset($id) ? $id : '' ?>">
        
        <div class="mb-3">
            <label for="supply_id" class="form-label">Nama Stok</label>
            <select class="form-select" name="supply_id" id="supply_id" required>
                <option value="" disabled selected>Pilih stok...</option>
                <?php 
                    $supply = $conn->query("SELECT * FROM supply_list ORDER BY name ASC");
                    while($row = $supply->fetch_assoc()):
                ?>
                <option value="<?php echo $row['id'] ?>" <?php echo (isset($supply_id) && $supply_id == $row['id']) ? "selected" : '' ?>>
                    <?php echo htmlspecialchars(ucwords($row['name'])) ?>
                </option>
                <?php endwhile; ?>
            </select>
        </div>
        
        <div class="mb-3">
            <label for="qty" class="form-label">Jumlah (QTY)</label>
            <input type="number" step="any" min="1" value="<?php echo isset($qty) ? $qty : '' ?>" class="form-control text-right" name="qty" id="qty" placeholder="Masukkan jumlah" required>
        </div>
        
        <div class="mb-3">
            <label for="stock_type" class="form-label">Tipe Transaksi</label>
            <select name="stock_type" id="stock_type" class="form-select" required>
                <option value="1" <?php echo (isset($stock_type) && $stock_type == 1) ? "selected" : '' ?>>Stok Masuk (Stock In)</option>
                <option value="2" <?php echo (isset($stock_type) && $stock_type == 2) ? "selected" : '' ?>>Stok Keluar (Digunakan)</option>
            </select>
        </div>
        
    </form>
</div>

<script>
    // Script Anda sudah bagus dan tidak perlu diubah.
    $('#manage-inv').submit(function(e){
        e.preventDefault();
        start_load();
        $.ajax({
            url: 'ajax.php?action=save_inv',
            method: 'POST',
            data: $(this).serialize(),
            success: function(resp){
                if(resp == 1){
                    alert_toast("Data berhasil disimpan", 'success');
                    setTimeout(function(){
                        location.reload();
                    }, 1000);
                } else {
                    alert_toast("Terjadi kesalahan", 'error');
                    end_load();
                }
            }
        });
    });
</script>