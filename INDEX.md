# 📚 File Index - Smart Notes Project

## 🚀 Start Here

1. **QUICK_START.md** ← Read this FIRST (5 minutes)
2. **ARCHITECTURE.md** ← Understand structure
3. **README_SETUP.md** ← Detailed setup guide
4. **SUMMARY.txt** ← Project summary

---

## 📂 Project Structure

### Core Files (`includes/`)
| File | Purpose | Key Functions |
|------|---------|----------------|
| **init.php** | Application bootstrap | session_start(), load all config, db, helpers, acl |
| **db.php** | Database connection | PDO setup, UTF-8 charset |
| **config.php** | App constants | APP_URL, NOTES_PER_PAGE, STATUS_LABELS |
| **helpers.php** | Utility functions | sanitize(), formatDate(), redirect(), isLoggedIn() |
| **acl.php** | Access control | hasAccess(), canEditNote(), getInRole(), getRoleName() |

### Model Functions (`models/`)
| File | Purpose | Functions Count |
|------|---------|-----------------|
| **user_functions.php** | User CRUD | 6: create/get/verify/update user |
| **project_functions.php** | Project CRUD + members | 11: create/get/update/delete projects, manage members |
| **note_functions.php** | Note CRUD + status | 10: create/get/update/delete notes, search, status |

### Page Handlers (`pages/`)
| File | Route | Purpose |
|------|-------|---------|
| **index.php** | `/` | Router (redirect to dashboard or login) |
| **Trangdangnhap.php** | `/Trangdangnhap.php` | Login form + handler |
| **register.php** | `/register.php` | Register form + handler |
| **logout.php** | `/logout.php` | Logout handler (destroy session) |
| **dashboard.php** | `/dashboard.php` | Project list + create form |
| **TrangChinh.php** | `/TrangChinh.php?project_id=X` | Note editor (main feature) |
| **project_settings.php** | `/project_settings.php?project_id=X` | Manage members |
| **config.php** | - | Local config overrides |

### Database Files
| File | Purpose |
|------|---------|
| **schema.sql** | Database schema (4 tables) |
| **duannotion.sql** | Sample data (optional) |

### Documentation
| File | Purpose |
|------|---------|
| **README_SETUP.md** | Complete setup guide + troubleshooting |
| **QUICK_START.md** | 5-minute quick start + test scenarios |
| **ARCHITECTURE.md** | Architecture overview + diagrams |
| **SUMMARY.txt** | Build summary + improvements |
| **INDEX.md** | This file |

### Static Files
| File | Purpose |
|------|---------|
| **assets/css_view.css** | Shared CSS styles |

