<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/auth_db.php';

if (!current_db_user_id()) {
    header('Location: ../view/Trangdangnhap.php');
    exit;
}

$user_id = current_db_user_id();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // simple form
    ?>
    <!doctype html><html lang="vi"><head><meta charset="utf-8"><title>Tạo dự án</title></head><body>
    <h2>Tạo dự án mới</h2>
    <form method="POST">
      <div><label>Tiêu đề: <input name="title" required /></label></div>
      <div><label>Mô tả: <textarea name="description"></textarea></label></div>
      <div><button type="submit">Tạo</button></div>
    </form>
    </body></html>
    <?php
    exit;
}

$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
if ($title === '') { echo 'Thiếu tiêu đề'; exit; }

$stmt = $pdo->prepare('INSERT INTO projects (title,description,owner_id,created_at) VALUES (?,?,?,NOW())');
$stmt->execute([$title, $description, $user_id]);
$project_id = (int)$pdo->lastInsertId();

// Trigger in DB will add owner to project_members; but ensure it's present
$stmt = $pdo->prepare('INSERT IGNORE INTO project_members (project_id,user_id,role,joined_at) VALUES (?,?,4,NOW())');
$stmt->execute([$project_id, $user_id]);

header('Location: ../pages/project.php?id=' . $project_id);
exit;
header('Location: ../pages/project.php?id=' . $project_id);
exit;
