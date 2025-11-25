# Smart Notes - Hướng Dẫn Setup & Chạy

Ứng dụng quản lý ghi chú và dự án dùng PHP + MySQL, kiến trúc procedural (không class).

## Yêu cầu
- XAMPP (hoặc PHP 7.4+, MySQL)
- Browser hiện đại (Chrome, Firefox, Edge)

## Setup

### 1. Cấu hình Database
```bash
# Mở phpMyAdmin (http://localhost/phpmyadmin)
# Hoặc dùng MySQL CLI:
mysql -u root < schema.sql
```

Nếu có dữ liệu mẫu (duannotion.sql):
```bash
mysql -u root project_cnw < duannotion.sql
```

### 2. Cấu hình Ứng Dụng
- File: `includes/db.php`
- Chỉnh host, DB name, user, pass nếu khác:
  ```php
  $host = 'localhost';
  $db_name = 'project_cnw';
  $user = 'root';
  $pass = '';
  ```

- File: `includes/config.php`
- Chỉnh `APP_URL` theo đường dẫn thực tế:
  ```php
  define('APP_URL', 'http://localhost/HoangMyLinh/Project_CNW');
  ```

### 3. Kiểm tra File Structures
```
Project_CNW/
├── includes/
│   ├── init.php         (Bootstrap)
│   ├── db.php           (PDO Connection)
│   ├── helpers.php      (Utility functions)
│   ├── acl.php          (Access Control)
│   └── config.php       (Config constants)
├── models/
│   ├── user_functions.php       (User CRUD)
│   ├── project_functions.php    (Project CRUD)
│   └── note_functions.php       (Note CRUD)
├── pages/
│   ├── index.php                (Router)
│   ├── Trangdangnhap.php        (Login)
│   ├── register.php             (Register)
│   ├── dashboard.php            (Projects dashboard)
│   ├── TrangChinh.php           (Note editor)
│   ├── logout.php               (Logout)
│   └── project_settings.php     (Project settings)
├── assets/
│   └── css_view.css
├── schema.sql
└── README.md (this file)
```

## Chạy Ứng Dụng

### Trên XAMPP
1. Đặt folder `Project_CNW` vào `C:/xampp/htdocs/HoangMyLinh/`
2. Mở browser: `http://localhost/HoangMyLinh/Project_CNW/pages/`
3. Redirect tự động đến login nếu chưa đăng nhập

### Tài Khoản Test
Tạo user mới bằng trang Register: `http://localhost/.../pages/register.php`

Hoặc chèn vào database:
```sql
INSERT INTO users (email, password, name) VALUES 
('test@example.com', '$2y$10$...', 'Test User');
-- Password hash được tạo bằng password_hash('password', PASSWORD_DEFAULT)
```

## Flow Chính

### 1. **Đăng ký / Đăng nhập**
- User đến `/pages/Trangdangnhap.php` hoặc `/pages/register.php`
- Gọi `verifyLogin()` hoặc `createUser()` từ `models/user_functions.php`
- Session được set: `user_id`, `user_email`, `user_name`

### 2. **Dashboard**
- `/pages/dashboard.php` hiển thị danh sách project của user
- Gọi `getProjectsByUser($user_id)` để lấy project
- User có thể tạo project mới via `createProject()`

### 3. **Soạn Ghi Chú**
- `/pages/TrangChinh.php?project_id=X` hiển thị editor
- Kiểm tra quyền: `hasAccess($user_id, $project_id, 'view')`
- Thêm/sửa/xóa ghi chú qua:
  - `createNote()` (POST action=add_note)
  - `updateNote()` (POST action=edit_note)
  - `deleteNote()` (POST action=delete_note)
  - `updateNoteStatus()` (POST action=change_status)

### 4. **Quản Lý Project**
- `/pages/project_settings.php?project_id=X` để thêm/xóa thành viên
- Kiểm tra quyền: `hasAccess($user_id, $project_id, 'manage_members')`
- Thêm thành viên via `addProjectMember()`
- Xóa thành viên via `removeProjectMember()`

