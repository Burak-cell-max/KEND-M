

<?php
session_start();
if (!isset($_SESSION["logged_in"])) {
  header("Location: login.php");
  exit;
}

$is_admin = true;
include "../includes/header.php";
?>

  <div class="dashboard">
    <h2>📋 Admin Paneline Hoş Geldin</h2>
    <a href="new-post.php">📝 Yeni Yazı Ekle</a>
    <a href="delete-post.php">✏️ Yazıları Yönet</a>
    <a href="../posts.php">📄 Yazıları Görüntüle</a>
    <a href="../index.php">🏠 Siteye Dön</a>
    <a href="logout.php">🚪 Çıkış Yap</a>
  </div>

<?php include "../includes/footer.php"; ?>

