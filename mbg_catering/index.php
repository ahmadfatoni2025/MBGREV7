<?php
session_start();
include "koneksi.php"; // Memanggil koneksi database

// Redirect jika user belum login
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Ambil data user dari session
$user_data = $_SESSION['user'];
$nama_user = htmlspecialchars($user_data['nama'] ?? $user_data['username']);
$username = htmlspecialchars($user_data['username']);

// =========================================================================
// === PHP LOGIC: MENGAMBIL DATA INVENTARIS DARI MYSQL UNTUK JAVASCRIPT ===
// (Kolom disesuaikan dengan tabel kamu: id_barang, nama, kategori, harga, jumlah)
// =========================================================================
$inventory_data = [];
// Pastikan query ini sudah benar sesuai nama tabel dan kolom kamu
$result = $koneksi->query("SELECT id_barang, nama, kategori, harga, jumlah FROM stok ORDER BY nama ASC"); 

if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Cast tipe data agar JavaScript tidak error
        $row['id_barang'] = (int)$row['id_barang']; 
        $row['harga'] = (float)$row['harga'];
        $row['jumlah'] = (int)$row['jumlah'];
        $inventory_data[] = $row;
    }
} else {
    // Tambahkan penanganan error jika query gagal
    error_log("Error fetching inventory data: " . $koneksi->error); 
}
// Encode data ke format JSON untuk diinjeksi ke JavaScript
$json_data = json_encode($inventory_data);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Raw Materials Inventory - MBG</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <!-- Memuat Pustaka untuk Membuat PDF/CSV -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js"></script>

    <style>
        :root {
            --background: #f0f2f5; --card-bg: #ffffff; --foreground: #1c1e21;
            --muted-foreground: #65676b; --primary: #1877F2; --primary-light: #e7f3ff;
            --success: #31A24C; --danger: #dc3545; --secondary: #6c757d;
            --border-color: #dddfe2; --shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }
        body { font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji"; }
        .sidebar { width: 250px; transition: all 0.3s; }
        .container-content { max-width: 1200px; margin: 0 auto; display: flex; flex-direction: column; gap: 1.5rem; }
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); justify-content: center; align-items: center; z-index: 1000; display: none; }
        .btn { display: inline-flex; align-items: center; justify-content: center; padding: 0.75rem 1rem; border-radius: 0.5rem; font-weight: bold; border: 1px solid transparent; cursor: pointer; transition: all 0.2s; white-space: nowrap; }
        .btn-primary { background-color: var(--primary); color: white; }
        .btn-success { background-color: var(--success); color: white; }
        .btn-secondary { background-color: var(--secondary); color: white; }

        /* Responsive Table Magic for Mobile */
        @media (max-width: 768px) {
            .sidebar { width: 100%; height: auto; }
            .content-area { padding-top: 1rem; }
            .header-buttons { flex-direction: column; gap: 0.5rem; }
            .btn { width: 100%; }
            table thead { display: none; }
            table tr { display: block; margin-bottom: 1rem; border: 1px solid var(--border-color); border-radius: 8px; box-shadow: var(--shadow); }
            table td { display: flex; justify-content: space-between; align-items: center; text-align: right; padding: 0.75rem 1rem; border-bottom: 1px solid #f0f0f0; white-space: normal; }
            table td::before { content: attr(data-label); font-weight: bold; text-align: left; margin-right: 1rem; color: var(--foreground); }
        }
    </style>
</head>
<body class="bg-gray-100 flex flex-col md:flex-row min-h-screen">

    <!-- Sidebar (Menu Navigasi) -->
    <aside class="sidebar bg-blue-600 text-white p-6 shadow-xl flex-shrink-0">
        <div class="text-3xl font-extrabold mb-8 border-b border-blue-500 pb-4">MBG Admin</div>
        <nav class="space-y-3">
            <a href="index.php" class="flex items-center p-3 rounded-xl bg-blue-700 font-semibold shadow-md">
                <i class="fas fa-boxes-stacked mr-3"></i> Inventaris Bahan
            </a>
            <a href="./laporanPenjualan.php" class="flex items-center p-3 rounded-xl hover:bg-blue-700 transition">
                <i class="fas fa-file-invoice mr-3"></i> Laporan Penjualan
            </a>
            <a href="./pengaturanAkun.php" class="flex items-center p-3 rounded-xl hover:bg-blue-700 transition">
                <i class="fas fa-user-gear mr-3"></i> Pengaturan Akun
            </a>
            <a href="logout.php" class="flex items-center p-3 rounded-xl hover:bg-red-700 transition mt-6 pt-4 border-t border-blue-500 text-red-100">
                <i class="fas fa-sign-out-alt mr-3"></i> Logout
            </a>
        </nav>
    </aside>

    <!-- Main Content (Inventory Management) -->
    <main class="flex-1 p-6 md:p-10 content-area">
        <div class="container-content relative">
            <header class="mb-8">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">Raw Materials Inventory</h1>
                        <p class="text-gray-500 mt-1">Halo, <?php echo $nama_user; ?>! Kelola stok bahan baku Anda.</p>
                    </div>
                    <button id="add-material-btn" class="btn btn-primary px-6 py-3 rounded-xl shadow-md">
                        <i class="fas fa-plus mr-2"></i> Tambah Bahan
                    </button>
                </div>
            </header>

            <!-- Stats Cards (Diambil dari JS) -->
            <section id="stats-grid" class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                <!-- Data akan diisi oleh JavaScript -->
            </section>

            <!-- Action Bar (Search & Export) -->
            <div class="flex flex-col md:flex-row justify-between items-center bg-white p-4 rounded-xl shadow-sm mb-6 gap-3">
                <div class="relative w-full md:w-1/3">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="text" id="search-input" placeholder="Cari barang atau kategori..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="flex gap-3 w-full md:w-auto justify-end">
                    <button id="export-pdf-btn" class="btn btn-secondary text-sm px-4 py-2 rounded-lg">
                        <i class="fas fa-file-pdf mr-2"></i> Export PDF
                    </button>
                    <button id="export-csv-btn" class="btn btn-success text-sm px-4 py-2 rounded-lg">
                        <i class="fas fa-file-csv mr-2"></i> Export CSV
                    </button>
                </div>
            </div>

            <!-- Product Table -->
            <section class="card table-container bg-white rounded-xl shadow-lg border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Barang</th> 
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th> 
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th> 
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/6">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="product-table-body" class="bg-white divide-y divide-gray-200">
                        <!-- Data akan diisi oleh JavaScript -->
                    </tbody>
                </table>
            </section>
        </div>
    </main>

    <!-- Product Modal (Form for Add/Edit) -->
    <div id="product-modal" class="modal-overlay">
        <div class="modal-content bg-white p-6 rounded-xl shadow-2xl max-w-lg w-full">
            <header class="modal-header border-b pb-3 mb-4 flex justify-between items-center">
                <h2 id="modal-title" class="text-xl font-bold">Tambah Barang Baru</h2>
                <button class="close-btn text-2xl font-light text-gray-500 hover:text-gray-800">&times;</button>
            </header>
            <form id="product-form" class="space-y-4">
                <input type="hidden" id="product-id">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label for="nama" class="block text-sm font-medium text-gray-700">Nama Barang</label>
                        <input type="text" id="nama" required class="mt-1 w-full border border-gray-300 p-2 rounded-lg">
                    </div>
                    <div>
                        <label for="kategori" class="block text-sm font-medium text-gray-700">Kategori</label>
                        <input type="text" id="kategori" required class="mt-1 w-full border border-gray-300 p-2 rounded-lg">
                    </div>
                    <div>
                        <label for="harga" class="block text-sm font-medium text-gray-700">Harga (IDR)</label>
                        <input type="number" id="harga" min="0" required class="mt-1 w-full border border-gray-300 p-2 rounded-lg">
                    </div>
                    <div class="md:col-span-2">
                        <label for="jumlah" class="block text-sm font-medium text-gray-700">Jumlah Stok</label>
                        <input type="number" id="jumlah" min="0" required class="mt-1 w-full border border-gray-300 p-2 rounded-lg">
                    </div>
                </div>
                <footer class="modal-footer pt-4 border-t mt-4 flex justify-end gap-3">
                    <button type="button" class="btn text-gray-700 bg-gray-200 px-4 py-2 rounded-lg close-btn">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 py-2 rounded-lg">Simpan</button>
                </footer>
            </form>
        </div>
    </div>
    
    <!-- ========================================================== -->
    <!-- === JAVASCRIPT LOGIC (Client-side Data Handling & CRUD) === -->
    <!-- ========================================================== -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const { jsPDF } = window.jspdf;
            
            // Injeksi data awal dari PHP
            let products = <?php echo $json_data; ?>;
            let editingProductId = null;

            // Elemen DOM
            const tableBody = document.getElementById('product-table-body');
            const statsGrid = document.getElementById('stats-grid');
            const modal = document.getElementById('product-modal');
            const productForm = document.getElementById('product-form');
            const modalTitle = document.getElementById('modal-title');
            const addBtn = document.getElementById('add-material-btn');
            const searchInput = document.getElementById('search-input');

            // === FUNGSI UTAMA ===

            // Mengirim permintaan AJAX ke inventory_action.php
            async function sendAction(action, data) {
                try {
                    const response = await fetch('inventory_action.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action, ...data }),
                    });
                    if (!response.ok) {
                        const errorText = await response.text();
                        console.error("Server Error:", errorText);
                        throw new Error(`Server error: ${response.status} ${response.statusText}. Response: ${errorText}`);
                    }
                     // Tambah: Cek jika response body kosong
                     const responseText = await response.text();
                     if (!responseText) {
                        // Jika body kosong (misalnya karena redirect), anggap sukses
                        return { success: true, message: 'Aksi berhasil (respon kosong).'}; 
                     }
                    // Jika ada body, parse sebagai JSON
                    return JSON.parse(responseText); 
                } catch (error) {
                    console.error("Network or JSON error:", error);
                    return { success: false, message: `Gagal berkomunikasi dengan server: ${error.message}` }; 
                }
            }

            // Memuat ulang data dari database
            async function fetchProducts() {
                try {
                    // Fetch langsung ke action file dengan action GET_ALL
                    const response = await fetch('inventory_action.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({ action: 'GET_ALL' })
                    });

                     if (!response.ok) {
                       throw new Error(`Server error: ${response.status} ${response.statusText}`);
                    }

                    const result = await response.json();
                    
                    if(result.success && Array.isArray(result.data)) {
                        products = result.data; // Update data products dari hasil fetch
                    } else {
                        console.warn("Could not retrieve valid product data:", result.message);
                        products = [];
                    }

                } catch (error) {
                    console.error("Error fetching data:", error);
                    alert(`Gagal memuat data dari server: ${error.message}`);
                    products = []; // Set ke array kosong jika fetch gagal
                }
                renderTable(); // Render ulang tabel dengan data baru
            }


            function updateStats(currentProducts = products) {
                const totalValue = currentProducts.reduce((sum, p) => sum + (p.harga * p.jumlah), 0);
                const totalStock = currentProducts.reduce((sum, p) => sum + p.jumlah, 0); // Perbaikan kalkulasi total stok
                const totalProducts = currentProducts.length;
                const categories = new Set(currentProducts.map(p => p.kategori)).size;

                statsGrid.innerHTML = `
                    <div class="bg-white p-5 rounded-xl shadow-md border-b-4 border-blue-400">
                        <p class="text-sm text-gray-500">Total Produk</p>
                        <p class="text-3xl font-bold text-gray-800 mt-1">${totalProducts}</p>
                    </div>
                    <div class="bg-white p-5 rounded-xl shadow-md border-b-4 border-green-400">
                        <p class="text-sm text-gray-500">Total Nilai</p>
                        <p class="text-3xl font-bold text-gray-800 mt-1">IDR ${totalValue.toLocaleString('id-ID')}</p>
                    </div>
                    <div class="bg-white p-5 rounded-xl shadow-md border-b-4 border-yellow-400">
                        <p class="text-sm text-gray-500">Kategori</p>
                        <p class="text-3xl font-bold text-gray-800 mt-1">${categories}</p>
                    </div>
                    <div class="bg-white p-5 rounded-xl shadow-md border-b-4 border-red-400">
                        <p class="text-sm text-gray-500">Total Stok</p>
                        <p class="text-3xl font-bold text-gray-800 mt-1">${totalStock}</p>
                    </div>
                `;
            }

            function renderTable(currentProducts = products) {
                tableBody.innerHTML = '';
                if (currentProducts.length === 0) {
                    tableBody.innerHTML = `<tr><td colspan="5" class="text-center py-8 text-gray-500">Tidak ada data produk ditemukan.</td></tr>`;
                    updateStats([]);
                    return;
                }

                currentProducts.forEach(product => {
                    const row = document.createElement('tr');
                    row.dataset.id = product.id_barang; 
                    row.className = 'hover:bg-gray-50 transition';

                    row.innerHTML = `
                        <td data-label="Nama Barang" class="px-6 py-4">${product.nama}</td>
                        <td data-label="Kategori" class="px-6 py-4">${product.kategori}</td>
                        <td data-label="Harga" class="px-6 py-4">IDR ${product.harga.toLocaleString('id-ID')}</td>
                        <td data-label="Jumlah" class="px-6 py-4">${product.jumlah}</td>
                        <td data-label="Aksi" class="px-6 py-4 action-cell flex justify-end">
                            <button class="edit bg-yellow-500 text-white p-2 rounded-lg hover:bg-yellow-600 transition" title="Ubah">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="delete bg-red-500 text-white p-2 rounded-lg hover:bg-red-600 transition ml-2" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    `;
                    tableBody.appendChild(row);
                });
                updateStats(currentProducts);
            }

            function openModal(product = null) {
                const id_input = document.getElementById('product-id');

                if (product) {
                    modalTitle.textContent = 'Ubah Barang';
                    editingProductId = product.id_barang; 
                    id_input.value = product.id_barang; 
                    document.getElementById('nama').value = product.nama; 
                    document.getElementById('kategori').value = product.kategori;
                    document.getElementById('harga').value = product.harga;
                    document.getElementById('jumlah').value = product.jumlah;
                } else {
                    modalTitle.textContent = 'Tambah Barang Baru';
                    editingProductId = null;
                    id_input.value = ''; 
                    productForm.reset();
                }
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }

            function closeModal() {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                editingProductId = null;
                document.getElementById('product-id').value = ''; 
                productForm.reset();
            }
            
            // ==========================================================
            // === LOGIKA UTAMA CRUD (Menggunakan AJAX ke PHP) ===
            // ==========================================================

            productForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const id_input = document.getElementById('product-id');

                const data = {
                    nama: document.getElementById('nama').value,
                    kategori: document.getElementById('kategori').value,
                    harga: parseFloat(document.getElementById('harga').value),
                    jumlah: parseInt(document.getElementById('jumlah').value, 10),
                };

                let response;

                if (editingProductId !== null) {
                    data.id_barang = editingProductId; 
                    response = await sendAction('EDIT', data);
                } else {
                    response = await sendAction('ADD', data);
                }

                if (response.success) {
                    alert(response.message);
                    closeModal();
                    await fetchProducts(); // Muat ulang data langsung dari server
                } else {
                    alert(`Gagal: ${response.message}`);
                }
            });
            
            // Event Delegation untuk Edit dan Delete
            tableBody.addEventListener('click', async (e) => {
                const editButton = e.target.closest('button.edit');
                const deleteButton = e.target.closest('button.delete');

                if (editButton) {
                    const id_barang = parseInt(editButton.closest('tr').dataset.id);
                    const product = products.find(p => p.id_barang === id_barang);
                    if (product) {
                        openModal(product);
                    } else {
                        console.error("Produk tidak ditemukan untuk diedit:", id_barang);
                    }
                }

                if (deleteButton) {
                    const row = deleteButton.closest('tr');
                    const id_barang = parseInt(row.dataset.id); 
                    const productNameElement = row.querySelector('[data-label="Nama Barang"]');
                    const productName = productNameElement ? productNameElement.textContent : 'Barang ini';

                    if (confirm(`Anda yakin ingin menghapus produk "${productName}"? Tindakan ini tidak bisa dibatalkan.`)) {
                        const response = await sendAction('DELETE', { id_barang }); 
                        
                        if (response.success) {
                            alert(response.message);
                            await fetchProducts(); // Muat ulang data langsung dari server
                        } else {
                            alert(`Gagal menghapus: ${response.message}`);
                        }
                    }
                }
            });

            // --- EKSPOR FUNGSI ---
            function exportToCsv() {
                if (products.length === 0) { alert("Tidak ada data untuk diekspor!"); return; }
                const headers = ['Nama Barang', 'Kategori', 'Harga', 'Jumlah'];
                let csvContent = headers.join(',') + '\n';
                products.forEach(p => {
                    const row = [`"${p.nama}"`, `"${p.kategori}"`, p.harga, p.jumlah]; 
                    csvContent += row.join(',') + '\n';
                });
                const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                link.setAttribute('href', URL.createObjectURL(blob));
                link.setAttribute('download', `rekap_inventaris_${new Date().toISOString().slice(0, 10)}.csv`);
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }

            function exportToPdf() {
                if (products.length === 0) { alert("Tidak ada data untuk diekspor!"); return; }
                const doc = new jsPDF();
                const tableHeaders = [['Nama Barang', 'Kategori', 'Harga Satuan', 'Jumlah', 'Total Harga']];
                const tableBody = products.map(p => [
                    p.nama, p.kategori, `IDR ${p.harga.toLocaleString('id-ID')}`, p.jumlah, `IDR ${(p.harga * p.jumlah).toLocaleString('id-ID')}`
                ]);
                const grandTotal = products.reduce((sum, p) => sum + (p.harga * p.jumlah), 0);
                const tableFooter = [['', '', '', 'Grand Total', `IDR ${grandTotal.toLocaleString('id-ID')}`]];
                
                doc.setFontSize(18); doc.text('Rekap Inventaris Bahan Baku', 14, 22);
                doc.setFontSize(11); doc.setTextColor(100);
                doc.text(`Tanggal: ${new Date().toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' })}`, 14, 29); 

                doc.autoTable({
                    head: tableHeaders, body: tableBody, foot: tableFooter, startY: 35, theme: 'grid',
                    headStyles: { fillColor: [24, 119, 242] },
                    footStyles: { fillColor: [240, 242, 245], textColor: [0, 0, 0], fontStyle: 'bold' }
                });
                doc.save(`rekap_inventaris_${new Date().toISOString().slice(0, 10)}.pdf`);
            }


            // --- EVENT LISTENERS ---
            addBtn.addEventListener('click', () => openModal(null));
            document.querySelectorAll('.close-btn').forEach(btn => btn.addEventListener('click', closeModal));
            document.getElementById('export-csv-btn').addEventListener('click', exportToCsv);
            document.getElementById('export-pdf-btn').addEventListener('click', exportToPdf);

            // Filter/Search
            searchInput.addEventListener('input', (e) => {
                const query = e.target.value.toLowerCase();
                const filteredProducts = products.filter(p => 
                    p.nama.toLowerCase().includes(query) || 
                    p.kategori.toLowerCase().includes(query)
                );
                renderTable(filteredProducts);
            });

            // --- INISIALISASI ---
            renderTable(); // Tampilkan data awal yang di-inject PHP
        });
    </script>
</body>
</html>


