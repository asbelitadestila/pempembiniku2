<?php
// include 'koneksi/koneksi.php';

// if(isset($_POST['id_transaksi'])){
//     $id_transaksi = $_POST['id_transaksi'];

//     $query = "SELECT * FROM history WHERE id_transaksi = '$id_transaksi'";
//     $result = mysqli_query($koneksi, $query);
//     $num = 1;
//     if(mysqli_num_rows($result) > 0){
//         while($row = mysqli_fetch_assoc($result)){
//             echo "
//             <tr>
//                 <th scope='row'>".$num."</th>
//                 <td>".$row['nama']."</td>
//                 <td>".$row['jumlah']."</td>
//                 <td>Rp. ".number_format($row['harga'], 0, ',', '.')."</td>
//             </tr>";
//             $num++;
//         }
//     } else {
//         echo "<tr><td colspan='4'>Tidak ada data</td></tr>";
//     }
// }


// get_history.php
    session_start();
    include("koneksi/koneksi.php");
    header('Content-Type: application/json');

    // Validasi
    if (!isset($_SESSION['id_admin']) || !isset($_POST['id_transaksi'])) {
        echo json_encode(['error' => 'Akses ditolak atau ID Transaksi tidak ada.']);
        exit();
    }

    $id_transaksi = $_POST['id_transaksi'];
    $id_user = $_SESSION['id_admin'];

    $response = [];

    // 1. Ambil data utama dari tabel 'transaksi'
    $query_transaksi = "SELECT * FROM transaksi WHERE id = ? AND id_user = ?";
    $stmt_transaksi = mysqli_prepare($koneksi, $query_transaksi);
    mysqli_stmt_bind_param($stmt_transaksi, "si", $id_transaksi, $id_user);
    mysqli_stmt_execute($stmt_transaksi);
    $result_transaksi = mysqli_stmt_get_result($stmt_transaksi);
    $transaksi_data = mysqli_fetch_assoc($result_transaksi);

    if ($transaksi_data) {
        $response['transaksi'] = $transaksi_data;

        // 2. Ambil semua item produk dari tabel 'history' yang berelasi
        $query_history = "SELECT * FROM history WHERE id_transaksi = ?";
        $stmt_history = mysqli_prepare($koneksi, $query_history);
        mysqli_stmt_bind_param($stmt_history, "s", $id_transaksi);
        mysqli_stmt_execute($stmt_history);
        $result_history = mysqli_stmt_get_result($stmt_history);
        $history_items = mysqli_fetch_all($result_history, MYSQLI_ASSOC);
        
        $response['items'] = $history_items;
        
        echo json_encode($response);

    } else {
        echo json_encode(['error' => 'Data transaksi tidak ditemukan.']);
    }


?>
