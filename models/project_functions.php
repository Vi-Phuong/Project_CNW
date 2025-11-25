<?php
/**
 * Project Functions
 * CRUD operations cho projects
 */

/**
 * Get project by ID
 * @param int $project_id
 * @return array|null
 */
function getProjectById($project_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
    $stmt->execute([$project_id]);
    return $stmt->fetch();
}

/**
 * Get all projects of a user
 * @param int $user_id
 * @return array
 */
function getProjectsByUser($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT p.* FROM projects p 
        JOIN project_members pm ON p.id = pm.project_id 
        WHERE pm.user_id = ? 
        ORDER BY p.created_at DESC
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

/**
 * Get all projects (for admin)
 * @return array
 */
function getAllProjects() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM projects ORDER BY created_at DESC");
    return $stmt->fetchAll();
}

/**
 * Create new project
 * @param string $title
 * @param string $description
 * @param int $owner_id
 * @return int|false (project_id or false on error)
 */
function createProject($title, $description, $owner_id) {
    global $pdo;
    
    try {
        // Insert project
        $stmt = $pdo->prepare("INSERT INTO projects (title, description, owner_id) VALUES (?, ?, ?)");
        $stmt->execute([sanitize($title), sanitize($description), $owner_id]);
        $project_id = $pdo->lastInsertId();
        
        // Add owner as member (role 4 = Owner)
        $stmt2 = $pdo->prepare("INSERT INTO project_members (project_id, user_id, role) VALUES (?, ?, ?)");
        $stmt2->execute([$project_id, $owner_id, ROLE_OWNER]);
        
        return $project_id;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Update project
 * @param int $project_id
 * @param string $title
 * @param string $description
 * @return bool
 */
function updateProject($project_id, $title, $description) {
    global $pdo;
    
    $stmt = $pdo->prepare("UPDATE projects SET title = ?, description = ? WHERE id = ?");
    return $stmt->execute([sanitize($title), sanitize($description), $project_id]);
}

/**
 * Delete project (cascade)
 * @param int $project_id
 * @return bool
 */
function deleteProject($project_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
        return $stmt->execute([$project_id]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Get project members
 * @param int $project_id
 * @return array
 */
function getProjectMembers($project_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT u.*, u.id as user_id, pm.role, pm.id as member_id FROM users u 
        JOIN project_members pm ON u.id = pm.user_id 
        WHERE pm.project_id = ? 
        ORDER BY u.name ASC
    ");
    $stmt->execute([$project_id]);
    return $stmt->fetchAll();
}

/**
 * Add member to project
 * @param int $project_id
 * @param int $user_id
 * @param int $role (1=Observer, 2=Contributor, 3=Moderator, 4=Owner)
 * @return bool
 */
function addProjectMember($project_id, $user_id, $role = ROLE_CONTRIBUTOR) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO project_members (project_id, user_id, role) VALUES (?, ?, ?)");
        return $stmt->execute([$project_id, $user_id, $role]);
    } catch (PDOException $e) {
        return false; // User already member
    }
}

/**
 * Update member role
 * @param int $project_id
 * @param int $user_id
 * @param int $role
 * @return bool
 */
function updateMemberRole($project_id, $user_id, $role) {
    global $pdo;
    
    $stmt = $pdo->prepare("UPDATE project_members SET role = ? WHERE project_id = ? AND user_id = ?");
    return $stmt->execute([$role, $project_id, $user_id]);
}

/**
 * Remove member from project
 * @param int $project_id
 * @param int $user_id
 * @return bool
 */
function removeProjectMember($project_id, $user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("DELETE FROM project_members WHERE project_id = ? AND user_id = ?");
    return $stmt->execute([$project_id, $user_id]);
}

?>
