<?php
session_start();
// Pastikan file koneksi ada
if (file_exists("koneksi/koneksi.php")) {
    include "koneksi/koneksi.php";
} else {
    // Tindakan darurat jika file koneksi tidak ditemukan
    die("Error: File koneksi database tidak ditemukan. Halaman tidak dapat dimuat.");
}


// --- PENINGKATAN KEAMANAN: MENGGUNAKAN PREPARED STATEMENTS ---
// Mengambil semua data produk dengan join ke kategori
$produk = array();
// Pastikan objek koneksi ada sebelum digunakan
if (isset($koneksi)) {
    $query_produk = "SELECT p.*, k.nama_kategori FROM produk p JOIN kategori k ON p.id_kategori = k.id_kategori ORDER BY p.id_produk DESC";
    $stmt_produk = $koneksi->prepare($query_produk);
    if ($stmt_produk) {
        $stmt_produk->execute();
        $result_produk = $stmt_produk->get_result();
        while ($pecah = $result_produk->fetch_assoc()) {
            $produk[] = $pecah;
        }
        $stmt_produk->close();
    }

    // Menangani penambahan produk ke keranjang
    if (isset($_POST['beli'])) {
        if (!isset($_SESSION['id_admin'])) {
            echo "<script>alert('Anda harus login terlebih dahulu untuk berbelanja.'); window.location.href='admin/login.php';</script>";
            exit();
        }

        $id_user = $_SESSION['id_admin'];
        $id_produk = filter_input(INPUT_POST, 'id_produk', FILTER_VALIDATE_INT);
        $jumlah = filter_input(INPUT_POST, 'jumlah', FILTER_VALIDATE_INT);

        // Validasi input
        if ($id_produk && $jumlah > 0) {
            // Cek apakah produk sudah ada di keranjang
            $query_cek = "SELECT * FROM keranjang_user WHERE id_user = ? AND id_produk = ?";
            $stmt_cek = $koneksi->prepare($query_cek);
            $stmt_cek->bind_param("ii", $id_user, $id_produk);
            $stmt_cek->execute();
            $result_cek = $stmt_cek->get_result();

            if ($result_cek->num_rows > 0) {
                // Jika sudah ada, update jumlahnya
                $query_update = "UPDATE keranjang_user SET jumlah = jumlah + ? WHERE id_user = ? AND id_produk = ?";
                $stmt_update = $koneksi->prepare($query_update);
                $stmt_update->bind_param("iii", $jumlah, $id_user, $id_produk);
                $stmt_update->execute();
            } else {
                // Jika belum ada, ambil detail produk dan masukkan sebagai item baru
                $query_get_produk = "SELECT nama_produk, harga_produk, foto_produk FROM produk WHERE id_produk = ?";
                $stmt_get_produk = $koneksi->prepare($query_get_produk);
                $stmt_get_produk->bind_param("i", $id_produk);
                $stmt_get_produk->execute();
                $result_get_produk = $stmt_get_produk->get_result();
                if($product_details = $result_get_produk->fetch_assoc()){
                    $nama_produk = $product_details['nama_produk'];
                    $harga_produk = $product_details['harga_produk'];
                    $foto_produk = $product_details['foto_produk'];

                    $query_insert = "INSERT INTO keranjang_user (id_user, id_produk, nama, harga, foto, jumlah) VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt_insert = $koneksi->prepare($query_insert);
                    $stmt_insert->bind_param("iissis", $id_user, $id_produk, $nama_produk, $harga_produk, $foto_produk, $jumlah);
                    $stmt_insert->execute();
                }
            }
            echo "<script>alert('Produk berhasil ditambahkan ke keranjang.'); window.location.href='index.php#produk';</script>";
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pempek Biniku - Cita Rasa Autentik Palembang</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* ========================================
         PALET WARNA BARU (SESUAI GAMBAR LOGIN)
         ======================================== */
        :root {
            --bg-main: #FEFBF4;       /* Krem Sangat Muda */
            --text-dark: #3A3A3A;     /* Arang Gelap (dari gambar) */
            --text-light: #777777;    /* Abu-abu Lembut */
            --accent: #D1514A;        /* Merah Terakota (dari tombol login) */
            --accent-hover: #B9443E;  /* Merah lebih gelap untuk hover */
            --surface: #FFFFFF;       /* Latar kartu/konten */
            --border-color: #F0F0F0;
        }

        /* Menggunakan font dari Google Fonts */
        body { font-family: 'Poppins', sans-serif; background-color: var(--bg-main); color: var(--text-dark); }
        .font-playfair { font-family: 'Playfair Display', serif; }

        /* Menyembunyikan panah pada input angka */
        input[type='number']::-webkit-outer-spin-button,
        input[type='number']::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
        input[type='number'] { -moz-appearance: textfield; }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="bg-[var(--surface)] px-4 sm:px-8 py-3 fixed w-full top-0 left-0 z-50 shadow-md transition-all duration-300">
        <div class="container mx-auto flex items-center justify-between">
            <!-- Logo -->
            <a href="index.php"><img src="./assets/foto/logo.png" class="w-20" alt="Logo Pempek Biniku"></a>

            <!-- Navigasi Desktop -->
            <div class="hidden md:flex items-center space-x-8">
                <a href="index.php" class="font-semibold text-[var(--text-dark)] hover:text-[var(--accent)] transition-colors">Beranda</a>
                <a href="index.php#about" class="font-semibold text-[var(--text-dark)] hover:text-[var(--accent)] transition-colors">Tentang</a>
                <a href="index.php#produk" class="font-semibold text-[var(--text-dark)] hover:text-[var(--accent)] transition-colors">Produk</a>
                <a href="index.php#kontak" class="font-semibold text-[var(--text-dark)] hover:text-[var(--accent)] transition-colors">Kontak</a>
            </div>

            <!-- Ikon dan Tombol Aksi -->
            <div class="flex items-center space-x-4">
                <?php if (isset($_SESSION['username'])): ?>
                    <a href="keranjang.php" class="relative text-[var(--text-dark)] hover:text-[var(--accent)]">
                        <i class="fas fa-shopping-cart text-xl"></i>
                        <?php
                            if(isset($koneksi)) {
                                $user_id = $_SESSION['id_admin'];
                                $stmt_cart_count = $koneksi->prepare("SELECT COUNT(*) as total FROM keranjang_user WHERE id_user = ?");
                                $stmt_cart_count->bind_param("i", $user_id);
                                $stmt_cart_count->execute();
                                $result_cart_count = $stmt_cart_count->get_result();
                                $data_cart_count = $result_cart_count->fetch_assoc();
                                if ($data_cart_count['total'] > 0) {
                                    echo '<span class="absolute -top-2 -right-3 bg-[var(--accent)] text-white rounded-full text-xs w-5 h-5 flex items-center justify-center font-bold">' . $data_cart_count['total'] . '</span>';
                                }
                            }
                        ?>
                    </a>
                    <div class="relative">
                        <button id="btn-user" class="focus:outline-none text-[var(--text-dark)] hover:text-[var(--accent)]"><i class="fas fa-user text-xl"></i></button>
                        <div id="user-dropdown" class="hidden absolute top-full right-0 mt-3 w-56 bg-white rounded-lg shadow-xl py-2 z-50 transition-all duration-300 opacity-0 transform -translate-y-2">
                            <div class="px-4 py-2 border-b"><p class="text-sm text-gray-500">Masuk sebagai,</p><p class="font-semibold text-[var(--accent)] truncate"><?php echo htmlspecialchars($_SESSION['username']); ?></p></div>
                            <a href="update_profil.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100"><i class="fas fa-user-edit w-6 text-[var(--accent)]"></i>Profil Saya</a>
                            <a href="keranjang.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100"><i class="fas fa-receipt w-6 text-[var(--accent)]"></i>Pesanan Saya</a>
                            <div class="border-t my-1"></div>
                            <a href="admin/logout.php" class="flex items-center px-4 py-2 text-red-600 hover:bg-red-50"><i class="fas fa-sign-out-alt w-6"></i>Logout</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="hidden sm:flex items-center space-x-2">
                        <a href="admin/login.php" class="bg-gray-100 text-[var(--text-dark)] px-5 py-2 rounded-full font-semibold hover:bg-gray-200 transition-all text-sm">Login</a>
                        <a href="daftar.php" class="bg-[var(--accent)] text-white px-5 py-2 rounded-full font-semibold hover:bg-[var(--accent-hover)] transition-all text-sm">Daftar</a>
                    </div>
                <?php endif; ?>
                <button id="btn-menu" class="md:hidden text-[var(--text-dark)] text-2xl"><i class="fas fa-bars"></i></button>
            </div>
        </div>
    </nav>
    
    <!-- Menu Mobile -->
    <div id="mobile-menu" class="hidden md:hidden fixed top-0 left-0 w-full h-full bg-[var(--surface)] z-40 p-8 text-center flex flex-col justify-center space-y-6">
        <a href="index.php" class="block text-[var(--text-dark)] text-2xl font-semibold">Beranda</a>
        <a href="#about" class="block text-[var(--text-dark)] text-2xl font-semibold">Tentang</a>
        <a href="#produk" class="block text-[var(--text-dark)] text-2xl font-semibold">Produk</a>
        <a href="#kontak" class="block text-[var(--text-dark)] text-2xl font-semibold">Kontak</a>
        <?php if (!isset($_SESSION['username'])): ?>
        <div class="pt-6">
            <a href="admin/login.php" class="bg-[var(--accent)] text-white px-8 py-3 rounded-full font-semibold hover:bg-[var(--accent-hover)] transition-all">Login</a>
        </div>
        <?php endif; ?>
    </div>

    <!-- Hero Section Carousel -->
    <section id="home" class="relative w-full h-screen overflow-hidden pt-24">
        <div class="relative w-full h-full">
            <!-- Slide 1 -->
            <div class="carousel-item active absolute inset-0 transition-opacity duration-1000 ease-in-out">
                <div class="absolute inset-0 bg-black/40"></div>
                <img src="./assets/foto/banner44.jpg" class="w-full h-full object-cover" alt="Pempek Khas Palembang">
                <div class="absolute inset-0 flex flex-col items-center justify-center text-white text-center p-4">
                    <h1 class="font-playfair text-5xl md:text-7xl font-extrabold drop-shadow-lg">Pempek Biniku</h1>
                    <p class="mt-4 text-lg md:text-2xl font-medium drop-shadow-md">Cita Rasa Autentik Palembang</p>
                </div>
            </div>
             <!-- Tambahkan slide lain jika perlu dengan struktur yang sama -->
        </div>
    </section>

    <div class="container mx-auto px-4 md:px-8">
        <!-- Tentang Kami -->
        <section id="about" class="py-20 md:py-28">
            <div class="flex flex-col md:flex-row items-center gap-12">
                <div class="w-full md:w-1/2"><img src="assets/foto/banner1.jpg" class="w-full rounded-xl shadow-xl" alt="Tentang Pempek Biniku"></div>
                <div class="w-full md:w-1/2 text-center md:text-left">
                    <h2 class="font-playfair text-4xl font-bold text-[var(--text-dark)] mb-4">Warisan Rasa, Kualitas Terjaga</h2>
                    <p class="text-lg text-[var(--text-light)] leading-relaxed">Selamat datang di <strong>Pempek Biniku</strong>. Kami membawa kelezatan asli Palembang ke hadapan Anda, dibuat dari resep warisan keluarga dan bahan baku ikan segar pilihan untuk menjamin setiap gigitan penuh rasa dan kualitas.</p>
                </div>
            </div>
        </section>

        <!-- Produk Unggulan -->
        <section id="featured-products" class="py-12 md:py-20 text-center">
            <h2 class="font-playfair text-4xl font-bold text-[var(--text-dark)] mb-4">Paling Laris</h2>
            <p class="max-w-2xl mx-auto text-[var(--text-light)] mb-12">Tiga produk yang paling sering dipesan dan menjadi favorit pelanggan kami.</p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php
                    $top_products = array_slice($produk, 0, 3);
                    foreach ($top_products as $index => $value):
                ?>
                <div class="group text-left">
                    <div class="relative overflow-hidden rounded-xl">
                        <img src="assets/foto_produk/<?php echo htmlspecialchars($value['foto_produk']); ?>" class="w-full h-72 object-cover rounded-xl shadow-md transition-transform duration-300 group-hover:scale-105" alt="<?php echo htmlspecialchars($value['nama_produk']); ?>">
                        <div class="absolute top-3 right-3 bg-[var(--accent)] text-white text-xs font-bold px-3 py-1 rounded-full">#<?php echo $index + 1; ?> Terlaris</div>
                    </div>
                    <h3 class="text-xl font-bold mt-4"><?php echo htmlspecialchars($value['nama_produk']); ?></h3>
                    <p class="text-lg font-semibold text-[var(--accent)]">Rp <?php echo number_format($value['harga_produk']); ?></p>
                    <a href="index.php#produk-item-<?php echo $value['id_produk']; ?>" class="inline-block mt-3 text-sm font-semibold text-[var(--accent)] hover:text-[var(--accent-hover)]">Lihat Detail <i class="fas fa-arrow-right ml-1"></i></a>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Semua Produk -->
        <section id="produk" class="py-20 md:py-28 text-center">
            <h2 class="font-playfair text-4xl font-bold text-[var(--text-dark)] mb-4">Semua Produk</h2>
            <p class="max-w-2xl mx-auto text-[var(--text-light)] mb-12">Jelajahi berbagai pilihan pempek kami, dari satuan hingga paket lengkap untuk setiap acara.</p>
            
            <div id="filter-buttons" class="flex flex-wrap justify-center gap-3 md:gap-4 mb-12">
                <button class="kategori-btn active" onclick="filterProduk('semua')">Semua</button>
                <button class="kategori-btn" onclick="filterProduk('pempek satuan')">Satuan</button>
                <button class="kategori-btn" onclick="filterProduk('pempek paket')">Paket</button>
                <button class="kategori-btn" onclick="filterProduk('other')">Lainnya</button>
            </div>

            <div id="produk-list" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                <?php foreach ($produk as $value): ?>
                <div id="produk-item-<?php echo $value['id_produk']; ?>" class="produk-item group" data-kategori="<?php echo strtolower(htmlspecialchars($value['nama_kategori'])); ?>">
                    <form action="index.php#produk" method="post" class="bg-[var(--surface)] border border-[var(--border-color)] rounded-xl shadow-md h-full flex flex-col overflow-hidden transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                        <div class="relative overflow-hidden">
                            <img src="assets/foto_produk/<?php echo htmlspecialchars($value['foto_produk']); ?>" class="w-full h-56 object-cover transition-transform duration-300 group-hover:scale-105" alt="<?php echo htmlspecialchars($value['nama_produk']); ?>">
                        </div>
                        <div class="p-5 flex flex-col flex-grow text-left">
                            <h3 class="text-lg font-bold text-[var(--text-dark)] flex-grow"><?php echo htmlspecialchars($value['nama_produk']); ?></h3>
                            <p class="text-sm text-[var(--text-light)] mb-3">Stok: <?php echo number_format($value['stok_produk']); ?></p>
                            <p class="text-xl font-bold text-[var(--accent)] mb-4">Rp <?php echo number_format($value['harga_produk']); ?></p>
                            
                            <div class="mt-auto">
                                <input type="hidden" name="id_produk" value="<?php echo $value['id_produk']; ?>">
                                <div class="flex items-center justify-center mb-4">
                                    <button type="button" class="quantity-btn decrease-btn">-</button>
                                    <input type="number" name="jumlah" value="1" min="1" max="<?php echo $value['stok_produk']; ?>" class="quantity-input" readonly>
                                    <button type="button" class="quantity-btn increase-btn">+</button>
                                </div>
                                <button type="submit" name="beli" class="w-full bg-[var(--accent)] text-white py-2.5 px-4 rounded-lg font-semibold hover:bg-[var(--accent-hover)] transition-all">
                                    <i class="fas fa-cart-plus mr-2"></i>Keranjang
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
    </div>

    <!-- Kontak -->
    <section id="kontak" class="py-20 md:py-28 bg-[var(--surface)]">
        <div class="container mx-auto px-4 md:px-8 text-center">
            <h2 class="font-playfair text-4xl font-bold text-[var(--text-dark)] mb-4">Kunjungi Kami</h2>
            <p class="max-w-2xl mx-auto text-[var(--text-light)] mb-12">Temukan lokasi kami atau hubungi kami melalui media sosial untuk pemesanan dan informasi lebih lanjut.</p>
            <div class="rounded-xl overflow-hidden shadow-xl"><iframe class="w-full h-96" src="https://www.google.com/maps/embed?pb=!1m17!1m12!1m3!1d16335807.982538566!2d92.02327591828396!3d-1.6145300000000005!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m2!1m1!2zMcKwMzYnNTIuMyJTIDEwM8KwMzcnMzAuMSJF!5e0!3m2!1sen!2sid!4v1724488443830!5m2!1sen!2sid" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe></div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-[var(--text-dark)] text-white py-12">
        <div class="container mx-auto px-4 md:px-8 text-center">
            <h3 class="text-xl font-bold mb-4">Ikuti Kami</h3>
            <div class="flex justify-center space-x-4 mb-6">
                <a href="https://instagram.com/pempekbiniku_" target="_blank" class="social-link"><i class="fab fa-instagram text-xl"></i></a>
                <a href="https://www.tokopedia.com/pempekbiniku" target="_blank" class="social-link"><img src="assets/foto/tokopedia.png" alt="Tokopedia" class="w-5 h-5"></a>
                <a href="https://shopee.co.id/pempekbiniku" target="_blank" class="social-link"><img src="assets/foto/shopee.png" alt="Shopee" class="w-5 h-5"></a>
            </div>
            <p class="text-sm text-white/70">&copy; <?php echo date("Y"); ?> Pempek Biniku. All Rights Reserved.</p>
        </div>
    </footer>

    <style>
        .kategori-btn { @apply px-5 py-2 text-base rounded-full border-2 border-[var(--border-color)] text-[var(--text-light)] font-semibold transition-all duration-300 hover:border-[var(--accent)] hover:text-[var(--accent)]; }
        .kategori-btn.active { @apply bg-[var(--accent)] text-white border-[var(--accent)]; }
        .quantity-btn { @apply w-10 h-10 bg-gray-100 text-gray-700 rounded-lg font-bold text-lg hover:bg-gray-200 transition-all; }
        .quantity-input { @apply w-16 h-10 text-center border-y-2 border-gray-100 mx-1 text-lg font-semibold focus:outline-none; }
        .social-link { @apply w-10 h-10 flex items-center justify-center bg-white/10 rounded-full hover:bg-white/20 transition-colors; }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const userBtn = document.getElementById('btn-user');
        const userDropdown = document.getElementById('user-dropdown');
        const menuBtn = document.getElementById('btn-menu');
        const mobileMenu = document.getElementById('mobile-menu');

        if (userBtn) {
            userBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                userDropdown.classList.toggle('hidden');
                setTimeout(() => {
                    userDropdown.classList.toggle('opacity-0');
                    userDropdown.classList.toggle('-translate-y-2');
                }, 10);
            });
        }
        
        if (menuBtn) {
            menuBtn.addEventListener('click', () => mobileMenu.classList.toggle('hidden'));
        }
        
        window.addEventListener('click', (e) => {
            if (userDropdown && !userDropdown.classList.contains('hidden') && !userBtn.contains(e.target)) {
                userDropdown.classList.add('opacity-0', '-translate-y-2');
                setTimeout(() => userDropdown.classList.add('hidden'), 300);
            }
        });

        document.querySelectorAll('.produk-item').forEach(item => {
            const decreaseBtn = item.querySelector('.decrease-btn');
            const increaseBtn = item.querySelector('.increase-btn');
            const quantityInput = item.querySelector('.quantity-input');

            decreaseBtn.addEventListener('click', () => {
                let currentValue = parseInt(quantityInput.value);
                if (currentValue > 1) quantityInput.value = currentValue - 1;
            });

            increaseBtn.addEventListener('click', () => {
                let currentValue = parseInt(quantityInput.value);
                let max = parseInt(quantityInput.max);
                if (currentValue < max) quantityInput.value = currentValue + 1;
            });
        });
    });

    function filterProduk(kategori) {
        document.querySelectorAll('.produk-item').forEach(item => {
            item.style.display = 'none';
            const itemkategori = item.dataset.kategori;
            if (kategori === 'semua' || itemkategori === kategori) {
                // Use block or flex depending on your layout needs
                item.style.display = 'block'; 
            }
        });
        
        document.querySelectorAll('.kategori-btn').forEach(btn => btn.classList.remove('active'));
        event.currentTarget.classList.add('active');
    }
    </script>
</body>
</html>
