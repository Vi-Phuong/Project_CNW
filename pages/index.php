<?php
require_once __DIR__ . '/../includes/init.php';

// Nếu đã login, đi đến dashboard; không thì đi đến login
if (isLoggedIn()) {
    header('Location: dashboard.php');
} else {
    header('Location: Trangdangnhap.php');
}
exit;

?>
