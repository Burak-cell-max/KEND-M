<?php include("includes/header.php"); ?>

<main>
  <section class="hero">
    <h1>🚀 kreatixcode</h1>
    <p>Yazılım, tasarım ve yaratıcılık burada birleşiyor.</p>
    <a href="portfolio.php" class="btn">Portfolyomu Gör</a>
  </section>

  <section class="latest-posts">
    <h2>📝 Son Yazılar</h2>
    <?php
      $json = file_get_contents("data/posts.json");
      $posts = json_decode($json, true);
      foreach ($posts as $post) {
        echo "<article>";
        echo "<h3>{$post['title']}</h3>";
        echo "<p>" . substr($post['content'], 0, 100) . "...</p>";
        echo "<a href='post.php?id={$post['id']}'>Devamını Oku</a>";
        echo "</article>";
      }
    ?>
  </section>
</main>

<?php include("includes/footer.php"); ?>