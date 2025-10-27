<?php
session_start();
// DEBUG: show errors during development (remove in production)
@ini_set('display_errors', 1);
@ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION["user_logged_in"])) {
    header("Location: /blog/user/login.php");
    exit;
}

$messages = [];
$errors = [];

// Load users
$users_json = file_get_contents(__DIR__ . "/../data/users.json");
$users = json_decode($users_json, true) ?? [];

// Find current user by id
$current_user = null;
foreach ($users as $idx => $u) {
    if (isset($_SESSION["user_id"]) && $u["id"] == $_SESSION["user_id"]) {
        $current_user = &$users[$idx];
        break;
    }
}

if ($current_user === null) {
    $errors[] = "Kullanıcı bulunamadı.";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Change password
    if (isset($_POST['action']) && $_POST['action'] === 'change_password') {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['new_password_confirm'] ?? '';

        if (strlen($new) < 6) {
            $errors[] = "Yeni şifre en az 6 karakter olmalıdır.";
        }
        if ($new !== $confirm) {
            $errors[] = "Yeni şifreler eşleşmiyor.";
        }

        if (empty($errors)) {
            if (!password_verify($current, $current_user['password'])) {
                $errors[] = "Mevcut şifre hatalı.";
            } else {
                $current_user['password'] = password_hash($new, PASSWORD_DEFAULT);
                // save
                $save = file_put_contents(__DIR__ . "/../data/users.json", json_encode($users, JSON_PRETTY_PRINT));
                if ($save === false) {
                    $errors[] = "Şifre kaydedilemedi.";
                } else {
                    $messages[] = "Şifre başarıyla güncellendi.";
                }
            }
        }
    }

    // Update profile (username/email)
    if (isset($_POST['action']) && $_POST['action'] === 'update_profile') {
        $new_username = trim($_POST['username'] ?? '');
        $new_email = trim($_POST['email'] ?? '');

        if (strlen($new_username) < 3) {
            $errors[] = "Kullanıcı adı en az 3 karakter olmalıdır.";
        }
        if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Geçerli bir email giriniz.";
        }

        // check duplicates
        foreach ($users as $u) {
            if ($u['id'] != $current_user['id']) {
                if ($u['username'] === $new_username) {
                    $errors[] = "Bu kullanıcı adı zaten kullanımda.";
                }
                if ($u['email'] === $new_email) {
                    $errors[] = "Bu email zaten kullanımda.";
                }
            }
        }

  // handle avatar upload if present
  $avatar_error = isset($_FILES['avatar']['error']) ? $_FILES['avatar']['error'] : UPLOAD_ERR_NO_FILE;
  if (empty($errors) && isset($_FILES['avatar']) && $avatar_error !== UPLOAD_ERR_NO_FILE) {
      $file = $_FILES['avatar'];
      if ($file['error'] === UPLOAD_ERR_OK) {
        if ($file['size'] > 2 * 1024 * 1024) {
          $errors[] = "Avatar boyutu en fazla 2MB olabilir.";
        } else {
          $finfo = finfo_open(FILEINFO_MIME_TYPE);
          $mime = finfo_file($finfo, $file['tmp_name']);
          finfo_close($finfo);
          $allowed = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif'
          ];
          if (!isset($allowed[$mime])) {
            $errors[] = "Yalnızca JPG, PNG veya GIF tipinde dosya yükleyebilirsiniz.";
          } else {
            $ext = $allowed[$mime];
            $avatarsDir = __DIR__ . "/../uploads/images/avatars";
            if (!is_dir($avatarsDir)) {
              @mkdir($avatarsDir, 0755, true);
            }
            $newFilename = 'avatar_' . $current_user['id'] . '_' . time() . '.' . $ext;
            $dest = $avatarsDir . '/' . $newFilename;
            if (!move_uploaded_file($file['tmp_name'], $dest)) {
              $errors[] = "Avatar kaydedilemedi.";
            } else {
              // remove old avatar if exists
              if (!empty($current_user['avatar'])) {
                $old = $avatarsDir . '/' . $current_user['avatar'];
                if (is_file($old)) @unlink($old);
              }
              $current_user['avatar'] = $newFilename;
              $_SESSION['user_avatar'] = $newFilename;
            }
          }
        }
      } else {
        $errors[] = "Avatar yüklenirken bir hata oluştu.";
      }
    }

    if (empty($errors)) {
      $current_user['username'] = $new_username;
      $current_user['email'] = $new_email;
      $save = file_put_contents(__DIR__ . "/../data/users.json", json_encode($users, JSON_PRETTY_PRINT));
      if ($save === false) {
        $errors[] = "Profil kaydedilemedi.";
      } else {
        $_SESSION['username'] = $new_username;
        $_SESSION['user_email'] = $new_email;
        $messages[] = "Profil başarıyla güncellendi.";
      }
    }
    }

  // Save settings (public_profile, email_notifications)
  if (isset($_POST['action']) && $_POST['action'] === 'save_settings') {
    $pub = isset($_POST['public_profile']) && $_POST['public_profile'] === 'private' ? 'private' : 'public';
    $notif = in_array($_POST['email_notifications'] ?? '', ['all', 'important', 'none']) ? $_POST['email_notifications'] : 'all';

    // assign to current user
    $current_user['public_profile'] = $pub;
    $current_user['email_notifications'] = $notif;

    $save = file_put_contents(__DIR__ . "/../data/users.json", json_encode($users, JSON_PRETTY_PRINT));
    if ($save === false) {
      $errors[] = "Ayarlar kaydedilemedi.";
    } else {
      $messages[] = "Ayarlar başarıyla kaydedildi.";
    }
  }
}

