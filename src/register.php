<?php
session_start();
if (isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

$db = new PDO('sqlite:/var/www/database.db');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $userRole = 'user';

    if (!$fullName || !$email || !$password) {
        echo "Boş alan bırakılamaz.";
        exit;
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $db->prepare("INSERT INTO User (full_name, email, role, password) VALUES (:full_name, :email, :role, :password)");
    try {
        $stmt->execute([
            ':full_name'=> $fullName,
            ':email'=> $email,
            ':role'=> $userRole,
            ':password'=> $hashed_password
        ]);
        echo "Kayıt başarılı!";
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) echo "Bu e-posta zaten kayıtlı.";
        else echo "Hata: ".$e->getMessage();
    }
}

include 'includes/header.php';
?>

<form method="post">
    Ad-Soyad: <input type="text" name="full_name" required><br>
    E-Posta: <input type="email" name="email" required><br>
    Şifre: <input type="password" name="password" required><br>
    <button type="submit">Kayıt Ol</button>
</form>

<?php include 'includes/footer.php'; ?>
