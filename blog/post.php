<?php
include("includes/header.php");

// URL'den gelen id'yi al
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// JSON dosyasından yazıları al
$json = file_get_contents("data/posts.json");
$posts = json_decode($json, true);

// İlgili yazıyı bul
$post = null;
foreach ($posts as $p) {
  if ($p['id'] === $id) {
    $post = $p;
    break;
  }
}

// Yazı bulunamadıysa varsayılan mesaj
if (!$post) {
  $post = [
    "title" => "Yazı Bulunamadı",
    "content" => "Üzgünüz, böyle bir yazı yok."
  ];
}
?>

<main>
  <section class="post-detail">
    <h2><?php echo $post['title']; ?></h2>
    <?php if (!empty($post['featured_image'])): ?>
      <img src="/blog/uploads/images/<?php echo htmlspecialchars($post['featured_image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" class="featured-image">
    <?php endif; ?>
    <p><?php echo nl2br($post['content']); ?></p>
    <a href="posts.php" class="btn">← Tüm Yazılar</a>
  </section>
</main>

<?php include("includes/footer.php"); ?>