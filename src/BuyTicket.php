<?php
session_start();
if (!empty($_POST['trip_id']) && !empty($_POST['selected_seat'])) {
    if (isset($_SESSION["id"]) && $_SESSION['role'] === 'user') {

        $trip_id = $_POST['trip_id'];
        $selected_seat = $_POST['selected_seat'];

        try {
            $pdo = new PDO('sqlite:/var/www/database.db');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $get_balance = 'SELECT balance FROM User WHERE id = :user_id';
            $get_trip_price = 'SELECT price FROM Trips WHERE id = :trip_id';

            $stmt = $pdo->prepare($get_balance);
            $stmt->execute(['user_id' => $_SESSION['id']]);

            $balance = $stmt->fetch(PDO::FETCH_ASSOC);

            $stmt = $pdo->prepare($get_trip_price);
            $stmt->execute(['trip_id' => $trip_id]);

            $price = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($balance && $price) {
                $balanceValue = (float) $balance['balance'];
                $priceValue = (float) $price['price'];

                if ($balanceValue < $priceValue) { ?>
                    <script>
                        alert('Bileti almak için yeterli bakiyeniz yok!');
                        window.location.href = 'index.php';
                    </script>
                    <?php exit;
                }

                $new_balance = $balanceValue - $priceValue;

                $save_ticket = 'INSERT INTO Tickets (trip_id, user_id, total_price)
                                VALUES (:trip_id, :user_id, :total_price)';
                $save_booked_seat = 'INSERT INTO Booked_Seats (ticket_id, seat_number)
                                    VALUES (:ticket_id, :seat_number)';
                $update_user_balance = 'UPDATE User SET balance = :new_balance 
                                        WHERE id = :user_id';

                $stmt = $pdo->prepare($save_ticket);
                $stmt->execute([
                    'trip_id' => $trip_id,
                    'user_id' => $_SESSION['id'],
                    'total_price' => $priceValue
                ]);

                // Son eklenen Ticket'in ID'sini alma
                $ticket_id = $pdo->lastInsertId();

                $stmt = $pdo->prepare($save_booked_seat);
                $stmt->execute([
                    'ticket_id' => $ticket_id,
                    'seat_number' => $selected_seat
                ]);

                $stmt = $pdo->prepare($update_user_balance);
                $stmt->execute([
                    'new_balance' => $new_balance,
                    'user_id' => $_SESSION['id']
                ]);

            }

        } catch (PDOException $e) {
            //Hata mesajı gösterme
        }

    } else { ?>
        <script>
            alert('Bilet almak için lütfen kullanıcı olarak giriş yapın!');
            window.location.href = 'login.php';
        </script>
        <?php exit;
    }

} else {
    echo "'trip_id' ve 'selected_seat' boş olamaz.";
}