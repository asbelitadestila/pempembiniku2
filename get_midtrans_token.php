<?php
// get_midtrans_token.php

session_start();
include("koneksi/koneksi.php");
require './vendor/autoload.php';

header('Content-Type: application/json');

// 1. Validasi Input dan Sesi
if (!isset($_POST['subtotal']) || !isset($_POST['ongkir']) || !isset($_SESSION['id_admin'])) {
    echo json_encode(['error' => 'Akses ditolak atau data tidak lengkap.']);
    exit();
}

$user_id = $_SESSION['id_admin'];
$subtotal = (float)$_POST['subtotal'];
$ongkir = (float)$_POST['ongkir'];
$grand_total = $subtotal + $ongkir;

// 2. Ambil detail item dari keranjang (sama seperti sebelumnya)
$query_keranjang = "SELECT * FROM keranjang_user WHERE id_user = ?";
$stmt_keranjang = mysqli_prepare($koneksi, $query_keranjang);
mysqli_stmt_bind_param($stmt_keranjang, "i", $user_id);
mysqli_stmt_execute($stmt_keranjang);
$result_keranjang = mysqli_stmt_get_result($stmt_keranjang);
$items = mysqli_fetch_all($result_keranjang, MYSQLI_ASSOC);

if (empty($items)) {
    echo json_encode(['error' => 'Keranjang Anda kosong.']);
    exit();
}

// 3. Siapkan detail untuk Midtrans
\Midtrans\Config::$serverKey = 'SB-Mid-server-C1ta5HP9_KFpsSrBQaSJP3zC'; // Ganti dengan Server Key Anda
\Midtrans\Config::$isProduction = false;
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;

$item_details = [];
foreach ($items as $item) {
    $item_details[] = [
        'id' => $item['id_produk'],
        'price' => $item['harga'],
        'quantity' => $item['jumlah'],
        'name' => $item['nama'],
    ];
}

// Tambahkan ongkir sebagai item terpisah (praktik terbaik)
if ($ongkir > 0) {
    $item_details[] = [
        'id' => 'ONGKIR',
        'price' => $ongkir,
        'quantity' => 1,
        'name' => 'Biaya Pengiriman',
    ];
}

$transaction_details = [
    'order_id' => 'PEMPEK-' . time(),
    'gross_amount' => $grand_total, // Gunakan total akhir
];

$customer_details = [
    'first_name' => $_SESSION['username'],
    'phone' => $_SESSION['noHp'],
    'address' => $_SESSION['alamat']
];

$transaction = [
    'transaction_details' => $transaction_details,
    'item_details' => $item_details,
    'customer_details' => $customer_details
];

// 4. Buat dan Kirim Snap Token
try {
    $snapToken = \Midtrans\Snap::getSnapToken($transaction);
    echo json_encode(['snapToken' => $snapToken]);
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(['error' => 'Gagal memproses pembayaran. Silakan coba lagi.']);
}