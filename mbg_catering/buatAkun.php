<?php
// Selalu mulai session di awal halaman
session_start();

// (OPSIONAL) Jika pengguna sudah login, arahkan ke dashboard
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'driver') {
        header('Location: driver.php');
        exit();
    } elseif ($_SESSION['role'] === 'admin') {
        header('Location: index.php'); // Asumsi admin ke index.php
        exit();
    }
}

// Sertakan file koneksi database
// Pastikan path ini benar sesuai struktur folder Anda
include 'koneksi.php';

$error_message = '';
$success_message = '';

// Cek apakah form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $konfirmasi_password = $_POST['konfirmasi_password'];
    $role = 'driver'; // Asumsi pendaftaran ini HANYA untuk driver

    // Validasi sederhana
    if (empty($username) || empty($password) || empty($konfirmasi_password)) {
        $error_message = 'Semua field wajib diisi!';
    } elseif ($password !== $konfirmasi_password) {
        $error_message = 'Password dan konfirmasi password tidak cocok!';
    } else {
        
        // Cek apakah username sudah ada
        $stmt_check = $koneksi->prepare("SELECT id FROM users WHERE username = ?");
        $stmt_check->bind_param("s", $username);
        $stmt_check->execute();
        $stmt_check->store_result();
        
        if ($stmt_check->num_rows > 0) {
            $error_message = 'Username sudah digunakan, silakan pilih yang lain.';
        } else {
            // Username tersedia, lanjutkan pendaftaran
            
            // --- KEAMANAN PENTING: HASH PASSWORD ---
            // Jangan pernah simpan password sebagai plain text.
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Siapkan query untuk INSERT
            $stmt_insert = $koneksi->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            $stmt_insert->bind_param("sss", $username, $hashed_password, $role);

            if ($stmt_insert->execute()) {
                $success_message = 'Akun berhasil dibuat! Silakan login.';
                // Kosongkan variabel agar form bersih setelah sukses
                $_POST = array(); 
            } else {
                $error_message = 'Terjadi kesalahan pada server. Coba lagi nanti.';
                // error_log($stmt_insert->error); // Untuk debugging
            }
            
            $stmt_insert->close();
        }
        
        $stmt_check->close();
    }
    
    $koneksi->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Akun Baru - MBG Catering</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .modern-shadow {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.03);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-white rounded-xl modern-shadow p-8 sm:p-10 border border-gray-100">
        
        <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 mb-6 text-center">
            Buat Akun Driver Baru
        </h1>

        <!-- Pesan Error -->
        <?php if ($error_message): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $error_message; ?></span>
            </div>
        <?php endif; ?>

        <!-- Pesan Sukses -->
        <?php if ($success_message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $success_message; ?></span>
            </div>
        <?php endif; ?>

        <!-- Form Pendaftaran -->
        <form method="POST" action="buat_akun.php" class="space-y-4">
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                <input type="text" id="username" name="username" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" 
                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
            </div>
            
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" id="password" name="password" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" 
                       required>
            </div>

            <div>
                <label for="konfirmasi_password" class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
                <input type="password" id="konfirmasi_password" name="konfirmasi_password" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" 
                       required>
            </div>

            <p class="text-xs text-gray-500">
                Akun yang dibuat akan otomatis mendapatkan peran sebagai "Driver".
            </p>

            <div>
                <button type="submit" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-300 ease-in-out transform hover:shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500 focus:ring-opacity-50">
                    Daftar
                </button>
            </div>
        </form>

        <div class="text-center mt-6">
            <p class="text-sm text-gray-600">
                Sudah punya akun? 
                <a href="login.php" class="font-bold text-blue-600 hover:text-blue-800 transition-colors">
                    Login di sini
                </a>
            </p>
        </div>
    </div>

</body>
</html>
