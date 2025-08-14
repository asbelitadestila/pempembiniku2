<?php
include("../../koneksi/koneksi.php");

// $id_transaksi =  $_GET['id'];

// // Query untuk memasukkan data ke tabel `transaksi`
// $sql = "UPDATE transaksi SET status = 'Sedang Dikirim' where id = '$id_transaksi' ";

// if ($koneksi->query($sql) === TRUE) {
//     header('location:../index.php?halaman=transaksi');
// } else {
//     echo "Error: " . $sql . "<br>" . $koneksi->error;
// }

// // Menutup koneksi
// $koneksi->close();

// file: admin/ajax/update_resi.php
session_start();
include("../../koneksi/koneksi.php"); // Sesuaikan path ke file koneksi Anda

header('Content-Type: application/json');

// Validasi: Pastikan admin yang login
// if (!isset($_SESSION['id_admin'])) {
//     echo json_encode(['success' => false, 'message' => 'Akses ditolak.']);
//     exit();
// }

// Validasi input dari form modal
if (!isset($_POST['id_transaksi']) || !isset($_POST['no_resi'])) {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap.']);
    exit();
}

$id_transaksi = $_POST['id_transaksi'];
$no_resi = trim($_POST['no_resi']);
// $kurir_code = trim($_POST['kurir_code']);
$status_baru = 'sedang dikirim';

// Pastikan input tidak kosong
if (empty($id_transaksi) || empty($no_resi)) {
    echo json_encode(['success' => false, 'message' => 'Semua kolom harus diisi.']);
    exit();
}

// Query untuk update data transaksi
$query = "UPDATE transaksi SET no_resi = ?, status = ? WHERE id = ?";
$stmt = mysqli_prepare($koneksi, $query);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "sss", $no_resi, $status_baru, $id_transaksi);
    
    if (mysqli_stmt_execute($stmt)) {
        // Cek apakah ada baris yang terpengaruh
        if (mysqli_stmt_affected_rows($stmt) > 0) {
            echo json_encode(['success' => true, 'message' => 'Data pengiriman berhasil disimpan. Status diubah menjadi "Sedang Dikirim".']);
        } else {
            echo json_encode(['success' => false, 'message' => 'ID Transaksi tidak ditemukan atau tidak ada data yang berubah.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menjalankan query.']);
    }
    
    mysqli_stmt_close($stmt);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal mempersiapkan query.']);
}

mysqli_close($koneksi);

?>