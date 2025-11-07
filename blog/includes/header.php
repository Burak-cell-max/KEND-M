<?php
// Ensure a session is available for pages that include this header
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <title><?php echo isset($page_title) ? htmlspecialchars($page_title . ' — kreatixcode') : 'kreatixcode'; ?></title>
  <link rel="icon" href="/blog/assets/favicon.svg" type="image/svg+xml">
  <link rel="stylesheet" href="/blog/assets/style.css">
  <script src="/blog/assets/script.js" defer></script>
</head>
<body>
  <header>
    <div class="header-top">
      <div class="search-top">
        <?php if (empty($is_admin)): ?>
          <form action="/blog/search.php" method="GET" class="search-box">
            <input type="text" name="q" placeholder="Ara..." required>
            <button type="submit" class="search-button">
              <svg class="vintage-search" viewBox="0 0 24 24" width="24" height="24">
                <path d="M15.5 14h-.79l-.28-.27a6.5 6.5 0 0 0 1.48-5.34c-.47-2.78-2.79-5-5.59-5.34a6.505 6.505 0 0 0-7.27 7.27c.34 2.8 2.56 5.12 5.34 5.59a6.5 6.5 0 0 0 5.34-1.48l.27.28v.79l4.25 4.25c.41.41 1.08.41 1.49 0 .41-.41.41-1.08 0-1.49L15.5 14zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
              </svg>
            </button>
          </form>
        <?php endif; ?>
      </div>
      
      <div class="auth-buttons">
        <?php if (!empty($is_admin)): ?>
          <a href="/blog/admin/dashboard.php" class="btn">Admin</a>
          <a href="/blog/admin/logout.php" class="btn">Çıkış</a>
        <?php elseif (!empty($_SESSION["user_logged_in"])): ?>
          <div class="account-wrapper">
            <button class="btn btn-outline account-btn" id="accountBtn">HESABIM ▾</button>
            <div class="account-dropdown" id="accountDropdown" aria-hidden="true">
              <div class="account-card">
                <?php if (!empty($_SESSION['user_avatar'])): ?>
                  <div class="account-avatar">
                    <img src="/blog/uploads/images/avatars/<?php echo htmlspecialchars($_SESSION['user_avatar']); ?>" alt="avatar">
                  </div>
                <?php else: ?>
                  <div class="account-avatar">
                    <?php echo strtoupper(substr(htmlspecialchars($_SESSION["username"]), 0, 1)); ?>
                  </div>
                <?php endif; ?>
                <div class="account-info">
                  <strong><?php echo htmlspecialchars($_SESSION["username"]); ?></strong>
                  <small><?php echo isset($_SESSION["user_email"]) ? htmlspecialchars($_SESSION["user_email"]) : ''; ?></small>
                </div>
              </div>
              <ul class="account-actions">
                <li><a href="/blog/user/account.php#center">Hesap Merkezi</a></li>
                <li><a href="/blog/user/account.php#profile">Profilim</a></li>
                <li><a href="/blog/user/account.php#settings">Hesap Ayarları</a></li>
                <li><a href="/blog/user/account.php#password">Şifre Yenileme</a></li>
                <li class="divider"></li>
                <li><a href="/blog/user/logout.php">Hesaptan Çıkış</a></li>
              </ul>
            </div>
          </div>
        <?php else: ?>
          <a href="/blog/user/login.php" class="btn btn-outline">Giriş</a>
          <a href="/blog/user/register.php" class="btn btn-primary">Üye Ol</a>
        <?php endif; ?>
      </div>
    </div>

    <div class="top-bar">
      <a href="/blog/index.php" class="site-title" aria-label="Ana Sayfa"> <h1>💻 kreatixcode</h1> </a>
    </div>

    <?php if (empty($is_admin)): ?>
      <nav>
        <a href="/blog/index.php">Ana Sayfa</a>
        <a href="/blog/about.php">Hakkımda</a>
        <a href="/blog/portfolio.php">Portfolyo</a>
        <a href="/blog/posts.php">Yazılar</a>
        <a href="/blog/contact.php">İletişim</a>
      </nav>
    <?php endif; ?>
  </header>