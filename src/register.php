<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

$db = new PDO('sqlite:database/database.db');

// Form gönderildiyse
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $userRole = "user";

    $fullName = $_POST['full_name'];
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Basit validation
    if (empty($fullName) || empty($email) || empty($password)) {
        echo "Ad soyad, e-posta ve şifre boş olamaz.";
        exit;
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Kullanıcıyı ekle
    $stmt = $db->prepare("INSERT INTO User (full_name, email, role , password) VALUES (:full_name, :email, :role, :password)");

    try {
        $stmt->execute([
            ':full_name'=> $fullName,
            ':email' => $email,
            ':role'=> $userRole,
            ':password' => $hashed_password
        ]);
        echo "Kayıt başarılı!";
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // UNIQUE constraint hatası
            echo "Bu e-posta ile zaten eşleşen kayıt var.";
        } else {
            echo "Hata: " . $e->getMessage();
        }
    }
}
?>

<form method="post">
    Ad-Soyad: <input type="text" name="full_name" required><br>
    E-Posta: <input type="email" name="email" required><br>
    Şifre: <input type="password" name="password" required><br>
    <button type="submit">Kayıt Ol</button>
</form>