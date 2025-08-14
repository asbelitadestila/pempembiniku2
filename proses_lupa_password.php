<?php
// proses_lupa_password.php

session_start();
include("./koneksi/koneksi.php"); // Sesuaikan path koneksi
require './vendor/autoload.php'; // Path ke autoload.php dari Composer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (isset($_POST['reset'])) {
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);

    // 1. Cek apakah email ada di database
    $query = "SELECT id_admin FROM user WHERE email = ?"; // Ganti 'users' dengan tabel Anda
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        // 2. Buat Token yang Aman dan Waktu Kedaluwarsa
        $token = bin2hex(random_bytes(32));
        $expires = date("Y-m-d H:i:s", time() + 60 * 15); // Token valid selama 15 menit

        // 3. Simpan token ke database
        $query_update = "UPDATE user SET reset_token = ?, reset_token_expires_at = ? WHERE email = ?";
        $stmt_update = mysqli_prepare($koneksi, $query_update);
        mysqli_stmt_bind_param($stmt_update, "sss", $token, $expires, $email);
        mysqli_stmt_execute($stmt_update);

        // 4. Kirim Email menggunakan PHPMailer
        $mail = new PHPMailer(true);
        try {
            // Konfigurasi Server SMTP (Contoh menggunakan Gmail)
            $mail->isSMTP();
        // $mail->Host       = 'smtp.gmail.com';                     // Ganti dengan host SMTP Anda (cth: smtp.gmail.com)
        $mail->Host       = 'pop.mail.yahoo.com';                     // Ganti dengan host SMTP Anda (cth: smtp.gmail.com)
        $mail->SMTPAuth   = true;
        // $mail->Username   = 'bangriccstore@gmail.com';                 // Ganti dengan username SMTP Anda (email Anda)
        $mail->Username =  'asbelitafarasy@yahoo.com';
        // $mail->Password   = 'stli bhpg aaak ylhh';                 // Ganti dengan password SMTP atau App Password Anda
        $mail->Password = 'okewnwuibwqfmqfg';
        $mail->SMTPSecure = 'SSL';
        $mail->Port       = 995;

            // Penerima
            $mail->setFrom('no-reply@pempekbiniku.com', 'Pempek Biniku');
            $mail->addAddress($email);

            // Konten Email
            $reset_link = "http://localhost/pempekbiniku/reset_password.php?token=" . $token; // Sesuaikan URL
            $mail->isHTML(true);
            $mail->Subject = 'Reset Password Akun Pempek Biniku';
            $mail->Body    = "Halo,<br><br>Kami menerima permintaan untuk mereset password akun Anda. Silakan klik link di bawah ini untuk melanjutkan:<br><br><a href='{$reset_link}'>Reset Password Saya</a><br><br>Jika Anda tidak meminta ini, abaikan saja email ini.<br><br>Terima kasih,<br>Tim Pempek Biniku";

            $mail->send();
            $_SESSION['success_message'] = "Jika email Anda terdaftar, kami telah mengirimkan link reset password.";
            header("Location: forgot_password.php");
        } catch (Exception $e) {
            // Jika email gagal terkirim, Anda bisa mencatat errornya
            error_log("Mailer Error: {$mail->ErrorInfo}");
            $_SESSION['error_message'] = "Tidak Dapat Mengirim Email, error $mail->ErrorInfo.";
            header("Location: forgot_password.php");
        }
    }

    // PENTING: Selalu tampilkan pesan sukses generik untuk keamanan
    // Ini mencegah orang menebak email mana yang terdaftar di sistem.
    $_SESSION['success_message'] = "Jika email Anda terdaftar, kami telah mengirimkan link reset password.";
    header("Location: forgot_password.php");
    exit();
}