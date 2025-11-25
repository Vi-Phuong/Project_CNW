<?php
/**
 * TrangChinh.php - Trang chính ghi chú
 * Tích hợp backend logic + HTML giao diện
 */

require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../models/project_functions.php';
require_once __DIR__ . '/../models/note_functions.php';
require_once __DIR__ . '/../models/user_functions.php';

// Check login
if (!isLoggedIn()) {
    redirect('Trangdangnhap.php');
}

$user_id = getCurrentUserId();
$user = getUserById($user_id);
$project_id = (int)($_GET['project_id'] ?? 0);

if (!$project_id) {
    // Nếu không có project_id, lấy project đầu tiên
    $projects = getProjectsByUser($user_id);
    if ($projects) {
        $project_id = $projects[0]['id'];
    } else {
        redirect('dashboard.php', 'Vui lòng tạo project trước.');
    }
}

// Check access
if (!hasAccess($user_id, $project_id, 'view')) {
    die('Bạn không có quyền xem project này.');
}

$project = getProjectById($project_id);
if (!$project) {
    die('Project không tồn tại.');
}

// Xử lý POST actions
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Add note
    if ($action === 'add_note') {
        if (!hasAccess($user_id, $project_id, 'add_note')) {
            $error = 'Bạn không có quyền thêm ghi chú.';
        } else {
            $title = $_POST['title'] ?? '';
            $content = $_POST['content'] ?? '';
            
            if (empty($content)) {
                $error = 'Nội dung ghi chú không được trống.';
            } else {
                $note_id = createNote($project_id, $user_id, $title, $content, 'pending');
                if ($note_id) {
                    $success = 'Ghi chú đã được tạo.';
                } else {
                    $error = 'Lỗi khi tạo ghi chú.';
                }
            }
        }
    }
    
    // Edit note
    if ($action === 'edit_note') {
        $note_id = (int)($_POST['note_id'] ?? 0);
        $title = $_POST['title'] ?? '';
        $content = $_POST['content'] ?? '';
        
        if (!canEditNote($user_id, $note_id)) {
            $error = 'Bạn không có quyền chỉnh sửa ghi chú này.';
        } else {
            if (empty($content)) {
                $error = 'Nội dung ghi chú không được trống.';
            } else {
                if (updateNote($note_id, $title, $content)) {
                    $success = 'Ghi chú đã được cập nhật.';
                } else {
                    $error = 'Lỗi khi cập nhật ghi chú.';
                }
            }
        }
    }
    
    // Delete note
    if ($action === 'delete_note') {
        $note_id = (int)($_POST['note_id'] ?? 0);
        
        if (!canDeleteNote($user_id, $note_id)) {
            $error = 'Bạn không có quyền xóa ghi chú này.';
        } else {
            if (deleteNote($note_id)) {
                $success = 'Ghi chú đã được xóa.';
            } else {
                $error = 'Lỗi khi xóa ghi chú.';
            }
        }
    }
    
    // Change status
    if ($action === 'change_status') {
        $note_id = (int)($_POST['note_id'] ?? 0);
        $status = $_POST['status'] ?? '';
        
        if (!hasAccess($user_id, $project_id, 'change_status')) {
            $error = 'Bạn không có quyền thay đổi trạng thái.';
        } else {
            if (updateNoteStatus($note_id, $status)) {
                $success = 'Trạng thái đã được cập nhật.';
            } else {
                $error = 'Lỗi khi cập nhật trạng thái.';
            }
        }
    }
}

// Lấy danh sách ghi chú và dữ liệu
$notes = getNotesByProject($project_id, 'updated_at DESC');
$projects = getProjectsByUser($user_id);
$current_note = isset($_GET['note_id']) ? getNoteById((int)$_GET['note_id']) : null;
$user_role = getInRole($user_id, $project_id);

