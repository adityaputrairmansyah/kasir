<?php
session_start();
require_once '../config/database.php';

// Cek login
if(!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user = $_SESSION['user'];

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $qty = $_POST['qty'];
    $bayar = $_POST['bayar'];
    
    try {
        $db->beginTransaction();
        
        // Update jumlah dan subtotal di detail_transaksi
        foreach($qty as $detail_id => $jumlah) {
            $stmt = $db->prepare("UPDATE detail_transaksi SET jumlah = ?, subtotal = jumlah * harga WHERE id = ?");
            $stmt->execute([$jumlah, $detail_id]);
        }
        
        // Update total_harga, bayar, dan kembalian di transaksi
        $stmt = $db->prepare("UPDATE transaksi t 
                             SET total_harga = (
                                 SELECT SUM(subtotal) 
                                 FROM detail_transaksi 
                                 WHERE id_transaksi = t.id
                             ),
                             bayar = ?,
                             kembalian = ? - (
                                 SELECT SUM(subtotal) 
                                 FROM detail_transaksi 
                                 WHERE id_transaksi = t.id
                             )
                             WHERE id = ?");
        $stmt->execute([$bayar, $bayar, $id]);
        
        $db->commit();
        header("Location: detail.php?id=" . $id);
        exit();
        
    } catch(PDOException $e) {
        $db->rollBack();
        header("Location: edit.php?id=" . $id . "&error=" . urlencode($e->getMessage()));
        exit();
    }
}

header("Location: index.php");
exit(); 