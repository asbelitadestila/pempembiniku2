<?php
include 'koneksi/koneksi.php';

header('Content-Type: application/json'); // Mengatur header respons sebagai JSON

$data = json_decode($_POST['data'], true);

$response = ['success' => false]; // Default response

if ($data && is_array($data)) {
    $is_stock_sufficient = true;

    // Periksa stok untuk setiap produk
    foreach ($data as $item) {
        $nama = $koneksi->real_escape_string($item['nama']);
        $jumlah = $koneksi->real_escape_string($item['jumlah']);
        
        // Cek stok produk
        $sql_check_stock = "SELECT stok_produk FROM produk WHERE nama_produk = '$nama'";
        $result = $koneksi->query($sql_check_stock);
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if ($row['stok_produk'] < $jumlah) {
                $is_stock_sufficient = false;
                break; // Jika ada stok yang kurang, berhenti dan batalkan
            }
        } else {
            // Jika produk tidak ditemukan, batalkan
            $is_stock_sufficient = false;
            break;
        }
    }

    // Jika stok mencukupi, lanjutkan proses
    if ($is_stock_sufficient) {
        // Jika berhasil, ubah nilai success menjadi true
        $response['success'] = true;
        // Lanjutkan ke proses insert dan update stok jika stok mencukupi
        // (Tambahkan logika insert ke tabel history dan update stok di sini)
    }
}

// Mengirimkan respons JSON kembali ke client
echo json_encode($response);
?>
