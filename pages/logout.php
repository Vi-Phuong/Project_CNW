<?php
require_once __DIR__ . '/../includes/auth_db.php';

logout_db_user();

// destroy session entirely
if (session_status() !== PHP_SESSION_NONE) {
    $_SESSION = [];
    session_destroy();
}

header('Location: ../view/Trangdangnhap.php');
exit;

?>