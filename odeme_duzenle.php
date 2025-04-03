<?php
require_once 'config.php';

// Oturum kontrolü
if (!isset($_SESSION['kullanici_id'])) {
    header('Location: login.php');
    exit();
}

// Ödeme ID'sini al
$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: odemeler.php');
    exit();
}

try {
    // Ödeme bilgilerini getir
    $stmt = $db->prepare("
        SELECT o.*, org.organizasyon_kodu, org.baslik, m.ad_soyad as musteri_adi 
        FROM odemeler o
        JOIN organizasyonlar org ON o.organizasyon_id = org.id
        JOIN musteriler m ON org.musteri_id = m.id
        WHERE o.id = ?
    ");
    $stmt->execute([$id]);
    $odeme = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$odeme) {
        header('Location: odemeler.php');
        exit();
    }

} catch (PDOException $e) {
    die("Veritabanı hatası: " . $e->getMessage());
}

// Form gönderildi mi kontrol et
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $organizasyon_id = $_POST['organizasyon_id'] ?? null;
    $tutar = $_POST['tutar'] ?? null;
    $odeme_tarihi = $_POST['odeme_tarihi'] ?? null;
    $odeme_turu = $_POST['odeme_turu'] ?? null;
    $aciklama = $_POST['aciklama'] ?? null;

    try {
        // Ödemeyi güncelle
        $stmt = $db->prepare("
            UPDATE odemeler SET
                organizasyon_id = ?,
                tutar = ?,
                odeme_tarihi = ?,
                odeme_turu = ?,
                aciklama = ?
            WHERE id = ?
        ");
        
        $stmt->execute([
            $organizasyon_id,
            $tutar,
            $odeme_tarihi,
            $odeme_turu,
            $aciklama,
            $id
        ]);

        header('Location: odemeler.php');
        exit();

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
    <title>Ödeme Düzenle - Flow Organizasyon</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Ödeme Düzenle</h2>
            <a href="odemeler.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Geri Dön
            </a>
        </div>

        <?php if (isset($hata)): ?>
            <div class="alert alert-danger"><?php echo $hata; ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Organizasyon</label>
                        <select class="form-select" name="organizasyon_id" required>
                            <option value="">Seçiniz</option>
                            <?php
                            $stmt = $db->query("SELECT o.id, o.organizasyon_kodu, o.baslik, m.ad_soyad 
                                              FROM organizasyonlar o 
                                              JOIN musteriler m ON o.musteri_id = m.id");
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                $selected = $row['id'] == $odeme['organizasyon_id'] ? 'selected' : '';
                                echo "<option value='{$row['id']}' {$selected}>{$row['organizasyon_kodu']} - {$row['baslik']} ({$row['ad_soyad']})</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tutar</label>
                        <input type="number" step="0.01" class="form-control" name="tutar" value="<?php echo $odeme['tutar']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ödeme Tarihi</label>
                        <input type="date" class="form-control" name="odeme_tarihi" value="<?php echo $odeme['odeme_tarihi']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ödeme Türü</label>
                        <select class="form-select" name="odeme_turu" required>
                            <option value="nakit" <?php echo $odeme['odeme_turu'] == 'nakit' ? 'selected' : ''; ?>>Nakit</option>
                            <option value="kredi_karti" <?php echo $odeme['odeme_turu'] == 'kredi_karti' ? 'selected' : ''; ?>>Kredi Kartı</option>
                            <option value="havale" <?php echo $odeme['odeme_turu'] == 'havale' ? 'selected' : ''; ?>>Havale</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Açıklama</label>
                        <textarea class="form-control" name="aciklama" rows="3"><?php echo htmlspecialchars($odeme['aciklama']); ?></textarea>
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