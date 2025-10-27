<?php
session_start();
@ini_set('display_errors', 1);
@ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$errors = [];
$messages = [];

$token = trim($_GET['token'] ?? '');
if ($token === '') {
    $errors[] = 'Geçersiz veya eksik token.';
} else {
    // load users and find token
    $users_json = file_get_contents(__DIR__ . "/../data/users.json");
    $users = json_decode($users_json, true) ?? [];
    $found_idx = null;
    foreach ($users as $idx => $u) {
        if (!empty($u['reset_token']) && hash_equals($u['reset_token'], $token)) {
            $found_idx = $idx;
            break;
        }
    }

    if ($found_idx === null) {
        $errors[] = 'Token bulunamadı veya zaten kullanılmış.';
    } else {
        $user = $users[$found_idx];
        if (empty($user['reset_expires']) || time() > (int)$user['reset_expires']) {
            $errors[] = 'Token süresi dolmuş. Lütfen yeniden talep edin.';
        }
    }
}

// handle new password POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($errors)) {
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['new_password_confirm'] ?? '';
    if (strlen($new) < 6) {
        $errors[] = 'Yeni şifre en az 6 karakter olmalıdır.';
    }
    if ($new !== $confirm) {
        $errors[] = 'Yeni şifreler eşleşmiyor.';
    }

    if (empty($errors)) {
        // reload users to avoid race
        $users_json = file_get_contents(__DIR__ . "/../data/users.json");
        $users = json_decode($users_json, true) ?? [];
        $found_idx = null;
        foreach ($users as $idx => $u) {
            if (!empty($u['reset_token']) && hash_equals($u['reset_token'], $token)) {
                $found_idx = $idx;
                break;
            }
        }

        if ($found_idx === null) {
            $errors[] = 'Token bulunamadı veya zaten kullanılmış.';
        } else {
            // set new password, clear reset fields
            $users[$found_idx]['password'] = password_hash($new, PASSWORD_DEFAULT);
            unset($users[$found_idx]['reset_token']);
            unset($users[$found_idx]['reset_expires']);

            $save = file_put_contents(__DIR__ . "/../data/users.json", json_encode($users, JSON_PRETTY_PRINT));
            if ($save === false) {
                $errors[] = 'Şifre kaydedilemedi. Lütfen tekrar deneyin.';
            } else {
                $messages[] = 'Şifreniz başarıyla güncellendi. Giriş sayfasına yönlendiriliyorsunuz.';
                // optional: auto-login user
                $_SESSION['user_logged_in'] = true;
                $_SESSION['username'] = $users[$found_idx]['username'];
                $_SESSION['user_email'] = $users[$found_idx]['email'];
                $_SESSION['user_id'] = $users[$found_idx]['id'];
                $_SESSION['user_avatar'] = isset($users[$found_idx]['avatar']) ? $users[$found_idx]['avatar'] : '';

                // redirect after short delay
                header('Refresh:2; url=login.php');
            }
        }
    }
}

$is_user = true;
include __DIR__ . "/../includes/header.php";
?>

<div class="auth-container">
  <div class="auth-box" style="max-width:520px;">
    <h2 style="display:flex;align-items:center;gap:10px;"><span style="font-size:28px;">🔑</span> Yeni Şifre Oluştur</h2>

    <?php if (!empty($user)): ?>
      <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
        <?php if (!empty($user['avatar'])): ?>
          <img src="/blog/uploads/images/avatars/<?php echo htmlspecialchars($user['avatar']); ?>" alt="avatar" style="width:56px;height:56px;border-radius:50%;object-fit:cover;border:2px solid rgba(0,255,255,0.08)">
        <?php else: ?>
          <div style="width:56px;height:56px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:linear-gradient(45deg,#00ffff,#00ccff);color:#000;font-weight:bold;font-size:20px;">
            <?php echo strtoupper(substr(htmlspecialchars($user['username'] ?? ''),0,1)); ?>
          </div>
        <?php endif; ?>
        <div>
          <strong style="color:#00ffff"><?php echo htmlspecialchars($user['username'] ?? ''); ?></strong>
          <div style="color:rgba(255,255,255,0.7);font-size:13px;"><?php echo htmlspecialchars($user['email'] ?? ''); ?></div>
        </div>
      </div>
    <?php endif; ?>

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

    <?php if (empty($errors) || ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($errors))): ?>
      <form method="POST" class="auth-form" action="?token=<?php echo htmlspecialchars($token); ?>">
        <div class="form-group">
          <label for="new_password">Yeni Şifre</label>
          <div class="pw-wrapper" style="position:relative;">
            <input type="password" id="new_password" name="new_password" required>
            <button type="button" class="btn-outline" id="toggleNew">Göster</button>
          </div>
          <div id="strength" style="height:8px;background:rgba(255,255,255,0.06);border-radius:6px;margin-top:8px;overflow:hidden;"><div id="strengthBar" style="height:100%;width:0;background:#ff4d4f;transition:width 160ms;"></div></div>
        </div>
        <div class="form-group">
          <label for="new_password_confirm">Yeni Şifre Tekrar</label>
          <div class="pw-wrapper" style="position:relative;">
            <input type="password" id="new_password_confirm" name="new_password_confirm" required>
            <button type="button" class="btn-outline" id="toggleConfirm">Göster</button>
          </div>
        </div>
        <button type="submit" class="btn-primary">Şifreyi Yenile</button>
        <div style="margin-top:10px;color:rgba(255,255,255,0.7);font-size:13px;">Şifre en az 6 karakter olmalıdır. Güçlü bir şifre için büyük/küçük harf, rakam ve sembol ekleyin.</div>
      </form>
    <?php endif; ?>

    <div style="margin-top:12px;text-align:center;"><a href="login.php">Giriş sayfasına dön</a></div>
  </div>
</div>

<?php include __DIR__ . "/../includes/footer.php"; ?>

<script>
  (function(){
    var pw = document.getElementById('new_password');
    var pwc = document.getElementById('new_password_confirm');
    var t1 = document.getElementById('toggleNew');
    var t2 = document.getElementById('toggleConfirm');
    var bar = document.getElementById('strengthBar');
    function strengthScore(s){
      var score = 0;
      if (!s) return 0;
      if (s.length >= 6) score += 1;
      if (/[A-Z]/.test(s)) score += 1;
      if (/[0-9]/.test(s)) score += 1;
      if (/[^A-Za-z0-9]/.test(s)) score += 1;
      return score; // 0-4
    }
    function updateBar(){
      var v = strengthScore(pw.value);
      var pct = (v/4)*100;
      bar.style.width = pct + '%';
      if (v <= 1) bar.style.background = '#ff4d4f';
      else if (v === 2) bar.style.background = '#ffb84d';
      else if (v === 3) bar.style.background = '#c3ff4d';
      else bar.style.background = '#4dff8a';
    }
    if (pw){
      pw.addEventListener('input', updateBar);
    }
    function toggle(el){
      if (el.nextElementSibling && el.nextElementSibling.tagName === 'INPUT') return;
    }
    if (t1){
      t1.addEventListener('click', function(){ pw.type = pw.type === 'password' ? 'text' : 'password'; t1.textContent = pw.type === 'password' ? 'Göster' : 'Gizle'; });
    }
    if (t2){
      t2.addEventListener('click', function(){ pwc.type = pwc.type === 'password' ? 'text' : 'password'; t2.textContent = pwc.type === 'password' ? 'Göster' : 'Gizle'; });
    }
  })();
</script>
