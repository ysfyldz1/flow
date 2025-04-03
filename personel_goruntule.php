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

    // Personelin görevlerini getir
    $stmt = $db->prepare("
        SELECT pg.*, o.baslik as organizasyon_baslik 
        FROM personel_gorevleri pg 
        LEFT JOIN organizasyonlar o ON pg.organizasyon_id = o.id 
        WHERE pg.personel_id = ? 
        ORDER BY pg.baslangic_tarihi DESC
    ");
    $stmt->execute([$id]);
    $gorevler = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Hata: ' . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personel Detay - Flow Organizasyon</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>Personel Detay</h4>
                        <div>
                            <a href="personel_duzenle.php?id=<?php echo $id; ?>" class="btn btn-warning btn-sm">
                                <i class="bi bi-pencil"></i> Düzenle
                            </a>
                            <a href="personel.php" class="btn btn-secondary btn-sm">
                                <i class="bi bi-arrow-left"></i> Geri
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h5>Kişisel Bilgiler</h5>
                                <table class="table table-bordered">
                                    <tr>
                                        <th>Ad Soyad:</th>
                                        <td><?php echo htmlspecialchars($personel['ad_soyad']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Pozisyon:</th>
                                        <td><?php echo htmlspecialchars($personel['pozisyon']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Telefon:</th>
                                        <td><?php echo htmlspecialchars($personel['telefon']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>E-posta:</th>
                                        <td><?php echo htmlspecialchars($personel['email']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Adres:</th>
                                        <td><?php echo nl2br(htmlspecialchars($personel['adres'])); ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h5>İş Bilgileri</h5>
                                <table class="table table-bordered">
                                    <tr>
                                        <th>Maaş:</th>
                                        <td><?php echo number_format($personel['maas'], 2, ',', '.'); ?> ₺</td>
                                    </tr>
                                    <tr>
                                        <th>Başlangıç Tarihi:</th>
                                        <td><?php echo date('d.m.Y', strtotime($personel['baslangic_tarihi'])); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Durum:</th>
                                        <td>
                                            <?php
                                            $durum_class = $personel['durum'] == 'aktif' ? 'success' : 'danger';
                                            ?>
                                            <span class="badge bg-<?php echo $durum_class; ?>">
                                                <?php echo ucfirst($personel['durum']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Notlar:</th>
                                        <td><?php echo nl2br(htmlspecialchars($personel['notlar'])); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <h5 class="mb-3">Görevler</h5>
                        <?php if (count($gorevler) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Organizasyon</th>
                                            <th>Görev</th>
                                            <th>Başlangıç</th>
                                            <th>Bitiş</th>
                                            <th>Durum</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($gorevler as $gorev): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($gorev['organizasyon_baslik']); ?></td>
                                                <td><?php echo htmlspecialchars($gorev['gorev']); ?></td>
                                                <td><?php echo date('d.m.Y', strtotime($gorev['baslangic_tarihi'])); ?></td>
                                                <td><?php echo date('d.m.Y', strtotime($gorev['bitis_tarihi'])); ?></td>
                                                <td>
                                                    <?php
                                                    $durum_class = $gorev['durum'] == 'tamamlandi' ? 'success' : 
                                                                 ($gorev['durum'] == 'devam' ? 'primary' : 'warning');
                                                    ?>
                                                    <span class="badge bg-<?php echo $durum_class; ?>">
                                                        <?php echo ucfirst($gorev['durum']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">Henüz görev atanmamış.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 