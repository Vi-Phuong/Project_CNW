<?php
// Database-backed auth helpers (PHP thuần, student-style)
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/init.php';

function register_db_user(PDO $pdo, string $name, string $email, string $password): bool {
    // check exists
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) return false;

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO users (name,email,password) VALUES (?,?,?)');
    return $stmt->execute([$name, $email, $hash]);
}

function login_db_user(PDO $pdo, string $email, string $password): ?array {
    $stmt = $pdo->prepare('SELECT id,name,password FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $u = $stmt->fetch();
    if (!$u) return null;
    if (password_verify($password, $u['password'])) {
        // set session
        $_SESSION['user_id'] = (int)$u['id'];
        $_SESSION['user_name'] = $u['name'];
        return ['id' => (int)$u['id'], 'name' => $u['name']];
    }
    return null;
}

function current_db_user_id(): ?int {
    return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
}

function logout_db_user(): void {
    unset($_SESSION['user_id']);
    unset($_SESSION['user_name']);
}

?>
