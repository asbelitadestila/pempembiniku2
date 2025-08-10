<?php

session_start();
include 'cek_role.php';

// Cek apakah role pengguna adalah admin
cek_role('user');

include 'koneksi/koneksi.php';
// $user = $_SESSION['username'];
$user_id = $_SESSION['id_admin'];

$sql = "SELECT * FROM user WHERE id_admin = '$user_id'";
$query_run = mysqli_query($koneksi, $sql);

if (mysqli_num_rows($query_run) > 0) {
    $user = mysqli_fetch_assoc($query_run);
} else {
    header('Location: login.php');
    exit;
}

$sql_history = "SELECT * FROM transaksi WHERE id_user = '$user_id'";
$sql_history_run = mysqli_query($koneksi, $sql_history);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halaman Profil</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <!-- Tailwind CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gray-50">

<!-- Navbar start-->
<nav class="flex items-center justify-between bg-red-700 px-8 py-2 fixed w-full top-0 left-0 z-50 shadow">
    <img src="./assets/foto/Logoo.png" class="w-20">
    <div class="hidden md:flex space-x-8 navbar-menu">
        <a href="index.php" class="text-white font-bold hover:text-yellow-200">Beranda</a>
        <a href="#about" class="text-white font-bold hover:text-yellow-200">About</a>
        <a href="#produk" class="text-white font-bold hover:text-yellow-200">Produk</a>
        <a href="#kontak" class="text-white font-bold hover:text-yellow-200">Kontak</a>
    </div>
    <div class="flex items-center space-x-4 navbar-icon">
        <div class="users">
            <?php if(isset($_SESSION['username'])){ ?>
                <a href="keranjang.php" class="relative">
                    <i class="fas fa-shopping-cart text-white text-xl"></i>
                    <?php
                        $sql_keranjang = "SELECT COUNT(*) as total FROM keranjang_user WHERE id_user = '$user_id'";
                        $result=mysqli_query($koneksi, $sql_keranjang);
                        $data=mysqli_fetch_assoc($result);
                    ?>
                    <span class="absolute -top-2 -right-2 bg-yellow-400 text-xs rounded-full px-2 py-0.5 text-black font-bold" id="jumlah_pesanan"><?php echo $data['total']; ?></span>
                </a>
            <?php } ?>
        </div>
        <a href="#" id="btn-user"><i class="fas fa-user text-white text-xl"></i></a>
        <a href="#" id="btn-menu" class="md:hidden"><i class="fas fa-bars text-white text-xl"></i></a>
    </div>
    <div class="user absolute right-8 top-16 bg-white rounded shadow-lg py-2 px-4 hidden">
        <?php if(isset($_SESSION['username'])){ ?>
        <li class="list-none mb-2"><a href="profil.php" class="text-red-700 font-semibold">Profil</a></li>
        <li class="list-none"><a href="admin/logout.php" class="text-red-700 font-semibold">Logout</a></li>
        <?php } else { ?>
        <li class="list-none mb-2"><a href="admin/login.php" class="text-red-700 font-semibold">Login</a></li>
        <li class="list-none"><a href="daftar.php" class="text-red-700 font-semibold">Daftar</a></li>
        <?php } ?>
    </div>
</nav>
<!-- Navbar end-->

