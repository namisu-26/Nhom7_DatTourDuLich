<?php
// Nếu bạn không cần kết nối DB chỉ để hiển thị form thì có thể không require file DB.
// Nhưng giữ require nếu bạn muốn dùng dữ liệu DB trên trang này.
// require_once __DIR__ . '/../../functions/db_connection.php';

// bật hiển thị lỗi tạm thời (bỏ/comment khi chạy production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Thêm Tour</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

  <style>
    body { background:#f8fafc; font-family: Inter, 'Segoe UI', system-ui, -apple-system, 'Helvetica Neue', Arial; }
    .page-card {
      max-width: 900px;
      margin: 40px auto;
      border-radius: 14px;
      box-shadow: 0 8px 30px rgba(15, 23, 42, 0.08);
      overflow: hidden;
      background: #fff;
    }
    .card-header {
      background: linear-gradient(90deg,#ffffff,#f8fafc);
      padding: 22px 28px;
      border-bottom: 1px solid rgba(0,0,0,0.04);
    }
    .form-section { padding: 20px 28px 28px; }
    .img-preview {
      width: 220px;
      height: 140px;
      object-fit: cover;
      border-radius: 10px;
      border: 1px solid #e6e9ee;
    }
    .btn-save { background:#16a34a; color:#fff; border-radius:10px; }
    .btn-save:hover { background:#15803d; color:#fff; }
    .note { color:#64748b; font-size:0.9rem; }
  </style>
</head>
<body>

<div class="page-card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <div>
      <h4 class="mb-0">Thêm Tour Mới</h4>
      <div class="note">Nhập thông tin tour và tải lên ảnh. Ảnh sẽ lưu vào thư mục <code>/uploads</code>.</div>
    </div>
    <div>
      <a href="list_tour.php" class="btn btn-outline-secondary"><i class="fa fa-arrow-left me-1"></i> Quay về</a>
    </div>
  </div>

  <div class="form-section">
    <!-- form gửi đến file xử lý tour (handle/tour_process.php) -->
    <form action="../../handle/tour_process.php" method="post" enctype="multipart/form-data" id="addTourForm">
      <div class="row g-3">
        <div class="col-md-8">
          <div class="mb-3">
            <label class="form-label">Tên tour</label>
            <input type="text" name="tour_name" class="form-control" required placeholder="Ví dụ: Tour Nha Trang 3 ngày">
          </div>

          <div class="mb-3">
            <label class="form-label">Mô tả</label>
            <textarea name="description" class="form-control" rows="4" placeholder="Mô tả ngắn" required></textarea>
          </div>

          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">Giá (VNĐ)</label>
              <input type="number" name="price" class="form-control" min="0" step="1000" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Ngày bắt đầu</label>
              <input type="date" name="start_date" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Ngày kết thúc</label>
              <input type="date" name="end_date" class="form-control" required>
            </div>
          </div>

          <div class="mt-4 d-flex gap-2">
            <button type="submit" name="add_tour" class="btn btn-save"><i class="fa fa-plus me-1"></i> Thêm tour</button>
            <a href="list_tour.php" class="btn btn-light border">Hủy</a>
          </div>
        </div>

        <div class="col-md-4">
          <label class="form-label">Ảnh tour (tùy chọn)</label>
          <div class="mb-2">
            <img src="https://via.placeholder.com/440x280?text=Preview" id="previewImg" class="img-preview" alt="preview">
          </div>
          <input type="file" name="image" id="imageInput" accept="image/*" class="form-control">

          <div class="mt-3 note">
            Kích thước tối ưu: 1200x800. Định dạng: jpg, png. <br>
            Tên file sẽ được đổi để tránh trùng lặp.
          </div>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
  // JavaScript preview ảnh trước khi upload
  document.getElementById('imageInput').addEventListener('change', function(e){
    const file = e.target.files[0];
    const preview = document.getElementById('previewImg');
    if (!file) {
      preview.src = 'https://via.placeholder.com/440x280?text=Preview';
      return;
    }
    const reader = new FileReader();
    reader.onload = function(ev) {
      preview.src = ev.target.result;
    }
    reader.readAsDataURL(file);
  });
</script>

</body>
</html>
