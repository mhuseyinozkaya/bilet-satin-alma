<?php
$pageTitle = 'Anasayfa - Sefer Arama';

$seferler = null; // Arama sonuçlarını tutacak değişken
$kalkis_noktalari = [];
$varis_noktalari = [];

try {
    $pdo = new PDO("sqlite:/var/www/database.db");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Combo List için lokasyonları çek
    $stmt_kalkis = $pdo->query("SELECT DISTINCT departure_city FROM Trips ORDER BY departure_city ASC");
    $kalkis_noktalari = $stmt_kalkis->fetchAll(PDO::FETCH_ASSOC);
    $stmt_varis = $pdo->query("SELECT DISTINCT destination_city FROM Trips ORDER BY destination_city ASC");
    $varis_noktalari = $stmt_varis->fetchAll(PDO::FETCH_ASSOC);

    // Form gönderilmişse seferleri ara
    if (isset($_GET['kalkis_yeri']) && isset($_GET['varis_yeri']) && isset($_GET['tarih'])) {

        $kalkis = $_GET['kalkis_yeri'];
        $varis = $_GET['varis_yeri'];
        $tarih = $_GET['tarih'];

        $sql = "SELECT Trips.*,
                    Bus_Company.name AS company_name,
                    Bus_Company.logo_path
                FROM Trips 
                LEFT JOIN 
                    Bus_Company ON Bus_Company.id = Trips.company_id
                WHERE departure_city = :kalkis 
                    AND destination_city = :varis
                    AND DATE(departure_time) = :tarih";

        $stmt_seferler = $pdo->prepare($sql);
        $stmt_seferler->execute([
            'kalkis' => $kalkis,
            'varis' => $varis,
            'tarih' => $tarih
        ]);

        $seferler = $stmt_seferler->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (PDOException $e) {
    $hata_mesaji = "Veritabanı hatası: " . $e->getMessage();
    $seferler = [];
}

require_once 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card shadow-sm">
            <div class="card-header">
                <h3 class="mb-0">Otobüs Bileti Ara</h3>
            </div>
            <div class="card-body">
                <form action="index.php" method="GET" class="row g-3 align-items-end">

                    <div class="col-md-4">
                        <label for="kalkis" class="form-label">Kalkış Yeri</label>
                        <select id="kalkis" name="kalkis_yeri" class="form-select" required>
                            <option value="" selected disabled>Seçiniz...</option>
                            <?php foreach ($kalkis_noktalari as $lokasyon): ?>
                                <option value="<?php echo htmlspecialchars($lokasyon['departure_city']); ?>">
                                    <?php echo htmlspecialchars($lokasyon['departure_city']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="varis" class="form-label">Varış Yeri</label>
                        <select id="varis" name="varis_yeri" class="form-select" required>
                            <option value="" selected disabled>Seçiniz...</option>
                            <?php foreach ($varis_noktalari as $lokasyon): ?>
                                <option value="<?php echo htmlspecialchars($lokasyon['destination_city']); ?>">
                                    <?php echo htmlspecialchars($lokasyon['destination_city']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label for="tarih" class="form-label">Tarih</label>
                        <input type="date" id="tarih" name="tarih" class="form-control" required
                            min="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Sefer Ara</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<hr class="my-5">

<div class="row justify-content-center">
    <div class="col-md-10">
        <h3 class="mb-4">Sefer Sonuçları</h3>

        <?php if (isset($hata_mesaji)): ?>
            <div class="alert alert-danger">
                <?php echo $hata_mesaji; ?>
            </div>
        <?php elseif ($seferler === null): ?>
            <div class="alert alert-info">
                Lütfen kalkış, varış yeri ve tarih seçerek arama yapınız.
            </div>
        <?php elseif (empty($seferler)): ?>
            <div class="alert alert-warning">
                Aradığınız kriterlere uygun sefer bulunamadı.
            </div>
        <?php else: ?>
            <?php foreach ($seferler as $sefer): ?>
                <div class="card mb-3 shadow-sm">
                    <div class="card-body">
                        <div class="row align-items-center">

                            <div class="col-md-2 text-center">

                                <?php
                                // logo_path veritabanından boş gelmediyse
                                if (!empty($sefer['logo_path'])):
                                    ?>

                                    <img src="<?php echo htmlspecialchars($sefer['logo_path']); ?>"
                                        style="max-height: 50px; max-width: 100%;">

                                    <?php
                                    // Logo yolu boşsa, onun yerine firma adını yaz
                                else:
                                    ?>
                                    <strong><?php echo htmlspecialchars($sefer['company_name']); ?></strong>
                                    <?php
                                endif;
                                ?>
                            </div>

                            <div class="col-md-2">
                                <strong>Kalkış Saati</strong><br>
                                <span class="fs-5 fw-bold">
                                    <?php echo (new DateTime($sefer['departure_time']))->format('H:i'); ?>
                                </span>
                            </div>

                            <div class="col-md-3">
                                <strong>Güzergah</strong><br>
                                <?php echo htmlspecialchars($sefer['departure_city']); ?>
                                &rarr;
                                <?php echo htmlspecialchars($sefer['destination_city']); ?>
                            </div>

                            <div class="col-md-2 text-center">
                                <strong>Fiyat</strong><br>
                                <span class="fs-5 fw-bold text-success">
                                    <?php echo htmlspecialchars($sefer['price']); ?> br 
                                </span>
                            </div>

                            <div class="col-md-3 text-end">
                                <a href="TripDetails.php?id=<?php echo $sefer['id']; ?>" class="btn btn-success btn-lg">
                                    Detayları Gör / Bilet Al
                                </a>
                            </div>

                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

    </div>
</div>

<?php
require_once 'includes/footer.php';
?>