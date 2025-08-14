<?php
// proses_pesanan.php

session_start();
include("koneksi/koneksi.php");
header('Content-Type: application/json');

if (!isset($_SESSION['id_admin'])) {
    echo json_encode(['success' => false, 'message' => 'Sesi tidak valid.']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['midtrans_result'])) {
    echo json_encode(['success' => false, 'message' => 'Data tidak valid.']);
    exit();
}

$midtransResult = $input['midtrans_result'];
$userId = $_SESSION['id_admin'];
$namaPelanggan = $_SESSION['username'];
$midtransCode = $midtransResult['order_id'];
$total = $midtransResult['gross_amount'];
$status = $midtransResult['transaction_status'];
$tanggal = date('Y-m-d H:i:s');

// ================= PERUBAHAN DIMULAI DI SINI =================

// 1. Buat ID Transaksi acak DI SINI, sebelum masuk ke database.
//    Format: TRX-<timestamp>-<4 karakter acak>
$id_transaksi_baru = 'TRX-' . time() . strtoupper(bin2hex(random_bytes(2)));

// Mulai transaksi database
mysqli_begin_transaction($koneksi);

try {
    // 2. Masukkan data ke tabel 'transaksi', sekarang DENGAN ID yang kita buat.
    //    Pastikan tabel `transaksi` Anda memiliki kolom `id` untuk menampung ID ini.
    $query_transaksi = "INSERT INTO transaksi (id, id_user, nama_pelanggan, kode_midtrans, tanggal, total, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt_transaksi = mysqli_prepare($koneksi, $query_transaksi);
    
    // 3. Perbarui bind_param: tambahkan 's' untuk ID string dan variabelnya di awal.
    mysqli_stmt_bind_param($stmt_transaksi, "sisssds", $id_transaksi_baru, $userId, $namaPelanggan, $midtransCode, $tanggal, $total, $status);
    mysqli_stmt_execute($stmt_transaksi);

    // Cek apakah insert berhasil. Jika tidak, mysqli_stmt_affected_rows akan < 1.
    if (mysqli_stmt_affected_rows($stmt_transaksi) < 1) {
        throw new Exception("Gagal memasukkan data ke tabel transaksi.");
    }

    // 4. HAPUS baris ini karena kita tidak lagi menggunakan AUTO_INCREMENT.
    // $id_transaksi_baru = mysqli_insert_id($koneksi); // <-- DIHAPUS

    // Ambil semua item dari keranjang pengguna (logika ini tetap sama)
    $query_keranjang = "SELECT nama, jumlah, harga FROM keranjang_user WHERE id_user = ?";
    $stmt_keranjang = mysqli_prepare($koneksi, $query_keranjang);
    mysqli_stmt_bind_param($stmt_keranjang, "i", $userId);
    mysqli_stmt_execute($stmt_keranjang);
    $result_keranjang = mysqli_stmt_get_result($stmt_keranjang);
    $items_keranjang = mysqli_fetch_all($result_keranjang, MYSQLI_ASSOC);

    if (empty($items_keranjang)) {
        throw new Exception("Keranjang belanja kosong.");
    }

    // 5. Masukkan setiap item ke tabel 'history', GUNAKAN ID YANG SUDAH KITA BUAT.
    $query_history = "INSERT INTO history (id_transaksi, nama, jumlah, harga, total) VALUES (?, ?, ?, ?, ?)";
    $stmt_history = mysqli_prepare($koneksi, $query_history);

    foreach ($items_keranjang as $item) {
        $total_per_item = $item['harga'] * $item['jumlah'];
        // Perhatikan $id_transaksi_baru di sini menggunakan variabel yang kita buat di awal.
        mysqli_stmt_bind_param($stmt_history, "ssidd", $id_transaksi_baru, $item['nama'], $item['jumlah'], $item['harga'], $total_per_item);
        mysqli_stmt_execute($stmt_history);
    }
    
    // Kosongkan keranjang pengguna (logika ini tetap sama)
    $query_hapus_keranjang = "DELETE FROM keranjang_user WHERE id_user = ?";
    $stmt_hapus = mysqli_prepare($koneksi, $query_hapus_keranjang);
    mysqli_stmt_bind_param($stmt_hapus, "i", $userId);
    mysqli_stmt_execute($stmt_hapus);

    // Jika semua query berhasil, commit transaksi
    mysqli_commit($koneksi);

    echo json_encode(['success' => true, 'message' => 'Pesanan berhasil diproses.', 'order_id' => $midtransCode]);

} catch (Exception $e) {
    // Jika ada error, batalkan semua perubahan (rollback)
    mysqli_rollback($koneksi);
    error_log("Gagal proses pesanan: " . $e->getMessage()); // Catat error untuk debugging
    // echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan saat memproses pesanan.']);
    echo json_encode(['success' => false, 'message' => 'Debug Info: ' . $e->getMessage()]);
}

// ================== AKHIR DARI PERUBAHAN ==================