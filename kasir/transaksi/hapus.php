<?php
session_start();
require_once '../config/database.php';

// Cek login
if(!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user = $_SESSION['user'];

// Cek role dan id transaksi
if($user['role'] != 'kasir' || !isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'];

// Cek kepemilikan transaksi
$stmt = $db->prepare("SELECT id_user FROM transaksi WHERE id = ?");
$stmt->execute([$id]);
$transaksi = $stmt->fetch();

if(!$transaksi || $transaksi['id_user'] != $user['id']) {
    header("Location: index.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    try {
        $db->beginTransaction();

        // Ambil detail transaksi untuk mengembalikan stok
        $stmt = $db->prepare("SELECT id_produk, jumlah FROM detail_transaksi WHERE id_transaksi = ?");
        $stmt->execute([$id]);
        $details = $stmt->fetchAll();

        // Kembalikan stok produk
        foreach($details as $detail) {
            $stmt = $db->prepare("UPDATE produk SET stok = stok + ? WHERE id = ?");
            $stmt->execute([$detail['jumlah'], $detail['id_produk']]);
        }

        // Hapus detail transaksi
        $stmt = $db->prepare("DELETE FROM detail_transaksi WHERE id_transaksi = ?");
        $stmt->execute([$id]);

        // Hapus transaksi
        $stmt = $db->prepare("DELETE FROM transaksi WHERE id = ?");
        $stmt->execute([$id]);
        
        $db->commit();
        header("Location: index.php?success=hapus");
        exit();
    } catch(PDOException $e) {
        $db->rollBack();
        header("Location: index.php?error=" . urlencode("Gagal menghapus transaksi: " . $e->getMessage()));
        exit();
    }
}

header("Location: index.php");
exit(); 