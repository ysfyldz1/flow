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
$organizasyon_id = $_POST['organizasyon_id'] ?? null;
$sozlesme_no = $_POST['sozlesme_no'] ?? null;
$imza_tarihi = $_POST['imza_tarihi'] ?? null;
$baslangic_tarihi = $_POST['baslangic_tarihi'] ?? null;
$bitis_tarihi = $_POST['bitis_tarihi'] ?? null;
$toplam_tutar = $_POST['toplam_tutar'] ?? null;
$odeme_plani = $_POST['odeme_plani'] ?? null;
$ozel_kosullar = $_POST['ozel_kosullar'] ?? null;

// Veri kontrolü
if (!$organizasyon_id || !$sozlesme_no || !$imza_tarihi || !$baslangic_tarihi || !$bitis_tarihi || !$toplam_tutar) {
    echo json_encode(['success' => false, 'message' => 'Tüm zorunlu alanları doldurun']);
    exit();
}

try {
    // Sözleşme numarası kontrolü
    $stmt = $db->prepare("SELECT COUNT(*) FROM sozlesmeler WHERE sozlesme_no = ?");
    $stmt->execute([$sozlesme_no]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'message' => 'Bu sözleşme numarası zaten kullanılıyor']);
        exit();
    }

    // Sözleşmeyi ekle
    $stmt = $db->prepare("
        INSERT INTO sozlesmeler (
            organizasyon_id, sozlesme_no, imza_tarihi, baslangic_tarihi, 
            bitis_tarihi, toplam_tutar, odeme_plani, ozel_kosullar
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $organizasyon_id,
        $sozlesme_no,
        $imza_tarihi,
        $baslangic_tarihi,
        $bitis_tarihi,
        $toplam_tutar,
        $odeme_plani,
        $ozel_kosullar
    ]);

    echo json_encode(['success' => true, 'message' => 'Sözleşme başarıyla eklendi']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
}
?> 