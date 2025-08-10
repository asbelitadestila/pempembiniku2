<?php
session_start();
// include 'koneksi/koneksi.php';

$errors = []; // Array untuk menyimpan pesan error

// Jika pengguna sudah login, redirect
if (isset($_SESSION['role'])) {
    header('Location: index.php');
    exit();
}

if (isset($_POST['daftar'])) {
    // Ambil dan bersihkan data dari form
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $noHp = trim($_POST['no_hp']);
    $email = trim($_POST['email']);
    $alamat = trim($_POST['alamat']);

    // --- VALIDASI INPUT ---
    if (empty($username)) $errors[] = "Username tidak boleh kosong.";
    if (empty($email)) $errors[] = "Email tidak boleh kosong.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Format email tidak valid.";
    if (empty($password)) $errors[] = "Password tidak boleh kosong.";
    if (strlen($password) < 6) $errors[] = "Password minimal harus 6 karakter.";
    if ($password !== $confirm_password) $errors[] = "Konfirmasi password tidak cocok.";

    // Jika tidak ada error validasi, lanjutkan ke pengecekan database
    if (empty($errors)) {
        // --- PENINGKATAN KEAMANAN: MENGGUNAKAN PREPARED STATEMENTS ---
        
        // Cek apakah username atau email sudah ada
        $query_cek = "SELECT username, email FROM user WHERE username = ? OR email = ?";
        $stmt_cek = mysqli_prepare($koneksi, $query_cek);
        mysqli_stmt_bind_param($stmt_cek, "ss", $username, $email);
        mysqli_stmt_execute($stmt_cek);
        $result_cek = mysqli_stmt_get_result($stmt_cek);
        
        if (mysqli_num_rows($result_cek) > 0) {
            $existing_user = mysqli_fetch_assoc($result_cek);
            if ($existing_user['username'] === $username) {
                $errors[] = "Username sudah digunakan. Silakan pilih yang lain.";
            }
            if ($existing_user['email'] === $email) {
                $errors[] = "Email sudah terdaftar.";
            }
        } else {
            // Jika semua aman, hash password dan masukkan data ke database
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $role = 'user'; // Role default untuk pendaftaran

            $query_insert = "INSERT INTO user (username, password, noHp, email, role, alamat) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_insert = mysqli_prepare($koneksi, $query_insert);
            mysqli_stmt_bind_param($stmt_insert, "ssssss", $username, $hashedPassword, $noHp, $email, $role, $alamat);

            if (mysqli_stmt_execute($stmt_insert)) {
                echo "<script>
                        alert('Pendaftaran berhasil! Silakan login dengan akun Anda.');
                        window.location.href='login.php';
                      </script>";
                exit();
            } else {
                $errors[] = "Terjadi kesalahan pada sistem. Gagal mendaftar.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - Pempek Biniku</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet" type="text/css">
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@400;500;600;700&display=swap');
        body { font-family: 'Poppins', sans-serif; }
        .font-playfair { font-family: 'Playfair Display', serif; }
    </style>
</head>
<body class="bg-[#FFFAE7]">

    <div class="container mx-auto flex justify-center items-center min-h-screen py-12 px-4">
        <div class="w-full max-w-lg">
            <div class="bg-white p-8 rounded-xl shadow-lg">
                <div class="text-center mb-8">
                    <a href="index.php"><img src="../assets/foto/logo.png" class="w-24 h-auto mx-auto mb-4" alt="Logo Pempek Biniku"></a>
                    <h2 class="mt-4 text-3xl font-bold text-gray-900">
                        Buat Akun Baru
                    </h2>
                    <p class="mt-2 text-sm text-gray-600">
                        Lengkapi data diri Anda untuk memulai
                    </p>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md mb-6" role="alert">
                        <p class="font-bold">Oops! Terjadi kesalahan:</p>
                        <ul class="list-disc list-inside mt-2 text-sm">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="daftar.php" method="POST" class="space-y-4">
                    <div class="relative">
                        <label for="username" class="sr-only">Username</label>
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3"><i class="fa-solid fa-user text-gray-400"></i></span>
                        <input type="text" name="username" id="username" class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500" placeholder="Username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                    </div>
                    <div class="relative">
                        <label for="email" class="sr-only">Email</label>
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3"><i class="fa-solid fa-envelope text-gray-400"></i></span>
                        <input type="email" name="email" id="email" class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500" placeholder="Alamat Email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                    </div>
                    <div class="relative">
                        <label for="no_hp" class="sr-only">No. HP</label>
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3"><i class="fa-solid fa-phone text-gray-400"></i></span>
                        <input type="text" name="no_hp" id="no_hp" class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500" placeholder="Nomor Handphone" value="<?php echo isset($_POST['no_hp']) ? htmlspecialchars($_POST['no_hp']) : ''; ?>" required>
                    </div>
                    <div class="relative">
                        <label for="password" class="sr-only">Password</label>
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3"><i class="fa-solid fa-lock text-gray-400"></i></span>
                        <input type="password" name="password" id="password" class="w-full pl-10 pr-10 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500" placeholder="Password (min. 6 karakter)" required>
                        <span class="absolute inset-y-0 right-0 flex items-center pr-3 cursor-pointer" onclick="togglePassword('password')"><i id="eye-icon-password" class="fa-solid fa-eye-slash text-gray-400"></i></span>
                    </div>
                    <div class="relative">
                        <label for="confirm_password" class="sr-only">Konfirmasi Password</label>
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3"><i class="fa-solid fa-lock text-gray-400"></i></span>
                        <input type="password" name="confirm_password" id="confirm_password" class="w-full pl-10 pr-10 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500" placeholder="Konfirmasi Password" required>
                        <span class="absolute inset-y-0 right-0 flex items-center pr-3 cursor-pointer" onclick="togglePassword('confirm_password')"><i id="eye-icon-confirm_password" class="fa-solid fa-eye-slash text-gray-400"></i></span>
                    </div>
                    <div>
                        <label for="alamat" class="sr-only">Alamat Lengkap</label>
                        <textarea name="alamat" id="alamat" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 resize-none" rows="3" placeholder="Alamat Lengkap untuk Pengiriman" required><?php echo isset($_POST['alamat']) ? htmlspecialchars($_POST['alamat']) : ''; ?></textarea>
                    </div>
                    <button type="submit" name="daftar" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-md transition duration-300">Daftar Sekarang</button>
                </form>

                <p class="mt-6 text-center text-sm text-gray-600">
                    Sudah punya akun?
                    <a href="login.php" class="font-medium text-red-600 hover:text-red-500">
                        Login di sini
                    </a>
                </p>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = document.getElementById('eye-icon-' + fieldId);
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        }
    </script>
</body>
</html>
