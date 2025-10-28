<?php
// ===========================================
// Koneksi ke Database (XAMPP default)
// ===========================================
$host = "localhost";
$user = "root";
$pass = "";
$db   = "mbg_inventory"; // pastikan nama database sesuai di phpMyAdmin

// Buat koneksi (pakai OOP biar konsisten dengan file login)
$koneksi = new mysqli($host, $user, $pass, $db);

// Cek koneksi
if ($koneksi->connect_error) {
    die("Koneksi database gagal: " . $koneksi->connect_error);
}

// (Opsional) kalau mau tes cepat
// echo "Koneksi berhasil";
?>


<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Data Admin - MBG</title>

  <!-- Tailwind & Font Awesome -->
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body class="bg-gray-100 flex flex-col md:flex-row min-h-screen">

  <!-- Sidebar -->
  <aside class="sidebar bg-blue-600 text-white p-6 shadow-xl flex-shrink-0">
    <div class="text-3xl font-extrabold mb-8 border-b border-blue-500 pb-4">MBG Admin</div>
    <nav class="space-y-3">
      <a href="index.php" class="flex items-center p-3 rounded-xl bg-blue-700 font-semibold shadow-md">
        <i class="fas fa-user mr-3"></i> Data Admin
      </a>
      <a href="logout.php" class="flex items-center p-3 rounded-xl hover:bg-red-700 transition mt-6 pt-4 border-t border-blue-500 text-red-100">
        <i class="fas fa-sign-out-alt mr-3"></i> Logout
      </a>
    </nav>
  </aside>

  <!-- Main Content -->
  <main class="flex-1 p-8 overflow-y-auto">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-semibold text-gray-800">Daftar Admin</h1>
    </div>

    <!-- Data Admin -->
    <div class="overflow-x-auto bg-white shadow-md rounded-xl p-6">
      <table class="min-w-full border border-gray-200 text-sm text-gray-700">
        <thead class="bg-blue-600 text-white">
          <tr>
            <th class="px-4 py-3 text-left">No</th>
            <th class="px-4 py-3 text-left">Username</th>
            <th class="px-4 py-3 text-left">Password</th>
          </tr>
        </thead>
        <tbody>
          <?php if (mysqli_num_rows($result) > 0): ?>
            <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): ?>
              <tr class="border-t hover:bg-gray-50">
                <td class="px-4 py-3"><?= $no++ ?></td>
                <td class="px-4 py-3"><?= htmlspecialchars($row['username']) ?></td>
                <td class="px-4 py-3"><?= htmlspecialchars($row['password']) ?></td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="3" class="text-center py-4 text-gray-500">Tidak ada data admin ditemukan.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>
</body>
</html>
