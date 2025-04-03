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
$ad_soyad = $_POST['ad_soyad'] ?? '';
$pozisyon = $_POST['pozisyon'] ?? '';
$telefon = $_POST['telefon'] ?? '';
$email = $_POST['email'] ?? '';
$adres = $_POST['adres'] ?? '';
$maas = $_POST['maas'] ?? 0;
$baslangic_tarihi = $_POST['baslangic_tarihi'] ?? date('Y-m-d');
$notlar = $_POST['notlar'] ?? '';

try {
    // Veritabanına personel ekle
    $stmt = $db->prepare("INSERT INTO personel (ad_soyad, pozisyon, telefon, email, adres, maas, baslangic_tarihi, durum, notlar, olusturma_tarihi) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, 'aktif', ?, NOW())");
    
    $stmt->execute([$ad_soyad, $pozisyon, $telefon, $email, $adres, $maas, $baslangic_tarihi, $notlar]);
    
    echo json_encode(['success' => true, 'message' => 'Personel başarıyla eklendi']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Hata: ' . $e->getMessage()]);
} 