<?php
/**
 * Access Control List (ACL)
 * Check quyền dựa trên role (4 cấp: Observer=1, Contributor=2, Moderator=3, Owner=4)
 */

/**
 * Các cấp độ quyền (role_id)
 */
define('ROLE_OBSERVER', 1);
define('ROLE_CONTRIBUTOR', 2);
define('ROLE_MODERATOR', 3);
define('ROLE_OWNER', 4);

/**
 * Check if user has access to an action in a project
 * @param int $user_id
 * @param int $project_id
 * @param string $action (view, add_note, edit_note, delete_note, change_status, manage_members, delete_project)
 * @return bool
 */
function hasAccess($user_id, $project_id, $action) {
    global $pdo;
    
    // Lấy role của user trong project
    $stmt = $pdo->prepare("SELECT role FROM project_members WHERE user_id = ? AND project_id = ?");
    $stmt->execute([$user_id, $project_id]);
    $role = $stmt->fetchColumn();
    
    if (!$role) return false; // Không phải member
    
    // Quy tắc quyền (dựa trên spec 4 role)
    $permissions = [
        'view' => [ROLE_OBSERVER, ROLE_CONTRIBUTOR, ROLE_MODERATOR, ROLE_OWNER],
        'add_note' => [ROLE_CONTRIBUTOR, ROLE_MODERATOR, ROLE_OWNER],
        'edit_note' => [ROLE_CONTRIBUTOR, ROLE_MODERATOR, ROLE_OWNER], // Contributor chỉ edit own
        'delete_note' => [ROLE_MODERATOR, ROLE_OWNER], // Moderator delete any, Contributor delete own
        'change_status' => [ROLE_MODERATOR, ROLE_OWNER],
        'manage_members' => [ROLE_OWNER],
        'delete_project' => [ROLE_OWNER]
    ];
    
    return in_array($role, $permissions[$action] ?? []);
}

/**
 * Check if user can edit a note (owner or higher role)
 * @param int $user_id
 * @param int $note_id
 * @return bool
 */
function canEditNote($user_id, $note_id) {
    global $pdo;
    
    // Lấy author_id của note
    $stmt = $pdo->prepare("SELECT author_id, project_id FROM notes WHERE id = ?");
    $stmt->execute([$note_id]);
    $note = $stmt->fetch();
    
    if (!$note) return false;
    
    // Nếu là author, có thể edit
    if ($note['author_id'] == $user_id) return true;
    
    // Nếu là Moderator hoặc Owner, có thể edit
    return hasAccess($user_id, $note['project_id'], 'edit_note') && 
           getInRole($user_id, $note['project_id']) >= ROLE_MODERATOR;
}

/**
 * Check if user can delete a note
 * @param int $user_id
 * @param int $note_id
 * @return bool
 */
function canDeleteNote($user_id, $note_id) {
    global $pdo;
    
    // Lấy author_id và project_id của note
    $stmt = $pdo->prepare("SELECT author_id, project_id FROM notes WHERE id = ?");
    $stmt->execute([$note_id]);
    $note = $stmt->fetch();
    
    if (!$note) return false;
    
    // Moderator hoặc Owner có thể delete
    return hasAccess($user_id, $note['project_id'], 'delete_note');
}

/**
 * Get user's role in a project
 * @param int $user_id
 * @param int $project_id
 * @return int|null (role ID or null if not member)
 */
function getInRole($user_id, $project_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT role FROM project_members WHERE user_id = ? AND project_id = ?");
    $stmt->execute([$user_id, $project_id]);
    return $stmt->fetchColumn();
}

/**
 * Get role name from role ID
 * @param int $role_id
 * @return string
 */
function getRoleName($role_id) {
    $roles = [
        ROLE_OBSERVER => 'Observer',
        ROLE_CONTRIBUTOR => 'Contributor',
        ROLE_MODERATOR => 'Moderator',
        ROLE_OWNER => 'Owner'
    ];
    return $roles[$role_id] ?? 'Unknown';
}

?>
