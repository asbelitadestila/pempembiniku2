<?php
include ("../../koneksi/koneksi.php");// Koneksi ke database

$id_user =  $_GET['id'];

$koneksi->query("DELETE FROM user WHERE id_admin = '$id_user'");

echo "<script>alert('data berhasil dihapus'); </script>";
echo "<script>location='../index.php?halaman=Pelanggan'; </script>";

?>