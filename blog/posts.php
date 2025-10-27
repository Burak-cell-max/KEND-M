<?php include("includes/header.php"); ?>

<main>
  <section class="posts">
    <h2>📝 Tüm Yazılar</h2>

    <?php
    $json = file_get_contents("data/posts.json");
    $posts = json_decode($json, true);

    foreach ($posts as $post) {
      echo "<article>";
      if (!empty($post['featured_image'])) {
        echo "<a href='post.php?id={$post['id']}'><img src='/blog/uploads/images/" . htmlspecialchars($post['featured_image']) . "' class='post-thumb' alt='" . htmlspecialchars($post['title']) . "'></a>";
      }
      echo "<h3>{$post['title']}</h3>";
      echo "<p>" . substr($post['content'], 0, 120) . "...</p>";
      echo "<a href='post.php?id={$post['id']}'>Devamını Oku</a>";
      echo "</article>";
    }
    ?>
  </section>
</main>

<?php include("includes/footer.php"); ?>