### Legacy (Can ignore/delete)
| Folder | Status |
|--------|--------|
| **view/** | Old views (replaced by pages/) |
| **backup_json/** | Old JSON backups |

---

## 🔥 Quick Navigation

### I want to...

**Understand the project**
→ Read: QUICK_START.md (5 min)

**Set it up locally**
→ Read: README_SETUP.md + follow Setup section

**Understand the architecture**
→ Read: ARCHITECTURE.md

**Add a new feature**
→ Edit: models/note_functions.php (add function)
→ Then: pages/TrangChinh.php (use function)

**Debug a problem**
→ Check: README_SETUP.md → Troubleshooting section

**Check file status**
→ This file: INDEX.md

---

## 📦 Function Quick Reference

### Authentication
```php
isLoggedIn()                        // Check if user logged in
getCurrentUserId()                  // Get user ID from session
getUserById($user_id)               // Get user from DB
createUser($email, $password, $name) // Register new user
verifyLogin($email, $password)      // Login verification
```

### Projects
```php
getProjectsByUser($user_id)         // Get user's projects
createProject($title, $desc, $owner_id) // Create project
getProjectMembers($project_id)      // Get members list
addProjectMember($project_id, $user_id, $role) // Add member
```

### Notes
```php
getNotesByProject($project_id)      // Get all notes
createNote($project_id, $author_id, $title, $content)
updateNote($note_id, $title, $content)
deleteNote($note_id)
updateNoteStatus($note_id, $status)
searchNotes($project_id, $query)
```

### Access Control
```php
hasAccess($user_id, $project_id, 'action')  // Check permission
canEditNote($user_id, $note_id)    // Can edit this note?
canDeleteNote($user_id, $note_id)  // Can delete this note?
getInRole($user_id, $project_id)   // Get user's role ID
getRoleName($role_id)              // Convert role ID to name
```

### Helpers
```php
sanitize($input)                    // HTML escape + trim
formatDate($date)                   // Format to d/m/Y H:i
redirect($url, $message)            // Redirect + set session message
```

---

## 🗄️ Database Tables

```sql
users
├── id (int, PK)
├── email (varchar 255, UNIQUE)
├── password (varchar 255, hashed)
├── name (varchar 255)
└── created_at (timestamp)

projects
├── id (int, PK)
├── title (varchar 255)
├── description (text)
├── owner_id (int, FK → users.id)
└── created_at (timestamp)

project_members
├── id (int, PK)
├── project_id (int, FK → projects.id)
├── user_id (int, FK → users.id)
├── role (tinyint: 1=Observer, 2=Contributor, 3=Moderator, 4=Owner)
└── created_at (timestamp)

notes
├── id (int, PK)
├── project_id (int, FK → projects.id)
├── author_id (int, FK → users.id)
├── title (varchar 255)
├── content (text)
├── status (enum: pending, confirmed, processing, resolved)
├── created_at (timestamp)
└── updated_at (timestamp)
```

---

## 🎯 Development Workflow

### Add New Feature (Example: Export Notes)

1. **Add function** to models/note_functions.php:
```php
function exportNotes($project_id, $format = 'csv') {
    $notes = getNotesByProject($project_id);
    if ($format === 'csv') {
        // generate CSV
    }
    return $output;
}
```

2. **Add handler** to pages/TrangChinh.php:
```php
if ($_POST['action'] === 'export') {
    $notes_csv = exportNotes($project_id, 'csv');
    header('Content-Type: text/csv');
    echo $notes_csv;
    exit;
}
```

3. **Add button** to TrangChinh.php HTML:
```html
<button name="export">Export CSV</button>
```

4. **Test** locally and deploy

---

## ✅ Checklist Before Use

- [ ] Read QUICK_START.md (5 min)
- [ ] Import schema.sql into MySQL
- [ ] Check includes/db.php config
- [ ] Start XAMPP (Apache + MySQL)
- [ ] Visit http://localhost/.../pages/
- [ ] Create test account
- [ ] Create test project
- [ ] Add test note
- [ ] Test permissions (change role)
- [ ] Read ARCHITECTURE.md for understanding

---

## 📞 Need Help?

| Issue | Solution |
|-------|----------|
| DB connection error | Check includes/db.php + README_SETUP.md |
| Login not working | Check users table + password hash |
| Permission denied | Check project_members + role (4=Owner) |
| Session lost | Restart XAMPP + clear browser cache |
| 404 error | Use correct URL: `/pages/` not `/view/` |

**Full guide:** See README_SETUP.md → Troubleshooting

---

## 🚀 Next Steps

After understanding the project:

1. ✅ Set up locally (QUICK_START.md)
2. ✅ Test all features
3. ✅ Read ARCHITECTURE.md
4. ✅ Extend features (add export, search, etc.)
5. ✅ Deploy to server (follow README_SETUP.md)

---

## 📋 Project Stats

| Metric | Count |
|--------|-------|
| Total Files | 15+ |
| Total Functions | 35+ |
| Total Lines of Code | 1500+ |
| Database Tables | 4 |
| User Roles | 4 |
| Pages | 7 |
| Documentation Files | 5 |

---

## 🎉 You're Ready!

**Next:** Open QUICK_START.md and follow the 5-minute setup 🚀

---

**Last Updated:** Nov 2025
**Project:** Smart Notes - Educational PHP Project
**Status:** ✅ Complete & Ready to Use
