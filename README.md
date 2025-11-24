README - Hướng dẫn triển khai hệ thống quản lý ghi chú (dự án môn) - PHP thuần + SQL + Session

Mục tiêu
--------
Tài liệu này mô tả cách triển khai hệ thống "Bảng ghi chú chia sẻ theo dự án" đúng theo yêu cầu trong `yeucaucuoiki.pdf`.
Yêu cầu chính:
- Triển khai PHP thuần (không dùng framework)
- Sử dụng SQL (MySQL) với 4 bảng: users, projects, project_members, notes
- Quản lý phiên bằng `$_SESSION`
- Phân quyền 4 cấp: 1 (Observer), 2 (Contributor), 3 (Moderator), 4 (Owner)
- Kiểm tra quyền ở backend trước mọi hành động thay đổi dữ liệu

Cấu trúc thư mục gợi ý
----------------------
Project_CNW/
- includes/
  - db.php          # Kết nối PDO
  - init.php        # session_start và cấu hình chung
  - auth.php        # hàm đăng ký/đăng nhập (sử dụng PDO)
  - acl.php         # hàm phân quyền: getUserRole(), canDoAction()
  - models.php      # các hàm thao tác DB (tùy chọn - giúp tách logic)
- pages/
  - register.php
  - login.php (index.php trong repo của bạn)
  - logout.php
  - create_project.php
  - invite_member.php
  - add_note.php
  - edit_note.php
  - delete_note.php
  - change_status.php
  - project.php     # trang hiển thị bảng ghi chú của 1 dự án
  - dashboard.php
- view/
  - TrangDangKi.php
  - Trangdangnhap.php
  - TrangChinh.php
- README.md

SQL schema (file: schema.sql)
-----------------------------
-- Dùng MySQL/MariaDB
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  name VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE projects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  owner_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE project_members (
  id INT AUTO_INCREMENT PRIMARY KEY,
  project_id INT NOT NULL,
  user_id INT NOT NULL,
  role TINYINT NOT NULL, -- 1..4
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE (project_id,user_id),
  FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE notes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  project_id INT NOT NULL,
  author_id INT NOT NULL,
  title VARCHAR(255),
  content TEXT NOT NULL,
  status ENUM('pending','confirmed','processing','resolved') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
  FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
);

Cách triển khai (chi tiết, từng bước)
-------------------------------------
1) Tạo database và import `schema.sql`.
   - Mở phpMyAdmin hoặc MySQL CLI, tạo database `project_cnw` rồi import schema.

2) Tạo `includes/db.php` (PDO):
```php
<?php
$host = '127.0.0.1';
$db   = 'project_cnw';
$user = 'root';
$pass = '';
$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
$options = [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];
$pdo = new PDO($dsn, $user, $pass, $options);
```

3) Tạo `includes/init.php` — session và cấu hình chung:
```php
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// hàm phụ trợ (nếu cần) có thể đặt ở đây
```

4) Tạo `includes/auth.php` — các hàm đăng ký/đăng nhập:
- Nguyên tắc: luôn dùng PDO + prepared statements; lưu hash bằng `password_hash()`; dùng `$_SESSION['user_id']`.

Ví dụ đơn giản (register/login):
```php
<?php
require_once __DIR__.'/db.php';
require_once __DIR__.'/init.php';

function registerUser(PDO $pdo, $name, $email, $password) {
  $hash = password_hash($password, PASSWORD_DEFAULT);
  $stmt = $pdo->prepare('INSERT INTO users (name,email,password) VALUES (?,?,?)');
  return $stmt->execute([$name,$email,$hash]);
}

function loginUser(PDO $pdo, $email, $password) {
  $stmt = $pdo->prepare('SELECT id,password,name FROM users WHERE email = ?');
  $stmt->execute([$email]);
  $u = $stmt->fetch();
  if ($u && password_verify($password, $u['password'])) {
    $_SESSION['user_id'] = $u['id'];
    $_SESSION['user_name'] = $u['name'];
    return true;
  }
  return false;
}
```

5) Tạo `includes/acl.php` — hàm phân quyền và kiểm tra trước mọi hành động:
- Luôn kiểm tra role trong backend. Mã dưới đây là pattern "sinh viên dễ hiểu".

```php
<?php
require_once __DIR__.'/db.php';
require_once __DIR__.'/init.php';

function getUserRole(PDO $pdo, $user_id, $project_id) {
  $stmt = $pdo->prepare('SELECT role FROM project_members WHERE user_id=? AND project_id=?');
  $stmt->execute([$user_id,$project_id]);
  $r = $stmt->fetch();
  return $r ? (int)$r['role'] : null;
}

function canDoAction($role, $action, $note_author_id = null, $current_user_id = null) {
  // action: create_note, edit_note, delete_note, change_status
  if ($action === 'create_note') return $role >= 2; // contributor+
  if ($action === 'edit_note' || $action === 'delete_note') {
    if ($role >= 3) return true; // moderator (3) and owner (4)
    if ($role === 2 && $note_author_id === $current_user_id) return true; // contributor only own
    return false;
  }
  if ($action === 'change_status') return $role === 4; // only owner
  return false; // default deny
}
```

