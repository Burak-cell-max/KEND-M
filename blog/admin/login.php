
<?php
session_start();
$correct_password = "burakaydin0440";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $password = $_POST["password"];
  if ($password === $correct_password) {
    $_SESSION["logged_in"] = true;
    header("Location: dashboard.php");
    exit;
  } else {
    $error = "Şifre yanlış!";
  }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <title>Admin Girişi</title>
  <link rel="stylesheet" href="../assets/style.css">
  <style>
    body {
      background: linear-gradient(to right, #0f0c29, #302b63, #24243e);
      font-family: 'Orbitron', sans-serif;
      color: #fff;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    .login-box {
      background-color: rgba(255,255,255,0.05);
      padding: 40px;
      border-radius: 10px;
      border: 2px solid #00ffff;
      box-shadow: 0 0 20px #00ffff;
      text-align: center;
      width: 300px;
    }

    .login-box h2 {
      margin-bottom: 20px;
      color: #00ffff;
    }

    .login-box input {
      width: 100%;
      padding: 10px;
      margin-bottom: 15px;
      border: none;
      border-radius: 5px;
      background-color: #222;
      color: #fff;
    }

    .login-box button {
      width: 100%;
      padding: 10px;
      background-color: #00ffff;
      color: #000;
      border: none;
      border-radius: 5px;
      font-weight: bold;
      cursor: pointer;
    }

    .error {
      color: #ff8080;
      margin-top: 10px;
    }
  </style>
</head>
<body>
  <div class="login-box">
    <h2>🔐 Admin Girişi</h2>
    <form method="POST">
      <input type="password" name="password" placeholder="Şifre" required>
      <button type="submit">Giriş Yap</button>
    </form>
    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
  </div>
</body>
</html>