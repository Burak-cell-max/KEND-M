<?php include("includes/header.php"); ?>

<main>
  <section class="portfolio">
    <h2>🚀 Yaptığım İşler</h2>

    <?php
    $projects = [
      [
        "title" => "Fütüristik Blog Tasarımı",
        "desc" => "Neon temalı, PHP tabanlı blog sitesi.",
        "image" => "uploads/images/44.png",
        "link" => "https://github.com/Burak-cell-max"
      ],
      [
        "title" => "Karakter Tasarımı: Nova",
        "desc" => "Hayal gücüyle çizilmiş bir AI karakteri.",
        "image" => "uploads/images/44.png",
        "link" => "https://github.com/Burak-cell-max"
      ],
      [
        "title" => "Bilgi Oyunu Scripti",
        "desc" => "Genel kültür soruları ile eğlenceli oyun.",
        "image" => "uploads/images/44.png",
        "link" => "https://github.com/Burak-cell-max"
      ]
    ];

    foreach ($projects as $project): ?>
      <div class="card">
        <img src="<?php echo $project['image']; ?>" alt="Proje görseli">
        <h3><?php echo $project['title']; ?></h3>
        <p><?php echo $project['desc']; ?></p>
        <a href="<?php echo $project['link']; ?>" target="_blank">İncele</a>
      </div>
    <?php endforeach; ?>
  </section>
</main>

<?php include("includes/footer.php"); ?>