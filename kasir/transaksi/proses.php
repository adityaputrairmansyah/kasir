<?php
session_start();
require_once '../config/database.php';

// Cek login
if(!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user = $_SESSION['user'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $db->beginTransaction();

        $no_transaksi = $_POST['no_transaksi'];
        $total = $_POST['total'];
        $bayar = $_POST['bayar'];
        $kembalian = $bayar - $total;
        $tanggal = date('Y-m-d H:i:s');
        $id_user = $_SESSION['user']['id'];

        // Insert transaksi
        $stmt = $db->prepare("INSERT INTO transaksi (no_transaksi, tanggal, id_user, total_harga, bayar, kembalian) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$no_transaksi, $tanggal, $id_user, $total, $bayar, $kembalian]);
        $id_transaksi = $db->lastInsertId();

        // Insert detail transaksi dan update stok
        foreach ($_POST['items'] as $item) {
            $id_produk = $item['id'];
            $qty = $item['qty'];
            $harga = $item['harga'];
            $subtotal = $qty * $harga;

            // Insert detail
            $stmt = $db->prepare("INSERT INTO detail_transaksi (id_transaksi, id_produk, jumlah, harga, subtotal) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$id_transaksi, $id_produk, $qty, $harga, $subtotal]);

            // Update stok
            $stmt = $db->prepare("UPDATE produk SET stok = stok - ? WHERE id = ?");
            $stmt->execute([$qty, $id_produk]);
        }

        $db->commit();
        header("Location: detail.php?id=" . $id_transaksi);
        exit();

    } catch (PDOException $e) {
        $db->rollBack();
        header("Location: baru.php?error=" . urlencode($e->getMessage()));
        exit();
    }
}

header("Location: index.php");
exit(); 