-- Tambah kategori
INSERT INTO kategori (nama_kategori) VALUES 
('Minuman'),
('Makanan Ringan'),
('Sembako'),
('Alat Tulis'),
('Perlengkapan Mandi'),
('Bumbu Dapur'),
('Obat-obatan'),
('Peralatan Rumah');

-- Tambah produk
INSERT INTO produk (kode_produk, nama_produk, id_kategori, harga_beli, harga_jual, stok) VALUES 
-- Minuman
('MNM001', 'Aqua 600ml', 1, 2500, 3000, 50),
('MNM002', 'Coca Cola 390ml', 1, 4000, 5000, 40),
('MNM003', 'Teh Pucuk 350ml', 1, 3500, 4500, 45),
('MNM004', 'Pocari Sweat 500ml', 1, 5500, 7000, 30),

-- Makanan Ringan
('MKN001', 'Chitato 68gr', 2, 7500, 9000, 25),
('MKN002', 'Oreo 133gr', 2, 8000, 10000, 30),
('MKN003', 'Taro 65gr', 2, 4500, 6000, 40),
('MKN004', 'Qtela 185gr', 2, 8500, 10500, 20),

-- Sembako
('SMB001', 'Beras Pulen 1kg', 3, 11000, 13000, 50),
('SMB002', 'Minyak Goreng 1L', 3, 15000, 17000, 30),
('SMB003', 'Gula Pasir 1kg', 3, 12500, 14000, 40),
('SMB004', 'Telur 1kg', 3, 23000, 25000, 25),

-- Alat Tulis
('ATK001', 'Pulpen Pilot', 4, 2000, 3000, 100),
('ATK002', 'Buku Tulis', 4, 3500, 5000, 80),
('ATK003', 'Pensil 2B', 4, 2500, 4000, 75),
('ATK004', 'Penghapus', 4, 1500, 2500, 90),

-- Perlengkapan Mandi
('MND001', 'Sabun Mandi', 5, 3500, 5000, 40),
('MND002', 'Shampoo Sachet', 5, 500, 1000, 100),
('MND003', 'Pasta Gigi', 5, 8500, 10000, 45),
('MND004', 'Sikat Gigi', 5, 4000, 6000, 50),

-- Bumbu Dapur
('BMB001', 'Kecap Manis', 6, 9000, 11000, 35),
('BMB002', 'Saos Sambal', 6, 8000, 10000, 40),
('BMB003', 'Royco Ayam', 6, 500, 1000, 100),
('BMB004', 'Merica Bubuk', 6, 1000, 2000, 75),

-- Obat-obatan
('OBT001', 'Paracetamol', 7, 8000, 10000, 50),
('OBT002', 'Antasida', 7, 6000, 8000, 40),
('OBT003', 'Minyak Kayu Putih', 7, 15000, 18000, 30),
('OBT004', 'Plester', 7, 5000, 7000, 60),

-- Peralatan Rumah
('PRT001', 'Sapu', 8, 15000, 18000, 20),
('PRT002', 'Ember Plastik', 8, 12000, 15000, 25),
('PRT003', 'Lap Pel', 8, 8000, 10000, 30),
('PRT004', 'Sikat WC', 8, 10000, 13000, 20); 