<?php
// Simple ACL helpers using DB (student-style)
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/init.php';

function getUserRole(PDO $pdo, int $user_id, int $project_id): ?int {
    $stmt = $pdo->prepare('SELECT role FROM project_members WHERE user_id = ? AND project_id = ?');
    $stmt->execute([$user_id, $project_id]);
    $r = $stmt->fetch();
    return $r ? (int)$r['role'] : null;
}

function canDoAction(?int $role, string $action, ?int $note_author_id = null, ?int $current_user_id = null): bool {
    // role may be null -> not a member
    if ($role === null) return false;
    // actions: create_note, edit_note, delete_note, change_status
    if ($action === 'create_note') return $role >= 2; // contributor+
    if ($action === 'edit_note' || $action === 'delete_note') {
        if ($role >= 3) return true; // moderator (3) and owner (4)
        if ($role === 2 && $note_author_id !== null && $current_user_id !== null && $note_author_id === $current_user_id) return true; // contributor only own
        return false;
    }
    if ($action === 'change_status') return $role === 4; // only owner
    return false;
}

?>
