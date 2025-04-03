<?php
require_once 'config.php';

// JSON yanıtı için header
header('Content-Type: application/json');

// Oturum kontrolü
if (!isset($_SESSION['kullanici_id'])) {
    echo json_encode(['success' => false, 'message' => 'Oturum açmanız gerekiyor']);
    exit();
}

// POST verilerini al
$id = $_POST['id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Sözleşme ID\'si gerekli']);
    exit();
}

try {
    // Sözleşmeyi sil
    $stmt = $db->prepare("DELETE FROM sozlesmeler WHERE id = ?");
    $stmt->execute([$id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Sözleşme başarıyla silindi']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Sözleşme bulunamadı']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
}
?> 