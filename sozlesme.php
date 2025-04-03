<?php
require_once 'config.php';

// Oturum kontrolü
if (!isset($_SESSION['kullanici_id'])) {
    header('Location: login.php');
    exit();
}

// Organizasyon ID kontrolü
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$org_id = $_GET['id'];

// Organizasyon ve müşteri bilgilerini çek
$stmt = $db->prepare("
    SELECT o.*, m.ad_soyad as musteri_adi, m.telefon, m.adres, m.tc_no,
           o.baslangic_saati, o.bitis_saati, o.ozel_istekler
    FROM organizasyonlar o
    LEFT JOIN musteriler m ON o.musteri_id = m.id
    WHERE o.id = ?
");
$stmt->execute([$org_id]);
$organizasyon = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$organizasyon) {
    header('Location: index.php');
    exit();
}

// PDF indirme isteği varsa
if (isset($_GET['pdf'])) {
    require_once 'vendor/autoload.php'; // TCPDF kütüphanesi
    
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    $pdf->SetCreator('Flow Organizasyon');
    $pdf->SetAuthor('Flow Organizasyon');
    $pdf->SetTitle('Organizasyon Sözleşmesi');
    
    $pdf->AddPage();
    
    // Logo
    $pdf->Image('assets/img/logo.png', 85, 10, 40);
    
    // Sözleşme içeriği
    $html = getSozlesmeHTML($organizasyon);
    $pdf->writeHTML($html, true, false, true, false, '');
    
    $pdf->Output('sozlesme.pdf', 'D');
    exit();
}

