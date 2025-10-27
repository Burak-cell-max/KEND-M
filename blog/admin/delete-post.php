<?php
session_start();
if (!isset($_SESSION["logged_in"])) {
    header("Location: login.php");
    exit;
}

$is_admin = true;
include "../includes/header.php";

// Yazıları oku
$json = file_get_contents("../data/posts.json");
$posts = json_decode($json, true) ?? [];

// Silme işlemi
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete"])) {
    $id = (int)$_POST["delete"];
    foreach ($posts as $key => $post) {
        if ($post["id"] === $id) {
            // Görseli sil
            if (!empty($post["featured_image"])) {
                $image_path = "../uploads/images/" . $post["featured_image"];
                if (file_exists($image_path)) {
                    unlink($image_path);
                }
            }
            // Yazıyı diziden kaldır
            unset($posts[$key]);
            break;
        }
    }
    // Diziyi yeniden indexle
    $posts = array_values($posts);
    // JSON'a kaydet
    file_put_contents("../data/posts.json", json_encode($posts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    $_SESSION["success_message"] = "Yazı başarıyla silindi!";
    header("Location: delete-post.php");
    exit;
}

// Düzenleme işlemi
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update"])) {
    $id = (int)$_POST["post_id"];
    $title = trim($_POST["title"]);
    $content = trim($_POST["content"]);
    $excerpt = trim($_POST["excerpt"]);
    $category = trim($_POST["category"]);

    foreach ($posts as &$post) {
        if ($post["id"] === $id) {
            $post["title"] = $title;
            $post["content"] = $content;
            $post["excerpt"] = $excerpt;
            $post["category"] = $category;
            
            // Yeni görsel yüklendiyse
            if (isset($_FILES["featured_image"]) && $_FILES["featured_image"]["error"] == 0) {
                $allowed = ["jpg" => "image/jpeg", "jpeg" => "image/jpeg", "png" => "image/png", "gif" => "image/gif"];
                $filename = $_FILES["featured_image"]["name"];
                $filetype = $_FILES["featured_image"]["type"];
                $filesize = $_FILES["featured_image"]["size"];

                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                if (!array_key_exists($ext, $allowed)) {
                    $_SESSION["error_message"] = "Geçersiz dosya formatı!";
                    break;
                }

                if ($filesize > 5 * 1024 * 1024) {
                    $_SESSION["error_message"] = "Dosya boyutu çok büyük!";
                    break;
                }

                if (in_array($filetype, $allowed)) {
                    // Eski görseli sil
                    if (!empty($post["featured_image"])) {
                        $old_image = "../uploads/images/" . $post["featured_image"];
                        if (file_exists($old_image)) {
                            unlink($old_image);
                        }
                    }
                    
                    // Yeni görseli yükle
                    $newname = uniqid() . "." . $ext;
                    if (move_uploaded_file($_FILES["featured_image"]["tmp_name"], "../uploads/images/" . $newname)) {
                        $post["featured_image"] = $newname;
                    }
                }
            }
            break;
        }
    }
    
    // JSON'a kaydet
    file_put_contents("../data/posts.json", json_encode($posts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    $_SESSION["success_message"] = "Yazı başarıyla güncellendi!";
    header("Location: delete-post.php");
    exit;
}

// Kategorileri oku
$categories_json = file_get_contents("../data/categories.json");
$categories = json_decode($categories_json, true) ?? [];
?>

<div class="post-management">
    <h2>📝 Yazıları Yönet</h2>
    
    <?php if (isset($_SESSION["success_message"])): ?>
        <div class="alert alert-success">
            <?php 
            echo $_SESSION["success_message"];
            unset($_SESSION["success_message"]); 
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION["error_message"])): ?>
        <div class="alert alert-danger">
            <?php 
            echo $_SESSION["error_message"];
            unset($_SESSION["error_message"]); 
            ?>
        </div>
    <?php endif; ?>

    <div class="posts-list">
        <?php foreach ($posts as $post): ?>
            <div class="post-item">
                <div class="post-info">
                    <h3><?php echo htmlspecialchars($post["title"]); ?></h3>
                    <?php if (!empty($post["featured_image"])): ?>
                        <img src="/blog/uploads/images/<?php echo htmlspecialchars($post["featured_image"]); ?>" 
                             alt="<?php echo htmlspecialchars($post["title"]); ?>" 
                             class="post-thumb">
                    <?php endif; ?>
                    <p class="excerpt"><?php echo htmlspecialchars($post["excerpt"] ?? substr($post["content"], 0, 150) . "..."); ?></p>
                </div>
                
                <div class="post-actions">
                    <button class="btn-edit" onclick="editPost(<?php echo htmlspecialchars(json_encode($post)); ?>)">
                        ✏️ Düzenle
                    </button>
                    
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="delete" value="<?php echo $post["id"]; ?>">
                        <button type="submit" class="btn-delete" 
                                onclick="return confirm('Bu yazıyı silmek istediğinizden emin misiniz?')">
                            🗑️ Sil
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Düzenleme Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h3>Yazıyı Düzenle</h3>
        
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="post_id" id="edit_post_id">
            
            <div class="form-group">
                <label for="edit_title">Başlık</label>
                <input type="text" id="edit_title" name="title" required>
            </div>

            <div class="form-group">
                <label for="edit_category">Kategori</label>
                <select id="edit_category" name="category" required>
                    <option value="">Kategori Seçin</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo htmlspecialchars($category["name"]); ?>">
                            <?php echo htmlspecialchars($category["name"]); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="edit_excerpt">Özet</label>
                <textarea id="edit_excerpt" name="excerpt" rows="3" required></textarea>
            </div>

            <div class="form-group">
                <label for="edit_featured_image">Yeni Görsel (Opsiyonel)</label>
                <input type="file" id="edit_featured_image" name="featured_image" accept="image/*">
                <small>Mevcut görseli değiştirmek istiyorsanız yeni görsel yükleyin</small>
            </div>

            <div class="form-group">
                <label for="edit_content">İçerik</label>
                <textarea id="edit_content" name="content" rows="15" required></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" name="update" class="btn-primary">💾 Değişiklikleri Kaydet</button>
                <button type="button" class="btn-secondary" onclick="closeModal()">İptal</button>
            </div>
        </form>
    </div>
