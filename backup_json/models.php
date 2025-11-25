<?php
require_once __DIR__ . '/helpers.php';

define('PROJECTS_FILE', __DIR__ . '/projects.json');
define('NOTES_FILE', __DIR__ . '/notes.json');

function get_projects(): array {
    if (!file_exists(PROJECTS_FILE)) return [];
    $data = json_decode(file_get_contents(PROJECTS_FILE), true);
    return is_array($data) ? $data : [];
}

function save_projects(array $projects): bool {
    return file_put_contents(PROJECTS_FILE, json_encode($projects, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false;
}

function create_project(string $title, string $description, string $owner_id): array {
    $projects = get_projects();
    $proj = [
        'id' => uniqid('p', true),
        'title' => $title,
        'description' => $description,
        'owner_id' => $owner_id,
        'members' => [ ['user_id' => $owner_id, 'role' => 4] ],
        'created_at' => date('c')
    ];
    $projects[] = $proj;
    save_projects($projects);
    return $proj;
}

function find_project(string $project_id): ?array {
    foreach (get_projects() as $p) {
        if ($p['id'] === $project_id) return $p;
    }
    return null;
}

function save_project(array $project): bool {
    $projects = get_projects();
    foreach ($projects as $i => $p) {
        if ($p['id'] === $project['id']) {
            $projects[$i] = $project;
            return save_projects($projects);
        }
    }
    return false;
}

function add_member_to_project(string $project_id, string $user_id, int $role): bool {
    $p = find_project($project_id);
    if (!$p) return false;
    // avoid duplicates
    foreach ($p['members'] as $m) {
        if ($m['user_id'] === $user_id) return false;
    }
    $p['members'][] = ['user_id' => $user_id, 'role' => $role];
    return save_project($p);
}

function get_member_role(string $project_id, string $user_id): ?int {
    $p = find_project($project_id);
    if (!$p) return null;
    foreach ($p['members'] as $m) {
        if ($m['user_id'] === $user_id) return (int)$m['role'];
    }
    return null;
}

function user_has_min_role(string $project_id, string $user_id, int $min_role): bool {
    $r = get_member_role($project_id, $user_id);
    if ($r === null) return false;
    return $r >= $min_role;
}

// Notes
function get_notes(): array {
    if (!file_exists(NOTES_FILE)) return [];
    $data = json_decode(file_get_contents(NOTES_FILE), true);
    return is_array($data) ? $data : [];
}

function save_notes(array $notes): bool {
    return file_put_contents(NOTES_FILE, json_encode($notes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false;
}

function create_note(string $project_id, string $author_id, string $title, string $content): array {
    $notes = get_notes();
    $note = [
        'id' => uniqid('n', true),
        'project_id' => $project_id,
        'author_id' => $author_id,
        'title' => $title,
        'content' => $content,
        'status' => 'dang_cho_xu_ly',
        'created_at' => date('c'),
        'updated_at' => date('c')
    ];
    $notes[] = $note;
    save_notes($notes);
    return $note;
}

function get_notes_by_project(string $project_id): array {
    $out = [];
    foreach (get_notes() as $n) {
        if ($n['project_id'] === $project_id) $out[] = $n;
    }
    return $out;
}

function find_note(string $note_id): ?array {
    foreach (get_notes() as $n) {
        if ($n['id'] === $note_id) return $n;
    }
    return null;
}

function save_note(array $note): bool {
    $notes = get_notes();
    foreach ($notes as $i => $n) {
        if ($n['id'] === $note['id']) {
            $notes[$i] = $note;
            return save_notes($notes);
        }
    }
    return false;
}

function update_note_content(string $note_id, string $user_id, string $title, string $content): bool {
    $note = find_note($note_id);
    if (!$note) return false;
    $project_id = $note['project_id'];
    $role = get_member_role($project_id, $user_id);
    // role 3 (moderator) can edit any, role 4 (owner) can edit, role 2 (contributor) can edit own
    if ($role === 4 || $role === 3 || ($role === 2 && $note['author_id'] === $user_id)) {
        $note['title'] = $title;
        $note['content'] = $content;
        $note['updated_at'] = date('c');
        return save_note($note);
    }
    return false;
}

function delete_note(string $note_id, string $user_id): bool {
    $note = find_note($note_id);
    if (!$note) return false;
    $project_id = $note['project_id'];
    $role = get_member_role($project_id, $user_id);
    if ($role === 4 || $role === 3 || ($role === 2 && $note['author_id'] === $user_id)) {
        $notes = get_notes();
        foreach ($notes as $i => $n) {
            if ($n['id'] === $note_id) {
                array_splice($notes, $i, 1);
                return save_notes($notes);
            }
        }
    }
    return false;
}

function change_note_status(string $note_id, string $user_id, string $new_status): bool {
    $note = find_note($note_id);
    if (!$note) return false;
    $project_id = $note['project_id'];
    $role = get_member_role($project_id, $user_id);
    // only owner (4) can change status
    if ($role === 4) {
        $note['status'] = $new_status;
        $note['updated_at'] = date('c');
        return save_note($note);
    }
    return false;
}

?>