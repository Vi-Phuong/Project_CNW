# 🏗️ Kiến Trúc Ứng Dụng Smart Notes

## 📋 Tóm Tắt
- **Framework**: PHP procedural (không class) + MySQL
- **Kiến trúc**: Kiểu MVC nhưng đơn giản hơn (không dùng class)
- **Bảo mật**: Prepared statements, password hashing, ACL 4-level
- **Database**: MySQL với schema.sql (projects, users, notes, project_members)

---

## 🗂️ Cấu Trúc Thư Mục

```
Project_CNW/
│
├── includes/                      # CORE: Được require ở mọi file
│   ├── init.php                  # Bootstrap (session, load config, db, helpers, acl)
│   ├── db.php                    # PDO Connection (prepared statements)
│   ├── config.php                # Constants (paths, pagination, statuses)
│   ├── helpers.php               # Utility functions (sanitize, format, redirect)
│   └── acl.php                   # Access Control + Role definitions (4 levels)
│
├── models/                        # MODELS: Functions thay thế class
│   ├── user_functions.php        # User CRUD (login, register, profile)
│   ├── project_functions.php     # Project CRUD + members management
│   └── note_functions.php        # Note CRUD + status, search
│
├── pages/                         # CONTROLLERS: Request handlers
│   ├── index.php                 # Router (redirect to dashboard or login)
│   ├── Trangdangnhap.php        # Login page + POST handler
│   ├── register.php              # Register page + POST handler
│   ├── logout.php                # Session destroy
│   ├── dashboard.php             # Project list + create project form
│   ├── TrangChinh.php            # Note editor (main feature)
│   ├── project_settings.php      # Manage members (add/remove)
│   └── config.php                # Local config (override if needed)
│
├── assets/                        # Static files
│   └── css_view.css              # Shared CSS
│
├── view/                          # Old views (legacy, có thể xóa)
│   └── ...
│
├── backup_json/                   # Backup data (không dùng)
│   └── ...
│
├── schema.sql                     # Database schema
├── duannotion.sql               # Sample data (optional)
└── README_SETUP.md              # Setup guide
```

---

## 🔄 Flow Chính

### 1️⃣ **Đăng nhập / Đăng ký**
```
User nhập email + password
          ↓
POST → pages/Trangdangnhap.php hoặc pages/register.php
          ↓
Gọi verifyLogin() / createUser() từ models/user_functions.php
          ↓
Check password_verify() / INSERT user
          ↓
SET $_SESSION['user_id'], $_SESSION['user_email']
          ↓
Redirect → dashboard.php
```

### 2️⃣ **Dashboard (Xem Projects)**
```
User vào pages/dashboard.php
          ↓
Check isLoggedIn()
          ↓
Gọi getProjectsByUser($user_id) từ models/project_functions.php
          ↓
Render danh sách projects
          ↓
User click "Open" → TrangChinh.php?project_id=X
```

### 3️⃣ **Soạn Ghi Chú (Main Feature)**
```
pages/TrangChinh.php?project_id=X
          ↓
Check hasAccess($user_id, $project_id, 'view') ← includes/acl.php
          ↓
GET: Lấy danh sách notes từ getNotesByProject()
          ↓
Render HTML + sidebar
          ↓
User soạn title + content
          ↓
POST action=add_note / edit_note / delete_note
          ↓
Check quyền: canEditNote() / canDeleteNote()
          ↓
Gọi createNote() / updateNote() / deleteNote()
          ↓
UPDATE database
          ↓
Refresh trang hoặc AJAX callback
```

### 4️⃣ **Quản Lý Project (Settings)**
```
pages/project_settings.php?project_id=X
          ↓
Check hasAccess(..., 'manage_members') [Owner chỉ]
          ↓
GET: Lấy danh sách thành viên từ getProjectMembers()
          ↓
POST action=add_member / remove_member
          ↓
Gọi addProjectMember() / removeProjectMember()
          ↓
UPDATE project_members table
          ↓
Refresh danh sách
```

---

## 🛡️ Hệ Thống Quyền (ACL)

**4 Cấp Độ Role** (định nghĩa ở `includes/acl.php`):

| Role | ID | Quyền |
|------|----|----|
| Observer | 1 | Chỉ xem ghi chú |
| Contributor | 2 | Thêm/sửa/xóa ghi chú của mình |
| Moderator | 3 | Thêm/sửa/xóa ghi chú của ai cũng được, thay đổi status |
| Owner | 4 | Toàn quyền, quản lý thành viên, xóa project |

**Kiểm tra quyền:**
```php
hasAccess($user_id, $project_id, 'view')           // Có thể xem?
hasAccess($user_id, $project_id, 'add_note')       // Có thể thêm note?
hasAccess($user_id, $project_id, 'edit_note')      // Có thể sửa note?
hasAccess($user_id, $project_id, 'delete_note')    // Có thể xóa note?
hasAccess($user_id, $project_id, 'change_status')  // Có thể đổi status?
hasAccess($user_id, $project_id, 'manage_members') // Có thể quản lý thành viên?
canEditNote($user_id, $note_id)                    // Có thể sửa note này?
canDeleteNote($user_id, $note_id)                  // Có thể xóa note này?
```

---

## 📦 Các Function Chính

### **User Functions** (`models/user_functions.php`)
```php
getUserByEmail($email)                    // → user array or null
getUserById($user_id)                     // → user array
createUser($email, $password, $name)      // → user_id or false
verifyLogin($email, $password)            // → user array if valid
updateUserProfile($user_id, $name, $email)
changePassword($user_id, $old_pwd, $new_pwd)
```

