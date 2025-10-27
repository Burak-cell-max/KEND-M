<?php
// Application config — adjust values as needed.
// WARNING: don't commit real credentials to public repos.

// SMTP configuration for PHPMailer. Set 'enable' => true and fill in your provider details.
// If PHPMailer is not installed (composer), the code will fall back to PHP mail().
$config = [
    'smtp' => [
        'enable' => false,           // set true to use PHPMailer SMTP
        'host' => 'smtp.example.com',
        'port' => 587,
        'secure' => 'tls',           // '', 'tls' or 'ssl'
        'auth' => true,              // whether SMTP requires auth
        'username' => 'user@example.com',
        'password' => 'changeme',
        'from_email' => 'no-reply@example.com',
        'from_name' => 'kreatixcode',
    ],
];

// Note: To use PHPMailer, install via composer in your project root:
//   composer require phpmailer/phpmailer
// Then ensure your web server's PHP uses the same PHP binary or include Composer's autoload in includes/header.php or the specific file.

return; // this file returns nothing; $config variable will be in scope when included.
