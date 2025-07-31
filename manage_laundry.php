<?php
include "db_connect.php";

// Inisialisasi variabel untuk menghindari error "undefined variable"
$customer_name = $customer_phone = $status = $remarks = $pay_status = $amount_tendered = $total_amount = $amount_change = '';
$cname_arr = [];
$id = isset($_GET['id']) ? $_GET['id'] : null;

if($id){
    // Gunakan prepared statements untuk keamanan yang lebih baik
    $stmt = $conn->prepare("SELECT * FROM laundry_list WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        foreach($result->fetch_assoc() as $k => $v){
            $$k = $v;
        }
    }
}

// Ambil data kategori terlebih dahulu
$cat_query = $conn->query("SELECT * FROM laundry_categories ORDER BY name ASC");
while($row = $cat_query->fetch_assoc()) {
    $cname_arr[$row['id']] = $row;
}
?>
<style>
    .laundry-item-list-mobile { display: none; }
    .laundry-item-card {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: .5rem;
        padding: .75rem;
        margin-bottom: .5rem;
    }
    .item-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-weight: bold;
        margin-bottom: .5rem;
    }
    .item-card-body .detail-row {
        display: flex;
        justify-content: space-between;
        font-size: 0.9em;
        padding: .25rem 0;
    }
    .item-card-body .label { color: #6c757d; }
    .item-card-body .value { font-weight: 500; }
    .item-card-body input {
        max-width: 80px; /* Batasi lebar input berat di tampilan kartu */
    }

    /* Media query untuk beralih antara tabel dan kartu */
    @media (max-width: 767.98px) {
        #list-table { display: none; } /* Sembunyikan tabel di mobile */
        .laundry-item-list-mobile { display: block; } /* Tampilkan daftar kartu di mobile */
    }
</style>

<div class="container-fluid">
    <form action="" id="manage-laundry">
        <input type="hidden" name="id" value="<?php echo isset($id) ? $id : '' ?>">

        <div class="row g-3">
            <div class="col-md-6">
                <label for="customer_name" class="form-label">Nama Pelanggan</label>
                <input type="text" id="customer_name" class="form-control" name="customer_name" value="<?php echo htmlspecialchars($customer_name); ?>" required>
            </div>
            <div class="col-md-6">
                <label for="customer_phone" class="form-label">Nomor WhatsApp</label>
                <input type="text" id="customer_phone" class="form-control" name="customer_phone" value="<?php echo htmlspecialchars($customer_phone); ?>" placeholder="Contoh: 6281234567890">
                <small class="form-text text-muted">Gunakan format internasional (misal: 62... bukan 0...)</small>
            </div>
            <div class="col-md-6">
                 <label for="remarks" class="form-label">Keterangan</label>
                 <textarea name="remarks" id="remarks" rows="2" class="form-control"><?php echo htmlspecialchars($remarks); ?></textarea>
            </div>
            <?php if(isset($_GET['id'])): ?>
            <div class="col-md-6">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select">
                    <option value="0" <?php echo $status == 0 ? "selected" : '' ?>>Pending</option>
                    <option value="1" <?php echo $status == 1 ? "selected" : '' ?>>Diproses</option>
                    <option value="2" <?php echo $status == 2 ? "selected" : '' ?>>Siap Diambil</option>
                    <option value="3" <?php echo $status == 3 ? "selected" : '' ?>>Sudah Diambil</option>
                </select>
            </div>
            <?php endif; ?>
        </div>

        <hr class="my-4">

        <div class="row g-3 align-items-end">
            <div class="col-md-5">
                <label for="laundry_category_id" class="form-label">Kategori Laundry</label>
                <select class="form-select" id="laundry_category_id">
                    <option value="" disabled selected>Pilih kategori...</option>
                    <?php foreach($cname_arr as $cat_id => $cat_data): ?>
                    <option value="<?php echo $cat_id ?>" data-price="<?php echo $cat_data['price'] ?>"><?php echo htmlspecialchars(ucwords($cat_data['name'])) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="weight" class="form-label">Berat (kg)</label>
                <input type="number" step="any" min="0.1" value="1" class="form-control text-end" id="weight">
            </div>
            <div class="col-md-3">
                <button class="btn btn-info w-100" type="button" id="add_to_list"><i class="fa fa-plus"></i> Tambah ke Daftar</button>
            </div>
        </div>

        <div class="mt-4">
            <table class="table table-bordered" id="list-table">
                <thead>
                    <tr>
                        <th class="text-center">Kategori</th>
                        <th class="text-center" style="width:15%;">Berat (kg)</th>
                        <th class="text-center" style="width:20%;">Harga Satuan</th>
                        <th class="text-center" style="width:20%;">Jumlah</th>
                        <th class="text-center" style="width:5%;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(isset($_GET['id'])):
                        $list = $conn->query("SELECT * FROM laundry_items WHERE laundry_id = ".$id);
                        while($row = $list->fetch_assoc()):
                    ?>
                    <tr data-id="<?php echo $row['laundry_category_id'] ?>">
                        <td>
                            <input type="hidden" name="item_id[]" value="<?php echo $row['id'] ?>">
                            <input type="hidden" name="laundry_category_id[]" value="<?php echo $row['laundry_category_id'] ?>">
                            <?php echo isset($cname_arr[$row['laundry_category_id']]) ? ucwords($cname_arr[$row['laundry_category_id']]['name']) : 'N/A' ?>
                        </td>
                        <td><input type="number" class="form-control form-control-sm text-end" name="weight[]" value="<?php echo $row['weight'] ?>"></td>
                        <td class="text-end">
                            <input type="hidden" name="unit_price[]" value="<?php echo $row['unit_price'] ?>">
                            <?php echo number_format($row['unit_price'], 2, ',', '.') ?>
                        </td>
                        <td class="text-end">
                            <input type="hidden" name="amount[]" value="<?php echo $row['amount'] ?>">
                            <p class="mb-0"><?php echo number_format($row['amount'], 2, ',', '.') ?></p>
                        </td>
                        <td class="text-center"><button class="btn btn-sm btn-danger" type="button" onclick="rem_item($(this))"><i class="fa fa-times"></i></button></td>
                    </tr>
                    <?php endwhile; endif; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th class="text-end" colspan="3">Total</th>
                        <th class="text-end" id="tamount">0,00</th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>

            <div class="laundry-item-list-mobile">
                </div>
        </div>

        <hr class="my-4">

        <div class="row g-3">
            <div class="col-12">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="paid" name="pay" value="1" <?php echo $pay_status == 1 ? 'checked' : '' ?>>
                    <label class="form-check-label" for="paid">Tandai Sudah Lunas</label>
                </div>
            </div>
        </div>
        <div class="row g-3 mt-2" id="payment-details" style="display:none;">
            <div class="col-md-4">
                <label for="tamount-input" class="form-label">Total Tagihan</label>
                <input type="number" id="tamount-input" class="form-control text-end" name="tamount" value="<?php echo $total_amount; ?>" readonly>
            </div>
            <div class="col-md-4">
                <label for="tendered" class="form-label">Uang Tunai</label>
                <input type="number" id="tendered" class="form-control text-end" name="tendered" value="<?php echo $amount_tendered; ?>">
            </div>
            <div class="col-md-4">
                <label for="change" class="form-label">Kembalian</label>
                <input type="number" id="change" class="form-control text-end" name="change" value="<?php echo $amount_change; ?>" readonly>
            </div>
        </div>
    </form>
</div>

<script>
// === Manajemen Daftar Item ===
function addItemToList(cat_id, weight) {
    if ($('#list-table tbody tr[data-id="' + cat_id + '"]').length > 0) {
        alert_toast('Kategori sudah ada di dalam daftar.', 'warning');
        return false;
    }

    const catOption = $('#laundry_category_id option[value="' + cat_id + '"]');
    const price = parseFloat(catOption.data('price'));
    const cname = catOption.html();
    const amount = price * parseFloat(weight);

    // 1. Tambah ke Tabel (untuk desktop)
    const tr = `
        <tr data-id="${cat_id}">
            <td>
                <input type="hidden" name="item_id[]" value="">
                <input type="hidden" name="laundry_category_id[]" value="${cat_id}">
                ${cname}
            </td>
            <td><input type="number" class="form-control form-control-sm text-end" name="weight[]" value="${weight}"></td>
            <td class="text-end">
                <input type="hidden" name="unit_price[]" value="${price}">
                ${price.toLocaleString('id-ID', {minimumFractionDigits: 0})}
            </td>
            <td class="text-end">
                <input type="hidden" name="amount[]" value="${amount}">
                <p class="mb-0">${amount.toLocaleString('id-ID', {minimumFractionDigits: 0})}</p>
            </td>
            <td class="text-center"><button class="btn btn-sm btn-danger" type="button" onclick="rem_item($(this))"><i class="fa fa-times"></i></button></td>
        </tr>`;
    $('#list-table tbody').append(tr);
    
    // 2. Tambah ke Daftar Kartu (untuk mobile)
    const card = `
        <div class="laundry-item-card" data-id="${cat_id}">
            <div class="item-card-header">
                <span>${cname}</span>
                <button class="btn btn-sm btn-danger" type="button" onclick="rem_item($(this))"><i class="fa fa-times"></i></button>
            </div>
            <div class="item-card-body">
                <div class="detail-row">
                    <span class="label">Berat (kg):</span>
                    <input type="number" class="form-control form-control-sm text-end" value="${weight}">
                </div>
                <div class="detail-row">
                    <span class="label">Harga:</span>
                    <span class="value">${price.toLocaleString('id-ID')} / kg</span>
                </div>
                 <div class="detail-row">
                    <span class="label">Subtotal:</span>
                    <span class="value fw-bold">${amount.toLocaleString('id-ID', {minimumFractionDigits: 0})}</span>
                </div>
            </div>
        </div>`;
    $('.laundry-item-list-mobile').append(card);

    updateCalculations();
}

function rem_item(_this) {
    const item = _this.closest('[data-id]');
    const cat_id = item.data('id');
    $('[data-id="' + cat_id + '"]').remove();
    updateCalculations();
}

// === Logika Kalkulasi ===
function updateCalculations() {
    let total = 0;
    $('#list-table tbody tr').each(function() {
        const row = $(this);
        const weight = parseFloat(row.find('[name="weight[]"]').val()) || 0;
        const unit_price = parseFloat(row.find('[name="unit_price[]"]').val()) || 0;
        const amount = weight * unit_price;

        row.find('[name="amount[]"]').val(amount);
        row.find('p').html(amount.toLocaleString('id-ID', {minimumFractionDigits: 0}));
        
        // Sinkronisasi tampilan kartu mobile
        const mobileCard = $('.laundry-item-card[data-id="' + row.data('id') + '"]');
        mobileCard.find('.value.fw-bold').html(amount.toLocaleString('id-ID', {minimumFractionDigits: 0}));

        total += amount;
    });

    $('#tamount').html(total.toLocaleString('id-ID', {minimumFractionDigits: 0}));
    $('[name="tamount"]').val(total);
    
    // Picu kalkulasi kembalian
    $('[name="tendered"]').trigger('input');
}

// === Event Handlers ===
$(document).ready(function() {
    // Kalkulasi awal untuk mode edit
    if ('<?php echo isset($_GET['id']) ?>') {
        $('#list-table tbody tr').each(function() {
            const row = $(this);
            const cat_id = row.data('id');
            const cat_name = row.find('td:first-child').text().trim();
            const weight = row.find('[name="weight[]"]').val();
            const price = row.find('[name="unit_price[]"]').val();
            const amount = row.find('[name="amount[]"]').val();
            
            // Buat ulang tampilan kartu mobile dari data tabel yang sudah ada
            const card = `
                <div class="laundry-item-card" data-id="${cat_id}">
                    <div class="item-card-header">
                        <span>${cat_name}</span>
                        <button class="btn btn-sm btn-danger" type="button" onclick="rem_item($(this))"><i class="fa fa-times"></i></button>
                    </div>
                    <div class="item-card-body">
                         <div class="detail-row">
                            <span class="label">Berat (kg):</span>
                            <input type="number" class="form-control form-control-sm text-end" value="${weight}">
                        </div>
                        <div class="detail-row">
                            <span class="label">Harga:</span>
                            <span class="value">${parseFloat(price).toLocaleString('id-ID')} / kg</span>
                        </div>
                        <div class="detail-row">
                            <span class="label">Subtotal:</span>
                            <span class="value fw-bold">${parseFloat(amount).toLocaleString('id-ID', {minimumFractionDigits: 0})}</span>
                        </div>
                    </div>
                </div>`;
            $('.laundry-item-list-mobile').append(card);
        });
        updateCalculations();
    }
    
    // Tombol tambah item
    $('#add_to_list').click(function() {
        const cat_id = $('#laundry_category_id').val();
        const weight = $('#weight').val();
        if (!cat_id || !weight || parseFloat(weight) <= 0) {
            alert_toast('Silakan pilih kategori dan masukkan berat yang valid.', 'warning');
            return;
        }
        addItemToList(cat_id, weight);
        $('#laundry_category_id').val('');
        $('#weight').val('1');
    });

    // Kalkulasi dinamis saat berat diubah (untuk tabel dan kartu)
    $(document).on('input', '#list-table [name="weight[]"], .laundry-item-card input', function() {
        const changedInput = $(this);
        const newValue = changedInput.val();
        const cat_id = changedInput.closest('[data-id]').data('id');
        
        // Sinkronisasi nilai antara dua tampilan
        const tableRowInput = $('#list-table tr[data-id="'+ cat_id +'"]').find('[name="weight[]"]');
        const cardInput = $('.laundry-item-card[data-id="'+ cat_id +'"]').find('input');

        if (!changedInput.is(tableRowInput)) tableRowInput.val(newValue);
        if (!changedInput.is(cardInput)) cardInput.val(newValue);
        
        updateCalculations();
    });

    // Logika tombol Lunas
    function togglePayment(isPaid) {
        if (isPaid) {
            $('#payment-details').slideDown();
            $('#tendered').prop('required', true);
        } else {
            $('#payment-details').slideUp();
            $('#tendered').prop('required', false);
        }
    }
    togglePayment($('#paid').is(':checked'));
    $('#paid').change(function() {
        togglePayment($(this).is(':checked'));
    });

    // Kalkulasi kembalian
    $('#tendered, #tamount-input').on('input', function() {
        const tendered = parseFloat($('#tendered').val()) || 0;
        const total = parseFloat($('#tamount-input').val()) || 0;
        const change = tendered - total;
        $('#change').val(change.toFixed(0));
    });

    // Pengiriman form
    $('#manage-laundry').submit(function(e) {
        e.preventDefault();
        if ($('#list-table tbody tr').length <= 0) {
            alert_toast("Silakan tambahkan minimal satu item laundry.", "warning");
            return false;
        }
        start_load();
        $.ajax({
            url: 'ajax.php?action=save_laundry',
            data: new FormData($(this)[0]),
            cache: false,
            contentType: false,
            processData: false,
            method: 'POST',
            success: function(resp) {
                if (resp == 1) {
                    alert_toast("Data berhasil ditambahkan", 'success');
                } else if (resp == 2) {
                    alert_toast("Data berhasil diperbarui", 'success');
                } else {
                    alert_toast("Terjadi sebuah kesalahan.", 'error');
                }
                setTimeout(function() {
                    location.href = 'index.php?page=laundry';
                }, 1500);
            }
        });
    });

});
</script>