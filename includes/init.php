<?php
/**
 * Application Bootstrap
 * Khởi tạo session, load DB, helpers, ACL
 */

// Session start (safe: được gọi trước khi output)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Load core files
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/acl.php';

// Global app config
define('APP_ROOT', dirname(dirname(__FILE__)));
define('APP_NAME', 'Smart Notes');

?>