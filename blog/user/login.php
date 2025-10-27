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
    // JSON dosyasından kullanıcıları oku
    $users_json = file_get_contents("../data/users.json");
    $users = json_decode($users_json, true) ?? [];
    
    $username = trim($_POST["username"]);
    $password = $_POST["password"];
    
    // Kullanıcı adı ve şifre kontrolü
    $user_found = false;
    foreach ($users as $user) {
        if ($user["username"] === $username && password_verify($password, $user["password"])) {
            $user_found = true;
            $_SESSION["user_logged_in"] = true;
            $_SESSION["username"] = $username;
            $_SESSION["user_email"] = $user["email"];
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["user_avatar"] = isset($user["avatar"]) ? $user["avatar"] : '';
            
            header("Location: /blog/index.php");
            exit;
        }
    }
    
    if (!$user_found) {
        $error = "Kullanıcı adı veya şifre hatalı!";
    }
}

$is_user = true;
include "../includes/header.php";
?>

<div class="auth-container">
    <div class="auth-box">
        <h2>🔐 Giriş Yap</h2>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="auth-form">
            <div class="form-group">
                <label for="username">Kullanıcı Adı</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Şifre</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn-primary">🚀 Giriş Yap</button>
        </form>
        <div style="text-align:center;margin-top:12px;">
            <a href="forgot.php" style="color:#00ffff;text-decoration:underline;">Şifremi Unuttum?</a>
        </div>
        
        <div class="auth-links">
            <p>Henüz üye değil misin? <a href="register.php">Hemen Üye Ol</a></p>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>