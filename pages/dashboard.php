<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../models/user_functions.php';
require_once __DIR__ . '/../models/project_functions.php';

// Check login
if (!isLoggedIn()) {
    redirect('Trangdangnhap.php');
}

$user_id = getCurrentUserId();
$user = getUserById($user_id);

// Xử lý tạo project mới
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_project'])) {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    
    if (empty($title)) {
        $error = 'Tiêu đề project không được trống.';
    } else {
        $project_id = createProject($title, $description, $user_id);
        if ($project_id) {
            redirect('TrangChinh.php?project_id=' . $project_id, 'Project created successfully!');
        } else {
            $error = 'Lỗi khi tạo project.';
        }
    }
}

// Lấy danh sách project của user
$projects = getProjectsByUser($user_id);
$message = getSessionMessage();
$error = $error ?? '';

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Smart Notes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <link rel="stylesheet" href="../assets/css_view.css">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, sans-serif; background: #f5f5f5; }
        
        .navbar {
            background: #667eea;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .navbar h1 {
            margin: 0;
            font-size: 24px;
        }
        
        .navbar-right {
            display: flex;
            gap: 20px;
            align-items: center;
        }
        
        .navbar-right a {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 5px;
            transition: background 0.3s;
        }
        
        .navbar-right a:hover {
            background: rgba(255, 255, 255, 0.2);
        }
        
        .logout-btn {
            background: #e74c3c !important;
            padding: 8px 15px !important;
            border-radius: 5px !important;
        }
        
        .logout-btn:hover {
            background: #c0392b !important;
        }
        
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .message {
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .header-section h2 {
            margin: 0;
            color: #333;
        }
        
        .btn-create {
            background: #667eea;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: background 0.3s;
        }
        
        .btn-create:hover {
            background: #5568d3;
        }
        
        .projects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .project-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
        }
        
        .project-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .project-card h3 {
            margin: 0 0 10px 0;
            color: #333;
            font-size: 18px;
        }
        
        .project-card p {
            margin: 0 0 15px 0;
            color: #666;
            font-size: 14px;
            line-height: 1.6;
        }
        
        .project-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
        
        .project-footer small {
            color: #999;
            font-size: 12px;
        }
        
        .project-footer a {
            background: #667eea;
            color: white;
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            transition: background 0.3s;
        }
        
        .project-footer a:hover {
            background: #5568d3;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }
        
        .empty-state i {
            font-size: 64px;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .empty-state p {
            color: #999;
            font-size: 16px;
            margin-bottom: 20px;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
        }
        
        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .modal-header h2 {
            margin: 0;
        }
        
        .close-btn {
            background: none;
            border: none;
            font-size: 28px;
            cursor: pointer;
            color: #666;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }
        
        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 5px rgba(102, 126, 234, 0.5);
        }
        
        .modal-buttons {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-weight: 600;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5568d3;
        }
        
        .btn-secondary {
            background: #e0e0e0;
            color: #333;
        }
        
        .btn-secondary:hover {
            background: #d0d0d0;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <div class="navbar">
        <h1><i class="fas fa-sticky-note"></i> Smart Notes</h1>
        <div class="navbar-right">
            <span><?= sanitize($user['name']) ?></span>
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
    
    <!-- Main Container -->
    <div class="container">
        <!-- Messages -->
        <?php if ($message): ?>
            <div class="message success-message"><?= sanitize($message) ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="message error-message"><?= sanitize($error) ?></div>
        <?php endif; ?>
        
        <!-- Header Section -->
        <div class="header-section">
            <h2>Các Project của Bạn</h2>
            <button class="btn-create" onclick="openCreateModal()"><i class="fas fa-plus"></i> Tạo Project Mới</button>
        </div>
        
        <!-- Projects Grid -->
        <?php if (!empty($projects)): ?>
            <div class="projects-grid">
                <?php foreach ($projects as $project): ?>
                    <div class="project-card">
                        <h3><?= sanitize($project['title']) ?></h3>
                        <p><?= sanitize(substr($project['description'] ?? '', 0, 100)) ?><?= strlen($project['description'] ?? '') > 100 ? '...' : '' ?></p>
                        <div class="project-footer">
                            <small><?= formatDateShort($project['created_at']) ?></small>
                            <a href="TrangChinh.php?project_id=<?= $project['id'] ?>"><i class="fas fa-arrow-right"></i> Open</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>Bạn chưa có project nào. Hãy tạo một project để bắt đầu!</p>
                <button class="btn-create" onclick="openCreateModal()"><i class="fas fa-plus"></i> Tạo Project Đầu Tiên</button>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Create Project Modal -->
    <div id="createModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Tạo Project Mới</h2>
                <button class="close-btn" onclick="closeCreateModal()">×</button>
            </div>
            
            <form method="POST">
                <div class="form-group">
                    <label for="title">Tiêu đề Project:</label>
                    <input type="text" id="title" name="title" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Mô tả:</label>
                    <textarea id="description" name="description"></textarea>
                </div>
                
                <div class="modal-buttons">
                    <button type="button" class="btn btn-secondary" onclick="closeCreateModal()">Hủy</button>
                    <button type="submit" name="create_project" class="btn btn-primary">Tạo</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function openCreateModal() {
            document.getElementById('createModal').classList.add('active');
        }
        
        function closeCreateModal() {
            document.getElementById('createModal').classList.remove('active');
        }
        
        // Close modal when clicking outside
        document.getElementById('createModal').addEventListener('click', function(event) {
            if (event.target === this) {
                closeCreateModal();
            }
        });
    </script>
</body>
</html>
