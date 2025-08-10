<?php
include("koneksi/koneksi.php");

$id_user = $_POST['id_user'];

// Query untuk memasukkan data ke tabel `transaksi`
$sql = "DELETE FROM keranjang_user WHERE id_user = '$id_user'";

if ($koneksi->query($sql) === TRUE) {
    echo "keranjang berhasil dihapus!";
} else {
    echo "Error: " . $sql . "<br>" . $koneksi->error;
}

// Menutup koneksi
$koneksi->close();

?>