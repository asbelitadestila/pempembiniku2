<?php
// reset_password.php
session_start();
include("./koneksi/koneksi.php");

$error = '';
$token_valid = false;

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    // Cek apakah token ada dan belum kedaluwarsa
    $query = "SELECT id_admin FROM user WHERE reset_token = ? AND reset_token_expires_at > NOW()";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "s", $token);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $token_valid = true;
    } else {
        $error = "Token reset tidak valid atau sudah kedaluwarsa.";
    }
} else {
    $error = "Token tidak ditemukan.";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Reset Password</title>
    </head>
<body>
<div class="min-h-screen flex items-center justify-center ...">
    <div class="max-w-md w-full space-y-8">
        <div class="bg-white p-8 rounded-xl shadow-lg">
            <div class="text-center mb-8">
                <h2 class="mt-4 text-3xl font-bold text-gray-900">Buat Password Baru</h2>
            </div>

            <?php if (!empty($error)): ?>
                <div class="bg-red-100 ..."><?php echo $error; ?></div>
            <?php elseif ($token_valid): ?>
                <form action="proses_reset_password.php" method="post" class="space-y-6">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    
                    <div class="relative">
                        <label for="new_pass" class="sr-only">Password Baru</label>
                        <input id="new_pass" type="password" name="new_pass" placeholder="Password Baru" required>
                    </div>

                    <div class="relative">
                        <label for="confirm_pass" class="sr-only">Konfirmasi Password</label>
                        <input id="confirm_pass" type="password" name="confirm_pass" placeholder="Konfirmasi Password Baru" required>
                    </div>

                    <div>
                        <button type="submit" name="update_password">Update Password</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>