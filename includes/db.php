<?php
/**
 * Database Configuration & Connection
 * Sử dụng PDO với prepared statements để an toàn SQL injection
 */

$host = 'localhost';
$db_name = 'project_cnw'; // Sử dụng schema mới
$user = 'root';
$pass = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db_name;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die('Database connection error: ' . $e->getMessage());
}
?>