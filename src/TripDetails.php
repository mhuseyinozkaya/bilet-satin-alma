<?php
session_start();

$pdo = new PDO("sqlite:database/database.db");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$sql = "SELECT Trips.*,
            Bus_Company.name AS company_name,
            Bus_Company.logo_path
        FROM Trips
        LEFT JOIN Bus_Company ON Bus_Company.id = Trips.company_id
        WHERE Trips.id = :trip_id";

$stmt_sefer = $pdo->prepare($sql);
$stmt_sefer->execute([
    'trip_id' => $_GET['id']
]);

$sefer = $stmt_sefer->fetch(PDO::FETCH_ASSOC);

require_once 'includes/header.php';
?>

<?php if (isset($sefer['id'])): ?>
    <div class="row justify-content-center">
        <div class="col-md-10">
            <h3 class="mb-4">Sefer Detayları ve Koltuk Seçimi</h3>

            <div class="card mb-3 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">

                        <div class="col-md-2 text-center">
                            <?php if (!empty(trim($sefer['logo_path']))): ?>
                                <img src="<?php echo htmlspecialchars(trim($sefer['logo_path'])); ?>"
                                    style="max-height: 50px; max-width: 100%;">
                            <?php else: ?>
                                <strong><?php echo htmlspecialchars($sefer['company_name']); ?></strong>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-2">
                            <strong>Tarih</strong><br>
                            <span class="fs-5 fw-bold">
                                <small><?php echo (new DateTime($sefer['departure_time']))->format('d-m-Y'); ?></small>
                            </span>
                        </div>

                        <div class="col-md-2">
                            <strong>Kalkış Saati</strong><br>
                            <span class="fs-5 fw-bold">
                                <small><?php echo (new DateTime($sefer['departure_time']))->format('H:i'); ?></small>
                            </span><br>
                        </div>

                        <div class="col-md-2">
                            <strong>Varış Saati</strong><br>
                            <span class="fs-5 fw-bold">
                                <small><?php echo (new DateTime($sefer['arrival_time']))->format('H:i') ?></small>
                            </span><br>

                        </div>

                        <div class="col-md-2">
                            <strong>Güzergah</strong><br>
                            <span class="fs-5">
                                <?php echo htmlspecialchars($sefer['departure_city']); ?>
                                &rarr;
                                <?php echo htmlspecialchars($sefer['destination_city']); ?>
                            </span>
                        </div>

                        <div class="col-md-2 text-end">
                            <strong>Bilet Fiyatı</strong><br>
                            <span class="fs-4 fw-bold text-success">
                                <?php echo htmlspecialchars($sefer['price']); ?> br
                            </span>
                        </div>

                    </div>
                </div>
            </div>

            // Koltuk Detayları Buraya Gelecek

            <form action="BuyTicket.php" method="POST" class="mt-4">
                <input type="hidden" name="trip_id" value="<?php echo $sefer['id']; ?>">
                <input type="hidden" name="secilen_koltuk" id="secilen_koltuk_input" value="">

                <button type="submit" class="btn btn-success btn-lg w-100">
                    Seçili Koltuğu Satın Al
                </button>
            </form>
        </div>
    </div>
    </div>
    </div>
    <?php
    require_once 'includes/footer.php';
?>
<?php else:
    echo "Sefer bulunamadı"; ?>
<?php endif; ?>