<?php
// Selalu mulai session di awal halaman
session_start();

// Sertakan file koneksi database
include 'koneksi.php';

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['username'])) {
    
    // Ambil data dari form dan sanitasi (sesuai kode Anda)
    $username = $koneksi->real_escape_string($_POST['username']);
    $password_hashed = md5($_POST['password']); // Menggunakan md5 sesuai kode Anda

    // --- 1. Coba Cek di Tabel 'user' (untuk Admin) ---
    // (Asumsi kolom di tabel 'user' adalah 'username' dan 'password')
    $query_admin = $koneksi->query("SELECT * FROM user WHERE username = '$username' AND password = '$password_hashed'");

    if ($query_admin && $query_admin->num_rows > 0) {
        // Login Admin Berhasil
        $data = $query_admin->fetch_assoc();
        $_SESSION['user'] = $data;
        $_SESSION['role'] = $data['role'] ?? 'admin'; // Tetapkan role 'admin'
        
        header("Location: index.php"); // Arahkan admin ke index.php
        exit;
    
    } else {
        // --- 2. Jika Gagal, Coba Cek di Tabel 'driver' ---
        
        // PERHATIKAN: Berdasarkan screenshot, tabel 'driver' menggunakan kolom 'nama', bukan 'username'
        $query_driver = $koneksi->query("SELECT * FROM driver WHERE nama = '$username' AND password = '$password_hashed'");
        
        if ($query_driver && $query_driver->num_rows > 0) {
            // Login Driver Berhasil
            $data = $query_driver->fetch_assoc();
            $_SESSION['user'] = $data; // Kita tetap gunakan session 'user'
            $_SESSION['role'] = $data['role'] ?? 'driver'; // Tapi kita set 'role' nya 'driver'
            
            header("Location: driver.php"); // Arahkan driver ke driver.php
            exit;
            
        } else {
            // --- 3. Jika Gagal di Keduanya ---
            // Login Gagal
            $error_message = "Username atau Password salah!";
        }
    }

}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login MBG - Light Mode</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-900 flex items-center justify-center min-h-screen p-4 sm:p-8">

    <div class="bg-white rounded-3xl shadow-2xl overflow-hidden max-w-4xl w-full flex flex-col md:flex-row border border-gray-200">

        <div class="bg-blue-600 text-white p-12 md:w-8/12 flex flex-col justify-center items-center text-center">
            <div class="max-w-md"> 
                <div class="mb-6"> 
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 mx-auto text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <h1 class="text-4xl font-extrabold mb-4">
                    Selamat Datang, di MBG!
                </h1>
                <p class="text-lg text-blue-200 px-4">
                    Nikmati kemudahan mengakses manajemen katering Anda. Silakan masuk untuk melanjutkan ke akun Anda.
                </p>
            </div>
        </div>

        <div class="flex flex-col justify-center p-12 sm:p-16 md:w-7/12">
            
            <h2 class="text-3xl font-bold text-gray-800 mb-8">
                Masuk ke Akun
            </h2>

            <?php
            // Tampilkan pesan error jika login gagal
            if (!empty($error_message)) {
                echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative mb-6" role="alert">';
                echo '<span class="block sm:inline">' . $error_message . '</span>';
                echo '</div>';
            }
            ?>

            <form class="space-y-8" method="POST" action="login.php">
                
                <div>
                    <label for="username" class="block text-base font-medium text-gray-700">Email atau Username</label>
                    <input type="text" id="username" name="username" placeholder="Masukkan email atau username" required class="mt-2 block w-full px-5 py-4 border border-gray-300 bg-white rounded-xl shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-gray-900 text-lg transition duration-150">
                </div>

                <div>
                    <label for="password" class="block text-base font-medium text-gray-700">Password</label>
                    <input type="password" id="password" name="password" placeholder="Masukkan password" required class="mt-2 block w-full px-5 py-4 border border-gray-300 bg-white rounded-xl shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-gray-900 text-lg transition duration-150">
                </div>

                <div class="text-right">
                    <a href="./lupaPassword.php" class="text-base font-medium text-blue-600 hover:text-blue-800 transition-colors">Lupa Password?</a>
                </div>

                <div>
                    <button type="submit" class="w-full flex justify-center items-center py-4 px-4 border border-transparent rounded-xl shadow-lg text-lg font-bold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 focus:ring-offset-white transition-colors">
                        Masuk
                    </button>
                </div>
            </form>

            <div class="mt-8 text-center text-base text-gray-600">
                Belum punya akun? 
                <!-- Link ini diasumsikan menuju halaman pendaftaran -->
                <a href="./buatAkun.php" class="font-bold mt-8 text-center text-base text-blue-600 hover:text-blue-800 transition-colors">
                Buat akun baru
                </a>
            </div>

        </div>
    </div>
</body>
</html>
