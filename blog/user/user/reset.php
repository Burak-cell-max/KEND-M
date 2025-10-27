<?php
// Friendly redirect: handle accidental double '/user/user/reset.php' requests
$token = isset($_GET['token']) ? trim($_GET['token']) : '';
if ($token !== '') {
    header('Location: /blog/user/reset.php?token=' . rawurlencode($token));
} else {
    header('Location: /blog/user/forgot.php');
}
exit;
