<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Project 1 - Smart Notes</title>
  <link rel="stylesheet" href="css_view.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body>
  <div class="container">
    <!-- SIDEBAR - GIỐNG HỆT TRANG TRƯỚC -->
    <aside class="sidebar" id="Sidebar">
      <div class="sb-top">
        <div class="sb-title">Smart Notes</div>
        <div class="sb-icons">
          <div class="icon" title="Trash"><i class="fa-regular fa-trash-can"></i></div>
          <div class="icon" title="Desktop"><i class="fas fa-desktop"></i></div>
          <div class="icon plus" title="New note">
            <i class="fa fa-plus"></i>
          </div>
        </div>
      </div>

      <div class="search-box">
        <input type="text" placeholder="Search notes...">
      </div>

      <nav class="sb-nav">
        <div class="nav-item">Advanc</div>
        <div class="nav-item"><i class="fa fa-folder"></i> All</div>
        <div class="nav-item"><i class="fa fa-thumbtack" style="color: red;"></i> Pinned</div>
        <div class="nav-item"><i class="fa fa-star" style="color: gold;"></i> Starred</div>
      </nav>

      <div class="section">
        <h4>Recent</h4>
        <div class="filter">
          <select><option>Last Updated</option></select>
          <button class="add-btn">+</button>
        </div>
      </div>

      <div class="section">
        <h4>All Project</h4>
        <ul class="project-list">
          <li><a href="project-detail.html">Project 1</a></li>
          <li><a href="project-detail.html">Project 2</a></li>
          <li><a href="project-detail.html">Project 3</a></li>
        </ul>
      </div>

      <div class="section">
        <h4>Notes</h4>
        <div class="note-item">Notes <span>0</span></div>
      </div>
    </aside>

    <!-- MAIN CONTENT: Hiển thị project + note list + editor -->
    <main class="main-content">
      <div class="project-header">
        <h1>Project 1</h1>
        <div class="project-actions">
          <button class="btn-primary">+ Add Note</button>
        </div>
      </div>

      <!-- Danh sách note con -->
      <div class="note-list">
        <div class="note-card" data-id="1">
          <div class="note-title">Ghi chú họp nhóm</div>
          <div class="note-preview">Nội dung cuộc họp ngày 24/11... chuẩn bị slide...</div>
          <div class="note-meta">
            <span>Nguyễn Văn A</span>
            <span>24/11/2025 10:30</span>
          </div>
          <div class="note-actions">
            <a href="#">Edit</a>
            <a href="#" onclick="return confirm('Xác nhận xóa?')">Delete</a>
            <a href="#">Update Status</a>
          </div>
        </div>

        <div class="note-card" data-id="2">
          <div class="note-title">Ý tưởng thiết kế UI</div>
          <div class="note-preview">Dùng màu xanh chủ đạo, bố cục tối giản...</div>
          <div class="note-meta">
            <span>Lê Thị B</span>
            <span>24/11/2025 09:15</span>
          </div>
          <div class="note-actions">
            <a href="#">Edit</a>
            <a href="#" onclick="return confirm('Xác nhận xóa?')">Delete</a>
            <a href="#">Update Status</a>
          </div>
        </div>
      </div>

      <!-- Vùng soạn thảo (giữ nguyên như giao diện gốc) -->
      <div class="editor-area">
        <div class="editor-toolbar">
          <button class="btn-tool">Text</button>
          <button class="btn-tool">Rich</button>
          <button class="btn-tool">Draw</button>
          <button class="btn-tool ai">AI Assistant</button>
          <button class="btn-save">Save</button>
        </div>
        <div class="editor-container">
          <textarea placeholder="Start writing your note... Ask your AI assistant for help organizing your thoughts!"></textarea>
        </div>
      </div>
    </main>
  </div>
</body>
</html>