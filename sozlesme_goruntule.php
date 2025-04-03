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
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sözleşme Detayı - Flow Organizasyon</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Sözleşme Detayı</h2>
            <div>
                <a href="sozlesme_duzenle.php?id=<?php echo $id; ?>" class="btn btn-warning">
                    <i class="bi bi-pencil"></i> Düzenle
                </a>
                <a href="sozlesmeler.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Geri Dön
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="card-title">Sözleşme Bilgileri</h5>
                        <table class="table">
                            <tr>
                                <th>Sözleşme No:</th>
                                <td><?php echo htmlspecialchars($sozlesme['sozlesme_no']); ?></td>
                            </tr>
                            <tr>
                                <th>Organizasyon:</th>
                                <td>
                                    <?php echo htmlspecialchars($sozlesme['organizasyon_kodu']); ?> - 
                                    <?php echo htmlspecialchars($sozlesme['baslik']); ?>
                                </td>
                            </tr>
                            <tr>
                                <th>İmza Tarihi:</th>
                                <td><?php echo date('d.m.Y', strtotime($sozlesme['imza_tarihi'])); ?></td>
                            </tr>
                            <tr>
                                <th>Başlangıç Tarihi:</th>
                                <td><?php echo date('d.m.Y', strtotime($sozlesme['baslangic_tarihi'])); ?></td>
                            </tr>
                            <tr>
                                <th>Bitiş Tarihi:</th>
                                <td><?php echo date('d.m.Y', strtotime($sozlesme['bitis_tarihi'])); ?></td>
                            </tr>
                            <tr>
                                <th>Toplam Tutar:</th>
                                <td><?php echo number_format($sozlesme['toplam_tutar'], 2, ',', '.'); ?> ₺</td>
                            </tr>
                            <tr>
                                <th>Durum:</th>
                                <td>
                                    <?php
                                    $durum_class = '';
                                    switch($sozlesme['durum']) {
                                        case 'aktif':
                                            $durum_class = 'success';
                                            break;
                                        case 'pasif':
                                            $durum_class = 'warning';
                                            break;
                                        case 'iptal':
                                            $durum_class = 'danger';
                                            break;
                                    }
                                    ?>
                                    <span class="badge bg-<?php echo $durum_class; ?>">
                                        <?php echo ucfirst($sozlesme['durum']); ?>
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5 class="card-title">Müşteri Bilgileri</h5>
                        <table class="table">
                            <tr>
                                <th>Ad Soyad:</th>
                                <td><?php echo htmlspecialchars($sozlesme['musteri_adi']); ?></td>
                            </tr>
                            <tr>
                                <th>Telefon:</th>
                                <td><?php echo htmlspecialchars($sozlesme['telefon']); ?></td>
                            </tr>
                            <tr>
                                <th>E-posta:</th>
                                <td><?php echo htmlspecialchars($sozlesme['email']); ?></td>
                            </tr>
                            <tr>
                                <th>Adres:</th>
                                <td><?php echo nl2br(htmlspecialchars($sozlesme['adres'])); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6">
                        <h5 class="card-title">Ödeme Planı</h5>
                        <div class="card">
                            <div class="card-body">
                                <?php echo nl2br(htmlspecialchars($sozlesme['odeme_plani'])); ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h5 class="card-title">Özel Koşullar</h5>
                        <div class="card">
                            <div class="card-body">
                                <?php echo nl2br(htmlspecialchars($sozlesme['ozel_kosullar'])); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 