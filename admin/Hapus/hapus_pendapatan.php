<?php

$id_pendapatan =  $_GET['id'];
$id_bulan =  $_GET['bulan_id'];

$sql_pendapatan = 'SELECT total FROM pendapatan WHERE id = "'. $id_pendapatan. '"';
$result = $koneksi->query($sql_pendapatan);

if ($result->num_rows > 0) {
  // output data of each row
    $row = $result->fetch_assoc();
    $total_pendapatan = $row['total'];
    

    $sql_bulan = 'SELECT total FROM bulan WHERE id = "'. $id_bulan. '"';
    $result2 = $koneksi->query($sql_bulan);

    if ($result2->num_rows > 0) {
      $row2 = $result2->fetch_assoc();
      $total_profit = $row2['total'];      
    }

    $hasil = intval($total_profit) - intval($total_pendapatan);

    $sql_update_bulan = 'UPDATE bulan SET total = "'. $hasil. '" WHERE id = "'. $id_bulan. '"';

    if ($koneksi->query($sql_update_bulan) === TRUE) {

        $koneksi->query("DELETE FROM pendapatan WHERE id='$id_pendapatan'");
        echo "<script>alert('Data berhasil dihapus.');</script>";
        echo "<script>location='index.php?halaman=detail-pendapatan&id=" . $id_bulan . "'; </script>";
    
    } else {
      echo "Error: " . $sql_update_bulan . "<br>" . $koneksi->error;
    }
} else {
    echo "data tidak ditemukan";
}


echo "<script>alert('data berhasil dihapus'); </script>";
echo "<script>location='index.php?halaman=detail-pendapatan&id=" . $id_bulan . "'; </script>";

?>