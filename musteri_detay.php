<?php
require_once 'config.php';

// Oturum kontrolü
if (!isset($_SESSION['kullanici_id'])) {
    header('Location: login.php');
    exit();
}

// ID kontrolü
if (!isset($_GET['id'])) {
    $_SESSION['hata'] = "Müşteri ID'si belirtilmedi.";
    header('Location: musteriler.php');
    exit();
}

$id = $_GET['id'];

try {
    // Müşteri bilgilerini getir
    $stmt = $db->prepare("SELECT * FROM musteriler WHERE id = ?");
    $stmt->execute([$id]);
    $musteri = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$musteri) {
        $_SESSION['hata'] = "Müşteri bulunamadı.";
        header('Location: musteriler.php');
        exit();
    }

    // Müşterinin organizasyonlarını getir
    $stmt = $db->prepare("
        SELECT * FROM organizasyonlar 
        WHERE musteri_id = ? 
        ORDER BY tarih DESC
    ");
    $stmt->execute([$id]);
    $organizasyonlar = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $_SESSION['hata'] = "Veritabanı hatası: " . $e->getMessage();
    header('Location: musteriler.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Müşteri Detay - Flow Organizasyon</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container py-4">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-person-vcard me-2"></i>
                            Müşteri Detay
                        </h5>
                        <div>
                            <a href="musteri_duzenle.php?id=<?php echo $id; ?>" class="btn btn-warning btn-sm me-2">
                                <i class="bi bi-pencil"></i> Düzenle
                            </a>
                            <a href="musteriler.php" class="btn btn-light btn-sm">
                                <i class="bi bi-arrow-left"></i> Geri Dön
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted">Kişisel Bilgiler</h6>
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="150">Ad Soyad:</th>
                                        <td><?php echo htmlspecialchars($musteri['ad_soyad']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Telefon:</th>
                                        <td><?php echo htmlspecialchars($musteri['telefon']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>E-posta:</th>
                                        <td><?php echo htmlspecialchars($musteri['email']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Adres:</th>
                                        <td><?php echo nl2br(htmlspecialchars($musteri['adres'])); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Notlar:</th>
                                        <td><?php echo nl2br(htmlspecialchars($musteri['notlar'])); ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">İstatistikler</h6>
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="150">Toplam Organizasyon:</th>
                                        <td><?php echo count($organizasyonlar); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Son Organizasyon:</th>
                                        <td>
                                            <?php 
                                            if (!empty($organizasyonlar)) {
                                                echo date('d.m.Y', strtotime($organizasyonlar[0]['tarih']));
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Organizasyonlar Tablosu -->
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-calendar-event me-2"></i>
                            Organizasyonlar
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($organizasyonlar)): ?>
                            <div class="alert alert-info">
                                Bu müşteriye ait henüz organizasyon bulunmamaktadır.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Kod</th>
                                            <th>Tarih</th>
                                            <th>Toplam Tutar</th>
                                            <th>Durum</th>
                                            <th>İşlemler</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($organizasyonlar as $org): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($org['organizasyon_kodu']); ?></td>
                                                <td><?php echo date('d.m.Y', strtotime($org['tarih'])); ?></td>
                                                <td><?php echo number_format($org['toplam_tutar'], 2, ',', '.'); ?> ₺</td>
                                                <td>
                                                    <?php
                                                    $durum_class = '';
                                                    switch ($org['durum']) {
                                                        case 'beklemede':
                                                            $durum_class = 'warning';
                                                            break;
                                                        case 'onaylandi':
                                                            $durum_class = 'success';
                                                            break;
                                                        case 'iptal':
                                                            $durum_class = 'danger';
                                                            break;
                                                        case 'tamamlandi':
                                                            $durum_class = 'info';
                                                            break;
                                                    }
                                                    ?>
                                                    <span class="badge bg-<?php echo $durum_class; ?>">
                                                        <?php echo ucfirst($org['durum']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="organizasyon_detay.php?id=<?php echo $org['id']; ?>" 
                                                       class="btn btn-sm btn-info">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="organizasyon_duzenle.php?id=<?php echo $org['id']; ?>" 
                                                       class="btn btn-sm btn-warning">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 