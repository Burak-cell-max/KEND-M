<?php
session_start();
// Kullanıcı girişi kontrolü
if (!isset($_SESSION["logged_in"])) {
    header("Location: login.php");
    exit;
}

// Form gönderildiğinde
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Form verilerini al
    $title = trim($_POST["title"]);
    $content = trim($_POST["content"]);
    $category = trim($_POST["category"]);
    $excerpt = trim($_POST["excerpt"]);
    
    // Görsel yükleme işlemi
    $featured_image = "";
    if (isset($_FILES["featured_image"]) && $_FILES["featured_image"]["error"] == 0) {
        $allowed = ["jpg" => "image/jpeg", "jpeg" => "image/jpeg", "png" => "image/png", "gif" => "image/gif"];
        $filename = $_FILES["featured_image"]["name"];
        $filetype = $_FILES["featured_image"]["type"];
        $filesize = $_FILES["featured_image"]["size"];

        // Dosya uzantısını doğrula
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (!array_key_exists($ext, $allowed)) {
            die("Hata: Lütfen geçerli bir görsel formatı seçin.");
        }

        // Dosya boyutunu kontrol et (5MB max)
        $maxsize = 5 * 1024 * 1024;
        if ($filesize > $maxsize) {
            die("Hata: Görsel boyutu çok büyük.");
        }

        // Dosya türünü doğrula
        if (in_array($filetype, $allowed)) {
            // Dosyayı yükle
            $newname = uniqid() . "." . $ext;
            if (move_uploaded_file($_FILES["featured_image"]["tmp_name"], "../uploads/images/" . $newname)) {
                $featured_image = $newname;
            } else {
                die("Hata: Dosya yüklenirken bir sorun oluştu.");
            }
        }
    }

    // Mevcut yazıları oku
    $json = file_get_contents("../data/posts.json");
    $posts = json_decode($json, true) ?? [];
    
    // Yeni yazı için ID oluştur
    $new_id = count($posts) + 1;
    
    // Yeni yazıyı ekle
    $posts[] = [
        "id" => $new_id,
        "title" => $title,
        "content" => $content,
        "category" => $category,
        "excerpt" => $excerpt,
        "featured_image" => $featured_image,
        "author" => $_SESSION["username"] ?? "Admin",
        "date" => date("Y-m-d H:i:s"),
        "views" => 0
    ];

    // JSON dosyasını güncelle
    $writeResult = file_put_contents(__DIR__ . "/../data/posts.json", json_encode($posts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    if ($writeResult === false) {
        // Yazma hatası
        $_SESSION["error_message"] = "Yazı kaydedilemedi. Lütfen dosya izinlerini kontrol edin.";
        header("Location: new-post.php");
        exit;
    }

    // Başarılı mesajı göster ve yönlendir
    $_SESSION["success_message"] = "Yazı başarıyla eklendi!";
    header("Location: dashboard.php");
    exit;
}

// Kategorileri oku
$categories_json = file_get_contents("../data/categories.json");
$categories = json_decode($categories_json, true) ?? [];

// işaretle: bu sayfa admin arayüzü, header'da site gezinti ögeleri gizlensin
$is_admin = true;
include "../includes/header.php";
?>

<div class="new-post-form">
    <h2>📝 Yeni Yazı Ekle</h2>
    
    <?php if (isset($_SESSION["error_message"])): ?>
        <div class="alert alert-danger">
            <?php 
            echo $_SESSION["error_message"];
            unset($_SESSION["error_message"]); 
            ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="title">Başlık</label>
            <input type="text" id="title" name="title" required>
        </div>

        <div class="form-group">
            <label for="category">Kategori</label>
            <select id="category" name="category" required>
                <option value="">Kategori Seçin</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo htmlspecialchars($category["name"]); ?>">
                        <?php echo htmlspecialchars($category["name"]); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="excerpt">Özet</label>
            <textarea id="excerpt" name="excerpt" rows="3" required></textarea>
            <small>Yazının kısa bir özeti (maksimum 300 karakter)</small>
        </div>

        <div class="form-group">
            <label for="featured_image">Öne Çıkan Görsel</label>
            <input type="file" id="featured_image" name="featured_image" accept="image/*">
            <small>İzin verilen formatlar: JPG, JPEG, PNG, GIF (Maksimum 5MB)</small>
        </div>

        <div class="form-group">
            <label for="content">İçerik</label>
            <textarea id="content" name="content" rows="15" required></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">✨ Yazıyı Yayınla</button>
            <a href="dashboard.php" class="btn-secondary">İptal</a>
        </div>
    </form>
</div>

<?php include "../includes/footer.php"; ?>
