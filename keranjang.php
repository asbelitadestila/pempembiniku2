<?php
session_start();

// Redirect jika pengguna belum login
// if (!isset($_SESSION['id_admin'])) {
//     header('Location: admin/login.php');
//     exit();
// }

include("koneksi/koneksi.php");

// $user_id = $_SESSION['id_admin'];

// --- PENINGKATAN KEAMANAN: MENGGUNAKAN PREPARED STATEMENTS ---

// Fungsi untuk menghapus produk dari keranjang
if (isset($_POST['action']) && $_POST['action'] == 'hapus') {
    $id_produk = $_POST['id_produk'];
    $query = "DELETE FROM keranjang_user WHERE id_produk = ? AND id_user = ?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "ii", $id_produk, $user_id);
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => 'Produk berhasil dihapus']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Produk gagal dihapus']);
    }
    exit();
}

// Fungsi untuk memperbarui jumlah produk di keranjang
if (isset($_POST['action']) && $_POST['action'] == 'update') {
    $id_produk = $_POST['id_produk'];
    $jumlah = (int)$_POST['jumlah']; // Pastikan jumlah adalah integer

    if ($jumlah > 0) {
        $query = "UPDATE keranjang_user SET jumlah = ? WHERE id_produk = ? AND id_user = ?";
        $stmt = mysqli_prepare($koneksi, $query);
        mysqli_stmt_bind_param($stmt, "iii", $jumlah, $id_produk, $user_id);
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true, 'message' => 'Jumlah berhasil diperbarui']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal memperbarui jumlah']);
        }
    } else { // Jika jumlah 0 atau kurang, hapus item
        $query = "DELETE FROM keranjang_user WHERE id_produk = ? AND id_user = ?";
        $stmt = mysqli_prepare($koneksi, $query);
        mysqli_stmt_bind_param($stmt, "ii", $id_produk, $user_id);
        mysqli_stmt_execute($stmt);
        echo json_encode(['success' => true, 'message' => 'Produk dihapus dari keranjang']);
    }
    exit();
}

// Mengambil data keranjang
$query_keranjang = "SELECT * FROM keranjang_user WHERE id_user = ?";
$stmt_keranjang = mysqli_prepare($koneksi, $query_keranjang);
mysqli_stmt_bind_param($stmt_keranjang, "i", $user_id);
mysqli_stmt_execute($stmt_keranjang);
$result_keranjang = mysqli_stmt_get_result($stmt_keranjang);
$items = mysqli_fetch_all($result_keranjang, MYSQLI_ASSOC);

// Menghitung total
$total_amount = 0;
foreach ($items as $item) {
    $total_amount += $item['harga'] * $item['jumlah'];
}

// Konfigurasi Midtrans
require 'vendor/autoload.php';
\Midtrans\Config::$serverKey = 'SB-Mid-server-C1ta5HP9_KFpsSrBQaSJP3zC'; // Ganti dengan Server Key Anda
\Midtrans\Config::$isProduction = false; // Set true untuk production
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;

