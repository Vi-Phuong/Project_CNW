<?php
require_once __DIR__ . '/init.php';

// Path to a simple JSON file storing users (for demo purposes).
define('USERS_FILE', __DIR__ . '/users.json');

function get_users(): array {
    if (!file_exists(USERS_FILE)) {
        return [];
    }
    $json = file_get_contents(USERS_FILE);
    $data = json_decode($json, true);
    return is_array($data) ? $data : [];
}

function save_users(array $users): bool {
    $json = json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return file_put_contents(USERS_FILE, $json) !== false;
}

function find_user_by_email(string $email): ?array {
    $users = get_users();
    foreach ($users as $u) {
        if (isset($u['email']) && strtolower($u['email']) === strtolower($email)) {
            return $u;
        }
    }
    return null;
}

function find_user_by_id(string $id): ?array {
    $users = get_users();
    foreach ($users as $u) {
        if (isset($u['id']) && $u['id'] === $id) return $u;
    }
    return null;
}

function get_user_name(string $id): string {
    $u = find_user_by_id($id);
    return $u['name'] ?? 'Người dùng';
}

function register_user(string $name, string $email, string $password): bool {
    if (find_user_by_email($email) !== null) {
        return false; // already exists
    }
    $users = get_users();
    $users[] = [
        'id' => uniqid('u', true),
        'name' => $name,
        'email' => $email,
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'created_at' => date('c')
    ];
    return save_users($users);
}

function verify_user(string $email, string $password): ?array {
    $user = find_user_by_email($email);
    if ($user && isset($user['password']) && password_verify($password, $user['password'])) {
        // don't return password hash
        unset($user['password']);
        return $user;
    }
    return null;
}

function login_user(array $user): void {
    // store minimal user info in session
    $_SESSION['user'] = [
        'id' => $user['id'] ?? null,
        'name' => $user['name'] ?? '',
        'email' => $user['email'] ?? ''
    ];
    // default role per project will be assigned when creating/joining project
}

function logout_user(): void {
    unset($_SESSION['user']);
}

function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}

function require_login(): void {
    if (!current_user()) {
        header('Location: ../view/Trangdangnhap.php');
        exit;
    }
}

?>