<?php include("includes/header.php"); ?>

<main>
  <section class="categories">
    <h2>📂 Kategorilere Göre Yazılar</h2>

    <?php
    $json = file_get_contents("data/posts.json");
    $posts = json_decode($json, true);

    // Kategorileri gruplandır
    $grouped = [];
    foreach ($posts as $post) {
      $cat = $post["category"];
      if (!isset($grouped[$cat])) {
        $grouped[$cat] = [];
      }
      $grouped[$cat][] = $post;
    }

    // Her kategori için yazıları göster
    foreach ($grouped as $category => $items) {
      echo "<h3>🗂️ $category</h3>";
      foreach ($items as $item) {
        echo "<article>";
        echo "<h4>{$item['title']}</h4>";
        echo "<p>" . substr($item['content'], 0, 100) . "...</p>";
        echo "<a href='post.php?id={$item['id']}'>Devamını Oku</a>";
        echo "</article>";
      }
    }
    ?>
  </section>
</main>

<?php include("includes/footer.php"); ?>