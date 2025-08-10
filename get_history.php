<?php
include 'koneksi/koneksi.php';

if(isset($_POST['id_transaksi'])){
    $id_transaksi = $_POST['id_transaksi'];

    $query = "SELECT * FROM history WHERE id_transaksi = '$id_transaksi'";
    $result = mysqli_query($koneksi, $query);
    $num = 1;
    if(mysqli_num_rows($result) > 0){
        while($row = mysqli_fetch_assoc($result)){
            echo "
            <tr>
                <th scope='row'>".$num."</th>
                <td>".$row['nama']."</td>
                <td>".$row['jumlah']."</td>
                <td>Rp. ".number_format($row['harga'], 0, ',', '.')."</td>
            </tr>";
            $num++;
        }
    } else {
        echo "<tr><td colspan='4'>Tidak ada data</td></tr>";
    }
}
?>
