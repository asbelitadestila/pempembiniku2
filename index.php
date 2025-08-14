<?php
session_start();
include "koneksi/koneksi.php";

// --- PENINGKATAN KEAMANAN: MENGGUNAKAN PREPARED STATEMENTS ---
// Mengambil semua data produk dengan join ke kategori
$produk = array();
$query_produk = "SELECT * FROM produk JOIN kategori ON produk.id_kategori = kategori.id_kategori";
$stmt_produk = $koneksi->prepare($query_produk);
$stmt_produk->execute();
$result_produk = $stmt_produk->get_result();
while ($pecah = $result_produk->fetch_assoc()) {
    $produk[] = $pecah;
}

// Menangani penambahan produk ke keranjang
if (isset($_POST['beli'])) {
    if (!isset($_SESSION['id_admin'])) {
        echo "<script>alert('Anda harus login terlebih dahulu untuk menambahkan produk ke keranjang.'); window.location.href='admin/login.php';</script>";
        exit();
    }

    $id_user = $_SESSION['id_admin'];
    $id_produk = $_POST['id_produk'];
    $nama_produk = $_POST['nama_produk'];
    $harga_produk = $_POST['harga_produk'];
    $foto_produk = $_POST['foto_produk'];
    $jumlah = (int)$_POST['jumlah'];

    // 1. Lakukan kalkulasi harga total sebelum query apapun
    $harga_total = $harga_produk * $jumlah;

    // Cek apakah produk sudah ada di keranjang
    $query_cek = "SELECT jumlah FROM keranjang_user WHERE id_user = ? AND id_produk = ?";
    $stmt_cek = $koneksi->prepare($query_cek);
    $stmt_cek->bind_param("ii", $id_user, $id_produk);
    $stmt_cek->execute();
    $result_cek = $stmt_cek->get_result();

    if ($result_cek->num_rows > 0) {
        // Jika produk sudah ada, UPDATE jumlah dan harga_keseluruhan

        // 2. Perbaiki Query UPDATE
        // Langsung kalkulasi di dalam query agar lebih efisien dan aman
        $query_update = "UPDATE keranjang_user SET jumlah = jumlah + ?, harga_keseluruhan = harga_keseluruhan +  $harga_total WHERE id_user = ? AND id_produk = ?";
        $stmt_update = $koneksi->prepare($query_update);
        // bind_param butuh variabel, jadi kita siapkan dulu jumlah barunya
        $jumlah_baru = $jumlah;
        $stmt_update->bind_param("iii", $jumlah_baru, $id_user, $id_produk);
        $stmt_update->execute();

    } else {
        // Jika produk belum ada, INSERT data baru

        // 3. Perbaiki Query INSERT
        $query_insert = "INSERT INTO keranjang_user (id_user, id_produk, nama, harga, harga_keseluruhan, foto, jumlah) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt_insert = $koneksi->prepare($query_insert);
        // Gunakan variabel $harga_total yang sudah dihitung
        // Tipe data untuk harga bisa 'd' (double) jika ada desimal
        $stmt_insert->bind_param("iisidss", $id_user, $id_produk, $nama_produk, $harga_produk, $harga_total, $foto_produk, $jumlah);
        $stmt_insert->execute();
    }

    // echo "<script>alert('Produk berhasil ditambahkan ke keranjang');</script>";
    $_SESSION['success_message'] = "$nama_produk sudah ditambahkan ke dalam keranjang";
    header("Location: index.php#produk");
    exit();
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
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <style>
    /* Menggunakan font dari Google Fonts */
    body {
        font-family: 'Poppins', sans-serif;
    }

    .font-playfair {
        font-family: 'Playfair Display', serif;
    }

    /* Custom class untuk transisi smooth */
    .transition-all-smooth {
        transition: all 0.3s ease-in-out;
    }

    /* Underline effect untuk nav link */
    .nav-link {
        position: relative;
    }

    .nav-link::after {
        content: '';
        position: absolute;
        width: 0;
        height: 2px;
        display: block;
        margin-top: 5px;
        right: 0;
        background: #FFFAE7;
        transition: width 0.3s ease;
    }

    .nav-link:hover::after {
        width: 100%;
        left: 0;
        background: #e9967a;
        /* darksalmon */
    }

    /* Hide number input spinners */
    input[type='number']::-webkit-outer-spin-button,
    input[type='number']::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    input[type='number'] {
        -moz-appearance: textfield;
    }
    </style>
</head>

<body class="bg-[#FFFAE7] text-gray-800">

    <!-- Navbar -->
    <nav
        class="flex items-center justify-between bg-red-700 px-4 sm:px-8 py-2 fixed w-full top-0 left-0 z-50 shadow-md">
        <!-- Logo -->
        <a href="index.php"><img src="./assets/foto/logo.png" class="w-20" alt="Logo Pempek Biniku"></a>

        <div class="hidden md:flex space-x-8">
            <a href="index.php" class="text-white font-bold hover:text-yellow-200 transition-colors">Beranda</a>
            <a href="index.php#about" class="text-white font-bold hover:text-yellow-200 transition-colors">About</a>
            <a href="index.php#produk" class="text-white font-bold hover:text-yellow-200 transition-colors">Produk</a>
            <a href="index.php#kontak" class="text-white font-bold hover:text-yellow-200 transition-colors">Kontak</a>
        </div>

        <div class="flex items-center space-x-4">
            <?php if (isset($_SESSION['username'])): ?>
            <!-- Ikon Keranjang -->
            <a href="keranjang.php" class="relative">
                <i class="fas fa-shopping-cart text-white text-xl"></i>
                <?php
                        $user_id = $_SESSION['id_admin'];
                        $stmt_cart_count = $koneksi->prepare("SELECT COUNT(*) as total FROM keranjang_user WHERE id_user = ?");
                        $stmt_cart_count->bind_param("i", $user_id);
                        $stmt_cart_count->execute();
                        $result_cart_count = $stmt_cart_count->get_result();
                        $data_cart_count = $result_cart_count->fetch_assoc();
                        if ($data_cart_count['total'] > 0) {
                            echo '<span class="absolute -top-2 -right-3 bg-white text-red-600 rounded-full text-xs w-5 h-5 flex items-center justify-center font-bold">' . $data_cart_count['total'] . '</span>';
                        }
                    ?>
            </a>

            <!-- Ikon User & Dropdown -->
            <div class="relative">
                <button id="btn-user" class="focus:outline-none"><i class="fas fa-user text-white text-xl"></i></button>
                <div class="relative inline-block text-left">
                    <div id="user-dropdown"
                        class="hidden origin-top-right absolute right-0 mt-2 w-64 rounded-lg shadow-xl bg-white ring-1 ring-black ring-opacity-5 focus:outline-none transition ease-out duration-100 transform opacity-0 scale-95"
                        role="menu" aria-orientation="vertical" aria-labelledby="user-menu-button">

                        <div class="py-1" role="none">
                            <!-- Header Section with User Info -->
                            <div class="flex items-center px-4 py-3 border-b border-gray-200">
                                <div
                                    class="flex-shrink-0 h-10 w-10 rounded-full bg-red-600 text-white flex items-center justify-center font-bold text-lg">
                                    <?php 
                            // Display the first letter of the username as an initial
                            // echo strtoupper(substr($_SESSION['username'], 0, 1)); 
                            echo "A"; // Placeholder
                        ?>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-semibold text-gray-800 truncate">
                                        <?php 
                                // echo htmlspecialchars($_SESSION['username']); 
                                echo "Nama Pengguna"; // Placeholder
                            ?>
                                    </p>
                                    <p class="text-xs text-gray-500 truncate">
                                        <?php 
                                // You can add the user's email here if available in session
                                // echo htmlspecialchars($_SESSION['email']); 
                                echo "user@example.com"; // Placeholder
                            ?>
                                    </p>
                                </div>
                            </div>

                            <!-- Navigation Links -->
                            <div class="py-2" role="none">
                                <a href="profil.php"
                                    class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition-colors"
                                    role="menuitem">
                                    <i class="fas fa-user-edit w-6 text-gray-500"></i>
                                    <span>Profil Saya</span>
                                </a>
                                <a href="keranjang.php"
                                    class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition-colors"
                                    role="menuitem">
                                    <i class="fas fa-receipt w-6 text-gray-500"></i>
                                    <span>Pesanan Saya</span>
                                </a>
                            </div>

                            <!-- Logout Link -->
                            <div class="border-t border-gray-200" role="none"></div>
                            <a href="admin/logout.php"
                                class="flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50 hover:font-semibold transition-all duration-150"
                                role="menuitem">
                                <i class="fas fa-sign-out-alt w-6"></i>
                                <span>Logout</span>
                            </a>
                        </div>
                    </div>
                </div>

                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const menuButton = document.getElementById('user-menu-button');
                    const dropdown = document.getElementById('user-dropdown');

                    menuButton.addEventListener('click', function(event) {
                        event.stopPropagation();
                        const isHidden = dropdown.classList.contains('hidden');

                        if (isHidden) {
                            // Show dropdown
                            dropdown.classList.remove('hidden');
                            setTimeout(() => {
                                dropdown.classList.remove('opacity-0', 'scale-95');
                                dropdown.classList.add('opacity-100', 'scale-100');
                            }, 10); // Small delay to allow transition
                        } else {
                            // Hide dropdown
                            dropdown.classList.remove('opacity-100', 'scale-100');
                            dropdown.classList.add('opacity-0', 'scale-95');
                            setTimeout(() => {
                                dropdown.classList.add('hidden');
                            }, 100); // Match transition duration
                        }
                    });

                    // Close dropdown when clicking outside
                    window.addEventListener('click', function(event) {
                        if (!dropdown.classList.contains('hidden') && !menuButton.contains(event
                            .target)) {
                            dropdown.classList.remove('opacity-100', 'scale-100');
                            dropdown.classList.add('opacity-0', 'scale-95');
                            setTimeout(() => {
                                dropdown.classList.add('hidden');
                            }, 100);
                        }
                    });
                });
                </script>
            </div>
            <?php else: ?>
            <div class="hidden sm:flex items-center space-x-2">
                <a href="admin/login.php"
                    class="bg-white text-red-600 px-5 py-2 rounded-full font-semibold hover:bg-gray-200 transition-all-smooth text-sm">Login</a>
                <a href="daftar.php"
                    class="border-2 border-white text-white px-5 py-2 rounded-full font-semibold hover:bg-white hover:text-red-600 transition-all-smooth text-sm">Daftar</a>
            </div>
            <?php endif; ?>

            <!-- Tombol Menu Mobile -->
            <button id="btn-menu" class="lg:hidden text-white text-2xl"><i class="fas fa-bars"></i></button>
        </div>
    </nav>

    <!-- Mobile Menu -->
    <div id="mobile-menu"
        class="hidden lg:hidden fixed top-0 left-0 w-full h-full bg-red-700 z-40 p-8 text-center space-y-6">
        <a href="#home" class="block text-white text-2xl font-semibold">Beranda</a>
        <a href="#about" class="block text-white text-2xl font-semibold">Tentang Kami</a>
        <a href="#produk" class="block text-white text-2xl font-semibold">Produk</a>
        <a href="#kontak" class="block text-white text-2xl font-semibold">Kontak</a>
        <?php if (!isset($_SESSION['username'])): ?>
        <div class="pt-6">
            <a href="admin/login.php"
                class="bg-white text-red-600 px-8 py-3 rounded-full font-semibold hover:bg-gray-200 transition-all-smooth">Login</a>
        </div>
        <?php endif; ?>
    </div>


    <!-- Hero Section with Carousel -->
    <section id="home" class="relative w-full h-screen overflow-hidden">

        <div id="carousel-container" class="relative w-full h-full">
            <!-- Slide 1 -->
            <div class="carousel-item active absolute inset-0 transition-opacity duration-1000 ease-in-out">
                <div class="absolute inset-0 bg-black/40"></div>
                <img src="./assets/foto/banner44.jpg" class="w-full h-full object-cover" alt="Pempek Khas Palembang">
                <div class="absolute inset-0 flex flex-col items-center justify-center text-white text-center p-4">
                    <h1 class="font-playfair text-5xl md:text-7xl font-extrabold drop-shadow-lg">Pempek Biniku</h1>
                    <p class="mt-4 text-lg md:text-2xl font-medium drop-shadow-md">Cita Rasa Autentik Palembang</p>
                </div>
            </div>
            <!-- Slide 2 -->
            <div class="carousel-item absolute inset-0 transition-opacity duration-1000 ease-in-out opacity-0">
                <div class="absolute inset-0 bg-black/40"></div>
                <img src="./assets/foto/banner22.jpg" class="w-full h-full object-cover" alt="Bahan Pilihan Terbaik">
                <div class="absolute inset-0 flex flex-col items-center justify-center text-white text-center p-4">
                    <h1 class="font-playfair text-5xl md:text-7xl font-extrabold drop-shadow-lg">Kualitas Terbaik</h1>
                    <p class="mt-4 text-lg md:text-2xl font-medium drop-shadow-md">Dari Bahan Pilihan Terbaik</p>
                </div>
            </div>
            <!-- Slide 3 -->
            <div class="carousel-item absolute inset-0 transition-opacity duration-1000 ease-in-out opacity-0">
                <div class="absolute inset-0 bg-black/40"></div>
                <img src="./assets/foto/banner33.jpg" class="w-full h-full object-cover" alt="Pesan Pempek Sekarang">
                <div class="absolute inset-0 flex flex-col items-center justify-center text-white text-center p-4">
                    <h1 class="font-playfair text-5xl md:text-7xl font-extrabold drop-shadow-lg">Pesan Sekarang</h1>
                    <p class="mt-4 text-lg md:text-2xl font-medium drop-shadow-md">Nikmati Kelezatan di Rumah Anda</p>
                </div>
            </div>
        </div>
        <!-- Carousel Controls -->
        <button id="prev-btn"
            class="absolute top-1/2 left-4 transform -translate-y-1/2 z-10 text-white bg-black/30 w-12 h-12 rounded-full hover:bg-black/50 transition-all-smooth"><i
                class="fas fa-chevron-left"></i></button>
        <button id="next-btn"
            class="absolute top-1/2 right-4 transform -translate-y-1/2 z-10 text-white bg-black/30 w-12 h-12 rounded-full hover:bg-black/50 transition-all-smooth"><i
                class="fas fa-chevron-right"></i></button>
    </section>

    <main class="container mx-auto px-4 md:px-8">



        <div class="relative flex items-center min-h-screen overflow-hidden">

            <!-- <div class="absolute top-0 left-0 h-full w-[55%] bg-[#F9F6F3] rounded-tr-[250px] rounded-br-[250px]"></div> -->

            <div class="relative container mx-auto px-12 flex items-center z-10">

                <div class="w-1/2 pr-16">
                    <div class="w-16 h-1 bg-red-600 mb-8"></div>

                    <h1 class="text-6xl font-bold text-gray-800 mb-6">Pempek Biniku</h1>

                    <p class="text-gray-500 mb-10 text-base leading-relaxed">
                        Satu gigitan pempek kami akan membawa Anda pada perjalanan rasa tak terlupakan, memadukan
                        tradisi Palembang dengan sentuhan modern yang unik dan istimewa.
                    </p>

                    <button
                        class="bg-red-600 text-white font-semibold uppercase tracking-widest px-10 py-3 rounded-full hover:bg-red-700 transition duration-300 ease-in-out shadow-md hover:shadow-lg">
                        Selengkapnya
                    </button>
                </div>

            </div>

        </div>

        <?php if (isset($_SESSION['success_message'])) : ?>

            <div id="toast-notification" class="fixed top-20 right-6 z-50 transition-opacity duration-300 ease-in-out">
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 px-6 py-4 rounded-md shadow-lg flex items-center">
                    <svg viewBox="0 0 24 24" class="text-green-600 w-6 h-6 mr-3" fill="currentColor">
                        <path d="M12,0A12,12,0,1,0,24,12,12.014,12.014,0,0,0,12,0Zm6.927,8.2-6.845,9.289a1.011,1.011,0,0,1-1.43.188L5.764,13.769a1,1,0,1,1,1.25-1.562l4.076,3.261,6.227-8.451A1,1,0,1,1,18.927,8.2Z"></path>
                    </svg>
                    <span class="text-green-800 font-medium">
                        <?php echo htmlspecialchars($_SESSION['success_message']); ?>
                    </span>
                </div>
            </div>

            <script>
                // Ambil elemen toast
                const toast = document.getElementById('toast-notification');

                // Pastikan elemen ada sebelum menjalankan timer
                if (toast) {
                    // Atur timer untuk 2 detik (2000 milidetik)
                    setTimeout(() => {
                        // Mulai transisi fade-out
                        toast.classList.add('opacity-0');

                        // Setelah transisi selesai (300ms), sembunyikan elemen sepenuhnya
                        setTimeout(() => {
                            toast.style.display = 'none';
                        }, 300); // Durasi ini harus sama dengan 'duration-300' di class

                    }, 2000); // Waktu tampilan toast
                }
            </script>

            <?php
                // Hapus session setelah ditampilkan agar tidak muncul lagi saat refresh
                unset($_SESSION['success_message']);
            ?>

        <?php endif; ?>

        <!-- Featured Products Section -->
        <section id="featured-products" class="py-20 md:py-28 text-center bg-white rounded-lg shadow-xl">
            <h2 class="font-playfair text-4xl font-bold text-red-600 mb-4">Produk Unggulan</h2>
            <p class="max-w-2xl mx-auto text-gray-600 mb-12">Pilihan terbaik berdasarkan metode SPK SMART kami.</p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Top 3 Products will be dynamically inserted here -->
                <?php
                // Placeholder for top 3 products
                $top_products = array_slice($produk, 0, 3);
                foreach ($top_products as $index => $value):
                ?>
                <div class="group">
                    <div class="relative">
                        <img src="assets/foto/<?php echo htmlspecialchars($value['foto_produk']); ?>"
                            class="w-full h-64 object-cover rounded-lg shadow-md"
                            alt="<?php echo htmlspecialchars($value['nama_produk']); ?>">
                        <div
                            class="absolute top-0 right-0 bg-red-600 text-white text-xs font-bold px-3 py-1 rounded-bl-lg">
                            #<?php echo $index + 1; ?> Best Seller
                        </div>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mt-4">
                        <?php echo htmlspecialchars($value['nama_produk']); ?></h3>
                    <p class="text-lg font-semibold text-red-600">Rp
                        <?php echo number_format($value['harga_produk']); ?></p>
                    <a href="index.php#produk"
                        class="inline-block mt-4 bg-red-600 text-white py-2 px-6 rounded-lg font-semibold hover:bg-red-700 transition-all-smooth">
                        Lihat Detail
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Produk Section -->
        <section id="produk" class="py-20 md:py-28 text-center">
            <h2 class="font-playfair text-4xl font-bold text-red-600 mb-4">Produk Kami</h2>
            <p class="max-w-2xl mx-auto text-gray-600 mb-12">Jelajahi berbagai pilihan pempek kami, dari satuan hingga
                paket lengkap.</p>

            <!-- Filter Buttons -->
            <div id="filter-buttons" class="flex flex-wrap justify-center gap-2 md:gap-4 mb-12">
                <button class="kategori-btn active" onclick="filterProduk('semua')">Semua</button>
                <button class="kategori-btn" onclick="filterProduk('pempek satuan')">Pempek Satuan</button>
                <button class="kategori-btn" onclick="filterProduk('pempek paket')">Pempek Paket</button>
                <button class="kategori-btn" onclick="filterProduk('other')">Lainnya</button>
            </div>

            <div id="produk-list" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 md:gap-8">
                <?php foreach ($produk as $value): ?>
                <div class="produk-item group"
                    data-kategori="<?php echo strtolower(htmlspecialchars($value['nama_kategori'])); ?>">
                    <form action="index.php" method="post"
                        class="bg-white rounded-lg shadow-lg h-full flex flex-col overflow-hidden transition-all-smooth transform hover:-translate-y-2 hover:shadow-2xl">
                        <div class="relative">
                            <img src="assets/foto/<?php echo htmlspecialchars($value['foto_produk']); ?>"
                                class="w-full h-56 object-cover"
                                alt="<?php echo htmlspecialchars($value['nama_produk']); ?>">
                            <div
                                class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-all-smooth">
                            </div>
                        </div>
                        <div class="p-5 flex flex-col flex-grow">
                            <h3 class="text-lg font-bold text-gray-800">
                                <?php echo htmlspecialchars($value['nama_produk']); ?></h3>
                            <p class="text-sm text-gray-500 mb-2">Stok:
                                <?php echo number_format($value['stok_produk']); ?></p>
                            <p class="text-xl font-bold text-red-600 my-2">Rp
                                <?php echo number_format($value['harga_produk']); ?></p>

                            <div class="mt-auto pt-4">
                                <!-- Hidden fields -->
                                <input type="hidden" name="id_produk" value="<?php echo $value['id_produk']; ?>">
                                <input type="hidden" name="nama_produk"
                                    value="<?php echo htmlspecialchars($value['nama_produk']); ?>">
                                <input type="hidden" name="harga_produk" value="<?php echo $value['harga_produk']; ?>">
                                <input type="hidden" name="foto_produk"
                                    value="<?php echo htmlspecialchars($value['foto_produk']); ?>">
                                <div class="flex gap-2 items-center justify-center mb-4">
                                    <button type="button" class="quantity-btn decrease-btn">-</button>
                                    <input type="number" name="jumlah" value="1" min="1"
                                        max="<?php echo $value['stok_produk']; ?>"
                                        class="quantity-input border-2 border-gray-300 text-center rounded-md">
                                    <button type="button" class="quantity-btn increase-btn">+</button>
                                </div>
                                <button type="submit" name="beli"
                                    class="w-full bg-red-600 text-white py-2.5 px-4 rounded-lg font-semibold hover:bg-red-700 transition-all-smooth">
                                    <i class="fas fa-cart-plus mr-2"></i>Keranjang
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <!-- Kontak Section -->
    <section id="kontak" class="py-20 md:py-28 bg-white">
        <div class="container mx-auto px-4 md:px-8 text-center">
            <h2 class="font-playfair text-4xl font-bold text-red-600 mb-4">Hubungi Kami</h2>
            <p class="max-w-2xl mx-auto text-gray-600 mb-12">Temukan lokasi kami atau hubungi kami melalui media sosial.
            </p>
            <div class="rounded-lg overflow-hidden shadow-xl">
                <iframe class="w-full h-96"
                    src="https://www.google.com/maps/embed?pb=!1m17!1m12!1m3!1d16335807.982538566!2d92.02327591828396!3d-1.6145300000000005!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m2!1m1!2zMcKwMzYnNTIuMyJTIDEwM8KwMzcnMzAuMSJF!5e0!3m2!1sen!2sid!4v1724488443830!5m2!1sen!2sid"
                    allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-red-700 text-white py-10">
        <div class="container mx-auto px-4 md:px-8 text-center">
            <h3 class="text-xl font-bold mb-4">Ikuti Kami</h3>
            <div class="flex justify-center space-x-6 mb-6">
                <a href="https://instagram.com/pempekbiniku_" target="_blank" aria-label="Instagram"
                    class="w-10 h-10 flex items-center justify-center bg-white/20 rounded-full hover:bg-white/40 transition-colors">
                    <i class="fab fa-instagram text-xl"></i>
                </a>
                <a href="https://www.tokopedia.com/pempekbiniku" target="_blank" aria-label="Tokopedia"
                    class="w-10 h-10 flex items-center justify-center bg-white/20 rounded-full hover:bg-white/40 transition-colors">
                    <img src="assets/foto/tokopedia.png" alt="Tokopedia" class="w-5 h-5">
                </a>
                <a href="https://shopee.co.id/pempekbiniku" target="_blank" aria-label="Shopee"
                    class="w-10 h-10 flex items-center justify-center bg-white/20 rounded-full hover:bg-white/40 transition-colors">
                    <img src="assets/foto/shopee.png" alt="Shopee" class="w-5 h-5">
                </a>
            </div>
            <p class="text-sm text-white/80">&copy; <?php echo date("Y"); ?> Pempek Biniku. All Rights Reserved.</p>
        </div>
    </footer>

    <style>
    .kategori-btn {
        @apply px-5 py-2 text-base rounded-full border-2 border-red-600 text-red-600 font-semibold transition-all-smooth hover: bg-red-600 hover:text-white;
    }

    .kategori-btn.active {
        @apply bg-red-600 text-white;
    }

    .quantity-btn {
        @apply w-8 h-8 bg-gray-200 text-gray-700 rounded-full font-bold text-lg hover: bg-gray-300 transition-all-smooth;
    }

    .quantity-input {
        @apply w-16 text-center border-y border-gray-300 mx-2 text-lg font-semibold focus: outline-none;
    }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Navbar & Dropdown Logic
        const userBtn = document.getElementById('btn-user');
        const userDropdown = document.getElementById('user-dropdown');
        const menuBtn = document.getElementById('btn-menu');
        const mobileMenu = document.getElementById('mobile-menu');

        if (userBtn) {
            userBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                userDropdown.classList.toggle('hidden');
                userDropdown.classList.toggle('opacity-0');
                userDropdown.classList.toggle('-translate-y-2');
            });
        }

        if (menuBtn) {
            menuBtn.addEventListener('click', () => mobileMenu.classList.toggle('hidden'));
        }

        // Close dropdown when clicking outside
        window.addEventListener('click', (e) => {
            if (userDropdown && !userDropdown.classList.contains('hidden') && !userBtn.contains(e
                    .target)) {
                userDropdown.classList.add('hidden', 'opacity-0', '-translate-y-2');
            }
        });

        // Carousel Logic
        const carouselItems = document.querySelectorAll('.carousel-item');
        let currentIndex = 0;

        function showSlide(index) {
            carouselItems.forEach((item, i) => {
                item.classList.toggle('opacity-0', i !== index);
            });
        }

        document.getElementById('next-btn').addEventListener('click', () => {
            currentIndex = (currentIndex + 1) % carouselItems.length;
            showSlide(currentIndex);
        });

        document.getElementById('prev-btn').addEventListener('click', () => {
            currentIndex = (currentIndex - 1 + carouselItems.length) % carouselItems.length;
            showSlide(currentIndex);
        });

        setInterval(() => {
            document.getElementById('next-btn').click();
        }, 5000); // Auto-slide every 5 seconds

        // Quantity buttons logic
        document.querySelectorAll('.produk-item').forEach(item => {
            const decreaseBtn = item.querySelector('.decrease-btn');
            const increaseBtn = item.querySelector('.increase-btn');
            const quantityInput = item.querySelector('.quantity-input');

            decreaseBtn.addEventListener('click', () => {
                let currentValue = parseInt(quantityInput.value);
                if (currentValue > 1) {
                    quantityInput.value = currentValue - 1;
                }
            });

            increaseBtn.addEventListener('click', () => {
                let currentValue = parseInt(quantityInput.value);
                let max = parseInt(quantityInput.max);
                if (currentValue < max) {
                    quantityInput.value = currentValue + 1;
                }
            });
        });
    });

    // Filter products function
    function filterProduk(kategori) {
        document.querySelectorAll('.produk-item').forEach(item => {
            const itemkategori = item.dataset.kategori;
            item.style.display = (kategori === 'semua' || itemkategori === kategori) ? 'flex' : 'none';
        });

        document.querySelectorAll('.kategori-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        event.currentTarget.classList.add('active');
    }
    </script>
</body>

</html>