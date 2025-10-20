<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-16">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($pageTitle ?? "Şubilet.com") ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand" href="index.php">Şubilet.com</a>
    <div class="ms-auto">
      <?php if (isset($_SESSION['email'])): ?>
        <span class="text-white me-2"><?= htmlspecialchars($_SESSION['name']) ?></span>
        <a href="logout.php" class="btn btn-danger btn-sm">Çıkış Yap</a>
      <?php elseif ($current_page === 'login.php'): ?>
        <a href="register.php" class="btn btn-primary btn-sm">Kayıt ol</a>
      <?php elseif ($current_page === 'register.php'): ?>
        <a href="login.php" class="btn btn-primary btn-sm">Giriş Yap</a>
      <?php else : ?>
        <a href="login.php" class="btn btn-primary btn-sm">Giriş Yap</a>
        <a href="register.php" class="btn btn-primary btn-sm">Kayıt ol</a>
      <?php endif; ?>
    </div>
  </div>
</nav>