$snapToken = '';
if (!empty($items)) {
    $transaction_details = [
        'order_id' => 'PEMPEK-' . time(), // Order ID unik
        'gross_amount' => $total_amount,
    ];

    $item_details = [];
    foreach ($items as $item) {
        $item_details[] = [
            'id' => $item['id_produk'],
            'price' => $item['harga'],
            'quantity' => $item['jumlah'],
            'name' => $item['nama'],
        ];
    }
    
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

    try {
        $snapToken = \Midtrans\Snap::getSnapToken($transaction);
    } catch (Exception $e) {
        // Tangani error jika gagal mendapatkan Snap Token
        error_log($e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja - Pempek Biniku</title>
    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet" type="text/css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Midtrans Snap.js -->
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="SB-Mid-client-4QJq_2Yvt-uFk85A"></script> <!-- Ganti dengan Client Key Anda -->
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .quantity-input::-webkit-outer-spin-button,
        .quantity-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        .quantity-input[type=number] {
            -moz-appearance: textfield;
        }
    </style>
</head>
<body class="bg-[#FFFAE7]">

    <!-- Navbar start-->
    <nav class="flex items-center justify-between bg-red-700 px-4 sm:px-8 py-2 fixed w-full top-0 left-0 z-50 shadow-md">
        <!-- Logo -->
        <a href="index.php"><img src="./assets/foto/logo.png" class="w-20" alt="Logo Pempek Biniku"></a>
        
        <div class="hidden md:flex space-x-8">
            <a href="index.php" class="text-white font-bold hover:text-yellow-200 transition-colors">Beranda</a>
            <a href="index.php#about" class="text-white font-bold hover:text-yellow-200 transition-colors">About</a>
            <a href="index.php#produk" class="text-white font-bold hover:text-yellow-200 transition-colors">Produk</a>
            <a href="index.php#kontak" class="text-white font-bold hover:text-yellow-200 transition-colors">Kontak</a>
        </div>
        
        <div class="flex items-center space-x-4">
            <div class="users">
                <?php if(isset($_SESSION['username'])): ?>
                    <a href="keranjang.php" class="relative">
                        <i class="fas fa-shopping-cart text-white text-xl"></i>
                        <?php
                            $sql_keranjang = "SELECT COUNT(*) as total FROM keranjang_user WHERE id_user = ?";
                            $stmt_count = mysqli_prepare($koneksi, $sql_keranjang);
                            mysqli_stmt_bind_param($stmt_count, "i", $user_id);
                            mysqli_stmt_execute($stmt_count);
                            $result_count = mysqli_stmt_get_result($stmt_count);
                            $data_count = mysqli_fetch_assoc($result_count);
                        ?>
                        <span id="cart-count" class="absolute -top-2 -right-2 bg-yellow-400 text-xs rounded-full px-2 py-0.5 text-black font-bold"><?php echo $data_count['total']; ?></span>
                    </a>
                <?php endif; ?>
            </div>

            <!-- Ikon User & Dropdown -->
            <div class="relative">
                <button id="btn-user" class="focus:outline-none"><i class="fas fa-user text-white text-xl"></i></button>
                <div id="user-menu" class="user-menu absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 hidden">
                    <?php if(isset($_SESSION['username'])): ?>
                        <a href="profil.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profil</a>
                        <a href="admin/logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</a>
                    <?php else: ?>
                        <a href="admin/login.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Login</a>
                        <a href="daftar.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Daftar</a>
                    <?php endif; ?>
                </div>
            </div>
            <button id="btn-menu" class="md:hidden focus:outline-none"><i class="fas fa-bars text-white text-xl"></i></button>
        </div>
    </nav>
    <div id="mobile-menu" class="hidden md:hidden bg-red-800 fixed w-full top-16 z-40 p-4 space-y-2">
        <a href="index.php" class="block text-white font-bold hover:text-yellow-200">Beranda</a>
        <a href="index.php#about" class="block text-white font-bold hover:text-yellow-200">About</a>
        <a href="index.php#produk" class="block text-white font-bold hover:text-yellow-200">Produk</a>
        <a href="index.php#kontak" class="block text-white font-bold hover:text-yellow-200">Kontak</a>
    </div>
    <!-- Navbar end-->

    <main class="pt-24 pb-12">
        <div class="container mx-auto px-4">
            <!-- <h1 class="text-3xl font-bold text-gray-800 mb-8">Keranjang Belanja Anda</h1> -->

            <?php if (empty($items)): ?>
                <div class="text-center mt-[6%] py-40 bg-white rounded-lg shadow-md">
                    <i class="fas fa-shopping-cart text-6xl text-gray-300 mb-4"></i>
                    <h2 class="text-2xl font-semibold text-gray-700">Keranjang Anda kosong</h2>
                    <p class="text-gray-500 mt-2">Ayo, temukan pempek favoritmu!</p>
                    <a href="index.php#produk" class="mt-6 inline-block bg-red-600 text-white font-bold py-2 px-6 rounded-lg hover:bg-red-700 transition-colors">Mulai Belanja</a>
                </div>
            <?php else: ?>
                <div class="flex flex-col lg:flex-row gap-8">
                    <!-- Daftar Item Keranjang -->
                    <div id="cart-items-container" class="w-full lg:w-2/3 space-y-4">
                        <?php foreach ($items as $item): ?>
                            <div id="item-<?php echo $item['id_produk']; ?>" class="flex items-center bg-white p-4 rounded-lg shadow-sm transition-all duration-300">
                                <img src="assets/foto_produk/<?php echo htmlspecialchars($item['foto']); ?>" alt="<?php echo htmlspecialchars($item['nama']); ?>" class="w-20 h-20 object-cover rounded-md">
                                <div class="flex-grow ml-4">
                                    <h3 class="font-bold text-lg text-gray-800"><?php echo htmlspecialchars($item['nama']); ?></h3>
                                    <p class="text-sm text-red-600 font-semibold">Rp <span class="item-price"><?php echo number_format($item['harga'], 0, ',', '.'); ?></span></p>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <input type="number" value="<?php echo $item['jumlah']; ?>" min="1" data-id="<?php echo $item['id_produk']; ?>" class="quantity-input w-16 text-center border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500">
                                    <p class="font-bold w-28 text-right">Rp <span class="item-total-price"><?php echo number_format($item['harga'] * $item['jumlah'], 0, ',', '.'); ?></span></p>
                                </div>
                                <button class="ml-4 text-gray-400 hover:text-red-600 transition-colors remove-item-btn" data-id="<?php echo $item['id_produk']; ?>">
                                    <i class="fas fa-trash-alt fa-lg"></i>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Ringkasan Pesanan -->
                    <div class="w-full lg:w-1/3">
                        <div class="bg-white p-6 rounded-lg shadow-sm lg:sticky lg:top-24">
                            <h2 class="text-xl font-bold border-b pb-4 mb-4">Ringkasan Pesanan</h2>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <p class="text-gray-600">Subtotal</p>
                                    <p class="font-semibold">Rp <span id="subtotal-amount"><?php echo number_format($total_amount, 0, ',', '.'); ?></span></p>
                                </div>
                                <div class="flex justify-between">
                                    <p class="text-gray-600">Biaya Pengiriman</p>
                                    <p class="font-semibold">Akan dihitung</p>
                                </div>
                            </div>
                            <div class="flex justify-between font-bold text-lg border-t pt-4 mt-4">
                                <p>Total</p>
                                <p>Rp <span id="total-amount"><?php echo number_format($total_amount, 0, ',', '.'); ?></span></p>
                            </div>
                            <button id="pay-button" class="w-full bg-red-600 text-white font-bold py-3 rounded-lg mt-6 hover:bg-red-700 transition-colors">
                                Lanjut ke Pembayaran
                            </button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Navbar toggles
        const btnUser = document.getElementById('btn-user');
        const userMenu = document.getElementById('user-menu');
        const btnMenu = document.getElementById('btn-menu');
        const mobileMenu = document.getElementById('mobile-menu');

        btnUser.addEventListener('click', (e) => {
            e.stopPropagation();
            userMenu.classList.toggle('hidden');
        });

        btnMenu.addEventListener('click', (e) => {
            e.stopPropagation();
            mobileMenu.classList.toggle('hidden');
        });
        
        document.addEventListener('click', () => {
            userMenu.classList.add('hidden');
        });

        // Fungsi untuk memformat angka menjadi format Rupiah
        function formatRupiah(angka) {
            return new Intl.NumberFormat('id-ID').format(angka);
        }

        // Fungsi untuk mengupdate total keseluruhan
        function updateGrandTotal() {
            let grandTotal = 0;
            document.querySelectorAll('.item-total-price').forEach(el => {
                grandTotal += parseFloat(el.textContent.replace(/\./g, ''));
            });
            document.getElementById('subtotal-amount').textContent = formatRupiah(grandTotal);
            document.getElementById('total-amount').textContent = formatRupiah(grandTotal);
        }

        // Fungsi untuk mengirim data ke server (update/hapus)
        function sendUpdateRequest(action, productId, quantity = null) {
            const formData = new FormData();
            formData.append('action', action);
            formData.append('id_produk', productId);
            if (quantity !== null) {
                formData.append('jumlah', quantity);
            }

            fetch('keranjang.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    alert(data.message || 'Terjadi kesalahan.');
                    // Jika gagal, mungkin perlu reload untuk sinkronisasi
                    window.location.reload();
                } else {
                    // Update jumlah di ikon keranjang jika berhasil
                    updateCartIconCount();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Tidak dapat terhubung ke server.');
                window.location.reload();
            });
        }
        
        // Fungsi untuk update jumlah di ikon keranjang
        function updateCartIconCount() {
            const totalItems = document.querySelectorAll('#cart-items-container > div').length;
            document.getElementById('cart-count').textContent = totalItems;
        }

        // Event listener untuk input kuantitas
        document.querySelectorAll('.quantity-input').forEach(input => {
            input.addEventListener('change', function() {
                const id = this.dataset.id;
                const quantity = parseInt(this.value);
                const itemRow = document.getElementById('item-' + id);
                const price = parseFloat(itemRow.querySelector('.item-price').textContent.replace(/\./g, ''));
                
                if (quantity > 0) {
                    const newTotal = price * quantity;
                    itemRow.querySelector('.item-total-price').textContent = formatRupiah(newTotal);
                    sendUpdateRequest('update', id, quantity);
                } else {
                    // Jika user memasukkan 0, hapus item
                    itemRow.remove();
                    sendUpdateRequest('update', id, 0);
                }
                updateGrandTotal();
            });
        });

        // Event listener untuk tombol hapus
        document.querySelectorAll('.remove-item-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                if (confirm('Apakah Anda yakin ingin menghapus produk ini?')) {
                    const id = this.dataset.id;
                    const itemRow = document.getElementById('item-' + id);
                    
                    itemRow.style.opacity = '0';
                    itemRow.style.transform = 'translateX(-100%)';
                    
                    setTimeout(() => {
                        itemRow.remove();
                        updateGrandTotal();
                        sendUpdateRequest('hapus', id);
                        
                        // Cek jika keranjang menjadi kosong
                        if (document.querySelectorAll('#cart-items-container > div').length === 0) {
                            window.location.reload(); // Reload untuk menampilkan pesan keranjang kosong
                        }
                    }, 300);
                }
            });
        });

        // Event listener untuk tombol bayar
        const payButton = document.getElementById('pay-button');
        if (payButton) {
            payButton.addEventListener('click', function () {
                const snapToken = '<?php echo $snapToken; ?>';
                if (snapToken) {
                    snap.pay(snapToken, {
                        onSuccess: function(result){
                            /* Anda bisa menambahkan logika di sini, misalnya redirect ke halaman status pesanan */
                            alert("Pembayaran berhasil!"); console.log(result);
                            window.location.href = 'status_pesanan.php?order_id=' + result.order_id;
                        },
                        onPending: function(result){
                            /* Logika untuk status pending */
                            alert("Menunggu pembayaran Anda!"); console.log(result);
                            window.location.href = 'status_pesanan.php?order_id=' + result.order_id;
                        },
                        onError: function(result){
                            /* Logika jika terjadi error */
                            alert("Pembayaran gagal!"); console.log(result);
                        },
                        onClose: function(){
                            /* Logika jika pop-up ditutup sebelum selesai */
                            console.log('Anda menutup popup tanpa menyelesaikan pembayaran');
                        }
                    });
                } else {
                    alert('Gagal memproses pembayaran. Silakan coba lagi.');
                }
            });
        }

    });
    </script>
</body>
</html>
