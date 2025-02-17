<?php
require_once '../config/database.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    try {
        $stmt = $db->prepare("DELETE FROM produk WHERE id = ?");
        $stmt->execute([$id]);
        
        header("Location: index.php?success=hapus");
        exit();
    } catch(PDOException $e) {
        header("Location: index.php?error=hapus");
        exit();
    }
}

header("Location: index.php");
exit(); 