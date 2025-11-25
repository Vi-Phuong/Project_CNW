<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/auth_db.php';
require_once __DIR__ . '/../includes/acl.php';

if (!current_db_user_id()) { header('Location: ../view/Trangdangnhap.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ../pages/dashboard.php'); exit; }

$note_id = (int)($_POST['note_id'] ?? 0);
$new_status = $_POST['status'] ?? '';
if ($note_id === 0 || $new_status === '') { echo 'Thiếu dữ liệu.'; exit; }

$stmt = $pdo->prepare('SELECT * FROM notes WHERE id = ?');
$stmt->execute([$note_id]);
$note = $stmt->fetch();
if (!$note) { echo 'Ghi chú không tồn tại.'; exit; }

$user_id = current_db_user_id();
$role = getUserRole($pdo, $user_id, (int)$note['project_id']);
if (!canDoAction($role, 'change_status', (int)$note['author_id'], $user_id)) { echo 'Bạn không có quyền đổi trạng thái.'; exit; }

$stmt = $pdo->prepare('UPDATE notes SET status = ?, updated_at = NOW() WHERE id = ?');
$stmt->execute([$new_status, $note_id]);

header('Location: ../pages/project.php?id=' . (int)$note['project_id']);
exit;

?>