<div class="max-w-4xl mx-auto mt-32 px-2">
    <div class="flex flex-col gap-8">
        <!-- Main Content -->
        <div class="bg-white shadow rounded-lg">
            <div class="border-b px-6 py-4">
                <h3 class="text-center text-xl font-bold">Profil Pengguna</h3>
            </div>
            <div class="px-6 py-6">
                <form action="update_profil.php" method="POST" class="space-y-4">
                    <div>
                        <label for="username" class="block font-semibold mb-1">Username</label>
                        <input type="text" name="username" id="username" class="w-full border rounded px-3 py-2 bg-gray-100" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                    </div>
                    <div>
                        <label for="no_hp" class="block font-semibold mb-1">No. HP</label>
                        <input type="text" name="no_hp" id="no_hp" class="w-full border rounded px-3 py-2" value="<?php echo htmlspecialchars($user['noHp']); ?>" required>
                    </div>
                    <div>
                        <label for="email" class="block font-semibold mb-1">Email</label>
                        <input type="text" name="email" id="email" class="w-full border rounded px-3 py-2" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    <div>
                        <label for="alamat" class="block font-semibold mb-1">Alamat</label>
                        <input type="text" name="alamat" id="alamat" class="w-full border rounded px-3 py-2" value="<?php echo htmlspecialchars($user['alamat']); ?>" required>
                    </div>
                    <div>
                        <label for="password" class="block font-semibold mb-1">Password Baru</label>
                        <input type="password" name="password" id="password" class="w-full border rounded px-3 py-2">
                        <small class="text-gray-500">Kosongkan jika tidak ingin mengubah password.</small>
                    </div>
                    <div>
                        <label for="confirm_password" class="block font-semibold mb-1">Konfirmasi Password Baru</label>
                        <input type="password" name="confirm_password" id="confirm_password" class="w-full border rounded px-3 py-2">
                    </div>
                    <div class="flex justify-center">
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 rounded transition">Perbarui Profil</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <div class="border-b pb-4 mb-4">
                <h3 class="text-center text-xl font-bold">Riwayat Pesanan</h3>
            </div>
            <div class="overflow-x-auto">
            <table class="min-w-full border border-gray-300 text-center">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-2 px-4 border">No</th>
                        <th class="py-2 px-4 border">ID transaksi</th>
                        <th class="py-2 px-4 border">Tanggal</th>
                        <th class="py-2 px-4 border">Total</th>
                        <th class="py-2 px-4 border">Status</th>
                        <th class="py-2 px-4 border">Opsi</th>
                    </tr>
                </thead>
                <tbody>
                <?php       
                    $num = 1;
                    if(mysqli_num_rows($sql_history_run) > 0){
                        foreach($sql_history_run as $row){
                ?>
                    <tr class="border-b">
                        <td class="py-2 px-4 border"><?php echo $num ?></td>
                        <td class="py-2 px-4 border"><span class="text-uppercase">ID-<?php echo $row['id'] ?></span></td>
                        <td class="py-2 px-4 border"><?php echo $row['tanggal'] ?></td>
                        <td class="py-2 px-4 border"><?php echo $row['total'] ?></td>
                        <td class="py-2 px-4 border"><?php echo $row['status'] ?></td>
                        <td class="py-2 px-4 border">
                            <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded detail-btn" data-id="<?php echo $row['id'] ?>" data-modal-toggle="exampleModal">Detail</button>
                        </td>
                    </tr>
                <?php
                    $num++;
                        }
                    } else {
                        echo "
                        <tr>
                            <td colspan='6' class='py-2 px-4 border text-center text-gray-500'>tidak ada data</td>
                        </tr>
                        ";
                    }
                ?>     
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>   

<!-- Modal -->
<div id="exampleModal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-lg">
        <div class="flex justify-between items-center border-b px-6 py-4">
            <h5 class="text-lg font-bold">DETAIL PRODUK</h5>
            <button type="button" class="text-gray-400 hover:text-gray-600 text-2xl font-bold focus:outline-none" onclick="document.getElementById('exampleModal').classList.add('hidden')">&times;</button>
        </div>
        <div class="px-6 py-4">
            <table class="min-w-full border border-gray-300 text-center">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-2 px-4 border">No.</th>
                        <th class="py-2 px-4 border">Nama</th>
                        <th class="py-2 px-4 border">Jumlah</th>
                        <th class="py-2 px-4 border">Harga</th>
                    </tr>
                </thead>
                <tbody id="historyModalBody">
                    <!-- Konten akan dimuat di sini -->
                </tbody>
            </table>
        </div>
        <div class="flex justify-end border-t px-6 py-3">
            <button type="button" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded" onclick="document.getElementById('exampleModal').classList.add('hidden')">Close</button>
        </div>
    </div>
</div>

<script type="text/javascript">
   $(document).ready(function($){
        $('.detail-btn').on('click', function(e){
            e.preventDefault();
            var id_transaksi = $(this).data('id'); 
            $.ajax({
                type: "POST",
                url: "get_history.php",
                data: {id_transaksi: id_transaksi},                    
                success: function (response) {
                    $('#historyModalBody').html(response);
                    document.getElementById('exampleModal').classList.remove('hidden');
                }
            });
        });
    });
</script>
<script src="assets/js/main.js"></script>
</body>
</html>