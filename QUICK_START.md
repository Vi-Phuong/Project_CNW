# ⚡ Quick Start - Smart Notes

## 5 Bước Setup (5 phút)

### 1️⃣ Import Database
```bash
# Mở Terminal / PowerShell ở folder project
mysql -u root < schema.sql
```
✅ Database `project_cnw` được tạo với 4 bảng: users, projects, project_members, notes

### 2️⃣ Kiểm Tra Config
File: `includes/db.php` - Nếu dùng XAMPP mặc định, không cần sửa
```php
$host = 'localhost';        // ✓ OK
$db_name = 'project_cnw';   // ✓ OK
$user = 'root';             // ✓ OK
$pass = '';                 // ✓ OK (XAMPP no password)
```

### 3️⃣ Khởi Động XAMPP
- Mở XAMPP Control Panel
- Click "Start" cho Apache + MySQL
- Hoặc: `xampp-control.exe start`

### 4️⃣ Truy Cập Ứng Dụng
```
http://localhost/HoangMyLinh/Project_CNW/pages/
```
✅ Auto redirect đến login page

### 5️⃣ Tạo Tài Khoản Test
1. Click "Đăng ký"
2. Nhập: Email: `test@example.com`, Password: `123456`, Name: `Test User`
3. Click "Đăng Ký"
4. Đăng nhập với account vừa tạo
5. Bạn ở Dashboard - Click "Tạo Project Mới"

---

## 🎮 Dùng Thử

### Scenario 1: Tạo Project & Ghi Chú
1. Dashboard → "Tạo Project Mới"
2. Nhập Title: "My First Project", Description: "Test project"
3. Click "Tạo"
4. Auto open project → TrangChinh.php
5. Nhập Title: "My First Note"
6. Nhập Content: "Hello, Smart Notes!"
7. Click "Save"
8. ✅ Note đã lưu

### Scenario 2: Quản Lý Thành Viên (Cần Owner role)
1. Dashboard → Open project
2. Sidebar click "Settings" (hoặc: `project_settings.php?project_id=X`)
3. "Add Member" → Nhập email: `test2@example.com`, Role: Contributor
4. ✅ Member added

### Scenario 3: Thay Đổi Status Note (Cần Moderator role)
1. Mở note
2. Dropdown "Status" → Chọn "Confirmed" / "Processing" / "Resolved"
3. Auto save
4. ✅ Status updated

---

## 📁 File Quan Trọng

| File | Tác Dụng | Sửa Khi |
|------|---------|--------|
| `includes/db.php` | Config DB | Đổi host/user/pass |
| `includes/config.php` | App URL | Đổi đường dẫn localhost |
| `pages/index.php` | Entry point | - |
| `pages/TrangChinh.php` | Main feature | Tùy chỉnh UI |
| `models/*.php` | Logic DB | Add new functions |

---

## 🐛 Troubleshooting

### ❌ "Database connection error"
**Fix:**
```php
// Check includes/db.php
$db_name = 'project_cnw';  // Đúng?
```
Chạy: `mysql -u root` (test connection)

### ❌ "Bạn không có quyền"
**Fix:** Chỉ Owner có quyền quản lý. Role của bạn = ?
```
Check: project_members table → role = 4 (Owner)?
```

### ❌ 404 Not Found
**Fix:** URL phải là:
```
✓ http://localhost/HoangMyLinh/Project_CNW/pages/
✗ http://localhost/Project_CNW/pages/
✗ http://localhost/.../view/TrangChinh.php (cũ)
```

### ❌ Session không lưu (login không hoạt động)
**Fix:** Khởi động lại XAMPP, xóa browser cache

---

## 🚀 Development Tips

### 1️⃣ Thêm Function Mới
```php
// File: models/note_functions.php
function myNewFunction($param1, $param2) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM notes WHERE ...");
    $stmt->execute([$param1, $param2]);
    return $stmt->fetchAll();
}

// Dùng ở pages/TrangChinh.php
require_once __DIR__ . '/../models/note_functions.php';
$result = myNewFunction('value1', 'value2');
```

### 2️⃣ Thêm Route Mới
```php
// File: pages/my_new_page.php
<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../models/note_functions.php';

if (!isLoggedIn()) redirect('Trangdangnhap.php');

// Your logic here
?>
<!-- HTML -->
```

Access: `http://localhost/.../pages/my_new_page.php`

### 3️⃣ Debug Query
```php
// Thêm vào trước query:
echo "<pre>";
var_dump($sql);  // Xem SQL
echo "</pre>";

// Hoặc use try-catch:
try {
    $stmt->execute([$param]);
} catch (PDOException $e) {
    die("SQL Error: " . $e->getMessage());
}
```

### 4️⃣ Check Session
```php
echo "<pre>";
var_dump($_SESSION);
echo "</pre>";
// Xem: user_id, user_email, user_name
```

---

## 📚 Học Thêm

### File nên đọc (theo thứ tự):
1. `ARCHITECTURE.md` - Kiến trúc tổng quát
2. `includes/init.php` - Entry bootstrap
3. `models/note_functions.php` - Ví dụ functions
4. `pages/TrangChinh.php` - Main feature integrate
5. `includes/acl.php` - Permission system

### Functions chính sử dụng:
```php
// Xác thực
isLoggedIn()                    // ← Check user logged in
getCurrentUserId()              // ← Get user ID

// Database
getNotesByProject($project_id)  // ← Get notes
createNote(...)                 // ← Add note
updateNote(...)                 // ← Edit note
deleteNote($note_id)            // ← Delete note

// Quyền
hasAccess($user_id, $project_id, 'action')
canEditNote($user_id, $note_id)
canDeleteNote($user_id, $note_id)

// Tiện ích
sanitize($input)                // ← Escape HTML
formatDate($date)               // ← Format ngày
redirect($url, $msg)            // ← Redirect + message
```

---

## ✅ Checklist Trước Nộp

- [ ] Database imported (`project_cnw`)
- [ ] XAMPP running (Apache + MySQL)
- [ ] Can login (`test@example.com` / `123456`)
- [ ] Can create project
- [ ] Can add note
- [ ] Can change note status
- [ ] Can manage members (Owner only)
- [ ] Can logout
- [ ] No SQL errors ✓
- [ ] No XSS vulnerability ✓

---

## 🎯 Next: Add Features

Want to extend? Try these:

### Easy 🟢
- [ ] Add note search filter
- [ ] Add logout confirm dialog
- [ ] Show "Last edited by" for notes

### Medium 🟡
- [ ] Add note export (JSON/CSV)
- [ ] Add bulk delete notes
- [ ] Add admin dashboard (see all projects)

### Hard 🔴
- [ ] Add AJAX real-time save
- [ ] Add note comments/discussion
- [ ] Add notification system

---

**Ready? Open browser:**
```
http://localhost/HoangMyLinh/Project_CNW/pages/
```

**Questions?** Check `README_SETUP.md` for detailed setup guide.

---

**Happy coding! 🚀**
