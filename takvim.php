<?php
require_once 'config.php';

// Oturum kontrolü
if (!isset($_SESSION['kullanici_id'])) {
    header('Location: login.php');
    exit();
}

// Organizasyonları getir
$stmt = $db->query("SELECT o.*, m.ad_soyad as musteri_adi 
                    FROM organizasyonlar o 
                    LEFT JOIN musteriler m ON o.musteri_id = m.id 
                    ORDER BY o.tarih, o.baslangic_saati");
$organizasyonlar = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Takvim verilerini hazırla
$takvim_verileri = [];
foreach ($organizasyonlar as $org) {
    $takvim_verileri[] = [
        'id' => $org['id'],
        'title' => $org['baslik'] . ' - ' . $org['musteri_adi'],
        'start' => $org['tarih'] . 'T' . $org['baslangic_saati'],
        'end' => $org['tarih'] . 'T' . $org['bitis_saati'],
        'color' => $org['durum'] == 'planlandi' ? '#0dcaf0' : 
                 ($org['durum'] == 'devam_ediyor' ? '#ffc107' : 
                 ($org['durum'] == 'tamamlandi' ? '#198754' : '#dc3545')),
        'url' => 'organizasyon_detay.php?id=' . $org['id']
    ];
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Takvim - Flow Organizasyon</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    <style>
        .fc-event {
            cursor: pointer;
        }
        .fc-toolbar-title {
            font-size: 1.2em !important;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Takvim</h1>
            <div>
                <a href="organizasyonlar.php" class="btn btn-primary">
                    <i class="bi bi-list-ul"></i> Organizasyon Listesi
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div id="takvim"></div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/tr.js'></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('takvim');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'tr',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                events: <?php echo json_encode($takvim_verileri); ?>,
                eventClick: function(info) {
                    window.location.href = info.event.url;
                },
                eventTimeFormat: {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false
                }
            });
            calendar.render();
        });
    </script>
</body>
</html> 