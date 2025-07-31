<?php
// Mengambil data pengaturan yang ada dari database untuk ditampilkan di form
include 'db_connect.php';
$qry = $conn->query("SELECT * FROM system_settings limit 1");
if($qry->num_rows > 0){
    foreach($qry->fetch_array() as $k => $val){
        $meta[$k] = $val;
    }
}
?>
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h4><i class="fa fa-cog"></i> Pengaturan Presensi</h4>
        </div>
        <div class="card-body">
            <form action="ajax.php?action=save_attendance_settings" id="manage-settings">
                <div class="form-group mb-3">
                    <label for="office_latitude" class="form-label">Latitude Kantor</label>
                    <input type="number" step="any" class="form-control" id="office_latitude" name="office_latitude" value="<?php echo isset($meta['office_latitude']) ? $meta['office_latitude'] : '' ?>" required>
                    <small class="form-text text-muted">Contoh: -7.832557</small>
                </div>
                <div class="form-group mb-3">
                    <label for="office_longitude" class="form-label">Longitude Kantor</label>
                    <input type="number" step="any" class="form-control" id="office_longitude" name="office_longitude" value="<?php echo isset($meta['office_longitude']) ? $meta['office_longitude'] : '' ?>" required>
                    <small class="form-text text-muted">Contoh: 110.359922</small>
                </div>
                <div class="form-group mb-4">
                    <label for="attendance_radius" class="form-label">Batas Jarak Presensi (dalam meter)</label>
                    <input type="number" class="form-control" id="attendance_radius" name="attendance_radius" value="<?php echo isset($meta['attendance_radius']) ? $meta['attendance_radius'] : '' ?>" required>
                    <small class="form-text text-muted">Contoh: 100 (artinya presensi hanya bisa dilakukan dalam jarak 100 meter dari kantor)</small>
                </div>
                <div class="card-footer text-end">
                    <button class="btn btn-primary" type="submit">Simpan Pengaturan</button>
                </div>
            </form>
        </div>
    </div>
</div>