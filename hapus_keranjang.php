<?php

session_start();

$id_produk = $_GET['id_produk'];

unset($_SESSION['keranjang_belanja'][$id_produk]);

echo "<script>alert('produk berhasil dihapus');</script>";
echo "<script>location='keranjang.php';</script>";

?>