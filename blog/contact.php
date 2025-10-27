<?php include("includes/header.php"); ?>

<main>
  <section class="contact">
    <h2>📬 Bana Ulaş</h2>
    <form id="contactForm" method="POST" action="">
      <input type="text" name="name" placeholder="Adınız" required>
      <input type="email" name="email" placeholder="E-posta" required>
      <textarea name="message" placeholder="Mesajınız" rows="6" required></textarea>
      <button type="submit">Gönder</button>
    </form>

    <?php
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
      $name = htmlspecialchars($_POST["name"]);
      $email = htmlspecialchars($_POST["email"]);
      $message = htmlspecialchars($_POST["message"]);

      $log = "data/messages.txt";
      $entry = "Ad: $name\nE-posta: $email\nMesaj: $message\n---\n";
      file_put_contents($log, $entry, FILE_APPEND);

      echo "<p style='color:#00ff99;'>Mesajınız başarıyla gönderildi!</p>";
    }
    ?>
  </section>
</main>

<?php include("includes/footer.php"); ?>