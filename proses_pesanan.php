<?php
// proses_pesanan.php (Versi Final dengan Alamat)

session_start();
include("koneksi/koneksi.php");
header('Content-Type: application/json');

// Validasi Sesi
if (!isset($_SESSION['id_admin'])) {
    echo json_encode(['success' => false, 'message' => 'Sesi tidak valid.']);
    exit();
}

// Validasi Input
$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['midtrans_result']) || !isset($input['shipping_details'])) {
    echo json_encode(['success' => false, 'message' => 'Data input tidak lengkap.']);
    exit();
}

// Ambil data dari Midtrans
$midtransResult = $input['midtrans_result'];
$midtransCode = $midtransResult['order_id'];
$total = $midtransResult['gross_amount'];
$status = $midtransResult['transaction_status'];

// Ambil data dari sesi
$userId = $_SESSION['id_admin'];
$namaPelanggan = $_SESSION['username'];

// Ambil data alamat pengiriman dari input
$shipping = $input['shipping_details'];
$provinsi = $shipping['provinsi'];
$kota = $shipping['kota'];
$kecamatan = $shipping['kecamatan'];
$detailAlamat = $shipping['detail'];
$kurir = $shipping['kurir'];
$ongkir = $shipping['ongkir'];

$tanggal = date('Y-m-d H:i:s');
$id_transaksi_baru = 'TRX-' . time() . strtoupper(bin2hex(random_bytes(2)));

// Mulai transaksi database
mysqli_begin_transaction($koneksi);

try {
    // 1. Perbarui query INSERT untuk tabel 'transaksi' dengan kolom alamat
    $query_transaksi = "INSERT INTO transaksi 
        (id, id_user, nama_pelanggan, kode_midtrans, tanggal, total, status, provinsi, kota, kecamatan, detail_alamat, kurir, ongkir) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
    $stmt_transaksi = mysqli_prepare($koneksi, $query_transaksi);
    
    // 2. Perbarui bind_param sesuai jumlah kolom baru
    mysqli_stmt_bind_param($stmt_transaksi, "sisssdsiiissi", 
        $id_transaksi_baru, $userId, $namaPelanggan, $midtransCode, $tanggal, $total, $status, 
        $provinsi, $kota, $kecamatan, $detailAlamat, $kurir, $ongkir
    );
    mysqli_stmt_execute($stmt_transaksi);

    if (mysqli_stmt_affected_rows($stmt_transaksi) < 1) {
        throw new Exception("Gagal memasukkan data ke tabel transaksi.");
    }

    // Proses pemindahan dari keranjang ke history (tidak ada perubahan di sini)
    $query_keranjang = "SELECT nama, jumlah, harga FROM keranjang_user WHERE id_user = ?";
    $stmt_keranjang = mysqli_prepare($koneksi, $query_keranjang);
    mysqli_stmt_bind_param($stmt_keranjang, "i", $userId);
    mysqli_stmt_execute($stmt_keranjang);
    $result_keranjang = mysqli_stmt_get_result($stmt_keranjang);
    $items_keranjang = mysqli_fetch_all($result_keranjang, MYSQLI_ASSOC);

    if (empty($items_keranjang)) throw new Exception("Keranjang belanja kosong.");

    $query_history = "INSERT INTO history (id_transaksi, nama, jumlah, harga, total) VALUES (?, ?, ?, ?, ?)";
    $stmt_history = mysqli_prepare($koneksi, $query_history);
    foreach ($items_keranjang as $item) {
        $total_per_item = $item['harga'] * $item['jumlah'];
        mysqli_stmt_bind_param($stmt_history, "ssidd", $id_transaksi_baru, $item['nama'], $item['jumlah'], $item['harga'], $total_per_item);
        mysqli_stmt_execute($stmt_history);
    }
    
    // Kosongkan keranjang (tidak ada perubahan di sini)
    $query_hapus_keranjang = "DELETE FROM keranjang_user WHERE id_user = ?";
    $stmt_hapus = mysqli_prepare($koneksi, $query_hapus_keranjang);
    mysqli_stmt_bind_param($stmt_hapus, "i", $userId);
    mysqli_stmt_execute($stmt_hapus);

    // Commit jika semua berhasil
    mysqli_commit($koneksi);

    echo json_encode(['success' => true, 'message' => 'Pesanan berhasil diproses.', 'order_id' => $midtransCode]);

} catch (Exception $e) {
    mysqli_rollback($koneksi);
    error_log("Gagal proses pesanan: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Debug Info: ' . $e->getMessage()]); // Biarkan mode debug untuk sementara
}
?>