<?php
require_once 'config.php';

// Oturum kontrolü
if (!isset($_SESSION['kullanici_id'])) {
    header('Location: login.php');
    exit();
}

// Sözleşme ID'sini al
$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: sozlesmeler.php');
    exit();
}

try {
    // Sözleşme bilgilerini getir
    $stmt = $db->prepare("
        SELECT s.*, o.organizasyon_kodu, o.baslik, o.tarih, o.baslangic_saati, o.bitis_saati,
               m.ad_soyad as musteri_adi, m.telefon, m.email, m.adres
        FROM sozlesmeler s
        JOIN organizasyonlar o ON s.organizasyon_id = o.id
        JOIN musteriler m ON o.musteri_id = m.id
        WHERE s.id = ?
    ");
    $stmt->execute([$id]);
    $sozlesme = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$sozlesme) {
        header('Location: sozlesmeler.php');
        exit();
    }

} catch (PDOException $e) {
    die("Veritabanı hatası: " . $e->getMessage());
}

// Form gönderildi mi kontrol et
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sozlesme_no = $_POST['sozlesme_no'] ?? null;
    $imza_tarihi = $_POST['imza_tarihi'] ?? null;
    $baslangic_tarihi = $_POST['baslangic_tarihi'] ?? null;
    $bitis_tarihi = $_POST['bitis_tarihi'] ?? null;
    $toplam_tutar = $_POST['toplam_tutar'] ?? null;
    $odeme_plani = $_POST['odeme_plani'] ?? null;
    $ozel_kosullar = $_POST['ozel_kosullar'] ?? null;
    $durum = $_POST['durum'] ?? null;

    try {
        // Sözleşme numarası kontrolü (kendi numarası hariç)
        $stmt = $db->prepare("SELECT COUNT(*) FROM sozlesmeler WHERE sozlesme_no = ? AND id != ?");
        $stmt->execute([$sozlesme_no, $id]);
        if ($stmt->fetchColumn() > 0) {
            $hata = "Bu sözleşme numarası zaten kullanılıyor";
        } else {
            // Sözleşmeyi güncelle
            $stmt = $db->prepare("
                UPDATE sozlesmeler SET
                    sozlesme_no = ?,
                    imza_tarihi = ?,
                    baslangic_tarihi = ?,
                    bitis_tarihi = ?,
                    toplam_tutar = ?,
                    odeme_plani = ?,
                    ozel_kosullar = ?,
                    durum = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $sozlesme_no,
                $imza_tarihi,
                $baslangic_tarihi,
                $bitis_tarihi,
                $toplam_tutar,
                $odeme_plani,
                $ozel_kosullar,
                $durum,
                $id
            ]);

            header('Location: sozlesme_goruntule.php?id=' . $id);
            exit();
        }
    } catch (PDOException $e) {
        $hata = "Veritabanı hatası: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sözleşme Düzenle - Flow Organizasyon</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Sözleşme Düzenle</h2>
            <a href="sozlesme_goruntule.php?id=<?php echo $id; ?>" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Geri Dön
            </a>
        </div>

        <?php if (isset($hata)): ?>
            <div class="alert alert-danger"><?php echo $hata; ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form method="POST">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Organizasyon</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($sozlesme['organizasyon_kodu'] . ' - ' . $sozlesme['baslik']); ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Sözleşme No</label>
                            <input type="text" class="form-control" name="sozlesme_no" value="<?php echo htmlspecialchars($sozlesme['sozlesme_no']); ?>" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">İmza Tarihi</label>
                            <input type="date" class="form-control" name="imza_tarihi" value="<?php echo $sozlesme['imza_tarihi']; ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Başlangıç Tarihi</label>
                            <input type="date" class="form-control" name="baslangic_tarihi" value="<?php echo $sozlesme['baslangic_tarihi']; ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Bitiş Tarihi</label>
                            <input type="date" class="form-control" name="bitis_tarihi" value="<?php echo $sozlesme['bitis_tarihi']; ?>" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Toplam Tutar</label>
                            <input type="number" step="0.01" class="form-control" name="toplam_tutar" value="<?php echo $sozlesme['toplam_tutar']; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Durum</label>
                            <select class="form-select" name="durum" required>
                                <option value="aktif" <?php echo $sozlesme['durum'] == 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                                <option value="pasif" <?php echo $sozlesme['durum'] == 'pasif' ? 'selected' : ''; ?>>Pasif</option>
                                <option value="iptal" <?php echo $sozlesme['durum'] == 'iptal' ? 'selected' : ''; ?>>İptal</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ödeme Planı</label>
                        <textarea class="form-control" name="odeme_plani" rows="3"><?php echo htmlspecialchars($sozlesme['odeme_plani']); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Özel Koşullar</label>
                        <textarea class="form-control" name="ozel_kosullar" rows="3"><?php echo htmlspecialchars($sozlesme['ozel_kosullar']); ?></textarea>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">Kaydet</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 