6) Ví dụ handler: `pages/add_note.php`
```php
<?php
require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/init.php';
require_once __DIR__.'/../includes/acl.php';

// yêu cầu login
if (!isset($_SESSION['user_id'])) { header('Location: ../view/Trangdangnhap.php'); exit; }
$user_id = (int)$_SESSION['user_id'];
$project_id = (int)($_POST['project_id'] ?? 0);
$title = trim($_POST['title'] ?? '');
$content = trim($_POST['content'] ?? '');

$role = getUserRole($pdo, $user_id, $project_id);
if (!canDoAction($role, 'create_note')) { die('Không có quyền'); }

$stmt = $pdo->prepare('INSERT INTO notes (project_id, author_id, title, content, status) VALUES (?,?,?,?,?)');
$stmt->execute([$project_id, $user_id, $title, $content, 'pending']);
header('Location: ../pages/project.php?id=' . $project_id);
```

7) Ví dụ handler: `pages/edit_note.php`
```php
<?php
require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/init.php';
require_once __DIR__.'/../includes/acl.php';

if (!isset($_SESSION['user_id'])) { header('Location: ../view/Trangdangnhap.php'); exit; }
$user_id = (int)$_SESSION['user_id'];
$note_id = (int)($_POST['id'] ?? 0);
// load note
$stmt = $pdo->prepare('SELECT * FROM notes WHERE id = ?'); $stmt->execute([$note_id]); $note = $stmt->fetch();
if (!$note) die('Không tìm thấy ghi chú');
$role = getUserRole($pdo, $user_id, $note['project_id']);
if (!canDoAction($role, 'edit_note', $note['author_id'], $user_id)) die('Không có quyền');

// thực hiện cập nhật
$stmt = $pdo->prepare('UPDATE notes SET title=?, content=?, updated_at=NOW() WHERE id=?');
$stmt->execute([$_POST['title'], $_POST['content'], $note_id]);
header('Location: ../pages/project.php?id=' . $note['project_id']);
```

8) Ví dụ handler: `pages/change_status.php` (CHỈ OWNER được phép)
```php
<?php
require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/init.php';
require_once __DIR__.'/../includes/acl.php';

if (!isset($_SESSION['user_id'])) { header('Location: ../view/Trangdangnhap.php'); exit; }
$user_id = (int)$_SESSION['user_id'];
$note_id = (int)($_POST['note_id'] ?? 0);
$new_status = $_POST['status'] ?? '';

$stmt = $pdo->prepare('SELECT * FROM notes WHERE id=?'); $stmt->execute([$note_id]); $note = $stmt->fetch();
$role = getUserRole($pdo, $user_id, $note['project_id']);
if (!canDoAction($role, 'change_status')) die('Không có quyền');

$stmt = $pdo->prepare('UPDATE notes SET status=?, updated_at=NOW() WHERE id=?');
$stmt->execute([$new_status, $note_id]);
header('Location: ../pages/project.php?id=' . $note['project_id']);
```

9) Frontend (view) - hiển thị/ẩn nút theo role
- Ở `pages/project.php` (và `view` tương ứng) bạn vẫn nên lấy `role` và dùng để ẩn nút HTML. Nhưng nhắc lại: "ẩn nút không đủ" — backend phải kiểm tra.

Ví dụ (hiển thị nút đổi trạng thái):
```php
if ($role === 4) {
  // show status change form/button
}
```

10) Tên trạng thái phải cố định
- pending -> "Đang chờ xử lý"
- confirmed -> "Đã xác nhận"
- processing -> "Đang xử lý"
- resolved -> "Đã giải quyết"

Lưu ý bảo mật + điểm chấm
--------------------------
- Dùng PDO + prepared statements để tránh SQL injection.
- Escape output với `htmlspecialchars()` khi in ra HTML.
- Đảm bảo tất cả pages thay đổi dữ liệu đều kiểm tra quyền ở server-side.
- Không lưu mật khẩu thô; luôn hash.

Gợi ý test nhanh (tài khoản mẫu)
---------------------------------
- Tạo 3 user bằng SQL hoặc form register: owner@example.com, mod@example.com, contrib@example.com
- Tạo project bằng owner, sau đó insert các record vào `project_members` cho mod (role=3), contrib (role=2) và observer (role=1).
- Thử luồng: contrib tạo note (status pending) -> mod sửa nội dung -> owner đổi status -> contrib không được đổi status.

Checklist để đạt A+
--------------------
- [ ] 4 vai trò đúng quyền hạn
- [ ] Backend kiểm tra quyền mọi hành động thay đổi dữ liệu
- [ ] Chỉ owner đổi trạng thái
- [ ] Password hash + Session
- [ ] SQL + PHP thuần
- [ ] README mô tả cách cài đặt + tài khoản test

Muốn tôi làm tiếp gì?
---------------------
- Tôi có thể tạo ngay các file `includes/db.php`, `includes/auth.php`, `includes/acl.php`, và các `pages/*.php` theo mẫu để bạn chạy thử.
- Hoặc nếu bạn muốn giữ JSON hiện tại (đã triển khai), tôi có thể chuyển toàn bộ code JSON thành kiểu SQL (= chuyển lên MySQL) và cập nhật các handlers.

Chọn 1 trong 2: "Tạo file mẫu SQL+PHP" hoặc "Giữ JSON". Tôi sẽ bắt đầu ngay.