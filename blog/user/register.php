<?php
session_start();
// DEBUG: show errors during development (remove in production)
@ini_set('display_errors', 1);
@ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Zaten giriş yapmışsa ana sayfaya yönlendir
if (isset($_SESSION["user_logged_in"])) {
    header("Location: /blog/index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $password_confirm = $_POST["password_confirm"];
    
    $errors = [];
    
    // Kullanıcı adı kontrolü
    if (strlen($username) < 3) {
        $errors[] = "Kullanıcı adı en az 3 karakter olmalıdır.";
    }
    
    // Email kontrolü
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Geçerli bir email adresi giriniz.";
    }
    
    // Şifre kontrolü
    if (strlen($password) < 6) {
        $errors[] = "Şifre en az 6 karakter olmalıdır.";
    }
    
    if ($password !== $password_confirm) {
        $errors[] = "Şifreler eşleşmiyor.";
    }
    
    // Hata yoksa devam et
    if (empty($errors)) {
        // JSON dosyasından mevcut kullanıcıları oku
        $users_json = file_get_contents("../data/users.json");
        $users = json_decode($users_json, true) ?? [];
        
        // Kullanıcı adı veya email kullanımda mı kontrol et
        foreach ($users as $user) {
            if ($user["username"] === $username) {
                $errors[] = "Bu kullanıcı adı zaten kullanımda.";
                break;
            }
            if ($user["email"] === $email) {
                $errors[] = "Bu email adresi zaten kullanımda.";
                break;
            }
        }
        
        // Hata yoksa yeni kullanıcıyı ekle
        if (empty($errors)) {
            $new_user = [
                "id" => count($users) + 1,
                "username" => $username,
                "email" => $email,
                "password" => password_hash($password, PASSWORD_DEFAULT),
                "created_at" => date("Y-m-d H:i:s"),
                // default settings
                "public_profile" => "public",
                "email_notifications" => "all",
                "avatar" => ""
            ];
            
            $users[] = $new_user;
            
            // JSON dosyasına kaydet
            $save_result = file_put_contents("../data/users.json", json_encode($users, JSON_PRETTY_PRINT));
            
            if ($save_result === false) {
                $errors[] = "Kullanıcı kaydedilirken bir hata oluştu. Hata: " . error_get_last()['message'];
            } else {
                // Otomatik giriş yap
                $_SESSION["user_logged_in"] = true;
                $_SESSION["username"] = $username;
                $_SESSION["user_email"] = $email;
                $_SESSION["user_id"] = $new_user["id"];
                $_SESSION["user_avatar"] = '';
                header("Location: /blog/index.php");
                exit;
            }
        }
    }
}

$is_user = true;
include "../includes/header.php";
?>

<div class="auth-container">
    <div class="auth-box">
        <h2>✨ Üye Ol</h2>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="auth-form">
            <div class="form-group">
                <label for="username">Kullanıcı Adı</label>
                <input type="text" id="username" name="username" required 
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                <small>En az 3 karakter</small>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="password">Şifre</label>
                <input type="password" id="password" name="password" required>
                <small>En az 6 karakter</small>
            </div>
            
            <div class="form-group">
                <label for="password_confirm">Şifre Tekrar</label>
                <input type="password" id="password_confirm" name="password_confirm" required>
            </div>
            
            <button type="submit" class="btn-primary">🚀 Üye Ol</button>
        </form>
        
        <div class="auth-links">
            <p>Zaten üye misin? <a href="login.php">Giriş Yap</a></p>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>