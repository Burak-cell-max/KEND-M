<?php
session_start();
@ini_set('display_errors', 1);
@ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$messages = [];
$errors = [];
$show_reset_url = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ident = trim($_POST['identifier'] ?? ''); // username or email
    if ($ident === '') {
        $errors[] = 'Kullanıcı adı veya email giriniz.';
    } else {
        $users_json = file_get_contents(__DIR__ . "/../data/users.json");
        $users = json_decode($users_json, true) ?? [];
        $found = false;
        foreach ($users as $idx => $u) {
            if (strcasecmp($u['username'], $ident) === 0 || strcasecmp($u['email'], $ident) === 0) {
                $found = true;
                // generate token and expiry (1 hour)
                try {
                    $token = bin2hex(random_bytes(16));
                } catch (Exception $e) {
                    $token = bin2hex(openssl_random_pseudo_bytes(16));
                }
                $expires = time() + 3600; // 1 hour
                $users[$idx]['reset_token'] = $token;
                $users[$idx]['reset_expires'] = $expires;

                // save back
                $save = file_put_contents(__DIR__ . "/../data/users.json", json_encode($users, JSON_PRETTY_PRINT));
                if ($save === false) {
                    $errors[] = 'Sıfırlama isteği oluşturulamadı. Lütfen tekrar deneyin.';
                } else {
                    $resetUrl = sprintf('%s://%s%s/user/reset.php?token=%s',
                        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http',
                        $_SERVER['HTTP_HOST'], rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'), $token
                    );

          // try to email user via PHPMailer/SMTP if configured, otherwise fallback to mail()
          $subject = 'Şifre Sıfırlama İsteği';
          $body = "Merhaba " . $u['username'] . ",\n\nBir şifre sıfırlama talebi alındı. Aşağıdaki linki kullanarak yeni şifre oluşturabilirsiniz:\n\n" . $resetUrl . "\n\nLink 1 saat geçerlidir. Eğer bu isteği siz yapmadıysanız, bu mesajı görmezden gelin.";

          $sent = false;
          // load optional config
          if (file_exists(__DIR__ . '/../includes/config.php')) {
            include_once __DIR__ . '/../includes/config.php';
          }
          // autoload (composer) if available, so PHPMailer classes can be found
          if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
            include_once __DIR__ . '/../vendor/autoload.php';
          }

          // If PHPMailer is installed and config smtp.enable is true, try SMTP
          $pmClass = '\\PHPMailer\\PHPMailer\\PHPMailer';
          if (!empty($config['smtp']['enable']) && class_exists($pmClass)) {
            try {
              // instantiate via variable to avoid static analysis issues when PHPMailer is not present
              $mail = new $pmClass(true);
              $mail->isSMTP();
              $mail->Host = $config['smtp']['host'] ?? 'localhost';
              $mail->SMTPAuth = !empty($config['smtp']['auth']);
              if (!empty($config['smtp']['auth'])) {
                $mail->Username = $config['smtp']['username'] ?? '';
                $mail->Password = $config['smtp']['password'] ?? '';
              }
              $mail->SMTPSecure = $config['smtp']['secure'] ?? '';
              $mail->Port = $config['smtp']['port'] ?? 25;
              $from = $config['smtp']['from_email'] ?? 'no-reply@localhost';
              $fromName = $config['smtp']['from_name'] ?? 'kreatixcode';
              $mail->setFrom($from, $fromName);
              $mail->addAddress($u['email'], $u['username']);
              $mail->Subject = $subject;
              $mail->Body = $body;
              $mail->AltBody = $body;
              $sent = $mail->send();
            } catch (\Exception $e) {
              $sent = false;
            }
          }

          // fallback to PHP mail() if PHPMailer not used or failed
          if (!$sent && !empty($u['email']) && filter_var($u['email'], FILTER_VALIDATE_EMAIL)) {
            $sent = @mail($u['email'], $subject, $body);
          }

          if ($sent) {
            $messages[] = 'Sıfırlama linki e-posta adresinize gönderildi. (Eğer gelmezse spam klasörünü kontrol edin)';
          } else {
            // Mail wasn't sent — show the link for local testing
            $messages[] = 'Sıfırlama linki oluşturuldu. Lokal test için aşağıdaki linki kullanabilirsiniz:';
            $messages[] = $resetUrl;
            // expose for nicer UI (copy button)
            $show_reset_url = $resetUrl;
          }
                }
                break;
            }
        }
        if (!$found) {
            // Do not reveal whether user exists — but for usability we still show a generic message
            $messages[] = 'Eğer bu e-posta adresi veya kullanıcı adı sistemde kayıtlıysa, sıfırlama bilgileri gönderildi.';
        }
    }
}

