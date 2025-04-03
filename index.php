<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flow Organizasyon - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .stat-card {
            border-radius: 10px;
        }
        .stat-card i {
            font-size: 2rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
<?php
require_once 'config.php';

// Oturum kontrolü
if (!isset($_SESSION['kullanici_id'])) {
    header('Location: login.php');
    exit();
}

// Tarih aralığı için varsayılan değerler
$baslangic_tarihi = date('Y-m-01'); // Ayın ilk günü
$bitis_tarihi = date('Y-m-t'); // Ayın son günü

// Tarih filtresi varsa güncelle
if (isset($_GET['baslangic_tarihi']) && isset($_GET['bitis_tarihi'])) {
    $baslangic_tarihi = $_GET['baslangic_tarihi'];
    $bitis_tarihi = $_GET['bitis_tarihi'];
}

// Toplam organizasyon sayısı
$stmt = $db->prepare("SELECT COUNT(*) as toplam FROM organizasyonlar WHERE tarih BETWEEN ? AND ?");
$stmt->execute([$baslangic_tarihi, $bitis_tarihi]);
$toplam_organizasyon = $stmt->fetch(PDO::FETCH_ASSOC)['toplam'];

// Toplam gelir
$stmt = $db->prepare("SELECT SUM(toplam_tutar) as toplam FROM organizasyonlar WHERE tarih BETWEEN ? AND ?");
$stmt->execute([$baslangic_tarihi, $bitis_tarihi]);
$toplam_gelir = $stmt->fetch(PDO::FETCH_ASSOC)['toplam'] ?? 0;

// Durum bazında organizasyon sayıları
$stmt = $db->prepare("SELECT durum, COUNT(*) as sayi FROM organizasyonlar WHERE tarih BETWEEN ? AND ? GROUP BY durum");
$stmt->execute([$baslangic_tarihi, $bitis_tarihi]);
$durum_istatistikleri = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Müşteri bazında organizasyon sayıları (en çok organizasyonu olan 5 müşteri)
$stmt = $db->prepare("
    SELECT m.ad_soyad, COUNT(o.id) as organizasyon_sayisi 
    FROM organizasyonlar o 
    JOIN musteriler m ON o.musteri_id = m.id 
    WHERE o.tarih BETWEEN ? AND ?
    GROUP BY m.id 
    ORDER BY organizasyon_sayisi DESC 
    LIMIT 5
");
$stmt->execute([$baslangic_tarihi, $bitis_tarihi]);
$musteri_istatistikleri = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Yaklaşan organizasyonlar
$stmt = $db->prepare("
    SELECT o.*, m.ad_soyad as musteri_adi 
    FROM organizasyonlar o 
    JOIN musteriler m ON o.musteri_id = m.id 
    WHERE o.tarih >= CURDATE() 
    ORDER BY o.tarih ASC 
    LIMIT 5
");
$stmt->execute();
$yaklasan_organizasyonlar = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Son eklenen müşteriler
$stmt = $db->prepare("SELECT * FROM musteriler ORDER BY olusturma_tarihi DESC LIMIT 5");
$stmt->execute();
$son_musteriler = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Son ödemeler
$stmt = $db->prepare("
    SELECT o.*, org.baslik as organizasyon_adi, m.ad_soyad as musteri_adi 
    FROM odemeler o 
    JOIN organizasyonlar org ON o.organizasyon_id = org.id 
    JOIN musteriler m ON org.musteri_id = m.id 
    ORDER BY o.odeme_tarihi DESC 
    LIMIT 5
");
$stmt->execute();
$son_odemeler = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once 'navbar.php';
?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Tarih Filtresi</h5>
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label for="baslangic_tarihi" class="form-label">Başlangıç Tarihi</label>
                            <input type="date" class="form-control" id="baslangic_tarihi" name="baslangic_tarihi" 
                                   value="<?php echo $baslangic_tarihi; ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="bitis_tarihi" class="form-label">Bitiş Tarihi</label>
                            <input type="date" class="form-control" id="bitis_tarihi" name="bitis_tarihi" 
                                   value="<?php echo $bitis_tarihi; ?>">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Filtrele</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- İstatistik Kartları -->
        <div class="col-md-3">
            <div class="card stat-card bg-primary text-white h-100">
                <div class="card-body text-center">
                    <i class="bi bi-calendar-event"></i>
                    <h5 class="card-title">Toplam Organizasyon</h5>
                    <h2 class="card-text"><?php echo $toplam_organizasyon; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card bg-success text-white h-100">
                <div class="card-body text-center">
                    <i class="bi bi-cash-stack"></i>
                    <h5 class="card-title">Toplam Gelir</h5>
                    <h2 class="card-text"><?php echo number_format($toplam_gelir, 2); ?> ₺</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card bg-info text-white h-100">
                <div class="card-body text-center">
                    <i class="bi bi-people"></i>
                    <h5 class="card-title">Aktif Müşteriler</h5>
                    <h2 class="card-text"><?php echo count($musteri_istatistikleri); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card bg-warning text-white h-100">
                <div class="card-body text-center">
                    <i class="bi bi-calendar-check"></i>
                    <h5 class="card-title">Yaklaşan Organizasyonlar</h5>
                    <h2 class="card-text"><?php echo count($yaklasan_organizasyonlar); ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Yaklaşan Organizasyonlar -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-calendar-check me-2"></i>
                        Yaklaşan Organizasyonlar
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Tarih</th>
                                    <th>Müşteri</th>
                                    <th>Organizasyon</th>
                                    <th>Durum</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($yaklasan_organizasyonlar as $org): ?>
                                <tr>
                                    <td><?php echo date('d.m.Y', strtotime($org['tarih'])); ?></td>
                                    <td><?php echo htmlspecialchars($org['musteri_adi']); ?></td>
                                    <td><?php echo htmlspecialchars($org['baslik']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $org['durum'] == 'planlandi' ? 'primary' : 
                                                ($org['durum'] == 'devam_ediyor' ? 'warning' : 
                                                ($org['durum'] == 'tamamlandi' ? 'success' : 'danger')); 
                                        ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $org['durum'])); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Son Ödemeler -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-cash-stack me-2"></i>
                        Son Ödemeler
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Tarih</th>
                                    <th>Müşteri</th>
                                    <th>Organizasyon</th>
                                    <th>Tutar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($son_odemeler as $odeme): ?>
                                <tr>
                                    <td><?php echo date('d.m.Y', strtotime($odeme['odeme_tarihi'])); ?></td>
                                    <td><?php echo htmlspecialchars($odeme['musteri_adi']); ?></td>
                                    <td><?php echo htmlspecialchars($odeme['organizasyon_adi']); ?></td>
                                    <td><?php echo number_format($odeme['tutar'], 2); ?> ₺</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Grafikler -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-pie-chart me-2"></i>
                        Organizasyon Durumları
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="durumGrafik"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-bar-chart me-2"></i>
                        Müşteri Bazında Organizasyonlar
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="musteriGrafik"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Organizasyon Durumları Grafiği
    const durumCtx = document.getElementById('durumGrafik').getContext('2d');
    const durumGrafik = new Chart(durumCtx, {
        type: 'pie',
        data: {
            labels: <?php echo json_encode(array_column($durum_istatistikleri, 'durum')); ?>,
            datasets: [{
                data: <?php echo json_encode(array_column($durum_istatistikleri, 'sayi')); ?>,
                backgroundColor: [
                    '#007bff',
                    '#28a745',
                    '#ffc107',
                    '#dc3545'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Müşteri Bazında Organizasyonlar Grafiği
    const musteriCtx = document.getElementById('musteriGrafik').getContext('2d');
    const musteriGrafik = new Chart(musteriCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($musteri_istatistikleri, 'ad_soyad')); ?>,
            datasets: [{
                label: 'Organizasyon Sayısı',
                data: <?php echo json_encode(array_column($musteri_istatistikleri, 'organizasyon_sayisi')); ?>,
                backgroundColor: '#007bff'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
</script>
</body>
</html> 