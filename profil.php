<?php
session_start();
include 'koneksi/koneksi.php';

// Keamanan: Cek jika pengguna sudah login dan rolenya adalah 'user'
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header('Location: admin/login.php');
    exit();
}

$user_id = $_SESSION['id_admin'];
$errors = [];
$success_message = '';

// --- Logika untuk Update Profil ---
if (isset($_POST['update_profil'])) {
    // Ambil dan bersihkan data
    $noHp = trim($_POST['no_hp']);
    $email = trim($_POST['email']);
    $alamat = trim($_POST['alamat']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validasi dasar
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid.";
    }

    // Logika update password
    $password_update_sql = "";
    if (!empty($password)) {
        if (strlen($password) < 6) {
            $errors[] = "Password baru minimal harus 6 karakter.";
        } elseif ($password !== $confirm_password) {
            $errors[] = "Konfirmasi password baru tidak cocok.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $password_update_sql = ", password = ?";
        }
    }

    if (empty($errors)) {
        // --- PENINGKATAN KEAMANAN: PREPARED STATEMENTS ---
        $query_update = "UPDATE user SET noHp = ?, email = ?, alamat = ? $password_update_sql WHERE id_admin = ?";
        $stmt_update = $koneksi->prepare($query_update);

        if (!empty($password)) {
            $stmt_update->bind_param("ssssi", $noHp, $email, $alamat, $hashedPassword, $user_id);
        } else {
            $stmt_update->bind_param("sssi", $noHp, $email, $alamat, $user_id);
        }

        if ($stmt_update->execute()) {
            // Update session data
            $_SESSION['noHp'] = $noHp;
            $_SESSION['alamat'] = $alamat;
            $success_message = "Profil berhasil diperbarui!";
        } else {
            $errors[] = "Gagal memperbarui profil. Silakan coba lagi.";
        }
    }
}


// --- Mengambil data pengguna terbaru ---
$stmt_user = $koneksi->prepare("SELECT * FROM user WHERE id_admin = ?");
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user = $stmt_user->get_result()->fetch_assoc();

