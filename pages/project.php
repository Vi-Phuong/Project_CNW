<!-- trang chi tiet du an- bang ghi chu -->
 <?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: auth.php');
    exit;
}

$project_id = (int)($_GET['id'] ?? 0);
if (!$project_id) {
    die('Công trường không tồn tại!');
}

$user_id = $_SESSION['user_id'];

// Lấy thông tin dự án và vai trò của người dùng
$stmt = $pdo->prepare("
    SELECT p.*, pur.role
    FROM projects p
    JOIN project_user_roles pur ON p.id = pur.project_id
    WHERE p.id = ? AND pur.user_id = ?
");
$stmt->execute([$project_id, $user_id]);
$project = $stmt->fetch();

if (!$project) {
    die('Bạn không có quyền truy cập công trường này!');
}

$user_role = $project['role'];

// Lấy danh sách ghi chú
$stmt = $pdo->prepare("
    SELECT n.*, u.username AS author_name
    FROM notes n
    JOIN users u ON n.author_id = u.id
    WHERE n.project_id = ?
    ORDER BY n.created_at DESC
");
$stmt->execute([$project_id]);
$notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hàm kiểm tra quyền chỉnh sửa ghi chú
function canEditNote($note_author_id, $current_user_id, $user_role) {
    if ($user_role === 'owner' || $user_role === 'moderator') return true;
    if ($user_role === 'contributor' && $note_author_id == $current_user_id) return true;
    return false;
}

// Hàm chuyển trạng thái sang tiếng Việt
function statusToVietnamese($status) {
    $map = [
        'pending' => '🟡 Chờ duyệt vật liệu',
        'confirmed' => '🟢 Đã duyệt',
        'processing' => '🔵 Đang thi công',
        'resolved' => '✅ Hoàn thành'
    ];
    return $map[$status] ?? $status;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>📋 <?= htmlspecialchars($project['title']) ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@500;600;700&family=Open+Sans&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Comic Sans MS', cursive; background: #fff8e1; margin: 0; padding: 0; }
    header { background: #f57f17; color: white; padding: 15px 20px; }
    .project-header {
      padding: 20px; background: white; margin: 0 0 20px 0; box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    .note-board { padding: 0 20px; }
    .note-card {
      background: white; padding: 15px; margin-bottom: 15px; border-radius: 10px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1); border-left: 4px solid #ffca28;
    }
    .note-status { font-weight: bold; margin-top: 8px; display: block; }
    .btn { padding: 6px 12px; border: none; border-radius: 6px; cursor: pointer; font-size: 13px; margin-right: 5px; }
    .btn-edit { background: #2196f3; color: white; }
    .btn-delete { background: #f44336; color: white; }
    .btn-status { background: #9c27b0; color: white; }
    .btn-manage { background: #00796b; color: white; padding: 10px 15px; margin-top: 10px; }
    .hidden { display: none; }
    
  body {
    font-family: 'Open Sans', sans-serif;
    background: #f8f9fa;
    margin: 0;
    padding: 0;
    color: #333;
  }
  h1, h2, h3, h4 {
    font-family: 'Quicksand', sans-serif;
    font-weight: 700;
  }
  .btn {
    font-family: 'Quicksand', sans-serif;
    padding: 10px 16px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    transition: all 0.2s;
  }
  .btn:hover {
    opacity: 0.9;
    transform: translateY(-1px);
  }
  </style>
</head>
<body>
  <header>
    <h2>📋 Bảng ghi chú: <?= htmlspecialchars($project['title']) ?></h2>
  </header>

  <div class="project-header">
    <p><?= htmlspecialchars($project['description'] ?: 'Không có mô tả.') ?></p>
    <?php if ($user_role === 'owner'): ?>
      <button class="btn-manage" onclick="alert('Chức năng quản lý thành viên (chỉ Chủ đầu tư)')">👥 Quản lý đội ngũ</button>
    <?php endif; ?>
  </div>

  <div class="note-board">
    <!-- Nút thêm ghi chú -->
    <?php if (in_array($user_role, ['owner', 'moderator', 'contributor'])): ?>
      <button class="btn btn-edit" onclick="alert('Chức năng thêm ghi chú (giả lập)')">➕ Gửi phiếu yêu cầu</button>
    <?php endif; ?>

    <h3 style="margin-top: 20px;">📝 Các phiếu yêu cầu</h3>
    <?php if (empty($notes)): ?>
      <p>Chưa có phiếu yêu cầu nào.</p>
    <?php else: ?>
      <?php foreach ($notes as $note): ?>
        <div class="note-card">
          <p><?= htmlspecialchars($note['content']) ?></p>
          <small>Tác giả: <?= htmlspecialchars($note['author_name']) ?></small>
          <span class="note-status"><?= statusToVietnamese($note['status']) ?></span>

          <div style="margin-top: 10px;">
            <?php if (canEditNote($note['author_id'], $user_id, $user_role)): ?>
              <button class="btn btn-edit">Sửa</button>
              <button class="btn btn-delete">Xóa</button>
            <?php endif; ?>

            <?php if ($user_role === 'owner'): ?>
              <button class="btn btn-status">🔄 Đổi trạng thái</button>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</body>
</html>