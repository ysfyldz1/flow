<?php
require_once 'config.php';

// JSON yanıt başlığı
header('Content-Type: application/json');

// Oturum kontrolü
if (!isset($_SESSION['kullanici_id'])) {
    echo json_encode(['success' => false, 'message' => 'Oturum açmanız gerekiyor']);
    exit();
}

// POST verilerini al
$id = $_POST['id'] ?? 0;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Personel ID gerekli']);
    exit();
}

try {
    // Önce personelin görevlerini kontrol et
    $stmt = $db->prepare("SELECT COUNT(*) FROM personel_gorevleri WHERE personel_id = ?");
    $stmt->execute([$id]);
    $gorev_sayisi = $stmt->fetchColumn();

    if ($gorev_sayisi > 0) {
        echo json_encode(['success' => false, 'message' => 'Bu personelin aktif görevleri var. Önce görevleri silmelisiniz.']);
        exit();
    }

    // Personeli sil
    $stmt = $db->prepare("DELETE FROM personel WHERE id = ?");
    $stmt->execute([$id]);
    
    echo json_encode(['success' => true, 'message' => 'Personel başarıyla silindi']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Hata: ' . $e->getMessage()]);
} 