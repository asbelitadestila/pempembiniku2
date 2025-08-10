<?php
session_start(); // Mulai session

// Jika pengguna sudah login, redirect ke halaman yang sesuai
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: index.php');
        exit();
    } elseif ($_SESSION['role'] === 'user') {
        header('Location: ../index.php');
        exit();
    }
}

include '../koneksi/koneksi.php'; // Koneksi ke database

$error = ''; // Variabel untuk menyimpan pesan error

if (isset($_POST['login'])) {
    $username = $_POST['user'];
    $password = $_POST['pass'];

    // --- PENINGKATAN KEAMANAN: MENGGUNAKAN PREPARED STATEMENTS ---
    // Query untuk memeriksa kredensial pengguna dengan cara yang aman
    $query = "SELECT * FROM user WHERE username = ?";
    
    $stmt = mysqli_prepare($koneksi, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) === 1) {
            $user = mysqli_fetch_assoc($result);

            // --- PENINGKATAN KEAMANAN: MENGGUNAKAN password_verify() ---
            // Verifikasi password yang di-hash
            if (password_verify($password, $user['password'])) {
                // Password cocok, login berhasil

                // Simpan informasi pengguna dalam session
                $_SESSION['id_admin'] = $user['id_admin'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['noHp'] = $user['noHp'];
                $_SESSION['alamat'] = $user['alamat'];
                $_SESSION['role'] = $user['role'];

                // Redirect berdasarkan peran
                if ($user['role'] === 'admin') {
                    echo "<script>
                            alert('Login berhasil sebagai admin');
                            window.location.href = 'index.php';
                          </script>";
                } elseif ($user['role'] === 'user') {
                    echo "<script>
                            alert('Login berhasil sebagai user');
                            window.location.href = '../index.php';
                          </script>";
                }
                exit();
            } else {
                // Password salah
                $error = "Username atau Password salah.";
            }
        } else {
            // Username tidak ditemukan
            $error = "Username atau Password salah.";
        }
        mysqli_stmt_close($stmt);
    } else {
        // Gagal mempersiapkan statement
        $error = "Terjadi kesalahan pada sistem. Silakan coba lagi nanti.";
    }
}

/*
 * CATATAN PENTING UNTUK REGISTRASI PENGGUNA BARU:
 * Saat membuat fitur registrasi atau menambahkan pengguna baru,
 * JANGAN simpan password secara langsung. Gunakan password_hash().
 *
 * Contoh:
 * $plain_password = 'password123';
 * $hashed_password = password_hash($plain_password, PASSWORD_BCRYPT);
 *
 * Lalu simpan $hashed_password ke kolom 'password' di database.
 * Kolom 'password' di database harus berjenis VARCHAR(255) untuk menampung hash.
*/
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Halaman Login Pempek Biniku">
    <meta name="author" content="">

    <title>Login - Pempek Biniku</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome untuk Ikon -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet" type="text/css">
    
    <style>
        /* Menambahkan font yang lebih modern jika tersedia */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>

<body class="bg-[#FFFAE7]">

    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            
            <div class="bg-white p-8 rounded-xl shadow-lg">
                
                <div class="text-center mb-8">
                    <a href="index.php"><img src="../assets/foto/logo.png" class="w-24 h-auto mx-auto mb-4" alt="Logo Pempek Biniku"></a>
                    <h2 class="mt-4 text-3xl font-bold text-gray-900">
                        Selamat Datang Kembali
                    </h2>
                    <p class="mt-2 text-sm text-gray-600">
                        Login untuk melanjutkan ke Pempek Biniku
                    </p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md relative mb-6" role="alert">
                        <p class="font-bold">Gagal Login</p>
                        <p><?php echo $error; ?></p>
                    </div>
                <?php endif; ?>

                <form action="login.php" method="post" class="space-y-6">
                    <input type="hidden" name="remember" value="true">
                    
                    <!-- Input Username -->
                    <div class="relative">
                        <label for="user" class="sr-only">Username</label>
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                            <i class="fa-solid fa-user text-gray-400"></i>
                        </span>
                        <input id="user" type="text" name="user" class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" placeholder="Username" required>
                    </div>

                    <!-- Input Password -->
                    <div class="relative">
                        <label for="pass" class="sr-only">Password</label>
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                            <i class="fa-solid fa-lock text-gray-400"></i>
                        </span>
                        <input id="pass" type="password" name="pass" class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" placeholder="Password" required>
                    </div>

                    <div>
                        <button type="submit" name="login" class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-300">
                            Login
                        </button>
                    </div>
                </form>

                <div class="text-center mt-6">
                    <a href="#" class="text-sm text-red-600 hover:text-red-500">
                        Lupa password?
                    </a>
                </div>

                <div class="text-center mt-4">
                    <p class="text-sm text-gray-600">
                        Belum punya akun? 
                        <a href="daftar.php" class="text-red-600 hover:text-red-500">Daftar Sekarang</a>
                    </p>

            </div>

        </div>
    </div>

</body>
</html>
