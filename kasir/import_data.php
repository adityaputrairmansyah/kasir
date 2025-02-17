<?php
require_once 'config/database.php';

try {
    $db->beginTransaction();

    // Query kategori
    $db->exec("INSERT INTO kategori (nama_kategori) VALUES 
        ('Minuman'),
        ('Makanan Ringan'),
        ('Sembako'),
        ('Alat Tulis'),
        ('Perlengkapan Mandi'),
        ('Bumbu Dapur'),
        ('Obat-obatan'),
        ('Peralatan Rumah')
    ");

    // Query produk (sebagian dari data)
    $db->exec("INSERT INTO produk (kode_produk, nama_produk, id_kategori, harga_beli, harga_jual, stok) VALUES 
        ('MNM001', 'Aqua 600ml', 1, 2500, 3000, 50),
        ('MNM002', 'Coca Cola 390ml', 1, 4000, 5000, 40),
        ('MKN001', 'Chitato 68gr', 2, 7500, 9000, 25),
        ('MKN002', 'Oreo 133gr', 2, 8000, 10000, 30),
        ('SMB001', 'Beras Pulen 1kg', 3, 11000, 13000, 50),
        ('SMB002', 'Minyak Goreng 1L', 3, 15000, 17000, 30)
    ");

    $db->commit();
    echo "Data berhasil diimport!";
} catch (PDOException $e) {
    $db->rollBack();
    echo "Error: " . $e->getMessage();
}
?> 