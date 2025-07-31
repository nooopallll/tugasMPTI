<div class="card shadow-sm">
    <div class="card-header bg-primary text-white text-center">
        <h4 style="color: white !important;"><i class="fa-solid fa-clipboard-user"></i> Presensi Kehadiran Staff</h4>
    </div>
    <div class="card-body text-center">
        <h5 class="card-title" style="color: #2c4964;">Selamat Datang, <?php echo $_SESSION['login_name']; ?>!</h5>
        <p class="text-muted" id="p-status">Silakan lakukan presensi sesuai jadwal.</p>
        
        <div id="clock" class="clock my-3" style="color: #2c4964;"></div>
        
        <button id="btn-presensi" class="btn btn-primary btn-lg">
            <i class="fa-solid fa-spinner fa-spin"></i> Memuat Status...
        </button>
        
        <div id="status" class="mt-3"></div>
        <div id="map" style="height: 250px; width: 100%; margin-top: 1rem; display: none;"></div>
    </div>
</div>

<script>
    // Elemen DOM
    const mapContainer = document.getElementById('map');
    const statusDiv = document.getElementById('status');
    const presensiButton = document.getElementById('btn-presensi');
    const pStatus = document.getElementById('p-status');
    let map = null, marker = null;

    // Fungsi Jam
    function updateClock() {
        const now = new Date();
        document.getElementById('clock').textContent = now.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) + ' - ' + now.toLocaleTimeString('id-ID');
    }
    setInterval(updateClock, 1000);
    updateClock();
    
    // Fungsi untuk memperbarui UI berdasarkan status presensi
    // (PERUBAHAN) Baris pertama di fungsi ini (yang menghapus statusDiv) adalah inti masalah, 
    // namun kita akan atasi dengan urutan pemanggilan yang benar.
    function updateUI(status, data) {
        statusDiv.innerHTML = ''; // Baris ini sengaja dibiarkan untuk membersihkan state sebelum menampilkan pesan baru
        switch(status) {
            case 'not_checked_in':
                presensiButton.className = 'btn btn-primary btn-lg';
                presensiButton.innerHTML = '<i class="fa-solid fa-right-to-bracket"></i> Check In';
                presensiButton.disabled = false;
                presensiButton.onclick = () => submitPresensi('in');
                pStatus.textContent = 'Anda belum melakukan presensi masuk hari ini.';
                break;
            case 'checked_in':
                presensiButton.className = 'btn btn-danger btn-lg';
                presensiButton.innerHTML = '<i class="fa-solid fa-right-from-bracket"></i> Check Out';
                presensiButton.disabled = false;
                presensiButton.onclick = () => submitPresensi('out');
                pStatus.innerHTML = `Anda sudah Check In pada pukul <strong>${data.waktu_masuk}</strong>. Silakan Check Out jika sudah selesai.`;
                break;
            case 'completed':
                presensiButton.className = 'btn btn-secondary btn-lg';
                presensiButton.innerHTML = '<i class="fa-solid fa-check-double"></i> Presensi Selesai';
                presensiButton.disabled = true;
                pStatus.innerHTML = `Presensi hari ini selesai. Masuk: <strong>${data.waktu_masuk}</strong>, Pulang: <strong>${data.waktu_pulang}</strong>.`;
                break;
        }
    }

    // Fungsi untuk Cek status presensi saat halaman dimuat
    async function checkStatusPresensi() {
        try {
            presensiButton.disabled = true;
            presensiButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memuat Status...';
            const response = await fetch('get_status_presensi.php');
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Gagal memuat status.');
            updateUI(data.status, data);
        } catch (error) {
            statusDiv.innerHTML = `<div class="alert alert-warning">${error.message}</div>`;
            presensiButton.disabled = true;
            presensiButton.innerHTML = 'Gagal Memuat';
        }
    }

    // Fungsi utama yang dipanggil saat tombol ditekan
    function submitPresensi(type) {
        presensiButton.disabled = true;
        presensiButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Mendapatkan Lokasi...';
        statusDiv.innerHTML = ''; 
        navigator.geolocation.getCurrentPosition(
            (position) => sendData(position, type), 
            (error) => showError(error, true), // (PERUBAHAN) Tambah parameter untuk membedakan error lokasi
            { timeout: 15000, maximumAge: 0, enableHighAccuracy: true }
        );
    }

    // Fungsi untuk mengirim data ke server (TELAH DIMODIFIKASI)
    async function sendData(position, type) {
        const { latitude: lat, longitude: lon } = position.coords;
        showMap(lat, lon);
        presensiButton.innerHTML = '<i class="fa-solid fa-server fa-spin"></i> Mengirim Data...';
        try {
            const formData = new URLSearchParams();
            formData.append('lat', lat);
            formData.append('lon', lon);
            formData.append('type', type);
            
            const response = await fetch('submit_presensi.php', { method: 'POST', body: formData });
            const resultText = await response.text();
            
            if (!response.ok) { throw new Error(resultText); }

            // (PERUBAHAN) LOGIKA BARU UNTUK SUKSES
            // 1. Update dulu state tombol dan UI ke kondisi terbaru
            await checkStatusPresensi();
            // 2. SETELAH UI ter-update, baru tampilkan pesan sukses. Pesan ini akan tetap ada.
            statusDiv.innerHTML = `<div class="alert alert-success" role="alert"><i class="fa-solid fa-check-circle"></i> ${resultText}</div>`;
            
        } catch (error) {
            // Jika gagal, panggil showError, yang juga akan mengikuti logika baru
            showError(error, false);
        }
    }
    
    function showMap(lat, lon) {
        mapContainer.style.display = 'block';
        if (!map) {
            map = L.map('map').setView([lat, lon], 17);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
            marker = L.marker([lat, lon]).addTo(map);
        } else {
            map.setView([lat, lon], 17);
            marker.setLatLng([lat, lon]);
        }
        marker.bindPopup("<b>Lokasi Presensi Anda</b>").openPopup();
    }
    
    // Fungsi untuk menampilkan berbagai macam error (TELAH DIMODIFIKASI)
    async function showError(error, isGeolocationError) {
        console.error("GAGAL: Terjadi error.", error);
        let message = '';

        if(isGeolocationError) {
            switch(error.code) {
                case error.PERMISSION_DENIED:
                    message = "Anda menolak izin akses lokasi. Silakan izinkan akses lokasi di pengaturan browser Anda.";
                    break;
                case error.POSITION_UNAVAILABLE:
                    message = "Informasi lokasi tidak tersedia saat ini. Coba lagi nanti.";
                    break;
                case error.TIMEOUT:
                    message = "Waktu untuk mendapatkan lokasi habis. Pastikan GPS Anda aktif dan sinyal tidak terhalang.";
                    break;
                default:
                    message = "Terjadi kesalahan yang tidak diketahui saat mengambil lokasi.";
                    break;
            }
        } else { // Ini adalah error dari fetch (server)
            message = error.message;
        }
        
        // (PERUBAHAN) LOGIKA BARU UNTUK ERROR
        // 1. Kembalikan tombol ke state terakhir yang valid dengan memanggil checkStatusPresensi()
        await checkStatusPresensi();
        // 2. SETELAH UI kembali ke state yang benar, baru tampilkan pesan error. Pesan ini akan tetap ada.
        statusDiv.innerHTML = `<div class="alert alert-danger" role="alert"><i class="fa-solid fa-triangle-exclamation"></i> <strong>Error:</strong> ${message}</div>`;
    }
    
    // Panggil fungsi pengecekan status saat halaman pertama kali dimuat
    document.addEventListener('DOMContentLoaded', checkStatusPresensi);
</script>