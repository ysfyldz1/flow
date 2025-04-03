<?php
require_once 'config.php';

// Oturum kontrolü
if (!isset($_SESSION['kullanici_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Oturum açmanız gerekiyor']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $stmt = $db->prepare("INSERT INTO musteriler (ad_soyad, telefon, email, adres, notlar) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['ad_soyad'],
            $_POST['telefon'],
            $_POST['email'],
            $_POST['adres'],
            $_POST['notlar']
        ]);

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Müşteri eklenirken bir hata oluştu: ' . $e->getMessage()]);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Geçersiz istek metodu']);
}
?> 