$is_user = true;
include __DIR__ . "/../includes/header.php";
?>

<div class="auth-container">
  <div class="auth-box">
    <h2>🔒 Şifre Sıfırlama</h2>

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

    <form method="POST" class="auth-form">
      <div class="form-group">
        <label for="identifier">Kullanıcı Adı veya Email</label>
        <input type="text" id="identifier" name="identifier" required value="<?php echo isset($_POST['identifier']) ? htmlspecialchars($_POST['identifier']) : ''; ?>">
      </div>
      <button type="submit" class="btn-primary">Sıfırlama Linki Gönder</button>
    </form>

    <?php if (!empty($show_reset_url)): ?>
      <div style="margin-top:16px; background:rgba(0,0,0,0.06); padding:12px; border-radius:8px;">
        <label style="display:block;color:#00ffff;margin-bottom:8px;">Test reset linki (kopyalayın veya tarayıcıda açın):</label>
        <div style="display:flex;gap:8px;align-items:center;">
          <input id="resetLinkInput" type="text" readonly value="<?php echo htmlspecialchars($show_reset_url); ?>" style="flex:1;padding:8px;border-radius:6px;border:1px solid rgba(0,255,255,0.08);background:rgba(0,0,0,0.12);color:#fff;">
          <button id="copyResetBtn" class="btn-primary" style="padding:8px 12px;">Kopyala</button>
        </div>
        <small id="copyNotice" style="display:block;margin-top:8px;color:rgba(255,255,255,0.7);">Link panoya kopyalandığında size bilgi verilecektir.</small>
      </div>
    <?php endif; ?>

    <div style="margin-top:12px;text-align:center;"><a href="login.php">Giriş sayfasına dön</a></div>

    <script>
      (function(){
        var btn = document.getElementById('copyResetBtn');
        if (!btn) return;
        btn.addEventListener('click', function(e){
          var input = document.getElementById('resetLinkInput');
          var notice = document.getElementById('copyNotice');
          var text = input.value;

          // Try modern Clipboard API first (works on HTTPS or localhost in many browsers)
          if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(function(){
              notice.textContent = 'Kopyalandı! Şimdi tarayıcıya yapıştırabilir veya açabilirsiniz.';
            }).catch(function(err){
              // fallback to execCommand
              tryExecCopy();
            });
          } else {
            tryExecCopy();
          }

          function tryExecCopy() {
            try {
              input.select();
              input.setSelectionRange(0, 99999);
              var ok = document.execCommand('copy');
              if (ok) {
                notice.textContent = 'Kopyalandı! Şimdi tarayıcıya yapıştırabilir veya açabilirsiniz.';
              } else {
                notice.textContent = 'Kopyalama desteklenmiyor. Linki manuel olarak seçip kopyalayın.';
              }
            } catch (err) {
              notice.textContent = 'Kopyalama sırasında hata oluştu; lütfen manuel olarak seçip kopyalayın.';
            }
          }
        });
      })();
    </script>
  </div>
</div>

<?php include __DIR__ . "/../includes/footer.php"; ?>