function getSozlesmeHTML($org) {
    $tarih = date('d.m.Y');
    $org_tarihi = date('d.m.Y', strtotime($org['tarih']));
    $org_saati = date('H:i', strtotime($org['baslangic_saati'])) . ' - ' . date('H:i', strtotime($org['bitis_saati']));
    
    return '
    <div style="text-align: center; margin-bottom: 20px;">
        <img src="assets/img/logo.png" style="width: 150px; margin-bottom: 15px;">
        <h2 style="color: #2c3e50; font-size: 20px;">' . htmlspecialchars($org['organizasyon_kodu']) . ' - NOLU ORGANİZASYON SÖZLEŞMESİ</h2>
        <p style="color: #7f8c8d; font-size: 12px;">Kemalpaşa mah. 4. Yıldırım sok. No:12/B Sefaköy K.çekmece/İST.</p>
    </div>
    
    <div style="margin-bottom: 20px;">
        <table cellpadding="5" style="width: 100%; border-collapse: separate; border-spacing: 0 5px; font-size: 12px;">
            <tr>
                <td style="width: 20%; background-color: #f8f9fa; padding: 10px; border-radius: 5px;">
                    <strong style="color: #2c3e50;">Tarih:</strong>
                </td>
                <td style="width: 30%; background-color: #ffffff; padding: 10px; border: 1px solid #dee2e6; border-radius: 5px;">
                    ' . $tarih . '
                </td>
                <td style="width: 20%; background-color: #f8f9fa; padding: 10px; border-radius: 5px;">
                    <strong style="color: #2c3e50;">Org. Tarihi:</strong>
                </td>
                <td style="width: 30%; background-color: #ffffff; padding: 10px; border: 1px solid #dee2e6; border-radius: 5px;">
                    ' . $org_tarihi . '
                </td>
            </tr>
            <tr>
                <td style="background-color: #f8f9fa; padding: 10px; border-radius: 5px;">
                    <strong style="color: #2c3e50;">Org. Saati:</strong>
                </td>
                <td style="background-color: #ffffff; padding: 10px; border: 1px solid #dee2e6; border-radius: 5px;">
                    ' . $org_saati . '
                </td>
                <td style="background-color: #f8f9fa; padding: 10px; border-radius: 5px;">
                    <strong style="color: #2c3e50;">Org. Türü:</strong>
                </td>
                <td style="background-color: #ffffff; padding: 10px; border: 1px solid #dee2e6; border-radius: 5px;">
                    ' . htmlspecialchars($org['baslik'] ?? '') . '
                </td>
            </tr>
            <tr>
                <td style="width: 15%; background-color: #f8f9fa; padding: 10px; border-radius: 5px;">
                    <strong style="color: #2c3e50;">Müşteri:</strong>
                </td>
                <td style="width: 35%; background-color: #ffffff; padding: 10px; border: 1px solid #dee2e6; border-radius: 5px;">
                    ' . htmlspecialchars($org['musteri_adi'] ?? '') . '
                </td>
                <td style="width: 15%; background-color: #f8f9fa; padding: 10px; border-radius: 5px;">
                    <strong style="color: #2c3e50;">T.C. No / Telefon:</strong>
                </td>
                <td style="width: 35%; background-color: #ffffff; padding: 10px; border: 1px solid #dee2e6; border-radius: 5px;">
                    ' . htmlspecialchars($org['tc_no'] ?? '') . ' / ' . htmlspecialchars($org['telefon'] ?? '') . '
                </td>
            </tr>
            <tr>
                <td style="background-color: #f8f9fa; padding: 10px; border-radius: 5px;">
                    <strong style="color: #2c3e50;">Adres:</strong>
                </td>
                <td colspan="3" style="background-color: #ffffff; padding: 10px; border: 1px solid #dee2e6; border-radius: 5px;">
                    ' . htmlspecialchars($org['adres'] ?? '') . '
                </td>
            </tr>
            <tr>
                <td style="background-color: #f8f9fa; padding: 10px; border-radius: 5px;">
                    <strong style="color: #2c3e50;">Toplam Tutar:</strong>
                </td>
                <td style="background-color: #ffffff; padding: 10px; border: 1px solid #dee2e6; border-radius: 5px;">
                    ' . number_format($org['toplam_tutar'] ?? 0, 2) . ' ₺
                </td>
                <td style="background-color: #f8f9fa; padding: 10px; border-radius: 5px;">
                    <strong style="color: #2c3e50;">Kapora:</strong>
                </td>
                <td style="background-color: #ffffff; padding: 10px; border: 1px solid #dee2e6; border-radius: 5px;">
                    ' . number_format($org['kapora'] ?? 0, 2) . ' ₺
                </td>
            </tr>
            <tr>
                <td style="background-color: #f8f9fa; padding: 10px; border-radius: 5px;">
                    <strong style="color: #2c3e50;">Kalan Tutar:</strong>
                </td>
                <td colspan="3" style="background-color: #ffffff; padding: 10px; border: 1px solid #dee2e6; border-radius: 5px;">
                    ' . number_format($org['kalan_tutar'] ?? 0, 2) . ' ₺
                </td>
            </tr>
            <tr>
                <td style="background-color: #f8f9fa; padding: 10px; border-radius: 5px;">
                    <strong style="color: #2c3e50;">Özel İstekler:</strong>
                </td>
                <td colspan="3" style="background-color: #ffffff; padding: 10px; border: 1px solid #dee2e6; border-radius: 5px;">
                    ' . nl2br(htmlspecialchars($org['ozel_istekler'] ?? '')) . '
                </td>
            </tr>
        </table>
    </div>
    
    <div style="margin: 20px 0; padding: 15px; background-color: #f8f9fa; border-radius: 5px;">
        <h3 style="color: #2c3e50; margin-bottom: 15px; font-size: 14px;">ORGANİZASYON ŞARTNAMESİ</h3>
        <ol style="color: #2c3e50; line-height: 1.4; font-size: 11px; margin: 0; padding-left: 20px;">
            <li>Mekanımız 40 kişi kapasiteli olup, fazladan gelen her misafir için ek ücret talep edilmektedir.</li>
            <li>Rezervasyon süresi 3 saat ile sınırlıdır.</li>
            <li>Organizasyon iptali halinde kapora geri iade edilmemektedir.</li>
            <li>Organizasyon sırasında verilen hasar misafir tarafından karşılanacaktır.</li>
            <li>Akşam saatlerinde olan organizasyonlarımız en geç 21:00 da bitmektedir.</li>
            <li>Organizasyon sırasında kişi başı 5 sandalye dışında sandalye çıkarılmamaktadır.</li>
            <li>Müzik için sadece ses düzeyi ve kullanımı bize aittir.</li>
            <li>Sonradan eklenen kişiler doğrultusunda fiyat güncellemesi gerçekleştirilecektir.</li>
            <li>Mağduriyetlerin önüne geçmek için alınan çocuklar ücretlendirilecektir.</li>
            <li>Organizasyon sırasında gelişen durumlar için firmamız sorumlu değildir. (Kaza, kavga, yaralanma vb.)</li>
        </ol>
    </div>
    
    <div style="margin: 20px 0;">
        <p style="color: #2c3e50; text-align: center; font-size: 12px;">İşbu sözleşme yukarıdaki maddelerden oluşup, ' . $tarih . ' tarihinde taraflar tarafından imzalanmıştır.</p>
    </div>
    
    <div style="margin-top: 20px;">
        <table style="width: 100%;">
            <tr>
                <td style="width: 50%; text-align: center; padding: 15px;">
                    <div style="border-bottom: 1px solid #dee2e6; padding-bottom: 8px; margin-bottom: 8px;">
                        <strong style="color: #2c3e50; font-size: 12px;">Firma Sahibi</strong>
                    </div>
                    <span style="color: #2c3e50; font-size: 12px;">Semiha Yıldız Aksoy</span>
                </td>
                <td style="width: 50%; text-align: center; padding: 15px;">
                    <div style="border-bottom: 1px solid #dee2e6; padding-bottom: 8px; margin-bottom: 8px;">
                        <strong style="color: #2c3e50; font-size: 12px;">Müşteri</strong>
                    </div>
                    <span style="color: #2c3e50; font-size: 12px;">' . htmlspecialchars($org['musteri_adi'] ?? '') . '</span>
                </td>
            </tr>
        </table>
    </div>';
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organizasyon Sözleşmesi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print {
                display: none;
            }
            body {
                padding: 0;
                margin: 0;
            }
            .container {
                width: 210mm;
                padding: 10mm;
                margin: 0;
                max-width: none;
            }
        }
        .container {
            background: white;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-top: 1rem !important;
        }
        @page {
            size: A4;
            margin: 0;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container my-2">
        <div class="text-end mb-4 no-print">
            <a href="?id=<?php echo $org_id; ?>&pdf=1" class="btn btn-primary me-2">
                <i class="bi bi-file-pdf"></i> PDF İndir
            </a>
            <button onclick="window.print()" class="btn btn-secondary">
                <i class="bi bi-printer"></i> Yazdır
            </button>
        </div>
        
        <?php echo getSozlesmeHTML($organizasyon); ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 