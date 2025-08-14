<?php
// proses_reset_password.php
session_start();
include("./koneksi/koneksi.php");

if (isset($_POST['update_password'])) {
    $token = $_POST['token'];
    $new_pass = $_POST['new_pass'];
    $confirm_pass = $_POST['confirm_pass'];

    // 1. Validasi dasar
    if ($new_pass !== $confirm_pass) {
        $_SESSION['error_message'] = "Password dan konfirmasi tidak cocok.";
        header("Location: reset_password.php?token=" . $token);
        exit();
    }
    // Tambahkan validasi kekuatan password jika perlu

    // 2. Cek ulang validitas token
    $query_token = "SELECT id_admin FROM user WHERE reset_token = ? AND reset_token_expires_at > NOW()";
    $stmt_token = mysqli_prepare($koneksi, $query_token);
    mysqli_stmt_bind_param($stmt_token, "s", $token);
    mysqli_stmt_execute($stmt_token);
    $result_token = mysqli_stmt_get_result($stmt_token);

    if (mysqli_num_rows($result_token) > 0) {
        // 3. Hash password baru
        $hashed_password = password_hash($new_pass, PASSWORD_DEFAULT);

        // 4. Update password dan hapus token agar tidak bisa dipakai lagi
        $query_update = "UPDATE user SET password = ?, reset_token = NULL, reset_token_expires_at = NULL WHERE reset_token = ?";
        $stmt_update = mysqli_prepare($koneksi, $query_update);
        mysqli_stmt_bind_param($stmt_update, "ss", $hashed_password, $token);
        mysqli_stmt_execute($stmt_update);

        $_SESSION['success_message'] = "Password Anda berhasil diubah. Silakan login.";
        header("Location: ./admin/login.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Token tidak valid atau sudah kedaluwarsa.";
        header("Location: ./admin/login.php");
        exit();
    }
}