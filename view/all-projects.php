<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Smart Notes - All Projects</title>
  <link rel="stylesheet" href="css_view.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body>
  <div class="container">
    <!-- SIDEBAR (GIỮ NGUYÊN CLASS) -->
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
        <div class="nav-item active"><i class="fa fa-folder"></i> All</div>
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

      <!-- Danh sách project -->
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

    <!-- MAIN CONTENT -->
    <main class="main-content">
      <h2>All Projects</h2>
      <p>Chọn một dự án từ menu bên trái để xem và quản lý ghi chú.</p>
    </main>
  </div>
</body>
</html>