<?php
/**
 * Note Functions
 * CRUD operations cho notes
 */

/**
 * Get note by ID
 * @param int $note_id
 * @return array|null
 */
function getNoteById($note_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM notes WHERE id = ?");
    $stmt->execute([$note_id]);
    return $stmt->fetch();
}

/**
 * Get all notes in a project
 * @param int $project_id
 * @param string $order (updated_at DESC, created_at DESC, etc)
 * @return array
 */
function getNotesByProject($project_id, $order = 'updated_at DESC') {
    global $pdo;
    
    // Validate order to prevent SQL injection
    $allowed_orders = ['updated_at DESC', 'updated_at ASC', 'created_at DESC', 'created_at ASC', 'status ASC'];
    $order = in_array($order, $allowed_orders) ? $order : 'updated_at DESC';
    
    $stmt = $pdo->prepare("
        SELECT n.*, u.name as author_name, u.email as author_email 
        FROM notes n 
        JOIN users u ON n.author_id = u.id 
        WHERE n.project_id = ? 
        ORDER BY n.$order
    ");
    $stmt->execute([$project_id]);
    return $stmt->fetchAll();
}

/**
 * Get notes by a specific user in a project
 * @param int $project_id
 * @param int $user_id
 * @return array
 */
function getNotesByUserInProject($project_id, $user_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT * FROM notes 
        WHERE project_id = ? AND author_id = ? 
        ORDER BY updated_at DESC
    ");
    $stmt->execute([$project_id, $user_id]);
    return $stmt->fetchAll();
}

/**
 * Get notes by status
 * @param int $project_id
 * @param string $status (pending, confirmed, processing, resolved)
 * @return array
 */
function getNotesByStatus($project_id, $status) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT n.*, u.name as author_name FROM notes n 
        JOIN users u ON n.author_id = u.id 
        WHERE n.project_id = ? AND n.status = ? 
        ORDER BY n.updated_at DESC
    ");
    $stmt->execute([$project_id, $status]);
    return $stmt->fetchAll();
}

/**
 * Create new note
 * @param int $project_id
 * @param int $author_id
 * @param string $title
 * @param string $content
 * @param string $status (default: pending)
 * @return int|false (note_id or false on error)
 */
function createNote($project_id, $author_id, $title, $content, $status = 'pending') {
    global $pdo;
    
    if (empty($content)) {
        return false; // Content is required
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO notes (project_id, author_id, title, content, status) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$project_id, $author_id, sanitize($title), sanitize($content), $status]);
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Update note
 * @param int $note_id
 * @param string $title
 * @param string $content
 * @return bool
 */
function updateNote($note_id, $title, $content) {
    global $pdo;
    
    if (empty($content)) {
        return false; // Content is required
    }
    
    $stmt = $pdo->prepare("
        UPDATE notes 
        SET title = ?, content = ?, updated_at = NOW() 
        WHERE id = ?
    ");
    return $stmt->execute([sanitize($title), sanitize($content), $note_id]);
}

/**
 * Update note status
 * @param int $note_id
 * @param string $status (pending, confirmed, processing, resolved)
 * @return bool
 */
function updateNoteStatus($note_id, $status) {
    global $pdo;
    
    $valid_statuses = ['pending', 'confirmed', 'processing', 'resolved'];
    if (!in_array($status, $valid_statuses)) {
        return false;
    }
    
    $stmt = $pdo->prepare("
        UPDATE notes 
        SET status = ?, updated_at = NOW() 
        WHERE id = ?
    ");
    return $stmt->execute([$status, $note_id]);
}

/**
 * Delete note
 * @param int $note_id
 * @return bool
 */
function deleteNote($note_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("DELETE FROM notes WHERE id = ?");
        return $stmt->execute([$note_id]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Search notes in project
 * @param int $project_id
 * @param string $query
 * @return array
 */
function searchNotes($project_id, $query) {
    global $pdo;
    
    $search = '%' . sanitize($query) . '%';
    
    $stmt = $pdo->prepare("
        SELECT n.*, u.name as author_name FROM notes n 
        JOIN users u ON n.author_id = u.id 
        WHERE n.project_id = ? AND (n.title LIKE ? OR n.content LIKE ?) 
        ORDER BY n.updated_at DESC
    ");
    $stmt->execute([$project_id, $search, $search]);
    return $stmt->fetchAll();
}

/**
 * Get note count by status in project
 * @param int $project_id
 * @return array (status => count)
 */
function getNoteCountByStatus($project_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT status, COUNT(*) as count FROM notes 
        WHERE project_id = ? 
        GROUP BY status
    ");
    $stmt->execute([$project_id]);
    
    $result = [];
    foreach ($stmt->fetchAll() as $row) {
        $result[$row['status']] = $row['count'];
    }
    
    return $result;
}

?>
