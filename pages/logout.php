<?php
require_once __DIR__ . '/../includes/init.php';

// Destroy session
if (session_status() !== PHP_SESSION_NONE) {
    $_SESSION = [];
    session_destroy();
}

// Redirect đến login
header('Location: Trangdangnhap.php');
exit;

?>