### **Project Functions** (`models/project_functions.php`)
```php
getProjectById($project_id)               // → project array
getProjectsByUser($user_id)               // → array of projects
createProject($title, $description, $owner_id)
updateProject($project_id, $title, $description)
deleteProject($project_id)
getProjectMembers($project_id)            // → array of members
addProjectMember($project_id, $user_id, $role)
removeProjectMember($project_id, $user_id)
updateMemberRole($project_id, $user_id, $role)
```

### **Note Functions** (`models/note_functions.php`)
```php
getNoteById($note_id)                     // → note array
getNotesByProject($project_id, $order)    // → array of notes
getNotesByUserInProject($project_id, $user_id)
getNotesByStatus($project_id, $status)
createNote($project_id, $author_id, $title, $content, $status)
updateNote($note_id, $title, $content)
updateNoteStatus($note_id, $status)
deleteNote($note_id)
searchNotes($project_id, $query)
getNoteCountByStatus($project_id)         // → array ['pending'=>5, ...]
```

### **Helper Functions** (`includes/helpers.php`)
```php
sanitize($input)                          // Escape HTML + trim
formatDate($date)                         // Format to d/m/Y H:i
formatDateShort($date)                    // Format to d/m/Y
redirect($url, $message)                  // Redirect + set session message
getSessionMessage()                       // Get & clear session message
isLoggedIn()                              // Check session user_id
getCurrentUserId()                        // Get user_id from session
```

### **ACL Functions** (`includes/acl.php`)
```php
hasAccess($user_id, $project_id, $action)
canEditNote($user_id, $note_id)
canDeleteNote($user_id, $note_id)
getInRole($user_id, $project_id)          // → role ID or null
getRoleName($role_id)                     // → "Owner", "Moderator", etc
```

---

## 🗄️ Database Schema

```sql
users
├── id (PK)
├── email (UNIQUE)
├── password (hashed)
├── name
└── created_at

projects
├── id (PK)
├── title
├── description
├── owner_id (FK → users)
└── created_at

project_members
├── id (PK)
├── project_id (FK → projects)
├── user_id (FK → users)
├── role (TINYINT: 1=Observer, 2=Contributor, 3=Moderator, 4=Owner)
└── created_at

notes
├── id (PK)
├── project_id (FK → projects)
├── author_id (FK → users)
├── title
├── content
├── status (ENUM: 'pending', 'confirmed', 'processing', 'resolved')
├── created_at
└── updated_at
```

---

## 🔐 Security Checklist

✅ **Done:**
- Prepared statements (PDO) → chống SQL injection
- htmlspecialchars() → chống XSS
- password_hash() + password_verify() → password safe
- Session-based auth
- Server-side ACL (không tin client)

⚠️ **TODO (Production):**
- [ ] HTTPS
- [ ] CSRF token
- [ ] Rate limiting (login attempts)
- [ ] Input validation (email format, length limits)
- [ ] Logging & monitoring
- [ ] API rate limiting (nếu có API)

---

## 🚀 Chạy Trên Local (XAMPP)

```bash
# 1. Copy project vào htdocs
cp -r Project_CNW C:/xampp/htdocs/HoangMyLinh/

# 2. Import DB
mysql -u root < Project_CNW/schema.sql
mysql -u root project_cnw < Project_CNW/duannotion.sql

# 3. Mở browser
http://localhost/HoangMyLinh/Project_CNW/pages/

# 4. Auto redirect to login nếu chưa đăng nhập
```

---

## 📝 Quy Ước Naming

| Tên | Kiểu | VD |
|-----|------|-----|
| Function | snake_case | `getProjectsByUser()`, `hasAccess()` |
| Variable | $camelCase | `$user_id`, `$project` |
| Constant | UPPER_SNAKE | `ROLE_OWNER`, `NOTES_PER_PAGE` |
| Class | N/A | Không dùng class (procedural) |
| File | camelCase.php | `user_functions.php`, `project_settings.php` |

---

## 📱 UI Pages

| URL | File | Chức năng |
|-----|------|---------|
| `/pages/` | index.php | Router (redirect) |
| `/pages/Trangdangnhap.php` | Trangdangnhap.php | Login form |
| `/pages/register.php` | register.php | Register form |
| `/pages/dashboard.php` | dashboard.php | Project list |
| `/pages/TrangChinh.php?project_id=X` | TrangChinh.php | Note editor |
| `/pages/project_settings.php?project_id=X` | project_settings.php | Manage members |
| `/pages/logout.php` | logout.php | Logout + destroy session |

---

## 🎯 Next Steps (Enhancements)

1. **Frontend JS**: Real-time search, AJAX save notes
2. **API**: REST endpoints cho mobile app
3. **Export**: PDF/Excel export notes
4. **Tags**: Note tags/categories
5. **Comments**: Collaborate on notes
6. **Mobile**: Flutter/React Native app
7. **Analytics**: User activity, productivity metrics

---

**Architecture Diagram**:
```
┌─────────────┐
│   Browser   │
└──────┬──────┘
       │ HTTP Request
       ↓
┌───────────────────────┐
│  pages/*.php          │ ← User Requests
│  (Controllers)        │
└────────────┬──────────┘
             │ require_once
       ┌─────┴──────────┐
       ↓                ↓
┌──────────────┐  ┌──────────────────┐
│ includes/    │  │  models/         │
│ - init.php   │  │  - user_func.php │
│ - db.php     │  │  - proj_func.php │
│ - helpers.   │  │  - note_func.php │
│ - acl.php    │  │                  │
└──────┬───────┘  └────────┬─────────┘
       │                   │
       └─────────┬─────────┘
                 │ query()
                 ↓
           ┌──────────────┐
           │ MySQL (PDO)  │
           │ project_cnw  │
           └──────────────┘
```

---

**Made by**: Procedural PHP Developer (Not OOP!)  
**Date**: Nov 2025  
**Project**: Smart Notes - CNW
