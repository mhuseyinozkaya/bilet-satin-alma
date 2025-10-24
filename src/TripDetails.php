<?php
session_start();

try {
    $pdo = new PDO("sqlite:/var/www/database.db");
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

    if ($sefer) {
        $toplam_koltuk_sayisi = (int) $sefer['capacity']; // Şemadaki 'capacity' sütunu
        $sql_dolu = "SELECT 
                            bs.seat_number 
                         FROM 
                            Booked_Seats AS bs
                         JOIN 
                            Tickets AS t ON bs.ticket_id = t.id
                         WHERE 
                            t.trip_id = :trip_id";

        $stmt_dolu = $pdo->prepare($sql_dolu);
        $stmt_dolu->execute(['trip_id' => $sefer['id']]);

        $dolu_koltuklar = $stmt_dolu->fetchAll(PDO::FETCH_COLUMN);

        //  (2+1) Koltuk düzenini ayarla
        $koltuk_duzeni = [1, 1, 0, 1]; // 2 Koltuk, 1 Koridor, 1 Koltuk
        $sira_basina_koltuk = 3;
        $toplam_sira_sayisi = ceil($toplam_koltuk_sayisi / $sira_basina_koltuk);

    } else {
        $hata_mesaji = "Belirtilen sefer bulunamadı.";
    }

} catch (PDOException $e) {
    //Hata mesajı gösterme
}

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

            <hr class="my-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Koltuk Seçimi</h5>
                </div>
                <div class="card-body">
                    <div class="seat-plan-container">
                        <div class="bus-front">ŞOFÖR</div>
                        <?php
                        $koltuk_no = 1;
                        for ($sira = 1; $sira <= $toplam_sira_sayisi; $sira++):
                            ?>
                            <div class="seat-row">
                                <?php
                                // $koltuk_duzeni = [1, 1, 0, 1] (2+1)
                                foreach ($koltuk_duzeni as $tip):
                                    if ($tip == 1 && $koltuk_no <= $toplam_koltuk_sayisi):
                                        // Bu bir koltuk
                                        $is_taken = in_array($koltuk_no, $dolu_koltuklar);
                                        $seat_class = 'seat';
                                        if ($is_taken) {
                                            $seat_class .= ' taken'; // Doluysa 'taken' sınıfını ekle
                                        }
                                        ?>
                                        <div class="<?php echo $seat_class; ?>" data-seat-number="<?php echo $koltuk_no; ?>">
                                            <?php echo $koltuk_no; ?>
                                        </div>
                                        <?php
                                        $koltuk_no++;
                                    elseif ($tip == 0):
                                        ?>
                                        <div class="seat aisle"></div> <?php else: ?>
                                        <div class="seat" style="visibility: hidden;"></div> <?php
                                    endif;
                                endforeach;
                                ?>
                            </div>
                            <?php
                        endfor;
                        ?>
                    </div>
                </div>
            </div>

            <form action="BuyTicket.php" method="POST" class="mt-4">
                <input type="hidden" name="trip_id" value="<?php echo $sefer['id']; ?>">
                <input type="hidden" name="selected_seat" id="selected_seat" value="">

                <button type="submit" class="btn btn-success btn-lg w-100">
                    Seçili Koltuğu Satın Al
                </button>
            </form>
        </div>
    </div>
    </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const seatContainer = document.querySelector('.seat-plan-container');
            const hiddenInput = document.getElementById('selected_seat');

            if (seatContainer) { // Koltuk planı varsa
                seatContainer.addEventListener('click', function (e) {
                    // Tıklanan yer: .seat sınıfına sahip, .taken (dolu) değil VE .aisle (koridor) değilse
                    if (e.target.classList.contains('seat') && !e.target.classList.contains('taken') && !e.target.classList.contains('aisle')) {

                        const clickedSeat = e.target;
                        const seatNumber = clickedSeat.dataset.seatNumber;
                        const isSelected = clickedSeat.classList.contains('selected');

                        // 1. Önce (varsa) diğer seçili koltuğu kaldır
                        seatContainer.querySelectorAll('.seat.selected').forEach(function (seat) {
                            seat.classList.remove('selected');
                        });

                        if (!isSelected) {
                            // 2. Tıklanana 'selected' sınıfı ekle
                            clickedSeat.classList.add('selected');
                            // 3. Formdaki gizli input'un değerini ayarla
                            hiddenInput.value = seatNumber;
                        } else {
                            // 4. Zaten seçili olana tıklandıysa, seçimi iptal et
                            hiddenInput.value = '';
                        }
                    }
                });
            }
        });
    </script>
    <?php require_once 'includes/footer.php'; ?>
<?php else:
    echo "Sefer bulunamadı"; ?>
<?php endif; ?>