// --- Mengambil riwayat transaksi ---
$history = [];
$stmt_history = $koneksi->prepare("SELECT id, tanggal, total, status FROM transaksi WHERE id_user = ? ORDER BY tanggal DESC");
$stmt_history->bind_param("i", $user_id);
$stmt_history->execute();
$result_history = $stmt_history->get_result();
while ($row = $result_history->fetch_assoc()) {
    $history[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - Pempek Biniku</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #FFFAE7; }
        .font-playfair { font-family: 'Playfair Display', serif; }
    </style>
</head>
<body class="bg-[#FFFAE7]">

    <!-- Navbar (diasumsikan sama seperti di index.php) -->
    <nav class="flex items-center justify-between bg-red-700 px-4 sm:px-8 py-2 fixed w-full top-0 left-0 z-50 shadow-md">
        <a href="index.php"><img src="./assets/foto/logo.png" class="w-20" alt="Logo Pempek Biniku"></a>
        <div class="hidden md:flex space-x-8">
            <a href="index.php" class="text-white font-bold hover:text-yellow-200">Beranda</a>
        </div>
        <div class="flex items-center space-x-4">
            <a href="keranjang.php" class="relative text-white"><i class="fas fa-shopping-cart text-xl"></i></a>
            <div class="relative">
                <button id="btn-user" class="focus:outline-none"><i class="fas fa-user text-white text-xl"></i></button>
                <div id="user-dropdown" class="hidden absolute top-full right-0 mt-3 w-48 bg-white rounded-lg shadow-xl py-2 z-50">
                    <a href="profil.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Profil Saya</a>
                    <a href="admin/logout.php" class="block px-4 py-2 text-red-600 hover:bg-red-50">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="pt-24 pb-12">
        <div class="container mx-auto px-4">
            <!-- <h1 class="font-playfair text-3xl font-bold text-red-600 mb-8">Profil Saya</h1> -->

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Kolom Kiri: Form Update Profil -->
                <div class="lg:col-span-2">
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <h2 class="text-xl font-bold text-gray-800 border-b pb-4 mb-6">Informasi Akun</h2>
                        
                        <?php if (!empty($errors)): ?>
                            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md mb-6">
                                <ul class="list-disc list-inside text-sm">
                                    <?php foreach ($errors as $error) echo "<li>$error</li>"; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        <?php if ($success_message): ?>
                            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md mb-6">
                                <p><?php echo $success_message; ?></p>
                            </div>
                        <?php endif; ?>

                        <form action="profil.php" method="POST" class="space-y-4">
                            <div>
                                <label for="username" class="block font-semibold text-sm mb-1">Username</label>
                                <input type="text" id="username" class="w-full border rounded px-3 py-2 bg-gray-100 cursor-not-allowed" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                            </div>
                            <div>
                                <label for="email" class="block font-semibold text-sm mb-1">Email</label>
                                <input type="email" name="email" id="email" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-red-500" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                            <div>
                                <label for="no_hp" class="block font-semibold text-sm mb-1">No. HP</label>
                                <input type="text" name="no_hp" id="no_hp" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-red-500" value="<?php echo htmlspecialchars($user['noHp']); ?>" required>
                            </div>
                            <div>
                                <label for="alamat" class="block font-semibold text-sm mb-1">Alamat</label>
                                <textarea name="alamat" id="alamat" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-red-500" rows="3" required><?php echo htmlspecialchars($user['alamat']); ?></textarea>
                            </div>
                            <hr class="my-4">
                            <p class="font-semibold text-gray-700">Ubah Password</p>
                            <small class="text-gray-500 block -mt-2">Kosongkan jika tidak ingin mengubah password.</small>
                            <div>
                                <label for="password" class="block font-semibold text-sm mb-1">Password Baru</label>
                                <input type="password" name="password" id="password" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-red-500">
                            </div>
                            <div>
                                <label for="confirm_password" class="block font-semibold text-sm mb-1">Konfirmasi Password Baru</label>
                                <input type="password" name="confirm_password" id="confirm_password" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-red-500">
                            </div>
                            <div class="text-right pt-4">
                                <button type="submit" name="update_profil" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-6 rounded-lg transition">Perbarui Profil</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Kolom Kanan: Riwayat Pesanan -->
                <div class="lg:col-span-1">
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <h2 class="text-xl font-bold text-gray-800 border-b pb-4 mb-6">Riwayat Pesanan</h2>
                        <div class="space-y-4 max-h-96 overflow-y-auto">
                            <?php if (empty($history)): ?>
                                <p class="text-center text-gray-500 py-4">Anda belum memiliki riwayat pesanan.</p>
                            <?php else: ?>
                                <?php foreach ($history as $order): ?>
                                <div class="border p-4 rounded-md hover:bg-gray-50">
                                    <div class="flex justify-between items-center">
                                        <p class="font-bold text-black">ID: #<?php echo $order['id']; ?></p>
                                        <span class="text-xs font-semibold px-2 py-1 rounded-full 
                                            <?php 
                                                switch(strtolower($order['status'])) {
                                                    case 'settlement': echo 'bg-green-100 text-green-800'; break;
                                                    case 'sedang dikirim': echo 'bg-sky-100 text-sky-800'; break;
                                                    case 'diproses': echo 'bg-yellow-100 text-yellow-800'; break;
                                                    case 'dibatalkan': echo 'bg-red-100 text-red-800'; break;
                                                    default: echo 'bg-gray-100 text-gray-800';
                                                }
                                            ?>">
                                            <?php echo htmlspecialchars(ucfirst($order['status'])); ?>
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-500 mt-1"><?php echo date('d F Y', strtotime($order['tanggal'])); ?></p>
                                    <p class="font-semibold mt-2">Total: Rp <?php echo number_format($order['total'], 0, ',', '.'); ?></p>
                                    <div class="flex gap-2 mt-2">
                                        <button class="text-sm text-red-500 font-semibold  detail-btn" data-id="<?php echo $order['id']; ?>">Lihat Detail</button>
                                        <p>|</p>
                                        <button class="text-sm text-red-500 font-semibold  detail-btn" data-id="<?php echo $order['id']; ?>">Lacak Paket</button>
                                    </div>
                                </div>
                                

                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal Detail Pesanan -->
    <div id="detailModal" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 hidden">
        <div class="bg-gray-50 rounded-xl shadow-2xl w-full max-w-2xl m-4 transform transition-all duration-300 ease-out">
            
            <div class="flex justify-between items-start border-b border-gray-200 px-6 py-4">
                <div>
                    <h5 class="text-xl font-bold text-gray-800">Detail Pesanan</h5>
                    <p class="text-sm text-gray-500">ID: <span id="modal-order-id">...</span></p>
                </div>
                <button type="button" class="text-gray-400 hover:text-red-600 text-3xl font-light" onclick="document.getElementById('detailModal').classList.add('hidden')">&times;</button>
            </div>
            
            <div id="modalBody" class="p-6 max-h-[70vh] overflow-y-auto">
                <div id="modal-content-container">
                    <div class="grid grid-cols-2 gap-4 mb-6 text-sm">
                        <div class="bg-white p-4 rounded-lg border">
                            <p class="font-semibold text-gray-600 mb-1">Pelanggan:</p>
                            <p class="font-bold text-gray-800" id="modal-customer-name">...</p>
                        </div>
                        <div class="bg-white p-4 rounded-lg border">
                            <p class="font-semibold text-gray-600 mb-1">Status Pembayaran:</p>
                            <span id="modal-status-badge" class="px-3 py-1 text-xs font-bold text-white rounded-full">...</span>
                        </div>
                    </div>

                    <h6 class="font-bold text-gray-700 mb-2">Rincian Produk</h6>
                    <div id="modal-items-container" class="space-y-3 border-t border-b border-gray-200 py-4">
                        </div>

                    <div class="mt-6 space-y-2 text-right">
                        <div class="flex justify-end items-center">
                            <p class="text-gray-600 mr-4">Subtotal Produk:</p>
                            <p class="font-semibold text-gray-800 w-32">Rp <span id="modal-subtotal">0</span></p>
                        </div>
                        <div class="flex justify-end items-center">
                            <p class="text-gray-600 mr-4">Biaya Pengiriman:</p>
                            <p class="font-semibold text-gray-800 w-32">Rp <span id="modal-shipping">0</span></p>
                        </div>
                        <div class="flex justify-end items-center mt-2 pt-2 border-t-2 border-dashed">
                            <p class="font-bold text-lg text-red-700 mr-4">Total Pesanan:</p>
                            <p class="font-bold text-lg text-red-700 w-32">Rp <span id="modal-grand-total">0</span></p>
                        </div>
                    </div>
                </div>
                <div id="modal-loading-state" class="text-center py-16 hidden">
                    <i class="fas fa-spinner fa-spin text-4xl text-red-600"></i>
                    <p class="mt-4 text-gray-600">Memuat detail...</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Dropdown menu
            $('#btn-user').on('click', function(e) {
                e.stopPropagation();
                $('#user-dropdown').toggleClass('hidden');
            });
            $(document).on('click', function() {
                $('#user-dropdown').addClass('hidden');
            });

            // AJAX untuk detail riwayat pesanan
            // Anda bisa menggunakan jQuery atau Vanilla JS. Ini versi jQuery yang disempurnakan.
            $('.detail-btn').on('click', function() {
                var id_transaksi = $(this).data('id');

                // Referensi elemen-elemen modal
                var modal = $('#detailModal');
                var modalContent = $('#modal-content-container');
                var modalLoading = $('#modal-loading-state');

                // 1. Tampilkan modal dan state loading
                modal.removeClass('hidden');
                modalContent.hide();
                modalLoading.show();

                // 2. Lakukan AJAX call untuk mendapatkan data JSON
                $.ajax({
                    type: "POST",
                    url: "get_history.php",
                    data: { id_transaksi: id_transaksi },
                    dataType: 'json', // Penting: beri tahu jQuery kita mengharapkan JSON
                    success: function(response) {
                        if (response.error) {
                            alert(response.error);
                            modal.addClass('hidden'); // Sembunyikan modal jika ada error
                            return;
                        }

                        // 3. Isi data ke placeholder di modal
                        var transaksi = response.transaksi;
                        var items = response.items;

                        $('#modal-order-id').text(transaksi.id);
                        $('#modal-customer-name').text(transaksi.nama_pelanggan);
                        
                        // Atur badge status
                        var statusBadge = $('#modal-status-badge');
                        statusBadge.text(transaksi.status);
                        if (transaksi.status === 'settlement' || transaksi.status === 'capture') {
                            statusBadge.removeClass('bg-yellow-500 bg-red-500').addClass('bg-green-500');
                        } else if (transaksi.status === 'pending') {
                            statusBadge.removeClass('bg-green-500 bg-red-500').addClass('bg-yellow-500');
                        } else {
                            statusBadge.removeClass('bg-green-500 bg-yellow-500').addClass('bg-red-500');
                        }

                        // 4. Bangun daftar item produk
                        var itemsContainer = $('#modal-items-container');
                        itemsContainer.empty(); // Kosongkan daftar item sebelumnya
                        var subtotalProduk = 0;

                        items.forEach(function(item) {
                            subtotalProduk += parseFloat(item.total);
                            var itemHtml = `
                                <div class="flex items-center text-sm">                                   
                                    <div class="flex-grow">
                                        <p class="font-bold text-gray-800">${item.nama}</p>
                                        <p class="text-gray-500">${item.jumlah} x Rp ${new Intl.NumberFormat('id-ID').format(item.harga)}</p>
                                    </div>
                                    <p class="font-semibold text-gray-700 w-24 text-right">Rp ${new Intl.NumberFormat('id-ID').format(item.total)}</p>
                                </div>
                            `;
                            itemsContainer.append(itemHtml);
                        });

                        // 5. Hitung dan tampilkan ringkasan biaya
                        var grandTotal = parseFloat(transaksi.total);
                        var shippingCost = grandTotal - subtotalProduk;

                        $('#modal-subtotal').text(new Intl.NumberFormat('id-ID').format(subtotalProduk));
                        $('#modal-shipping').text(new Intl.NumberFormat('id-ID').format(shippingCost));
                        $('#modal-grand-total').text(new Intl.NumberFormat('id-ID').format(grandTotal));
                        
                        // 6. Tampilkan konten dan sembunyikan loading
                        modalLoading.hide();
                        modalContent.show();

                    },
                    error: function() {
                        // Tampilkan error di dalam modal
                        modalLoading.hide();
                        $('#modalBody').html('<div class="p-8 text-center"><p class="text-red-500 font-bold">Terjadi Kesalahan</p><p>Gagal memuat detail pesanan. Silakan coba lagi.</p></div>');
                    }
                });
            });
        });
    </script>
</body>
</html>
