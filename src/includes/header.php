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
  <style>
    .seat-plan-container {
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 20px;
      border: 1px solid #ddd;
      border-radius: 8px;
      background-color: #f9f9f9;
      max-width: 300px;
      /* 2+1 düzen için daralttık */
      margin: 0 auto;
    }

    .bus-front {
      font-weight: bold;
      color: #555;
      border: 2px solid #555;
      padding: 5px 20px;
      border-radius: 5px;
      margin-bottom: 25px;
    }

    .seat-row {
      display: flex;
      justify-content: center;
      margin-bottom: 10px;
    }

    .seat {
      width: 40px;
      height: 40px;
      margin: 5px;
      border-radius: 7px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
      color: #333;
      background-color: #c8e6c9;
      border: 1px solid #a5d6a7;
      cursor: pointer;
    }

    .seat.aisle {
      background: none;
      border: none;
      cursor: default;
      visibility: hidden;
    }

    .seat.taken {
      background-color: #ef9a9a;
      border-color: #e57373;
      cursor: not-allowed;
    }

    .seat.selected {
      background-color: #64b5f6;
      border-color: #42a5f5;
      color: white;
    }
  </style>
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
        <?php else: ?>
          <a href="login.php" class="btn btn-primary btn-sm">Giriş Yap</a>
          <a href="register.php" class="btn btn-primary btn-sm">Kayıt ol</a>
        <?php endif; ?>
      </div>
    </div>
  </nav>