</div>

<style>
/* Modal styles */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.8);
    z-index: 1000;
}

.modal-content {
    position: relative;
    background-color: #1a1a1a;
    margin: 5% auto;
    padding: 30px;
    width: 80%;
    max-width: 800px;
    border-radius: 12px;
    border: 1px solid #00ffff;
    box-shadow: 0 0 30px rgba(0, 255, 255, 0.2);
}

.close {
    position: absolute;
    right: 20px;
    top: 15px;
    color: #00ffff;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.close:hover {
    color: #fff;
}

/* Post management styles */
.post-management {
    max-width: 1000px;
    margin: 40px auto;
    padding: 20px;
}

.post-item {
    background-color: rgba(255,255,255,0.05);
    margin-bottom: 20px;
    padding: 20px;
    border-radius: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
}

.post-info {
    flex: 1;
}

.post-info h3 {
    color: #00ffff;
    margin: 0 0 10px 0;
}

.post-thumb {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 5px;
    margin: 10px 0;
}

.post-actions {
    display: flex;
    gap: 10px;
}

.btn-edit, .btn-delete {
    padding: 8px 15px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-family: 'Orbitron', sans-serif;
    font-size: 14px;
    transition: all 0.3s ease;
}

.btn-edit {
    background-color: #00ffff;
    color: #000;
}

.btn-delete {
    background-color: #ff4444;
    color: #fff;
}

.btn-edit:hover, .btn-delete:hover {
    transform: scale(1.05);
    opacity: 0.9;
}

.excerpt {
    color: #888;
    margin: 10px 0;
    font-size: 14px;
}
</style>

<script>
function editPost(post) {
    document.getElementById('edit_post_id').value = post.id;
    document.getElementById('edit_title').value = post.title;
    document.getElementById('edit_category').value = post.category;
    document.getElementById('edit_excerpt').value = post.excerpt || '';
    document.getElementById('edit_content').value = post.content;
    
    document.getElementById('editModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('editModal').style.display = 'none';
}

// Modal dışına tıklandığında kapat
window.onclick = function(event) {
    let modal = document.getElementById('editModal');
    if (event.target == modal) {
        modal.style.display = "none";
    }
}

// ESC tuşu ile modalı kapat
document.addEventListener('keydown', function(event) {
    if (event.key === "Escape") {
        document.getElementById('editModal').style.display = 'none';
    }
});
</script>

<?php include "../includes/footer.php"; ?>