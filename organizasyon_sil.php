<?php
require_once 'config.php';

// Oturum kontrolü
if (!isset($_SESSION['kullanici_id'])) {
    header('Location: login.php');
    exit();
}

// ID kontrolü
if (!isset($_GET['id'])) {
    header('Location: organizasyonlar.php');
    exit();
}

$id = $_GET['id'];

try {
    // Önce organizasyonun ödemelerini kontrol et
    $stmt = $db->prepare("SELECT COUNT(*) FROM odemeler WHERE organizasyon_id = ?");
    $stmt->execute([$id]);
    $odeme_sayisi = $stmt->fetchColumn();

    if ($odeme_sayisi > 0) {
        $_SESSION['hata'] = "Bu organizasyonun ödemeleri bulunmaktadır. Önce ödemeleri silmelisiniz.";
        header('Location: organizasyonlar.php');
        exit();
    }

    // Sözleşmeleri kontrol et
    $stmt = $db->prepare("SELECT COUNT(*) FROM sozlesmeler WHERE organizasyon_id = ?");
    $stmt->execute([$id]);
    $sozlesme_sayisi = $stmt->fetchColumn();

    if ($sozlesme_sayisi > 0) {
        $_SESSION['hata'] = "Bu organizasyonun sözleşmeleri bulunmaktadır. Önce sözleşmeleri silmelisiniz.";
        header('Location: organizasyonlar.php');
        exit();
    }

    // Personel görevlerini kontrol et
    $stmt = $db->prepare("SELECT COUNT(*) FROM personel_gorevleri WHERE organizasyon_id = ?");
    $stmt->execute([$id]);
    $gorev_sayisi = $stmt->fetchColumn();

    if ($gorev_sayisi > 0) {
        $_SESSION['hata'] = "Bu organizasyonun personel görevleri bulunmaktadır. Önce görevleri silmelisiniz.";
        header('Location: organizasyonlar.php');
        exit();
    }

    // Organizasyonu sil
    $stmt = $db->prepare("DELETE FROM organizasyonlar WHERE id = ?");
    $stmt->execute([$id]);

    $_SESSION['basarili'] = "Organizasyon başarıyla silindi.";
    header('Location: organizasyonlar.php');
    exit();

} catch (PDOException $e) {
    $_SESSION['hata'] = "Organizasyon silinirken bir hata oluştu: " . $e->getMessage();
    header('Location: organizasyonlar.php');
    exit();
} 