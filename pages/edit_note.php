<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/auth_db.php';
require_once __DIR__ . '/../includes/acl.php';

if (!current_db_user_id()) {
    header('Location: ../view/Trangdangnhap.php');
    exit;
}

$user_id = current_db_user_id();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = (int)($_GET['id'] ?? 0);
    $stmt = $pdo->prepare('SELECT * FROM notes WHERE id = ?');
    $stmt->execute([$id]);
    $note = $stmt->fetch();
    if (!$note) { echo 'Ghi chú không tồn tại.'; exit; }

    ?>
    <!doctype html>
    <html lang="vi"><head><meta charset="utf-8"><title>Sửa ghi chú</title></head><body>
    <h2>Sửa ghi chú</h2>
    <form method="POST">
      <input type="hidden" name="id" value="<?= htmlspecialchars($note['id']) ?>" />
      <div><input type="text" name="title" value="<?= htmlspecialchars($note['title']) ?>" placeholder="Tiêu đề" style="width:60%;padding:6px;" /></div>
      <div><textarea name="content" style="width:60%;height:120px;padding:6px;"><?= htmlspecialchars($note['content']) ?></textarea></div>
      <div><button type="submit">Lưu</button></div>
    </form>
    </body></html>
    <?php
    exit;
}

// POST -> update
$id = (int)($_POST['id'] ?? 0);
$title = trim($_POST['title'] ?? '');
$content = trim($_POST['content'] ?? '');

if ($id === 0) { echo 'Thiếu dữ liệu.'; exit; }

$stmt = $pdo->prepare('SELECT * FROM notes WHERE id = ?');
$stmt->execute([$id]);
$note = $stmt->fetch();
if (!$note) { echo 'Ghi chú không tồn tại.'; exit; }

$role = getUserRole($pdo, $user_id, (int)$note['project_id']);
if (!canDoAction($role, 'edit_note', (int)$note['author_id'], $user_id)) { echo 'Bạn không có quyền sửa ghi chú này.'; exit; }

$stmt = $pdo->prepare('UPDATE notes SET title = ?, content = ?, updated_at = NOW() WHERE id = ?');
$stmt->execute([$title, $content, $id]);

header('Location: ../pages/project.php?id=' . (int)$note['project_id']);
exit;

?>