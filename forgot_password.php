<?php
session_start();
// Di sini Anda bisa menambahkan logika PHP untuk menangani pesan error atau sukses
// Contoh:
$error = $_SESSION['error_message'] ?? '';
$success = $_SESSION['success_message'] ?? '';
// unset($_SESSION['error_message'], $_SESSION['success_message']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - Pempek Biniku</title>
    <!-- Pastikan path ke CSS atau CDN sama dengan halaman login -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #F9FAFB; /* Sedikit abu-abu seperti di halaman login */ }
    </style>
</head>
<body>

<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        
        <div class="bg-white p-8 rounded-xl shadow-lg">
            
            <div class="text-center mb-8">
                <a href="../index.php"><img src="./assets/foto/logo.png" class="w-24 h-auto mx-auto mb-4" alt="Logo Pempek Biniku"></a>
                <h2 class="mt-4 text-3xl font-bold text-gray-900">
                    Lupa Password Anda?
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    Jangan khawatir. Masukkan email Anda di bawah ini.
                </p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md relative mb-6" role="alert">
                    <p class="font-bold">Gagal</p>
                    <p><?php echo $error; ?></p>
                </div>
                
            <?php unset($_SESSION['error_message'], $_SESSION['success_message']); endif; ?>


            <?php  if (!empty($success)): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md relative mb-6" role="alert">
                    <p class="font-bold">Berhasil</p>
                    <p><?php echo $success; ?></p>
                </div>
            <?php unset($_SESSION['error_message'], $_SESSION['success_message']); endif;  ?>

            <!-- Ganti action ke file proses reset password -->
            <form action="proses_lupa_password.php" method="post" class="space-y-6">
                
                <!-- Input Email -->
                <div class="relative">
                    <label for="email" class="sr-only">Email</label>
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                        <i class="fa-solid fa-envelope text-gray-400"></i>
                    </span>
                    <input id="email" type="email" name="email" class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" placeholder="Masukkan alamat email Anda" required>
                </div>

                <div>
                    <button type="submit" name="reset" class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-300">
                        Kirim Link Reset Password
                    </button>
                </div>
            </form>

            <div class="text-center mt-6">
                <p class="text-sm text-gray-600">
                    Ingat password Anda? 
                    <a href="login.php" class="text-red-600 hover:text-red-500">Kembali ke Login</a>
                </p>
            </div>

        </div>
    </div>
</div>

</body>
</html>