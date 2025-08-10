<?php
session_start();

$id_produk = $_GET['id_produk'];

if (isset($_SESSION['keranjang_belanja'[$id_produk]])) {
    $_SESSION['keranjang_belanja'][$id_produk] += 1;
} else {
    $_SESSION['keranjang_belanja'][$id_produk] = 1;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>

</body>

</html>