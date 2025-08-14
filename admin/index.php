<?php
session_start();
include '../koneksi/koneksi.php';

// Keamanan: Cek jika pengguna adalah admin, jika tidak, redirect ke login.
// if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
//     header('Location: login.php');
//     exit();
// }

// Menentukan halaman yang akan ditampilkan
$halaman = isset($_GET['halaman']) ? $_GET['halaman'] : 'dashboard';

// Logout
if ($halaman == 'logout') {
    session_destroy();
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Pempek Biniku</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@400;500;600;700&display=swap');
        body { font-family: 'Poppins', sans-serif; background-color: #FFFAE7; }
        .font-playfair { font-family: 'Playfair Display', serif; }
        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #dc2626; border-radius: 10px;}
        ::-webkit-scrollbar-thumb:hover { background: #b91c1c; }
    </style>
</head>
<body class="bg-white">

    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-red-700 text-white flex-shrink-0 flex flex-col">
            <div class="h-20 flex items-center justify-center bg-red-800">
                <a href="index.php" class="text-xl font-bold">Pempek Biniku</a>
            </div>
            <nav class="flex-grow text-sm p-4 mt-4 space-y-2">
                <a href="index.php?halaman=dashboard" class="flex items-center px-4 py-2.5 rounded-lg transition-colors <?php echo $halaman == 'dashboard' ? 'bg-red-900' : 'hover:bg-red-600'; ?>">
                    <i class="fas fa-tachometer-alt w-6 mr-3"></i>
                    <span>Dashboard</span>
                </a>
                <a href="index.php?halaman=kategori" class="flex items-center px-4 py-2.5 rounded-lg transition-colors <?php echo $halaman == 'kategori' ? 'bg-red-900' : 'hover:bg-red-600'; ?>">
                    <i class="fas fa-tags w-6 mr-3"></i>
                    <span>Kategori</span>
                </a>
                <a href="index.php?halaman=produk" class="flex items-center px-4 py-2.5 rounded-lg transition-colors <?php echo $halaman == 'produk' ? 'bg-red-900' : 'hover:bg-red-600'; ?>">
                    <i class="fas fa-box-open w-6 mr-3"></i>
                    <span>Produk</span>
                </a>
                <a href="index.php?halaman=transaksi" class="flex items-center px-4 py-2.5 rounded-lg transition-colors <?php echo $halaman == 'transaksi' || $halaman == 'detail_pembelian' ? 'bg-red-900' : 'hover:bg-red-600'; ?>">
                    <i class="fas fa-receipt w-6 mr-3"></i>
                    <span>Transaksi</span>
                </a>
                <a href="index.php?halaman=pelanggan" class="flex items-center px-4 py-2.5 rounded-lg transition-colors <?php echo $halaman == 'pelanggan' ? 'bg-red-900' : 'hover:bg-red-600'; ?>">
                    <i class="fas fa-users w-6 mr-3"></i>
                    <span>Pelanggan</span>
                </a>
            </nav>
            <div class="p-4 border-t text-sm border-red-600">
                <a href="index.php?halaman=logout" class="flex items-center px-4 py-2.5 rounded-lg hover:bg-red-600 transition-colors">
                    <i class="fas fa-sign-out-alt w-6 mr-3"></i>
                    <span>Logout</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-4 sm:p-8 overflow-y-auto">
            <?php
            // Routing konten halaman
            switch ($halaman) {
                case 'kategori':
                    include 'kategori.php';
                    break;
                case 'produk':
                    include 'produk.php';
                    break;
                case 'transaksi':
                    include 'transaksi.php';
                    break;
                case 'pelanggan':                    
                    include 'pelanggan.php';
                    break;
                case 'detail_pembelian':                    
                    include 'Detail/detail_pembelian.php';
                    break;
                case 'dashboard':
                default:
                    include 'dashboard.php';
                    break;
            }
            ?>
            
            <!-- Footer -->
                <footer class="bg-red-700 text-white text-center p-4 mt-4">
                    <p class="text-sm">&copy; <?php echo date("Y"); ?> Pempek Biniku. All rights reserved.</p>  
                </footer>
                <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/js/all.min.js"></script>
                <script src="https://cdn.tailwindcss.com"></script>

        </main>
    </div>
    
    

</body>
</html>