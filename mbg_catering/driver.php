<?php
// Memulai session untuk menyimpan status login pengguna
session_start();

// --- KEAMANAN ---
// Pengecekan role di sisi server. Jauh lebih aman.
// Jika tidak ada session 'role' atau role-nya bukan 'driver', tendang ke halaman login.
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'driver') {
    header('Location: login.php'); // Asumsikan halaman login Anda bernama login.php
    exit(); // Hentikan eksekusi skrip
}

// --- SUMBER DATA (Contoh) ---
// Di aplikasi nyata, data ini akan diambil dari database (SELECT * FROM pesanan WHERE ...)
$pengantaran_data = [
    [
        'id' => 1,
        'nama_barang' => 'Ayam Potong',
        'jumlah' => '10 Kg',
        'total_harga' => 'Rp 340.000',
        'status' => 'Pending'
    ],
    [
        'id' => 2,
        'nama_barang' => 'Susu UHT',
        'jumlah' => '5 Liter',
        'total_harga' => 'Rp 100.000',
        'status' => 'Ongoing'
    ],
    [
        'id' => 3,
        'nama_barang' => 'Apel Fuji',
        'jumlah' => '3 Kg',
        'total_harga' => 'Rp 120.000',
        'status' => 'Done'
    ],
    [
        'id' => 4,
        'nama_barang' => 'Beras Pandan Wangi',
        'jumlah' => '25 Kg',
        'total_harga' => 'Rp 320.000',
        'status' => 'Pending'
    ]
];

// Fungsi untuk logout
function logout() {
    session_destroy();
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keterangan Driver - Aplikasi MBG</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-900">
    
    <main class="w-full p-8">
        
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-4xl font-bold text-white bg-blue-600 p-6 rounded-xl shadow-lg">
                Daftar Pengantaran Barang
            </h2>
            <a href="logout.php" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg">
                Logout
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
            
            <div class="p-4 flex justify-between items-center border-b border-gray-200">
                <div>
                    <input type="text" id="searchInput" placeholder="Cari barang..." class="bg-white border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <button onclick="location.reload();" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m-15.357-2A8.001 8.001 0 0019.418 15m0 0H15"></path></svg>
                        Refresh
                    </button>
                </div>
            </div>

            <table class="w-full text-sm text-left text-gray-700">
                <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th scope="col" class="px-6 py-4">Nama Barang</th>
                        <th scope="col" class="px-6 py-4">Jumlah Barang</th>
                        <th scope="col" class="px-6 py-4">Total Harga</th>
                        <th scope="col" class="px-6 py-4">Status Saat Ini</th>
                        <th scope="col" class="px-6 py-4">Ubah Status</th>
                    </tr>
                </thead>
                <tbody id="orderTableBody" class="divide-y divide-gray-200">
                    
                    <?php foreach ($pengantaran_data as $item): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium text-gray-900"><?php echo htmlspecialchars($item['nama_barang']); ?></td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($item['jumlah']); ?></td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($item['total_harga']); ?></td>
                            <td class="px-6 py-4">
                                <?php
                                    // Menentukan warna badge berdasarkan status dari data
                                    $badge_color = 'bg-gray-100 text-gray-800'; // Default
                                    if ($item['status'] == 'Pending') $badge_color = 'bg-blue-100 text-blue-800';
                                    if ($item['status'] == 'Ongoing') $badge_color = 'bg-yellow-100 text-yellow-800';
                                    if ($item['status'] == 'Done') $badge_color = 'bg-green-100 text-green-800';
                                ?>
                                <span id="status-badge-<?php echo $item['id']; ?>" class="px-3 py-1 text-xs font-medium rounded-full <?php echo $badge_color; ?>">
                                    <?php echo htmlspecialchars($item['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <select data-id="<?php echo $item['id']; ?>" data-target="status-badge-<?php echo $item['id']; ?>" class="status-select bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                    <option value="Pending" <?php echo ($item['status'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                    <option value="Ongoing" <?php echo ($item['status'] == 'Ongoing') ? 'selected' : ''; ?>>Ongoing</option>
                                    <option value="Done" <?php echo ($item['status'] == 'Done') ? 'selected' : ''; ?>>Done</option>
                                </select>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
            </table>
        </div>
    </main>

    <script>
        // ----- FUNGSI SEARCH BAR (TETAP SAMA, TIDAK PERLU DIUBAH) -----
        // Fungsi ini bekerja dengan baik pada tabel yang sudah digenerate oleh PHP
        const searchInput = document.getElementById('searchInput');
        const tableBody = document.getElementById('orderTableBody');
        const rows = tableBody.getElementsByTagName('tr');

        searchInput.addEventListener('keyup', function() {
            const filter = searchInput.value.toLowerCase();
            
            for (let i = 0; i < rows.length; i++) {
                let td = rows[i].getElementsByTagName('td')[0]; 
                if (td) {
                    let textValue = td.textContent || td.innerText;
                    if (textValue.toLowerCase().indexOf(filter) > -1) {
                        rows[i].style.display = "";
                    } else {
                        rows[i].style.display = "none";
                    }
                }
            }
        });

        // ----- FUNGSI DROPDOWN STATUS (TETAP SAMA, TAPI ADA PENAMBAHAN) -----
        // Fungsi ini mengubah tampilan di browser.
        // Di aplikasi nyata, Anda perlu menambahkan `fetch` di sini untuk mengirim perubahan ke server.
        const statusColors = {
            'Pending': 'bg-blue-100 text-blue-800',
            'Ongoing': 'bg-yellow-100 text-yellow-800',
            'Done': 'bg-green-100 text-green-800',
        };

        const dropdowns = document.querySelectorAll('.status-select');

        dropdowns.forEach(dropdown => {
            dropdown.addEventListener('change', function() {
                const newStatus = this.value;
                const targetBadgeId = this.getAttribute('data-target');
                const orderId = this.getAttribute('data-id');
                const badge = document.getElementById(targetBadgeId);

                if (badge) {
                    badge.textContent = newStatus;
                    badge.className = 'px-3 py-1 text-xs font-medium rounded-full '; // Reset class
                    badge.classList.add(...statusColors[newStatus].split(' '));
                    
                    // -- LANGKAH SELANJUTNYA (PENTING) --
                    // Kirim perubahan ini ke server agar tersimpan di database
                    // Contoh menggunakan fetch:
                    /*
                    fetch('update_status.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({ id: orderId, status: newStatus })
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Update success:', data);
                        // Tambahkan notifikasi jika perlu
                    })
                    .catch(error => console.error('Error updating status:', error));
                    */
                   alert(`Status untuk ID Pesanan ${orderId} diubah menjadi ${newStatus}. (Perubahan belum disimpan ke server)`);
                }
            });
        });

    </script>
</body>
</html>