$is_user = true;
include __DIR__ . "/../includes/header.php";
?>

<div class="auth-container">
  <div class="auth-box" id="center" style="max-width:820px;">
    <h2>Hesabım</h2>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger">
        <?php foreach ($errors as $e): ?>
          <p><?php echo htmlspecialchars($e); ?></p>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($messages)): ?>
      <div class="alert alert-success">
        <?php foreach ($messages as $m): ?>
          <p><?php echo htmlspecialchars($m); ?></p>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div class="account-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start;">
      <div class="account-summary" style="background:rgba(255,255,255,0.02);padding:20px;border-radius:12px;">
        <div style="display:flex;gap:12px;align-items:center;">
          <?php if (!empty($current_user['avatar'])): ?>
            <div class="account-avatar" style="width:72px;height:72px;border-radius:50%;overflow:hidden;">
              <img src="/blog/uploads/images/avatars/<?php echo htmlspecialchars($current_user['avatar']); ?>" alt="avatar">
            </div>
          <?php else: ?>
            <div class="account-avatar" style="width:72px;height:72px;border-radius:50%;background:linear-gradient(45deg,#00ffff,#00ccff);color:#000;display:flex;align-items:center;justify-content:center;font-size:28px;font-weight:bold;">
              <?php echo strtoupper(substr(htmlspecialchars($_SESSION['username']),0,1)); ?>
            </div>
          <?php endif; ?>
          <div>
            <h3 style="margin:0;color:#00ffff"><?php echo htmlspecialchars($_SESSION['username']); ?></h3>
            <p style="margin:4px 0;color:rgba(255,255,255,0.8);"><?php echo htmlspecialchars($current_user['email']); ?></p>
            <small style="color:rgba(255,255,255,0.6);">Üye tarih: <?php echo htmlspecialchars($current_user['created_at']); ?></small>
          </div>
        </div>
      </div>

      <div>
        <form method="POST" class="auth-form" id="profileForm">
          <a id="profile"></a>
          <input type="hidden" name="action" value="update_profile">
          <div class="form-group">
            <label for="username">Kullanıcı Adı</label>
            <input type="text" id="username" name="username" required value="<?php echo htmlspecialchars($current_user['username']); ?>">
          </div>
          <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($current_user['email']); ?>">
          </div>
          <button type="submit" class="btn-primary">Profili Güncelle</button>
  </form>

        <!-- account settings section (linked from header "Hesap Ayarları") -->
        <div style="height:20px;"></div>
        <div class="auth-form" id="settingsSection" style="margin-bottom:20px;">
          <a id="settings"></a>
          <h3 style="color:#00ffff;margin-top:0;">Hesap Ayarları</h3>
          <p style="color:rgba(255,255,255,0.8);">Buradan bildirim tercihleri, gizlilik ve diğer hesap ayarlarını yönetebilirsiniz.</p>
          <form method="POST" class="settings-form" style="display:block;" enctype="multipart/form-data">
            <input type="hidden" name="action" value="save_settings">
            <div class="form-group">
              <label for="email_notifications">E-posta bildirimleri</label>
              <select id="email_notifications" name="email_notifications">
                <option value="all" <?php echo (!empty($current_user['email_notifications']) && $current_user['email_notifications'] === 'all') ? 'selected' : ''; ?>>Tüm bildirimler</option>
                <option value="important" <?php echo (!empty($current_user['email_notifications']) && $current_user['email_notifications'] === 'important') ? 'selected' : ''; ?>>Sadece önemli</option>
                <option value="none" <?php echo (!empty($current_user['email_notifications']) && $current_user['email_notifications'] === 'none') ? 'selected' : ''; ?>>Hiçbiri</option>
              </select>
            </div>
            <div class="form-group">
              <label for="public_profile">Profil görünürlüğü</label>
              <select id="public_profile" name="public_profile">
                <option value="public" <?php echo (!empty($current_user['public_profile']) && $current_user['public_profile'] === 'public') ? 'selected' : ''; ?>>Herkese açık</option>
                <option value="private" <?php echo (!empty($current_user['public_profile']) && $current_user['public_profile'] === 'private') ? 'selected' : ''; ?>>Sadece ben</option>
              </select>
            </div>
            <div style="margin-top:10px;">
              <button type="submit" class="btn-primary">Ayarları Kaydet</button>
            </div>
          </form>
        </div>

        <form method="POST" class="auth-form" id="passwordForm">
          <a id="password"></a>
          <input type="hidden" name="action" value="change_password">
          <div class="form-group">
            <label for="current_password">Mevcut Şifre</label>
            <input type="password" id="current_password" name="current_password" required>
          </div>
          <div class="form-group">
            <label for="new_password">Yeni Şifre</label>
            <input type="password" id="new_password" name="new_password" required>
          </div>
          <div class="form-group">
            <label for="new_password_confirm">Yeni Şifre Tekrar</label>
            <input type="password" id="new_password_confirm" name="new_password_confirm" required>
          </div>
          <button type="submit" class="btn-primary">Şifreyi Değiştir</button>
        </form>
      </div>
    </div>

  </div>
</div>

<?php include __DIR__ . "/../includes/footer.php"; ?>