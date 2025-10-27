<?php include("includes/header.php"); ?>

<main>
  <section class="search">
    <h2>🔍 Yazı Ara</h2>
    <form method="GET">
      <input type="text" name="q" placeholder="Anahtar kelime..." value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>" required>
      <button type="submit">Ara</button>
    </form>

    <?php
    if (isset($_GET['q'])) {
      $query = strtolower(trim($_GET['q']));
      $json = file_get_contents("data/posts.json");
      $posts = json_decode($json, true);
      $results = [];

      foreach ($posts as $post) {
        if (strpos(strtolower($post['title']), $query) !== false) {
          $results[] = $post;
        }
      }

      echo "<h3>🔎 Sonuçlar:</h3>";
      if (count($results) > 0) {
        foreach ($results as $post) {
          echo "<article>";
          echo "<h4>{$post['title']}</h4>";
          echo "<p>" . substr($post['content'], 0, 100) . "...</p>";
          echo "<a href='post.php?id={$post['id']}'>Devamını Oku</a>";
          echo "</article>";
        }
      } else {
        echo "<p style='color:#ff8080;'>Sonuç bulunamadı.</p>";
      }
    }
    ?>
  </section>
</main>

<?php include("includes/footer.php"); ?>