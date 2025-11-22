<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Smart Notes - Demo</title>

<!-- Font Awesome cho icon -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

<style>
/* -----------------------------
   Simple, easy-to-read CSS
   - Uses flexbox only (no grid)
   - Clear class names and comments
   ----------------------------- */

/* Basic reset */
* { box-sizing: border-box; }
html, body { height: 100%; margin: 0; font-family: Arial, Helvetica, sans-serif; }
/* Theme: sky blue, white, black */
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
/* Removed contenteditable placeholder pseudo-element (we use a textarea instead) */
.editor-label { display:block; color:#888; font-size:14px; margin-bottom:8px; }

/* Responsive */
/* Responsive rules for narrower viewports (tablets / small laptops)
   - keep rules simple and documented so they're easy to understand
   - only use class selectors (no variables or ARIA changes) */
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
        <div class="icon plus" id="btn-new-top" title="New note"><i class="fa fa-plus"></i></div>
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
      <div class="btn"><i class="fa-solid fa-sliders"></i> <span style="margin-left:6px;color:#444">Advanc</span></div>
    </div>

  <!-- Filter all/pinned/starred -->
  <div class="filters" id="filters">
  <button class="btn filter-btn filter-all active" id="filterAll"><i class="fa fa-folder-open"></i> All</button>
  <button class="btn filter-btn filter-pinned" id="filterPinned"><i class="fa fa-thumbtack" style="color:red"></i> Pinned</button>
  <button class="btn filter-btn filter-starred" id="filterStarred"><i class="fa fa-star " style="color:yellow "></i> Starred</button>
    </div>

    <!-- Recent & sort -->
    <div class="recent">
      <div style="font-weight:600;color:#222">Recent</div>
      <div style="display:flex; gap:8px; align-items:center;">
        <select id="sortSelect">
          <option>Last Updated</option>
          <option>Newest</option>
          <option>Oldest</option>
        </select>
        <div class="icon" id="btn-new-small" title="New quick note" style="width:36px;height:36px;"><i class="fa fa-square-plus" style="color: #74C0FC;"></i></div>
      </div>
    </div>

    <div class="divider"></div>

  <!-- 4) Folders + notes -->
  <div class="sb-list" id="sidebarList">
      <div class="folder-row">
        <div class="left"><i class="fas fa-folder" style="color: #74C0FC;"></i> <span style="margin-left:8px">All Notes</span></div>
        <div class="badge" id="allCount">0</div>
      </div>

  <div class="folder-row" style="background:#fff; border:1px solid #eee; align-items:center;">
        <div style="display:flex; align-items:center; gap:10px;">
          <div style="width:10px;height:10px;border-radius:10px;background:var(--blue-dot)"></div>
          <div>Notes</div>
        </div>
        <div class="badge" id="notesCount">0</div>
      </div>

      <!-- small note card(s) -->
      <div id="noteSmallContainer"></div>
    </div>
  </aside>

  <!-- MAIN AREA -->
  <main class="main" id="mainContent">
    <!-- Top row: title (editable) & toolbar right -->
    <div class="top-row">
      <input id="noteTitle" class="title-input" placeholder="Note title..." />
      <div class="right-tools">
        <!-- toolbar buttons (mirrors screenshot) -->
  <div class="tb-btn tb-btn--text active"><i class="fa fa-font"></i> Text</div>
  <div class="tb-btn tb-btn--rich"><i class="fa-regular fa-file-lines"></i> Rich</div>
  <div class="tb-btn tb-btn--draw"><i class="fas fa-pen-nib" style="color: #f3d230;"></i></i> Draw</div>
  <div class="tb-btn tb-btn--ai"><i class="fas fa-robot" style="color: #23a495;"></i> AI Assistant</div>
  <button id="saveBtn" class="save-btn" title="Save note"><i class="fa-solid fa-floppy-disk" ></i> Save</button>
      </div>
    </div>

    <!-- folder + tags + add tag -->
    <div class="sub-row">
      <div style="display:flex; align-items:center; gap:12px;">
        <div style="display:flex; align-items:center; gap:8px;">
          <i class="fa-regular fa-folder"></i>
          <select id="folderSelect" class="folder-select">
            <option>No Folder</option>
            <option>Notes</option>
          </select>
        </div>
      </div>

      <div style="margin-left:auto; display:flex; align-items:center; gap:8px;">
        <div style="display:flex; gap:8px; align-items:center;" id="tagList">
          <!-- tags will render here -->
        </div>
        <div class="tag-input">
          <input id="tagInput" class="tag-field" placeholder="Add tag..." />
          <button id="addTagBtn" class="add-tag-btn"><i class="fa fa-plus"></i> Add</button>
        </div>
      </div>
    </div>

    <!-- dates -->
    <div class="dates" id="datesRow">
      <i class="fa-regular fa-calendar"></i> Created: <span id="createdDate">-</span>
      &nbsp;&nbsp;&nbsp;
      <i class="fa-regular fa-clock"></i> Updated: <span id="updatedDate">-</span>
    </div>

    <div class="content-area">
      <label for="editor" class="editor-label">Start writing your note... Ask your AI assistant for help organizing your thoughts!</label>
      <textarea id="editor" class="note-editor" name="editor"></textarea>
    </div>
  </main>
</div>


</body>
</html>