## Cấu Trúc Quyền (Role)

4 cấp độ quyền được định nghĩa trong `includes/acl.php`:

1. **Observer** (role=1): Chỉ xem
2. **Contributor** (role=2): Thêm/sửa/xóa ghi chú của mình
3. **Moderator** (role=3): Thêm/sửa/xóa ghi chú của ai cũng được, thay đổi status
4. **Owner** (role=4): Full quyền, quản lý thành viên

## Các Function Chính

### User Functions (`models/user_functions.php`)
- `getUserByEmail($email)` - Lấy user by email
- `getUserById($user_id)` - Lấy user by ID
- `createUser($email, $password, $name)` - Tạo user mới
- `verifyLogin($email, $password)` - Kiểm tra login
- `updateUserProfile($user_id, $name, $email)` - Cập nhật profile

### Project Functions (`models/project_functions.php`)
- `getProjectsByUser($user_id)` - Danh sách project của user
- `createProject($title, $description, $owner_id)` - Tạo project
- `getProjectMembers($project_id)` - Danh sách thành viên
- `addProjectMember($project_id, $user_id, $role)` - Thêm thành viên
- `removeProjectMember($project_id, $user_id)` - Xóa thành viên

### Note Functions (`models/note_functions.php`)
- `getNotesByProject($project_id, $order)` - Danh sách ghi chú
- `getNoteById($note_id)` - Lấy ghi chú by ID
- `createNote($project_id, $author_id, $title, $content, $status)` - Tạo ghi chú
- `updateNote($note_id, $title, $content)` - Cập nhật ghi chú
- `updateNoteStatus($note_id, $status)` - Thay đổi status
- `deleteNote($note_id)` - Xóa ghi chú
- `searchNotes($project_id, $query)` - Tìm kiếm ghi chú

### ACL Functions (`includes/acl.php`)
- `hasAccess($user_id, $project_id, $action)` - Kiểm tra quyền
- `canEditNote($user_id, $note_id)` - Kiểm tra edit note
- `canDeleteNote($user_id, $note_id)` - Kiểm tra delete note
- `getInRole($user_id, $project_id)` - Lấy role của user
- `getRoleName($role_id)` - Tên role từ ID

### Helper Functions (`includes/helpers.php`)
- `sanitize($input)` - Escape HTML, trim
- `formatDate($date)` - Format ngày (d/m/Y H:i)
- `formatDateShort($date)` - Format ngày (d/m/Y)
- `redirect($url, $message)` - Redirect + session message
- `isLoggedIn()` - Check đăng nhập
- `getCurrentUserId()` - Lấy user ID từ session

## Security Notes

✅ **Đã áp dụng:**
- Prepared statements (PDO) để chống SQL injection
- `htmlspecialchars()` để chống XSS
- `password_hash()` + `password_verify()` cho password
- Session-based auth
- Server-side ACL check

⚠️ **Nên cải thiện:**
- HTTPS (trên production)
- CSRF token cho forms
- Rate limiting cho login/register
- Input validation cho email, length, v.v.

## Troubleshooting

### Lỗi: "Database connection error"
- Check DB name, user, pass ở `includes/db.php`
- Đảm bảo MySQL running

### Lỗi: "Bạn không có quyền"
- Check role trong `project_members` table
- Owner role = 4, Moderator = 3, v.v.

### Session không lưu
- Check `session.save_path` ở php.ini
- Nếu dùng XAMPP, mặc định là `C:/xampp/tmp/`

## Todo (Chức năng thêm)

- [ ] Export notes to PDF/Excel
- [ ] Bulk operations (delete multiple notes)
- [ ] Filter/sort advanced
- [ ] Note tags/categories
- [ ] Collaborators real-time (WebSocket)
- [ ] API REST endpoints
- [ ] Mobile app (Flutter/React Native)

---

**Made with ❤️ for Project_CNW**
