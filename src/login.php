<?php
session_start();
if (isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

$db = new PDO('sqlite:/var/www/database.db');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!$email || !$password) {
        echo "E-posta ve şifre boş olamaz.";
        exit;
    }

    $stmt = $db->prepare("SELECT id, full_name, email, role, password FROM User WHERE email=:email LIMIT 1");
    $stmt->execute([':email'=>$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    //  Kullanıcı kaydı eşleşiyorsa SESSION ataması yap
    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
        if($_SESSION['role'] === 'admin'){
            header("Location: admin/admin.php");   
        }else{
            header("Location: index.php");
        }
        exit();
    } else {
        echo "Kullanıcı adı veya şifre hatalı!";
    }
}

include 'includes/header.php';
?>

<form method="post">
    E-Posta: <input type="email" name="email" required><br>
    Şifre: <input type="password" name="password" required><br>
    <button type="submit">Giriş Yap</button>
</form>

<?php include 'includes/footer.php'; ?>
