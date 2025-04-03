<?php
require_once 'config.php';

// Oturum kontrolü
if (!isset($_SESSION['kullanici_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Oturum açmanız gerekiyor']);
    exit();
}

// JSON verisini al
$data = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($data['id'])) {
    try {
        // Önce müşterinin organizasyonları var mı kontrol et
        $stmt = $db->prepare("SELECT COUNT(*) FROM organizasyonlar WHERE musteri_id = ?");
        $stmt->execute([$data['id']]);
        $organizasyon_sayisi = $stmt->fetchColumn();

        if ($organizasyon_sayisi > 0) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Bu müşteriye ait organizasyonlar bulunmaktadır. Önce organizasyonları silmelisiniz.']);
            exit();
        }

        // Müşteriyi sil
        $stmt = $db->prepare("DELETE FROM musteriler WHERE id = ?");
        $stmt->execute([$data['id']]);

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Müşteri silinirken bir hata oluştu: ' . $e->getMessage()]);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Geçersiz istek']);
}
?> 