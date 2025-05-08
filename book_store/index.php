<?php
session_start();
include "config.php";

// Ambil data produk
$query = "SELECT * FROM produk";
$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
if (!empty($keyword)) {
    $query .= " WHERE nama_produk LIKE '%$keyword%' OR kategori_produk LIKE '%$keyword%'";
}
$result = mysqli_query($conn, $query);

// Ambil riwayat pesanan
$riwayat_pesanan = [];
if (isset($_SESSION['user_id'])) {
    $query_pesanan = mysqli_query($conn, "
        SELECT * FROM pesanan 
        WHERE id_user = {$_SESSION['user_id']}
        ORDER BY created_at DESC
    ");
    while ($row = mysqli_fetch_assoc($query_pesanan)) {
        $riwayat_pesanan[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produk - Toko Buku Agus</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: #e3f2fd;
            color: #333;
        }

        nav {
            background-color: #1976d2;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
        }

        .nav-left,
        .nav-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        nav a,
        nav span {
            color: white;
            text-decoration: none;
            font-weight: 500;
        }

        nav a:hover {
            text-decoration: underline;
        }

        nav ul {
            list-style: none;
            display: flex;
            gap: 20px;
            margin: 0;
            padding: 0;
        }

        .logo-kiri {
            height: 40px;
            width: auto;
            margin-right: 10px;
        }

        .container {
            max-width: 1000px;
            margin: 30px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #1976d2;
            text-align: center;
        }

        form {
            text-align: center;
            margin: 20px 0;
        }

        input[type="text"] {
            padding: 8px;
            width: 250px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }

        button {
            padding: 8px 16px;
            background-color: #1976d2;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #1565c0;
        }

        .produk-wrapper {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .produk-card {
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
            background-color: white;
            text-align: center;
        }

        .produk-card img {
            max-width: 100%;
            height: auto;
        }

        .riwayat-pesanan {
            margin-top: 50px;
        }

        .riwayat-pesanan h2 {
            margin-bottom: 15px;
        }

        .status-pending { color: #f39c12; }
        .status-diproses { color: #3498db; }
        .status-dikirim { color: #2ecc71; }
        .status-selesai { color: #27ae60; }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav>
        <div class="nav-left">
            <a href="index.php">
                <img src="logobuku.png" alt="Logo" class="logo-kiri">
            </a>
            <a href="index.php" class="logo">TOKO BUKU AGUS</a>
            <ul>
                <li><a href="index.php">Produk</a></li>
                <li><a href="about.php">Tentang Kami</a></li>
                <li><a href="contact.php">Kontak</a></li>
            </ul>
        </div>
        <div class="nav-right">
            <?php if (isset($_SESSION['user'])): ?>
                <span>Welcome <?php echo htmlspecialchars($_SESSION['user']); ?></span> |
                <a href="logout.php">Logout</a> |
                <a href="keranjang.php">Keranjang</a>
            <?php else: ?>
                <a href="login_user.php">Login User</a> |
                <a href="login_user.php">Keranjang</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container">
        <h2>Selamat datang di halaman utama <strong>Toko Buku Agus</strong></h2>
        <h2>Daftar Produk</h2>
        <form method="GET">
            <input type="text" name="keyword" placeholder="Cari produk atau kategori..." value="<?php echo htmlspecialchars($keyword); ?>">
            <button type="submit">Cari</button>
        </form>

        <div class="produk-wrapper">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <div class="produk-card">
                        <img src="admin/uploads/<?php echo htmlspecialchars($row['gambar']); ?>" alt="<?php echo htmlspecialchars($row['nama_produk']); ?>">
                        <h3><?php echo htmlspecialchars($row['nama_produk']); ?></h3>
                        <p>Kategori: <?php echo htmlspecialchars($row['kategori_produk']); ?></p>
                        <p>Harga: Rp<?php echo number_format($row['harga'], 0, ',', '.'); ?></p>
                        <p>Stok: <?php echo $row['stok']; ?></p>
                        <button onclick="addToCart(<?php echo $row['id_produk']; ?>)">Add to Cart</button>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Tidak ada produk ditemukan.</p>
            <?php endif; ?>
        </div>

        <!-- Riwayat Pemesanan -->
        <?php if (!empty($riwayat_pesanan)): ?>
            <div class="riwayat-pesanan">
                <h2>Riwayat Pemesanan Anda</h2>
                <table border="1" cellpadding="10" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tanggal</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($riwayat_pesanan as $pesanan): ?>
                            <tr>
                                <td><?php echo $pesanan['id_pesanan']; ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($pesanan['created_at'])); ?></td>
                                <td>Rp <?php echo number_format($pesanan['total'], 0, ',', '.'); ?></td>
                                <td class="status-<?php echo $pesanan['status']; ?>">
                                    <?php
                                    $status_map = [
                                        'pending' => 'Pending',
                                        'diproses' => 'Diproses',
                                        'dikirim' => 'Dikirim',
                                        'selesai' => 'Selesai'
                                    ];
                                    echo $status_map[$pesanan['status']] ?? $pesanan['status'];
                                    ?>
                                </td>
                                <td><a href="detail_pesanan_user.php?id=<?php echo $pesanan['id_pesanan']; ?>">Detail</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function addToCart(productId) {
            <?php if (!isset($_SESSION['user_id'])): ?>
                alert("Silakan login terlebih dahulu!");
                window.location.href = "login_user.php";
            <?php else: ?>
                fetch("add_to_cart.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: "id_produk=" + productId
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    if (data.status === "success") {
                        window.location.reload();
                    }
                });
            <?php endif; ?>
        }
    </script>
     <!-- Footer -->
     <footer style="margin-top: 60px; background-color: #1976d2; color: white; text-align: center; padding: 20px;">
        <p>&copy; <?php echo date("Y"); ?> Toko Buku Agus. All rights reserved.</p>
        <p><a href="../book_store/admin" style="color: #ffeb3b; text-decoration: underline;">Login sebagai Admin</a></p>
    </footer>

</body>
</html>