// Thống kê
$status_count = getNoteCountByStatus($project_id);

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Smart Notes - Demo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <style>
        /* Basic reset */
        * { box-sizing: border-box; }
        html, body { height: 100%; margin: 0; font-family: Arial, Helvetica, sans-serif; }
        body { background: #ffffff; color: #111111; line-height: 1.6; font-size:15px; }

        /* Page layout */
        .container { display: flex; height: 100vh; }
        .sidebar { width: 320px; background: #ffffff; border-right: 1px solid #e6f2fb; padding: 20px; overflow: auto; }
        .main { flex: 1; padding: 24px 30px; overflow: auto; }

        /* Sidebar header (title + icons) */
        .sb-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
        .sb-title { font-size: 18px; font-weight: 700; letter-spacing: 0.2px; }
        .sb-icons { display: flex; gap: 8px; }
        .sb-icons .icon { width: 34px; height: 34px; background: #f0fbff; border-radius: 8px; display:flex; align-items:center; justify-content:center; color: #111111; cursor: pointer; }
        .sb-icons .icon.plus { background: #1EA7FF; color: #ffffff; width: 38px; height: 38px; border-radius: 10px; }

        /* small utility icon color (avoid CSS variables) */
        .icon-muted { color: #0f6fb0; }

        /* Search box */
        .search { display:flex; gap:10px; align-items:center; background:#f6fbff; padding:10px 12px; border-radius:12px; margin-bottom:14px; }
        .search input { border: 0; background: transparent; outline: none; width:100%; font-size:15px; color:#111111; }

        /* Divider line */
        .divider { height: 1px; background: #e6f2fb; margin: 10px 0; }

        /* Small controls */
        .adv { margin-bottom: 10px; }
        .adv .btn { background: #f8fbff; padding: 8px 10px; border-radius: 8px; border: 1px solid #e6f2fb; cursor: pointer; }

        /* Filters row (All / Pinned / Starred) */
        .filters { display:flex; gap: 8px; margin-bottom: 12px; }
        .filters .btn { padding: 8px 10px; border-radius: 8px; border: 0; background: transparent; cursor:pointer; font-weight:600; color:#111111; }
        .filters .btn.active { background: #1EA7FF; color: #ffffff; }

        /* Recent area */
        .recent { margin-bottom: 10px; }
        .recent select { padding: 8px 10px; border-radius: 8px; border: 1px solid #e6f2fb; }

        /* Sidebar list (folders + notes) */
        .sb-list { display:flex; flex-direction:column; gap:12px; }
        .folder-row { display:flex; justify-content:space-between; align-items:center; padding:12px; border-radius:8px; background:#ffffff; font-weight:600; }
        .badge { background:#ffffff; padding:8px 10px; border-radius:12px; border:1px solid #e6f2fb; font-size:13px; }

        /* Small note card */
        .note-small { background:#ffffff; border:1px solid #e6f2fb; border-radius:10px; padding:12px; cursor:pointer; }
        .note-small .title { font-weight:700; color:#111111; }
        .note-small .content { color:#3b3f45; font-size:14px; margin-top:8px; line-height:1.6; }
        .note-small .chips { margin-top:10px; display:flex; gap:8px; }
        .chip { background:#eef0f4; padding:6px 10px; border-radius:999px; font-size:13px; color:#444; }

        /* Top area in main (title + toolbar) */
        .top-row { display:flex; justify-content:space-between; align-items:center; gap:16px; margin-bottom:16px; }
        .title-input { width:60%; border:0; outline:0; font-size:21px; font-weight:700; padding:6px 0; }
        .right-tools { display:flex; gap:10px; align-items:center; }

        /* Toolbar buttons (Text / Rich / Draw / AI) */
        .tb-btn { display:inline-flex; gap:8px; align-items:center; padding:10px 14px; border-radius:12px; background:#ffffff; border:1px solid #e6f2fb; cursor:pointer; font-weight:700; color:#111111; }
        .tb-btn.active { background:#1EA7FF; color:#ffffff; }

        /* Save button */
        .save-btn { display:inline-flex; gap:8px; align-items:center; padding:8px 12px; border-radius:10px; background:red; color:#ffffff; border:0; font-weight:700; cursor:pointer; }
        .save-btn i { color: #ffffff; }

        /* Folder/tags row */
        .sub-row { display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; gap:12px; }
        .folder-select { padding:8px 10px; border-radius:8px; border:1px solid #e6f2fb; background:#ffffff; }
        .tag-field { padding:8px 10px; border-radius:8px; border:1px solid #e6f2fb; background:#ffffff; color:#111111; }
        .add-tag-btn { display:inline-flex; gap:8px; align-items:center; background:#1EA7FF; color:#ffffff; padding:8px 10px; border-radius:8px; border:0; cursor:pointer; font-weight:600; }
        .add-tag-btn i { color: #ffffff; }

        /* small icon spacing for buttons */
        .right-tools .tb-btn i,
        .btn i,
        .save-btn i,
        .add-tag-btn i { margin-right:6px; }

        /* Dates */
        .dates { color:#333333; font-size:13px; margin-bottom:12px; }

        /* Content area / editor */
        .content-area { margin-top:12px; border-top:1px solid #f0f0f2; padding-top:18px; }
        .note-editor { display:block; width:100%; min-height: 340px; padding:22px; border-radius:12px; background:#ffffff; border:1px solid #e6f2fb; font-size:16px; color:#111111; line-height:1.8; font-family: inherit; resize:vertical; }
        .editor-label { display:block; color:#888; font-size:14px; margin-bottom:8px; }

        /* Messages */
        .message { padding: 12px; border-radius: 5px; margin-bottom: 15px; }
        .success-msg { background: #d4edda; color: #155724; }
        .error-msg { background: #f8d7da; color: #721c24; }

        @media (max-width: 1000px) {
            .sidebar { width: 280px; }
            .title-input { width: 50%; }
        }
    </style>
</head>
<body>

<div class="container">
    <!-- SIDEBAR -->
    <aside class="sidebar" id="Sidebar">
        <!-- 1) Header row -->
        <div class="sb-top">
            <div class="sb-title">Smart Notes</div>
            <div class="sb-icons">
                <div class="icon" title="Trash"><i class="fa-regular fa-trash-can"></i></div>
                <div class="icon" title="Desktop"><i class="fas fa-desktop" style="color: #000000;"></i></div>
                <a href="?action=new" class="icon plus" title="New note">
                    <i class="fa fa-plus"></i>
                </a>
            </div>
        </div>

        <!-- 2) Search -->
        <div class="search">
            <i class="fa fa-search icon-muted"></i>
            <input id="searchInput" placeholder="Search notes..." />
        </div>

        <div class="divider"></div>

        <!-- 3) Advanced -->
        <div class="adv">
            <div class="btn"><i class="fa-solid fa-sliders"></i> <span style="margin-left:6px;color:#444">Projects</span></div>
        </div>

        <!-- Projects list -->
        <div style="margin-bottom: 15px;">
            <?php foreach ($projects as $p): ?>
                <div style="padding: 8px 10px; border-radius: 6px; background: <?= $p['id'] == $project_id ? '#e8f4ff' : '#fff' ?>; cursor: pointer;">
                    <a href="TrangChinh.php?project_id=<?= $p['id'] ?>" style="text-decoration: none; color: #111; font-weight: 600;">
                        <?= sanitize($p['title']) ?>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Filter all/pinned/starred -->
        <div class="filters" id="filters">
            <button class="btn filter-btn filter-all active" id="filterAll"><i class="fa fa-folder-open"></i> All</button>
            <button class="btn filter-btn filter-pinned" id="filterPinned"><i class="fa fa-thumbtack" style="color:red"></i> Status</button>
        </div>

        <!-- Recent & sort -->
        <div class="recent">
            <div style="font-weight:600;color:#222">Ghi Chú</div>
            <div style="display:flex; gap:8px; align-items:center;">
                <select id="sortSelect" onchange="sortNotes()">
                    <option value="recent">Last Updated</option>
                    <option value="oldest">Oldest</option>
                </select>
            </div>
        </div>

        <div class="divider"></div>

        <!-- Project Actions -->
        <div style="display: flex; gap: 8px; margin-bottom: 15px;">
            <a href="dashboard.php" style="flex: 1; padding: 10px; background: #f0f2f5; border-radius: 6px; text-align: center; text-decoration: none; color: #111; font-weight: 600; font-size: 13px;">
                <i class="fa fa-arrow-left" style="margin-right: 5px;"></i>Dashboard
            </a>
            <?php if ($user_role == ROLE_OWNER): ?>
                <a href="project_settings.php?project_id=<?= $project_id ?>" style="flex: 1; padding: 10px; background: #667eea; border-radius: 6px; text-align: center; text-decoration: none; color: white; font-weight: 600; font-size: 13px;">
                    <i class="fa fa-cog" style="margin-right: 5px;"></i>Settings
                </a>
            <?php endif; ?>
        </div>

        <div class="divider"></div>

        <!-- 4) Folders + notes -->
        <div class="sb-list" id="sidebarList">
            <div class="folder-row">
                <div class="left"><i class="fas fa-folder" style="color: #74C0FC;"></i> <span style="margin-left:8px">Tất cả Ghi Chú</span></div>
                <div class="badge" id="allCount"><?= count($notes) ?></div>
            </div>

            <!-- Status stats -->
            <?php foreach (['pending' => 'Chờ xử lý', 'confirmed' => 'Đã xác nhận', 'processing' => 'Đang xử lý', 'resolved' => 'Đã giải quyết'] as $st => $label): ?>
                <div class="folder-row" style="background:#fff; border:1px solid #eee; align-items:center;">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <div style="width:10px;height:10px;border-radius:10px;background:var(--blue-dot)"></div>
                        <div><?= $label ?></div>
                    </div>
                    <div class="badge" id="count_<?= $st ?>"><?= $status_count[$st] ?? 0 ?></div>
                </div>
            <?php endforeach; ?>

            <!-- Notes list -->
            <?php foreach ($notes as $note): ?>
                <div class="note-small" data-id="<?= $note['id'] ?>">
                    <div class="title"><?= sanitize($note['title'] ?: 'Untitled') ?></div>
                    <div class="content"><?= sanitize(substr($note['content'], 0, 50)) ?>...</div>
                    <div class="chip"><?= ucfirst($note['status']) ?> - <?= formatDateShort($note['updated_at']) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </aside>

    <!-- MAIN AREA -->
    <main class="main" id="mainContent">
        <!-- Messages -->
        <?php if ($success): ?>
            <div class="message success-msg"><?= sanitize($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="message error-msg"><?= sanitize($error) ?></div>
        <?php endif; ?>

        <!-- Top row: title (editable) & toolbar right -->
        <div class="top-row">
            <form id="noteForm" method="POST" style="flex: 1; display: flex; gap: 10px; align-items: center;">
                <input type="hidden" name="action" id="actionInput" value="add_note">
                <input type="hidden" name="note_id" id="noteIdInput" value="">
                
                <input 
                    type="text" 
                    id="noteTitle" 
                    name="title" 
                    class="title-input" 
                    placeholder="Note title..." 
                    value="<?= $current_note ? sanitize($current_note['title']) : '' ?>" 
                />
                
                <div class="right-tools">
                    <div class="tb-btn tb-btn--text active"><i class="fa fa-font"></i> Text</div>
                    <button type="submit" class="save-btn" title="Save note">
                        <i class="fa-solid fa-floppy-disk"></i> Save
                    </button>
                </div>
            </form>
        </div>

        <!-- folder + tags + add tag -->
        <div class="sub-row">
            <div style="display:flex; align-items:center; gap:12px;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <i class="fa-regular fa-folder"></i>
                    <select id="statusSelect" name="status" class="folder-select" onchange="changeNoteStatus()">
                        <option value="pending" <?= $current_note && $current_note['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="confirmed" <?= $current_note && $current_note['status'] == 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                        <option value="processing" <?= $current_note && $current_note['status'] == 'processing' ? 'selected' : '' ?>>Processing</option>
                        <option value="resolved" <?= $current_note && $current_note['status'] == 'resolved' ? 'selected' : '' ?>>Resolved</option>
                    </select>
                </div>
            </div>

            <div style="margin-left:auto;">
                <small>Role: <?= getRoleName($user_role) ?></small>
            </div>
        </div>

        <!-- dates -->
        <div class="dates" id="datesRow">
            <i class="fa-regular fa-calendar"></i> Created: <span id="createdDate"><?= $current_note ? formatDate($current_note['created_at']) : '-' ?></span>
            &nbsp;&nbsp;&nbsp;
            <i class="fa-regular fa-clock"></i> Updated: <span id="updatedDate"><?= $current_note ? formatDate($current_note['updated_at']) : '-' ?></span>
        </div>

        <!-- Content editor -->
        <div class="content-area">
            <label for="editor" class="editor-label">Start writing your note...</label>
            <textarea id="editor" class="note-editor" name="content" form="noteForm"><?= $current_note ? sanitize($current_note['content']) : '' ?></textarea>
        </div>
    </main>
</div>

<script>
    // Load note when clicked
    document.querySelectorAll('.note-small').forEach(el => {
        el.addEventListener('click', function() {
            const noteId = this.getAttribute('data-id');
            window.location.href = '?project_id=<?= $project_id ?>&note_id=' + noteId;
        });
    });

    function changeNoteStatus() {
        const status = document.getElementById('statusSelect').value;
        if (!document.getElementById('noteIdInput').value) return;
        
        const form = document.getElementById('noteForm');
        document.getElementById('actionInput').value = 'change_status';
        form.submit();
    }

    function sortNotes() {
        // Reload page with sort param if needed
        console.log('Sort changed');
    }

    // Auto-save on textarea change (optional)
    document.getElementById('editor').addEventListener('change', function() {
        console.log('Content changed');
    });

    // Load note ID if editing
    <?php if ($current_note): ?>
        document.getElementById('noteIdInput').value = <?= $current_note['id'] ?>;
        document.getElementById('actionInput').value = 'edit_note';
    <?php endif; ?>
</script>

</body>
</html>
