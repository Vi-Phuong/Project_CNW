<?php
/**
 * User Functions
 * CRUD operations cho users
 */

/**
 * Get user by email
 * @param string $email
 * @return array|null
 */
function getUserByEmail($email) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([sanitize($email)]);
    return $stmt->fetch();
}

/**
 * Get user by ID
 * @param int $user_id
 * @return array|null
 */
function getUserById($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

/**
 * Create new user (register)
 * @param string $email
 * @param string $password (plaintext – sẽ hash)
 * @param string $name
 * @return int|false (user_id or false on error)
 */
function createUser($email, $password, $name) {
    global $pdo;
    
    // Check email already exists
    if (getUserByEmail($email)) {
        return false; // Email đã tồn tại
    }
    
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO users (email, password, name) VALUES (?, ?, ?)");
        $stmt->execute([sanitize($email), $hashed, sanitize($name)]);
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Verify login (email + password)
 * @param string $email
 * @param string $password
 * @return array|null (user data if valid)
 */
function verifyLogin($email, $password) {
    $user = getUserByEmail($email);
    
    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    
    return null;
}

/**
 * Update user profile
 * @param int $user_id
 * @param string $name
 * @param string $email (optional)
 * @return bool
 */
function updateUserProfile($user_id, $name, $email = null) {
    global $pdo;
    
    if ($email && $email !== getUserById($user_id)['email']) {
        // Check email already exists (and not current user)
        if (getUserByEmail($email)) {
            return false;
        }
        $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        return $stmt->execute([sanitize($name), sanitize($email), $user_id]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET name = ? WHERE id = ?");
        return $stmt->execute([sanitize($name), $user_id]);
    }
}

/**
 * Change password
 * @param int $user_id
 * @param string $old_password
 * @param string $new_password
 * @return bool
 */
function changePassword($user_id, $old_password, $new_password) {
    $user = getUserById($user_id);
    
    if (!$user || !password_verify($old_password, $user['password'])) {
        return false; // Mật khẩu cũ không đúng
    }
    
    global $pdo;
    $hashed = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    return $stmt->execute([$hashed, $user_id]);
}

?>
