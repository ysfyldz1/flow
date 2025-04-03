<?php
require_once 'config.php';

// Oturum kontrolü
if (!isset($_SESSION['kullanici_id'])) {
    header('Location: login.php');
    exit();
}

// ID kontrolü
$id = $_GET['id'] ?? 0;
if (!$id) {
    header('Location: personel.php');
    exit();
}

// Personel bilgilerini getir
try {
    $stmt = $db->prepare("SELECT * FROM personel WHERE id = ?");
    $stmt->execute([$id]);
    $personel = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$personel) {
        header('Location: personel.php');
        exit();
    }
} catch (PDOException $e) {
    die('Hata: ' . $e->getMessage());
}

// Form gönderildi mi?
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ad_soyad = $_POST['ad_soyad'] ?? '';
    $pozisyon = $_POST['pozisyon'] ?? '';
    $telefon = $_POST['telefon'] ?? '';
    $email = $_POST['email'] ?? '';
    $adres = $_POST['adres'] ?? '';
    $maas = $_POST['maas'] ?? 0;
    $baslangic_tarihi = $_POST['baslangic_tarihi'] ?? '';
    $durum = $_POST['durum'] ?? 'aktif';
    $notlar = $_POST['notlar'] ?? '';

    try {
        $stmt = $db->prepare("UPDATE personel SET 
                            ad_soyad = ?, 
                            pozisyon = ?, 
                            telefon = ?, 
                            email = ?, 
                            adres = ?, 
                            maas = ?, 
                            baslangic_tarihi = ?, 
                            durum = ?, 
                            notlar = ? 
                            WHERE id = ?");
        
        $stmt->execute([$ad_soyad, $pozisyon, $telefon, $email, $adres, $maas, $baslangic_tarihi, $durum, $notlar, $id]);
        
        header('Location: personel.php');
        exit();
    } catch (PDOException $e) {
        $error = 'Hata: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personel Düzenle - Flow Organizasyon</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-header">
                        <h4>Personel Düzenle</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Ad Soyad</label>
                                <input type="text" class="form-control" name="ad_soyad" value="<?php echo htmlspecialchars($personel['ad_soyad']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Pozisyon</label>
                                <input type="text" class="form-control" name="pozisyon" value="<?php echo htmlspecialchars($personel['pozisyon']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Telefon</label>
                                <input type="text" class="form-control" name="telefon" value="<?php echo htmlspecialchars($personel['telefon']); ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">E-posta</label>
                                <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($personel['email']); ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Adres</label>
                                <textarea class="form-control" name="adres" rows="3"><?php echo htmlspecialchars($personel['adres']); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Maaş</label>
                                <input type="number" step="0.01" class="form-control" name="maas" value="<?php echo $personel['maas']; ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Başlangıç Tarihi</label>
                                <input type="date" class="form-control" name="baslangic_tarihi" value="<?php echo $personel['baslangic_tarihi']; ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Durum</label>
                                <select class="form-select" name="durum">
                                    <option value="aktif" <?php echo $personel['durum'] == 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                                    <option value="pasif" <?php echo $personel['durum'] == 'pasif' ? 'selected' : ''; ?>>Pasif</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Notlar</label>
                                <textarea class="form-control" name="notlar" rows="3"><?php echo htmlspecialchars($personel['notlar']); ?></textarea>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Kaydet</button>
                                <a href="personel.php" class="btn btn-secondary">İptal</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 