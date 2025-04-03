<?php
require_once 'config.php';

// Oturum kontrolü
if (!isset($_SESSION['kullanici_id'])) {
    header('Location: login.php');
    exit();
}

// Tarih aralığı filtreleme
$baslangic_tarihi = isset($_GET['baslangic']) ? $_GET['baslangic'] : date('Y-m-01');
$bitis_tarihi = isset($_GET['bitis']) ? $_GET['bitis'] : date('Y-m-t');

// Toplam organizasyon sayısı
$stmt = $db->prepare("SELECT COUNT(*) FROM organizasyonlar WHERE tarih BETWEEN ? AND ?");
$stmt->execute([$baslangic_tarihi, $bitis_tarihi]);
$toplam_organizasyon = $stmt->fetchColumn();

// Toplam gelir
$stmt = $db->prepare("SELECT SUM(toplam_tutar) FROM organizasyonlar WHERE tarih BETWEEN ? AND ?");
$stmt->execute([$baslangic_tarihi, $bitis_tarihi]);
$toplam_gelir = $stmt->fetchColumn();

// Durum bazında organizasyon sayıları
$stmt = $db->prepare("SELECT durum, COUNT(*) as sayi FROM organizasyonlar WHERE tarih BETWEEN ? AND ? GROUP BY durum");
$stmt->execute([$baslangic_tarihi, $bitis_tarihi]);
$durum_istatistikleri = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Müşteri bazında organizasyon sayıları
$stmt = $db->prepare("SELECT m.ad_soyad, COUNT(*) as sayi 
                     FROM organizasyonlar o 
                     LEFT JOIN musteriler m ON o.musteri_id = m.id 
                     WHERE o.tarih BETWEEN ? AND ? 
                     GROUP BY m.id 
                     ORDER BY sayi DESC 
                     LIMIT 5");
$stmt->execute([$baslangic_tarihi, $bitis_tarihi]);
$musteri_istatistikleri = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Raporlar - Flow Organizasyon</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Raporlar</h1>
            <form class="d-flex gap-2">
                <input type="date" class="form-control" name="baslangic" value="<?php echo $baslangic_tarihi; ?>">
                <input type="date" class="form-control" name="bitis" value="<?php echo $bitis_tarihi; ?>">
                <button type="submit" class="btn btn-primary">Filtrele</button>
            </form>
        </div>

        <div class="row">
            <!-- Özet Kartları -->
            <div class="col-md-3 mb-4">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">Toplam Organizasyon</h5>
                        <h2 class="card-text"><?php echo $toplam_organizasyon; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">Toplam Gelir</h5>
                        <h2 class="card-text"><?php echo number_format($toplam_gelir, 2, ',', '.'); ?> ₺</h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Durum Grafiği -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Organizasyon Durumları</h5>
                        <canvas id="durumChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Müşteri Grafiği -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">En Çok Organizasyon Yapan Müşteriler</h5>
                        <canvas id="musteriChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Durum Grafiği
        const durumCtx = document.getElementById('durumChart').getContext('2d');
        new Chart(durumCtx, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode(array_column($durum_istatistikleri, 'durum')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($durum_istatistikleri, 'sayi')); ?>,
                    backgroundColor: ['#0dcaf0', '#ffc107', '#198754', '#dc3545']
                }]
            }
        });

        // Müşteri Grafiği
        const musteriCtx = document.getElementById('musteriChart').getContext('2d');
        new Chart(musteriCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($musteri_istatistikleri, 'ad_soyad')); ?>,
                datasets: [{
                    label: 'Organizasyon Sayısı',
                    data: <?php echo json_encode(array_column($musteri_istatistikleri, 'sayi')); ?>,
                    backgroundColor: '#0d6efd'
                }]
            },
            options: {
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