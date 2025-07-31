<?php
session_start();
ini_set('display_errors', 1);
Class Action {
	private $db;

	public function __construct() {
		ob_start();
   	include 'db_connect.php';
    
    $this->db = $conn;
	}
	function __destruct() {
	    $this->db->close();
	    ob_end_flush();
	}

function login(){
    // 1. Ambil input dari form
    $username = $_POST['username'];
    $plaintext_password = $_POST['password'];

    // 2. Cari user dengan aman menggunakan prepared statement
    $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user_data = $result->fetch_assoc();
        // Pastikan menggunakan trim()
        $hashed_password_from_db = trim($user_data['password']); 

        // 3. Verifikasi password dengan aman
        if (password_verify($plaintext_password, $hashed_password_from_db)) {
            
            // 4. JIKA BERHASIL: Set semua session seperti di kode lama Anda
            foreach ($user_data as $key => $value) {
                if ($key != 'password' && !is_numeric($key)) {
                    $_SESSION['login_' . $key] = $value;
                }
            }
            
            return 1; // Kirim sinyal sukses
        }
    }

    // Jika user tidak ditemukan atau password salah
    return 3;
}
	function login2(){
    // 1. HINDARI extract().
    $email = $_POST['email'];
    $password = $_POST['password'];

    // 2. GUNAKAN PREPARED STATEMENT untuk query SELECT.
    $stmt = $this->db->prepare("SELECT * FROM user_info WHERE email = ?");
    if(!$stmt) return 3;

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0){
        $user = $result->fetch_assoc();

        // 3. VERIFIKASI PASSWORD dengan standar modern.
        // (Lihat catatan penting tentang password di bawah)
        if (password_verify($password, $user['password'])) {
            
            // 4. SET SESSION secara eksplisit.
            // Pastikan nama kolom di tabel Anda benar (misal: 'id', 'first_name')
            $_SESSION['login_id'] = $user['id'];
            $_SESSION['login_name'] = $user['first_name'];

            // 5. UPDATE KERANJANG DENGAN CARA AMAN
            $ip = $_SERVER['REMOTE_ADDR'];
            if (!empty($_SERVER['HTTP_CLIENT_IP'])) { $ip = $_SERVER['HTTP_CLIENT_IP']; }
            elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) { $ip = $_SERVER['HTTP_X_FORWARDED_FOR']; }

            if (!empty($ip) && isset($_SESSION['login_id'])) {
                $userId = $_SESSION['login_id'];
                $update_stmt = $this->db->prepare("UPDATE cart SET user_id = ? WHERE client_ip = ?");
                if ($update_stmt) {
                    $update_stmt->bind_param("is", $userId, $ip); // 'i' untuk integer, 's' untuk string
                    $update_stmt->execute();
                    $update_stmt->close();
                }
            }
            
            $stmt->close();
            return 1; // Login Sukses
        }
    }

    $stmt->close();
    return 3; // Login Gagal
}

	function logout(){
		session_destroy();
		foreach ($_SESSION as $key => $value) {
			unset($_SESSION[$key]);
		}
		header("location:login.php");
	}
	function logout2(){
		session_destroy();
		foreach ($_SESSION as $key => $value) {
			unset($_SESSION[$key]);
		}
		header("location:../index.php");
	}

	function save_user() {
    // 1. Hindari extract($_POST), akses variabel secara langsung.
    $id = $_POST['id'];
    $name = $_POST['name'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $type = $_POST['type'];

    // 2. Siapkan query dengan prepared statements untuk mencegah SQL Injection.
    // Logika untuk hanya update password jika diisi.
    if (!empty($password)) {
        // 3. Hashing password dengan standar modern.
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Query untuk INSERT atau UPDATE dengan password baru.
        $sql_password_part = ", password = ?";
        $types_password_part = "s";
    } else {
        // Jangan ubah password jika kolomnya kosong saat update.
        $sql_password_part = "";
        $types_password_part = "";
    }

    if (empty($id)) {
        // === INSERT (User Baru) ===
        // Password wajib untuk user baru.
        if (empty($password)) {
            return "Error: Password is required for new users.";
        }
        
        $stmt = $this->db->prepare("INSERT INTO users SET name = ?, username = ?, type = ? {$sql_password_part}");
        // Tipe data: s=string, i=integer
        $stmt->bind_param("ssi{$types_password_part}", $name, $username, $type, $hashed_password);

    } else {
        // === UPDATE (User Lama) ===
        $stmt = $this->db->prepare("UPDATE users SET name = ?, username = ?, type = ? {$sql_password_part} WHERE id = ?");

        if (!empty($password)) {
            // Jika password diubah
            $stmt->bind_param("ssi{$types_password_part}i", $name, $username, $type, $hashed_password, $id);
        } else {
            // Jika password tidak diubah
            $stmt->bind_param("ssii", $name, $username, $type, $id);
        }
    }

    // Eksekusi query
    if ($stmt->execute()) {
        return 1; // Sukses
    } else {
        return $stmt->error; // Gagal, kembalikan pesan error
    }
}
	function signup(){
		extract($_POST);
		$data = " first_name = '$first_name' ";
		$data .= ", last_name = '$last_name' ";
		$data .= ", mobile = '$mobile' ";
		$data .= ", address = '$address' ";
		$data .= ", email = '$email' ";
		$data .= ", password = '".md5($password)."' ";
		$chk = $this->db->query("SELECT * FROM user_info where email = '$email' ")->num_rows;
		if($chk > 0){
			return 2;
			exit;
		}
			$save = $this->db->query("INSERT INTO user_info set ".$data);
		if($save){
			$login = $this->login2();
			return 1;
		}
	}

	function save_settings(){
		extract($_POST);
		$data = " name = '$name' ";
		$data .= ", email = '$email' ";
		$data .= ", contact = '$contact' ";
		$data .= ", about_content = '".htmlentities(str_replace("'","&#x2019;",$about))."' ";
		if($_FILES['img']['tmp_name'] != ''){
						$fname = strtotime(date('y-m-d H:i')).'_'.$_FILES['img']['name'];
						$move = move_uploaded_file($_FILES['img']['tmp_name'],'../assets/img/'. $fname);
					$data .= ", cover_img = '$fname' ";

		}
		
		// echo "INSERT INTO system_settings set ".$data;
		$chk = $this->db->query("SELECT * FROM system_settings");
		if($chk->num_rows > 0){
			$save = $this->db->query("UPDATE system_settings set ".$data." where id =".$chk->fetch_array()['id']);
		}else{
			$save = $this->db->query("INSERT INTO system_settings set ".$data);
		}
		if($save){
		$query = $this->db->query("SELECT * FROM system_settings limit 1")->fetch_array();
		foreach ($query as $key => $value) {
			if(!is_numeric($key))
				$_SESSION['setting_'.$key] = $value;
		}

			return 1;
				}
	}

	
	function save_category(){
		extract($_POST);
		$data = " name = '$name' ";
		$data .= ", price = '$price' ";
		if(empty($id)){
			$save = $this->db->query("INSERT INTO laundry_categories set ".$data);
		}else{
			$save = $this->db->query("UPDATE laundry_categories set ".$data." where id=".$id);
		}
		if($save)
			return 1;
	}
	function delete_category(){
		extract($_POST);
		$delete = $this->db->query("DELETE FROM laundry_categories where id = ".$id);
		if($delete)
			return 1;
	}
	function save_supply(){
		extract($_POST);
		$data = " name = '$name' ";
		if(empty($id)){
			$save = $this->db->query("INSERT INTO supply_list set ".$data);
		}else{
			$save = $this->db->query("UPDATE supply_list set ".$data." where id=".$id);
		}
		if($save)
			return 1;
	}
	function delete_supply(){
		extract($_POST);
		$delete = $this->db->query("DELETE FROM supply_list where id = ".$id);
		if($delete)
			return 1;
	}

	function save_laundry(){
        // Ekstrak semua variabel dari POST
        extract($_POST);

        $status = isset($status) ? $status : 0;
        $tendered = isset($tendered) ? $tendered : 0; 
         $change = isset($change) ? $change : 0; 
        // Ambil status lama SEBELUM update (jika ini adalah proses edit)
        $old_status = null;
        if(isset($id) && !empty($id)){
            $stmt_old = $this->db->prepare("SELECT status FROM laundry_list WHERE id = ?");
            $stmt_old->bind_param("i", $id);
            $stmt_old->execute();
            $result_old = $stmt_old->get_result();
            if($result_old->num_rows > 0){
                $old_status = $result_old->fetch_assoc()['status'];
            }
            $stmt_old->close();
        }

        // Proses simpan atau update data utama
        if(empty($id)){
            // === INSERT DATA BARU ===
            $queue_stmt = $this->db->query("SELECT `queue` FROM laundry_list where date(date_created) = '".date('Y-m-d')."' order by `queue` desc limit 1");
            $queue = $queue_stmt->num_rows > 0 ? $queue_stmt->fetch_array()['queue'] + 1 : 1;

            $stmt = $this->db->prepare("INSERT INTO laundry_list (customer_name, customer_phone, remarks, total_amount, amount_tendered, amount_change, pay_status, `queue`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $pay_status = isset($pay) ? 1 : 0;
            $stmt->bind_param("sssdddii", $customer_name, $customer_phone, $remarks, $tamount, $tendered, $change, $pay_status, $queue);
            
            $save = $stmt->execute();
            if($save){
                $id = $this->db->insert_id; // Dapatkan ID dari data yang baru dimasukkan
            }
        }else{
            // === UPDATE DATA LAMA ===
            $stmt = $this->db->prepare("UPDATE laundry_list SET customer_name = ?, customer_phone = ?, remarks = ?, total_amount = ?, amount_tendered = ?, amount_change = ?, pay_status = ?, status = ? WHERE id = ?");
            $pay_status = isset($pay) ? 1 : 0;
            $new_status = isset($status) ? $status : $old_status; // Gunakan status lama jika tidak ada status baru
            $stmt->bind_param("sssdddisi", $customer_name, $customer_phone, $remarks, $tamount, $tendered, $change, $pay_status, $new_status, $id);
            $save = $stmt->execute();
        }

        // Jika query utama berhasil, proses item-itemnya
        if($save){
            // Proses item cucian
            $laundry_category_id = $_POST['laundry_category_id'];
            $weight = $_POST['weight'];
            $unit_price = $_POST['unit_price'];
            $amount = $_POST['amount'];
            $item_ids = isset($_POST['item_id']) ? $_POST['item_id'] : [];
            
            $existing_item_ids = [];

            foreach ($weight as $key => $value) {
                $item_data = [
                    'laundry_id' => $id,
                    'laundry_category_id' => $laundry_category_id[$key],
                    'weight' => $weight[$key],
                    'unit_price' => $unit_price[$key],
                    'amount' => $amount[$key]
                ];
                
                if(empty($item_ids[$key])){
                    // Insert item baru
                    $item_stmt = $this->db->prepare("INSERT INTO laundry_items (laundry_id, laundry_category_id, weight, unit_price, amount) VALUES (?, ?, ?, ?, ?)");
                    $item_stmt->bind_param("iiddi", $item_data['laundry_id'], $item_data['laundry_category_id'], $item_data['weight'], $item_data['unit_price'], $item_data['amount']);
                    $item_stmt->execute();
                    $existing_item_ids[] = $this->db->insert_id;
                } else {
                    // Update item yang sudah ada
                    $current_item_id = $item_ids[$key];
                    $item_stmt = $this->db->prepare("UPDATE laundry_items SET laundry_category_id = ?, weight = ?, unit_price = ?, amount = ? WHERE id = ?");
                    $item_stmt->bind_param("iddii", $item_data['laundry_category_id'], $item_data['weight'], $item_data['unit_price'], $item_data['amount'], $current_item_id);
                    $item_stmt->execute();
                    $existing_item_ids[] = $current_item_id;
                }
            }
            
            // Hapus item yang tidak ada lagi di list (jika proses update)
            if(!empty($id) && count($existing_item_ids) > 0) {
                $ids_to_keep = implode(',', array_map('intval', $existing_item_ids));
                $this->db->query("DELETE FROM laundry_items WHERE laundry_id = {$id} AND id NOT IN ({$ids_to_keep})");
            }


            // ================== LOGIKA KIRIM WHATSAPP ==================
            $new_status_check = isset($status) ? $status : 0;
            if ($new_status_check == 2 && $old_status != 2) {
                if(!empty($customer_phone)){
                    $message = "Halo {$customer_name},\n\nCucian Anda dengan nomor antrian #{$id} sudah selesai dan siap diambil.\n\nTotal tagihan: Rp " . number_format($tamount) . "\n\nTerima kasih.";
                    $this->send_whatsapp_message($customer_phone, $message);
                }
            }
            // ================== AKHIR LOGIKA WHATSAPP ==================

            return empty($_POST['id']) ? 1 : 2; // 1 untuk sukses insert, 2 untuk sukses update
        } else {
            // Jika query gagal, kembalikan pesan error
            return $this->db->error;
        }
    }


	function delete_laundry(){
		extract($_POST);
		$delete = $this->db->query("DELETE FROM laundry_list where id = ".$id);
		$delete2 = $this->db->query("DELETE FROM laundry_items where laundry_id = ".$id);
		if($delete && $delete2)
			return 1;
	}
	function save_inv(){
		extract($_POST);
		$data = " supply_id = '$supply_id' ";
		$data .= ", qty = '$qty' ";
		$data .= ", stock_type = '$stock_type' ";
		if(empty($id)){
			$save = $this->db->query("INSERT INTO inventory set ".$data);
		}else{
			$save = $this->db->query("UPDATE inventory set ".$data." where id=".$id);
		}
		if($save)
			return 1;
	}
	function delete_inv(){
		extract($_POST);
		$delete = $this->db->query("DELETE FROM inventory where id = ".$id);
		if($delete)
			return 1;
	}

	private function send_whatsapp_message($to, $message) {
        // --- GANTI DENGAN TOKEN FONNTE ANDA ---
        $api_token = 'UmKumP2v3iCm9PACBL4y'; // Ganti dengan Token Fonnte Anda

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://api.fonnte.com/send',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => array(
            'target' => $to,
            'message' => $message, 
            'countryCode' => '62', //optional
          ),
          CURLOPT_HTTPHEADER => array(
            'Authorization: ' . $api_token // Menggunakan token Fonnte Anda
          ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        
        // Optional: Log untuk debugging
        file_put_contents('whatsapp_log.txt', date('Y-m-d H:i:s') . " - To: $to, Response: $response\n", FILE_APPEND);
        
        return $response;
    }

    function reset_password() {
    // 1. Ambil data dari form secara aman
    $username = $_POST['username'];
    $new_password = $_POST['new_password'];

    // 2. Cari user berdasarkan username menggunakan prepared statement
    $stmt_find = $this->db->prepare("SELECT id, type FROM users WHERE username = ?");
    $stmt_find->bind_param("s", $username);
    $stmt_find->execute();
    $result = $stmt_find->get_result();

    // 3. Cek apakah user ditemukan
    if ($result->num_rows > 0) {
        // User ditemukan, lanjutkan proses update password
        $user_data = $result->fetch_assoc();
            
            // Asumsi: tipe admin adalah 1. Sesuaikan jika berbeda.
            if($user_data['type'] != 1) {
                // Jika user BUKAN admin, kembalikan kode error 3
                echo 3; 
                return;
            }
        // 4. HASH PASSWORD BARU (Sangat Penting untuk Keamanan)
        // Password tidak disimpan sebagai teks biasa, tapi di-hash.
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // 5. Update password di database menggunakan prepared statement
        $stmt_update = $this->db->prepare("UPDATE users SET password = ? WHERE username = ?");
        $stmt_update->bind_param("ss", $hashed_password, $username);
        
        if ($stmt_update->execute()) {
            return 1; // Sukses
        } else {
            return "Gagal mengupdate password: " . $stmt_update->error; // Gagal
        }

    } else {
        // Jika username tidak ditemukan
        return 2; 
    }
}

function save_attendance_settings(){
    extract($_POST);
    
    // Gunakan prepared statement untuk keamanan
    $stmt = $this->db->prepare("UPDATE system_settings SET office_latitude = ?, office_longitude = ?, attendance_radius = ? WHERE id = 1");
    if($stmt === false) { return "Prepare failed: " . $this->db->error; }

    $stmt->bind_param("ssi", $office_latitude, $office_longitude, $attendance_radius);
    
    if($stmt->execute()){
        return 1; // Sukses
    } else {
        return "Execute failed: " . $stmt->error; // Gagal
    }
}

function delete_user(){
    // Mengambil ID dari POST request dengan aman
    $id = $_POST['id'];

    // Menggunakan prepared statement untuk mencegah SQL Injection
    $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
    if ($stmt === false) {
        return "Gagal menyiapkan query: " . $this->db->error;
    }

    // Bind parameter ID sebagai integer
    $stmt->bind_param("i", $id);

    // Eksekusi query
    if($stmt->execute()){
        return 1; // Kirim sinyal sukses
    } else {
        return "Gagal menghapus data: " . $stmt->error; // Kirim pesan error jika gagal
    }
}

}

