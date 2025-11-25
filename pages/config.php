<?php
/**
 * pages/config.php
 * Cấu hình application-level (paths, constants)
 * Load từ init.php
 */

// App paths
define('APP_URL', 'http://localhost/HoangMyLinh/Project_CNW');
define('APP_PAGES_URL', APP_URL . '/pages');
define('APP_ASSETS_URL', APP_URL . '/assets');

// Pagination
define('NOTES_PER_PAGE', 20);
define('PROJECTS_PER_PAGE', 12);

// Note statuses
define('STATUSES', ['pending', 'confirmed', 'processing', 'resolved']);
define('STATUS_LABELS', [
    'pending' => 'Chờ xử lý',
    'confirmed' => 'Đã xác nhận',
    'processing' => 'Đang xử lý',
    'resolved' => 'Đã giải quyết'
]);

?>
